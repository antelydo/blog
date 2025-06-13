<?php
/**
 * IP地理位置控制器相关语言包
 */
return [
    'ipLocation' => [
        // 通用
        'query_success' => '获取IP地理位置成功',
        'query_failed' => '查询失败',
        'batch_query_completed' => '批量查询IP地理位置完成',
        
        // 错误信息
        'no_permission' => '无权限访问',
        'invalid_ip' => '无效的IP地址',
        'invalid_ip_list' => '无效的IP列表',
        'ip_list_limit_exceeded' => 'IP列表数量超过限制，最多{count}个',
        'api_response_parse_failed' => '解析API响应失败',
        'unknown_error' => '未知错误',
        
        // 查询结果
        'country' => '国家/地区',
        'country_code' => '国家代码',
        'region' => '省/州',
        'region_name' => '省/州名称',
        'city' => '城市',
        'zip' => '邮政编码',
        'latitude' => '纬度',
        'longitude' => '经度',
        'timezone' => '时区',
        'isp' => 'ISP',
        'org' => '组织',
        'as' => 'AS',
        'query' => '查询IP',
    ]
];
