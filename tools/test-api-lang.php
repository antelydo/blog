<?php
/**
 * API语言测试脚本
 * 用于测试API中的语言文件是否正常工作
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 颜色函数
function colorText($text, $color) {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

// 测试API端点
$endpoints = [
    // 测试基本语言功能
    'api/test/lang' => '测试基本语言功能',
    
    // 测试搜索功能
    'api/search?query=test' => '测试搜索功能',
    
    // 测试AI工具相关功能
    'api/tool/list' => '测试AI工具列表',
    
    // 测试语言切换
    'api/lang/current' => '测试当前语言',
    'api/lang/change?lang=en-us' => '切换到英文',
    'api/lang/change?lang=zh-cn' => '切换回中文'
];

// 基础URL
$baseUrl = 'http://localhost/';

echo colorText("开始测试API语言功能...\n", 'blue');

// 遍历测试端点
foreach ($endpoints as $endpoint => $description) {
    $url = $baseUrl . $endpoint;
    echo colorText("\n测试: $description\n", 'yellow');
    echo colorText("URL: $url\n", 'blue');
    
    // 使用file_get_contents发起请求
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true
        ]
    ]);
    
    try {
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            echo colorText("  请求失败: " . error_get_last()['message'] . "\n", 'red');
            continue;
        }
        
        // 解析JSON响应
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo colorText("  JSON解析失败: " . json_last_error_msg() . "\n", 'red');
            echo colorText("  原始响应: " . substr($response, 0, 200) . "...\n", 'yellow');
            continue;
        }
        
        // 检查响应
        if (isset($data['code']) && $data['code'] == 200) {
            echo colorText("  请求成功 (状态码: {$data['code']})\n", 'green');
            echo colorText("  消息: {$data['msg']}\n", 'green');
            
            // 打印数据摘要
            if (isset($data['data']) && is_array($data['data'])) {
                echo colorText("  数据摘要:\n", 'blue');
                foreach ($data['data'] as $key => $value) {
                    if (is_array($value)) {
                        echo colorText("    $key: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n", 'yellow');
                    } else {
                        echo colorText("    $key: $value\n", 'yellow');
                    }
                }
            }
        } else {
            echo colorText("  请求失败 (状态码: {$data['code']})\n", 'red');
            echo colorText("  错误消息: {$data['msg']}\n", 'red');
        }
    } catch (Exception $e) {
        echo colorText("  异常: " . $e->getMessage() . "\n", 'red');
    }
}

echo colorText("\nAPI语言测试完成\n", 'blue');
