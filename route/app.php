<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: antelydo <antelydo@gmail.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | 路由设置 - 统一API路由定义
// +----------------------------------------------------------------------

use think\facade\Route;
use think\facade\Config;

// // 开启多模块URL自动解析 `8.1+`版本开始支持
Route::auto();

// 前端用户路由组
Route::group('api/user', function () {
    // 无需登录的接口
    Route::post('login', 'api.controller.User/login');
    Route::post('register', 'api.controller.User/register');

    // 需要登录的接口
    Route::group(function () {
        Route::post('logout', 'api.controller.User/logout');
        Route::get('info', 'api.controller.User/info');
        Route::post('update', 'api.controller.User/update');
        Route::post('changePassword', 'api.controller.User/changePassword');
        Route::post('uploadAvatar', 'api.controller.User/uploadAvatar');
        Route::get('messages', 'api.controller.User/messages');
        Route::post('readMessage', 'api.controller.User/readMessage');
        Route::get('vipInfo', 'api.controller.User/vipInfo');
        Route::get('orders', 'api.controller.User/orders');
        Route::post('create_order', 'api.controller.User/createOrder');
        Route::post('cancelOrder', 'api.controller.User/cancelOrder');
    })->middleware(\app\middleware\CheckUserLogin::class);


    // 文章
    Route::group('post', function () {
        Route::get('list', 'api.controller.Post/list');
        Route::get('detail', 'api.controller.Post/detail');
        Route::get('recommend', 'api.controller.Post/recommend');
        Route::get('hot', 'api.controller.Post/hot');
        Route::get('banner_top', 'api.controller.Post/bannerTop');
        //更新文章浏览时长
        Route::post('updateViewDuration', 'api.controller.Post/updateViewDuration');
    });

    // 分类
    Route::group('category', function () {
        Route::get('list', 'api.controller.Cat/list');
        Route::get('detail', 'api.controller.Cat/detail');
        Route::get('withCount', 'api.controller.Cat/withCount');
        Route::get('categoryList', 'api.controller.Cat/getCatList');
    });

    // 标签
    Route::group('tag', function () {
        Route::get('list', 'api.controller.Tag/list');
        Route::get('detail', 'api.controller.Tag/detail');
        Route::get('withCount', 'api.controller.Tag/withCount');
        Route::get('hot', 'api.controller.Tag/hot');
    });

    // 点赞
    Route::group('like', function () {
        Route::post('add', 'api.controller.Like/add');
        Route::post('cancel', 'api.controller.Like/cancel');
        Route::get('check', 'api.controller.Like/check');
        Route::Post('likeStatus', 'api.controller.Like/likeStatus');
        Route::Post('guestLikeStatus', 'api.controller.Like/guestLikeStatus');
        Route::post('likePost', 'api.controller.Like/likePost'); //点赞文章
        Route::post('unlikePost', 'api.controller.Like/unlikePost');//  取消点赞
    });

    // 评论
    Route::group('comment', function () {
        Route::get('list', 'api.controller.Comment/list');
        Route::post('add', 'api.controller.Comment/add');
    });

    // 评论点赞
    Route::group('commentLike', function () {
        Route::post('like', 'api.controller.CommentLike/like');
        Route::post('unlike', 'api.controller.CommentLike/unlike');
        Route::post('status', 'api.controller.CommentLike/status');
    });

    // 文章收藏
    Route::group('favorite', function () {
        Route::post('add', 'api.controller.Favorite/add');
        Route::post('cancel', 'api.controller.Favorite/cancel');
        Route::post('status', 'api.controller.Favorite/status');
        Route::get('list', 'api.controller.Favorite/getUserFavorites');
    });
    // 友情链接
    Route::group('link', function () {
        Route::get('list', 'api.controller.Link/list');
        Route::get('getLinkTypeList', 'api.controller.Link/getLinkTypeList');
    });
    // 联系表单
    Route::group('contact', function () {
        Route::post('submitForm', 'api.controller.Contact/submitForm');
    });
    // 搜索
    Route::group('search', function () {
        Route::get('index', 'api.controller.Search/index');
    });
    //上传
    Route::group('upload', function () {
        Route::post('image', 'api.controller.Upload/image');
    } );
    //统计
    Route::group('statistics', function () {
        Route::get('webStatistics', 'api.controller.Statistics/webStatistics');
        Route::get('getStatistics', 'api.controller.Statistics/getStatistics');
    });
    //更新网站浏览时长
    Route::group('statistics', function () {
        Route::post('updateSiteDuration', 'api.controller.Statistics/updateSiteDuration');
    });
});

