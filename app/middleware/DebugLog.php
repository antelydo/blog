<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\Debug;
use think\facade\Config;
use think\facade\Request;

/**
 * 调试日志中间件
 * 用于自动记录控制器调用
 */
class DebugLog
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 检查是否启用调试日志
        if (!Config::get('debug.enabled', false)) {
            return $next($request);
        }
        
        // 检查是否记录控制器调用
        if (!Config::get('debug.record_controller', true)) {
            return $next($request);
        }
        
        // 获取控制器和方法名
        $controller = Request::controller();
        $action = Request::action();
        
        // 获取请求参数
        $params = $request->param();
        
        // 记录开始时间
        $startTime = microtime(true);
        
        // 执行请求
        $response = $next($request);
        
        // 计算执行时间
        $executionTime = round((microtime(true) - $startTime) * 1000);
        
        // 获取响应结果
        $result = null;
        if (method_exists($response, 'getData')) {
            $result = $response->getData();
        } elseif (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            // 尝试解析JSON
            if (is_string($content)) {
                $json = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result = $json;
                } else {
                    $result = $content;
                }
            } else {
                $result = $content;
            }
        }
        
        // 记录调试信息
        Debug::recordController($controller, $action, $params, $result, $executionTime);
        
        return $response;
    }
}
