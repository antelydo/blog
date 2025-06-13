<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolVisitLogTable extends Migrator
{
    /**
     * 创建AI工具访问记录表
     */
    public function up()
    {
        $table = $this->table('ai_tool_visit_log', ['engine' => 'InnoDB', 'comment' => 'AI工具访问记录表']);
        $table
            ->addColumn('tool_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '工具ID'])
            ->addColumn('user_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '用户ID，0表示未登录用户'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'null' => true, 'comment' => '用户类型：user普通用户，admin管理员'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => true, 'comment' => 'IP地址'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
            ->addColumn('referer', 'string', ['limit' => 255, 'null' => true, 'comment' => '来源页面'])
            ->addColumn('visit_date', 'date', ['null' => false, 'comment' => '访问日期'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['tool_id'])
            ->addIndex(['user_id'])
            ->addIndex(['visit_date'])
            ->addIndex(['create_time'])
            ->create();
    }

    /**
     * 删除AI工具访问记录表
     */
    public function down()
    {
        $this->dropTable('ai_tool_visit_log');
    }
}
