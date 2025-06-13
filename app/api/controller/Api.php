<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Lang;

class Api extends BaseController
{
    /**
     * API首页
     * @return \think\response\Json
     */
    public function index()
    {
        return json([
            'code' => 200,
            'msg' => Lang::get('api.service_normal'),
            'data' => [
                'version' => Config::get('app.version', '1.0.0'),
                'time' => date('Y-m-d H:i:s'),
                'server' => Lang::get('api.server_name')
            ]
        ]);
    }

    /**
     * API测试
     * @return \think\response\Json
     */
    public function test()
    {
        $method = Request::method();
        $params = $method == 'POST' ? Request::post() : Request::get();

        return json([
            'code' => 200,
            'msg' => Lang::get('api.test_successful'),
            'data' => [
                Lang::get('api.request_method') => $method,
                Lang::get('api.request_params') => $params,
                Lang::get('api.request_time') => date('Y-m-d H:i:s'),
                'request_info' => [
                    Lang::get('api.request_ip') => Request::ip(),
                    Lang::get('api.request_domain') => Request::domain(),
                    Lang::get('api.request_url') => Request::url()
                ]
            ]
        ]);
    }
}