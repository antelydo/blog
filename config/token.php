<?php

return [
    // Token配置
    'expire_time' => env('TOKEN_EXPIRE', 7200), // Token有效期（秒）
    'refresh_expire_time' => env('TOKEN_REFRESH_EXPIRE', 604800), // 刷新Token有效期（秒）

    // 缓存配置
    'cache_type' => env('TOKEN_CACHE_TYPE', 'file'), // 缓存类型：file, redis, memcache等

    'cache_prefix' => 'token_', // 缓存前缀
    'cache_tag' => 'token_tag', // 缓存标签

    // 中间件配置
    'middleware' => [
        'admin' => \app\middleware\CheckApiToken::class,
        'user' => \app\middleware\CheckApiToken::class,
    ],

    // 路由配置
    'route' => [
        'auth_group' => 'auth', // 认证路由组名称
        'admin_group' => 'admin', // 管理员路由组名称
        'user_group' => 'user', // 用户路由组名称
    ],

    // 多语言配置
    'lang' => [
        'default' => 'zh-cn', // 默认语言
        'allow_list' => ['zh-cn', 'en-us'], // 允许的语言列表
    ],

    // 安全配置
    'secret_key' => env('TOKEN_SECRET_KEY', 'e7a8f2c6b5d9e1f0a3c5b7d9e1f3a5c7b9e1f3a5c7b9e1f3a5c7b9e1f3a5c7b9'), // 安全密钥
    'check_ip_binding' => env('TOKEN_CHECK_IP', true), // 是否检查IP绑定
    'blacklist_prefix' => 'token_blacklist_', // 黑名单缓存前缀
    'enable_activity_log' => true, // 是否启用活动日志
    'enable_log' => true, // 是否启用系统日志

    // 增强安全配置
    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5), // 最大登录尝试次数
    'login_lockout_time' => env('LOGIN_LOCKOUT_TIME', 1800), // 登录锁定时间（秒）
    'token_rotation' => env('TOKEN_ROTATION', false), // 是否启用Token轮换
    'token_rotation_interval' => env('TOKEN_ROTATION_INTERVAL', 3600), // Token轮换间隔（秒）
    'check_user_agent' => env('CHECK_USER_AGENT', true), // 是否检查User-Agent
    'rate_limit' => env('API_RATE_LIMIT', 60), // API请求限制（每分钟）
];