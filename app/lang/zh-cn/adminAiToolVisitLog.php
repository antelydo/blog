<?php
/**
 * AI工具访问日志管理相关语言包
 */
return [
    'adminAiToolVisitLog' => [
        // 通用
        'params_error' => '参数错误',
        'not_logged_in' => '未登录',
        'no_permission' => '没有权限',
        
        // 访问日志相关
        'get_visit_log_list_success' => '获取访问日志列表成功',
        'get_visit_log_info_success' => '获取访问日志详情成功',
        'delete_visit_log_success' => '删除访问日志成功',
        'visit_log_not_exist' => '访问日志不存在',
        'delete_visit_log_log' => '删除AI工具访问日志',
        'batch_delete_visit_log_log' => '批量删除AI工具访问日志',
        'batch_delete_success' => '批量删除访问日志成功',
        
        // 列表相关
        'id' => 'ID',
        'tool_id' => '工具ID',
        'tool_name' => '工具名称',
        'user_id' => '用户ID',
        'user_nickname' => '用户昵称',
        'user_type' => '用户类型',
        'ip' => 'IP地址',
        'user_agent' => '用户代理',
        'referer' => '来源页面',
        'visit_time' => '访问时间',
        'operations' => '操作',
        
        // 用户类型
        'user_type_user' => '注册用户',
        'user_type_admin' => '管理员',
        'user_type_guest' => '访客',
        
        // 操作按钮
        'delete' => '删除',
        'view_tool' => '查看工具',
        'view_user' => '查看用户',
        'batch_delete' => '批量删除',
        
        // 筛选相关
        'filter_keyword' => '关键词',
        'filter_tool' => '工具',
        'filter_user_type' => '用户类型',
        'filter_date_range' => '时间范围',
        'filter_search' => '搜索',
        'filter_reset' => '重置',
        
        // 统计相关
        'total_visits' => '总访问次数',
        'today_visits' => '今日访问次数',
        'user_type_distribution' => '用户类型分布',
        'most_visited_tools' => '访问最多的工具',
        'visit_user_count' => '访问用户数',
        'visit_guest_count' => '访问访客数',
        'visit_trend' => '访问趋势',
        'visit_source' => '访问来源',
        'visit_device' => '访问设备',
        'visit_browser' => '访问浏览器'
    ]
];
