<?php
declare (strict_types = 1);

namespace app\common;

use think\facade\Log;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;

/**
 * 性能监控工具类
 */
class Performance
{
    /**
     * 性能计时器
     * @var array
     */
    protected static $timers = [];
    
    /**
     * 内存使用记录
     * @var array
     */
    protected static $memoryUsage = [];
    
    /**
     * 数据库查询次数
     * @var int
     */
    protected static $dbQueries = 0;
    
    /**
     * 缓存命中次数
     * @var int
     */
    protected static $cacheHits = 0;
    
    /**
     * 缓存未命中次数
     * @var int
     */
    protected static $cacheMisses = 0;
    
    /**
     * 开始计时
     * @param string $name 计时器名称
     * @return void
     */
    public static function startTimer(string $name): void
    {
        self::$timers[$name] = [
            'start' => microtime(true),
            'end' => null,
            'duration' => null,
        ];
        
        // 记录内存使用
        self::$memoryUsage[$name] = [
            'start' => memory_get_usage(),
            'end' => null,
            'usage' => null,
        ];
    }
    
    /**
     * 停止计时
     * @param string $name 计时器名称
     * @return float|null 耗时（秒）
     */
    public static function stopTimer(string $name): ?float
    {
        if (!isset(self::$timers[$name])) {
            return null;
        }
        
        self::$timers[$name]['end'] = microtime(true);
        self::$timers[$name]['duration'] = self::$timers[$name]['end'] - self::$timers[$name]['start'];
        
        // 记录内存使用
        self::$memoryUsage[$name]['end'] = memory_get_usage();
        self::$memoryUsage[$name]['usage'] = self::$memoryUsage[$name]['end'] - self::$memoryUsage[$name]['start'];
        
        return self::$timers[$name]['duration'];
    }
    
    /**
     * 获取计时结果
     * @param string $name 计时器名称
     * @return float|null 耗时（秒）
     */
    public static function getTimerDuration(string $name): ?float
    {
        if (!isset(self::$timers[$name]) || self::$timers[$name]['duration'] === null) {
            return null;
        }
        
        return self::$timers[$name]['duration'];
    }
    
    /**
     * 获取所有计时结果
     * @return array
     */
    public static function getAllTimers(): array
    {
        return self::$timers;
    }
    
    /**
     * 记录数据库查询
     * @param int $count 查询次数
     * @return void
     */
    public static function recordDbQueries(int $count = 1): void
    {
        self::$dbQueries += $count;
    }
    
    /**
     * 记录缓存命中
     * @param int $count 命中次数
     * @return void
     */
    public static function recordCacheHit(int $count = 1): void
    {
        self::$cacheHits += $count;
    }
    
    /**
     * 记录缓存未命中
     * @param int $count 未命中次数
     * @return void
     */
    public static function recordCacheMiss(int $count = 1): void
    {
        self::$cacheMisses += $count;
    }
    
    /**
     * 获取性能报告
     * @return array
     */
    public static function getReport(): array
    {
        // 计算总执行时间
        $totalTime = 0;
        foreach (self::$timers as $timer) {
            if ($timer['duration'] !== null) {
                $totalTime += $timer['duration'];
            }
        }
        
        // 计算总内存使用
        $totalMemory = 0;
        foreach (self::$memoryUsage as $memory) {
            if ($memory['usage'] !== null) {
                $totalMemory += $memory['usage'];
            }
        }
        
        // 计算缓存命中率
        $cacheTotal = self::$cacheHits + self::$cacheMisses;
        $cacheHitRate = $cacheTotal > 0 ? (self::$cacheHits / $cacheTotal) * 100 : 0;
        
        return [
            'request' => [
                'url' => Request::url(true),
                'method' => Request::method(),
                'ip' => Request::ip(),
                'time' => date('Y-m-d H:i:s'),
            ],
            'execution' => [
                'total_time' => $totalTime,
                'memory_usage' => $totalMemory,
                'peak_memory' => memory_get_peak_usage(),
            ],
            'database' => [
                'queries' => self::$dbQueries,
            ],
            'cache' => [
                'hits' => self::$cacheHits,
                'misses' => self::$cacheMisses,
                'hit_rate' => $cacheHitRate,
            ],
            'timers' => self::$timers,
            'memory' => self::$memoryUsage,
        ];
    }
    
    /**
     * 记录性能报告
     * @param bool $logToFile 是否记录到文件
     * @return array 性能报告
     */
    public static function logReport(bool $logToFile = true): array
    {
        $report = self::getReport();
        
        if ($logToFile) {
            Log::write('性能报告: ' . json_encode($report, JSON_UNESCAPED_UNICODE), 'performance');
        }
        
        return $report;
    }
    
    /**
     * 监控慢查询
     * @param float $duration 查询耗时（秒）
     * @param string $sql SQL语句
     * @param float $threshold 阈值（秒）
     * @return void
     */
    public static function monitorSlowQuery(float $duration, string $sql, float $threshold = 1.0): void
    {
        if ($duration >= $threshold) {
            Log::write('慢查询: ' . $sql . ' [耗时: ' . $duration . '秒]', 'slow_query');
            
            // 记录慢查询统计
            $cacheKey = 'slow_query_' . md5($sql);
            $count = Cache::get($cacheKey, 0);
            Cache::set($cacheKey, $count + 1, 86400); // 保存一天
        }
    }
    
    /**
     * 监控高内存使用
     * @param int $memory 内存使用量（字节）
     * @param string $operation 操作名称
     * @param int $threshold 阈值（字节）
     * @return void
     */
    public static function monitorHighMemory(int $memory, string $operation, int $threshold = 10485760): void
    {
        if ($memory >= $threshold) {
            Log::write('高内存使用: ' . $operation . ' [内存: ' . self::formatBytes($memory) . ']', 'high_memory');
        }
    }
    
    /**
     * 格式化字节数
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
