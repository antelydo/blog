<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\DeBugLog;
use think\facade\Request;
use think\Response;

/**
 * 调试演示控制器
 */
class DebugDemo
{
    /**
     * 演示记录控制器调用
     */
    /**
     * 返回JSON响应
     *
     * @param array $data 数据
     * @param int $code 状态码
     * @param string $msg 消息
     * @return Response
     */
    protected function json($data = [], $code = 200, $msg = '\think\facade\Lang::get("debug.success")')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];

        return Response::create($result, 'json');
    }

    public function controller()
    {
        // 这个方法的调用会被DebugLog中间件自动记录

        // 模拟一些处理
        sleep(1);

        try {
            // 手动记录调试信息，以防中间件未生效
            DebugLog::recordController('DebugDemo', 'controller', Request::param(), ['time' => date('Y-m-d H:i:s')
            ], 1000, '手动记录控制器调用');
        } catch (\Exception $e) {
            // 忽略记录失败的错误
        }

        return $this->json([
            'time' => date('Y-m-d H:i:s'),
            'params' => Request::param()
        ], 200, \think\facade\Lang::get('debug.controller_demo'));
    }

    /**
     * 演示记录回调函数
     */
    public function callback()
    {
        // 定义一个回调函数
        $callback = function($a, $b) {
            return $a + $b;
        };

        // 直接调用函数
        $result = $callback(10, 20);

        try {
            // 手动记录调试信息
            DebugLog::recordCallback('add_numbers', [10, 20], $result, 0, '演示回调函数记录');
        } catch (\Exception $e) {
            // 忽略记录失败的错误
        }

        return $this->json([
            'result' => $result,
            'time' => date('Y-m-d H:i:s')
        ], 200, \think\facade\Lang::get('debug.callback_demo'));
    }

    /**
     * 演示手动记录
     */
    public function manual()
    {
        // 获取参数
        $params = Request::param();

        // 记录开始时间
        $startTime = microtime(true);

        // 模拟一些处理
        sleep(1);
        $result = [
            'success' => true,
            'data' => [
                'message' => '手动记录演示',
                'time' => date('Y-m-d H:i:s')
            ]
        ];

        // 计算执行时间
        $executionTime = round((microtime(true) - $startTime) * 1000);

        try {
            // 手动记录调试信息
            DebugLog::record('manual', 'manual_demo', $params, $result, $executionTime, '手动记录演示');
        } catch (\Exception $e) {
            // 忽略记录失败的错误
        }

        return $this->json($result, 200, \think\facade\Lang::get('debug.manual_demo'));
    }

    /**
     * 演示记录API调用
     */
    public function api()
    {
        // 模拟API调用参数
        $apiParams = [
            'user_id' => 1,
            'action' => 'get_user_info',
            'timestamp' => time()
        ];

        // 记录开始时间
        $startTime = microtime(true);

        // 模拟API调用
        sleep(1);
        $apiResult = [
            'code' => 200,
            'message' => '成功',
            'data' => [
                'user_id' => 1,
                'username' => 'test_user',
                'email' => 'test@example.com',
                'created_at' => '2023-01-01 00:00:00'
            ]
        ];

        // 计算执行时间
        $executionTime = round((microtime(true) - $startTime) * 1000);

        try {
            // 记录API调用
            DebugLog::recordApi('get_user_info', $apiParams, $apiResult, $executionTime, 'API调用演示');
        } catch (\Exception $e) {
            // 忽略记录失败的错误
        }

        return $this->json($apiResult, 200, \think\facade\Lang::get('debug.api_demo'));
    }

    /**
     * 演示记录SQL查询
     */
    public function sql()
    {
        // 模拟SQL查询
        $sql = "SELECT * FROM users WHERE id = ? AND status = ?";
        $sqlParams = [1, 'active'];

        // 记录开始时间
        $startTime = microtime(true);

        // 模拟SQL执行
        sleep(1);
        $sqlResult = [
            [
                'id' => 1,
                'username' => 'test_user',
                'email' => 'test@example.com',
                'status' => 'active',
                'created_at' => '2023-01-01 00:00:00'
            ]
        ];

        // 计算执行时间
        $executionTime = round((microtime(true) - $startTime) * 1000);

        try {
            // 记录SQL查询
            DebugLog::recordSql($sql, $sqlParams, $sqlResult, $executionTime, 'SQL查询演示');
        } catch (\Exception $e) {
            // 忽略记录失败的错误
        }

        return $this->json($sqlResult, 200, \think\facade\Lang::get('debug.sql_demo'));
    }

    /**
     * 获取调试日志列表
     */
    public function list()
    {
        // 获取参数
        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 20);
        $type = Request::param('type/s', '');

        // 构建查询条件
        $where = [];
        if ($type) {
            $where[] = ['type', '=', $type];
        }

        try {
            // 获取调试日志列表
            $list = DebugLog::getList($where, $page, $limit);
            return $this->json($list, 200, \think\facade\Lang::get('debug.get_log_list_success'));
        } catch (\Exception $e) {
            // 如果出错，返回空列表
            return $this->json([
                'total' => 0,
                'list' => [],
                'page' => $page,
                'limit' => $limit
            ], 200, \think\facade\Lang::get('debug.get_log_list_success'));
        }
    }

    /**
     * 获取调试日志详情
     */
    public function info()
    {
        // 获取参数
        $id = Request::param('id/d', 0);

        try {
            // 获取调试日志详情
            $info = DebugLog::getInfo($id);

            if (!$info) {
                return $this->json([], 404, \think\facade\Lang::get('debug.log_not_exist'));
            }

            return $this->json($info, 200, \think\facade\Lang::get('debug.get_log_detail_success'));
        } catch (\Exception $e) {
            return $this->json([], 500, \think\facade\Lang::get('debug.get_log_detail_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * 清除调试日志
     */
    public function clear()
    {
        // 获取参数
        $type = Request::param('type/s', '');
        $days = Request::param('days/d', 30);

        // 构建查询条件
        $where = [];
        if ($type) {
            $where[] = ['type', '=', $type];
        }

        if ($days > 0) {
            $time = time() - ($days * 86400);
            $where[] = ['create_time', '<', $time];
        }

        try {
            // 清除调试日志
            $result = DebugLog::clear($where);

            if ($result) {
                return $this->json([], 200, \think\facade\Lang::get('debug.clear_log_success'));
            } else {
                return $this->json([], 500, \think\facade\Lang::get('debug.clear_log_failed'));
            }
        } catch (\Exception $e) {
            return $this->json([], 500, \think\facade\Lang::get('debug.clear_log_failed') . ': ' . $e->getMessage());
        }
    }
}
