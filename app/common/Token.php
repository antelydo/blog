<?php
declare (strict_types = 1);

namespace app\common;




use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Log;
use app\common\DeBugLog;


/**
 * Token 管理类
 * 用于管理员后台登录、前端用户登录和API接口token验证
 * 支持多语言和全局配置
 */
class Token
{

    /**
     * 获取配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getConfig(string $key, $default = null)
    {
        return Config::get('token.' . $key, $default);
    }

    /**
     * 获取缓存键名
     * @param string $type 用户类型
     * @param string $token Token
     * @return string
     */
    protected static function getCacheKey(string $type, string $token): string
    {
        $prefix = self::getConfig('cache_prefix', 'token_');
        return $prefix . $type . '_' . $token;
    }

    /**
     * 生成安全的 Token
     * @param array $data 用户数据
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @param int|null $expire Token有效期（秒）
     * @return string
     */
    public static function generateToken(array $data, string $type = 'user', ?int $expire = null): string
    {
        // 获取配置的过期时间，如果未指定则使用默认值
        $expire = $expire ?? self::getConfig('expire_time', 7200);

       // DeBugLog::recordController('token', 'generateToken', ['user_type'=>$type], $type, 1000, '用户类型');

        // 获取安全密钥
        $secretKey = self::getConfig('secret_key', 'default_secret_key');

       // DeBugLog::recordController('token', 'generateToken', ['secretKey'=>$secretKey],$secretKey, 1000, '安全密钥');

        // 获取客户端IP
        $clientIp = Request::ip();

        // 生成随机字符串
        $randomStr = bin2hex(random_bytes(16));

        // 生成时间戳
        $timestamp = time();

        // 生成唯一标识 - 使用更安全的算法
        $payload = json_encode([
            'id' => $data['id'],
            'type' => $type,
            'random' => $randomStr,
            'timestamp' => $timestamp,
            'ip' => $clientIp
        ]);

        // 使用 HMAC 算法生成签名
        $signature = hash_hmac('sha256', $payload, $secretKey);
        // 最终 Token
        $token = base64_encode($payload) . '.' . $signature;

       // DeBugLog::recordController('token', 'generateToken', ['token'=>$token], $token, 1000, '最终token');

        // 获取当前语言
        $currentLang = Lang::getLangSet();

        // 缓存用户数据
        $cacheData = [
            'id' => $data['id'],
            'type' => $type,
            'token' => $token,
            'data' => $data,
            'create_time' => $timestamp,
            'expire_time' => $timestamp + $expire,
            'lang' => $currentLang, // 保存用户当前使用的语言
            'ip' => $clientIp, // 保存用户IP，用于IP绑定
            'user_agent' => Request::header('user-agent'), // 保存用户代理信息
            'last_activity' => $timestamp, // 最后活动时间
            'nonce' => $randomStr // 防重放攻击的随机数
        ];


        // 获取缓存键名
        $cacheKey = self::getCacheKey($type, token: $token);

       // DeBugLog::recordController('token', 'generateToken', ['cacheKey'=>$cacheKey], $cacheKey, 1000, '获取缓存键名');


        // 获取缓存标签
        $cacheTag = self::getConfig('cache_tag', 'token');

      //  DeBugLog::recordController('token', 'generateToken', ['cacheTag'=>$cacheTag], $cacheTag, 1000, '获取缓存标签');


        // 存储到缓存
        if ($cacheTag) {
            Cache::tag($cacheTag)->set($cacheKey, $cacheData, $expire);
            // 获取缓存键名
            $cacheKey = self::getCacheKey($type, $token);

            // 从缓存获取数据
            $cacheData = Cache::get($cacheKey);
        //    DeBugLog::recordController('token', 'generateToken', ['cacheData'=>$cacheData], $cacheData, 1000, '从缓存获取数据1');

        } else {
            Cache::set($cacheKey, $cacheData, $expire);
            $cacheData = Cache::get($cacheKey);
       //     DeBugLog::recordController('token', 'generateToken', ['cacheData'=>$cacheData], $cacheData, 1000, '从缓存获取数据2');
        }

        // 如果是管理员或用户，更新数据库中的token

        if (in_array($type, ['admin', 'user'])) {
            $table = $type == 'admin' ? 'admin' : 'user';
            Db::name($table)->where('id', $data['id'])->update([
                'token' => $token,
                'token_expire' => $timestamp + $expire,
                'lang' => $currentLang, // 保存用户语言偏好到数据库
                'last_login_ip' => $clientIp, // 记录最后登录IP
                'last_login_time' => $timestamp, // 记录最后登录时间
                'login_count' => Db::raw('login_count+1') // 登录次数+1
            ]);

            // 记录登录日志

            // 应该改为
             $langs = $type == 'admin'? 'admin' : 'user';
            $deacon=Lang::get('token.'.$langs.'_login');
            self::logActivity($data['id'], $type, 'login', description: $deacon, data: [
                'ip' => $clientIp,
                'user_agent' => Request::header('user-agent')
            ]);
        }

        return $token;
    }

