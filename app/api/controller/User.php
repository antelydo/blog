<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Validate;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;
use app\common\Token;
use app\service\LoginLogService;

/**
 * 用户控制器
 * @route('user')
 */
class User extends BaseController
{
    /**
     * 用户类型
     * @var string
     */
    protected string $userType;

    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        parent::initialize();
    }

    /**
     * 用户登录
     * @route('login', 'post')
     */
    public function login(): Json
    {
        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'username' => 'require',
            'password' => 'require'
        ],
            [
                'username.require' => Lang::get('user.username_required'),
                'password.require' => Lang::get('user.password_required')
            ]
        );

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }


        // 查询用户
        $user = Db::name('user')
            ->where('username', $data['username'])
            ->find();


        if (!$user) {
            // 记录登录失败日志（用户不存在）
            // 注意：由于用户不存在，我们使用 0 作为用户ID
            LoginLogService::recordLoginLog(
                0,
                $data['username'],
                false,
                Lang::get('user.user_not_exist')
            );
            return $this->error(Lang::get('user.user_not_exist'));
        }

        // 验证密码
        if (!password_verify($data['password'], $user['password'])) {
            // 记录登录失败日志
            LoginLogService::recordLoginLog(
                $user['id'],
                $user['username'],
                false,
                Lang::get('user.password_incorrect')
            );
            return $this->error(Lang::get('user.password_incorrect'));
        }

        // 验证状态
        if ($user['status'] != 1) {
            // 记录登录失败日志
            LoginLogService::recordLoginLog(
                $user['id'],
                $user['username'],
                false,
                Lang::get('user.account_disabled')
            );
            return $this->error(Lang::get('user.account_disabled'));
        }

        // 使用Token类生成token
        $token = Token::generateToken($user, 'user');

        $out_time = Token::getConfig('expire_time', 7200);
        // 返回用户信息
        $userInfo = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'token' => $token,
            'token_expire' => time() + $out_time
        ];

        // 记录用户活动
        $this->userId = $user['id'];
        $this->user = $user;
        $this->recordActivity($this->userId, $this->userType, 'login', Lang::get('user.user_login'));

        // 记录登录成功日志
        LoginLogService::recordLoginLog(
            $user['id'],
            $user['username'],
            true,
            Lang::get('user.login_successful')
        );

        return $this->success($userInfo, Lang::get('user.login_successful'));
    }

    /**
     * 用户退出
     * @route('logout', 'post')
     */
    public function logout(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $token = Request::header('Authorization');

        if ($token) {
            // 使用Token类清除token
            Token::clearToken($token, 'user');
        }

        // 记录用户活动
        $this->recordActivity($this->userId, $this->userType, 'logout', Lang::get('user.user_logout'));

        return $this->success([], Lang::get('user.logout_successful'));
    }

    /**
     * 用户注册
     * @route('register', 'post')
     */
    public function register(): Json
    {
        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'username' => 'require|alphaNum|length:4,20|unique:user',
            'password' => 'require|length:6,20',
            'confirm_password' => 'require|confirm:password',
            'email' => 'email|unique:user',
            'mobile' => 'mobile|unique:user'
        ],
            [
                'username.alphaNum' => Lang::get('user.username_format_invalid'),
                'username.unique' => Lang::get('user.username_already_exists'),
                'email.email' => Lang::get('user.email_format_invalid'),
                'email.unique' => Lang::get('user.email_already_exists'),
                'mobile.unique' => Lang::get('user.mobile_already_exists'),
                'confirm_password.confirm' => Lang::get('user.password_mismatch')
            ]
        );

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 检查是否允许注册
        $registerStatus = Db::name('website_config')
            ->where('name', 'user_register_status')
            ->value('value');

        if ($registerStatus != 1) {
            return $this->error(Lang::get('user.registration_closed'));
        }

        // 用户默认状态
        $defaultStatus = Db::name('website_config')
            ->where('name', 'user_default_status')
            ->value('value');

        // 组装数据
        $user = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'nickname' => $data['nickname'] ?? $data['username'],
            'email' => $data['email'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'status' => $defaultStatus ?? 1,
            'register_ip' => Request::ip(),
            'register_time' => time(),
            'create_time' => time(),
            'update_time' => time()
        ];

        // 插入数据
        $userId = Db::name('user')->insertGetId($user);

        if (!$userId) {
            return $this->error(Lang::get('user.registration_failed'));
        }

        // 记录用户活动
        $this->userId = $userId;
        $this->recordActivity($this->userId, $this->userType, 'register', Lang::get('user.user_registration'));

       // 修改这里，使用 Lang::get() 方法
       return $this->success([], Lang::get('user.registration_successful'));
    }

    /**
     * 获取用户信息
     * @route('info', 'get')
     */
    public function info(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $user = Db::name('user')
            ->field('id,username,nickname,real_name,last_active_time,remark,status,avatar,email,mobile,gender,birthday,vip_level,vip_expire_time,balance,points,last_login_time,register_time,last_login_ip,login_count,register_ip,register_time,introduction ')
            ->where('id', $this->userId)
            ->find();

        if (!$user) {

            return $this->error(Lang::get('user.user_does_not_exist'));
        }

        return $this->success($user, Lang::get('user.get_info_successful'));
    }

    /**
     * 修改用户信息
     * @route('update', 'post')
     */
    public function update(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'nickname' => 'length:2,50',
            'email' => 'email',
            'mobile' => 'mobile',
            'gender' => 'in:0,1,2',
            'birthday' => 'date',
            'introduction' => 'max:500',
            'real_name' => 'max:20',
            'remark'=>'max:200'
        ],
            [
                'nickname.length' => Lang::get('user.nickname_length_invalid'),
                'email.email' => Lang::get('user.email_format_invalid'),
                'mobile.mobile' => Lang::get('user.mobile_format_invalid'),
                'gender.in' => Lang::get('user.gender_invalid'),
                'birthday.date' => Lang::get('user.birthday_format_invalid'),
                'introduction.max' => Lang::get('user.introduction_length_invalid'),
                'real_name.max' => Lang::get('user.real_name_length_invalid'),
                'remark.max' => Lang::get('user.remark_length_invalid')
            ]
        );

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 组装数据
        $update = [];

        if (isset($data['nickname'])) {
            $update['nickname'] = $data['nickname'];
        }

        if (isset($data['email'])) {
            // 检查邮箱是否已被使用
            $exists = Db::name('user')
                ->where('email', $data['email'])
                ->where('id', '<>', $this->userId)
                ->find();

            if ($exists) {
                return $this->error(Lang::get('user.email_already_in_use'));
            }

            $update['email'] = $data['email'];
        }

        if (isset($data['mobile'])) {
            // 检查手机号是否已被使用
            $exists = Db::name('user')
                ->where('mobile', $data['mobile'])
                ->where('id', '<>', $this->userId)
                ->find();

            if ($exists) {
                return $this->error(Lang::get('user.mobile_already_in_use'));
            }

            $update['mobile'] = $data['mobile'];
        }

        if (isset($data['gender'])) {
            $update['gender'] = $data['gender'];
        }

        if (isset($data['birthday'])) {
            $update['birthday'] = $data['birthday'];
        }

        if (isset($data['introduction'])) {
            $update['introduction'] = $data['introduction'];
        }
        if (isset($data['remark'])) {
            $update['remark'] = $data['remark'];
        }

        if (isset($data['real_name'])) {
            $update['real_name'] = $data['real_name'];
        }

        if (empty($update)) {
            // 没有需要更新的数据
            return $this->error(Lang::get('user.no_data_to_update'));
        }

        $update['update_time'] = time();

        // 更新数据
        $result = Db::name('user')->where('id', $this->userId)->update($update);

        if ($result) {
            // 记录用户活动
            $this->recordActivity($this->userId, $this->userType, 'update_info', Lang::get('user.update_personal_information'));
            return $this->success([], Lang::get('user.update_successful'));
        } else {
            return $this->error(msg: Lang::get('user.update_failed'));
        }
    }

    /**
     * 修改密码
     * @route('changePassword', 'post')
     */
    public function changePassword(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'old_password' => 'require',
            'new_password' => 'require|length:6,20',
            'confirm_password' => 'require|confirm:new_password'
        ],[
            'confirm_password.confirm' => Lang::get('password_mismatch'),
            'new_password.length' => Lang::get('password_length_invalid'),
            'new_password.require' => Lang::get('password_required'),
            'old_password.require' => Lang::get('old_password_required'),
            'old_password.length' => Lang::get('old_password_length_invalid'),
            'old_password.confirm' => Lang::get('old_password_mismatch'),
            'old_password.unique' => Lang::get('old_password_already_exists'),
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 验证旧密码
        if (!password_verify($data['old_password'], $this->user['password'])) {
            return $this->error(Lang::get('user.original_password_incorrect'));
        }

        // 更新密码
        $result = Db::name('user')->where('id', $this->userId)->update([
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'update_time' => time()
        ]);

        if ($result) {
            // 记录用户活动
            $this->recordActivity($this->userId, $this->userType, 'change_password', Lang::get('user.change_password'));

            return $this->success([], Lang::get('user.password_changed_successfully'));
        } else {
            return $this->error(Lang::get('user.password_change_failed'));

        }
    }

    /**
     * 上传头像
     * @route('uploadAvatar', 'post')
     */
    public function uploadAvatar(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $file = Request::file('avatar');

        if (!$file) {
            return $this->error(Lang::get('user.please_select_file'));
        }

        // 使用正确的验证方法
        $validate = Validate::rule([
            'avatar' => [
                'file',
                'fileSize' => 2 * 1024 * 1024, // 2MB
                'fileExt' => 'jpg,jpeg,png,gif',
                'fileMime' => 'image/jpeg,image/png,image/gif'
            ]
        ]);

        if (!$validate->check(['avatar' => $file])) {
            return $this->error($validate->getError());
        }

        // 上传文件
        $savename = \think\facade\Filesystem::disk('public')->putFile('avatar', $file);

        if (!$savename) {
            return $this->error(Lang::get('user.upload_failed'));
        }

        $avatar =Request::domain().'/storage/' . $savename;
       // $avatar = '/storage/user/avatar/' . $savename;

        // 更新头像
        $result = Db::name('user')->where('id', $this->userId)->update([
            'avatar' => $avatar,
            'update_time' => time()
        ]);

        if ($result) {
            // 记录用户活动
            $this->recordActivity($this->userId, $this->userType, 'upload_avatar', Lang::get('user.upload_avatar'));
            return $this->success(['avatar' => $avatar], Lang::get('user.upload_successful'));
        } else {
            return $this->error(Lang::get('user.upload_failed'));
        }
    }

    /**
     * 获取用户消息
     * @route('messages', 'get')
     */
    public function messages(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);
        $type = Request::param('type', '');
        $isRead = Request::param('is_read/d', -1);

        $where = [
            ['user_id', '=', $this->userId]
        ];

        if ($type) {
            $where[] = ['type', '=', $type];
        }

        if ($isRead != -1) {
            $where[] = ['is_read', '=', $isRead];
        }

        $messages = Db::name('user_message')
            ->where($where)
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);

        return $this->success($messages, Lang::get('user.get_messages_successful'));
    }

    /**
     * 标记消息为已读
     * @route('readMessage', 'post')
     */
    public function readMessage(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::param('id/d', 0);

        if ($id) {
            // 标记单条消息
            $result = Db::name('user_message')
                ->where('id', $id)
                ->where('user_id', $this->userId)
                ->update([
                    'is_read' => 1,
                    'read_time' => time()
                ]);
        } else {
            // 标记所有消息
            $result = Db::name('user_message')
                ->where('user_id', $this->userId)
                ->where('is_read', 0)
                ->update([
                    'is_read' => 1,
                    'read_time' => time()
                ]);
        }

        return $this->success(['affected' => $result], Lang::get('user.mark_as_read_successful'));
    }

    /**
     * 获取用户VIP信息
     * @route('vip_info', 'get')
     */
    public function vipInfo(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $user = Db::name('user')
            ->field('id,username,nickname,avatar,email,mobile,gender,birthday,vip_level,vip_expire_time,balance,points,last_login_time,register_time')
            ->where('id', $this->userId)
            ->find();

        if (!$user) {
            return $this->error(Lang::get('user.user_does_not_exist'));
        }

        return $this->success($user, Lang::get('user.get_info_successful'));
    }

    /**
     * 获取用户订单
     * @route('orders', 'get')
     */
    public function orders(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $user = Db::name('user')
            ->field('id,username,nickname,avatar,email,mobile,gender,birthday,vip_level,vip_expire_time,balance,points,last_login_time,register_time')
            ->where('id', $this->userId)
            ->find();

        if (!$user) {
            return $this->error(Lang::get('user.user_does_not_exist'));
        }

        return $this->success($user, Lang::get('user.get_info_successful'));
    }

    /**
     * 创建订单
     * @route('createOrder', 'post')
     */
    public function createOrder(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'username' => 'require',
            'password' => 'require'
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 查询用户
        $user = Db::name('user')
            ->where('username', $data['username'])
            ->find();

        if (!$user) {
            return $this->error(Lang::get('user.user_not_exist'));
        }

        // 验证密码
        if (!password_verify($data['password'], $user['password'])) {
            return $this->error(Lang::get('user.password_incorrect'));
        }

        // 验证状态
        if ($user['status'] != 1) {
            return $this->error(Lang::get('user.account_disabled'));
        }

        // 使用Token类生成token
        $token = Token::generateToken($user, 'user');

        // 返回用户信息
        $userInfo = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'token' => $token,
            'token_expire' => time() + Token::getConfig('expire_time', 7200)
        ];

        // 记录用户活动
        $this->userId = $user['id'];
        $this->user = $user;
        $this->recordActivity($this->userId, $this->userType, 'login', Lang::get('user.user_login'));

        return $this->success($userInfo, Lang::get('user.login_successful'));
    }

    /**
     * 取消订单
     * @route('cancelOrder', 'post')
     */
    public function cancelOrder(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $token = Request::header('Authorization');

        if ($token) {
            // 使用Token类清除token
            Token::clearToken($token, 'user');
        }

        // 记录用户活动
        $this->recordActivity($this->userId, $this->userType, 'logout', Lang::get('user.user_logout'));

        return $this->success([], Lang::get('user.logout_successful'));
    }

    /**
     * 获取登录日志
     * @route('loginLogs', 'get')
     */
    public function loginLogs(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);

        // 查询登录日志
        $query = Db::name('user_login_log')
            ->where('user_id', $this->userId)
            ->order('create_time', 'desc');

        // 使用通用分页方法
        $result = $this->getPageList($query, $page, $limit);

        return $this->success($result, Lang::get('user.get_login_logs_successful'));
    }

    /**
     * 获取登录统计信息
     * @route('loginStats', 'get')
     */
    public function loginStats(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        // 获取登录统计信息
        $stats = LoginLogService::getUserLoginStats($this->userId);

        return $this->success($stats, Lang::get('user.get_login_stats_successful'));
    }


    /**
     * 获取用户工具收藏列表
     * @route('toolFavoritesList', 'get')
     */
    public function toolFavoritesList(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);


        // 查询用户工具收藏列表 关联当前工具
        $query = Db::name('ai_tool_favorite')
            ->alias('f')
            ->join('ai_ai_tool t', 't.id = f.tool_id')
            ->where('f.user_id', $this->userId)
            ->field('f.*, t.name as title, t.logo, t.short_description as description')
            ->order('f.create_time', 'desc');

        // 使用通用分页方法
        $result = $this->getPageList($query, $page, $limit);
        if($result['list']){
            foreach($result['list'] as $k =>$v){
                $result['list'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        return $this->success($result, Lang::get('aiTool.get_favorite_list_success'));
    }


    /**
     * 获取用户工具点赞列表
     * @route('toolLikesList', 'get')
     */
    public function toolLikesList(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);

        // 查询用户工具点赞列表 关联当前工具
        $query = Db::name('ai_tool_like')
            ->alias('l')
            ->join('ai_ai_tool t', 't.id = l.tool_id')
            ->where('l.user_id', $this->userId)
            ->field('l.*, t.name as title, t.logo, t.short_description as description')
            ->order('l.create_time', 'desc');


        // 使用通用分页方法
        $result = $this->getPageList($query, $page, $limit);

        if($result['list']){
            foreach($result['list'] as $k =>$v){
                $result['list'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        return $this->success($result, Lang::get('aiTool.get_like_list_success'));
    }



    /**
     * 获取用户工具评价列表
     * @route('toolCommentsList', 'get')
     */
    public function toolCommentsList(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);

        // 查询用户工具评价列表 关联当前工具
        $query = Db::name('ai_tool_comment')
            ->alias('c')
            ->join('ai_ai_tool t', 't.id = c.tool_id')
            ->where('c.user_id', $this->userId)
            ->field('c.*, t.name as title, t.logo, t.short_description as description')
            ->order('c.create_time', 'desc');

        // 使用通用分页方法
        $result = $this->getPageList($query, $page, $limit);
        if($result['list']){
            foreach($result['list'] as $k =>$v){
                $result['list'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        return $this->success($result, Lang::get('aiTool.get_comment_list_success'));
    }


    /**
     * 获取用户工具浏览列表
     * @route('toolViewList', 'get')
     */
    public function toolViewList(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);

        // 查询用户工具浏览列表 关联当前工具
        $query = Db::name('ai_tool_visit_log')
            ->alias('v')
            ->join('ai_ai_tool t', 't.id = v.tool_id')
            ->where('v.user_id', $this->userId)
            ->field('v.*, t.name as title, t.logo, t.short_description as description')
            ->order('v.create_time', 'desc');

        // 使用通用分页方法
        $result = $this->getPageList($query, $page, $limit);
        if($result['list']){
            foreach($result['list'] as $k =>$v){
                //检查当前工具用户是否已经收藏
                $favorite = Db::name('ai_tool_favorite')
                    ->where('tool_id', $v['tool_id'])
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $result['list'][$k]['is_favorite'] = (bool)$favorite;
                $result['list'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        return $this->success($result, Lang::get('aiTool.get_visit_log_list_success'));

    }




}