<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Log;

/**
 * 内容安全中间件
 * 实现XSS过滤、敏感内容检测等功能
 */
class ContentSecurity
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 获取请求参数
        $params = $request->param();
        
        // 过滤XSS
        $filteredParams = $this->filterXss($params);
        
        // 将过滤后的参数重新绑定到请求对象
        $request->withParam($filteredParams);
        
        // 继续处理请求
        $response = $next($request);
        
        // 添加安全响应头
        return $this->addSecurityHeaders($response);
    }
    
    /**
     * 过滤XSS攻击
     * @param array $params 请求参数
     * @return array 过滤后的参数
     */
    protected function filterXss(array $params): array
    {
        // 递归过滤数组
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->filterXss($value);
            } else if (is_string($value)) {
                // 过滤XSS攻击
                $params[$key] = $this->cleanXss($value);
            }
        }
        
        return $params;
    }
    
    /**
     * 清理XSS攻击
     * @param string $string 需要过滤的字符串
     * @return string 过滤后的字符串
     */
    protected function cleanXss(string $string): string
    {
        // 过滤常见的XSS攻击向量
        $string = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $string);
        $string = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $string);
        $string = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $string);
        $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
        
        // 移除危险的属性
        $string = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $string);
        
        // 移除javascript:, vbscript:, data: 等协议
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*d[\x00-\x20]*a[\x00-\x20]*t[\x00-\x20]*a[\x00-\x20]*:#iu', '$1=$2nodata...', $string);
        
        // 移除命名空间元素
        $string = preg_replace('#</*\w+:\w[^>]*+>#i', '', $string);
        
        // 移除不需要的标签
        do {
            $old_string = $string;
            $string = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $string);
        } while ($old_string !== $string);
        
        return $string;
    }
    
    /**
     * 添加安全响应头
     * @param Response $response 响应对象
     * @return Response
     */
    protected function addSecurityHeaders(Response $response): Response
    {
        // 添加安全响应头
        $response->header([
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'",
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
        ]);
        
        return $response;
    }
}
