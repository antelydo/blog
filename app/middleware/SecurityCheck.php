<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Log;

/**
 * 安全检查中间件
 * 实现登录尝试限制、API速率限制等安全功能
 */
class SecurityCheck
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @param string $type 检查类型 login-登录检查 api-API请求检查
     * @return Response
     */
    public function handle($request, Closure $next, string $type = 'api')
    {
        // 获取客户端IP
        $clientIp = $request->ip();
        
        // 根据检查类型执行不同的安全检查
        switch ($type) {
            case 'login':
                // 登录尝试限制检查
                $result = $this->checkLoginAttempts($clientIp, $request);
                if ($result !== true) {
                    return $result;
                }
                break;
                
            case 'api':
                // API请求速率限制检查
                $result = $this->checkRateLimit($clientIp, $request);
                if ($result !== true) {
                    return $result;
                }
                break;
                
            default:
                // 默认不做任何检查
                break;
        }
        
        // 继续处理请求
        return $next($request);
    }
    
    /**
     * 检查登录尝试次数
     * @param string $ip 客户端IP
     * @param Request $request 请求对象
     * @return bool|Response 通过返回true，否则返回Response对象
     */
    protected function checkLoginAttempts(string $ip, Request $request)
    {
        // 获取配置
        $maxAttempts = Config::get('token.max_login_attempts', 5);
        $lockoutTime = Config::get('token.login_lockout_time', 1800);
        
        // 缓存键名
        $cacheKey = 'login_attempts_' . md5($ip);
        $lockKey = 'login_lockout_' . md5($ip);
        
        // 检查是否被锁定
        if (Cache::has($lockKey)) {
            // 获取剩余锁定时间
            $remainingTime = Cache::get($lockKey);
            
            // 记录日志
            Log::warning('登录尝试被锁定', [
                'ip' => $ip,
                'remaining_time' => $remainingTime,
                'user_agent' => $request->header('user-agent')
            ]);
            
            // 返回锁定信息
            return json([
                'code' => 429,
                'msg' => '登录尝试次数过多，请' . ceil($remainingTime / 60) . '分钟后再试',
                'data' => null
            ]);
        }
        
        // 如果是登录请求，则不做进一步检查
        // 登录成功或失败的处理会在登录控制器中进行
        return true;
    }
    
    /**
     * 检查API请求速率
     * @param string $ip 客户端IP
     * @param Request $request 请求对象
     * @return bool|Response 通过返回true，否则返回Response对象
     */
    protected function checkRateLimit(string $ip, Request $request)
    {
        // 获取配置
        $rateLimit = Config::get('token.rate_limit', 60);
        
        // 缓存键名
        $cacheKey = 'rate_limit_' . md5($ip) . '_' . date('YmdHi');
        
        // 获取当前请求次数
        $currentCount = Cache::get($cacheKey, 0);
        
        // 增加请求次数
        Cache::set($cacheKey, $currentCount + 1, 60); // 1分钟有效期
        
        // 检查是否超过限制
        if ($currentCount >= $rateLimit) {
            // 记录日志
            Log::warning('API请求速率超限', [
                'ip' => $ip,
                'count' => $currentCount,
                'limit' => $rateLimit,
                'uri' => $request->url(),
                'user_agent' => $request->header('user-agent')
            ]);
            
            // 返回限制信息
            return json([
                'code' => 429,
                'msg' => '请求过于频繁，请稍后再试',
                'data' => null
            ]);
        }
        
        return true;
    }
    
    /**
     * 记录登录失败
     * 此方法应在登录控制器中调用
     * @param string $ip 客户端IP
     * @param string $username 尝试登录的用户名
     * @return bool 是否被锁定
     */
    public static function recordLoginFailure(string $ip, string $username = '')
    {
        // 获取配置
        $maxAttempts = Config::get('token.max_login_attempts', 5);
        $lockoutTime = Config::get('token.login_lockout_time', 1800);
        
        // 缓存键名
        $cacheKey = 'login_attempts_' . md5($ip);
        $lockKey = 'login_lockout_' . md5($ip);
        
        // 获取当前尝试次数
        $attempts = Cache::get($cacheKey, 0);
        $attempts++;
        
        // 记录日志
        Log::warning('登录失败', [
            'ip' => $ip,
            'username' => $username,
            'attempts' => $attempts
        ]);
        
        // 检查是否超过最大尝试次数
        if ($attempts >= $maxAttempts) {
            // 锁定账户
            Cache::set($lockKey, $lockoutTime, $lockoutTime);
            Cache::delete($cacheKey);
            
            // 记录锁定日志
            Log::warning('登录尝试锁定', [
                'ip' => $ip,
                'username' => $username,
                'lockout_time' => $lockoutTime
            ]);
            
            return true; // 已锁定
        } else {
            // 更新尝试次数
            Cache::set($cacheKey, $attempts, $lockoutTime);
            return false; // 未锁定
        }
    }
    
    /**
     * 重置登录尝试次数
     * 此方法应在登录成功后调用
     * @param string $ip 客户端IP
     * @return bool
     */
    public static function resetLoginAttempts(string $ip)
    {
        // 缓存键名
        $cacheKey = 'login_attempts_' . md5($ip);
        
        // 删除尝试记录
        return Cache::delete($cacheKey);
    }
}
