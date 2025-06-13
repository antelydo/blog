<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Cache;
use think\facade\Config;
use app\common\Performance;

/**
 * 缓存优化中间件
 */
class CacheOptimizer
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 检查是否启用页面缓存
        if ($this->shouldCache($request)) {
            // 生成缓存键
            $cacheKey = $this->generateCacheKey($request);
            
            // 尝试从缓存获取响应
            $cachedResponse = Cache::get($cacheKey);
            
            if ($cachedResponse !== null) {
                // 缓存命中
                Performance::recordCacheHit();
                
                // 返回缓存的响应
                return $this->createResponseFromCache($cachedResponse);
            }
            
            // 缓存未命中
            Performance::recordCacheMiss();
            
            // 处理请求
            $response = $next($request);
            
            // 缓存响应
            $this->cacheResponse($cacheKey, $response);
            
            return $response;
        }
        
        // 不缓存，直接处理请求
        return $next($request);
    }
    
    /**
     * 判断是否应该缓存请求
     * @param Request $request 请求对象
     * @return bool
     */
    protected function shouldCache(Request $request): bool
    {
        // 只缓存GET请求
        if (!$request->isGet()) {
            return false;
        }
        
        // 检查是否启用页面缓存
        if (!Config::get('app.page_cache_enabled', false)) {
            return false;
        }
        
        // 检查是否在排除列表中
        $excludePaths = Config::get('app.page_cache_exclude', []);
        $requestPath = $request->pathinfo();
        
        foreach ($excludePaths as $path) {
            if (strpos($requestPath, $path) === 0) {
                return false;
            }
        }
        
        // 检查是否需要认证
        if ($request->header('Authorization') || $request->header('Cookie')) {
            // 有认证信息的请求通常不应该缓存
            // 但可以根据实际需求调整
            return false;
        }
        
        return true;
    }
    
    /**
     * 生成缓存键
     * @param Request $request 请求对象
     * @return string
     */
    protected function generateCacheKey(Request $request): string
    {
        // 基于URL和查询参数生成缓存键
        $url = $request->url(true);
        $params = $request->param();
        ksort($params); // 排序参数，确保相同参数不同顺序生成相同的键
        
        // 添加语言信息（如果有）
        $lang = $request->header('Accept-Language', '');
        
        return 'page_cache_' . md5($url . json_encode($params) . $lang);
    }
    
    /**
     * 缓存响应
     * @param string $key 缓存键
     * @param Response $response 响应对象
     * @return void
     */
    protected function cacheResponse(string $key, Response $response): void
    {
        // 只缓存成功的响应
        if ($response->getCode() !== 200) {
            return;
        }
        
        // 获取缓存时间
        $cacheTime = Config::get('app.page_cache_time', 3600); // 默认1小时
        
        // 缓存响应数据
        $data = [
            'content' => $response->getContent(),
            'header' => $response->getHeader(),
            'code' => $response->getCode(),
            'timestamp' => time(),
        ];
        
        Cache::set($key, $data, $cacheTime);
    }
    
    /**
     * 从缓存创建响应对象
     * @param array $data 缓存的响应数据
     * @return Response
     */
    protected function createResponseFromCache(array $data): Response
    {
        $response = Response::create($data['content'], 'html', $data['code']);
        
        // 设置响应头
        foreach ($data['header'] as $name => $value) {
            $response->header($name, $value);
        }
        
        // 添加缓存标记
        $response->header('X-Cache', 'HIT');
        $response->header('X-Cache-Time', date('Y-m-d H:i:s', $data['timestamp']));
        
        return $response;
    }
}
