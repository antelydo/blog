# Token系统优化说明文档

## 1. 全局配置文件

在 `config/token.php` 中集中管理所有Token相关配置：

```php
return [
    // Token配置
    'expire_time' => 7200, // Token有效期（秒）
    'refresh_expire_time' => 604800, // 刷新Token有效期（秒）
    
    // 缓存配置
    'cache_type' => 'file', // 缓存类型：file, redis, memcache等
    'cache_prefix' => 'token_', // 缓存前缀
    'cache_tag' => 'token', // 缓存标签
    
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
        'allow_list' => ['zh-cn', 'en'], // 允许的语言列表
    ],
    
    // 安全配置
    'secret_key' => 'e7a8f2c6b5d9e1f0a3c5b7d9e1f3a5c7b9e1f3a5c7b9e1f3a5c7b9e1f3a5c7b9', // 安全密钥
    'check_ip_binding' => true, // 是否检查IP绑定
    'blacklist_prefix' => 'token_blacklist_', // 黑名单缓存前缀
    'enable_activity_log' => true, // 是否启用活动日志
    'enable_log' => true, // 是否启用系统日志
];

## 2. Token类优化
Token类( app\common\Token )已进行以下优化：

- 添加了获取配置的辅助方法
- 添加了获取缓存键名的辅助方法
- 所有方法都使用全局配置
- 支持缓存标签，便于管理和清除缓存
- 添加了清除所有Token的方法
- 支持多种缓存驱动切换
- 增强了安全性，使用HMAC-SHA256算法生成签名
- 添加了Token黑名单机制，防止Token被重用
- 支持IP绑定，防止Token被盗用
- 增加了用户活动日志记录功能
主要方法：

- generateToken ：生成Token
- verifyToken ：验证Token
- refreshToken ：刷新Token
- clearToken ：清除Token
- getRequestToken ：从请求中获取Token
- checkApiToken ：API接口Token验证
- setUserLanguage ：设置用户语言
- clearAllTokens ：清除所有Token
- forceLogout ：强制用户下线
- addTokenToBlacklist ：将Token添加到黑名单
- isTokenBlacklisted ：检查Token是否在黑名单中
- logActivity ：记录用户活动日志
## 3. 路由配置优化
路由配置( route\app.php )已进行以下优化：

- 使用全局配置来设置路由组名称和中间件
- 提供默认值，确保配置缺失时仍能正常工作
- 支持动态切换路由组和中间件
- 支持多语言路由
- 支持动态路由参数
- 支持路由别名

// 认证相关路由
Route::group($tokenConfig['route']['auth_group'] ?? 'auth', function () {
    // 管理员登录
    Route::post('admin/login', 'Auth/adminLogin');
    // 用户登录
    Route::post('user/login', 'Auth/userLogin');
    // 退出登录
    Route::post('logout', 'Auth/logout');
    // 刷新Token
    Route::post('refresh', 'Auth/refreshToken');
    // 设置语言
    Route::post('language', 'Auth/setLanguage');
});

// 需要管理员权限的API
Route::group($tokenConfig['route']['admin_group'] ?? 'admin', function () {
    // 示例接口
    Route::get('info', 'Admin/info');
})->middleware($tokenConfig['middleware']['admin'] ?? \app\middleware\CheckApiToken::class, 'admin');

// 需要用户权限的API
Route::group($tokenConfig['route']['user_group'] ?? 'user', function () {
    // 示例接口
    Route::get('info', 'User/info');
})->middleware($tokenConfig['middleware']['user'] ?? \app\middleware\CheckApiToken::class, 'user');

## 4. 中间件优化
中间件( app\middleware\CheckApiToken )已进行以下优化：

- 使用全局配置来验证语言设置
- 支持从请求头中获取语言设置
- 使用语言包中的消息
- 增强了错误处理和日志记录

public function handle($request, \Closure $next, string $type = 'user')
{
    // 获取请求中的语言设置
    $lang = $request->header('Accept-Language');
    if (!empty($lang)) {
        // 检查是否在允许的语言列表中
        $allowList = Config::get('token.lang.allow_list', ['zh-cn', 'en']);
        if (in_array($lang, $allowList)) {
            // 设置语言
            Lang::setLangSet($lang);
        }
    }
    
    // 验证token
    $userData = Token::checkApiToken($type);
    
    // 验证失败
    if (!$userData) {
        return json([
            'code' => 401,
            'msg' => Lang::get('token.unauthorized'),
            'data' => null
        ]);
    }
    
    // 将用户数据绑定到请求对象
    $request->userData = $userData;
    
    return $next($request);
}


## 5. 多语言支持
- 在 app\lang\zh-cn\token.php 和 app\lang\en\token.php 中添加了语言包
- 支持通过请求头 Accept-Language 切换语言
- 支持通过API接口 /auth/language 设置用户语言偏好
- 用户语言偏好会保存在Token缓存和数据库中
zh-cn语言包示例：

return [
    'unauthorized' => '未授权或授权已过期',
    'login_failed' => '用户名或密码错误',
    'login_success' => '登录成功',
    'logout_success' => '退出成功',
    'logout_failed' => '退出失败',
    'refresh_success' => 'Token刷新成功',
    'refresh_failed' => 'Token刷新失败',
];

en语言包示例：
return [
    'unauthorized' => 'Unauthorized or authorization expired',
    'login_failed' => 'Username or password incorrect',
    'login_success' => 'Login successful',
    'logout_success' => 'Logout successful',
    'logout_failed' => 'Logout failed',
    'refresh_success' => 'Token refreshed successfully',
    'refresh_failed' => 'Token refresh failed',
];

## 6. 数据库字段
需要在admin和user表中添加以下字段：

- token ：存储用户的Token
- token_expire ：存储Token的过期时间
- lang ：存储用户的语言偏好
- last_login_ip ：记录最后登录IP
- last_login_time ：记录最后登录时间
- login_count ：记录登录次数
示例SQL：


ALTER TABLE `ai_admin` 
ADD COLUMN `token` VARCHAR(255) NULL COMMENT 'Token' AFTER `password`,
ADD COLUMN `token_expire` INT(11) NULL DEFAULT 0 COMMENT 'Token过期时间' AFTER `token`,
ADD COLUMN `lang` VARCHAR(10) NULL DEFAULT 'zh-cn' COMMENT '语言偏好' AFTER `token_expire`,
ADD COLUMN `last_login_ip` VARCHAR(50) NULL COMMENT '最后登录IP' AFTER `lang`,
ADD COLUMN `last_login_time` INT(11) NULL DEFAULT 0 COMMENT '最后登录时间' AFTER `last_login_ip`,
ADD COLUMN `login_count` INT(11) NULL DEFAULT 0 COMMENT '登录次数' AFTER `last_login_time`;

ALTER TABLE `ai_user` 
ADD COLUMN `token` VARCHAR(255) NULL COMMENT 'Token' AFTER `password`,
ADD COLUMN `token_expire` INT(11) NULL DEFAULT 0 COMMENT 'Token过期时间' AFTER `token`,
ADD COLUMN `lang` VARCHAR(10) NULL DEFAULT 'zh-cn' COMMENT '语言偏好' AFTER `token_expire`,
ADD COLUMN `last_login_ip` VARCHAR(50) NULL COMMENT '最后登录IP' AFTER `lang`,
ADD COLUMN `last_login_time` INT(11) NULL DEFAULT 0 COMMENT '最后登录时间' AFTER `last_login_ip`,
ADD COLUMN `login_count` INT(11) NULL DEFAULT 0 COMMENT '登录次数' AFTER `last_login_time`;


## 7. 用户活动日志表
为了记录用户活动，需要创建用户活动日志表：

CREATE TABLE `ai_user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `user_type` varchar(20) NOT NULL COMMENT '用户类型',
  `action` varchar(50) NOT NULL COMMENT '操作类型',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `ip` varchar(50) NOT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户代理',
  `data` text DEFAULT NULL COMMENT '附加数据',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_action` (`action`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户活动日志';


也可以使用ThinkPHP的迁移功能创建表：
// 在命令行中执行
php think migrate:create CreateUserActivityLog

// 然后编辑迁移文件
public function change()
{
    $table = $this->table('user_activity_log', ['engine' => 'InnoDB', 'comment' => '用户活动日志']);
    $table->addColumn('user_id', 'integer', ['comment' => '用户ID'])
          ->addColumn('user_type', 'string', ['limit' => 20, 'comment' => '用户类型'])
          ->addColumn('action', 'string', ['limit' => 50, 'comment' => '操作类型'])
          ->addColumn('description', 'string', ['limit' => 255, 'comment' => '描述'])
          ->addColumn('ip', 'string', ['limit' => 50, 'comment' => 'IP地址'])
          ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
          ->addColumn('data', 'text', ['null' => true, 'comment' => '附加数据'])
          ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
          ->addIndex(['user_id', 'user_type'], ['name' => 'idx_user'])
          ->addIndex('action', ['name' => 'idx_action'])
          ->addIndex('create_time', ['name' => 'idx_create_time'])
          ->create();
}

// 执行迁移
php think migrate:run


## 8. 使用方法
### 8.1 切换语言
- 前端可以通过设置 Accept-Language 请求头来指定语言
- 也可以调用 /auth/language 接口设置用户的语言偏好

// 前端设置请求头示例
axios.defaults.headers.common['Accept-Language'] = 'en';

// 或者调用接口设置语言
axios.post('/auth/language', {
  lang: 'en',
  type: 'user' // 或 'admin'
}, {
  headers: {
    'Authorization': 'Bearer ' + token
  }
});


### 8.2 添加新语言
- 在 app/lang 目录下创建新的语言文件夹和对应的语言文件
- 在 config/token.php 的 lang.allow_list 中添加新语言

// 添加日语支持
// 1. 创建语言文件 app/lang/ja/token.php
return [
    'unauthorized' => '権限がないか、認証の期限が切れています',
    'login_failed' => 'ユーザー名またはパスワードが間違っています',
    'login_success' => 'ログイン成功',
    'logout_success' => 'ログアウト成功',
    'logout_failed' => 'ログアウト失敗',
    'refresh_success' => 'トークンの更新に成功しました',
    'refresh_failed' => 'トークンの更新に失敗しました',
];

// 2. 在配置中添加日语
'lang' => [
    'default' => 'zh-cn',
    'allow_list' => ['zh-cn', 'en', 'ja'],
],


### 8.3 配置缓存方式
- 在 config/token.php 的 cache_type 中设置缓存类型
- 支持file, redis, memcache等多种缓存驱动

// 使用Redis缓存
'cache_type' => 'redis',

// 需要在config/cache.php中配置Redis连接
'redis' => [
    'type'     => 'redis',
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'password' => '',
    'select'   => 0,
    'timeout'  => 0,
    'expire'   => 0,
    'persistent' => false,
    'prefix'   => '',
],

### 8.4 修改Token有效期
- 在 config/token.php 的 expire_time 中设置Token有效期
- 在 config/token.php 的 refresh_expire_time 中设置刷新Token有效期

// 设置Token有效期为1小时
'expire_time' => 3600,

// 设置刷新Token有效期为30天
'refresh_expire_time' => 2592000,


### 8.5 强制用户下线
- 管理员可以调用 Token::forceLogout 方法强制用户下线

// 在控制器中
public function forceLogout()
{
    $userId = Request::param('user_id', 0, 'intval');
    $type = Request::param('type', 'user');
    
    if (Token::forceLogout($userId, $type)) {
        return json([
            'code' => 200,
            'msg' => '强制下线成功',
            'data' => null
        ]);
    }
    
    return json([
        'code' => 400,
        'msg' => '强制下线失败',
        'data' => null
    ]);
}

## 9. 安全建议
### 9.1 基本安全措施
- 在生产环境中使用HTTPS
- 定期清理过期的Token
- 对敏感API添加额外的安全验证
- 监控异常的Token使用行为
### 9.2 密钥管理
- 使用随机生成的长字符串作为安全密钥
- 不同环境使用不同的密钥
- 定期更换密钥

// 生成随机密钥
$secretKey = bin2hex(random_bytes(32));
echo $secretKey; // 输出生成的密钥


### 9.3 IP绑定
- 默认启用IP绑定，防止Token被盗用
- 对于移动设备或网络环境不稳定的场景，可以关闭IP绑定

// 关闭IP绑定
'check_ip_binding' => false,

### 9.4 Token黑名单
- 当用户登出或刷新Token时，旧Token会被加入黑名单
- 黑名单中的Token无法再次使用，防止重放攻击
### 9.5 活动日志
- 记录用户的登录、登出、Token刷新等活动
- 可以通过日志追踪异常行为
// 查询用户活动日志示例
$logs = Db::name('user_activity_log')
    ->where('user_id', $userId)
    ->where('user_type', $type)
    ->order('create_time', 'desc')
    ->limit(10)
    ->select();

    ## 10. 性能优化
### 10.1 缓存选择
- 对于高并发场景，建议使用Redis或Memcached作为缓存驱动
- 文件缓存适合小型应用或开发环境
### 10.2 数据库优化
- 为token_expire字段添加索引，加速查询
- 定期清理过期的Token记录
ALTER TABLE `ai_admin` ADD INDEX `idx_token_expire` (`token_expire`);
ALTER TABLE `ai_user` ADD INDEX `idx_token_expire` (`token_expire`);


### 10.3 日志优化
- 对于高流量网站，可以考虑异步记录日志
- 定期归档或清理旧的日志记录
## 11. 故障排除
### 11.1 常见问题
- Token验证失败：检查Token是否过期、是否在黑名单中、IP是否变化
- 缓存问题：检查缓存配置是否正确、缓存服务是否可用
- 数据库问题：检查数据库连接是否正常、表结构是否正确
### 11.2 调试方法
- 启用日志记录，查看详细的错误信息
- 使用Token::verifyToken方法手动验证Token
- 检查缓存中的Token数据

// 调试Token
$token = Request::param('token', '');
$type = Request::param('type', 'user');
$userData = Token::verifyToken($token, $type, false); // 不检查IP
var_dump($userData);

### 11.3 常见错误码
- 401：未授权或授权已过期
- 400：请求参数错误
- 403：权限不足
- 500：服务器内部错误
## 12. 升级指南
如果您从旧版本升级到新版本，需要执行以下步骤：

1. 更新配置文件：添加新的安全配置项
2. 更新数据库表结构：添加新的字段
3. 创建用户活动日志表
4. 更新Token类和中间件
5. 添加语言包文件

