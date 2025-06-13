<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddIntroductionToUser extends Migrator
{
    public function change()
    {
        // 输出当前执行环境信息
        $this->output->writeln('----- 开始执行迁移 -----');
        $this->output->writeln('当前工作目录: ' . getcwd());
        
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        $this->output->writeln('数据库表前缀: ' . ($prefix ?: '无'));
        
        // 获取可能的表名称列表（带前缀和不带前缀）
        $tableNames = ['user'];
        if ($prefix) {
            $tableNames[] = $prefix.'user';
        }
        
        $this->output->writeln('尝试查找的表名: ' . implode(', ', $tableNames));
        
        // 尝试所有可能的表名
        $tableExists = false;
        $actualTableName = '';
        
        foreach ($tableNames as $tableName) {
            $this->output->writeln('检查表 [' . $tableName . '] 是否存在...');
            if ($this->hasTable($tableName)) {
                $tableExists = true;
                $actualTableName = $tableName;
                $this->output->writeln('找到表: ' . $tableName);
                break;
            } else {
                $this->output->writeln('表 [' . $tableName . '] 不存在');
            }
        }
        
        if ($tableExists) {
            // 添加introduction字段到user表
            $table = $this->table($actualTableName);
            $this->output->writeln('准备修改表: ' . $actualTableName);
            
            // 检查字段是否已存在
            $columns = $table->getColumns();
            $columnNames = array_map(function($column) {
                return $column->getName();
            }, $columns);
            
            $this->output->writeln('现有字段: ' . implode(', ', $columnNames));
            
            if (!in_array('introduction', $columnNames)) {
                $this->output->writeln('准备添加字段: introduction');
                
                try {
                    $table->addColumn('introduction', 'text', [
                        'null' => true,
                        'comment' => '个人简介',
                        'after' => 'birthday' // 在birthday字段后添加
                    ])->update();
                    
                    // 输出日志信息
                    $this->output->writeln('成功添加个人简介字段(introduction)到用户表('.$actualTableName.')');
                } catch (\Exception $e) {
                    $this->output->writeln('添加字段失败: ' . $e->getMessage());
                }
            } else {
                $this->output->writeln('个人简介字段(introduction)已存在于用户表('.$actualTableName.')中');
            }
        } else {
            // 列出所有表，帮助调试
            try {
                $tables = $this->fetchAll('SHOW TABLES');
                $tableList = array_map(function($table) {
                    return array_values($table)[0];
                }, $tables);
                
                $this->output->writeln('用户表不存在，无法添加字段');
                $this->output->writeln('当前数据库中的表: ' . implode(', ', $tableList));
            } catch (\Exception $e) {
                $this->output->writeln('获取数据库表列表失败: ' . $e->getMessage());
            }
        }
        
        $this->output->writeln('----- 迁移执行完毕 -----');
    }
} 