    /**
     * 验证 Token
     * @param string $token Token
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @param bool $checkIp 是否检查IP绑定
     * @return array|bool 成功返回用户数据，失败返回false
     */
    public static function verifyToken(string $token, string $type = 'user', bool $checkIp = true)
    {

        if (empty($token)) {
            self::logWarning('Token验证失败', ['reason' => 'Token为空']);
            return false;
        }

        // 检查Token是否在黑名单中
        if (self::isTokenBlacklisted($token)) {
            self::logWarning('Token验证失败', ['reason' => 'Token在黑名单中', 'token' => $token]);
            return false;
        }

        // 获取缓存键名
        $cacheKey = self::getCacheKey($type, $token);
       // DeBugLog::recordController('Token', 'getCacheKey', ['cacheKey'=>$cacheKey], json_encode(['cacheKey'=>$cacheKey,'type'=>$type,]), 1000, '验证TOKEN 获取缓存 获取缓存键名');

        // 从缓存获取数据
        $cacheData = Cache::get($cacheKey);

       // DeBugLog::recordController('Token', 'Cache::get', ['cacheData'=>$cacheData], json_encode(['cacheData'=>$cacheData,'type'=>$type,]), 1000, '验证TOKEN  从缓存获取数据');


        // 验证缓存是否存在
        if (empty($cacheData)) {
            self::logWarning('Token验证失败', ['reason' => '缓存不存在', 'token' => $token]);
            return false;
        }


        // 验证是否过期
        if ($cacheData['expire_time'] < time()) {
            // 获取缓存标签
            $cacheTag = self::getConfig('cache_tag', 'token');

            // 删除缓存
            if ($cacheTag) {
                Cache::tag($cacheTag)->clear();
            } else {
                Cache::delete($cacheKey);
            }

            self::logWarning('Token验证失败', ['reason' => 'Token已过期', 'token' => $token]);
            return false;
        }

        // 检查IP绑定
        if ($checkIp && self::getConfig('check_ip_binding', true)) {
            $currentIp = Request::ip();
            if ($cacheData['ip'] !== $currentIp) {
                self::logWarning('Token验证失败', [
                    'reason' => 'IP不匹配',
                    'token' => $token,
                    'expected_ip' => $cacheData['ip'],
                    'current_ip' => $currentIp
                ]);
                return false;
            }
        }



        // 检查Token是否被篡改
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            self::logWarning('Token验证失败', ['reason' => 'Token格式错误', 'token' => $token]);
            return false;
        }


        $payload = $parts[0];
        $signature = $parts[1];

        // 获取安全密钥
        $secretKey = self::getConfig('secret_key', 'default_secret_key');

        // 解码payload
        $decodedPayload = base64_decode($payload);

        if ($decodedPayload === false) {
            self::logWarning('Token验证失败', ['reason' => 'Payload解码失败', 'token' => $token]);
            return false;
        }

        // 验证签名
        $expectedSignature = hash_hmac('sha256', $decodedPayload, $secretKey);
        if (!hash_equals($expectedSignature, $signature)) {
            self::logWarning('Token验证失败', ['reason' => '签名验证失败', 'token' => $token]);
            return false;
        }

        // 更新最后活动时间
        $cacheData['last_activity'] = time();

        // 更新缓存
        $expire = $cacheData['expire_time'] - time();
        if ($expire > 0) {
            $cacheTag = self::getConfig('cache_tag', 'token');
            if ($cacheTag) {
                Cache::tag($cacheTag)->set($cacheKey, $cacheData, $expire);
            } else {
                Cache::set($cacheKey, $cacheData, $expire);
            }
        }

