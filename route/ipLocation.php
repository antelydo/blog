<?php
/**
 * IP地理位置查询路由配置
 */

use think\facade\Route;

// IP地理位置查询API
Route::group('api/ipLocation', function () {
    Route::get('query', 'api.controller.IpLocation/query');
    Route::post('batchQuery', 'api.controller.IpLocation/batchQuery');
});

// 管理员IP地理位置查询API（需要管理员登录）
Route::group('api/adminIpLocation', function () {
    Route::get('query', 'api.controller.IpLocation/query');
    Route::post('batchQuery', 'api.controller.IpLocation/batchQuery');
})->middleware(\app\middleware\CheckAdminLogin::class);
