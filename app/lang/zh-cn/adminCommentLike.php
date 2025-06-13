<?php
/**
 * 评论点赞管理相关语言包
 */
return [
    'adminCommentLike' => [
        // 通用
        'params_error' => '参数错误',
        'not_logged_in' => '未登录',
        'no_permission' => '没有权限',
        
        // 点赞相关
        'get_like_list_success' => '获取点赞列表成功',
        'delete_like_success' => '删除点赞记录成功',
        'like_not_exist' => '点赞记录不存在',
        'delete_like_log' => '删除评论点赞记录',
        'batch_delete_like_log' => '批量删除评论点赞记录',
        'batch_delete_success' => '批量删除点赞记录成功',
        
        // 列表相关
        'id' => 'ID',
        'comment_id' => '评论ID',
        'comment_content' => '评论内容',
        'post_id' => '文章ID',
        'user_id' => '用户ID',
        'username' => '用户名',
        'nickname' => '用户昵称',
        'user_type' => '用户类型',
        'ip' => 'IP地址',
        'create_time' => '创建时间',
        'operations' => '操作',
        
        // 用户类型
        'user_type_user' => '注册用户',
        'user_type_admin' => '管理员',
        'user_type_guest' => '访客',
        
        // 操作按钮
        'delete' => '删除',
        'view_comment' => '查看评论',
        'view_post' => '查看文章',
        'view_user' => '查看用户',
        'batch_delete' => '批量删除',
        
        // 筛选相关
        'filter_keyword' => '关键词',
        'filter_comment' => '评论',
        'filter_user_type' => '用户类型',
        'filter_date_range' => '时间范围',
        'filter_search' => '搜索',
        'filter_reset' => '重置',
        
        // 统计相关
        'total_likes' => '总点赞数',
        'today_likes' => '今日新增点赞',
        'yesterday_likes' => '昨日点赞',
        'week_likes' => '本周点赞',
        'month_likes' => '本月点赞',
        'user_type_distribution' => '用户类型分布',
        'most_liked_comments' => '最受欢迎的评论'
    ]
];
