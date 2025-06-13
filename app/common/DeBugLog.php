<?php
declare(strict_types=1);

namespace app\common;

use think\facade\Db;
use think\facade\Request;
use think\facade\Log;
use think\facade\Config;

/**
 * 调试工具类
 * 用于记录控制器和回调函数的调试信息
 */
class DeBugLog
{
    /**
     * 记录调试信息
     *
     * @param string $type 调试类型（如controller, callback等）
     * @param string $name 控制器名称或回调函数名称
     * @param array $params 参数
     * @param mixed $result 执行结果
     * @param int $executionTime 执行时间（毫秒）
     * @param string $remark 备注
     * @return int|bool 成功返回记录ID，失败返回false
     */
    public static function record(string $type, string $name, array $params = [], $result = null, int $executionTime = 0, string $remark = ''): int|bool
    {
        try {
            // 检查是否启用调试记录
            if (!self::isEnabled()) {
                return false;
            }

            // 初始化数据库连接
            try {
                Db::connect();
            } catch (\Exception $e) {
                Log::error('调试日志数据库连接失败: ' . $e->getMessage());
                return false;
            }

            // 获取当前用户信息
            $userId = 0;
            $userType = '';

            // 尝试从请求中获取用户信息
            $user = Request::middleware('admin');
            if ($user) {
                $userId = $user['id'] ?? 0;
                $userType = 'admin';
            } else {
                $user = Request::middleware('user');
                if ($user) {
                    $userId = $user['id'] ?? 0;
                    $userType = 'user';
                }
            }

            // 准备数据
            $data = [
                'type' => $type,
                'name' => $name,
                'params' => self::formatParams($params),
                'result' => self::formatResult($result),
                'execution_time' => $executionTime,
                'ip' => Request::ip(),
                'user_id' => $userId,
                'user_type' => $userType,
                'request_url' => Request::url(true),
                'request_method' => Request::method(),
                'create_time' => time(),
                'remark' => $remark
            ];

            // 检查表是否存在
            try {
                $tableExists = Db::query("SHOW TABLES LIKE 'ai_debug_logs'");
                if (empty($tableExists)) {
                    Log::error('调试日志表不存在，请先运行数据库迁移');
                    return false;
                }

                // 插入数据库
                return Db::name('debug_logs')->insert($data);
            } catch (\Exception $e) {
                Log::error('插入调试日志失败: ' . $e->getMessage());
                return false;
            }
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('调试信息记录失败: ' . $e->getMessage(), [
                'type' => $type,
                'name' => $name,
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * 记录控制器调用
     *
     * @param string $controller 控制器名称
     * @param string $action 方法名称
     * @param array $params 参数
     * @param mixed $result 执行结果
     * @param int $executionTime 执行时间（毫秒）
     * @param string $remark 备注
     * @return int|bool 成功返回记录ID，失败返回false
     */
    public static function recordController(string $controller, string $action, array $params = [], $result = null, int $executionTime = 0, string $remark = ''): int|bool
    {
        $name = $controller . '@' . $action;
        return self::record('controller', $name, $params, $result, $executionTime, $remark);
    }

    /**
     * 记录回调函数调用
     *
     * @param string $callback 回调函数名称
     * @param array $params 参数
     * @param mixed $result 执行结果
     * @param int $executionTime 执行时间（毫秒）
     * @param string $remark 备注
     * @return int|bool 成功返回记录ID，失败返回false
     */
    public static function recordCallback(string $callback, array $params = [], $result = null, int $executionTime = 0, string $remark = ''): int|bool
    {
        return self::record('callback', $callback, $params, $result, $executionTime, $remark);
    }

    /**
     * 记录API调用
     *
     * @param string $api API名称
     * @param array $params 参数
     * @param mixed $result 执行结果
     * @param int $executionTime 执行时间（毫秒）
     * @param string $remark 备注
     * @return int|bool 成功返回记录ID，失败返回false
     */
    public static function recordApi(string $api, array $params = [], $result = null, int $executionTime = 0, string $remark = ''): int|bool
    {
        return self::record('api', $api, $params, $result, $executionTime, $remark);
    }

    /**
     * 记录SQL查询
     *
     * @param string $sql SQL语句
     * @param array $params 参数
     * @param mixed $result 执行结果
     * @param int $executionTime 执行时间（毫秒）
     * @param string $remark 备注
     * @return int|bool 成功返回记录ID，失败返回false
     */
    public static function recordSql(string $sql, array $params = [], $result = null, int $executionTime = 0, string $remark = ''): int|bool
    {
        return self::record('sql', $sql, $params, $result, $executionTime, $remark);
    }

    /**
     * 获取调试日志列表
     *
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string $order 排序
     * @return array 调试日志列表
     */
    public static function getList(array $where = [], int $page = 1, int $limit = 20, string $order = 'create_time desc'): array
    {
        try {
            $count = Db::name('debug_logs')
                ->where($where)
                ->count();

            $list = Db::name('debug_logs')
                ->where($where)
                ->page($page, $limit)
                ->order($order)
                ->select()
                ->toArray();

            // 处理数据
            foreach ($list as &$item) {
                // 解析JSON数据
                $item['params'] = self::parseJson($item['params']);
                $item['result'] = self::parseJson($item['result']);

                // 格式化时间
                $item['create_time_format'] = date('Y-m-d H:i:s', $item['create_time']);
            }

            return [
                'total' => $count,
                'list' => $list,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (\Exception $e) {
            Log::error('获取调试日志失败: ' . $e->getMessage());
            return [
                'total' => 0,
                'list' => [],
                'page' => $page,
                'limit' => $limit
            ];
        }
    }

    /**
     * 获取单条调试日志
     *
     * @param int $id 日志ID
     * @return array|null 调试日志
     */
    public static function getInfo(int $id): ?array
    {
        try {
            $info = Db::name('debug_logs')
                ->where('id', $id)
                ->find();

            if ($info) {
                // 解析JSON数据
                $info['params'] = self::parseJson($info['params']);
                $info['result'] = self::parseJson($info['result']);

                // 格式化时间
                $info['create_time_format'] = date('Y-m-d H:i:s', $info['create_time']);
            }

            return $info;
        } catch (\Exception $e) {
            Log::error('获取调试日志详情失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 清除调试日志
     *
     * @param array $where 查询条件
     * @return bool 是否成功
     */
    public static function clear(array $where = []): bool
    {
        try {
            Db::name('debug_logs')
                ->where($where)
                ->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('清除调试日志失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 清除过期的调试日志
     *
     * @param int $days 保留天数
     * @return bool 是否成功
     */
    public static function clearExpired(int $days = 30): bool
    {
        try {
            $time = time() - ($days * 86400);

            Db::name('debug_logs')
                ->where('create_time', '<', $time)
                ->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('清除过期调试日志失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 导出调试日志
     *
     * @param array $where 查询条件
     * @param string $format 导出格式（json, csv）
     * @return string|null 导出的数据
     */
    public static function export(array $where = [], string $format = 'json'): ?string
    {
        try {
            $list = Db::name('debug_logs')
                ->where($where)
                ->order('create_time desc')
                ->select()
                ->toArray();

            // 处理数据
            foreach ($list as &$item) {
                // 解析JSON数据
                $item['params'] = self::parseJson($item['params']);
                $item['result'] = self::parseJson($item['result']);

                // 格式化时间
                $item['create_time_format'] = date('Y-m-d H:i:s', $item['create_time']);
            }

            // 根据格式导出
            if ($format === 'json') {
                return json_encode($list, JSON_UNESCAPED_UNICODE);
            } elseif ($format === 'csv') {
                return self::exportToCsv($list);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('导出调试日志失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 导出为CSV格式
     *
     * @param array $data 数据
     * @return string CSV数据
     */
    protected static function exportToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $header = array_keys($data[0]);
        $csv = implode(',', $header) . "\n";

        foreach ($data as $row) {
            $line = [];
            foreach ($row as $value) {
                // 处理数组和对象
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                // 处理CSV特殊字符
                $value = str_replace('"', '""', $value);
                $line[] = '"' . $value . '"';
            }

            $csv .= implode(',', $line) . "\n";
        }

        return $csv;
    }

    /**
     * 格式化参数
     *
     * @param array $params 参数
     * @return string JSON字符串
     */
    protected static function formatParams(array $params): string
    {
        // 过滤敏感信息
        $filteredParams = self::filterSensitiveData($params);

        // 转换为JSON
        return json_encode($filteredParams, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 格式化结果
     *
     * @param mixed $result 结果
     * @return string JSON字符串
     */
    protected static function formatResult($result): string
    {
        // 如果是对象，尝试转换为数组
        if (is_object($result)) {
            if (method_exists($result, 'toArray')) {
                $result = $result->toArray();
            } else {
                $result = (array) $result;
            }
        }

        // 如果是数组，过滤敏感信息
        if (is_array($result)) {
            $result = self::filterSensitiveData($result);
        }

        // 转换为JSON
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 过滤敏感数据
     *
     * @param array $data 数据
     * @return array 过滤后的数据
     */
    protected static function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'pwd', 'pass', 'secret', 'token', 'auth', 'key', 'api_key', 'apikey',
            'access_token', 'refresh_token', 'private', 'secret_key', 'credential'
        ];

        foreach ($data as $key => $value) {
            // 检查键名是否包含敏感词
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (stripos($key, $sensitiveKey) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                // 替换敏感数据
                $data[$key] = '******';
            } elseif (is_array($value)) {
                // 递归处理嵌套数组
                $data[$key] = self::filterSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * 解析JSON字符串
     *
     * @param string|null $json JSON字符串
     * @return array|string 解析后的数据
     */
    protected static function parseJson(?string $json)
    {
        if (empty($json)) {
            return [];
        }

        $data = json_decode($json, true);

        return $data !== null ? $data : $json;
    }

    /**
     * 检查是否启用调试记录
     *
     * @return bool 是否启用
     */
    protected static function isEnabled(): bool
    {
        // 先检查debug配置文件中的enabled设置
        $enabled = Config::get('debug.enabled', null);
        if ($enabled !== null) {
            return $enabled;
        }

        // 如果没有找到，则检查app配置文件中的debug_log_enabled设置
        return Config::get('app.debug_log_enabled', false);
    }

    /**
     * 创建调试记录装饰器
     * 用于包装方法并记录其执行情况
     *
     * @param string $type 调试类型
     * @param string $name 名称
     * @param callable $callback 回调函数
     * @param string $remark 备注
     * @return callable 包装后的回调函数
     */
    public static function createDecorator(string $type, string $name, callable $callback, string $remark = ''): callable
    {
        return function (...$args) use ($type, $name, $callback, $remark) {
            $startTime = microtime(true);

            try {
                $result = $callback(...$args);
                $executionTime = round((microtime(true) - $startTime) * 1000);

                self::record($type, $name, $args, $result, $executionTime, $remark);

                return $result;
            } catch (\Exception $e) {
                $executionTime = round((microtime(true) - $startTime) * 1000);

                self::record($type, $name, $args, [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], $executionTime, $remark);

                throw $e;
            }
        };
    }

    /**
     * 创建控制器方法装饰器
     *
     * @param string $controller 控制器名称
     * @param string $action 方法名称
     * @param callable $callback 回调函数
     * @param string $remark 备注
     * @return callable 包装后的回调函数
     */
    public static function createControllerDecorator(string $controller, string $action, callable $callback, string $remark = ''): callable
    {
        $name = $controller . '@' . $action;
        return self::createDecorator('controller', $name, $callback, $remark);
    }
}
