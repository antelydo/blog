<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddFaviconToWebsiteConfig extends Migrator
{
    /**
     * 添加Favicon字段到网站配置表
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
        
        // 检查Favicon配置是否已存在
        $existingConfig = $this->getAdapter()->fetchRow("SELECT * FROM `{$tableName}` WHERE `name` = 'Favicon'");
        
        if (!$existingConfig) {
            // 如果配置不存在，则插入
            $config = [
                'name' => 'Favicon',
                'value' => '',
                'group' => 'basic',
                'title' => '网站图标',
                'type' => 'image',
                'options' => '',
                'sort' => 3,
                'remark' => '网站浏览器标签页图标',
                'create_time' => $time,
                'update_time' => $time
            ];
            
            $sql = "INSERT INTO `{$tableName}` (`name`, `value`, `group`, `title`, `type`, `options`, `sort`, `remark`, `create_time`, `update_time`) 
                    VALUES ('{$config['name']}', '{$config['value']}', '{$config['group']}', '{$config['title']}', '{$config['type']}', 
                    '{$config['options']}', {$config['sort']}, '{$config['remark']}', {$config['create_time']}, {$config['update_time']})";
            
            $this->getAdapter()->query($sql);
            $this->output->writeln("Added config: {$config['name']}");
        } else {
            // 如果配置已存在，输出提示信息
            $this->output->writeln("Config already exists: Favicon");
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
        $this->getAdapter()->query("DELETE FROM `{$tableName}` WHERE `name` = 'Favicon'");
        $this->output->writeln("Removed config: Favicon");
    }
}
