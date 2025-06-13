<?php

return [
    // 是否启用调试日志记录
    'enabled' => env('DEBUG_LOG_ENABLED', false),

    // 调试日志保留天数
    'keep_days' => env('DEBUG_LOG_KEEP_DAYS', 30),

    // 是否记录控制器调用
    'record_controller' => env('DEBUG_LOG_RECORD_CONTROLLER', true),

    // 是否记录API调用
    'record_api' => env('DEBUG_LOG_RECORD_API', true),

    // 是否记录SQL查询
    'record_sql' => env('DEBUG_LOG_RECORD_SQL', false),

    // 是否记录回调函数
    'record_callback' => env('DEBUG_LOG_RECORD_CALLBACK', false),

    // 敏感字段列表（这些字段的值将被替换为******）
    'sensitive_fields' => [
        'password', 'pwd', 'pass', 'secret', 'token', 'auth', 'key', 'api_key', 'apikey',
        'access_token', 'refresh_token', 'private', 'secret_key', 'credential'
    ],
];
