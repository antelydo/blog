# 贡献指南

感谢您对 AiChat UI 项目的关注！我们欢迎所有形式的贡献，包括但不限于：

- 🐛 报告和修复 Bug
- 💡 提出新功能建议
- 📝 改进文档
- 🎨 优化用户界面
- ⚡ 性能优化
- 🧪 编写测试用例

## 开始之前

在开始贡献之前，请确保您已经：

1. 阅读了项目的 [README.md](README.md)
2. 了解了项目的技术栈和架构
3. 设置好了本地开发环境

## 开发环境设置

### 系统要求

- **PHP**: 8.0+
- **Node.js**: 16+
- **MySQL**: 5.7+
- **Redis**: 6.0+ (可选)
- **Composer**: 2.0+

### 安装步骤

1. **Fork 并克隆项目**
   ```bash
   git clone https://github.com/your-username/aichat-ui.git
   cd aichat-ui
   ```

2. **安装后端依赖**
   ```bash
   composer install
   cp .env.example .env
   # 编辑 .env 文件配置数据库连接
   ```

3. **安装前端依赖**
   ```bash
   cd frontend
   npm install
   cp .env.example .env.local
   # 编辑 .env.local 文件配置API地址
   ```

4. **初始化数据库**
   ```bash
   # 在项目根目录
   php think migrate:run
   php think seed:run
   ```

5. **启动开发服务器**
   ```bash
   # 后端服务
   php think run

   # 前端服务 (新终端)
   cd frontend
   npm run dev
   ```

## 代码规范

### 后端 (PHP)

我们遵循以下代码规范：

- **PSR-4**: 自动加载规范
- **PSR-12**: 代码风格规范
- **ThinkPHP 8**: 框架最佳实践

#### 命名规范

- **类名**: 使用 PascalCase，如 `UserController`
- **方法名**: 使用 camelCase，如 `getUserInfo()`
- **变量名**: 使用 camelCase，如 `$userName`
- **常量**: 使用 UPPER_CASE，如 `MAX_RETRY_COUNT`

#### 注释规范

```php
/**
 * 获取用户信息
 * @param int $userId 用户ID
 * @return array 用户信息数组
 * @throws \Exception 当用户不存在时抛出异常
 */
public function getUserInfo(int $userId): array
{
    // 实现代码
}
```

### 前端 (Vue.js)

我们遵循以下代码规范：

- **Vue.js 3**: 官方风格指南
- **ESLint**: 代码检查工具
- **Prettier**: 代码格式化工具

#### 命名规范

- **组件名**: 使用 PascalCase，如 `UserProfile.vue`
- **文件名**: 使用 kebab-case，如 `user-profile.vue`
- **变量名**: 使用 camelCase，如 `userName`
- **常量**: 使用 UPPER_CASE，如 `API_BASE_URL`

#### 组件结构

```vue
<template>
  <!-- 模板内容 -->
</template>

<script setup>
// 导入
import { ref, computed } from 'vue'

// 响应式数据
const userName = ref('')

// 计算属性
const displayName = computed(() => {
  return userName.value || '匿名用户'
})

// 方法
const handleSubmit = () => {
  // 处理逻辑
}
</script>

<style scoped>
/* 样式 */
</style>
```

## 提交规范

我们使用 [Conventional Commits](https://www.conventionalcommits.org/) 规范：

### 提交类型

- `feat`: 新功能
- `fix`: 修复bug
- `docs`: 文档更新
- `style`: 代码格式调整（不影响功能）
- `refactor`: 代码重构
- `perf`: 性能优化
- `test`: 测试相关
- `chore`: 构建过程或辅助工具的变动

### 提交格式

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### 示例

```
feat(user): add user profile editing functionality

- Add user profile form component
- Implement profile update API
- Add form validation

Closes #123
```

## Pull Request 流程

1. **创建功能分支**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **开发和测试**
   - 编写代码
   - 添加测试用例
   - 确保所有测试通过
   - 检查代码风格

3. **提交更改**
   ```bash
   git add .
   git commit -m "feat: add your feature"
   ```

4. **推送分支**
   ```bash
   git push origin feature/your-feature-name
   ```

5. **创建 Pull Request**
   - 在 GitHub 上创建 PR
   - 填写详细的描述
   - 关联相关的 Issue

### PR 检查清单

在提交 PR 之前，请确保：

- [ ] 代码遵循项目的代码规范
- [ ] 添加了必要的测试用例
- [ ] 所有测试都通过
- [ ] 更新了相关文档
- [ ] 提交信息符合规范
- [ ] 没有合并冲突

## 问题报告

在报告问题时，请提供以下信息：

### Bug 报告模板

```markdown
## Bug 描述
简要描述遇到的问题

## 复现步骤
1. 进入页面 '...'
2. 点击 '....'
3. 滚动到 '....'
4. 看到错误

## 期望行为
描述您期望发生的行为

## 实际行为
描述实际发生的行为

## 环境信息
- 操作系统: [例如 Windows 10, macOS 12.0]
- 浏览器: [例如 Chrome 95, Firefox 94]
- PHP 版本: [例如 8.1.0]
- Node.js 版本: [例如 16.14.0]

## 附加信息
添加任何其他有助于解决问题的信息，如截图、日志等
```

### 功能请求模板

```markdown
## 功能描述
简要描述您希望添加的功能

## 问题背景
描述这个功能要解决的问题

## 解决方案
描述您希望的解决方案

## 替代方案
描述您考虑过的其他解决方案

## 附加信息
添加任何其他相关信息
```

## 测试

### 后端测试

```bash
# 运行所有测试
php think test

# 运行特定测试
php think test --filter UserTest
```

### 前端测试

```bash
cd frontend

# 运行单元测试
npm run test:unit

# 运行端到端测试
npm run test:e2e
```

## 文档

如果您的贡献涉及新功能或API变更，请确保：

1. 更新相关的代码注释
2. 更新 README.md（如果需要）
3. 更新 API 文档
4. 添加使用示例

## 社区

- **GitHub Issues**: 报告问题和功能请求
- **GitHub Discussions**: 讨论和交流
- **QQ群**: 123456789

## 行为准则

我们致力于为所有人提供友好、安全和欢迎的环境。请遵循以下准则：

1. 使用友好和包容的语言
2. 尊重不同的观点和经验
3. 优雅地接受建设性批评
4. 关注对社区最有利的事情
5. 对其他社区成员表示同理心

## 许可证

通过贡献代码，您同意您的贡献将在 [Apache License 2.0](LICENSE) 下获得许可。

---

再次感谢您的贡献！🎉
