<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Config;

/**
 * 性能监控命令
 */
class Performance extends Command
{
    /**
     * 配置指令
     */
    protected function configure()
    {
        $this->setName('performance')
            ->addArgument('action', Argument::OPTIONAL, '操作类型: status, optimize, clear-cache, analyze-db', 'status')
            ->addOption('detail', 'd', Option::VALUE_NONE, '显示详细信息')
            ->setDescription('性能监控和优化工具');
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     * @return int
     */
    protected function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        $detail = $input->getOption('detail');
        
        switch ($action) {
            case 'status':
                $this->showStatus($output, $detail);
                break;
                
            case 'optimize':
                $this->optimize($output);
                break;
                
            case 'clear-cache':
                $this->clearCache($output);
                break;
                
            case 'analyze-db':
                $this->analyzeDb($output, $detail);
                break;
                
            default:
                $output->error('未知操作: ' . $action);
                return 1;
        }
        
        return 0;
    }
    
    /**
     * 显示系统状态
     * @param Output $output
     * @param bool $detail 是否显示详细信息
     */
    protected function showStatus(Output $output, bool $detail)
    {
        $output->writeln('系统性能状态:');
        $output->writeln('-------------');
        
        // PHP信息
        $output->writeln('<info>PHP版本:</info> ' . PHP_VERSION);
        $output->writeln('<info>内存限制:</info> ' . ini_get('memory_limit'));
        $output->writeln('<info>最大执行时间:</info> ' . ini_get('max_execution_time') . '秒');
        
        // OPCache状态
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status(false);
            $output->writeln('<info>OPCache:</info> ' . ($opcacheStatus['opcache_enabled'] ? '已启用' : '未启用'));
            
            if ($opcacheStatus['opcache_enabled']) {
                $memoryUsage = $opcacheStatus['memory_usage'];
                $memoryUsedPercent = round(($memoryUsage['used_memory'] / $memoryUsage['total_memory']) * 100, 2);
                
                $output->writeln('<info>OPCache内存使用:</info> ' . $this->formatBytes($memoryUsage['used_memory']) . ' / ' . $this->formatBytes($memoryUsage['total_memory']) . ' (' . $memoryUsedPercent . '%)');
                
                if ($detail) {
                    $output->writeln('<info>OPCache命中率:</info> ' . round($opcacheStatus['opcache_statistics']['opcache_hit_rate'], 2) . '%');
                    $output->writeln('<info>OPCache缓存脚本数:</info> ' . $opcacheStatus['opcache_statistics']['num_cached_scripts']);
                }
            }
        } else {
            $output->writeln('<info>OPCache:</info> 未安装');
        }
        
        // 数据库连接信息
        try {
            $dbConfig = Config::get('database.connections.' . Config::get('database.default'));
            $output->writeln('<info>数据库类型:</info> ' . $dbConfig['type']);
            $output->writeln('<info>数据库主机:</info> ' . $dbConfig['hostname'] . ':' . $dbConfig['hostport']);
            $output->writeln('<info>数据库名:</info> ' . $dbConfig['database']);
            $output->writeln('<info>数据库字段缓存:</info> ' . ($dbConfig['fields_cache'] ? '已启用' : '未启用'));
            $output->writeln('<info>数据库持久连接:</info> ' . (isset($dbConfig['persistent']) && $dbConfig['persistent'] ? '已启用' : '未启用'));
            
            if ($detail) {
                // 获取数据库状态
                $dbStatus = Db::query('SHOW STATUS');
                $dbStatusMap = [];
                foreach ($dbStatus as $item) {
                    $dbStatusMap[$item['Variable_name']] = $item['Value'];
                }
                
                $output->writeln('<info>数据库连接数:</info> ' . ($dbStatusMap['Threads_connected'] ?? '未知'));
                $output->writeln('<info>数据库最大连接数:</info> ' . ($dbStatusMap['max_connections'] ?? '未知'));
                $output->writeln('<info>数据库查询缓存命中率:</info> ' . (isset($dbStatusMap['Qcache_hits'], $dbStatusMap['Com_select']) && $dbStatusMap['Com_select'] > 0 ? round(($dbStatusMap['Qcache_hits'] / ($dbStatusMap['Qcache_hits'] + $dbStatusMap['Com_select'])) * 100, 2) . '%' : '未知'));
            }
        } catch (\Exception $e) {
            $output->writeln('<error>获取数据库信息失败: ' . $e->getMessage() . '</error>');
        }
        
