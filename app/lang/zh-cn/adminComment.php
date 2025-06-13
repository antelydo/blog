<?php
return [
    'adminComment' => [
        // 评论管理
        'title' => '评论管理',
        'list_title' => '评论列表',
        'detail_title' => '评论详情',
        
        // 列表相关
        'id' => 'ID',
        'post_title' => '文章标题',
        'content' => '评论内容',
        'user' => '评论用户',
        'type' => '评论类型',
        'ip' => 'IP地址',
        'user_agent' => '用户代理',
        'status' => '状态',
        'create_time' => '评论时间',
        'update_time' => '更新时间',
        'operations' => '操作',
        'likes' => '点赞数',
        'parent_comment' => '父评论',
        'total_comments' => '共 {total} 条评论',
        
        // 筛选相关
        'filter_keyword' => '搜索评论内容/用户',
        'filter_status' => '状态',
        'filter_all_status' => '全部状态',
        'filter_post' => '文章',
        'filter_user_type' => '用户类型',
        'filter_date_range' => '时间范围',
        'filter_search' => '搜索',
        'filter_reset' => '重置',
        
        // 评论类型
        'type_normal' => '普通评论',
        'type_admin' => '管理员',
        'type_author' => '作者',
        'type_guest' => '访客',
        'type_unknown' => '未知',
        
        // 评论状态
        'status_approved' => '已通过',
        'status_pending' => '待审核',
        'status_rejected' => '已拒绝',
        'status_unknown' => '未知',
        'status_1' => '已通过',
        'status_0' => '待审核',
        'status_-1' => '已拒绝',
        
        // 回复相关
        'reply_title' => '回复评论',
        'reply_original_comment' => '原评论',
        'reply_content' => '回复内容',
        'reply_submit' => '发布回复',
        'reply_cancel' => '取消',
        'reply_content_required' => '请输入回复内容',
        'reply_content_length' => '长度在 2 到 500 个字符',
        'reply_success' => '回复评论成功',
        'reply_error' => '回复评论失败',
        'reply_badge' => '回复',
        
        // 审核相关
        'approve_title' => '审核评论',
        'approve_confirm' => '确定要通过此评论吗？',
        'approve_success' => '评论已通过审核',
        'approve_error' => '更新评论状态失败',
        'approve_button' => '通过',
        
        // 删除相关
        'delete_confirm' => '确定要删除此评论吗？此操作不可恢复。',
        'delete_success' => '删除评论成功',
        'delete_error' => '删除评论失败',
        'delete_warning' => '警告',
        'delete_confirm_button' => '确定',
        'delete_cancel_button' => '取消',
        'delete_button' => '删除',
        
        // 编辑器相关
        'editor_toolbar_bold' => '加粗',
        'editor_toolbar_italic' => '斜体',
        'editor_toolbar_underline' => '下划线',
        'editor_toolbar_emoji_smile' => '笑脸',
        'editor_toolbar_emoji_thumbs_up' => '点赞',
        'editor_toolbar_emoji_heart' => '爱心',
        'editor_toolbar_emoji_thanks' => '谢谢',
        'editor_toolbar_clear' => '清除格式',
        'editor_char_count' => '{count}/500',
        
        // 错误信息
        'error_fetch_failed' => '获取评论列表失败',
        'error_network_error' => '获取评论列表失败，请检查网络连接',
        'error_unexpected_format' => '评论数据格式异常',
        
        // 其他
        'no_title' => '无标题',
        'no_content' => '无内容',
        'anonymous_user' => '匿名用户',
        'user_agent_unknown' => '未知',
        'user_agent_other' => '其他浏览器',
        'view_article' => '查看文章',
        'reply_button' => '回复',
        'like_count' => '点赞数: {count}',
        'export_csv' => '导出CSV',
        'export_excel' => '导出Excel',
        'batch_approve' => '批量通过',
        'batch_delete' => '批量删除',
        'select_all' => '全选',
        'selected_count' => '已选择 {count} 项',
        'comment_detail' => '评论详情',
        'user_info' => '用户信息',
        'device_info' => '设备信息',
        'browser' => '浏览器',
        'os' => '操作系统',
        'refresh_list' => '刷新列表',
        'no_data' => '暂无数据',
        'loading' => '加载中...',
        'expand' => '展开',
        'collapse' => '收起',
        'show_parent' => '显示父评论',
        'hide_parent' => '隐藏父评论',
    ]
];
