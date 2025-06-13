# 项目文件结构说明

## 核心配置文件

### composer.json
- 项目依赖管理配置
- PHP版本要求：>= 8.0
- 主要依赖：
  - ThinkPHP 8框架
  - ORM组件
  - 文件系统组件
  - 数据库迁移工具
  - 验证码组件等

### .travis.yml
- CI/CD配置文件
- 自动化测试和部署配置
- 包含ThinkPHP核心和完整版本的打包配置

### vite.config.js
- 前端构建工具配置
- 开发服务器配置
- 代码分割策略
- 构建优化配置
- 代理服务器设置

## 工具脚本

### tools/deploy-production.sh
- 生产环境部署脚本
- 包含前后端部署步骤
- 性能优化配置
- 服务重启和状态检查

### tools/I18N-README.md
- 国际化工具说明
- 语言包管理功能
- Git Hooks配置
- 翻译同步工具使用说明

## 文档文件

### performance-optimization-guide.md
- PHP性能优化指南
- 缓存配置说明
- 数据库优化建议
- 监控工具使用说明

### vue-project-optimization.md
- Vue项目优化文档
- 代码分割策略
- 缓存优化方案
- 构建优化说明

### README.md
- 项目总体说明
- 技术栈说明
- 环境要求
- 安装部署说明
- API文档

## 环境配置

### .env.performance
- 性能优化环境配置
- 缓存设置
- 数据库优化参数
- 日志级别设置

## 前端资源

### frontend/
- Vue 3项目目录
- 使用Vite构建工具
- Element Plus UI组件
- 国际化支持

## 性能优化相关

### performance-optimization-guide.html
- 性能优化指南的HTML版本
- 包含详细的配置说明
- 优化步骤说明
- 常见问题解答

## 目录结构

```
.
├── app/                    # 应用目录
├── config/                 # 配置目录
├── frontend/              # 前端项目目录
├── public/                # 公共资源目录
├── runtime/               # 运行时目录
├── tools/                 # 工具脚本目录
├── vendor/                # Composer依赖目录
├── .env.example           # 环境变量示例
├── .env.performance       # 性能优化环境配置
├── .travis.yml           # CI配置
├── composer.json         # Composer配置
├── README.md             # 项目说明
└── vite.config.js        # 前端构建配置
```

## 关键文件用途

1. **部署相关**
   - `tools/deploy-production.sh`: 生产环境部署脚本
   - `.travis.yml`: 持续集成配置
   - `composer.json`: 项目依赖管理

2. **性能优化**
   - `performance-optimization-guide.md`: 性能优化指南
   - `.env.performance`: 性能优化环境配置
   - `vue-project-optimization.md`: 前端优化指南

3. **国际化**
   - `tools/I18N-README.md`: 国际化工具说明
   - `lang/`: 语言包目录

4. **前端构建**
   - `vite.config.js`: 前端构建配置
   - `frontend/`: 前端项目源码

## 使用建议

1. **开发环境设置**
   - 复制 `.env.example` 到 `.env`
   - 安装后端依赖: `composer install`
   - 安装前端依赖: `cd frontend && npm install`

2. **部署流程**
   - 使用 `composer run-script deploy-prod` 部署
   - 定期执行性能优化命令
   - 监控系统状态

3. **性能优化**
   - 参考性能优化指南进行配置
   - 定期检查性能状态
   - 使用提供的监控工具

4. **国际化管理**
   - 使用提供的I18N工具管理翻译
   - 遵循翻译键命名规范
   - 定期同步语言包