        // 缓存信息
        $cacheConfig = Config::get('cache');
        $output->writeln('<info>缓存驱动:</info> ' . $cacheConfig['default']);
        
        if ($cacheConfig['default'] === 'redis') {
            $redisConfig = $cacheConfig['stores']['redis'];
            $output->writeln('<info>Redis主机:</info> ' . $redisConfig['host'] . ':' . $redisConfig['port']);
            $output->writeln('<info>Redis数据库:</info> ' . $redisConfig['select']);
            $output->writeln('<info>Redis连接池大小:</info> ' . ($redisConfig['pool_size'] ?? '未设置'));
            $output->writeln('<info>Redis持久连接:</info> ' . (isset($redisConfig['persistent']) && $redisConfig['persistent'] ? '已启用' : '未启用'));
        }
        
        // 会话信息
        $sessionConfig = Config::get('session');
        $output->writeln('<info>会话驱动:</info> ' . $sessionConfig['default']);
        $output->writeln('<info>会话有效期:</info> ' . $sessionConfig['expire'] . '秒');
        
        // 路由信息
        $routeConfig = Config::get('route');
        $output->writeln('<info>路由缓存:</info> ' . (isset($routeConfig['route_check_cache']) && $routeConfig['route_check_cache'] ? '已启用' : '未启用'));
        
        // 应用信息
        $appConfig = Config::get('app');
        $output->writeln('<info>调试模式:</info> ' . ($appConfig['debug'] ? '已启用' : '未启用'));
        $output->writeln('<info>应用跟踪:</info> ' . ($appConfig['app_trace'] ? '已启用' : '未启用'));
        
