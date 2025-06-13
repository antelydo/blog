# 部署指南

本文档详细介绍了如何在不同环境中部署 AiChat UI 项目。

## 📋 部署前准备

### 系统要求

#### 最低要求
- **CPU**: 1核心
- **内存**: 1GB RAM
- **存储**: 10GB 可用空间
- **网络**: 稳定的互联网连接

#### 推荐配置
- **CPU**: 2核心或以上
- **内存**: 4GB RAM 或以上
- **存储**: 50GB SSD
- **网络**: 100Mbps 带宽

#### 软件要求
- **操作系统**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **Web服务器**: Nginx 1.18+ / Apache 2.4+
- **PHP**: 8.0+ (推荐 8.1+)
- **数据库**: MySQL 8.0+ / MariaDB 10.6+
- **缓存**: Redis 6.0+ (可选但推荐)
- **Node.js**: 16+ (构建时需要)

### PHP 扩展要求
确保安装以下 PHP 扩展：
```bash
# Ubuntu/Debian
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql php8.1-redis php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl

# CentOS/RHEL
sudo yum install php81-php-cli php81-php-fpm php81-php-mysqlnd php81-php-redis php81-php-mbstring php81-php-xml php81-php-curl php81-php-zip php81-php-gd php81-php-intl
```

## 🐳 Docker 部署 (推荐)

Docker 部署是最简单和推荐的部署方式。

### 1. 准备 Docker 环境

```bash
# 安装 Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# 安装 Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 2. 克隆项目

```bash
git clone https://github.com/your-username/aichat-ui.git
cd aichat-ui
```

### 3. 配置环境变量

```bash
# 复制配置文件
cp .env.docker .env
cp docker-compose.yml.example docker-compose.yml

# 编辑配置文件
nano .env
```

### 4. 启动服务

```bash
# 构建并启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 5. 初始化数据库

```bash
# 进入应用容器
docker-compose exec app bash

# 运行数据库迁移
php think migrate:run

# 填充初始数据
php think seed:run
```

### Docker Compose 配置示例

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./storage:/var/www/html/storage
      - ./public/uploads:/var/www/html/public/uploads
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    depends_on:
      - mysql
      - redis
    restart: unless-stopped

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    restart: unless-stopped

volumes:
  mysql_data:
  redis_data:
```

## 🖥️ 传统部署

### 1. 环境准备

#### 安装 Nginx

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx

# CentOS/RHEL
sudo yum install nginx

# 启动并设置开机自启
sudo systemctl start nginx
sudo systemctl enable nginx
```

#### 安装 PHP

```bash
# Ubuntu/Debian
sudo apt install php8.1-fpm php8.1-cli php8.1-mysql php8.1-redis php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd

# CentOS/RHEL
sudo yum install php81-php-fpm php81-php-cli php81-php-mysqlnd php81-php-redis php81-php-mbstring php81-php-xml php81-php-curl php81-php-zip php81-php-gd
```

#### 安装 MySQL

```bash
# Ubuntu/Debian
sudo apt install mysql-server

# CentOS/RHEL
sudo yum install mysql-server

# 启动并设置开机自启
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation
```

#### 安装 Redis

```bash
# Ubuntu/Debian
sudo apt install redis-server

# CentOS/RHEL
sudo yum install redis

# 启动并设置开机自启
sudo systemctl start redis
sudo systemctl enable redis
```

#### 安装 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2. 部署应用

#### 克隆代码

```bash
cd /var/www
sudo git clone https://github.com/your-username/aichat-ui.git
sudo chown -R www-data:www-data aichat-ui
cd aichat-ui
```

#### 安装依赖

```bash
# 安装 PHP 依赖
composer install --no-dev --optimize-autoloader

# 安装 Node.js (用于构建前端)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# 构建前端
cd frontend
npm ci
npm run build
cd ..
```

#### 配置环境

```bash
# 复制配置文件
cp .env.production .env

# 编辑配置文件
sudo nano .env
```

#### 设置权限

```bash
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 runtime
sudo chmod -R 777 public/storage
```

#### 初始化数据库

```bash
# 创建数据库
mysql -u root -p -e "CREATE DATABASE aichat_ui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 运行迁移
php think migrate:run

# 填充数据
php think seed:run
```

### 3. 配置 Nginx

创建 Nginx 配置文件：

```bash
sudo nano /etc/nginx/sites-available/aichat-ui
```

