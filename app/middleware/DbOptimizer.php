<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Db;
use think\facade\Log;
use think\facade\Config;
use app\common\Performance;

/**
 * 数据库查询优化中间件
 */
class DbOptimizer
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 开始性能监控
        Performance::startTimer('request_total');
        
        // 记录初始数据库查询次数
        $initialQueries = Db::getQueryTimes();
        
        // 处理请求
        $response = $next($request);
        
        // 记录最终数据库查询次数
        $finalQueries = Db::getQueryTimes();
        $queryCount = $finalQueries - $initialQueries;
        
        // 记录数据库查询次数
        Performance::recordDbQueries($queryCount);
        
        // 停止性能监控
        $duration = Performance::stopTimer('request_total');
        
        // 检查是否需要记录性能报告
        if ($this->shouldLogPerformance($request, $duration, $queryCount)) {
            $report = Performance::logReport();
            
            // 添加性能报告到响应头（仅在调试模式下）
            if (Config::get('app.debug', false)) {
                $response->header([
                    'X-Performance-Time' => round($duration * 1000, 2) . 'ms',
                    'X-Performance-DB-Queries' => $queryCount,
                    'X-Performance-Memory' => Performance::formatBytes(memory_get_peak_usage()),
                ]);
            }
        }
        
        return $response;
    }
    
    /**
     * 判断是否需要记录性能报告
     * @param Request $request 请求对象
     * @param float|null $duration 请求耗时
     * @param int $queryCount 查询次数
     * @return bool
     */
    protected function shouldLogPerformance(Request $request, ?float $duration, int $queryCount): bool
    {
        // 静态资源不记录
        $extension = pathinfo($request->url(), PATHINFO_EXTENSION);
        if (in_array($extension, ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'])) {
            return false;
        }
        
        // 检查是否超过阈值
        $timeThreshold = Config::get('app.performance_time_threshold', 1.0); // 默认1秒
        $queryThreshold = Config::get('app.performance_query_threshold', 20); // 默认20次查询
        
        // 如果超过阈值，记录性能报告
        if (($duration !== null && $duration >= $timeThreshold) || $queryCount >= $queryThreshold) {
            return true;
        }
        
        // 随机采样，记录一定比例的请求
        $sampleRate = Config::get('app.performance_sample_rate', 0.01); // 默认1%
        return mt_rand(1, 100) <= ($sampleRate * 100);
    }
}
