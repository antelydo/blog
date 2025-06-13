# PHP网站性能优化指南

本文档提供了针对本PHP网站的性能优化方案和使用说明，旨在提高网站的并发能力和响应速度，同时不改变现有的业务逻辑。

## 目录

1. [优化概述](#优化概述)
2. [快速开始](#快速开始)
3. [缓存优化](#缓存优化)
4. [数据库优化](#数据库优化)
5. [中间件优化](#中间件优化)
6. [性能监控工具](#性能监控工具)
7. [配置文件说明](#配置文件说明)
8. [常见问题](#常见问题)

## 优化概述

本优化方案主要包括以下几个方面：

- **缓存优化**：使用Redis替代文件缓存，提高缓存读写速度
- **数据库优化**：启用字段缓存和持久连接，减少数据库负担
- **中间件优化**：添加性能监控和优化中间件
- **性能监控**：提供性能监控工具和命令行工具
- **配置优化**：提供性能优化的配置文件

所有优化措施都不会改变现有的业务逻辑，可以根据实际需求逐步启用。

## 快速开始

### 1. 使用性能优化配置

复制性能优化环境变量配置文件：

```bash
cp .env.performance .env
```

然后根据实际情况修改`.env`文件中的配置。

### 2. 执行优化命令

```bash
php think performance optimize
```

此命令将执行一系列优化操作，包括清除缓存、准备路由缓存目录、优化数据库表等。

### 3. 查看系统状态

```bash
php think performance status
```

此命令将显示系统当前的性能状态，包括PHP配置、OPCache状态、数据库连接信息、缓存配置等。

## 缓存优化

### Redis缓存配置

在`.env`文件中设置以下配置启用Redis缓存：

```
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
REDIS_PREFIX=cache:
REDIS_POOL_SIZE=10
REDIS_PERSISTENT=true
```

### 会话存储优化

在`.env`文件中设置以下配置启用Redis会话存储：

```
SESSION_DRIVER=redis
SESSION_EXPIRE=7200
REDIS_SESSION_DB=1
REDIS_SESSION_PREFIX=session:
```

### 路由缓存优化

在`.env`文件中设置以下配置启用路由缓存：

```
ROUTE_CHECK_CACHE=true
ROUTE_CACHE_TYPE=redis
ROUTE_CACHE_PREFIX=route:
ROUTE_CACHE_EXPIRE=86400
```

### 页面缓存配置

在`.env`文件中设置以下配置启用页面缓存：

```
PAGE_CACHE_ENABLED=true
PAGE_CACHE_TIME=3600
```

页面缓存排除规则可以在`config/performance.php`文件中配置：

```php
'page_cache_exclude' => [
    'admin/',
    'user/profile',
    'api/auth',
],
```

## 数据库优化

### 字段缓存配置

在`.env`文件中设置以下配置启用数据库字段缓存：

```
DB_FIELDS_CACHE=true
```

### 持久连接配置

在`.env`文件中设置以下配置启用数据库持久连接：

```
DB_PERSISTENT=true
```

### 慢查询监控

在`.env`文件中设置慢查询监控阈值：

```
DB_SLOW_QUERY_THRESHOLD=1.0
```

此配置将监控执行时间超过1秒的查询，并记录到日志文件中。

### 数据库分析

使用以下命令分析数据库状态：

```bash
php think performance analyze-db
```

添加`-d`参数可以查看更详细的信息：

```bash
php think performance analyze-db -d
```

## 中间件优化

### 启用优化中间件

在应用的路由配置中添加中间件：

```php
// 在路由配置中添加
Route::group('api', function () {
    // 路由定义...
})->middleware(['cache_optimizer', 'db_optimizer']);
```

或者在全局中间件配置中添加：

编辑`app/middleware.php`文件：

```php
return [
    // 全局中间件
    \app\middleware\CacheOptimizer::class,
    \app\middleware\DbOptimizer::class,
];
```

### 中间件说明

- **CacheOptimizer**：实现页面级缓存，减少重复计算
- **DbOptimizer**：监控数据库查询性能，记录慢查询

## 性能监控工具

### 命令行工具

本项目提供了一个命令行工具用于性能监控和优化：

```bash
php think performance [action] [options]
```

可用的操作：

- **status**：显示系统性能状态
- **optimize**：执行系统优化
- **clear-cache**：清除系统缓存
- **analyze-db**：分析数据库

选项：

- **-d, --detail**：显示详细信息

示例：

```bash
# 显示系统状态
php think performance status

# 显示详细系统状态
php think performance status -d

# 执行系统优化
php think performance optimize

# 清除系统缓存
php think performance clear-cache

# 分析数据库
php think performance analyze-db
```

### OPCache状态监控

访问`/opcache-status.php`可以查看OPCache的状态（需要管理员权限）。

### 性能监控类

项目提供了`app\common\Performance`类用于性能监控，可以在代码中使用：

```php
use app\common\Performance;

// 开始计时
Performance::startTimer('my_operation');

// 执行操作
// ...

// 停止计时
$duration = Performance::stopTimer('my_operation');

// 记录数据库查询
Performance::recordDbQueries(5);

// 记录缓存命中
Performance::recordCacheHit();

// 记录缓存未命中
Performance::recordCacheMiss();

// 获取性能报告
$report = Performance::getReport();

// 记录性能报告到日志
Performance::logReport();
```

## 配置文件说明

### .env.performance

此文件包含了性能优化的环境变量配置，可以复制为`.env`文件使用。主要配置项包括：

- 应用设置（调试模式、跟踪等）
- 数据库设置（字段缓存、持久连接等）
- 缓存设置（缓存驱动、Redis配置等）
- 会话设置（会话驱动、有效期等）
- 路由设置（路由缓存等）
- 日志设置（日志通道、级别等）

### config/performance.php

此文件集中管理性能相关的配置，主要配置项包括：

- 页面缓存配置
- 数据库优化配置
- 路由优化配置
- 性能监控配置
- 缓存配置
- 会话配置
- 日志配置

## 常见问题

### 1. Redis连接失败

**问题**：启用Redis缓存后，网站无法正常运行，报Redis连接错误。

**解决方案**：
- 确认Redis服务已启动
- 检查Redis连接配置（主机、端口、密码等）
- 确认PHP已安装Redis扩展

### 2. 性能优化后网站出现异常

**问题**：应用性能优化配置后，网站某些功能出现异常。

**解决方案**：
- 执行`php think performance clear-cache`清除所有缓存
- 逐步启用优化功能，找出导致问题的配置
- 检查日志文件，查找错误信息

### 3. 数据库查询仍然很慢

**问题**：尽管进行了优化，但数据库查询仍然很慢。

**解决方案**：
- 执行`php think performance analyze-db -d`分析数据库
- 检查慢查询日志，找出慢查询
- 为频繁查询的字段添加索引
- 优化SQL语句，避免使用`SELECT *`

### 4. 缓存不生效

**问题**：配置了缓存，但似乎没有生效。

**解决方案**：
- 确认缓存驱动配置正确
- 检查缓存键是否正确
- 使用`Performance::recordCacheHit()`和`Performance::recordCacheMiss()`监控缓存命中率
- 检查缓存排除规则是否过于宽松

### 5. OPCache未启用

**问题**：OPCache配置后未生效。

**解决方案**：
- 在php.ini中设置`opcache.enable=1`
- 重启PHP服务
- 访问`/opcache-status.php`确认OPCache状态

## 联系与支持

如有任何问题或需要进一步的优化建议，请联系网站管理员。

---

文档最后更新时间：2025年4月27日
