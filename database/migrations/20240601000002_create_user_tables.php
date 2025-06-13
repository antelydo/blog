<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserTables extends Migrator
{
    public function change()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 用户表结构定义
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
                  ->addColumn('role', 'string', ['null' => false, 'comment' => '默认普通用户user,编辑用户editor，管理员admin'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                  ->addColumn('lang', 'string', ['limit' => 50, 'null' => true, 'comment' => '语言'])
                  ->addColumn('balance', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0, 'comment' => '余额'])
                  ->addColumn('points', 'integer', ['default' => 0, 'comment' => '积分'])
                  ->addColumn('vip_level', 'integer', ['limit' => 1, 'default' => 0, 'comment' => 'VIP等级'])
                  ->addColumn('register_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '注册IP'])
                  ->addColumn('register_time', 'integer', ['comment' => '注册时间'])
                  ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
                  ->addColumn('last_login_time', 'integer', ['null' => true, 'comment' => '最后登录时间'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('username', ['unique' => true, 'name' => 'idx_username'])
                  ->addIndex('email', ['name' => 'idx_email'])
                  ->addIndex('mobile', ['name' => 'idx_mobile'])
                  ->addIndex('status', ['name' => 'idx_status'])
                  ->create();
        }
        
        // 用户模拟数据
        $users = [
            [
                'username' => 'user1',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'nickname' => '测试用户1',
                'avatar' => '',
                'email' => 'user1@example.com',
                'mobile' => '13800138001',
                'real_name' => '张三',
                'gender' => 1,
                'birthday' => '1990-01-01',
                'status' => 1,
                'lang' => 'zh-cn',
                'balance' => 100.00,
                'points' => 100,
                'vip_level' => 1,
                'register_ip' => '127.0.0.1',
                'register_time' => time(),
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'username' => 'user2',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'nickname' => '测试用户2',
                'avatar' => '',
                'email' => 'user2@example.com',
                'mobile' => '13800138002',
                'real_name' => '李四',
                'gender' => 2,
                'birthday' => '1995-05-05',
                'status' => 1,
                'lang' => 'zh-cn',
                'balance' => 200.00,
                'points' => 200,
                'vip_level' => 2,
                'register_ip' => '127.0.0.1',
                'register_time' => time(),
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'username' => 'user3',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'nickname' => '测试用户3',
                'avatar' => '',
                'email' => 'user3@example.com',
                'mobile' => '13800138003',
                'real_name' => '王五',
                'gender' => 1,
                'birthday' => '1985-10-10',
                'status' => 1,
                'lang' => 'zh-cn',
                'balance' => 300.00,
                'points' => 300,
                'vip_level' => 3,
                'register_ip' => '127.0.0.1',
                'register_time' => time(),
                'create_time' => time(),
                'update_time' => time()
            ]
        ];
           // 插入数据
        $this->table('user')->insert($users)->save();
    
    }
}