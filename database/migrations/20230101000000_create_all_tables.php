<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAllTables extends Migrator
{
    public function change()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');

        // 创建管理员表
        if (!$this->hasTable($prefix.'admin')) {
            $table = $this->table('admin', ['engine' => 'InnoDB', 'comment' => '管理员表']);
            $table->addColumn('username', 'string', ['limit' => 50, 'comment' => '用户名'])
                  ->addColumn('password', 'string', ['limit' => 255, 'comment' => '密码'])
                  ->addColumn('nickname', 'string', ['limit' => 50, 'null' => true, 'comment' => '昵称'])
                  ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
                  ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
                  ->addColumn('mobile', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
                  ->addColumn('role', 'string', ['limit' => 20, 'default' => 'admin', 'comment' => '角色'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                  ->addColumn('token', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Token'])
                  ->addColumn('token_expire', 'integer', ['null' => true, 'default' => 0, 'comment' => 'Token过期时间'])
                  ->addColumn('lang', 'string', ['limit' => 10, 'null' => true, 'default' => 'zh-cn', 'comment' => '语言偏好'])
                  ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
                  ->addColumn('last_login_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '最后登录时间'])
                  ->addColumn('login_count', 'integer', ['null' => true, 'default' => 0, 'comment' => '登录次数'])
                  ->addColumn('last_active_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '最后活跃时间'])
                  ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注'])
                  ->addColumn('create_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '更新时间'])
                  ->addIndex('username', ['unique' => true, 'name' => 'idx_username'])
                  ->addIndex(['email'], ['name' => 'idx_email'])
                  ->addIndex(['mobile'], ['name' => 'idx_mobile'])
                  ->addIndex(['status'], ['name' => 'idx_status'])
                  ->addIndex(['token_expire'], ['name' => 'idx_token_expire'])
                  ->create();

            // 添加默认管理员
            $this->insertDefaultAdmin();
        }

        // 创建用户表
        if (!$this->hasTable($prefix.'user')) {
            $table = $this->table('user', ['engine' => 'InnoDB', 'comment' => '用户表']);
            $table->addColumn('username', 'string', ['limit' => 50, 'comment' => '用户名'])
                  ->addColumn('password', 'string', ['limit' => 255, 'comment' => '密码'])
                  ->addColumn('nickname', 'string', ['limit' => 50, 'null' => true, 'comment' => '昵称'])
                  ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
                  ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
                  ->addColumn('mobile', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
                  ->addColumn('real_name', 'string', ['limit' => 50, 'null' => true, 'comment' => '真实姓名'])
                  ->addColumn('gender', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '性别：0未知，1男，2女'])
                  ->addColumn('birthday', 'date', ['null' => true, 'comment' => '生日'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                  ->addColumn('token', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Token'])
                  ->addColumn('token_expire', 'integer', ['null' => true, 'default' => 0, 'comment' => 'Token过期时间'])
                  ->addColumn('lang', 'string', ['limit' => 10, 'null' => true, 'default' => 'zh-cn', 'comment' => '语言偏好'])
                  ->addColumn('balance', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0, 'comment' => '余额'])
                  ->addColumn('points', 'integer', ['default' => 0, 'comment' => '积分'])
                  ->addColumn('vip_level', 'integer', ['limit' => 1, 'default' => 0, 'comment' => 'VIP等级'])
                  ->addColumn('vip_expire_time', 'integer', ['null' => true, 'default' => 0, 'comment' => 'VIP过期时间'])
                  ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
                  ->addColumn('last_login_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '最后登录时间'])
                  ->addColumn('login_count', 'integer', ['null' => true, 'default' => 0, 'comment' => '登录次数'])
                  ->addColumn('register_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '注册IP'])
                  ->addColumn('register_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '注册时间'])
                  ->addColumn('last_active_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '最后活跃时间'])
                  ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注'])
                  ->addColumn('create_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '更新时间'])
                  ->addIndex('username', ['unique' => true, 'name' => 'idx_username'])
                  ->addIndex(['email'], ['name' => 'idx_email'])
                  ->addIndex(['mobile'], ['name' => 'idx_mobile'])
                  ->addIndex(['status'], ['name' => 'idx_status'])
                  ->addIndex(['vip_level'], ['name' => 'idx_vip_level'])
                  ->addIndex(['register_time'], ['name' => 'idx_register_time'])
                  ->addIndex(['token_expire'], ['name' => 'idx_token_expire'])
                  ->create();
        }

        // 创建网站配置表
        if (!$this->hasTable($prefix.'website_config')) {
            $table = $this->table('website_config', ['engine' => 'InnoDB', 'comment' => '网站配置']);
            $table->addColumn('name', 'string', ['limit' => 50, 'comment' => '配置名称'])
                  ->addColumn('value', 'text', ['null' => true, 'comment' => '配置值'])
                  ->addColumn('group', 'string', ['limit' => 30, 'default' => 'basic', 'comment' => '分组'])
                  ->addColumn('title', 'string', ['limit' => 100, 'comment' => '配置标题'])
                  ->addColumn('type', 'string', ['limit' => 30, 'default' => 'text', 'comment' => '配置类型'])
                  ->addColumn('options', 'text', ['null' => true, 'comment' => '配置选项'])
                  ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
                  ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('name', ['unique' => true, 'name' => 'idx_name'])
                  ->addIndex('group', ['name' => 'idx_group'])
                  ->create();

            // 初始化网站配置数据
            $this->insertWebsiteConfig();
        }

        // 创建用户消息表
        if (!$this->hasTable($prefix.'user_message')) {
            $table = $this->table('user_message', ['engine' => 'InnoDB', 'comment' => '用户消息']);
            $table->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('title', 'string', ['limit' => 100, 'comment' => '消息标题'])
                  ->addColumn('content', 'text', ['comment' => '消息内容'])
                  ->addColumn('type', 'string', ['limit' => 30, 'default' => 'system', 'comment' => '消息类型'])
                  ->addColumn('is_read', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否已读：0未读，1已读'])
                  ->addColumn('read_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '阅读时间'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex('user_id', ['name' => 'idx_user_id'])
                  ->addIndex(['type', 'is_read'], ['name' => 'idx_type_read'])
                  ->create();
        }

        // 创建用户操作日志表
        if (!$this->hasTable($prefix.'user_operation_log')) {
            $table = $this->table('user_operation_log', ['engine' => 'InnoDB', 'comment' => '用户操作日志']);
            $table->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'comment' => '用户类型'])
                  ->addColumn('module', 'string', ['limit' => 30, 'comment' => '操作模块'])
                  ->addColumn('action', 'string', ['limit' => 50, 'comment' => '操作类型'])
                  ->addColumn('url', 'string', ['limit' => 255, 'comment' => '操作URL'])
                  ->addColumn('method', 'string', ['limit' => 10, 'comment' => '请求方法'])
                  ->addColumn('params', 'text', ['null' => true, 'comment' => '请求参数'])
                  ->addColumn('result', 'text', ['null' => true, 'comment' => '操作结果'])
                  ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
                  ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
                  ->addIndex('module', ['name' => 'idx_module'])
                  ->addIndex('action', ['name' => 'idx_action'])
                  ->addIndex('create_time', ['name' => 'idx_create_time'])
                  ->create();
        }

        // 创建用户活动日志表
        if (!$this->hasTable($prefix.'user_activity_log')) {
            $table = $this->table('user_activity_log', ['engine' => 'InnoDB', 'comment' => '用户活动日志']);
            $table->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'comment' => '用户类型'])
                  ->addColumn('action', 'string', ['limit' => 50, 'comment' => '操作类型'])
                  ->addColumn('description', 'string', ['limit' => 255, 'comment' => '描述'])
                  ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
                  ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
                  ->addColumn('data', 'text', ['null' => true, 'comment' => '附加数据'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
                  ->addIndex('action', ['name' => 'idx_action'])
                  ->addIndex('create_time', ['name' => 'idx_create_time'])
                  ->create();
        }
    }

    /**
     * 初始化网站配置数据
     */
    private function insertWebsiteConfig()
    {
        $time = time();
        $configs = [
            [
                'name' => 'site_name',
                'value' => 'AichatUi',
                'group' => 'basic',
                'title' => '网站名称',
                'type' => 'text',
                'options' => '',
                'sort' => 1,
                'remark' => '网站名称',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_title',
                'value' => 'AI网站',
                'group' => 'basic',
                'title' => '网站标题',
                'type' => 'text',
                'options' => '',
                'sort' => 1,
                'remark' => '网站标题',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_logo',
                'value' => '',
                'group' => 'basic',
                'title' => '网站LOGO',
                'type' => 'image',
                'options' => '',
                'sort' => 2,
                'remark' => '网站LOGO',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_favicon',
                'value' => '',
                'group' => 'basic',
                'title' => '网站图标',
                'type' => 'image',
                'options' => '',
                'sort' => 3,
                'remark' => '网站图标',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_keywords',
                'value' => 'AichatUi,AI聊天,人工智能',
                'group' => 'basic',
                'title' => '网站关键词',
                'type' => 'text',
                'options' => '',
                'sort' => 4,
                'remark' => '网站关键词',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_description',
                'value' => 'AichatUi是一个基于人工智能的聊天平台',
                'group' => 'basic',
                'title' => '网站描述',
                'type' => 'textarea',
                'options' => '',
                'sort' => 5,
                'remark' => '网站描述',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_icp',
                'value' => '',
                'group' => 'basic',
                'title' => 'ICP备案号',
                'type' => 'text',
                'options' => '',
                'sort' => 6,
                'remark' => 'ICP备案号',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_public_security_record',
                'value' => '',
                'group' => 'basic',
                'title' => '网站公安备案号',
                'type' => 'text',
                'options' => '',
                'sort' => 7,
                'remark' => '网站公安备案号',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_copyright',
                'value' => 'Copyright © 2023 AichatUi All Rights Reserved.',
                'group' => 'basic',
                'title' => '版权信息',
                'type' => 'text',
                'options' => '',
                'sort' => 8,
                'remark' => '版权信息',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_status',
                'value' => '1',
                'group' => 'basic',
                'title' => '网站状态',
                'type' => 'radio',
                'options' => '{"1":"开启","0":"关闭"}',
                'sort' => 9,
                'remark' => '网站状态',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_close_reason',
                'value' => '网站维护中，请稍后再试...',
                'group' => 'basic',
                'title' => '关闭原因',
                'type' => 'textarea',
                'options' => '',
                'sort' => 10,
                'remark' => '网站关闭原因',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'contact_phone',
                'value' => '13800018000',
                'group' => 'basic',
                'title' => '联系方式',
                'type' => 'text',
                'options' => '',
                'sort' => 11,
                'remark' => '联系方式',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'contact_email',
                'value' => 'a@qq.com',
                'group' => 'basic',
                'title' => '联系邮箱',
                'type' => 'text',
                'options' => '',
                'sort' => 12,
                'remark' => '联系邮箱',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'contact_address',
                'value' => '联系地址',
                'group' => 'basic',
                'title' => '联系地址',
                'type' => 'text',
                'options' => '',
                'sort' => 13,
                'remark' => '联系地址',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'user_register_status',
                'value' => '1',
                'group' => 'user',
                'title' => '用户注册',
                'type' => 'radio',
                'options' => '{"1":"开启","0":"关闭"}',
                'sort' => 14,
                'remark' => '是否开启用户注册',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'user_register_verify',
                'value' => '0',
                'group' => 'user',
                'title' => '注册验证',
                'type' => 'radio',
                'options' => '{"0":"无需验证","1":"邮箱验证","2":"手机验证"}',
                'sort' => 15,
                'remark' => '用户注册验证方式',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'user_default_status',
                'value' => '1',
                'group' => 'user',
                'title' => '用户默认状态',
                'type' => 'radio',
                'options' => '{"1":"启用","0":"禁用"}',
                'sort' => 16,
                'remark' => '新用户注册后的默认状态',
                'create_time' => $time,
                'update_time' => $time
            ]
        ];

        $this->table('website_config')->insert($configs)->save();
    }

    /**
     * 添加默认管理员
     */
    private function insertDefaultAdmin()
    {
        $time = time();
        $admin = [
            'username' => 'admin',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'nickname' => '超级管理员',
            'role' => 'super_admin',
            'status' => 1,
            'create_time' => $time,
            'update_time' => $time
        ];

        $this->table('admin')->insert($admin)->save();
    }
}