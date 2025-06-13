#!/bin/bash

# 设置错误时退出
set -e

# 设置环境变量
export NODE_ENV=production
export VITE_DISABLE_SOURCEMAP=true
export NODE_OPTIONS="--max-old-space-size=4096"

# 清理构建目录和缓存
rm -rf dist
rm -rf node_modules/.vite

echo "Starting production build..."

# 构建步骤
echo "Step 1: Building vendor chunks..."
./node_modules/.bin/vite build --config vite.vendor.config.js

# 清理临时文件
rm -rf node_modules/.vite
sleep 2

echo "Step 2: Building app..."
./node_modules/.bin/vite build --config vite.app.config.js

# 验证构建结果
if [ ! -f "dist/index.html" ]; then
    echo "Error: index.html not found in dist directory!"
    exit 1
fi

# 清理临时缓存
rm -rf node_modules/.vite

echo "Build completed successfully!"