// 后台管理路由组
Route::group('api/admin', function () {

    // 无需登录的接口
    Route::group('user', function () {
        Route::post('login', 'api.controller.admin/login');
        Route::post('upload', 'api.controller.admin/upload');

        // 需要登录的接口
        Route::group(function () {
            Route::post('logout', 'api.controller.admin/logout');
            Route::get('info', 'api.controller.admin/info');
            Route::post('update', 'api.controller.admin/update');
            Route::post('change_password', 'api.controller.admin/changePassword');
            Route::post('upload_avatar', 'api.controller.admin/uploadAvatar');
            Route::get('list', 'api.controller.admin/list');
            Route::post('add', 'api.controller.admin/add');
            Route::post('edit', 'api.controller.admin/edit');
            Route::post('delete', 'api.controller.admin/delete');
            Route::get('userList', 'api.controller.admin/userList');
            Route::post('userDetail', 'api.controller.admin/userDetail');
            Route::post('updateUserStatus', 'api.controller.admin/updateUserStatus');
            Route::post('editUser', 'api.controller.admin/editUser');
            Route::post('deleteUser', 'api.controller.admin/deleteUser');
            Route::post('getConfig', 'api.controller.admin/getConfig');
            Route::post('updateConfig', 'api.controller.admin/updateConfig');
            Route::get('activityLog', 'api.controller.admin/activityLog');
            Route::post('statistics', 'api.controller.admin/statistics');
            Route::post('clearCache', 'api.controller.admin/clearCache');
            Route::get('systemLog', 'api.controller.admin/systemLog');
        })->middleware(\app\middleware\CheckAdminLogin::class);
    });
});

