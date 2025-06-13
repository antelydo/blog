/**
 * i18n重新加载工具
 * 用于在开发过程中快速重新加载翻译文件
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

// 配置
const config = {
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 需要处理的语言
  languages: ['zh', 'en'],
  // 是否在控制台输出详细信息
  verbose: true
};

/**
 * 重新加载语言包
 */
function reloadI18n() {
  console.log(chalk.blue('开始重新加载语言包...'));
  
  // 遍历所有语言
  for (const language of config.languages) {
    const localePath = path.join(config.localesDir, language);
    
    // 确保语言目录存在
    if (!fs.existsSync(localePath)) {
      console.error(chalk.red(`语言目录不存在: ${localePath}`));
      continue;
    }
    
    // 获取所有语言文件
    const files = fs.readdirSync(localePath);
    
    // 遍历所有语言文件
    for (const file of files) {
      if (file.endsWith('.js')) {
        const filePath = path.join(localePath, file);
        
        try {
          // 读取文件内容
          const content = fs.readFileSync(filePath, 'utf8');
          
          // 添加时间戳注释以强制文件更新
          const timestamp = new Date().toISOString();
          const updatedContent = `// Last updated: ${timestamp}\n${content}`;
          
          // 写回文件
          fs.writeFileSync(filePath, updatedContent, 'utf8');
          
          if (config.verbose) {
            console.log(chalk.green(`已更新文件: ${filePath}`));
          }
        } catch (error) {
          console.error(chalk.red(`更新文件失败: ${filePath}`), error);
        }
      }
    }
  }
  
  console.log(chalk.green('语言包重新加载完成!'));
  console.log(chalk.yellow('请重新启动开发服务器或刷新浏览器以使更改生效。'));
}

// 执行重新加载
reloadI18n();
