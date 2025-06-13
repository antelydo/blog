<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        // 已有中间件
        'debug_log' => \app\middleware\DebugLog::class,
        'auth'      => \app\middleware\CheckApiToken::class,
        'admin'     => \app\middleware\AdminAuth::class,
        'cors'      => \app\middleware\AllowCrossDomain::class,

        // 新增安全中间件
        'security'  => \app\middleware\SecurityCheck::class,
        'content'   => \app\middleware\ContentSecurity::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [
        \app\middleware\AllowCrossDomain::class,
        \app\middleware\SecurityCheck::class,
        \app\middleware\ContentSecurity::class,
        \app\middleware\DebugLog::class,
    ],
];
