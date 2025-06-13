<?php
/**
 * 语言文件测试脚本
 * 用于测试新创建的语言文件是否正常工作
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定义测试的语言文件和键
$testFiles = [
    'adminAiTool' => [
        'params_error',
        'get_tool_list_success',
        'create_tool_success',
        'tool_not_exist',
        'status_draft',
        'pricing_free',
        'edit',
        'filter_keyword',
        'platforms'
    ],
    'adminAiToolCategory' => [
        'params_error',
        'get_category_list_success',
        'category_not_exist',
        'status_enabled',
        'edit',
        'filter_keyword',
        'form_name'
    ],
    'tool' => [
        'params_error',
        'get_tool_list_success',
        'tool_not_exist',
        'filter_category',
        'sort_newest',
        'pricing_free',
        'visit_website',
        'like_success'
    ],
    'search' => [
        'params_error',
        'please_input_search_keyword',
        'search_result',
        'no_search_result',
        'search_all',
        'sort_relevance',
        'search_placeholder'
    ]
];

// 测试语言
$languages = ['zh-cn', 'en-us'];

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

echo colorText("开始测试语言文件...\n", 'blue');

// 遍历语言
foreach ($languages as $lang) {
    echo colorText("\n测试语言: $lang\n", 'yellow');
    
    // 遍历测试文件
    foreach ($testFiles as $file => $keys) {
        echo colorText("\n  测试文件: $file.php\n", 'blue');
        
        // 加载语言文件
        $filePath = __DIR__ . "/../app/lang/$lang/$file.php";
        if (!file_exists($filePath)) {
            echo colorText("    文件不存在: $filePath\n", 'red');
            continue;
        }
        
        $langData = include($filePath);
        
        // 检查文件结构
        if (!is_array($langData) || !isset($langData[$file])) {
            echo colorText("    文件结构错误: 缺少 '$file' 键\n", 'red');
            continue;
        }
        
        // 测试每个键
        foreach ($keys as $key) {
            $fullKey = "$file.$key";
            $exists = isset($langData[$file][$key]);
            $value = $exists ? $langData[$file][$key] : null;
            
            if ($exists) {
                echo colorText("    ✓ $fullKey = \"$value\"\n", 'green');
            } else {
                echo colorText("    ✗ $fullKey 不存在\n", 'red');
            }
        }
        
        // 测试兼容性键（如果有）
        if (isset($langData[$keys[0]])) {
            echo colorText("\n    测试兼容性键:\n", 'blue');
            echo colorText("    ✓ 发现兼容性键\n", 'green');
        }
    }
}

echo colorText("\n语言文件测试完成\n", 'blue');
