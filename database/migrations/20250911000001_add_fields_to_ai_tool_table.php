<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddFieldsToAiToolTable extends Migrator
{
    /**
     * 添加新字段到AI工具表
     */
    public function up()
    {
        $table = $this->table('ai_tool');
        $table
            ->addColumn('platforms', 'text', ['null' => true, 'comment' => '支持的平台，JSON格式存储，如["Web", "iOS", "Android"]'])
            ->addColumn('languages', 'text', ['null' => true, 'comment' => '支持的语言，JSON格式存储，如["中文", "英文"]'])
            ->addColumn('usage_tips', 'text', ['null' => true, 'comment' => '使用技巧，JSON格式存储'])
            ->addColumn('company', 'string', ['limit' => 100, 'null' => true, 'comment' => '公司名称'])
            ->update();
    }

    /**
     * 删除AI工具表中的新字段
     */
    public function down()
    {
        $table = $this->table('ai_tool');
        $table
            ->removeColumn('platforms')
            ->removeColumn('languages')
            ->removeColumn('usage_tips')
            ->removeColumn('company')
            ->update();
    }
}
