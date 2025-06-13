<?php
declare (strict_types=1);

namespace app\api\controller;
use app\BaseController;
use think\facade\Lang;
use think\facade\Validate;
use think\validate\ValidateRule as Rule;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;
use app\common\Token;

/**
 * 管理员控制器
 * @route('admin')
 */
class Admin  extends BaseController
{
    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();

    }

    /**
     * 管理员登录
     * @route('login', 'post')
     */
    public function login(): Json
    {
        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'username' => 'require',
            'password' => 'require'
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 查询管理员
        $admin = Db::name('admin')
            ->where('username', $data['username'])
            ->find();


        if (!$admin) {
            return $this->error(Lang::get('admin.admin_not_exist'));
        }

        // 验证密码
        if (!password_verify($data['password'], $admin['password'])) {
            return $this->error(Lang::get('admin.password_incorrect'));
        }

        // 验证状态
        if ($admin['status'] != 1) {
            return $this->error(Lang::get('admin.account_disabled'));
        }

        // 使用Token类生成token
        $token = Token::generateToken($admin, 'admin');

        // 更新登录信息
        $update = [
            'last_login_time' => time(),
            'last_login_ip' => Request::ip(),
            'login_count' => $admin['login_count'] + 1,
            'token' => $token,  // 修复这里，将 token 改为 $token
            'token_expire' => time() + Token::getConfig('expire_time', 86400 * 7) // 使用配置的过期时间
        ];

        Db::name('admin')->where('id', $admin['id'])->update($update);

        // 返回管理员信息
        $adminInfo = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'avatar' => $admin['avatar'],
            'role' => $admin['role'],
            'token' => $update['token'],
            'token_expire' => $update['token_expire']
        ];

        // 记录管理员活动
        $this->userId = $admin['id'];
        $this->user = $admin;
        $this->recordActivity($this->userId, 'admin', 'login', Lang::get('admin.admin_login'));

        return $this->success($adminInfo, Lang::get('admin.login_successful'));
    }

    /**
     * 管理员退出
     * @route('logout', 'post')
     */
    public function logout(): Json
    {
//        if (!$this->isLogin()) {
//            return $this->needLogin();
//        }

        // 清除token
        $token = Request::header('Authorization');

        if ($token) {
            // 使用Token类清除token
            Token::clearToken($token, 'admin');
        }

        // 记录管理员活动
        $this->recordActivity($this->userId, 'admin', 'logout', Lang::get('admin.admin_logout'));

        return $this->success([], Lang::get('admin.logout_successful'));
    }

    /**
     * 获取管理员信息
     * @route('info', 'get')
     */
    public function info(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $admin = Db::name('admin')
            ->field('id,username,nickname,avatar,email,mobile,bio,role,status,token_expire,lang,last_login_ip,last_login_time,login_count,last_active_time,remark,create_time,update_time')
            ->where('id', $this->userId)
            ->find();

        if (!$admin) {
            return $this->error(Lang::get('admin.admin_not_exist'));
        }

        return $this->success($admin, Lang::get('admin.get_information_successful'));
    }

    /**
     * 修改管理员信息
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
            'bio' => 'max:500'
        ]);

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
            $exists = Db::name('admin')
                ->where('email', $data['email'])
                ->where('id', '<>', $this->userId)
                ->find();

            if ($exists) {
                return $this->error(Lang::get('admin.email_already_used'));
            }

            $update['email'] = $data['email'];
        }

        if (isset($data['mobile'])) {
            // 检查手机号是否已被使用
            $exists = Db::name('admin')
                ->where('mobile', $data['mobile'])
                ->where('id', '<>', $this->userId)
                ->find();

            if ($exists) {
                return $this->error(Lang::get('admin.mobile_already_used'));
            }

            $update['mobile'] = $data['mobile'];
        }

        // 添加对bio字段的处理
        if (isset($data['bio'])) {
            $update['bio'] = $data['bio'];
        }

        if (empty($update)) {
            return $this->error(Lang::get('admin.no_data_update'));
        }

        $update['update_time'] = time();

        // 更新数据
        $result = Db::name('admin')->where('id', $this->userId)->update($update);

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($this->userId, 'admin', 'update_info', Lang::get('admin.update_personal_information'));
            return $this->success([], Lang::get('admin.update_successful'));
        } else {
            return $this->error('admin.update_failed');
        }
    }

    /**
     * 修改密码
     * @route('change_password', 'post')
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
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 验证旧密码
        if (!password_verify($data['old_password'], $this->user['password'])) {
            return $this->error(Lang::get('admin.original_password_incorrect'));
        }

        // 更新密码
        $result = Db::name('admin')->where('id', $this->userId)->update([
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'update_time' => time()
        ]);

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($this->userId, 'admin', 'change_password', Lang::get('admin.change_password'));

            return $this->success([], Lang::get('admin.password_changed_successfully'));
        } else {

            return $this->error(Lang::get('admin.password_changed_failed'));
        }
    }

    /**
     * 上传头像
     * @route('upload_avatar', 'post')
     */
    public function uploadAvatar(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $file = Request::file('avatar');

        if (!$file) {
            return $this->error(Lang::get('admin.please_select_file'));
        }

        // 验证文件，使用正确的方法 Validate::rule 而不是 fileRule
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
        $savename = \think\facade\Filesystem::disk('public')->putFile('admin/avatar', $file);

        if (!$savename) {
            return $this->error(Lang::get('admin.upload_failed'));
        }

        $avatar =Request::domain().'/storage/' . $savename;

        // 更新头像
        $result = Db::name('admin')->where('id', $this->userId)->update([
            'avatar' => $avatar,
            'update_time' => time()
        ]);

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($this->userId, 'admin', 'upload_avatar', Lang::get('admin.upload_avatar'));

            return $this->success(['avatar' => $avatar], Lang::get('admin.upload_successful'));
        } else {
            return $this->error(Lang::get('admin.upload_failed'));
        }
    }

     /**
     * 上传图片
     * @route('upload', 'post')
     */
    public function upload(){
        $file = Request::file('file');

        $image = Request::file('image');
        $link_logo = Request::file('link_logo');
        if (!$file && !$image && !$link_logo) {
            return $this->error(Lang::get('admin.please_select_file'));
        }

        if($file){
            $roo_path='admin/web_image';
        }else if($image){
            $roo_path='admin/cover';
        }else if($link_logo){
            $roo_path='admin/link_logo';
        }
        $file =$file?? $image?? $link_logo;

        // 验证文件
        $validate = Validate::Rule([
            'file' => [
                'fileSize' => 2 * 1024 * 1024, // 2MB
                'fileExt' => 'jpg,jpeg,png,gif',
                'fileMime' => 'image/jpeg,image/png,image/gif'
            ]
        ]);

        if (!$validate->check(['webimage' => $file])) {
            return $this->error($validate->getError());
        }


        // 上传文件
        $savename = \think\facade\Filesystem::disk('public')->putFile($roo_path, $file);

        if (!$savename) {
            return $this->error(Lang::get('admin.upload_failed'));
        }

        $path =Request::domain().'/storage/' . $savename;

        if ($path) {
            // 记录管理员活动
            $this->recordActivity($this->userId, 'admin', 'upload', Lang::get('admin.upload_image'));

            return $this->success(['url' => $path], Lang::get('admin.upload_successful'));
        } else {

            return $this->error(Lang::get('admin.upload_failed'));
        }
    }

    /**
     * 管理员列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        // 检查权限
        if ($this->user['role'] != 'super_admin') {
            return $this->forbidden();
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);
        $keyword = Request::param('keyword', '');
        $status = Request::param('status/d', -1);

        $where = [];

        if ($keyword) {
            $where[] = ['username|nickname|email|mobile', 'like', "%{$keyword}%"];
        }

        if ($status != -1) {
            $where[] = ['status', '=', $status];
        }

        $admins = Db::name('admin')
            ->field('id,username,nickname,avatar,email,mobile,role,status,last_login_time,last_login_ip,login_count,create_time')
            ->where($where)
            ->order('id', 'asc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);


        return $this->success($admins, Lang::get('admin.get_information_successful'));
    }

    /**
     * 添加管理员
     * @route('add', 'post')
     */
    public function add(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => '未登录']);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => '没有权限']);
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'username' => 'require|alphaNum|length:4,20|unique:admin',
            'password' => 'require|length:6,20',
            'nickname' => 'require|length:2,50',
            'email' => 'email|unique:admin',
            'mobile' => 'mobile|unique:admin',
            'role' => 'require|in:admin,super_admin',
            'status' => 'require|in:0,1'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        // 组装数据
        $newAdmin = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'nickname' => $data['nickname'],
            'email' => $data['email'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'role' => $data['role'],
            'status' => $data['status'],
            'create_time' => time(),
            'update_time' => time()
        ];

        // 插入数据
        $newAdminId = Db::name('admin')->insertGetId($newAdmin);

        if (!$newAdminId) {
            return json(['code' => 0, 'msg' => '添加失败']);
        }

        // 记录管理员活动
        $this->recordActivity($adminId, 'admin', 'add_admin', Lang::get('admin.add_admin_action'), [
            'admin_id' => $newAdminId,
            'username' => $data['username']
        ]);

        return json(['code' => 200, 'msg' => Lang::get('admin.add_successful')]);
    }

    /**
     * 编辑管理员
     * @route('edit', 'post')
     */
    public function edit(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'id' => 'require|number',
            'nickname' => 'require|length:2,50',
            'email' => 'email',
            'mobile' => 'mobile',
            'role' => 'require|in:admin,super_admin',
            'status' => 'require|in:0,1'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        // 检查管理员是否存在
        $targetAdmin = Db::name('admin')->where('id', $data['id'])->find();

        if (!$targetAdmin) {
            return json(['code' => 0, 'msg' => Lang::get('admin.admin_not_exist')]);
        }


        // 不能修改自己的角色
        if ($data['id'] == $adminId && $data['role'] != $admin['role']) {
            return json(['code' => 0, 'msg' => Lang::get('admin.cannot_change_own_role')]);
        }

        // 检查邮箱是否已被使用
        if (!empty($data['email']) && $data['email'] != $targetAdmin['email']) {
            $exists = Db::name('admin')
                ->where('email', $data['email'])
                ->where('id', '<>', $data['id'])
                ->find();

            if ($exists) {
                return json(['code' => 0, 'msg' => Lang::get('admin.email_already_used')]);
            }
        }

        // 检查手机号是否已被使用
        if (!empty($data['mobile']) && $data['mobile'] != $targetAdmin['mobile']) {
            $exists = Db::name('admin')
                ->where('mobile', $data['mobile'])
                ->where('id', '<>', $data['id'])
                ->find();

            if ($exists) {
                return json(['code' => 0, 'msg' => Lang::get('admin.mobile_already_used')]);
            }
        }

        // 组装数据
        $update = [
            'nickname' => $data['nickname'],
            'email' => $data['email'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'role' => $data['role'],
            'status' => $data['status'],
            'update_time' => time()
        ];

        // 如果提供了新密码，则更新密码
        if (!empty($data['password'])) {
            $update['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // 更新数据
        $result = Db::name('admin')->where('id', $data['id'])->update($update);

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($adminId, 'admin', 'edit_admin', Lang::get('admin.edit_admin_action'), [
                'admin_id' => $data['id'],
                'username' => $targetAdmin['username']
            ]);
            return json(['code' => 200, 'msg' => Lang::get('admin.update_successful')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('admin.update_failed')]);
        }
    }

    /**
     * 删除管理员
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }

        $id = Request::param('id/d', 0);

        if (!$id) {
            return json(['code' => 0, 'msg' => Lang::get('admin.param_error')]);
        }

        // 不能删除自己
        if ($id == $adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.cannot_delete_self')]);
        }

        // 检查管理员是否存在
        $targetAdmin = Db::name('admin')->where('id', $id)->find();

        if (!$targetAdmin) {
            return json(['code' => 0, 'msg' => Lang::get('admin.admin_not_exist')]);
        }

        // 删除管理员
        $result = Db::name('admin')->where('id', $id)->delete();

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($adminId, 'admin', 'delete_admin', Lang::get('admin.delete_admin_action'), [
                'admin_id' => $id,
                'username' => $targetAdmin['username']
            ]);
            return json(['code' => 200, 'msg' => Lang::get('admin.delete_successful')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('admin.delete_failed')]);
        }
    }

    /**
     * 获取用户列表
     * @route('user_list', 'get')
     */
    public function userList(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        $page = intval(input('page', 1));
        $limit = intval(input('limit',10));
        $keyword = input('keyword', '');
        $status = input('status', -1);

        $where = [];
        if ($keyword) {
            $where[] = ['username|nickname|email|mobile', 'like', "%{$keyword}%"];
        }

        if ($status > -1) {
            $where[] = ['status', '=', intval($status)];
        }

        $query = Db::name('user')
            ->field('id,username,nickname,real_name,avatar,role,email,mobile,gender,lang,birthday,status,vip_level,vip_expire_time,balance,points,last_login_time,last_login_ip,register_time,register_ip,login_count,create_time');

        // 使用统一的分页查询方法
        $result = $this->getPageList($query,$page,$limit,$where,['id desc']);

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $result]);
    }

    /**
     * 编辑用户
     * @route('editUser', 'post')
     */
    public function editUser(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        $data = Request::post();

        // 验证数据
        $validate = Validate::rule([
            'id' => 'require|number',
            'nickname' => 'require|length:2,50',
            'email' => 'email',
            'mobile' => 'mobile',
            'status' => 'require|in:0,1',
            'vip_level' => 'number|between:0,9',
            'vip_expire_time' => 'number',
            'balance' => 'float|egt:0',
            'points' => 'number|egt:0'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        // 检查用户是否存在
        $user = Db::name('user')->where('id', $data['id'])->find();

        if (!$user) {
            return json(['code' => 0, 'msg' => Lang::get('admin.user_not_exist')]);
        }

        // 检查邮箱是否已被使用
        if (!empty($data['email']) && $data['email'] != $user['email']) {
            $exists = Db::name('user')
                ->where('email', $data['email'])
                ->where('id', '<>', $data['id'])
                ->find();

            if ($exists) {
                return json(['code' => 0, 'msg' => Lang::get('admin.email_already_used')]);
            }
        }

        // 检查手机号是否已被使用
        if (!empty($data['mobile']) && $data['mobile'] != $user['mobile']) {
            $exists = Db::name('user')
                ->where('mobile', $data['mobile'])
                ->where('id', '<>', $data['id'])
                ->find();

            if ($exists) {
                return json(['code' => 0, 'msg' => Lang::get('admin.mobile_already_used')]);
            }
        }

        // 组装数据
        $update = [
            'nickname' => $data['nickname']??$user['nickname'],
            'avatar'=> $data['avatar']??$user['avatar'],
            'email' => $data['email']??$user['email'],
            'mobile' => $data['mobile'] ??$user['mobile'],
            'status' => $data['status']??$user['status'],
            'real_name' => $data['real_name']??$user['real_name'],
            'gender' => $data['gender']??$user['gender'],
            'birthday' => $data['birthday']??$user['birthday'],
            'lang' => $data['lang']??$user['lang'],
            'remark' => $data['remark']??$user['remark'],
            'role' => $data['role']??$user['role'],
            'update_time' => time()
        ];

        // 可选字段
        if (isset($data['vip_level'])) {
            $update['vip_level'] = $data['vip_level'];
        }

        if (isset($data['vip_expire_time'])) {
            $update['vip_expire_time'] = $data['vip_expire_time'];
        }

        if (isset($data['balance'])) {
            $update['balance'] = $data['balance'];
        }

        if (isset($data['points'])) {
            $update['points'] = $data['points'];
        }

        // 如果提供了新密码，则更新密码
        if (!empty($data['password'])) {
            $update['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // 更新数据
        $result = Db::name('user')->where('id', $data['id'])->update($update);

        if ($result) {
            // 记录管理员活动
            $this->recordActivity($adminId, 'admin', 'edit_user', Lang::get('admin.edit_user_action'), [
                'user_id' => $data['id'],
                'username' => $user['username']
            ]);
            return json(['code' => 200, 'msg' => Lang::get('admin.update_successful')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('admin.update_failed')]);
        }
    }


     /**
     * 修改用户状态
     * @route('updateUserStatus', 'post')
     */
    public function updateUserStatus(){

        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        $id = Request::post('id', 0);
        $status = Request::post('status', -1);
        //Log::info('status' . json_encode($status));
        if ($id <= 0) {
            return $this->error(Lang::get('user.invalid_id'));
        }
        if ($status < 0) {
            return $this->error(Lang::get('user.status_error'));
        }
        $user = Db::name('user')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 0, 'msg' => Lang::get('admin.user_not_exist')]);
        }else{
              // 更新数据
             $result = Db::name('user')->where('id', $id)->update([
                'id' =>$id,
                'status' => $status
             ]);
            if ($result) {
                // 记录管理员活动
                $this->recordActivity($adminId,'admin', 'edit_user', Lang::get('admin.edit_user_action'),
                 [
                    'id' =>$id,
                    'status' => $status
                ]);
                return json(['code' => 200, 'msg' => Lang::get('admin.update_successful')]);
            } else {
                return json(['code' => 0, 'msg' => Lang::get('admin.update_failed')]);
            }
        }

    }


     /**
    /**
     * 用户详细
     * @route('userDetail', 'post')
     */
    public function userDetail(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::post('id', 0);
        if ($id <= 0) {
            return $this->error(Lang::get('user.invalid_id'));
        }

        // 查询用户信息
        $user = Db::name('user')
            ->field('id,username,nickname,real_name,avatar,email,mobile,gender,lang,birthday,status,vip_level,vip_expire_time,balance,points,last_login_time,last_login_ip,register_time,register_ip,login_count,create_time')
            ->where('id', $id)
            ->find();

        if (!$user) {
            return $this->error(Lang::get('user.user_not_found'));
        }

        // 记录管理员活动
        $this->recordActivity($this->userId, 'admin', 'userDetail', Lang::get('admin.get_successful'));

        return $this->success($user, Lang::get('admin.get_successful'));
    }

    /**
     * 获取网站配置
     * @route('getConfig', 'post')
     */
    public function getConfig(): Json
    {
        // $adminId = $this->getAdminId();

        // if (!$adminId) {
        //     return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        // }

        $group = Request::param('group', '');

        $where = [];

        if ($group) {
            $where[] = ['group', '=', $group];
        }

        $configs = Db::name('website_config')
            ->field('name, value') // 只选择 name 和 value 字段
            ->where($where)
            ->order('group,sort')
            ->select()
            ->toArray();

        // // 处理配置选项 (不再需要，因为只查询了 name 和 value)
        // foreach ($configs as &$config) {
        //     if (isset($config['options']) && $config['options']) { // 检查 options 是否存在且不为空
        //         $decodedOptions = json_decode($config['options'], true);
        //         // 检查 JSON 解码是否成功
        //         if (json_last_error() === JSON_ERROR_NONE) {
        //             $config['options'] = $decodedOptions;
        //         } else {
        //             // 可以选择记录错误或将 options 设为默认值
        //             $config['options'] = [];
        //             // Log::error('Failed to decode JSON options for config: ' . $config['name']);
        //         }
        //     } else {
        //         $config['options'] = []; // 如果 options 不存在或为空，设置默认值
        //     }
        // }

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $configs]);
    }

    /**
     * 更新网站配置
     * @route('updateConfig', 'post')
     */
    public function updateConfig(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }

        $data = Request::post();

        if (empty($data)) {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_update_data')]);
        }

        $time = time();
        $success = 0;

        // 批量更新配置
        foreach ($data as $name => $value) {
            $result = Db::name('website_config')
                ->where('name', $name)
                ->update([
                    'value' => $value,
                    'update_time' => $time
                ]);

            if ($result) {
                $success++;
            }
        }

        if ($success > 0) {
            // 记录管理员活动
            $this->recordActivity($admin['id'], 'admin', 'update_config', Lang::get('admin.update_config_action'), [
                'count' => $success
            ]);
            return json(['code' => 200, 'msg' => Lang::get('admin.update_successful'), 'data' => ['count' => $success]]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('admin.update_failed')]);
        }
    }

     /**
     * 删除用户
     * @route('deleteUser', 'post')
     */
    public function deleteUser(){
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }
        $id = Request::post('id', 0);

        if ($id <= 0) {
            return $this->error(Lang::get('user.invalid_id'));
        }

        $user = Db::name('user')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 0, 'msg' => Lang::get('admin.user_not_exist')]);
        }else{
              // 更新数据
             $result = Db::name('user')->where('id', $id)->delete();
            if ($result) {
                // 记录管理员活动
                $this->recordActivity($adminId,'admin', 'delete_user', Lang::get('admin.delete_user'),
                 [
                    'id' =>$id,
                    'username' => $user['username']
                ]);
                return json(['code' => 200, 'msg' => Lang::get('admin.delete_successful')]);
            } else {
                return json(['code' => 0, 'msg' => Lang::get('admin.delete_failed')]);
            }

        }
    }

    /**
     * 获取用户活动日志
     * @route('activityLog', 'get')
     */
    public function activityLog(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);
        $userId = Request::param('user_id/d', 0);
        $userType = Request::param('user_type', '');
        $action = Request::param('action', '');
        $startTime = Request::param('start_time', 0);
        $endTime = Request::param('end_time', 0);
        $username = Request::param('username', '');
        $ip = Request::param('ip', '');

        $where = [];

        if ($userId) {
            $where[] = ['user_id', '=', $userId];
        }
        if ($username) {
            $where_1[] = ['username', 'like', "%{$username}%"];
            $admin_ids = Db::name('admin')->where($where_1)->column('id');
            $user_ids = Db::name('user')->where($where_1)->column('id');
            $ids_list =array_merge($admin_ids,$user_ids);

            if ($ids_list) {
                $where[] = ['user_id', 'in', $ids_list];
            }else{
                $where[] = ['user_id', 'in', 0];
            }
        }


        if ($ip) {
            $where[] = ['ip', '=', $ip];
        }

        if ($userId) {
            $where[] = ['user_id', '=', $userId];
        }

        if ($userType) {
            $where[] = ['user_type', '=', $userType];
        }

        if ($action) {
            $where[] = ['action', '=', $action];
        }

        if ($startTime) {
            $startTimes = strtotime($startTime);
            $where[] = ['create_time', '>=', $startTimes];
        }

        if ($endTime) {
            $endTimes  = strtotime($endTime);
            $where[] = ['create_time', '<=', $endTimes];
        }

        $logs = Db::name('user_activity_log')
            ->where($where)
            ->order('create_time','desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ])->toArray();


           if($logs['data']){
            foreach($logs['data'] as $k=>$v){
                if($v['user_type']=='admin'){
                    $logs['data'][$k]['username'] =Db::name('admin')->where('id',$v['user_id'])->value('username');

                }else if($v['user_type']='user'){
                    $logs['data'][$k]['username'] =Db::name('user')->where('id',$v['user_id'])->value('username');

                }else{
                    $logs['data'][$k]['username']='匿名';
                }
            }
           }

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $logs]);
    }

    /**
     * 获取系统统计数据
     * @route('statistics', 'post')
     */
    public function statistics(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 用户总数
        $userCount = Db::name('user')->count();

        // 今日新增用户
        $todayStart = strtotime(date('Y-m-d'));
        $todayUserCount = Db::name('user')
            ->where('create_time', '>=', $todayStart)
            ->count();

        // 活跃用户数（30天内有登录）
        $activeTime = time() - 86400 * 30;
        $activeUserCount = Db::name('user')
            ->where('last_login_time', '>=', $activeTime)
            ->count();

        // VIP用户数
        $vipUserCount = Db::name('user')
            ->where('vip_level', '>', 0)
            ->where('vip_expire_time', '>', time())
            ->count();

        // 系统收入统计
        $totalIncome = Db::name('user_order')
            ->where('status', 'completed')
            ->sum('amount');

        // 今日收入
        $todayIncome = Db::name('user_order')
            ->where('status', 'completed')
            ->where('pay_time', '>=', $todayStart)
            ->sum('amount');

        // 本月收入
        $monthStart = strtotime(date('Y-m-01'));
        $monthIncome = Db::name('user_order')
            ->where('status', 'completed')
            ->where('pay_time', '>=', $monthStart)
            ->sum('amount');

        // 订单总数
        $orderCount = Db::name('user_order')->count();

        // 已完成订单数
        $completedOrderCount = Db::name('user_order')
            ->where('status', 'completed')
            ->count();

        // 今日订单数
        $todayOrderCount = Db::name('user_order')
            ->where('create_time', '>=', $todayStart)
            ->count();

        // 系统资源统计
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsage = round($diskUsed / $diskTotal * 100, 2);

        // 服务器信息
        $serverInfo = [
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? '',
            'mysql_version' => Db::query('SELECT VERSION() as version')[0]['version'] ?? '',
        ];

        $data = [
            'user' => [
                'total' => $userCount,
                'today' => $todayUserCount,
                'active' => $activeUserCount,
                'vip' => $vipUserCount
            ],
            'income' => [
                'total' => $totalIncome,
                'today' => $todayIncome,
                'month' => $monthIncome
            ],
            'order' => [
                'total' => $orderCount,
                'completed' => $completedOrderCount,
                'today' => $todayOrderCount
            ],
            'system' => [
                'disk_total' => $diskTotal,
                'disk_free' => $diskFree,
                'disk_used' => $diskUsed,
                'disk_usage' => $diskUsage,
                'server' => $serverInfo
            ]
        ];

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $data]);
    }

    /**
     * 获取用户增长趋势数据
     * @route('user_growth', 'get')
     */
    public function userGrowth(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        $days = Request::param('days/d', 30);
        $type = Request::param('type', '');

        // 如果是获取统计数据
        if ($type === 'statistics') {
            // 获取总用户数
            $totalUsers = Db::name('user')->count();

            // 获取活跃用户数（7天内有登录的用户）
            $activeTime = time() - 86400 * 7;
            $activeUsers = Db::name('user')
                ->where('last_login_time', '>=', $activeTime)
                ->count();

            // 计算活跃率
            $activeRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0;

            // 获取今日新增用户数
            $todayStart = strtotime(date('Y-m-d'));
            $todayNewUsers = Db::name('user')
                ->where('create_time', '>=', $todayStart)
                ->count();

            // 计算用户增长率（与上月相比）
            $thisMonth = strtotime(date('Y-m-01'));
            $lastMonth = strtotime('-1 month', $thisMonth);

            $thisMonthUsers = Db::name('user')
                ->where('create_time', '>=', $lastMonth)
                ->where('create_time', '<', $thisMonth)
                ->count();

            $lastMonthUsers = Db::name('user')
                ->where('create_time', '>=', strtotime('-2 month', $thisMonth))
                ->where('create_time', '<', $lastMonth)
                ->count();

            $userGrowth = $lastMonthUsers > 0 ?
                round((($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 2) :
                ($thisMonthUsers > 0 ? 100 : 0);

            // 计算今日新增用户增长率（与昨日相比）
            $yesterdayStart = $todayStart - 86400;
            $yesterdayNewUsers = Db::name('user')
                ->where('create_time', '>=', $yesterdayStart)
                ->where('create_time', '<', $todayStart)
                ->count();

            $newUserGrowth = $yesterdayNewUsers > 0 ?
                round((($todayNewUsers - $yesterdayNewUsers) / $yesterdayNewUsers) * 100, 2) :
                ($todayNewUsers > 0 ? 100 : 0);

            return json([
                'code' => 200,
                'msg' => Lang::get('admin.get_successful'),
                'data' => [
                    'totalUsers' => $totalUsers,
                    'activeUsers' => $activeUsers,
                    'activeRate' => $activeRate . '%',
                    'todayNewUsers' => $todayNewUsers,
                    'userGrowth' => $userGrowth,
                    'newUserGrowth' => $newUserGrowth
                ]
            ]);
        }

        // 获取用户增长趋势数据
        $endDate = strtotime(date('Y-m-d'));
        $startDate = $endDate - ($days * 86400);

        // 按天统计用户增长
        $growthData = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $nextDate = $currentDate + 86400;
            $dateStr = date('Y-m-d', $currentDate);

            $count = Db::name('user')
                ->where('create_time', '>=', $currentDate)
                ->where('create_time', '<', $nextDate)
                ->count();

            $growthData[$dateStr] = $count;
            $currentDate = $nextDate;
        }

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $growthData]);
    }

    /**
     * 获取系统访问统计数据
     * @route('access_stats', 'get')
     */
    public function accessStats(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        $days = Request::param('days/d', 30);
        $endDate = strtotime(date('Y-m-d'));
        $startDate = $endDate - ($days * 86400);

        // 按天统计访问量
        $accessData = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $nextDate = $currentDate + 86400;
            $dateStr = date('Y-m-d', $currentDate);

            $count = Db::name('user_activity_log')
                ->where('action', 'login')
                ->where('create_time', '>=', $currentDate)
                ->where('create_time', '<', $nextDate)
                ->count();

            $accessData[$dateStr] = $count;
            $currentDate = $nextDate;
        }

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => $accessData]);
    }

    /**
     * 清除缓存
     * @route('clear_cache', 'post')
     */
    public function clearCache(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }

        $type = Request::param('type', 'all');

        try {
            switch ($type) {
                case 'data':
                    // 清除数据缓存
                    \think\facade\Cache::clear();
                    break;
                case 'template':
                    // 清除模板缓存
                    $this->clearTemplateCache();
                    break;
                default:
                    // 清除所有缓存
                    \think\facade\Cache::clear();
                    $this->clearTemplateCache();
                    break;
            }

            // 记录管理员活动
            $this->recordActivity($admin['id'], 'admin', 'clear_cache', Lang::get('admin.clear_cache_action'), [
                'type' => $type
            ]);

            return json(['code' => 200, 'msg' => Lang::get('admin.clear_cache_successful')]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => Lang::get('admin.clear_cache_failed') . '：' . $e->getMessage()]);
        }
    }

    /**
     * 清除模板缓存
     */
    private function clearTemplateCache(): bool
    {
        $runtimePath = app()->getRuntimePath();
        $templateCachePath = $runtimePath . 'temp/';

        if (is_dir($templateCachePath)) {
            $files = scandir($templateCachePath);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    @unlink($templateCachePath . $file);
                }
            }
        }

        return true;
    }

    /**
     * 获取系统日志
     * @route('systemLog', 'get')
     */
    public function systemLog(): Json
    {
        $adminId = $this->getAdminId();

        if (!$adminId) {
            return json(['code' => 0, 'msg' => Lang::get('admin.not_logged_in')]);
        }

        // 检查权限
        $admin = Db::name('admin')->where('id', $adminId)->find();

        if ($admin['role'] != 'super_admin') {
            return json(['code' => 0, 'msg' => Lang::get('admin.no_permission')]);
        }

        $type = Request::param('type', 'error');
        $date = Request::param('date', date('Y-m-d'));

        $runtimePath = app()->getRuntimePath();
        $logPath = $runtimePath . 'log/' . $date . '.log';

        if (!file_exists($logPath)) {
            return json(['code' => 0, 'msg' => Lang::get('admin.log_file_not_exist')]);
        }

        $content = file_get_contents($logPath);

        return json(['code' => 200, 'msg' => Lang::get('admin.get_successful'), 'data' => [
            'content' => $content
        ]]);
    }


    /**
     * 创建Token
     */
    protected function createToken(int $adminId): string
    {
        return md5($adminId . time() . uniqid());
    }

    /**
     * 根据Token获取管理员ID
     */
    private function getAdminIdByToken(string $token): int
    {
        $admin = Db::name('admin')
            ->where('token', $token)
            ->where('token_expire', '>', time())
            ->find();

        return $admin ? $admin['id'] : 0;
    }


    /**
     * 获取当前登录管理员ID
     */
    private function getAdminId(): int
    {
        return $this->userId;
    }


}