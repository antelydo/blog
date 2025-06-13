<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddMissingWebsiteConfigs extends Migrator
{
    /**
     * 添加缺失的网站配置字段
     */
    public function up()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        $tableName = $prefix . 'website_config';
        
        // 检查表是否存在
        $tableExists = $this->getAdapter()->fetchRow("SHOW TABLES LIKE '{$tableName}'");
        if (!$tableExists) {
            $this->output->writeln("Table '{$tableName}' does not exist. Please run the initial migration first.");
            return;
        }
        
        $time = time();
        
        // 缺失的网站配置数据
        $configs = [
            // 注册功能配置
            [
                'name' => 'register_enabled',
                'value' => '0',
                'group' => 'user',
                'title' => '启用注册',
                'type' => 'radio',
                'options' => '{"1":"启用","0":"禁用"}',
                'sort' => 1,
                'remark' => '是否允许用户注册',
                'create_time' => $time,
                'update_time' => $time
            ],
            
            // 评论功能配置（如果已存在comment_enabled，这将更新它）
            [
                'name' => 'comment_enabled',
                'value' => '0',
                'group' => 'comment',
                'title' => '启用评论',
                'type' => 'radio',
                'options' => '{"1":"启用","0":"禁用"}',
                'sort' => 1,
                'remark' => '是否启用文章评论功能',
                'create_time' => $time,
                'update_time' => $time
            ],
            
            // 评论审核配置（如果已存在comment_audit，这将使用新名称comment_need_audit）
            [
                'name' => 'comment_need_audit',
                'value' => '1',
                'group' => 'comment',
                'title' => '评论需要审核',
                'type' => 'radio',
                'options' => '{"1":"启用","0":"禁用"}',
                'sort' => 2,
                'remark' => '新评论是否需要审核后才显示',
                'create_time' => $time,
                'update_time' => $time
            ]
        ];
        
        // 逐个插入配置，如果已存在则跳过
        foreach ($configs as $config) {
            // 检查配置是否已存在
            $existingConfig = $this->getAdapter()->fetchRow("SELECT * FROM `{$tableName}` WHERE `name` = '{$config['name']}'");
            
            if (!$existingConfig) {
                // 如果配置不存在，则插入
                $sql = "INSERT INTO `{$tableName}` (`name`, `value`, `group`, `title`, `type`, `options`, `sort`, `remark`, `create_time`, `update_time`) 
                        VALUES ('{$config['name']}', '{$config['value']}', '{$config['group']}', '{$config['title']}', '{$config['type']}', 
                        '{$config['options']}', {$config['sort']}, '{$config['remark']}', {$config['create_time']}, {$config['update_time']})";
                
                $this->getAdapter()->query($sql);
                $this->output->writeln("Added config: {$config['name']}");
            } else {
                // 如果配置已存在，可以选择更新它
                $this->output->writeln("Config already exists: {$config['name']}");
            }
        }
    }

    /**
     * 回滚操作
     */
    public function down()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        $tableName = $prefix . 'website_config';
        
        // 检查表是否存在
        $tableExists = $this->getAdapter()->fetchRow("SHOW TABLES LIKE '{$tableName}'");
        if (!$tableExists) {
            $this->output->writeln("Table '{$tableName}' does not exist.");
            return;
        }
        
        // 删除添加的配置
        $configNames = [
            'register_enabled',
            'comment_need_audit'
        ];
        
        foreach ($configNames as $name) {
            $this->getAdapter()->query("DELETE FROM `{$tableName}` WHERE `name` = '{$name}'");
            $this->output->writeln("Removed config: {$name}");
        }
        
        // 注意：我们不删除comment_enabled，因为它可能是之前就存在的
        $this->output->writeln("Note: 'comment_enabled' was not removed as it might have existed before this migration");
    }
}
