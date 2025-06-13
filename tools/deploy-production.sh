#!/bin/bash

# 生产环境部署脚本
echo "开始部署生产环境..."

# 1. 后端部署
echo "开始部署后端..."

# 更新代码
git pull origin main

# 安装composer依赖
composer install --no-dev --optimize-autoloader

# 复制生产环境配置
cp .env.performance .env

# 设置目录权限
chmod -R 755 .
chmod -R 777 runtime
chmod -R 777 public/uploads

# 清理缓存
php think clear
php think performance optimize

# 2. 前端部署
echo "开始部署前端..."
cd frontend

# 安装npm依赖
npm install

# 构建生产环境代码
npm run build

# 将构建后的文件复制到生产目录
rm -rf ../public/dist
mv dist ../public/

# 返回项目根目录
cd ..

# 3. 优化配置
echo "优化生产环境配置..."

# 设置PHP配置
php -r "file_put_contents('.user.ini', 'opcache.enable=1\nopcache.enable_cli=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=4000\nopcache.revalidate_freq=60\nopcache.fast_shutdown=1\n');"

# 4. 重启服务
echo "重启服务..."
php think service restart

echo "部署完成!"

# 5. 检查部署状态
echo "检查部署状态..."
php think performance status