        // 性能配置信息
        $performanceConfig = Config::get('performance');
        if ($performanceConfig) {
            $output->writeln('<info>页面缓存:</info> ' . ($performanceConfig['page_cache_enabled'] ? '已启用' : '未启用'));
            $output->writeln('<info>性能监控:</info> ' . ($performanceConfig['performance_monitor_enabled'] ? '已启用' : '未启用'));
        }
    }
    
    /**
     * 优化系统
     * @param Output $output
     */
    protected function optimize(Output $output)
    {
        $output->writeln('执行系统优化:');
        $output->writeln('-------------');
        
        // 清除运行时缓存
        $output->write('清除运行时缓存... ');
        $runtimePath = app()->getRuntimePath();
        $this->removeDir($runtimePath . 'cache');
        $this->removeDir($runtimePath . 'temp');
        $output->writeln('<info>完成</info>');
        
        // 生成路由缓存
        $output->write('生成路由缓存... ');
        try {
            $routePath = $runtimePath . 'route' . DIRECTORY_SEPARATOR;
            if (!is_dir($routePath)) {
                mkdir($routePath, 0755, true);
            }
            
            // 这里不直接调用路由缓存生成，因为可能会改变现有逻辑
            // 仅创建目录结构
            $output->writeln('<info>目录已准备</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>失败: ' . $e->getMessage() . '</error>');
        }
        
        // 生成数据库字段缓存目录
        $output->write('准备数据库字段缓存... ');
        try {
            $schemaPath = $runtimePath . 'schema' . DIRECTORY_SEPARATOR;
            if (!is_dir($schemaPath)) {
                mkdir($schemaPath, 0755, true);
            }
            $output->writeln('<info>目录已准备</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>失败: ' . $e->getMessage() . '</error>');
        }
        
        // 优化数据库
        $output->write('优化数据库表... ');
        try {
            // 获取所有表
            $tables = Db::query('SHOW TABLES');
            $tableCount = count($tables);
            $optimizedCount = 0;
            
            foreach ($tables as $table) {
                $tableName = current($table);
                try {
                    Db::query("OPTIMIZE TABLE `{$tableName}`");
                    $optimizedCount++;
                } catch (\Exception $e) {
                    // 忽略错误，继续优化其他表
                }
            }
            
            $output->writeln("<info>完成 ({$optimizedCount}/{$tableCount})</info>");
        } catch (\Exception $e) {
            $output->writeln('<error>失败: ' . $e->getMessage() . '</error>');
        }
        
        // 创建性能配置文件
        $output->write('检查性能配置文件... ');
        $performanceConfigFile = app()->getConfigPath() . 'performance.php';
        if (!file_exists($performanceConfigFile)) {
            $output->writeln('<comment>未找到，请手动创建</comment>');
        } else {
            $output->writeln('<info>已存在</info>');
        }
        
        $output->writeln('');
        $output->writeln('<info>系统优化完成</info>');
        $output->writeln('');
        $output->writeln('建议的优化步骤:');
        $output->writeln('1. 启用OPCache (php.ini中设置 opcache.enable=1)');
        $output->writeln('2. 启用Redis缓存 (在.env文件中设置 CACHE_DRIVER=redis)');
        $output->writeln('3. 启用Redis会话存储 (在.env文件中设置 SESSION_DRIVER=redis)');
        $output->writeln('4. 启用数据库字段缓存 (在.env文件中设置 DB_FIELDS_CACHE=true)');
        $output->writeln('5. 启用路由缓存 (在.env文件中设置 ROUTE_CHECK_CACHE=true)');
        $output->writeln('6. 禁用调试模式和应用跟踪 (在.env文件中设置 APP_DEBUG=false APP_TRACE=false)');
    }
    
    /**
     * 清除缓存
     * @param Output $output
     */
    protected function clearCache(Output $output)
    {
        $output->writeln('清除系统缓存:');
        $output->writeln('-------------');
        
        // 清除运行时缓存
        $output->write('清除运行时缓存... ');
        $runtimePath = app()->getRuntimePath();
        $this->removeDir($runtimePath . 'cache');
        $this->removeDir($runtimePath . 'temp');
        $output->writeln('<info>完成</info>');
        
        // 清除路由缓存
        $output->write('清除路由缓存... ');
        $this->removeDir($runtimePath . 'route');
        $output->writeln('<info>完成</info>');
        
        // 清除数据库字段缓存
        $output->write('清除数据库字段缓存... ');
        $this->removeDir($runtimePath . 'schema');
        $output->writeln('<info>完成</info>');
        
        // 清除应用缓存
        $output->write('清除应用缓存... ');
        try {
            Cache::clear();
            $output->writeln('<info>完成</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>失败: ' . $e->getMessage() . '</error>');
        }
        
        // 清除日志文件
        $output->write('清除过期日志文件... ');
        $logPath = $runtimePath . 'log' . DIRECTORY_SEPARATOR;
        $now = time();
        $count = 0;
        
        if (is_dir($logPath)) {
            $files = glob($logPath . '*.log');
            foreach ($files as $file) {
                // 删除30天前的日志文件
                if (filemtime($file) < ($now - 30 * 86400)) {
                    unlink($file);
                    $count++;
                }
            }
        }
        
        $output->writeln("<info>完成 (删除{$count}个文件)</info>");
        
        $output->writeln('');
        $output->writeln('<info>缓存清除完成</info>');
    }
    
    /**
     * 分析数据库
     * @param Output $output
     * @param bool $detail 是否显示详细信息
     */
    protected function analyzeDb(Output $output, bool $detail)
    {
        $output->writeln('数据库分析:');
        $output->writeln('-----------');
        
        try {
            // 获取所有表
            $tables = Db::query('SHOW TABLES');
            $tableCount = count($tables);
            
            $output->writeln("<info>数据库表数量:</info> {$tableCount}");
            
            // 获取数据库大小
            $dbSize = 0;
            $tableInfo = [];
            
            foreach ($tables as $table) {
                $tableName = current($table);
                $status = Db::query("SHOW TABLE STATUS LIKE '{$tableName}'")[0];
                
                $size = $status['Data_length'] + $status['Index_length'];
                $dbSize += $size;
                
                $tableInfo[] = [
                    'name' => $tableName,
                    'rows' => $status['Rows'],
                    'size' => $size,
                    'engine' => $status['Engine'],
                    'collation' => $status['Collation'],
                ];
            }
            
            $output->writeln("<info>数据库总大小:</info> " . $this->formatBytes($dbSize));
            
            // 显示最大的表
            usort($tableInfo, function($a, $b) {
                return $b['size'] - $a['size'];
            });
            
            $output->writeln('');
            $output->writeln('最大的表:');
            $output->writeln(str_repeat('-', 80));
            $output->writeln(sprintf('%-30s %-10s %-15s %-10s %-15s', '表名', '行数', '大小', '引擎', '排序规则'));
            $output->writeln(str_repeat('-', 80));
            
            $limit = $detail ? count($tableInfo) : min(10, count($tableInfo));
            
            for ($i = 0; $i < $limit; $i++) {
                $table = $tableInfo[$i];
                $output->writeln(sprintf(
                    '%-30s %-10s %-15s %-10s %-15s',
                    $table['name'],
                    number_format($table['rows']),
                    $this->formatBytes($table['size']),
                    $table['engine'],
                    $table['collation']
                ));
            }
            
            // 检查索引
            if ($detail) {
                $output->writeln('');
                $output->writeln('缺少索引的表:');
                $output->writeln(str_repeat('-', 80));
                
                $noIndexTables = [];
                
                foreach ($tableInfo as $table) {
                    $indexes = Db::query("SHOW INDEX FROM `{$table['name']}`");
                    
                    if (count($indexes) <= 1) { // 只有主键
                        $noIndexTables[] = $table['name'];
                    }
                }
                
                if (empty($noIndexTables)) {
                    $output->writeln('没有发现缺少索引的表');
                } else {
                    foreach ($noIndexTables as $table) {
                        $output->writeln($table);
                    }
                }
            }
            
            // 检查慢查询
            $output->writeln('');
            $output->writeln('慢查询日志:');
            $output->writeln(str_repeat('-', 80));
            
            try {
                $slowQueryStatus = Db::query("SHOW VARIABLES LIKE 'slow_query_log'")[0]['Value'];
                $slowQueryTime = Db::query("SHOW VARIABLES LIKE 'long_query_time'")[0]['Value'];
                $slowQueryFile = Db::query("SHOW VARIABLES LIKE 'slow_query_log_file'")[0]['Value'];
                
                $output->writeln("<info>慢查询日志:</info> " . ($slowQueryStatus === 'ON' ? '已启用' : '未启用'));
                $output->writeln("<info>慢查询时间阈值:</info> {$slowQueryTime}秒");
                $output->writeln("<info>慢查询日志文件:</info> {$slowQueryFile}");
            } catch (\Exception $e) {
                $output->writeln('<error>获取慢查询信息失败: ' . $e->getMessage() . '</error>');
            }
            
        } catch (\Exception $e) {
            $output->writeln('<error>数据库分析失败: ' . $e->getMessage() . '</error>');
        }
    }
    
    /**
     * 递归删除目录
     * @param string $dir 目录路径
     * @return bool
     */
    protected function removeDir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                
                if (is_dir($path)) {
                    $this->removeDir($path);
                } else {
                    unlink($path);
                }
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * 格式化字节数
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
