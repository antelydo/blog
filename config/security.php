<?php
// +----------------------------------------------------------------------
// | 安全配置
// +----------------------------------------------------------------------

return [
    // 密码策略
    'password' => [
        'min_length' => 8,                 // 最小长度
        'require_uppercase' => true,       // 是否要求大写字母
        'require_lowercase' => true,       // 是否要求小写字母
        'require_number' => true,          // 是否要求数字
        'require_special' => true,         // 是否要求特殊字符
        'max_age' => 90,                   // 密码最大有效期（天）
        'history_count' => 5,              // 密码历史记录数量
    ],
    
    // 登录安全
    'login' => [
        'max_attempts' => 5,               // 最大尝试次数
        'lockout_time' => 1800,            // 锁定时间（秒）
        'captcha_after_failures' => 3,     // 失败多少次后显示验证码
        'two_factor_auth' => false,        // 是否启用双因素认证
        'remember_me_expiry' => 604800,    // 记住我有效期（秒）
    ],
    
    // 会话安全
    'session' => [
        'regenerate_id' => true,           // 是否重新生成会话ID
        'expire_on_close' => true,         // 关闭浏览器后会话是否过期
        'same_site' => 'lax',              // SameSite属性
        'secure' => false,                 // 是否仅通过HTTPS发送
        'http_only' => true,               // 是否禁止JavaScript访问
    ],
    
    // 内容安全
    'content' => [
        'xss_filter' => true,              // 是否启用XSS过滤
        'sql_injection_filter' => true,    // 是否启用SQL注入过滤
        'allowed_html_tags' => '<p><br><a><strong><em><ul><ol><li><h1><h2><h3><h4><h5><blockquote><pre><code><img><table><thead><tbody><tr><th><td>', // 允许的HTML标签
        'max_upload_size' => 2048,         // 最大上传大小（KB）
        'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar', // 允许的文件类型
    ],
    
    // API安全
    'api' => [
        'rate_limit' => 60,                // 速率限制（每分钟请求数）
        'rate_limit_window' => 60,         // 速率限制窗口（秒）
        'token_expiry' => 7200,            // Token有效期（秒）
        'refresh_token_expiry' => 604800,  // 刷新Token有效期（秒）
        'check_ip_binding' => true,        // 是否检查IP绑定
        'check_user_agent' => true,        // 是否检查User-Agent
    ],
    
    // 日志和监控
    'logging' => [
        'login_attempts' => true,          // 是否记录登录尝试
        'admin_actions' => true,           // 是否记录管理员操作
        'sensitive_actions' => true,       // 是否记录敏感操作
        'log_level' => 'warning',          // 日志级别
        'log_retention' => 30,             // 日志保留天数
    ],
    
    // 其他安全设置
    'misc' => [
        'force_https' => false,            // 是否强制HTTPS
        'secure_headers' => true,          // 是否添加安全响应头
        'csrf_protection' => true,         // 是否启用CSRF保护
        'honeypot' => true,                // 是否启用蜜罐
        'bot_protection' => true,          // 是否启用机器人保护
    ],
];
