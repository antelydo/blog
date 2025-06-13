<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddFavoritesToPostTable extends Migrator
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
        
        // 尝试不同的表名形式
        $postTableNames = [
            $prefix.'blog_post',  // 带前缀
            'blog_post',         // 不带前缀
            $prefix.'post',      // 可能的其他名称
            'post'               // 可能的其他名称
        ];
        
        $postTableFound = false;
        $postTableName = '';
        
        foreach ($postTableNames as $tableName) {
            if ($this->hasTable($tableName)) {
                $postTableFound = true;
                $postTableName = $tableName;
                break;
            }
        }
        
        if ($postTableFound) {
            $table = $this->table($postTableName);
            
            // 检查字段是否已存在
            if (!$table->hasColumn('favorites')) {
                // 添加favorites字段
                $table->addColumn('favorites', 'integer', [
                    'default' => 0,
                    'comment' => '收藏数',
                    'after' => 'likes'
                ])->update();
                
                $this->output->writeln('成功添加 favorites 字段到 ' . $postTableName . ' 表');
            } else {
                $this->output->writeln('favorites 字段已存在于 ' . $postTableName . ' 表中');
            }
        } else {
            $this->output->writeln('文章表不存在，请手动添加 favorites 字段');
        }
    }

    /**
     * 回滚操作
     */
    public function down()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 尝试不同的表名形式
        $postTableNames = [
            $prefix.'blog_post',  // 带前缀
            'blog_post',         // 不带前缀
            $prefix.'post',      // 可能的其他名称
            'post'               // 可能的其他名称
        ];
        
        $postTableFound = false;
        $postTableName = '';
        
        foreach ($postTableNames as $tableName) {
            if ($this->hasTable($tableName)) {
                $postTableFound = true;
                $postTableName = $tableName;
                break;
            }
        }
        
        if ($postTableFound) {
            $table = $this->table($postTableName);
            
            // 检查字段是否存在
            if ($table->hasColumn('favorites')) {
                // 删除 favorites 字段
                $table->removeColumn('favorites')->update();
                $this->output->writeln('成功从 ' . $postTableName . ' 表中删除 favorites 字段');
            } else {
                $this->output->writeln('favorites 字段不存在于 ' . $postTableName . ' 表中');
            }
        } else {
            $this->output->writeln('文章表不存在');
        }
    }
}