// 文章管理
Route::group('api/adminPost', function () {

    Route::group('post', function () {
        Route::get('list', 'api.controller.adminPost/list');
        Route::get('info', 'api.controller.adminPost/info');
        Route::post('create', 'api.controller.adminPost/create');
        Route::post('update', 'api.controller.adminPost/update');
        Route::post('delete', 'api.controller.adminPost/delete');
        Route::post('top', 'api.controller.adminPost/top');
        Route::post('recommend', 'api.controller.adminPost/recommend');
        Route::post('setBannerTop', 'api.controller.adminPost/setBannerTop');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 分类管理
Route::group('api/adminCat', function () {
    // 分类管理
    Route::group('category', function () {
        Route::get('list', 'api.controller.adminCat/list');
        Route::post('create', 'api.controller.adminCat/create');
        Route::post('update', 'api.controller.adminCat/update');
        Route::post('delete', 'api.controller.adminCat/delete');
        Route::post('updateStatus', 'api.controller.adminCat/updateStatus');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 标签管理
Route::group('api/adminTag', function () {
    // 标签管理
    Route::group('tag', function () {
        Route::get('list', 'api.controller.adminTag/list');
        Route::post('create', 'api.controller.adminTag/create');
        Route::post('update', 'api.controller.adminTag/update');
        Route::post('delete', 'api.controller.adminTag/delete');
        Route::post('updateStatus', 'api.controller.adminTag/updateStatus');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});


// 点赞管理
Route::group('api/adminLike', function () {
    // 点赞管理
    Route::group('like', function () {
        Route::get('list', 'api.controller.adminLike/list');
        Route::post('delete', 'api.controller.adminLike/delete');
        Route::get('stats', 'api.controller.adminLike/stats');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 评论点赞管理
Route::group('api/adminCommentLike', function () {
    // 评论点赞管理
    Route::group('commentLike', function () {
        Route::get('list', 'api.controller.AdminCommentLike/list');
        Route::post('delete', 'api.controller.AdminCommentLike/delete');
        Route::get('stats', 'api.controller.AdminCommentLike/stats');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 文章收藏管理
Route::group('api/adminFavorite', function () {
    // 文章收藏管理
    Route::group('favorite', function () {
        Route::get('list', 'api.controller.AdminFavorite/list');
        Route::post('delete', 'api.controller.AdminFavorite/delete');
        Route::get('stats', 'api.controller.AdminFavorite/stats');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 评论管理
Route::group('api/adminComment', function () {
    // 评论管理
    Route::group('comment', function () {
        Route::get('list', 'api.controller.adminComment/list');
        Route::post('updateStatus', 'api.controller.adminComment/updateStatus');
        Route::post('delete', 'api.controller.adminComment/delete');
        Route::post('reply', 'api.controller.adminComment/reply');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});


// 友情链接
Route::group('api/adminLink', function () {
    // 友情链接
    Route::group('link', function () {
        Route::get('list', 'api.controller.adminLink/list');
        Route::post('add', 'api.controller.adminLink/add');
        Route::post('update', 'api.controller.adminLink/update');
        Route::post('delete', 'api.controller.adminLink/delete');
        Route::post('upload', 'api.controller.adminLink/upload');
        Route::post('updateSort', 'api.controller.adminLink/updateSort');
        Route::post('updateStatus', 'api.controller.adminLink/updateStatus');
        Route::post('getLinkTypeList', 'api.controller.adminLink/getLinkTypeList');
    })->middleware(\app\middleware\CheckAdminLogin::class);
});

// 联系表单
Route::group('api/adminContact', function () {
    Route::get('getMessages', 'api.controller.adminContact/getMessages');
    Route::get('getMessage', 'api.controller.adminContact/getMessage');
    Route::post('updateStatus', 'api.controller.adminContact/updateStatus');
    Route::post('replyMessage', 'api.controller.adminContact/replyMessage');
    Route::post('deleteMessage', 'api.controller.adminContact/deleteMessage');
})->middleware(\app\middleware\CheckAdminLogin::class);






// 获取Token配置
$tokenConfig = Config::get('token', []);

// 认证相关路由
Route::group('api/' . ($tokenConfig['route']['auth_group'] ?? 'auth'), function () {
    // 用户登录
    Route::post('user/login', 'api.controller.Auth/userLogin');
    // 退出登录
    Route::post('logout', 'api.controller.Auth/logout');
    // 刷新Token
    Route::post('refresh', 'api.controller.Auth/refreshToken');
    // 设置语言
    Route::post('language', 'api.controller.Auth/setLanguage');
    // 注释掉管理员登录，统一使用api/admin/login接口
    // Route::post('admin/login', 'api.controller.Auth/adminLogin');
});

// 需要管理员权限的API
Route::group('api/' . ($tokenConfig['route']['admin_group'] ?? 'admin'), function () {
    // 示例接口
    Route::get('info', 'api.controller.admin/info');
    // 强制用户下线
    Route::post('force_logout', 'api.controller.Auth/forceLogout');
    // 获取用户活动日志
    Route::get('activity_logs', 'api.controller.Auth/getActivityLogs');
    // 清除所有Token（仅超级管理员）
    Route::post('clear_all_tokens', 'api.controller.Auth/clearAllTokens');
})->middleware($tokenConfig['middleware']['admin'] ?? \app\middleware\CheckApiToken::class, 'admin');

// 需要用户权限的API
Route::group('api/' . ($tokenConfig['route']['user_group'] ?? 'user'), function () {
    // 示例接口
    Route::get('info', 'api.controller.User/info');
})->middleware($tokenConfig['middleware']['user'] ?? \app\middleware\CheckApiToken::class, 'user');

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

//全局网站配置
Route::group('api/web', function () {
    Route::get('getConfig', 'api.controller.Web/getConfig');
});

// 语言切换路由
Route::group('api/lang', function () {
    Route::get('change', 'api.controller.Lang/change');
    Route::get('current', 'api.controller.Lang/current');
});

// 测试路由
Route::group('api/test', function () {
    Route::get('lang', 'api.controller.Test/lang');
    Route::get('test_pagination_combinations', 'api.controller.Test/testPaginationCombinations');
    Route::get('test_pagination', 'api.controller.Test/testPagination');
});

// API路由
Route::group('api/api', function () {
    Route::get('/', 'api.controller.Api/index');
    Route::get('test', 'api.controller.Api/test');
    Route::post('test', 'api.controller.Api/test');
});




// 默认首页
Route::get('/', function () {
    return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>AichatUi</h1><p> ThinkPHP V' . \think\facade\App::version() . '<br/><span style="font-size:30px;">API服务已启动</span></p></div>';
});

// 404页面
Route::miss(function () {
    return json(['code' => 404, 'msg' => '接口不存在']);
});
