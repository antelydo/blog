<?php
/**
 * 跨越 中间件
 */
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class AllowCrossDomain
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 获取当前请求的域名
        $origin = $request->header('Origin', '');

        // 允许的域名列表
        $allowOrigins = [
            'http://localhost:5175',    // Vite开发服务器
            'http://127.0.0.1:5175',
            'http://localhost:5174',    // Vite开发服务器
            'http://127.0.0.1:5174',
            'http://localhost:5173',    // Vite开发服务器
            'http://127.0.0.1:5173',    // Vite开发服务器 (IP地址)
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'http://localhost',         // 本地开发
            'http://127.0.0.1',         // 本地开发IP
            'https://sbozl.com',   // 生产环境域名
            'https://gwuzz.com',   // 生产环境域名
            'https://www.sbozl.com',   // 生产环境域名
            'https://www.gwuzz.com',   // 生产环境域名
        ];

        // 开发环境下允许所有来源
        if (env('APP_DEBUG', false)) {
            $allowOrigin = $origin ?: '*';
            return $this->setCorsHeaders($next($request), $allowOrigin);
        }

        // 设置允许的域名
        $allowOrigin = '';
        if (in_array($origin, $allowOrigins)) {
            $allowOrigin = $origin;
        } else {
            // 如果不在白名单内，可以设置为*或指定域名
            $allowOrigin = '*';
        }

        // 对于OPTIONS请求直接返回200，并设置CORS头
        if ($request->method(true) == 'OPTIONS') {
            $response = Response::create()->code(200);
            $response->header([
                'Access-Control-Allow-Origin' => $allowOrigin,
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, Accept-Language, X-Token, token,uuid,X-Visitor-ID,tool_uuid',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '1728000',  // 预检请求缓存20天
                'Content-Type' => 'text/plain charset=UTF-8',
                'Content-Length' => '0'
            ]);
            return $response;
        }

        // 正常请求的处理
        $response = $next($request);

        // 设置CORS响应头
        return $this->setCorsHeaders($response, $allowOrigin);

    }

    /**
     * 设置CORS响应头
     * @param Response $response 响应对象
     * @param string $origin 允许的来源
     * @return Response
     */
    protected function setCorsHeaders(Response $response, string $origin): Response
    {
        $response->header([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, Accept-Language, X-Token, token,uuid',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '1728000',  // 预检请求缓存20天
        ]);

        return $response;
    }

}