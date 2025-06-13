<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserLoginLog;
use think\facade\Request;
use think\facade\Config;
use think\facade\Log;

/**
 * 登录日志服务
 */
class LoginLogService
{
    /**
     * 记录用户登录日志
     * @param int $userId 用户ID
     * @param string $username 用户名
     * @param bool $status 登录状态
     * @param string $remark 备注
     * @return bool
     */
    public static function recordLoginLog(int $userId, string $username, bool $status = true, string $remark = ''): bool
    {
        try {
            // 获取IP地址
            $ipAddress = Request::ip();
            
            // 获取用户代理
            $userAgent = Request::header('user-agent', '');
            
            // 解析设备信息
            $device = self::parseDevice($userAgent);
            
            // 解析位置信息
            $location = self::parseLocation($ipAddress);
            
            // 记录登录日志
            return UserLoginLog::addLog(
                $userId,
                $username,
                $ipAddress,
                $device,
                $location,
                $userAgent,
                $status ? 1 : 0,
                $remark
            );
        } catch (\Exception $e) {
            Log::error('记录登录日志失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 解析设备信息
     * @param string $userAgent 用户代理
     * @return string
     */
    protected static function parseDevice(string $userAgent): string
    {
        // 浏览器检测
        $browser = 'Unknown Browser';
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            if (preg_match('/Edge/i', $userAgent)) {
                $browser = 'Edge';
            } elseif (preg_match('/Edg/i', $userAgent)) {
                $browser = 'Edge';
            } elseif (preg_match('/OPR|Opera/i', $userAgent)) {
                $browser = 'Opera';
            } else {
                $browser = 'Chrome';
            }
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        }
        
        // 操作系统检测
        $os = 'Unknown OS';
        if (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $os = 'MacOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        }
        
        return $browser . ' on ' . $os;
    }
    
    /**
     * 解析位置信息
     * @param string $ip IP地址
     * @return string
     */
    protected static function parseLocation(string $ip): string
    {
        // 默认位置
        $location = '未知位置';
        
        // 如果是本地IP，直接返回
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return '本地访问';
        }
        
        try {
            // 这里可以集成第三方IP地址库或API
            // 例如：高德地图、百度地图、淘宝IP库等
            // 为了演示，这里使用简单的模拟数据
            
            // 模拟IP地址库
            $ipMap = [
                '192.168.' => '中国, 北京市',
                '10.0.' => '中国, 上海市',
                '172.16.' => '中国, 广州市',
                '172.17.' => '中国, 深圳市',
            ];
            
            foreach ($ipMap as $prefix => $loc) {
                if (strpos($ip, $prefix) === 0) {
                    return $loc;
                }
            }
            
            // 实际项目中，可以使用如下方式获取位置信息
            // 1. 使用IP地址库文件（如GeoIP、IP2Region等）
            // 2. 调用第三方API（如高德、百度等）
            // 3. 使用自建的IP地址库
            
            // 示例：调用第三方API（需要自行实现）
            // $location = self::callLocationApi($ip);
            
            return $location;
        } catch (\Exception $e) {
            Log::error('解析IP位置失败: ' . $e->getMessage());
            return '未知位置';
        }
    }
    
    /**
     * 获取用户最近的登录日志
     * @param int $userId 用户ID
     * @param int $limit 限制数量
     * @return array
     */
    public static function getRecentLogs(int $userId, int $limit = 10): array
    {
        return UserLoginLog::getRecentLogs($userId, $limit);
    }
    
    /**
     * 获取用户登录统计信息
     * @param int $userId 用户ID
     * @return array
     */
    public static function getUserLoginStats(int $userId): array
    {
        return UserLoginLog::getUserLoginStats($userId);
    }
}