        // 设置用户的语言偏好
        if (isset($cacheData['lang'])) {
            Lang::setLangSet($cacheData['lang']);
        }
        //DeBugLog::recordController('Token', 'Cache::get', ['cacheData'=>$cacheData], json_encode(['cacheData'=>$cacheData,'type'=>$type,]), 1000, '验证成功！！！！');
        return $cacheData['data'];
    }

    /**
     * 刷新 Token
     * @param string $token 原Token
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @param int|null $expire Token有效期（秒）
     * @return string|bool 成功返回新Token，失败返回false
     */
    public static function refreshToken(string $token, string $type = 'user', ?int $expire = null)
    {
        // 获取配置的过期时间，如果未指定则使用默认值
        $expire = $expire ?? self::getConfig('expire_time', 7200);

        // 验证旧token
        $userData = self::verifyToken($token, $type, false); // 刷新时不检查IP绑定
        if (!$userData) {
            return false;
        }

        // 获取缓存键名
        $cacheKey = self::getCacheKey($type, $token);

        // 从缓存获取数据
        $cacheData = Cache::get($cacheKey);
        $lang = $cacheData['lang'] ?? Lang::getLangSet();

        // 获取缓存标签
        $cacheTag = self::getConfig('cache_tag', 'token');

        // 将旧token加入黑名单
        self::addTokenToBlacklist($token, $cacheData['expire_time'] - time());

        // 删除旧token
        if ($cacheTag) {
            Cache::tag($cacheTag)->clear();
        } else {
            Cache::delete($cacheKey);
        }

        // 设置语言
        Lang::setLangSet($lang);

        // 记录Token刷新日志
        self::logActivity($userData['id'], $type, 'refresh_token', 'Token刷新', [
            'old_token' => $token,
            'ip' => Request::ip()
        ]);

        // 生成新token
        return self::generateToken($userData, $type, $expire);
    }

    /**
     * 清除 Token
     * @param string $token Token
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @return bool
     */
    public static function clearToken(string $token, string $type = 'user'): bool
    {
        // 获取缓存键名
        $cacheKey = self::getCacheKey($type, $token);

        // 从缓存获取数据
        $cacheData = Cache::get($cacheKey);

        // 验证缓存是否存在
        if (!empty($cacheData) && isset($cacheData['id'])) {
            // 将token加入黑名单
            $remainingTime = $cacheData['expire_time'] - time();
            if ($remainingTime > 0) {
                self::addTokenToBlacklist($token, $remainingTime);
            }

            // 如果是管理员或用户，清除数据库中的token
            if (in_array($type, ['admin', 'user'])) {
                $table = $type == 'admin' ? 'admin' : 'user';
                Db::name($table)->where('id', $cacheData['id'])->update([
                    'token' => '',
                    'token_expire' => 0
                    // 不清除语言偏好，保留用户的语言设置
                ]);

                // 记录登出日志
                self::logActivity($cacheData['id'], $type, 'logout', '用户登出', [
                    'ip' => Request::ip(),
                    'token' => $token
                ]);
            }
        }

        // 获取缓存标签
        $cacheTag = self::getConfig('cache_tag', 'token');


        // 删除缓存
        if ($cacheTag) {
            return Cache::tag($cacheTag)->clear();
        } else {
            return Cache::delete($cacheKey);
        }
    }

    /**
     * 从请求头获取Token
     * @return string|null
     */
    public static function getRequestToken(): ?string
    {
        $token = Request::header('Authorization');
        // 如果Authorization头不存在，尝试从请求参数获取
        if (empty($token)) {
            $token = Request::param('token', '');
        } else {
            // 处理Bearer token格式
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
        }

        return $token ?: null;
    }

    /**
     * API接口Token验证中间件方法
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @return array|bool 成功返回用户数据，失败返回false
     */
    public static function checkApiToken(string $type = 'user')
    {
        // 获取请求中的token
        $token = self::getRequestToken();

        // 获取请求中的语言设置
        $lang = Request::header('Accept-Language');
        if (!empty($lang)) {
            // 检查是否在允许的语言列表中
            $allowList = self::getConfig('lang.allow_list', ['zh-cn', 'en']);
            if (in_array($lang, $allowList)) {
                // 设置语言
                Lang::setLangSet($lang);
            }
        }

        // 验证token
        return self::verifyToken($token, $type);
    }

    /**
     * 设置用户语言
     * @param string $token Token
     * @param string $lang 语言代码
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @return bool
     */
    public static function setUserLanguage(string $token, string $lang, string $type = 'user'): bool
    {
        // 检查是否在允许的语言列表中
        $allowList = self::getConfig('lang.allow_list', ['zh-cn', 'en']);
        if (!in_array($lang, $allowList)) {
            return false;
        }

        // 验证token
        $userData = self::verifyToken($token, $type);
        if (!$userData) {
            return false;
        }

        // 获取缓存键名
        $cacheKey = self::getCacheKey($type, $token);

        // 从缓存获取数据
        $cacheData = Cache::get($cacheKey);
        if (empty($cacheData)) {
            return false;
        }

        // 更新缓存中的语言设置
        $cacheData['lang'] = $lang;

        // 获取缓存标签
        $cacheTag = self::getConfig('cache_tag', 'token');

        // 更新缓存
        $expire = $cacheData['expire_time'] - time();
        if ($expire <= 0) {
            return false;
        }

        if ($cacheTag) {
            Cache::tag($cacheTag)->set($cacheKey, $cacheData, $expire);
        } else {
            Cache::set($cacheKey, $cacheData, $expire);
        }

        // 如果是管理员或用户，更新数据库中的语言设置
        if (in_array($type, ['admin', 'user'])) {
            $table = $type == 'admin' ? 'admin' : 'user';
            Db::name($table)->where('id', $userData['id'])->update([
                'lang' => $lang
            ]);
        }

        // 设置当前语言
        Lang::setLangSet($lang);

        return true;
    }

    /**
     * 清除所有Token
     * @param string $type 用户类型 admin-管理员 user-前端用户 all-所有
     * @return bool
     */
    public static function clearAllTokens(string $type = 'all'): bool
    {
        // 获取缓存标签
        $cacheTag = self::getConfig('cache_tag', 'token');

        if ($cacheTag) {
            if ($type === 'all') {
                // 清除所有token
                return Cache::tag($cacheTag)->clear();
            } else {
                // 清除指定类型的token
                $prefix = self::getConfig('cache_prefix', 'token_') . $type . '_';
                $keys = Cache::tag($cacheTag);

                foreach ($keys as $key) {
                    if (strpos($key, $prefix) === 0) {
                        Cache::delete($key);
                    }
                }

                return true;
            }
        }

        // 如果没有使用标签，则无法有效清除所有token
        return false;
    }

    /**
     * 将Token添加到黑名单
     * @param string $token Token
     * @param int $ttl 有效期（秒）
     * @return bool
     */
    protected static function addTokenToBlacklist(string $token, int $ttl): bool
    {
        if ($ttl <= 0) {
            return false;
        }

        $blacklistKey = self::getConfig('blacklist_prefix', 'token_blacklist_') . $token;
        return Cache::set($blacklistKey, time(), $ttl);
    }

    /**
     * 检查Token是否在黑名单中
     * @param string $token Token
     * @return bool
     */
    protected static function isTokenBlacklisted(string $token): bool
    {
        $blacklistKey = self::getConfig('blacklist_prefix', 'token_blacklist_') . $token;
        return Cache::has($blacklistKey);
    }

    /**
     * 记录用户活动日志
     * @param int $userId 用户ID
     * @param string $userType 用户类型
     * @param string $action 操作类型
     * @param string $description 描述
     * @param array $data 附加数据
     * @return bool
     */
    protected static function logActivity(int $userId, string $userType, string $action, string $description, array $data = []): bool
    {
        try {
            // 检查是否启用活动日志
            if (!self::getConfig('enable_activity_log', true)) {
                return false;
            }

            // 记录到数据库
            Db::name('user_activity_log')->insert([
                'user_id' => $userId,
                'user_type' => $userType,
                'action' => $action,
                'description' => $description,
                'ip' => Request::ip(),
                'user_agent' => Request::header('user-agent'),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'create_time' => time()
            ]);

            return true;
        } catch (\Exception $e) {
            self::logError('记录活动日志失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'action' => $action
            ]);
            return false;
        }
    }

    /**
     * 记录警告日志
     * @param string $message 消息
     * @param array $context 上下文
     */
    protected static function logWarning(string $message, array $context = []): void
    {
        if (self::getConfig('enable_log', true)) {
            Log::warning($message, $context);
        }
    }

    /**
     * 记录错误日志
     * @param string $message 消息
     * @param array $context 上下文
     */
    protected static function logError(string $message, array $context = []): void
    {
        if (self::getConfig('enable_log', true)) {
            Log::error($message, $context);
        }
    }

    /**
     * 强制用户下线
     * @param int $userId 用户ID
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @return bool
     */
    public static function forceLogout(int $userId, string $type = 'user'): bool
    {
        try {
            // 获取用户当前token
            $table = $type == 'admin' ? 'admin' : 'user';
            $user = Db::name($table)->where('id', $userId)->field('token, token_expire')->find();

            if (!$user || empty($user['token'])) {
                return false;
            }

            // 将token加入黑名单
            $remainingTime = $user['token_expire'] - time();
            if ($remainingTime > 0) {
                self::addTokenToBlacklist($user['token'], $remainingTime);
            }

            // 清除数据库中的token
            Db::name($table)->where('id', $userId)->update([
                'token' => '',
                'token_expire' => 0
            ]);

            // 记录强制下线日志
            self::logActivity($userId, $type, 'force_logout', '强制用户下线', [
                'admin_id' => Request::param('admin_id', 0),
                'reason' => Request::param('reason', '管理员操作')
            ]);

            return true;
        } catch (\Exception $e) {
            self::logError('强制用户下线失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Public wrapper for logActivity
     *
     * @param int $userId User ID
     * @param string $userType User type (admin/user)
     * @param string $action Action name
     * @param string $description Action description
     * @param array $data Additional data
     * @return bool
     */
    public static function recordActivity($userId, $userType, $action, $description, $data = [])
    {
        return self::logActivity($userId, $userType, $action, $description, $data);
    }
}