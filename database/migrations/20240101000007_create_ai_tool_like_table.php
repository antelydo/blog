<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolLikeTable extends Migrator
{
    /**
     * 创建AI工具点赞表
     */
    public function up()
    {
        $table = $this->table('ai_tool_like', ['engine' => 'InnoDB', 'comment' => 'AI工具点赞表']);
        $table
            ->addColumn('tool_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '工具ID'])
            ->addColumn('user_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user普通用户，admin管理员'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => true, 'comment' => 'IP地址'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['tool_id'])
            ->addIndex(['user_id'])
            ->addIndex(['tool_id', 'user_id', 'user_type'], ['unique' => true])
            ->create();
    }

    /**
     * 删除AI工具点赞表
     */
    public function down()
    {
        $this->dropTable('ai_tool_like');
    }
}
