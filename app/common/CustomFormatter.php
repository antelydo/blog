<?php
/**
 * 自定义日志格式化器
 * 用于正确处理和显示日志中的context参数
 * 特别处理ThinkPHP 8中使用命名参数传递的context
 */
namespace app\common;

use think\contract\LogHandlerInterface;
use think\App;

class CustomFormatter implements LogHandlerInterface
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'time_format' => 'Y-m-d H:i:s',
        'file_size'   => 2097152,
        'path'        => '',
        'apart_level' => [],
        'json'        => true,
        'json_options' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ];

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 构造函数
     * @param App $app 应用实例
     * @param array $config 配置参数
     */
    public function __construct(App $app, array $config = [])
    {
        $this->app = $app;
        
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        
        if (empty($this->config['path'])) {
            $this->config['path'] = $app->getRuntimePath() . 'log';
        }
        
        if (substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log): bool
    {
        $destination = $this->getMasterLogFile();
        
        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);
        
        $info = [];
        
        // 日志信息封装
        $time = date($this->config['time_format']);
        
        foreach ($log as $type => $val) {
            $message = [];
            foreach ($val as $msg) {
                // 预处理日志消息，提取context
                $processedMsg = $this->processLogMessage($msg);
                
                $message[] = $this->config['json'] ?
                    json_encode(['time' => $time, 'type' => $type, 'msg' => $processedMsg], $this->config['json_options']) :
                    sprintf('[%s][%s] %s', $time, $type, $processedMsg);
            }
            
            if (true === $this->config['apart_level'] || in_array($type, $this->config['apart_level'])) {
                // 独立记录的日志级别
                $filename = $this->getApartLevelFile($path, $type);
                $this->write($message, $filename);
                continue;
            }
            
            $info[$type] = $message;
        }
        
        if ($info) {
            return $this->write($info, $destination);
        }
        
        return true;
    }
    
    /**
     * 处理日志消息，提取并格式化context
     * @param mixed $msg 日志消息
     * @return string 处理后的消息
     */
    protected function processLogMessage($msg): string
    {
        // 如果不是数组，直接转换为字符串返回
        if (!is_array($msg)) {
            return is_string($msg) ? $msg : var_export($msg, true);
        }
        
        // 记录原始消息用于调试
        // file_put_contents($this->config['path'] . 'debug_log.txt', var_export($msg, true) . PHP_EOL, FILE_APPEND);
        
        // 处理各种形式的context参数
        
        // 情况1: 处理命名参数形式 - Log::info('消息', context: [...])
        // 这种情况下，ThinkPHP会将其转换为特定格式
        if (isset($msg[0]) && is_string($msg[0]) && isset($msg['context']) && is_array($msg['context'])) {
            $messageContent = $msg[0];
            $context = $msg['context'];
            
            // 将context转换为可读格式，确保嵌套数组也能正确处理
            return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
        }
        
        // 情况2: 处理命名参数形式的另一种情况
        if (isset($msg['message']) && isset($msg['context'])) {
            $messageContent = $msg['message'];
            $context = $msg['context'];
            
            // 将context转换为可读格式，确保嵌套数组也能正确处理
            return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
        }
        
        // 情况3: 处理普通数组参数 - Log::info('消息', ['context' => ...])
        if (isset($msg[0]) && is_array($msg[0]) && isset($msg[0]['context'])) {
            $context = $msg[0]['context'];
            $messageContent = '';
            
            if (isset($msg[0]['message'])) {
                $messageContent = $msg[0]['message'];
            }
            
            // 将context转换为可读格式，确保嵌套数组也能正确处理
            return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
        }
        
        // 情况4: 处理ThinkPHP内部传递的数组形式日志
        if (count($msg) == 2 && !isset($msg['context']) && !isset($msg['message'])) {
            // 可能是 [$message, $context] 形式
            $messageContent = $msg[0] ?? '';
            $context = $msg[1] ?? [];
            
            // 确保context是数组
            if (is_array($context)) {
                // 将context转换为可读格式，确保嵌套数组也能正确处理
                return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
            }
            return $messageContent . ' ' . (is_string($context) ? $context : var_export($context, true));
        }
        
        // 情况5: 处理嵌套数组形式 - Log::info('消息', [ [...] ])
        // 例如: Log::info('消999999999999', [ ['1'=>4,'2'=> 5]])
        if (isset($msg[0]) && is_array($msg[0])) {
            $messageContent = '';
            if (isset($msg[0])) {
                $context = $msg[0];
                if (isset($msg[1])) {
                    $messageContent = $msg[1];
                } else {
                    // 第一个元素可能是消息，也可能是上下文
                    if (is_string(array_keys($msg)[0])) {
                        $messageContent = '';
                        $context = $msg;
                    } else {
                        $messageContent = '';
                        $context = $msg[0];
                    }
                }
                return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
            }
        }
        
        // 情况6: 处理简单键值对形式 - Log::info('消息', ['key' => 'value'])
        if (count($msg) >= 2 && is_string($msg[0])) {
            $messageContent = $msg[0];
            // 移除消息部分，剩下的都是context
            $context = $msg;
            unset($context[0]);
            
            if (!empty($context)) {
                return $messageContent . ' [Context: ' . $this->formatContext($context) . ']';
            }
            return $messageContent;
        }
        
        // 其他情况，直接转换为字符串
        return var_export($msg, true);
    }
    
    // 移除重复的formatContext方法，保留下方更完善的实现

    /**
     * 格式化上下文数据
     * @access protected
     * @param mixed $context 上下文数据
     * @return string 格式化后的字符串
     */
    protected function formatContext($context): string
    {
        if (is_null($context)) {
            return 'null';
        }
        
        if (is_scalar($context)) {
            return (string) $context;
        }
        
        if (is_array($context) || is_object($context)) {
            try {
                return json_encode($context, $this->config['json_options']);
            } catch (\Exception $e) {
                return 'Error encoding context: ' . $e->getMessage();
            }
        }
        
        return var_export($context, true);
    }
    
    /**
     * 日志写入
     * @access protected
     * @param array $message 日志信息
     * @param string $destination 日志文件
     * @return bool
     */
    protected function write(array $message, string $destination): bool
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        $this->checkLogSize($destination);
        
        $info = [];
        
        foreach ($message as $type => $msg) {
            $info[$type] = is_array($msg) ? implode(PHP_EOL, $msg) : $msg;
        }
        
        $message = implode(PHP_EOL, $info) . PHP_EOL;
        
        return error_log($message, 3, $destination);
    }

    /**
     * 获取主日志文件名
     * @access protected
     * @return string
     */
    protected function getMasterLogFile(): string
    {
        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $destination = $this->config['path'] . $name . '.log';
        } else {
            $filename = date('Ym') . DIRECTORY_SEPARATOR . date('d') . '.log';
            $destination = $this->config['path'] . $filename;
        }
        
        return $destination;
    }

    /**
     * 获取独立日志文件名
     * @access protected
     * @param string $path 日志目录
     * @param string $type 日志类型
     * @return string
     */
    protected function getApartLevelFile(string $path, string $type): string
    {
        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $name .= '_' . $type;
        } else {
            $name = date('d') . '_' . $type;
        }
        
        return $path . DIRECTORY_SEPARATOR . $name . '.log';
    }

    /**
     * 检查日志文件大小并自动生成备份文件
     * @access protected
     * @param string $destination 日志文件
     * @return void
     */
    protected function checkLogSize(string $destination): void
    {
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            try {
                rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
            } catch (\Exception $e) {
                // 创建失败不输出错误
            }
        }
    }
}