<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSiteViewLog extends Migrator
{
    public function up()
    {
        $table = $this->table('site_view_log', ['engine' => 'InnoDB', 'comment' => '网站浏览时长记录表']);
        $table->addColumn('user_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'guest', 'comment' => '用户类型: guest, registered, admin'])
            ->addColumn('page_type', 'string', ['limit' => 50, 'default' => 'site', 'comment' => '页面类型: site, home, category, tag, admin等'])
            ->addColumn('page_id', 'integer', ['signed' => true, 'null' => true, 'comment' => '页面ID，如分类ID、标签ID等'])
            ->addColumn('ip_address', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP地址'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'default' => '', 'comment' => '浏览器标识'])
            ->addColumn('referer', 'string', ['limit' => 255, 'default' => '', 'comment' => '来源页面'])
            ->addColumn('view_duration', 'integer', ['signed' => true, 'default' => 0, 'comment' => '浏览时长(秒)'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
            ->addIndex(['page_type'], ['name' => 'idx_page_type'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->create();
            
        // 为表格添加模拟数据
        $this->generateSampleData();
    }

    public function down()
    {
        $this->dropTable('site_view_log');
    }
    
    /**
     * 生成网站浏览日志的模拟数据
     */
    private function generateSampleData()
    {
        $prefix = config('database.connections.mysql.prefix');
        $table = $prefix . 'site_view_log';
        
        // 获取用户ID列表
        $admins = $this->fetchAll("SELECT id FROM {$prefix}admin LIMIT 5");
        $adminIds = array_column($admins, 'id');
        
        $users = $this->fetchAll("SELECT id FROM {$prefix}user LIMIT 20");
        $userIds = array_column($users, 'id');
        
        // 模拟IP地址
        $ipAddresses = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '127.0.0.1',
            '8.8.8.8',
            '114.114.114.114'
        ];
        
        // 模拟User-Agent
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36'
        ];
        
        // 模拟来源页面
        $referers = [
            'https://www.google.com/',
            'https://www.bing.com/',
            'https://www.baidu.com/',
            'https://www.facebook.com/',
            'https://twitter.com/',
            'https://www.instagram.com/',
            'https://www.linkedin.com/',
            'https://www.youtube.com/',
            ''
        ];
        
        // 模拟页面类型
        $pageTypes = [
            'home',
            'category',
            'tag',
            'site',
            'admin'
        ];
        
        // 当前时间
        $now = time();
        
        // 生成100条模拟数据
        $viewLogs = [];
        
        for ($i = 1; $i <= 100; $i++) {
            // 随机选择页面类型
            $pageType = $pageTypes[array_rand($pageTypes)];
            
            // 随机选择页面ID
            $pageId = null;
            if ($pageType === 'category' || $pageType === 'tag') {
                $pageId = rand(1, 10);
            }
            
            // 随机选择用户类型和ID
            $userType = ['guest', 'registered', 'admin'][array_rand([0, 1, 2])];
            $userId = 0;
            
            if ($userType === 'admin') {
                $userId = !empty($adminIds) ? $adminIds[array_rand($adminIds)] : 1;
            } elseif ($userType === 'registered') {
                $userId = !empty($userIds) ? $userIds[array_rand($userIds)] : 1;
            }
            
            // 随机生成浏览时间（最近30天内）
            $createTime = $now - rand(0, 30 * 86400);
            
            $viewLogs[] = [
                'user_id' => $userId,
                'user_type' => $userType,
                'page_type' => $pageType,
                'page_id' => $pageId,
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'referer' => $referers[array_rand($referers)],
                'view_duration' => rand(10, 1800), // 浏览时长10秒到30分钟
                'create_time' => $createTime
            ];
        }
        
        // 批量插入数据
        if (!empty($viewLogs)) {
            $this->table('site_view_log')->insert($viewLogs)->save();
        }
    }
}
