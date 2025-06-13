<?php
/**
 * 联系表单管理相关语言包
 */
return [
    'adminContact' => [
        // 通用
        'params_error' => '参数错误',
        'not_logged_in' => '未登录',
        'no_permission' => '没有权限',
        
        // 联系表单相关
        'get_message_list_success' => '获取消息列表成功',
        'get_message_info_success' => '获取消息详情成功',
        'delete_message_success' => '删除消息成功',
        'update_status_success' => '更新状态成功',
        'reply_success' => '回复成功',
        'message_not_exist' => '消息不存在',
        'id_required' => '消息ID不能为空',
        'invalid_status' => '无效的状态',
        'reply_required' => '回复内容不能为空',
        'delete_message_log' => '删除联系表单消息',
        'update_status_log' => '更新联系表单消息状态',
        'reply_message_log' => '回复联系表单消息',
        'batch_delete_message_log' => '批量删除联系表单消息',
        'batch_update_status_log' => '批量更新联系表单消息状态',
        'batch_delete_success' => '批量删除消息成功',
        'batch_update_status_success' => '批量更新状态成功',
        
        // 列表相关
        'id' => 'ID',
        'name' => '姓名',
        'email' => '邮箱',
        'subject' => '主题',
        'message' => '消息内容',
        'ip_address' => 'IP地址',
        'status' => '状态',
        'reply' => '回复内容',
        'replied_by' => '回复人',
        'replied_time' => '回复时间',
        'create_time' => '创建时间',
        'update_time' => '更新时间',
        'operations' => '操作',
        
        // 状态相关
        'status_unread' => '未读',
        'status_read' => '已读',
        'status_replied' => '已回复',
        'status_0' => '未读',
        'status_1' => '已读',
        'status_2' => '已回复',
        
        // 操作按钮
        'view' => '查看',
        'reply_button' => '回复',
        'mark_as_read' => '标记为已读',
        'mark_as_unread' => '标记为未读',
        'delete' => '删除',
        'batch_operation' => '批量操作',
        'batch_delete' => '批量删除',
        'batch_mark_as_read' => '批量标记为已读',
        'batch_mark_as_unread' => '批量标记为未读',
        
        // 筛选相关
        'filter_keyword' => '关键词',
        'filter_status' => '状态',
        'filter_date_range' => '时间范围',
        'filter_search' => '搜索',
        'filter_reset' => '重置',
        
        // 表单相关
        'form_reply_content' => '回复内容',
        'form_submit' => '提交',
        'form_cancel' => '取消',
        
        // 统计相关
        'total_messages' => '总消息数',
        'unread_messages' => '未读消息',
        'read_messages' => '已读消息',
        'replied_messages' => '已回复消息',
        'today_messages' => '今日新增消息',
        'average_response_time' => '平均响应时间'
    ]
];
