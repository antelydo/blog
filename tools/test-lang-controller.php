<?php
/**
 * 语言控制器测试脚本
 * 直接测试语言文件在控制器中的使用
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

// 模拟ThinkPHP的Lang类
class MockLang {
    private static $langData = [];
    
    public static function load($files, $lang = 'zh-cn') {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $data = include($file);
                self::$langData = array_merge(self::$langData, $data);
            }
        }
    }
    
    public static function get($key, $vars = [], $lang = null) {
        $keys = explode('.', $key);
        
        if (count($keys) === 1) {
            // 直接键
            return self::$langData[$key] ?? $key;
        } else {
            // 分组键
            $group = $keys[0];
            $name = $keys[1];
            
            if (isset(self::$langData[$group][$name])) {
                $value = self::$langData[$group][$name];
                
                // 替换变量
                if (!empty($vars) && is_array($vars)) {
                    foreach ($vars as $k => $v) {
                        $value = str_replace('{' . $k . '}', $v, $value);
                    }
                }
                
                return $value;
            }
            
            return $key;
        }
    }
}

// 加载语言文件
$langFiles = [
    __DIR__ . '/../app/lang/zh-cn/adminAiTool.php',
    __DIR__ . '/../app/lang/zh-cn/adminAiToolCategory.php',
    __DIR__ . '/../app/lang/zh-cn/adminAiToolComment.php',
    __DIR__ . '/../app/lang/zh-cn/tool.php',
    __DIR__ . '/../app/lang/zh-cn/toolComment.php',
    __DIR__ . '/../app/lang/zh-cn/search.php',
    __DIR__ . '/../app/lang/zh-cn/common.php',
];

MockLang::load($langFiles, 'zh-cn');

echo colorText("开始测试控制器中的语言使用...\n", 'blue');

// 测试用例
$testCases = [
    // AdminAiTool控制器
    'AdminAiTool' => [
        'adminAiTool.params_error' => '参数错误',
        'adminAiTool.get_tool_list_success' => '获取工具列表成功',
        'adminAiTool.tool_not_exist' => '工具不存在',
        'adminAiTool.create_tool_success' => '创建工具成功',
        'adminAiTool.update_tool_success' => '更新工具成功',
        'adminAiTool.delete_tool_success' => '删除工具成功',
    ],
    
    // Tool控制器
    'Tool' => [
        'tool.params_error' => '参数错误',
        'tool.get_tool_list_success' => '获取工具列表成功',
        'tool.get_tool_detail_success' => '获取工具详情成功',
        'tool.tool_not_exist' => '工具不存在',
        'tool.tool_not_published' => '工具尚未发布',
    ],
    
    // Search控制器
    'Search' => [
        'search.params_error' => '参数错误',
        'search.please_input_search_keyword' => '请输入搜索关键词',
        'search.search_result' => '搜索结果',
        'search.search_result_for' => '"{keyword}"的搜索结果',
        'search.no_search_result' => '没有找到与"{keyword}"相关的内容',
    ],
    
    // 变量替换测试
    'VariableReplacement' => [
        'search.search_result_for' => [
            'vars' => ['keyword' => '人工智能'],
            'expected' => '"人工智能"的搜索结果'
        ],
        'search.no_search_result' => [
            'vars' => ['keyword' => 'AI工具'],
            'expected' => '没有找到与"AI工具"相关的内容'
        ]
    ]
];

// 执行测试
foreach ($testCases as $controller => $tests) {
    echo colorText("\n测试控制器: $controller\n", 'yellow');
    
    foreach ($tests as $key => $expected) {
        if (is_array($expected)) {
            // 变量替换测试
            $vars = $expected['vars'];
            $expectedValue = $expected['expected'];
            $actual = MockLang::get($key, $vars);
            
            if ($actual === $expectedValue) {
                echo colorText("  ✓ $key (带变量) = \"$actual\"\n", 'green');
            } else {
                echo colorText("  ✗ $key (带变量) = \"$actual\", 期望 \"$expectedValue\"\n", 'red');
            }
        } else {
            // 普通测试
            $actual = MockLang::get($key);
            
            if ($actual === $expected) {
                echo colorText("  ✓ $key = \"$actual\"\n", 'green');
            } else {
                echo colorText("  ✗ $key = \"$actual\", 期望 \"$expected\"\n", 'red');
            }
        }
    }
}

// 测试英文语言
echo colorText("\n切换到英文语言\n", 'blue');

// 加载英文语言文件
$enLangFiles = [
    __DIR__ . '/../app/lang/en-us/adminAiTool.php',
    __DIR__ . '/../app/lang/en-us/adminAiToolCategory.php',
    __DIR__ . '/../app/lang/en-us/adminAiToolComment.php',
    __DIR__ . '/../app/lang/en-us/tool.php',
    __DIR__ . '/../app/lang/en-us/toolComment.php',
    __DIR__ . '/../app/lang/en-us/search.php',
    __DIR__ . '/../app/lang/en-us/common.php',
];

// 重新加载语言数据
MockLang::load($enLangFiles, 'en-us');

// 测试几个关键的英文翻译
$enTestCases = [
    'AdminAiTool' => [
        'adminAiTool.params_error' => 'Parameter error',
        'adminAiTool.get_tool_list_success' => 'Get tool list successfully',
        'adminAiTool.tool_not_exist' => 'Tool does not exist',
    ],
    
    'Tool' => [
        'tool.params_error' => 'Parameter error',
        'tool.get_tool_list_success' => 'Get tool list successfully',
        'tool.tool_not_exist' => 'Tool does not exist',
    ],
    
    'Search' => [
        'search.search_result_for' => [
            'vars' => ['keyword' => 'AI tools'],
            'expected' => 'Search results for "AI tools"'
        ]
    ]
];

// 执行英文测试
foreach ($enTestCases as $controller => $tests) {
    echo colorText("\n测试控制器 (英文): $controller\n", 'yellow');
    
    foreach ($tests as $key => $expected) {
        if (is_array($expected)) {
            // 变量替换测试
            $vars = $expected['vars'];
            $expectedValue = $expected['expected'];
            $actual = MockLang::get($key, $vars);
            
            if ($actual === $expectedValue) {
                echo colorText("  ✓ $key (带变量) = \"$actual\"\n", 'green');
            } else {
                echo colorText("  ✗ $key (带变量) = \"$actual\", 期望 \"$expectedValue\"\n", 'red');
            }
        } else {
            // 普通测试
            $actual = MockLang::get($key);
            
            if ($actual === $expected) {
                echo colorText("  ✓ $key = \"$actual\"\n", 'green');
            } else {
                echo colorText("  ✗ $key = \"$actual\", 期望 \"$expected\"\n", 'red');
            }
        }
    }
}

echo colorText("\n控制器语言测试完成\n", 'blue');
