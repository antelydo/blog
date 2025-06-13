<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolTagRelationTable extends Migrator
{
    /**
     * 创建AI工具与标签关联表
     */
    public function up()
    {
        $table = $this->table('ai_tool_tag_relation', ['engine' => 'InnoDB', 'comment' => 'AI工具与标签关联表']);
        $table
            ->addColumn('tool_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '工具ID'])
            ->addColumn('tag_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '标签ID'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['tool_id'])
            ->addIndex(['tag_id'])
            ->addIndex(['tool_id', 'tag_id'], ['unique' => true])
            ->create();
    }

    /**
     * 删除AI工具与标签关联表
     */
    public function down()
    {
        $this->dropTable('ai_tool_tag_relation');
    }
}
