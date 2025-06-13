<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddIsShowToAiToolTagTable extends Migrator
{
    /**
     * 添加是否显示字段到AI工具标签表
     */
    public function up()
    {
        $table = $this->table('ai_tool_tag');
        $table
            ->addColumn('is_show', 'boolean', ['default' => true, 'comment' => '是否显示，1显示，0隐藏'])
            ->addIndex(['is_show'])
            ->update();
    }

    /**
     * 删除AI工具标签表中的是否显示字段
     */
    public function down()
    {
        $table = $this->table('ai_tool_tag');
        $table->removeColumn('is_show')->update();
    }
}
