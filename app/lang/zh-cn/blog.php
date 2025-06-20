<?php
return [
    'blog' => [
        // 文章相关
        'post_not_exist' => '文章不存在',
        'get_post_successful' => '获取文章成功',
        'get_post_list_successful' => '获取文章列表成功',
        'create_post_successful' => '创建文章成功',
        'update_post_successful' => '更新文章成功',
        'delete_post_successful' => '删除文章成功',
        'post_title_required' => '文章标题不能为空',
        'post_content_required' => '文章内容不能为空',
        'create_post_log' => '创建文章',
        'update_post_log' => '更新文章',
        'delete_post_log' => '删除文章',
        'unknown' => '未知',
        'get_stats_successful'=> '获取状态统计数据成功',

        // 置顶和推荐相关
        'top_post_log' => '置顶文章',
        'untop_post_log' => '取消置顶文章',
        'top_post_successful' => '文章置顶成功',
        'untop_post_successful' => '取消文章置顶成功',
        'recommend_post_log' => '推荐文章',
        'unrecommend_post_log' => '取消推荐文章',
        'recommend_post_successful' => '文章推荐成功',
        'unrecommend_post_successful' => '取消文章推荐成功',
        'operation_failed' => '操作失败',

        // 搜索相关
        'please_input_search_keyword' => '请输入搜索关键词',
        'search_successful' => '搜索成功',
        'search_failed' => '搜索失败',
        'search_no_results' => '没有搜索结果',
        'search_no_more_results' => '没有更多搜索结果',
        'search_no_more_results_tip' => '没有更多搜索结果，请重新搜索',
        'search_no_more_results_tip_btn' => '返回首页',

        // 首页banner展示相关
        'set_banner_top_log' => '设置首页banner展示',
        'set_banner_top_successful' => '设置首页banner展示成功',
        'set_banner_top_failed' => '设置首页banner展示失败',

        // 分类相关
        'category_not_exist' => '分类不存在',
        'get_category_successful' => '获取分类成功',
        'get_category_list_successful' => '获取分类列表成功',
        'create_category_successful' => '创建分类成功',
        'update_category_successful' => '更新分类成功',
        'delete_category_successful' => '删除分类成功',
        'create_category_failed' => '创建分类失败',
        'update_category_failed' => '更新分类失败',
        'delete_category_failed' => '删除分类失败',
        'category_name_required' => '分类名称不能为空',
        'category_already_exists' => '分类名称已存在',
        'has_subcategories' => '该分类下有子分类，无法删除',
        'has_posts' => '该分类下有文章，无法删除',
        'cant_set_self_as_parent' => '不能将自己设为父级分类',
        'cant_set_child_as_parent' => '不能将子分类设为父级分类',
        'create_category_log' => '创建文章分类',
        'update_category_log' => '更新文章分类',
        'delete_category_log' => '删除文章分类',
        'category_status_error'=>'分类状态异常',

        // 标签相关
        'tag_not_exist' => '标签不存在',
        'get_tag_successful' => '获取标签成功',
        'get_tag_list_successful' => '获取标签列表成功',
        'create_tag_successful' => '创建标签成功',
        'update_tag_successful' => '更新标签成功',
        'delete_tag_successful' => '删除标签成功',
        'create_tag_failed' => '创建标签失败',
        'update_tag_failed' => '更新标签失败',
        'tag_name_required' => '标签名称不能为空',
        'tag_already_exists' => '标签名称已存在',
        'create_tag_log' => '创建文章标签',
        'update_tag_log' => '更新文章标签',
        'delete_tag_log' => '删除文章标签',
        'tag_status_error'=>'文章标签状态异常',

        // 点赞相关
        'like_successful' => '点赞成功',
        'unlike_successful' => '取消点赞成功',
        'already_liked' => '已经点赞过了',
        'get_like_list_successful' => '获取点赞列表成功',
        'like_not_exist' => '点赞记录不存在',
        'invalid_params' => '参数错误',
        'get_like_stats_successful' => '获取点赞统计成功',
        'delete_like_log' => '删除文章点赞记录',
        'like_post_log' => '点赞文章',
        'unlike_post_log' => '取消点赞文章',

        // 评论相关
        'comment_not_exist' => '评论不存在',
        'get_comment_successful' => '获取评论成功',
        'get_comment_list_successful' => '获取评论列表成功',
        'create_comment_successful' => '评论成功',
        'update_comment_successful' => '更新评论成功',
        'delete_comment_successful' => '删除评论成功',
        'update_comment_failed' => '更新评论失败',
        'comment_content_required' => '评论内容不能为空',
        'status_approved' => '审核通过',
        'status_pending' => '待审核',
        'status_rejected' => '拒绝',
        'update_comment_status_log' => '更新评论状态为',
        'delete_comment_log' => '删除评论',
        'create_comment_log' => '发表评论',
        'comment_post_log' => '评论文章',

        // 收藏相关
        'already_favorited' => '您已经收藏过了',
        'not_favorited' => '您还未收藏',
        'favorite_success' => '收藏成功',
        'unfavorite_success' => '取消收藏成功',
        'get_favorite_success' => '获取收藏列表成功',
        'get_favorite_list_success' => '获取收藏列表成功',
        'get_favorite_stats_success' => '获取收藏统计成功',
        'favorite_not_exist' => '收藏记录不存在',
        'delete_favorite_success' => '删除收藏成功',
        'delete_favorite_log' => '删除收藏记录',
        'need_login' => '请先登录',
    ],
    // 兼容性键，用于支持现有控制器中的调用
    'get_like_list_successful' => '获取点赞列表成功',
    'unlike_successful' => '取消点赞成功',
    'like_not_exist' => '点赞记录不存在',
    'delete_like_successful' => '删除点赞记录成功',
    'delete_like_log' => '删除文章点赞记录',
    'get_like_stats_successful' => '获取点赞统计成功',
    'get_favorite_list_success' => '获取收藏列表成功',
    'delete_favorite_success' => '删除收藏成功',
    'favorite_not_exist' => '收藏记录不存在',
    'delete_favorite_log' => '删除收藏记录',
    'get_favorite_stats_success' => '获取收藏统计成功'
];