<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolCategoryRelationTable extends Migrator
{
    /**
     * 创建AI工具与分类关联表
     */
    public function up()
    {
        $table = $this->table('ai_tool_category_relation', ['engine' => 'InnoDB', 'comment' => 'AI工具与分类关联表']);
        $table
            ->addColumn('tool_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '工具ID'])
            ->addColumn('category_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '分类ID'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addIndex(['tool_id'])
            ->addIndex(['category_id'])
            ->addIndex(['tool_id', 'category_id'], ['unique' => true])
            ->create();
    }

    /**
     * 删除AI工具与分类关联表
     */
    public function down()
    {
        $this->dropTable('ai_tool_category_relation');
    }
}
