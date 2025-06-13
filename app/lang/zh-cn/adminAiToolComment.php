<?php
/**
 * AI工具评论管理相关语言包
 */
return [
    'adminAiToolComment' => [
        // 通用
        'params_error' => '参数错误',
        'not_logged_in' => '未登录',
        'no_permission' => '没有权限',
        
        // 评论相关
        'get_comment_list_success' => '获取评论列表成功',
        'get_comment_info_success' => '获取评论详情成功',
        'reply_comment_success' => '回复评论成功',
        'update_comment_status_success' => '更新评论状态成功',
        'delete_comment_success' => '删除评论成功',
        'comment_not_exist' => '评论不存在',
        'reply_comment_log' => '回复AI工具评论',
        'update_comment_status_log' => '更新AI工具评论状态',
        'delete_comment_log' => '删除AI工具评论',
        'batch_delete_comment_log' => '批量删除AI工具评论',
        'batch_approve_comment_log' => '批量审核通过AI工具评论',
        'batch_reject_comment_log' => '批量拒绝AI工具评论',
        'batch_delete_success' => '批量删除评论成功',
        'batch_approve_success' => '批量审核通过评论成功',
        'batch_reject_success' => '批量拒绝评论成功',
        
        // 列表相关
        'id' => 'ID',
        'tool_id' => '工具ID',
        'tool_name' => '工具名称',
        'user_id' => '用户ID',
        'user_nickname' => '用户昵称',
        'user_type' => '用户类型',
        'parent_id' => '父评论ID',
        'content' => '评论内容',
        'rating' => '评分',
        'status' => '状态',
        'ip' => 'IP地址',
        'user_agent' => '用户代理',
        'likes' => '点赞数',
        'create_time' => '创建时间',
        'update_time' => '更新时间',
        'operations' => '操作',
        
        // 状态相关
        'status_pending' => '待审核',
        'status_approved' => '已通过',
        'status_rejected' => '已拒绝',
        
        // 用户类型
        'user_type_user' => '注册用户',
        'user_type_admin' => '管理员',
        'user_type_guest' => '访客',
        
        // 操作按钮
        'reply' => '回复',
        'approve' => '通过',
        'reject' => '拒绝',
        'delete' => '删除',
        'view_tool' => '查看工具',
        'batch_operation' => '批量操作',
        
        // 筛选相关
        'filter_keyword' => '关键词',
        'filter_tool' => '工具',
        'filter_status' => '状态',
        'filter_user_type' => '用户类型',
        'filter_date_range' => '时间范围',
        'filter_search' => '搜索',
        'filter_reset' => '重置',
        
        // 表单相关
        'form_reply_content' => '回复内容',
        'form_submit' => '提交',
        'form_cancel' => '取消'
    ]
];
