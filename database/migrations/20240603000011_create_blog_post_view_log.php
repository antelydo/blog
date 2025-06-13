<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateBlogPostViewLog extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('blog_post_view_log', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('post_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '文章ID'])
            ->addColumn('user_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'guest', 'comment' => '用户类型: guest, registered, admin'])
            ->addColumn('ip_address', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP地址'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'default' => '', 'comment' => '浏览器标识'])
            ->addColumn('referer', 'string', ['limit' => 255, 'default' => '', 'comment' => '来源页面'])
            ->addColumn('view_duration', 'integer', ['signed' => true, 'default' => 0, 'comment' => '浏览时长(秒)'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['post_id'], ['name' => 'idx_post_id'])
            ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->create();
            
        // 为表格添加模拟数据
        $this->generateSampleData();
    }

    public function down()
    {
        $this->dropTable('blog_post_view_log');
    }
    
    /**
     * 生成博客浏览日志的模拟数据
     */
    private function generateSampleData()
    {
        $prefix = config('database.connections.mysql.prefix');
        $table = $prefix . 'blog_post_view_log';
        
        // 获取文章ID列表
        $posts = $this->fetchAll("SELECT id FROM {$prefix}blog_post ORDER BY id LIMIT 30");
        $postIds = array_column($posts, 'id');
        
        // 获取用户ID列表
        $admins = $this->fetchAll("SELECT id FROM {$prefix}admin LIMIT 5");
        $adminIds = array_column($admins, 'id');
        
        $users = $this->fetchAll("SELECT id FROM {$prefix}user LIMIT 20");
        $userIds = array_column($users, 'id');
        
        // 生成模拟的浏览器标识
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36 Edg/117.0.2045.60',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 13; SM-S908B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36'
        ];
        
        // 生成模拟的来源页面
        $referers = [
            'https://www.google.com/',
            'https://www.bing.com/',
            'https://www.baidu.com/',
            'https://www.facebook.com/',
            'https://twitter.com/',
            'https://www.instagram.com/',
            'https://www.linkedin.com/',
            'https://example.com/blog',
            'https://example.com/category/tech',
            'https://example.com/home',
            ''  // 直接访问
        ];
        
        // 生成模拟的IP地址
        $ipAddresses = [
            '192.168.1.' . rand(1, 254),
            '172.16.' . rand(1, 254) . '.' . rand(1, 254),
            '10.0.' . rand(1, 254) . '.' . rand(1, 254),
            '8.8.8.' . rand(1, 254),
            '203.0.113.' . rand(1, 254),
            '103.21.' . rand(1, 254) . '.' . rand(1, 254),
            '51.15.' . rand(1, 254) . '.' . rand(1, 254),
            '128.0.' . rand(1, 254) . '.' . rand(1, 254)
        ];
        
        // 生成100条浏览日志记录
        $viewLogs = [];
        $now = time();
        
        for ($i = 1; $i <= 100; $i++) {
            // 随机选择文章ID
            $postId = $postIds[array_rand($postIds)];
            
            // 随机选择用户类型和ID
            $userType = ['guest', 'registered', 'admin'][array_rand([0, 1, 2])];
            $userId = 0;
            
            if ($userType === 'admin') {
                $userId = $adminIds[array_rand($adminIds)];
            } elseif ($userType === 'registered') {
                $userId = $userIds[array_rand($userIds)];
            }
            
            // 随机生成浏览时间（最近30天内）
            $createTime = $now - rand(0, 30 * 86400);
            
            $viewLogs[] = [
                'post_id' => $postId,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'referer' => $referers[array_rand($referers)],
                'view_duration' => rand(10, 600), // 浏览时长10秒到10分钟
                'create_time' => $createTime
            ];
        }
        
        // 插入数据
        $this->table('blog_post_view_log')->insert($viewLogs)->saveData();
    }
} 