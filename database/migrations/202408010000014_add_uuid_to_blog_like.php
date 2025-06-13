<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddUuidToBlogLike extends Migrator
{
    /**
     * 添加 uuid 字段到 blog_like 表
     */
    public function up()
    {
        // 获取表前缀
        $prefix = '';
        
        // 检查表是否存在
        if ($this->hasTable($prefix.'blog_like')) {
            $table = $this->table($prefix.'blog_like');
            
            // 检查字段是否已存在
            if (!$table->hasColumn('uuid')) {
                // 添加 uuid 字段
                $table->addColumn('uuid', 'string', [
                    'limit' => 180,
                    'null' => true,
                    'comment' => '访客唯一标识',
                    'after' => 'ip'
                ])->update();
                
                // 添加索引
                $table->addIndex('uuid', ['name' => 'idx_uuid'])->update();
                
                $this->output->writeln('成功添加 uuid 字段到 blog_like 表');
            } else {
                $this->output->writeln('uuid 字段已存在于 blog_like 表中');
            }
        } else {
            $this->output->writeln('blog_like 表不存在');
        }
    }

    /**
     * 回滚操作，删除 uuid 字段
     */
    public function down()
    {
        // 获取表前缀
        $prefix = '';
        
        // 检查表是否存在
        if ($this->hasTable($prefix.'blog_like')) {
            $table = $this->table($prefix.'blog_like');
            
            // 检查字段是否存在
            if ($table->hasColumn('uuid')) {
                // 删除索引
                if ($table->hasIndex('idx_uuid')) {
                    $table->removeIndex('idx_uuid')->update();
                }
                
                // 删除字段
                $table->removeColumn('uuid')->update();
                
                $this->output->writeln('成功删除 blog_like 表中的 uuid 字段');
            } else {
                $this->output->writeln('uuid 字段不存在于 blog_like 表中');
            }
        } else {
            $this->output->writeln('blog_like 表不存在');
        }
    }
}
