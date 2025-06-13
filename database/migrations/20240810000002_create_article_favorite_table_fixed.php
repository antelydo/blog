<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateArticleFavoriteTableFixed extends Migrator
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
        
        // 创建文章收藏表
        if (!$this->hasTable($prefix.'blog_favorite')) {
            $table = $this->table('blog_favorite', ['engine' => 'InnoDB', 'comment' => '文章收藏表']);
            $table->addColumn('post_id', 'integer', ['comment' => '文章ID'])
                  ->addColumn('user_id', 'integer', ['comment' => '用户ID'])
                  ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'user', 'comment' => '用户类型：user用户，admin管理员'])
                  ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
                  ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
                  ->addIndex(['post_id', 'user_id', 'user_type'], ['unique' => true, 'name' => 'idx_post_user'])
                  ->addIndex('post_id', ['name' => 'idx_post_id'])
                  ->addIndex('user_id', ['name' => 'idx_user_id'])
                  ->create();
                  
            $this->output->writeln('成功创建文章收藏表 blog_favorite');
        } else {
            $this->output->writeln('文章收藏表 blog_favorite 已存在');
        }
        
        // 注意：添加 favorites 字段到文章表的操作已移动到单独的迁移文件中
        $this->output->writeln('请运行 20240810000003_add_favorites_to_post_table 迁移文件添加 favorites 字段');
    }

    /**
     * 回滚操作
     */
    public function down()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 删除文章收藏表
        if ($this->hasTable($prefix.'blog_favorite')) {
            $this->table($prefix.'blog_favorite')->drop()->save();
            $this->output->writeln('成功删除文章收藏表 blog_favorite');
        } else {
            $this->output->writeln('文章收藏表 blog_favorite 不存在');
        }
        
        // 注意：从文章表中删除 favorites 字段的操作已移动到单独的迁移文件中
        $this->output->writeln('请运行 migrate:rollback 回滚 20240810000003_add_favorites_to_post_table 迁移文件删除 favorites 字段');
    }
}
