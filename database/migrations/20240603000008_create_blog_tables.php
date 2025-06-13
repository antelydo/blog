<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateBlogTables extends Migrator
{
    public function change()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 创建文章表
        if (!$this->hasTable($prefix.'blog_post')) {
            $table = $this->table('blog_post', ['engine' => 'InnoDB', 'comment' => '博客文章表']);
            $table->addColumn('title', 'string', ['limit' => 100, 'comment' => '文章标题'])
                  ->addColumn('content', 'text', ['comment' => '文章内容'])
                  ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '文章描述'])
                  ->addColumn('author_id', 'integer', ['comment' => '作者ID'])
                  ->addColumn('author_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '作者类型：user用户，admin管理员'])
                  ->addColumn('thumbnail', 'string', ['limit' => 255, 'null' => true, 'comment' => '缩略图'])
                  ->addColumn('views', 'integer', ['default' => 0, 'comment' => '浏览量'])
                  ->addColumn('likes', 'integer', ['default' => 0, 'comment' => '点赞数'])
                  ->addColumn('comments', 'integer', ['default' => 0, 'comment' => '评论数'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0草稿，1发布，2待审核，-1禁用'])
                  ->addColumn('is_top', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否置顶：0否，1是'])
                  ->addColumn('is_recommend', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否推荐：0否，1是'])
                  ->addColumn('is_banner_top', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否首页banner展示，默认0否 1是'])
                  ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
                  ->addColumn('publish_time', 'integer', ['null' => true, 'default' => 0, 'comment' => '发布时间'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('title', ['name' => 'idx_title'])
                  ->addIndex(['author_id', 'author_type'], ['name' => 'idx_author'])
                  ->addIndex('status', ['name' => 'idx_status'])
                  ->addIndex('is_top', ['name' => 'idx_is_top'])
                  ->addIndex('is_recommend', ['name' => 'idx_is_recommend'])
                  ->addIndex('publish_time', ['name' => 'idx_publish_time'])
                  ->create();
        }
        
        // 创建文章分类表
        if (!$this->hasTable($prefix.'blog_category')) {
            $table = $this->table('blog_category', ['engine' => 'InnoDB', 'comment' => '博客分类表']);
            $table->addColumn('name', 'string', ['limit' => 50, 'comment' => '分类名称'])
                  ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '分类描述'])
                  ->addColumn('parent_id', 'integer', ['default' => 0, 'comment' => '父级ID'])
                  ->addColumn('path', 'string', ['limit' => 255, 'null' => true, 'comment' => '分类路径'])
                  ->addColumn('level', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '层级'])
                  ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('name', ['name' => 'idx_name'])
                  ->addIndex('parent_id', ['name' => 'idx_parent_id'])
                  ->addIndex('status', ['name' => 'idx_status'])
                  ->create();
        }
        
        // 创建文章标签表
        if (!$this->hasTable($prefix.'blog_tag')) {
            $table = $this->table('blog_tag', ['engine' => 'InnoDB', 'comment' => '博客标签表']);
            $table->addColumn('name', 'string', ['limit' => 50, 'comment' => '标签名称'])
                  ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '标签描述'])
                  ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('name', ['unique' => true, 'name' => 'idx_name'])
                  ->addIndex('status', ['name' => 'idx_status'])
                  ->create();
        }
        
        // 创建文章分类关联表
        if (!$this->hasTable($prefix.'blog_post_category')) {
            $table = $this->table('blog_post_category', ['engine' => 'InnoDB', 'comment' => '博客文章分类关联表']);
            $table->addColumn('post_id', 'integer', ['comment' => '文章ID'])
                  ->addColumn('category_id', 'integer', ['comment' => '分类ID'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['post_id', 'category_id'], ['unique' => true, 'name' => 'idx_post_category'])
                  ->create();
        }
        
        // 创建文章标签关联表
        if (!$this->hasTable($prefix.'blog_post_tag')) {
            $table = $this->table('blog_post_tag', ['engine' => 'InnoDB', 'comment' => '博客文章标签关联表']);
            $table->addColumn('post_id', 'integer', ['comment' => '文章ID'])
                  ->addColumn('tag_id', 'integer', ['comment' => '标签ID'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['post_id', 'tag_id'], ['unique' => true, 'name' => 'idx_post_tag'])
                  ->create();
        }
        
        // 创建文章点赞表
        if (!$this->hasTable($prefix.'blog_like')) {
            $table = $this->table('blog_like', ['engine' => 'InnoDB', 'comment' => '博客点赞表']);
            $table->addColumn('post_id', 'integer', ['comment' => '文章ID'])
                  ->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user用户，admin管理员'])
                  ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['post_id', 'user_id', 'user_type'], ['unique' => true, 'name' => 'idx_post_user'])
                  ->addIndex('post_id', ['name' => 'idx_post_id'])
                  ->create();
        }
        
        // 创建文章评论表
        if (!$this->hasTable($prefix.'blog_comment')) {
            $table = $this->table('blog_comment', ['engine' => 'InnoDB', 'comment' => '博客评论表']);
            $table->addColumn('post_id', 'integer', ['comment' => '文章ID'])
                  ->addColumn('parent_id', 'integer', ['default' => 0, 'comment' => '父级评论ID'])
                  ->addColumn('content', 'text', ['comment' => '评论内容'])
                  ->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user用户，admin管理员'])
                  ->addColumn('likes', 'integer', ['default' => 0, 'comment' => '点赞数'])
                  ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
                  ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
                  ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0待审核，1通过，-1拒绝'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex('post_id', ['name' => 'idx_post_id'])
                  ->addIndex('parent_id', ['name' => 'idx_parent_id'])
                  ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
                  ->addIndex('status', ['name' => 'idx_status'])
                  ->addColumn('type', 'string', ['limit' => 20, 'null' => true, 'comment' => '评论类型:normal:普通评论,admin:管理员,author:作者,guest:访客'])
                  ->create();
        }
    }
}