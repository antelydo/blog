<?php
declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Log;
/**
 * 管理员IP地理位置查询控制器
 */
class AdminIpLocation extends BaseController
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();
    }
    /**
     * IP-API服务地址
     */
    protected $ipApiUrl = 'http://ip-api.com/json/';

    /**
     * 缓存前缀
     */
    protected $cachePrefix = 'admin_ip_location_';

    /**
     * 缓存时间（秒）
     */
    protected $cacheExpire = 86400; // 24小时

    /**
     * 查询单个IP地理位置
     */
    public function query()
    {
        // 验证管理员权限
        if (!$this->isAdmin()) {
            return json(['code' => 401, 'msg' => Lang::get('adminIpLocation.no_permission')]);
        }

        // 获取IP参数
        $ip = Request::param('ip', '');


        // 如果未提供IP，使用当前用户IP
        if (empty($ip)) {
            $ip = Request::ip();
        }

        // 验证IP格式
        if (!Validate::isIp($ip)) {
            return json(['code' => 402, 'msg' => Lang::get('adminIpLocation.invalid_ip')]);
        }

        // 尝试从缓存获取
        $cacheKey = $this->cachePrefix . $ip;
        $cacheData = Cache::get($cacheKey);

        if ($cacheData) {
            return $cacheData;
        }

         //从API获取数据
        try {
            $result = $this->queryFromApi($ip);
            if ($result['code'] == 200) {
                // 缓存结果
                Cache::set($cacheKey, $result,$this->cacheExpire);
                return $result;
            } else {
                return json(['code' => 0, 'msg' => Lang::get('adminIpLocation.query_failed') . ': ' . ($result['message'] ?? Lang::get('adminIpLocation.unknown_error'))]);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => Lang::get('adminIpLocation.query_failed')]);
        }
    }

    /**
     * 批量查询IP地理位置
     */
    public function batchQuery()
    {
        // 验证管理员权限
        if (!$this->isAdmin()) {
            return json(['code' => 401, 'msg' => Lang::get('adminIpLocation.no_permission')]);
        }

        // 获取IP列表参数
        $ipList = Request::param('ip_list', []);

        // 验证参数
        if (!is_array($ipList) || empty($ipList)) {
            return json(['code' => 401, 'msg' => Lang::get('adminIpLocation.invalid_ip_list')]);
        }

        // 限制最大查询数量
        $maxCount = 50; // 管理员可以查询更多IP
        if (count($ipList) > $maxCount) {
            return json(['code' => 401, 'msg' => str_replace('{count}', $maxCount, Lang::get('adminIpLocation.ip_list_limit_exceeded'))]);
        }

        $results = [];

        foreach ($ipList as $ip) {
            // 验证IP格式
            if (!Validate::isIp($ip)) {
                $results[$ip] = [
                    'code' => 401,
                    'msg' => Lang::get('adminIpLocation.invalid_ip')
                ];
                continue;
            }

            // 尝试从缓存获取
            $cacheKey = $this->cachePrefix . $ip;
            $cacheData = Cache::get($cacheKey);

            if ($cacheData) {
                $results[$ip] = [
                    'code' => 200,
                    'data' => $cacheData
                ];
                continue;
            }

            // 从API获取数据
            try {
                $result = $this->queryFromApi($ip);

                if ($result['code'] === '200') {
                    // 缓存结果
                    Cache::set($cacheKey, $result, $this->cacheExpire);
                    $results[$ip] = [
                        'code' => 200,
                        'data' => $result
                    ];
                } else {
                    $results[$ip] = [
                        'code' => 401,
                        'msg' => Lang::get('adminIpLocation.query_failed') . ': ' . ($result['message'] ?? Lang::get('adminIpLocation.unknown_error'))
                    ];
                }
            } catch (\Exception $e) {
                Log::error('管理员批量IP地理位置查询失败', [
                    'ip' => $ip,
                    'error' => $e->getMessage()
                ]);
                $results[$ip] = [
                    'code' => 401,
                    'msg' => Lang::get('adminIpLocation.query_failed') . ': ' . $e->getMessage()
                ];
            }

            // 添加延迟，避免请求过于频繁
            if (array_search($ip, $ipList) < count($ipList) - 1) {
                usleep(300000); // 300毫秒，管理员查询可以更快
            }
        }
        $results['msg'] = Lang::get('adminIpLocation.batch_query_completed');
        return json($results);
    }

    /**
     * 从API查询IP地理位置
     * @param string $ip IP地址
     * @return array 查询结果
     */
    protected function queryFromApi($ip): array
    {
        // 构建请求URL
        $url = $this->ipApiUrl . $ip . '?lang=zh-CN&fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query';

        // 执行请求并获取响应
        $response = file_get_contents($url);
        $response =json_decode($response,true);

        // 检查响应
        return  [
           'code'=>$response?200:400,
           'msg'=>$response?Lang::get('adminIpLocation.query_success'):Lang::get('adminIpLocation.api_response_parse_failed'),
           'data'=>$response
        ];
    }

    /**
     * 检查当前用户是否为管理员
     * @return bool
     */
    protected function isAdmin()
    {
        // 使用父类中的用户信息
        return $this->userType == 'admin' && $this->userId > 0;
    }
}
