<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\Token;
use think\facade\Lang;

class CheckAdminLogin
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
        
        if (!$token) {
            return json(['code' => 401, 'msg' => Lang::get('admin.please_login_first')]);
        }
        
        // 验证token
        $admin = Token::verifyToken($token, 'admin');
        
        if (!$admin) {
            return json(['code' => 401, 'msg' => Lang::get('admin.please_login_first')]);
        }
        
        // 将管理员信息传递给控制器
        $request->userId = $admin['id'];
        $request->user = $admin;
        
        return $next($request);
    }
}