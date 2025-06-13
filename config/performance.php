<?php
// +----------------------------------------------------------------------
// | 性能优化配置
// +----------------------------------------------------------------------

return [
    // 页面缓存
    'page_cache_enabled' => env('PAGE_CACHE_ENABLED', false),
    'page_cache_time' => env('PAGE_CACHE_TIME', 3600),
    'page_cache_exclude' => [
        'admin/',
        'user/profile',
        'api/auth',
    ],
    
    // 数据库优化
    'db_fields_cache' => env('DB_FIELDS_CACHE', true),
    'db_persistent' => env('DB_PERSISTENT', false),
    'db_slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1.0), // 秒
    
    // 路由优化
    'route_check_cache' => env('ROUTE_CHECK_CACHE', false),
    'route_cache_type' => env('ROUTE_CACHE_TYPE', 'file'),
    
    // 性能监控
    'performance_monitor_enabled' => env('PERFORMANCE_MONITOR_ENABLED', false),
    'performance_time_threshold' => env('PERFORMANCE_TIME_THRESHOLD', 1.0), // 秒
    'performance_query_threshold' => env('PERFORMANCE_QUERY_THRESHOLD', 20), // 查询次数
    'performance_sample_rate' => env('PERFORMANCE_SAMPLE_RATE', 0.01), // 采样率
    
    // 缓存配置
    'cache_driver' => env('CACHE_DRIVER', 'file'),
    'redis_pool_size' => env('REDIS_POOL_SIZE', 0),
    'redis_persistent' => env('REDIS_PERSISTENT', false),
    
    // 会话配置
    'session_driver' => env('SESSION_DRIVER', 'file'),
    
    // 日志配置
    'log_channel' => env('LOG_CHANNEL', 'file'),
    'log_level' => env('LOG_LEVEL', 'debug'),
    'log_max_files' => env('LOG_MAX_FILES', 30),
    
    // 调试配置
    'debug_log_enabled' => env('DEBUG_LOG_ENABLED', true),
];
