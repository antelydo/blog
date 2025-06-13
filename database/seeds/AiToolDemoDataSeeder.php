<?php

use think\migration\Seeder;

class AiToolDemoDataSeeder extends Seeder
{
    /**
     * 运行数据填充
     *
     * @return void
     */
    public function run()
    {
        // 获取SQL文件路径
        $sqlFile = $this->getSqlFilePath();
        
        // 检查文件是否存在
        if (!file_exists($sqlFile)) {
            $this->output->error("SQL文件不存在: {$sqlFile}");
            return;
        }
        
        // 读取SQL文件内容
        $sql = file_get_contents($sqlFile);
        
        // 分割SQL语句
        $statements = $this->splitSqlFile($sql);
        
        // 执行SQL语句
        $this->executeSqlStatements($statements);
        
        // 输出完成信息
        $this->output->info('AI工具模拟数据填充完成！');
    }
    
    /**
     * 获取SQL文件路径
     *
     * @return string
     */
    protected function getSqlFilePath()
    {
        return __DIR__ . '/ai_tool_demo_data.sql';
    }
    
    /**
     * 分割SQL文件为单独的语句
     *
     * @param string $sql
     * @return array
     */
    protected function splitSqlFile($sql)
    {
        // 处理SOURCE语句
        $sql = preg_replace_callback('/SOURCE\s+([^;]+);/i', function($matches) {
            $includedFile = __DIR__ . '/' . basename($matches[1]);
            if (file_exists($includedFile)) {
                return file_get_contents($includedFile);
            }
            return '';
        }, $sql);
        
        // 移除注释
        $sql = preg_replace(['/#.*/', '/-- .*/', '/\/\*.*?\*\//s'], '', $sql);
        
        // 分割为单独的语句
        $statements = [];
        $currentStatement = '';
        
        foreach (explode("\n", $sql) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $currentStatement .= ' ' . $line;
            
            if (substr($line, -1) === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }
        
        // 添加最后一个语句（如果没有分号结尾）
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }
        
        return array_filter($statements);
    }
    
    /**
     * 执行SQL语句
     *
     * @param array $statements
     * @return void
     */
    protected function executeSqlStatements($statements)
    {
        $db = $this->getConnection();
        $count = 0;
        
        foreach ($statements as $statement) {
            try {
                if (!empty(trim($statement))) {
                    $db->execute($statement);
                    $count++;
                }
            } catch (\Exception $e) {
                $this->output->error("执行SQL语句失败: " . $e->getMessage());
                $this->output->error("问题语句: " . $statement);
            }
        }
        
        $this->output->info("成功执行 {$count} 条SQL语句");
    }
}
