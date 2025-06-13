<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserLoginLog extends Migrator
{
    public function change()
    {
        // 创建用户登录日志表
        $table = $this->table('user_login_log', ['engine' => 'InnoDB', 'comment' => '用户登录日志表']);
        $table->addColumn('user_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '用户ID'])
              ->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'comment' => '用户名'])
              ->addColumn('ip_address', 'string', ['limit' => 50, 'default' => '', 'comment' => '登录IP'])
              ->addColumn('device', 'string', ['limit' => 255, 'default' => '', 'comment' => '登录设备'])
              ->addColumn('location', 'string', ['limit' => 255, 'default' => '', 'comment' => '登录地点'])
              ->addColumn('user_agent', 'string', ['limit' => 500, 'default' => '', 'comment' => '用户代理信息'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '登录状态：0失败，1成功'])
              ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注信息'])
              ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
              ->addIndex(['user_id'], ['name' => 'idx_user_id'])
              ->addIndex(['ip_address'], ['name' => 'idx_ip_address'])
              ->addIndex(['create_time'], ['name' => 'idx_create_time'])
              ->addIndex(['status'], ['name' => 'idx_status'])
              ->create();
              
        // 添加一些示例数据
        $this->insertSampleData();
    }
    
    /**
     * 插入示例数据
     */
    protected function insertSampleData()
    {
        $time = time();
        $data = [
            [
                'user_id' => 1,
                'username' => 'user1',
                'ip_address' => '192.168.1.1',
                'device' => 'Chrome on Windows',
                'location' => '中国, 北京市',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'remark' => '正常登录',
                'create_time' => $time - 86400 * 7
            ],
            [
                'user_id' => 1,
                'username' => 'user1',
                'ip_address' => '192.168.1.2',
                'device' => 'Firefox on MacOS',
                'location' => '中国, 上海市',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0',
                'status' => 1,
                'remark' => '正常登录',
                'create_time' => $time - 86400 * 5
            ],
            [
                'user_id' => 2,
                'username' => 'user2',
                'ip_address' => '10.0.0.1',
                'device' => 'Safari on iOS',
                'location' => '中国, 广州市',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
                'status' => 1,
                'remark' => '正常登录',
                'create_time' => $time - 86400 * 3
            ],
            [
                'user_id' => 3,
                'username' => 'user3',
                'ip_address' => '172.16.0.1',
                'device' => 'Edge on Windows',
                'location' => '中国, 深圳市',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
                'status' => 0,
                'remark' => '密码错误',
                'create_time' => $time - 86400 * 2
            ],
            [
                'user_id' => 3,
                'username' => 'user3',
                'ip_address' => '172.16.0.1',
                'device' => 'Edge on Windows',
                'location' => '中国, 深圳市',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59',
                'status' => 1,
                'remark' => '正常登录',
                'create_time' => $time - 86400 * 2 + 60
            ]
        ];
        
        $this->table('user_login_log')->insert($data)->save();
    }
}
