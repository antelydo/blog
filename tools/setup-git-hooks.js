/**
 * 设置 Git Hooks 脚本
 * 用于在 Git 操作前自动执行语言包同步
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const { execSync } = require('child_process');

// Git Hooks 目录
const gitHooksDir = path.resolve(__dirname, '../.git/hooks');
const customHooksDir = path.resolve(__dirname, '../git-hooks');

// 确保 Git Hooks 目录存在
if (!fs.existsSync(gitHooksDir)) {
  console.error(chalk.red('Git Hooks 目录不存在，请确保这是一个 Git 仓库'));
  process.exit(1);
}

// 确保自定义 Hooks 目录存在
if (!fs.existsSync(customHooksDir)) {
  fs.mkdirSync(customHooksDir, { recursive: true });
}

// 创建 pre-commit hook
const preCommitHook = `#!/bin/sh
# 自动同步语言包的 pre-commit hook

echo "执行语言包同步..."
node tools/i18n-cli.js sync

# 如果语言包有变化，添加到暂存区
git diff --name-only --cached | grep -q "src/locales" && git add src/locales

# 继续提交
exit 0
`;

// 写入 pre-commit hook 文件
fs.writeFileSync(path.join(customHooksDir, 'pre-commit'), preCommitHook);
fs.chmodSync(path.join(customHooksDir, 'pre-commit'), '755');

// 创建 post-merge hook
const postMergeHook = `#!/bin/sh
# 自动同步语言包的 post-merge hook

echo "合并后执行语言包同步..."
node tools/i18n-cli.js all

# 如果语言包有变化，创建新的提交
if git diff --name-only | grep -q "src/locales"; then
  git add src/locales
  git commit -m "chore: 自动同步语言包"
fi

exit 0
`;

// 写入 post-merge hook 文件
fs.writeFileSync(path.join(customHooksDir, 'post-merge'), postMergeHook);
fs.chmodSync(path.join(customHooksDir, 'post-merge'), '755');

// 安装 Hooks
console.log(chalk.blue('开始安装 Git Hooks...'));

try {
  // 复制 Hooks 到 .git/hooks 目录
  fs.copyFileSync(
    path.join(customHooksDir, 'pre-commit'),
    path.join(gitHooksDir, 'pre-commit')
  );
  
  fs.copyFileSync(
    path.join(customHooksDir, 'post-merge'),
    path.join(gitHooksDir, 'post-merge')
  );
  
  // 设置执行权限
  fs.chmodSync(path.join(gitHooksDir, 'pre-commit'), '755');
  fs.chmodSync(path.join(gitHooksDir, 'post-merge'), '755');
  
  console.log(chalk.green('Git Hooks 安装成功!'));
} catch (error) {
  console.error(chalk.red('安装 Git Hooks 时出错:'), error);
  process.exit(1);
}

// 提示用户
console.log(chalk.yellow('现在，每次提交代码前会自动同步语言包，合并代码后也会自动同步语言包。'));
console.log(chalk.yellow('如果需要手动执行同步，可以运行:'));
console.log('  npm run i18n:all');
