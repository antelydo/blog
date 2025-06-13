<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateFriendshipLinksTable extends Migrator
{
    public function change()
    {
        $table = $this->table('friendship_links', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('title', 'string', ['limit' => 100, 'comment' => '链接标题'])
            ->addColumn('logo', 'string', ['limit' => 255, 'comment' => '链接logo'])
            ->addColumn('url', 'string', ['limit' => 255, 'comment' => '链接地址'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('type', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '类型默认1友情站点，2技术博客 3合作伙伴'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '描述'])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
            ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
            ->addColumn('creator', 'string', ['limit' => 50, 'comment' => '创建者'])
            ->addColumn('creator_type', 'string', ['limit' => 20, 'comment' => '创建者类型'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('user_id', 'integer', ['default' => 1, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['null' => false, 'comment' => '用户类型默认user前端用户，默认admin为后端管理员'])
            ->addIndex(['sort'], ['name' => 'idx_sort'])
            ->addIndex(['user_id'], ['name' => 'id_user'])
            ->create();
    }
} 