<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\Token;
use think\Response;
use think\facade\Lang;
use think\facade\Config;
use think\facade\Log;

/**
 * API接口Token验证中间件
 */
class CheckApiToken
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @param string $type 用户类型 admin-管理员 user-前端用户
     * @return Response
     */
    public function handle($request, \Closure $next, string $type = 'user')
    {
        // 验证token
        $userData = Token::checkApiToken($type);
        
        // 验证失败
        if (!$userData) {
           
            // 这部分逻辑可以简化为直接使用语言包
            $message = Lang::get('token.unauthorized');
            return json([
                'code' => 401,
                'msg' => $message,
                'data' => null
            ]);
        }
        
        // 将用户数据绑定到请求对象
        $request->userData = $userData;
        
        return $next($request);
    }
}