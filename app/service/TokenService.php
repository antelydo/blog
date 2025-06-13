<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;
use think\facade\Config;

/**
 * Token服务类
 */
class TokenService
{
    /**
     * 生成管理员Token
     * @param int $adminId 管理员ID
     * @return string
     */
    public static function generateAdminToken(int $adminId): string
    {
        // 生成随机字符串
        $randomStr = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        
        // 生成token
        $token = md5($adminId . $randomStr . time());
        
        // 设置token过期时间（默认24小时）
        $expireTime = Config::get('token.admin_expire', 86400);
        
        // 将token存入缓存
        Cache::set('admin_token_' . $token, $adminId, $expireTime);
        
        return $token;
    }
    
    /**
     * 从Token中获取管理员ID
     * @param string $token Token字符串
     * @return int
     * @throws \Exception
     */
    public static function getAdminIdFromToken(string $token): int
    {
        // 从缓存中获取管理员ID
        $adminId = Cache::get('admin_token_' . $token);
        
        // 如果token不存在或已过期
        if (!$adminId) {
            throw new \Exception('Token无效或已过期');
        }
        
        return (int)$adminId;
    }
    
    /**
     * 验证管理员Token
     * @param string $token Token字符串
     * @return bool
     */
    public static function verifyAdminToken(string $token): bool
    {
        try {
            self::getAdminIdFromToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 清除管理员Token
     * @param string $token Token字符串
     * @return bool
     */
    public static function clearAdminToken(string $token): bool
    {
        return Cache::delete('admin_token_' . $token);
    }
} 