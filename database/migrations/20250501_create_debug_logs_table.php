<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateDebugLogsTable extends Migrator
{
    /**
     * 创建调试日志表
     */
    public function up()
    {
        $table = $this->table('debug_logs', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('type', 'string', ['limit' => 50, 'default' => '', 'comment' => '调试类型'])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'comment' => '控制器/回调名称'])
            ->addColumn('params', 'text', ['null' => true, 'comment' => '参数(JSON格式)'])
            ->addColumn('result', 'text', ['null' => true, 'comment' => '执行结果(JSON格式)'])
            ->addColumn('execution_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '执行时间(毫秒)'])
            ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP地址'])
            ->addColumn('user_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('user_type', 'string', ['limit' => 20, 'default' => '', 'comment' => '用户类型'])
            ->addColumn('request_url', 'string', ['limit' => 1000, 'default' => '', 'comment' => '请求URL'])
            ->addColumn('request_method', 'string', ['limit' => 10, 'default' => '', 'comment' => '请求方法'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('remark', 'string', ['limit' => 1000, 'null' => true, 'comment' => '备注'])
            ->addIndex(['type', 'name'])
            ->addIndex(['user_id', 'user_type'])
            ->addIndex(['create_time'])
            ->create();
    }

    /**
     * 删除调试日志表
     */
    public function down()
    {
        $this->dropTable('debug_logs');
    }
}
