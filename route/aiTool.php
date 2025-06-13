<?php
/**
 * AI工具管理路由配置
 */

use think\facade\Route;

// AI工具分类管理
Route::group('api/adminAiToolCategory', function () {
    Route::get('list', 'api.controller.AdminAiToolCategory/list');
    Route::get('info', 'api.controller.AdminAiToolCategory/info');
    Route::post('create', 'api.controller.AdminAiToolCategory/create');
    Route::post('update', 'api.controller.AdminAiToolCategory/update');
    Route::post('delete', 'api.controller.AdminAiToolCategory/delete');
    Route::post('updateStatus', 'api.controller.AdminAiToolCategory/updateStatus');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具管理
Route::group('api/adminAiTool', function () {
    Route::get('list', 'api.controller.AdminAiTool/list');
    Route::get('info', 'api.controller.AdminAiTool/info');
    Route::post('create', 'api.controller.AdminAiTool/create');
    Route::post('update', 'api.controller.AdminAiTool/update');
    Route::post('delete', 'api.controller.AdminAiTool/delete');
    Route::post('recommend', 'api.controller.AdminAiTool/recommend');
    Route::post('top', 'api.controller.AdminAiTool/top');
    Route::post('updateStatus', 'api.controller.AdminAiTool/updateStatus');
    Route::post('batch', 'api.controller.AdminAiTool/batch');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具标签管理
Route::group('api/adminAiToolTag', function () {
    Route::get('list', 'api.controller.AdminAiToolTag/list');
    Route::get('all', 'api.controller.AdminAiToolTag/all');
    Route::get('info', 'api.controller.AdminAiToolTag/info');
    Route::post('create', 'api.controller.AdminAiToolTag/create');
    Route::post('update', 'api.controller.AdminAiToolTag/update');
    Route::post('delete', 'api.controller.AdminAiToolTag/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolTag/batchDelete');
    Route::post('merge', 'api.controller.AdminAiToolTag/merge');
    Route::post('updateStatus', 'api.controller.AdminAiToolTag/updateStatus');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具收藏管理
Route::group('api/adminAiToolFavorite', function () {
    Route::get('list', 'api.controller.AdminAiToolFavorite/list');
    Route::get('info', 'api.controller.AdminAiToolFavorite/info');
    Route::post('delete', 'api.controller.AdminAiToolFavorite/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolFavorite/batchDelete');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具点赞管理
Route::group('api/adminAiToolLike', function () {
    Route::get('list', 'api.controller.AdminAiToolLike/list');
    Route::get('info', 'api.controller.AdminAiToolLike/info');
    Route::post('delete', 'api.controller.AdminAiToolLike/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolLike/batchDelete');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具评论管理
Route::group('api/adminAiToolComment', function () {
    Route::get('list', 'api.controller.AdminAiToolComment/list');
    Route::get('info', 'api.controller.AdminAiToolComment/info');
    Route::post('reply', 'api.controller.AdminAiToolComment/reply');
    Route::post('updateStatus', 'api.controller.AdminAiToolComment/updateStatus');
    Route::post('batchUpdateStatus', 'api.controller.AdminAiToolComment/batchUpdateStatus');
    Route::post('delete', 'api.controller.AdminAiToolComment/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolComment/batchDelete');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具访问日志管理
Route::group('api/adminAiToolVisitLog', function () {
    Route::get('list', 'api.controller.AdminAiToolVisitLog/list');
    Route::get('info', 'api.controller.AdminAiToolVisitLog/info');
    Route::get('stats', 'api.controller.AdminAiToolVisitLog/stats');
    Route::post('delete', 'api.controller.AdminAiToolVisitLog/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolVisitLog/batchDelete');
    Route::post('clear', 'api.controller.AdminAiToolVisitLog/clear');
})->middleware(\app\middleware\CheckAdminLogin::class);

// AI工具评价点赞管理
Route::group('api/adminAiToolCommentLike', function () {
    Route::get('list', 'api.controller.AdminAiToolCommentLike/list');
    Route::get('info', 'api.controller.AdminAiToolCommentLike/info');
    Route::get('statistics', 'api.controller.AdminAiToolCommentLike/statistics');
    Route::post('delete', 'api.controller.AdminAiToolCommentLike/delete');
    Route::post('batchDelete', 'api.controller.AdminAiToolCommentLike/batchDelete');
})->middleware(\app\middleware\CheckAdminLogin::class);



//前端C端  工具不需要登录
Route::group('api/tool', function () {
    Route::get('list', 'api.controller.Tool/list');    // 工具列表
    Route::get('detail', 'api.controller.Tool/detail');    // 工具详情
});

// 前端C端 工具分类
Route::group('api/ToolCategory', function () {
    Route::get('list', 'api.controller.ToolCategory/list'); // 工具分类列表
    Route::get('tree', 'api.controller.ToolCategory/tree');// 工具分类树
    Route::get('children', 'api.controller.ToolCategory/children');    // 工具分类子分类
    Route::get('detail', 'api.controller.ToolCategory/detail');    // 工具分类详情
    Route::get('getParentCat', 'api.controller.ToolCategory/getParentCat');    // 获取父级分类
});

// 标签相关接口
Route::group('api/ToolTag', function () {
    Route::get('list', 'api.controller.ToolTag/list');//工具标签列表
    Route::get('detail', 'api.controller.ToolTag/detail');//工具标签详情
    Route::get('byTool', 'api.controller.ToolTag/byTool');//工具标签详情
    Route::get('getTagName', 'api.controller.ToolTag/getTagName');//工具标签详情
});

//工具点赞
Route::group('api/toolLike', function () {
    Route::post('like', 'api.controller.ToolLike/like');    //工具点赞
    Route::post('unlike', 'api.controller.ToolLike/unlike');//工具取消点赞
});

//工具评价
Route::group('api/toolComment', function () {
    Route::post('getToolCommentList', 'api.controller.toolComment/getToolCommentList');    //工具评价列表
    Route::get('getToolCommentInfo', 'api.controller.toolComment/getToolCommentInfo');    //工具评价详情
    Route::get('getToolComRatingPercentage', 'api.controller.toolComment/getToolComRatingPercentage');    //获取工具评价星级百分比
});

//工具评价点赞
Route::group('api/toolCommentLike', function () {
    Route::post('like', 'api.controller.toolCommentLike/like');    //工具评价点赞
    Route::post('unlike', 'api.controller.toolCommentLike/unlike');    //工具评价取消点赞
});

// 需要登录的接口
Route::group(function () {
    //工具评价
    Route::group('api/toolComment', function () {
        Route::post('addToolCom', 'api.controller.ToolComment/addToolCom');    //工具评价
        Route::post('toolReply', 'api.controller.ToolComment/toolReply');    //回复工具评价
    });

    // 工具收藏
    Route::group('api/toolFavorite', function () {
        Route::post('favorite', 'api.controller.ToolFavorite/favorite');//工具收藏
        Route::post('unfavorite', 'api.controller.ToolFavorite/unfavorite');//工具取消收藏
    });
})->middleware(\app\middleware\CheckUserLogin::class);


// 前端C端用户路由组
Route::group('api/user', function () {
    Route::get('toolFavoritesList', 'api.controller.User/toolFavoritesList');//获取用户工具收藏列表
    Route::get('toolLikesList', 'api.controller.User/toolLikesList');//获取用户工具点赞列表
    Route::get('toolCommentsList', 'api.controller.User/toolCommentsList');//  获取用户工具评论列表
    Route::get('toolViewList', 'api.controller.User/toolViewList');//获取用户工具浏览列表
})->middleware(\app\middleware\CheckUserLogin::class);