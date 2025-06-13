/**
 * 国际化语言包版本控制工具
 * 用于跟踪语言包的变更历史
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const crypto = require('crypto');

// 配置
const config = {
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 版本历史文件
  versionHistoryFile: path.resolve(__dirname, '../i18n-version-history.json'),
  // 需要处理的语言
  languages: ['zh', 'en'],
  // 是否在控制台输出详细信息
  verbose: true
};

/**
 * 计算对象的哈希值
 * @param {Object} obj - 要计算哈希的对象
 * @returns {String} - 哈希值
 */
function calculateHash(obj) {
  const str = JSON.stringify(obj);
  return crypto.createHash('md5').update(str).digest('hex');
}

/**
 * 加载语言包
 * @param {String} language - 语言代码
 * @returns {Object} - 语言包对象
 */
function loadLocale(language) {
  const localePath = path.join(config.localesDir, language);
  const files = fs.readdirSync(localePath);
  const locale = {};
  
  for (const file of files) {
    if (file.endsWith('.js')) {
      const filePath = path.join(localePath, file);
      const content = fs.readFileSync(filePath, 'utf8');
      
      // 提取 export default {...} 中的内容
      const match = content.match(/export\s+default\s+({[\s\S]*})/);
      if (match && match[1]) {
        try {
          // 将内容转换为对象
          const moduleContent = new Function(`return ${match[1]}`)();
          const moduleName = file.replace('.js', '');
          locale[moduleName] = moduleContent;
        } catch (error) {
          console.error(`解析文件 ${filePath} 失败:`, error);
        }
      }
    }
  }
  
  return locale;
}

/**
 * 加载版本历史
 * @returns {Object} - 版本历史对象
 */
function loadVersionHistory() {
  if (fs.existsSync(config.versionHistoryFile)) {
    const content = fs.readFileSync(config.versionHistoryFile, 'utf8');
    return JSON.parse(content);
  }
  
  return {
    versions: [],
    current: null
  };
}

/**
 * 保存版本历史
 * @param {Object} history - 版本历史对象
 */
function saveVersionHistory(history) {
  fs.writeFileSync(config.versionHistoryFile, JSON.stringify(history, null, 2), 'utf8');
}

/**
 * 创建语言包快照
 */
function createLocaleSnapshot() {
  console.log(chalk.blue('开始创建语言包快照...'));
  
  // 加载所有语言包
  const locales = {};
  for (const language of config.languages) {
    locales[language] = loadLocale(language);
  }
  
  // 计算每个语言包的哈希值
  const hashes = {};
  for (const language in locales) {
    hashes[language] = calculateHash(locales[language]);
  }
  
  // 加载版本历史
  const history = loadVersionHistory();
  
  // 创建新版本
  const newVersion = {
    timestamp: new Date().toISOString(),
    hashes,
    locales
  };
  
  // 检查是否有变化
  if (history.current) {
    let hasChanges = false;
    for (const language in hashes) {
      if (history.current.hashes[language] !== hashes[language]) {
        hasChanges = true;
        break;
      }
    }
    
    if (!hasChanges) {
      console.log(chalk.yellow('语言包没有变化，不创建新快照'));
      return;
    }
  }
  
  // 添加新版本
  history.versions.push(newVersion);
  history.current = newVersion;
  
  // 保存版本历史
  saveVersionHistory(history);
  
  console.log(chalk.green(`语言包快照创建成功! 版本: ${newVersion.timestamp}`));
}

/**
 * 恢复到指定版本
 * @param {String} timestamp - 版本时间戳
 */
function restoreLocaleVersion(timestamp) {
  console.log(chalk.blue(`开始恢复到版本: ${timestamp}...`));
  
  // 加载版本历史
  const history = loadVersionHistory();
  
  // 查找指定版本
  const version = history.versions.find(v => v.timestamp === timestamp);
  if (!version) {
    console.error(chalk.red(`找不到版本: ${timestamp}`));
    return;
  }
  
  // 恢复语言包
  for (const language in version.locales) {
    const localePath = path.join(config.localesDir, language);
    
    // 确保目录存在
    if (!fs.existsSync(localePath)) {
      fs.mkdirSync(localePath, { recursive: true });
    }
    
    // 保存每个模块
    for (const moduleName in version.locales[language]) {
      const filePath = path.join(localePath, `${moduleName}.js`);
      const content = `export default ${JSON.stringify(version.locales[language][moduleName], null, 2)}`;
      fs.writeFileSync(filePath, content, 'utf8');
    }
  }
  
  // 更新当前版本
  history.current = version;
  
  // 保存版本历史
  saveVersionHistory(history);
  
  console.log(chalk.green(`语言包恢复成功! 当前版本: ${timestamp}`));
}

/**
 * 列出所有版本
 */
function listVersions() {
  console.log(chalk.blue('语言包版本历史:'));
  
  // 加载版本历史
  const history = loadVersionHistory();
  
  if (history.versions.length === 0) {
    console.log(chalk.yellow('没有版本历史'));
    return;
  }
  
  // 显示所有版本
  for (let i = 0; i < history.versions.length; i++) {
    const version = history.versions[i];
    const isCurrent = history.current && version.timestamp === history.current.timestamp;
    
    console.log(chalk.green(`[${i + 1}] ${version.timestamp}${isCurrent ? ' (当前)' : ''}`));
  }
}

// 根据命令行参数执行不同操作
const args = process.argv.slice(2);
const command = args[0];

if (command === 'create') {
  createLocaleSnapshot();
} else if (command === 'restore') {
  const timestamp = args[1];
  if (!timestamp) {
    console.error(chalk.red('请指定要恢复的版本时间戳'));
  } else {
    restoreLocaleVersion(timestamp);
  }
} else if (command === 'list') {
  listVersions();
} else {
  console.log(chalk.yellow('用法:'));
  console.log('  node i18n-version-control.js create    # 创建新快照');
  console.log('  node i18n-version-control.js list      # 列出所有版本');
  console.log('  node i18n-version-control.js restore <timestamp>  # 恢复到指定版本');
}

module.exports = {
  createLocaleSnapshot,
  restoreLocaleVersion,
  listVersions
};
