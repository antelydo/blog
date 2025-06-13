<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateCommentLikeTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 创建评论点赞表
        if (!$this->hasTable($prefix.'comment_like')) {
            $table = $this->table('comment_like', ['engine' => 'InnoDB', 'comment' => '评论点赞表']);
            $table->addColumn('comment_id', 'integer', ['comment' => '评论ID'])
                  ->addColumn('user_id', 'integer', ['default' => 0, 'comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user用户，admin管理员，guest访客'])
                  ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
                  ->addColumn('uuid', 'string', ['limit' => 180, 'null' => true, 'comment' => '访客唯一标识'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addIndex(['comment_id', 'user_id', 'user_type', 'uuid'], ['unique' => true, 'name' => 'idx_comment_user'])
                  ->addIndex('comment_id', ['name' => 'idx_comment_id'])
                  ->create();
                  
            $this->output->writeln('成功创建评论点赞表 comment_like');
        } else {
            $this->output->writeln('评论点赞表 comment_like 已存在');
        }
        
        // 修改blog_comment表，添加likes字段（如果不存在）
        if ($this->hasTable($prefix.'blog_comment')) {
            $table = $this->table($prefix.'blog_comment');
            
            // 检查字段是否已存在
            if (!$table->hasColumn('likes')) {
                // 添加likes字段
                $table->addColumn('likes', 'integer', [
                    'default' => 0,
                    'comment' => '点赞数',
                    'after' => 'content'
                ])->update();
                
                $this->output->writeln('成功添加 likes 字段到 blog_comment 表');
            } else {
                $this->output->writeln('likes 字段已存在于 blog_comment 表中');
            }
        } else {
            $this->output->writeln('blog_comment 表不存在');
        }
    }

    /**
     * 回滚操作
     */
    public function down()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 删除评论点赞表
        if ($this->hasTable($prefix.'comment_like')) {
            $this->table($prefix.'comment_like')->drop()->save();
            $this->output->writeln('成功删除评论点赞表 comment_like');
        } else {
            $this->output->writeln('评论点赞表 comment_like 不存在');
        }
        
        // 从blog_comment表中删除likes字段（如果存在）
        if ($this->hasTable($prefix.'blog_comment')) {
            $table = $this->table($prefix.'blog_comment');
            
            // 检查字段是否存在
            if ($table->hasColumn('likes')) {
                // 删除likes字段
                $table->removeColumn('likes')->update();
                $this->output->writeln('成功从 blog_comment 表中删除 likes 字段');
            } else {
                $this->output->writeln('likes 字段不存在于 blog_comment 表中');
            }
        } else {
            $this->output->writeln('blog_comment 表不存在');
        }
    }
}
