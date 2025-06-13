<?php
declare (strict_types = 1);

namespace app\common;

use think\facade\Request;
use think\facade\Log;
use think\facade\Cache;
use think\facade\Config;

/**
 * 安全助手类
 * 提供通用的安全功能
 */
class Security
{
    /**
     * 生成CSRF令牌
     * @param string $sessionId 会话ID
     * @return string
     */
    public static function generateCsrfToken(string $sessionId): string
    {
        // 生成随机字符串
        $randomStr = bin2hex(random_bytes(16));
        
        // 生成时间戳
        $timestamp = time();
        
        // 生成CSRF令牌
        $token = hash_hmac('sha256', $sessionId . $randomStr . $timestamp, Config::get('token.secret_key', ''));
        
        // 存储到缓存
        $cacheKey = 'csrf_token_' . $sessionId;
        Cache::set($cacheKey, [
            'token' => $token,
            'timestamp' => $timestamp
        ], 3600); // 1小时有效期
        
        return $token;
    }
    
    /**
     * 验证CSRF令牌
     * @param string $token CSRF令牌
     * @param string $sessionId 会话ID
     * @return bool
     */
    public static function verifyCsrfToken(string $token, string $sessionId): bool
    {
        // 获取缓存中的令牌
        $cacheKey = 'csrf_token_' . $sessionId;
        $cachedData = Cache::get($cacheKey);
        
        // 验证令牌是否存在
        if (empty($cachedData) || !isset($cachedData['token'])) {
            return false;
        }
        
        // 验证令牌是否匹配
        if (!hash_equals($cachedData['token'], $token)) {
            // 记录可能的CSRF攻击
            Log::warning('CSRF令牌验证失败', [
                'session_id' => $sessionId,
                'ip' => Request::ip(),
                'user_agent' => Request::header('user-agent')
            ]);
            
            return false;
        }
        
        // 验证成功后删除令牌，防止重放攻击
        Cache::delete($cacheKey);
        
        return true;
    }
    
    /**
     * 生成安全的随机密码
     * @param int $length 密码长度
     * @param bool $includeSpecial 是否包含特殊字符
     * @return string
     */
    public static function generateRandomPassword(int $length = 12, bool $includeSpecial = true): string
    {
        // 字符集
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';
        
        // 组合字符集
        $chars = $lowercase . $uppercase . $numbers;
        if ($includeSpecial) {
            $chars .= $special;
        }
        
        // 生成密码
        $password = '';
        $charsLength = strlen($chars);
        
        // 确保密码包含至少一个小写字母、一个大写字母和一个数字
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        
        // 如果包含特殊字符，确保密码包含至少一个特殊字符
        if ($includeSpecial) {
            $password .= $special[random_int(0, strlen($special) - 1)];
        }
        
        // 生成剩余的密码字符
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }
        
        // 打乱密码字符顺序
        return str_shuffle($password);
    }
    
    /**
     * 检查密码强度
     * @param string $password 密码
     * @return array 包含强度评分和建议的数组
     */
    public static function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $suggestions = [];
        
        // 检查长度
        $length = strlen($password);
        if ($length < 8) {
            $score += 0;
            $suggestions[] = '密码长度应至少为8个字符';
        } elseif ($length < 12) {
            $score += 1;
        } elseif ($length < 16) {
            $score += 2;
        } else {
            $score += 3;
        }
        
        // 检查是否包含小写字母
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $suggestions[] = '密码应包含小写字母';
        }
        
        // 检查是否包含大写字母
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $suggestions[] = '密码应包含大写字母';
        }
        
        // 检查是否包含数字
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $suggestions[] = '密码应包含数字';
        }
        
        // 检查是否包含特殊字符
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 1;
        } else {
            $suggestions[] = '密码应包含特殊字符';
        }
        
        // 检查是否包含连续字符
        if (preg_match('/(123|abc|qwe|asd|zxc)/i', $password)) {
            $score -= 1;
            $suggestions[] = '密码不应包含连续字符';
        }
        
        // 检查是否包含重复字符
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 1;
            $suggestions[] = '密码不应包含重复字符';
        }
        
        // 评分等级
        $strength = 'weak';
        if ($score >= 4) {
            $strength = 'medium';
        }
        if ($score >= 6) {
            $strength = 'strong';
        }
        
        return [
            'score' => $score,
            'strength' => $strength,
            'suggestions' => $suggestions
        ];
    }
    
    /**
     * 安全地哈希密码
     * @param string $password 原始密码
     * @return string 哈希后的密码
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4次迭代
            'threads' => 2          // 2个线程
        ]);
    }
    
    /**
     * 验证密码
     * @param string $password 原始密码
     * @param string $hash 哈希后的密码
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 检查密码是否需要重新哈希
     * @param string $hash 哈希后的密码
     * @return bool
     */
    public static function passwordNeedsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 2
        ]);
    }
    
    /**
     * 检测请求是否可能是机器人
     * @return bool
     */
    public static function isBotRequest(): bool
    {
        // 获取User-Agent
        $userAgent = Request::header('user-agent', '');
        
        // 检查是否为空或包含常见机器人标识
        if (empty($userAgent) || 
            preg_match('/(bot|crawl|spider|slurp|teoma|archive|track|seo|digest|feedfetcher)/i', $userAgent)) {
            return true;
        }
        
        // 检查请求头是否完整
        $requiredHeaders = ['accept', 'accept-language', 'accept-encoding'];
        foreach ($requiredHeaders as $header) {
            if (empty(Request::header($header))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 记录安全事件
     * @param string $event 事件名称
     * @param array $data 事件数据
     * @return bool
     */
    public static function logSecurityEvent(string $event, array $data = []): bool
    {
        // 添加基本信息
        $data = array_merge($data, [
            'ip' => Request::ip(),
            'user_agent' => Request::header('user-agent'),
            'url' => Request::url(true),
            'method' => Request::method(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // 记录日志
        Log::write('安全事件: ' . $event, 'security');
        Log::record(json_encode($data, JSON_UNESCAPED_UNICODE), 'security');
        
        return true;
    }
}
