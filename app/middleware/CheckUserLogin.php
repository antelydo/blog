<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\Token;
use think\facade\Lang;

class CheckUserLogin
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return \think\Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取token
        $token = $request->header('Authorization');
            // 添加调试日志
            trace('token: ' . $token, 'info');
        if (!$token) {
            return json(['code' => 401, 'msg' => Lang::get('user.please_login_first')]);
        }
        
        // 验证token
        $user = Token::verifyToken($token, 'user');
        
        if (!$user) {
            return json(['code' => 401, 'msg' => Lang::get('user.please_login_first')]);
        }
        
        // 将用户信息传递给控制器
        $request->userId = $user['id'];
        $request->user = $user;
        
        return $next($request);
    }
}