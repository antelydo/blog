<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\Token;
use think\facade\Db;
use think\facade\Request;
use think\facade\Lang;
use think\facade\Config;
use think\facade\Log;

class Auth extends BaseController
{
    // 在文件开头添加语言包加载
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 管理员登录
     */
    public function adminLogin()
    {
        $username = Request::post('username', '');
        $password = Request::post('password', '');

        // 验证用户名和密码
        $admin = Db::name('admin')
            ->where('username', $username)
            ->find();

        if (!$admin || !password_verify($password, $admin['password'])) {
            // 记录登录失败日志
            if (Config::get('token.enable_log', true)) {
                Log::warning(Lang::get('auth.admin_login_failed'), [
                    'username' => $username,
                    'ip' => Request::ip(),
                    'user_agent' => Request::header('user-agent')
                ]);
            }

            return json([
                'code' => 400,
                'msg' => Lang::get('auth.login_failed'),
                'data' => null
            ]);
        }

        // 设置用户语言偏好
        if (!empty($admin['lang'])) {
            Lang::setLangSet($admin['lang']);
        }

        // 生成token
        $token = Token::generateToken($admin, 'admin');

        return json([
            'code' => 200,
            'msg' => Lang::get('auth.login_success'),
            'data' => [
                'token' => $token,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nickname' => $admin['nickname'] ?? '',
                    'lang' => Lang::getLangSet(),
                    'last_login_time' => $admin['last_login_time'] ?? 0,
                    'last_login_ip' => $admin['last_login_ip'] ?? ''
                ]
            ]
        ]);
    }

    /**
     * 用户登录
     */
    public function userLogin()
    {
        $username = Request::post('username', '');
        $password = Request::post('password', '');

        // 验证用户名和密码
        $user = Db::name('user')
            ->where('username', $username)
            ->find();

        if (!$user || !password_verify($password, $user['password'])) {
            return json([
                'code' => 400,
                'msg' => Lang::get('auth.login_failed'),
                'data' => null
            ]);
        }

        // 设置用户语言偏好
        if (!empty($user['lang'])) {
            Lang::setLangSet($user['lang']);
        }

        // 生成token
        $token = Token::generateToken($user, 'user');

        return json([
            'code' => 200,
            'msg' => Lang::get('auth.login_success'),
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'nickname' => $user['nickname'] ?? '',
                    'lang' => Lang::getLangSet()
                ]
            ]
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = Token::getRequestToken();
        $type = Request::param('type', 'user');

        // 获取用户信息，用于记录日志
        $userData = Token::verifyToken($token, $type, false);

        if (Token::clearToken($token, $type)) {
            // 记录登出日志
            if ($userData && Config::get('token.enable_activity_log', true)) {
                // Use a public method instead
                Token::recordActivity(
                    $userData['id'],
                    $type,
                    'logout',
                    Lang::get('auth.user_logout'),
                    ['ip' => Request::ip()]
                );
            }

            return json([
                'code' => 200,
                'msg' => Lang::get('auth.logout_success'),
                'data' => null
            ]);
        }

        return json([
            'code' => 400,
            'msg' => Lang::get('auth.logout_failed'),
            'data' => null
        ]);
    }

    /**
     * 刷新Token
     */
    public function refreshToken()
    {
        $token = Token::getRequestToken();
        $type = Request::param('type', 'user');

        $newToken = Token::refreshToken($token, $type);

        if ($newToken) {
            return json([
                'code' => 200,
                'msg' => Lang::get('auth.refresh_success'),
                'data' => [
                    'token' => $newToken
                ]
            ]);
        }

        return json([
            'code' => 400,
            'msg' => Lang::get('auth.refresh_failed'),
            'data' => null
        ]);
    }

    /**
     * 设置语言
     */
    public function setLanguage()
    {
        $token = Token::getRequestToken();
        $type = Request::param('type', 'user');
        $lang = Request::param('lang', 'zh-cn');

        // 验证语言是否在允许列表中
        $allowList = Config::get('token.lang.allow_list', ['zh-cn', 'en']);
        if (!in_array($lang, $allowList)) {
            return json([
                'code' => 400,
                'msg' => Lang::get('auth.invalid_language'),
                'data' => null
            ]);
        }

        if (Token::setUserLanguage($token, $lang, $type)) {
            return json([
                'code' => 200,
                'msg' => Lang::get('auth.language_set_success'),
                'data' => [
                    'lang' => $lang
                ]
            ]);
        }

        return json([
            'code' => 400,
            'msg' => Lang::get('auth.language_set_failed'),
            'data' => null
        ]);
    }

    /**
     * 强制用户下线
     * 需要管理员权限
     */
    public function forceLogout()
    {
        $userId = Request::param('user_id', 0, 'intval');
        $type = Request::param('type', 'user');

        // 验证当前用户是否有权限
        $adminData = Request::param('userData');
        if (empty($adminData) || $adminData['role'] != 'admin') {
            return json([
                'code' => 403,
                'msg' => Lang::get('auth.permission_denied'),
                'data' => null
            ]);
        }

        if (Token::forceLogout($userId, $type)) {
            // 记录操作日志
            if (Config::get('token.enable_activity_log', true)) {
                  Token::recordActivity(
                    $adminData['id'],
                    $type,
                    'force_logout',
                    Lang::get('auth.force_user_logout'),
                    [
                        'target_user_id' => $userId,
                        'target_user_type' => $type,
                        'ip' => Request::ip()
                    ]
                );
            }

            return json([
                'code' => 200,
                'msg' => Lang::get('auth.force_logout_success'),
                'data' => null
            ]);
        }

        return json([
            'code' => 400,
            'msg' => Lang::get('auth.force_logout_failed'),
            'data' => null
        ]);
    }

    /**
     * 获取用户活动日志
     * 需要管理员权限
     */
    public function getActivityLogs()
    {
        $userId = Request::param('user_id', 0, 'intval');
        $type = Request::param('type', 'user');
        $page = Request::param('page', 1, 'intval');
        $limit = Request::param('limit', 10, 'intval');

        // 验证当前用户是否有权限
        $adminData = Request::param('userData');
        if (empty($adminData) || $adminData['role'] != 'admin') {
            return json([
                'code' => 403,
                'msg' => Lang::get('auth.permission_denied'),
                'data' => null
            ]);
        }

        $logs = Db::name('user_activity_log')
            ->where('user_id', $userId)
            ->where('user_type', $type)
            ->order('create_time', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $total = Db::name('user_activity_log')
            ->where('user_id', $userId)
            ->where('user_type', $type)
            ->count();

        return json([
            'code' => 200,
            'msg' => Lang::get('auth.activity_logs_success'),
            'data' => [
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }

    /**
     * 清除所有Token
     * 仅超级管理员可用
     */
    public function clearAllTokens()
    {
        // 验证当前用户是否有权限
        $adminData = Request::param('userData');
        if (empty($adminData) || $adminData['role'] != 'super_admin') {
            return json([
                'code' => 403,
                'msg' => Lang::get('auth.permission_denied'),
                'data' => null
            ]);
        }

        $type = Request::param('type', '');

        if (Token::clearAllTokens($type)) {
            // 记录操作日志
            if (Config::get('token.enable_activity_log', true)) {
                Token::recordActivity(
                    $adminData['id'],
                    'admin',
                    'clear_all_tokens',
                    Lang::get('auth.clear_all_tokens_action'),
                    [
                        'type' => $type,
                        'ip' => Request::ip()
                    ]
                );
            }

            return json([
                'code' => 200,
                'msg' => Lang::get('auth.clear_all_tokens_success'),
                'data' => null
            ]);
        }

        return json([
            'code' => 400,
            'msg' => Lang::get('auth.clear_all_tokens_failed'),
            'data' => null
        ]);
    }
}