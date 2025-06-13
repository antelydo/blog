<?php
/**
 * 测试控制器相关语言包
 */
return [
    'test' => [
        // 通用
        'test_successful' => '测试成功',
        'pagination_test_completed' => '分页测试完成',
        'pagination_combinations_test_completed' => '分页组合测试完成',
        
        // 测试结果
        'test_success_no_articles' => '测试成功，但没有该分类下的文章',
        
        // 验证信息
        'missing_pagination_field' => '缺少必要的分页字段',
        'page_mismatch' => '返回的页码与请求的页码不一致',
        'limit_mismatch' => '返回的每页数量与请求的每页数量不一致',
        'item_count_mismatch' => '返回的数据条数与预期的条数不一致',
        'page_out_of_range' => '请求的页码超出了最大页码',
        'validation_passed' => '通过',
        
        // 测试用例
        'test_case_format' => '页码 %d, 每页 %d 条',
    ]
];
