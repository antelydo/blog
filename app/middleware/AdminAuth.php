<?php
declare(strict_types=1);

namespace app\middleware;

use app\model\Admin;
use think\facade\Lang;
use think\Response;

/**
 * 管理员认证中间件
 */
class AdminAuth
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取请求头中的token
        $token = $request->header('Authorization');
        
        // 如果没有token，返回未授权错误
        if (!$token) {
            return json([
                'code' => 401,
                'msg' => Lang::get('system.login_required'),
                'data' => null
            ]);
        }
        
        // 验证token
        try {
            // 从token中获取管理员ID
            $adminId = \app\service\TokenService::getAdminIdFromToken($token);
            
            // 查询管理员信息
            $admin = Admin::where('id', $adminId)->find();
            
            // 管理员不存在或已禁用
            if (!$admin || $admin->status != 1) {
                return json([
                    'code' => 403,
                    'msg' => Lang::get('system.permission_denied'),
                    'data' => null
                ]);
            }
            
            // 将管理员信息添加到请求中
            $request->admin = $admin;
            
            return $next($request);
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('管理员认证失败: ' . $e->getMessage());
            
            return json([
                'code' => 401,
                'msg' => Lang::get('system.auth_failed'),
                'data' => null
            ]);
        }
    }
} 