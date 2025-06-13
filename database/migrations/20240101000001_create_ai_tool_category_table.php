<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolCategoryTable extends Migrator
{
    /**
     * 创建工具分类表
     */
    public function up()
    {
        $table = $this->table('ai_tool_category', ['engine' => 'InnoDB', 'comment' => 'AI工具分类表']);
        $table
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '分类名称'])
            ->addColumn('slug', 'string', ['limit' => 50, 'null' => false, 'comment' => '分类别名，用于URL'])
            ->addColumn('icon', 'string', ['limit' => 255, 'null' => true, 'comment' => '分类图标'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '分类描述'])
            ->addColumn('parent_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '父级分类ID，0表示顶级分类'])
            ->addColumn('sort_order', 'integer', ['signed' => true, 'default' => 0, 'comment' => '排序，数字越小越靠前'])
            ->addColumn('is_show', 'boolean', ['default' => true, 'comment' => '是否显示，1显示，0隐藏'])
            ->addColumn('seo_title', 'string', ['limit' => 100, 'null' => true, 'comment' => 'SEO标题'])
            ->addColumn('seo_keywords', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO关键词'])
            ->addColumn('seo_description', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO描述'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '更新时间'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['parent_id'])
            ->addIndex(['sort_order'])
            ->addIndex(['is_show'])
            ->create();
    }

    /**
     * 删除工具分类表
     */
    public function down()
    {
        $this->dropTable('ai_tool_category');
    }
}
