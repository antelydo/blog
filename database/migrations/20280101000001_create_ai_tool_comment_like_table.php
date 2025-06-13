<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolCommentLikeTable extends Migrator
{
    /**
     * 创建工具评价点赞记录表
     */
    public function up()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 创建工具评价点赞表
        $tableName = 'ai_tool_comment_like';
        if (!$this->hasTable($tableName)) {
            $table = $this->table($tableName, [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '工具评价点赞记录表'
            ]);
            
            $table
                ->addColumn('id', 'integer', ['signed' => false, 'identity' => true, 'comment' => '主键ID'])
                ->addColumn('comment_id', 'integer', ['signed' => false, 'comment' => '评价ID'])
                ->addColumn('user_id', 'integer', ['signed' => false, 'default' => 0, 'comment' => '用户ID，0表示访客'])
                ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'guest', 'comment' => '用户类型：admin=管理员, user=注册用户, guest=访客'])
                ->addColumn('uuid', 'string', ['limit' => 50, 'default' => '', 'comment' => '访客唯一标识，用于识别未登录用户'])
                ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP地址'])
                ->addColumn('user_agent', 'string', ['limit' => 255, 'default' => '', 'comment' => '用户代理信息'])
                ->addColumn('create_time', 'integer', ['signed' => false, 'default' => 0, 'comment' => '创建时间'])
                ->addIndex(['comment_id'], ['name' => 'idx_comment_id'])
                ->addIndex(['user_id'], ['name' => 'idx_user_id'])
                ->addIndex(['uuid'], ['name' => 'idx_uuid'])
                ->addIndex(['create_time'], ['name' => 'idx_create_time'])
                ->addIndex(['comment_id', 'user_id', 'user_type', 'uuid'], ['unique' => true, 'name' => 'uk_comment_user'])
                ->create();
                
            $this->output->writeln('成功创建工具评价点赞记录表');
        } else {
            $this->output->writeln('工具评价点赞记录表已存在');
        }
        
        // 在工具评价表中添加点赞数字段
        $commentTableName = 'ai_tool_comment';
        if ($this->hasTable($commentTableName)) {
            // 检查点赞数字段是否已存在
            $columns = $this->getAdapter()->fetchAll("SHOW COLUMNS FROM `{$prefix}{$commentTableName}` LIKE 'likes'");
            if (empty($columns)) {
                $this->table($commentTableName)
                    ->addColumn('likes', 'integer', ['signed' => false, 'default' => 0, 'comment' => '点赞数'])
                    ->update();
                $this->output->writeln('成功添加点赞数字段到工具评价表');
            } else {
                $this->output->writeln('点赞数字段已存在于工具评价表中');
            }
        } else {
            $this->output->writeln('工具评价表不存在，无法添加点赞数字段');
        }
    }

    /**
     * 删除工具评价点赞记录表
     */
    public function down()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 删除工具评价点赞表
        $tableName = 'ai_tool_comment_like';
        if ($this->hasTable($tableName)) {
            $this->table($tableName)->drop()->save();
            $this->output->writeln('成功删除工具评价点赞记录表');
        } else {
            $this->output->writeln('工具评价点赞记录表不存在');
        }
        
        // 从工具评价表中删除点赞数字段
        $commentTableName = 'ai_tool_comment';
        if ($this->hasTable($commentTableName)) {
            // 检查点赞数字段是否存在
            $columns = $this->getAdapter()->fetchAll("SHOW COLUMNS FROM `{$prefix}{$commentTableName}` LIKE 'likes'");
            if (!empty($columns)) {
                $this->table($commentTableName)
                    ->removeColumn('likes')
                    ->update();
                $this->output->writeln('成功从工具评价表中删除点赞数字段');
            } else {
                $this->output->writeln('点赞数字段不存在于工具评价表中');
            }
        } else {
            $this->output->writeln('工具评价表不存在');
        }
    }
}
