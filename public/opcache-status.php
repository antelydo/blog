<?php
// 仅允许管理员访问
$allowedIps = ['127.0.0.1', '::1']; // 添加你的IP地址
if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// 显示OPCache状态
echo '<h1>OPCache Status</h1>';
echo '<pre>';
print_r(opcache_get_status());
echo '</pre>';

echo '<h1>OPCache Configuration</h1>';
echo '<pre>';
print_r(opcache_get_configuration());
echo '</pre>';
