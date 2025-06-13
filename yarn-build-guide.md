# Yarn 打包教程

本文档提供了使用 Yarn 进行项目依赖管理和打包构建的完整指南。

## 目录

1. [Yarn 简介](#yarn-简介)
2. [安装 Yarn](#安装-yarn)
3. [迁移到 Yarn](#迁移到-yarn)
4. [基本命令](#基本命令)
5. [依赖管理](#依赖管理)
6. [构建流程](#构建流程)
7. [优化策略](#优化策略)
8. [常见问题](#常见问题)

## Yarn 简介

Yarn 是一个快速、可靠、安全的依赖管理工具，由 Facebook 开发，用于替代 npm。Yarn 的主要优势包括：

- **速度更快**：并行安装和缓存机制
- **更可靠**：使用锁文件确保依赖版本一致性
- **更安全**：严格的包完整性检查
- **离线模式**：支持离线安装已缓存的包

## 安装 Yarn

### 全局安装

```bash
# 使用 npm 安装
npm install -g yarn

# 验证安装
yarn --version
```

### 使用包管理器安装

```bash
# Windows (使用 Chocolatey)
choco install yarn

# macOS (使用 Homebrew)
brew install yarn

# Linux (使用 apt)
curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
sudo apt update && sudo apt install yarn
```

## 迁移到 Yarn

### 从 npm 迁移到 Yarn

1. 确保你的项目中有 `package.json` 文件
2. 在项目根目录下运行：

```bash
# 删除 node_modules 目录和 package-lock.json
rm -rf node_modules
rm package-lock.json

# 使用 Yarn 安装依赖
yarn
```

这将生成 `yarn.lock` 文件，用于锁定依赖版本。

### 更新 CI/CD 配置

如果你使用 CI/CD 流程，需要更新相关配置：

```yaml
# 示例 .travis.yml 配置
install:
  - yarn install --frozen-lockfile

# 示例 GitHub Actions 配置
steps:
  - uses: actions/setup-node@v3
    with:
      node-version: '16'
      cache: 'yarn'
  - run: yarn install --frozen-lockfile
```

## 基本命令

### 初始化项目

```bash
yarn init
```

### 安装依赖

```bash
# 安装所有依赖
yarn

# 安装特定依赖
yarn add [package]

# 安装开发依赖
yarn add [package] --dev

# 全局安装
yarn global add [package]
```

### 移除依赖

```bash
yarn remove [package]
```

### 更新依赖

```bash
# 更新所有依赖
yarn upgrade

# 更新特定依赖
yarn upgrade [package]

# 更新到特定版本
yarn upgrade [package]@[version]
```

### 运行脚本

```bash
yarn [script]
```

例如，运行 `package.json` 中定义的 `build` 脚本：

```bash
yarn build
```

## 依赖管理

### 依赖类型

- **dependencies**：生产环境依赖
- **devDependencies**：开发环境依赖
- **peerDependencies**：对等依赖
- **optionalDependencies**：可选依赖
- **resolutions**：强制解析特定版本

### 版本控制

Yarn 使用语义化版本控制（SemVer）：

- **精确版本**：`"package": "1.2.3"`
- **兼容版本**：`"package": "^1.2.3"` (1.x.x)
- **近似版本**：`"package": "~1.2.3"` (1.2.x)
- **范围版本**：`"package": ">=1.2.3 <2.0.0"`

### 锁文件

`yarn.lock` 文件确保所有开发者和环境使用相同的依赖版本。不要手动修改此文件，让 Yarn 自动管理它。

### 工作区

Yarn 支持 Workspaces，用于管理多包项目：

```json
// package.json
{
  "private": true,
  "workspaces": [
    "packages/*"
  ]
}
```

## 构建流程

### 配置构建脚本

在 `package.json` 中定义构建脚本：

```json
{
  "scripts": {
    "dev": "vite",
    "build": "bash build-production.sh",
    "build:vendor": "vite build --config vite.vendor.config.js",
    "build:app": "vite build --config vite.app.config.js"
  }
}
```

### 使用 Yarn 构建项目

```bash
# 开发模式
yarn dev

# 生产构建
yarn build

# 构建特定部分
yarn build:vendor
yarn build:app
```

### 自定义构建流程

本项目使用 `build-production.sh` 脚本进行构建，你可以使用 Yarn 运行它：

```bash
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
yarn build:vendor

# 清理临时文件
rm -rf node_modules/.vite
sleep 2

echo "Step 2: Building app..."
yarn build:app

# 验证构建结果
if [ ! -f "dist/index.html" ]; then
    echo "Error: index.html not found in dist directory!"
    exit 1
fi

# 清理临时缓存
rm -rf node_modules/.vite

echo "Build completed successfully!"
```

## 优化策略

### 依赖优化

1. **分析依赖**：

```bash
yarn add --dev webpack-bundle-analyzer
# 或者
yarn add --dev rollup-plugin-visualizer
```

2. **优化依赖大小**：

```bash
# 查找重复依赖
yarn dedupe

# 清理缓存
yarn cache clean
```

3. **使用 PnP 模式**：

在 `.yarnrc.yml` 中启用 Plug'n'Play 模式：

```yaml
nodeLinker: pnp
```

### 构建优化

1. **并行构建**：

```json
{
  "scripts": {
    "build:parallel": "yarn build:vendor & yarn build:app"
  }
}
```

2. **缓存优化**：

```json
{
  "scripts": {
    "build": "VITE_CACHE=true yarn build:vendor && VITE_CACHE=true yarn build:app"
  }
}
```

3. **代码分割**：

在 `vite.config.js` 中配置：

```js
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': [
            'vue',
            'vue-router',
            'pinia'
          ],
          'ui': [
            'element-plus',
            '@element-plus/icons-vue'
          ]
        }
      }
    }
  }
})
```

## 常见问题

### 依赖冲突

如果遇到依赖冲突，可以使用 `resolutions` 字段强制解析特定版本：

```json
{
  "resolutions": {
    "dompurify": "^3.2.4"
  }
}
```

### 内存不足

构建大型项目时可能遇到内存不足问题：

```bash
# 增加 Node.js 内存限制
export NODE_OPTIONS="--max-old-space-size=8192"
yarn build
```

### 缓存问题

如果遇到缓存问题：

```bash
# 清理 Yarn 缓存
yarn cache clean

# 清理构建缓存
rm -rf node_modules/.vite
rm -rf node_modules/.cache
```

### 网络问题

如果遇到网络问题：

```bash
# 设置镜像源
yarn config set registry https://registry.npmmirror.com

# 离线模式
yarn --offline
```

### 锁文件冲突

如果多人协作导致锁文件冲突：

```bash
# 使用远程锁文件
git checkout origin/main yarn.lock
yarn
```

---

## 项目特定配置

### 前端构建流程

本项目使用 Vite 作为构建工具，构建流程分为两个主要步骤：

1. **构建第三方库**：将常用的第三方库打包成单独的文件，提高缓存效率
2. **构建应用代码**：打包应用特定的代码

### 使用 Yarn 进行构建

```bash
# 进入前端目录
cd frontend

# 安装依赖
yarn

# 开发模式
yarn dev

# 生产构建
yarn build
```

### 自定义构建配置

本项目使用多个 Vite 配置文件：

- `vite.config.js`：基本配置
- `vite.vendor.config.js`：第三方库配置
- `vite.app.config.js`：应用代码配置

你可以根据需要修改这些配置文件，然后使用 Yarn 运行构建。

---

文档最后更新时间：2024年9月15日
