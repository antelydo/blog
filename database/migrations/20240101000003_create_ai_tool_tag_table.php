<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolTagTable extends Migrator
{
    /**
     * 创建AI工具标签表
     */
    public function up()
    {
        $table = $this->table('ai_tool_tag', ['engine' => 'InnoDB', 'comment' => 'AI工具标签表']);
        $table
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '标签名称'])
            ->addColumn('slug', 'string', ['limit' => 50, 'null' => false, 'comment' => '标签别名，用于URL'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '标签描述'])
            ->addColumn('count', 'integer', ['signed' => true, 'default' => 0, 'comment' => '使用次数'])
            ->addColumn('seo_title', 'string', ['limit' => 100, 'null' => true, 'comment' => 'SEO标题'])
            ->addColumn('seo_keywords', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO关键词'])
            ->addColumn('seo_description', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO描述'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '更新时间'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['count'])
            ->create();
    }

    /**
     * 删除AI工具标签表
     */
    public function down()
    {
        $this->dropTable('ai_tool_tag');
    }
}
