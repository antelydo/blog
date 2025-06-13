<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        $response = parent::render($request, $e);
        
        // 获取请求的Origin
        $origin = $request->header('Origin', '');
        
        // 允许的域名列表
        $allowOrigins = [
            'http://localhost:5173',    // Vite开发服务器
            'http://127.0.0.1:5173',    // Vite开发服务器 (IP地址)
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'https://yourdomain.com',   // 生产环境域名
        ];
        
        // 如果是允许的域名，添加CORS头
        if (in_array($origin, $allowOrigins)) {
            $response->header([
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, Accept-Language,X-Token,uuid',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '1728000',  // 预检请求缓存20天
            ]);
        }
        
        return $response;
    }
}
