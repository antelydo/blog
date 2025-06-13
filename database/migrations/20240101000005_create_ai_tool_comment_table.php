<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolCommentTable extends Migrator
{
    /**
     * 创建AI工具评论表
     */
    public function up()
    {
        $table = $this->table('ai_tool_comment', ['engine' => 'InnoDB', 'comment' => 'AI工具评论表']);
        $table
            ->addColumn('tool_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '工具ID'])
            ->addColumn('user_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user普通用户，admin管理员'])
            ->addColumn('parent_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '父评论ID，0表示顶级评论'])
            ->addColumn('content', 'text', ['null' => false, 'comment' => '评论内容'])
            ->addColumn('rating', 'integer', ['signed' => true, 'default' => 0, 'comment' => '评分，1-5分'])
            ->addColumn('likes', 'integer', ['signed' => true, 'default' => 0, 'comment' => '点赞数'])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'pending', 'comment' => '状态：pending待审核，approved已通过，rejected已拒绝'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => true, 'comment' => 'IP地址'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '更新时间'])
            ->addIndex(['tool_id'])
            ->addIndex(['user_id'])
            ->addIndex(['parent_id'])
            ->addIndex(['status'])
            ->addIndex(['create_time'])
            ->create();
    }

    /**
     * 删除AI工具评论表
     */
    public function down()
    {
        $this->dropTable('ai_tool_comment');
    }
}