配置内容：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/aichat-ui/public;
    index index.php index.html;

    # 安全设置
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # 前端路由
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API 路由
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # 隐藏敏感文件
    location ~ /\. {
        deny all;
    }

    location ~ /(composer\.(json|lock)|package\.json|\.env) {
        deny all;
    }

    # Gzip 压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

启用站点：

```bash
sudo ln -s /etc/nginx/sites-available/aichat-ui /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. SSL 证书配置

使用 Let's Encrypt 免费证书：

```bash
# 安装 Certbot
sudo apt install certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d your-domain.com

# 设置自动续期
sudo crontab -e
# 添加以下行
0 12 * * * /usr/bin/certbot renew --quiet
```

## ⚡ 性能优化

### PHP 优化

编辑 `/etc/php/8.1/fpm/php.ini`：

```ini
; 基础设置
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 50M
upload_max_filesize = 50M

; OPcache 设置
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
opcache.jit_buffer_size=100M
opcache.jit=1255

; 会话设置
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
```

### MySQL 优化

编辑 `/etc/mysql/mysql.conf.d/mysqld.cnf`：

```ini
[mysqld]
# 基础设置
max_connections = 500
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# 查询缓存
query_cache_type = 0
query_cache_size = 0

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### Redis 优化

编辑 `/etc/redis/redis.conf`：

```ini
# 内存设置
maxmemory 1gb
maxmemory-policy allkeys-lru

# 持久化设置
save 900 1
save 300 10
save 60 10000

# 网络设置
tcp-keepalive 300
timeout 0
```

## 🔧 维护与监控

### 定时任务

```bash
sudo crontab -e
```

添加以下任务：

```bash
# 每天凌晨清理缓存
0 0 * * * cd /var/www/aichat-ui && php think cache:clear

# 每周优化数据库
0 2 * * 0 cd /var/www/aichat-ui && php think optimize:database

# 每月清理日志
0 3 1 * * cd /var/www/aichat-ui && php think log:clear

# 备份数据库
0 1 * * * mysqldump -u root -p aichat_ui > /backup/aichat_ui_$(date +\%Y\%m\%d).sql
```

### 日志轮转

创建 `/etc/logrotate.d/aichat-ui`：

```
/var/www/aichat-ui/runtime/log/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    postrotate
        systemctl reload php8.1-fpm
    endscript
}
```

### 监控脚本

创建简单的健康检查脚本：

```bash
#!/bin/bash
# /usr/local/bin/health-check.sh

# 检查服务状态
services=("nginx" "php8.1-fpm" "mysql" "redis")

for service in "${services[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "$(date): $service is not running" >> /var/log/health-check.log
        systemctl restart $service
    fi
done

# 检查磁盘空间
disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $disk_usage -gt 80 ]; then
    echo "$(date): Disk usage is ${disk_usage}%" >> /var/log/health-check.log
fi

# 检查内存使用
memory_usage=$(free | awk 'NR==2{printf "%.2f", $3*100/$2}')
if (( $(echo "$memory_usage > 90" | bc -l) )); then
    echo "$(date): Memory usage is ${memory_usage}%" >> /var/log/health-check.log
fi
```

设置执行权限并添加到定时任务：

```bash
sudo chmod +x /usr/local/bin/health-check.sh
sudo crontab -e
# 添加：*/5 * * * * /usr/local/bin/health-check.sh
```

## 🚨 故障排除

### 常见问题

1. **500 错误**
   - 检查 PHP 错误日志：`tail -f /var/log/php8.1-fpm.log`
   - 检查 Nginx 错误日志：`tail -f /var/log/nginx/error.log`
   - 检查文件权限

2. **数据库连接失败**
   - 检查数据库服务状态：`systemctl status mysql`
   - 验证数据库配置：`.env` 文件中的数据库设置
   - 检查防火墙设置

3. **Redis 连接失败**
   - 检查 Redis 服务状态：`systemctl status redis`
   - 验证 Redis 配置
   - 检查网络连接

4. **前端资源加载失败**
   - 检查静态文件是否存在
   - 验证 Nginx 配置
   - 检查文件权限

### 日志位置

- **应用日志**: `/var/www/aichat-ui/runtime/log/`
- **Nginx 日志**: `/var/log/nginx/`
- **PHP 日志**: `/var/log/php8.1-fpm.log`
- **MySQL 日志**: `/var/log/mysql/`
- **Redis 日志**: `/var/log/redis/`

---

通过以上步骤，您应该能够成功部署 AiChat UI 项目。如果遇到问题，请查看相关日志文件或提交 Issue。
