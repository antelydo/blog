<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolTable extends Migrator
{
    /**
     * 创建AI工具表
     */
    public function up()
    {
        $table = $this->table('ai_tool', ['engine' => 'InnoDB', 'comment' => 'AI工具表']);
        $table
            ->addColumn('category_id', 'integer', ['signed' => true, 'null' => false, 'comment' => '所属分类ID'])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '工具名称'])
            ->addColumn('slug', 'string', ['limit' => 100, 'null' => false, 'comment' => '工具别名，用于URL'])
            ->addColumn('logo', 'string', ['limit' => 255, 'null' => true, 'comment' => '工具Logo'])
            ->addColumn('banner', 'string', ['limit' => 255, 'null' => true, 'comment' => '工具Banner图'])
            ->addColumn('screenshots', 'text', ['null' => true, 'comment' => '工具截图，JSON格式存储多张图片'])
            ->addColumn('short_description', 'string', ['limit' => 255, 'null' => true, 'comment' => '简短描述'])
            ->addColumn('description', 'text', ['null' => true, 'comment' => '详细描述'])
            ->addColumn('features', 'text', ['null' => true, 'comment' => '功能特点，JSON格式'])
            ->addColumn('website_url', 'string', ['limit' => 255, 'null' => true, 'comment' => '官方网站'])
            ->addColumn('pricing_type', 'string', ['limit' => 20, 'default' => 'free', 'comment' => '价格类型：free免费，freemium部分免费，paid付费'])
            ->addColumn('pricing_info', 'text', ['null' => true, 'comment' => '价格信息，JSON格式'])
            ->addColumn('is_verified', 'boolean', ['default' => false, 'comment' => '是否已验证，1已验证，0未验证'])
            ->addColumn('is_recommended', 'boolean', ['default' => false, 'comment' => '是否推荐，1推荐，0不推荐'])
            ->addColumn('is_top', 'boolean', ['default' => false, 'comment' => '是否置顶，1置顶，0不置顶'])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'published', 'comment' => '状态：draft草稿，published已发布，pending待审核'])
            ->addColumn('views', 'integer', ['signed' => true, 'default' => 0, 'comment' => '浏览量'])
            ->addColumn('likes', 'integer', ['signed' => true, 'default' => 0, 'comment' => '点赞数'])
            ->addColumn('favorites', 'integer', ['signed' => true, 'default' => 0, 'comment' => '收藏数'])
            ->addColumn('comments', 'integer', ['signed' => true, 'default' => 0, 'comment' => '评论数'])
            ->addColumn('rating', 'decimal', ['precision' => 3, 'scale' => 1, 'default' => 0.0, 'comment' => '评分，1-5分'])
            ->addColumn('rating_count', 'integer', ['signed' => true, 'default' => 0, 'comment' => '评分人数'])
            ->addColumn('seo_title', 'string', ['limit' => 100, 'null' => true, 'comment' => 'SEO标题'])
            ->addColumn('seo_keywords', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO关键词'])
            ->addColumn('seo_description', 'string', ['limit' => 255, 'null' => true, 'comment' => 'SEO描述'])
            ->addColumn('sort_order', 'integer', ['signed' => true, 'default' => 0, 'comment' => '排序，数字越小越靠前'])
            ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('publish_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '发布时间'])
            ->addIndex(['category_id'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['status'])
            ->addIndex(['is_recommended'])
            ->addIndex(['is_top'])
            ->addIndex(['sort_order'])
            ->addIndex(['views'])
            ->addIndex(['rating'])
            ->addIndex(['create_time'])
            ->addIndex(['publish_time'])
            ->create();
    }

    /**
     * 删除AI工具表
     */
    public function down()
    {
        $this->dropTable('ai_tool');
    }
}
