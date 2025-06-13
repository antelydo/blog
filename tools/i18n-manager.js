/**
 * 国际化语言包管理工具
 * 用于检测和同步不同语言之间的翻译键
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk'); // 需要安装：npm install chalk

// 配置
const config = {
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 主要语言（作为参考基准）
  primaryLanguage: 'zh',
  // 需要同步的语言
  languages: ['zh', 'en'],
  // 是否自动添加缺失的键（使用主语言的键作为值）
  autoAddMissingKeys: true,
  // 是否生成报告
  generateReport: true,
  // 报告输出路径
  reportPath: path.resolve(__dirname, '../i18n-report.json'),
  // 是否在控制台输出详细信息
  verbose: true
};

/**
 * 递归读取对象的所有键
 * @param {Object} obj - 要读取的对象
 * @param {String} prefix - 键前缀
 * @returns {Array} - 所有键的数组
 */
function getAllKeys(obj, prefix = '') {
  let keys = [];
  
  for (const key in obj) {
    const currentKey = prefix ? `${prefix}.${key}` : key;
    
    if (typeof obj[key] === 'object' && obj[key] !== null) {
      // 递归处理嵌套对象
      keys = [...keys, ...getAllKeys(obj[key], currentKey)];
    } else {
      keys.push(currentKey);
    }
  }
  
  return keys;
}

/**
 * 根据键路径获取对象中的值
 * @param {Object} obj - 要获取值的对象
 * @param {String} path - 键路径，如 'a.b.c'
 * @returns {*} - 获取到的值
 */
function getValueByPath(obj, path) {
  const keys = path.split('.');
  let current = obj;
  
  for (const key of keys) {
    if (current[key] === undefined) {
      return undefined;
    }
    current = current[key];
  }
  
  return current;
}

/**
 * 根据键路径设置对象中的值
 * @param {Object} obj - 要设置值的对象
 * @param {String} path - 键路径，如 'a.b.c'
 * @param {*} value - 要设置的值
 */
function setValueByPath(obj, path, value) {
  const keys = path.split('.');
  let current = obj;
  
  for (let i = 0; i < keys.length - 1; i++) {
    const key = keys[i];
    if (current[key] === undefined) {
      current[key] = {};
    }
    current = current[key];
  }
  
  current[keys[keys.length - 1]] = value;
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
          // 注意：这种方式不安全，仅用于演示
          // 实际项目中应使用更安全的方法解析JS文件
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
 * 保存语言包
 * @param {String} language - 语言代码
 * @param {Object} locale - 语言包对象
 */
function saveLocale(language, locale) {
  const localePath = path.join(config.localesDir, language);
  
  for (const moduleName in locale) {
    const filePath = path.join(localePath, `${moduleName}.js`);
    const content = `export default ${JSON.stringify(locale[moduleName], null, 2)}`;
    fs.writeFileSync(filePath, content, 'utf8');
  }
}

/**
 * 检测并同步语言包
 */
function syncLocales() {
  console.log(chalk.blue('开始检测并同步语言包...'));
  
  // 加载所有语言包
  const locales = {};
  for (const language of config.languages) {
    locales[language] = loadLocale(language);
  }
  
  // 以主要语言为基准，检测其他语言中缺失的键
  const primaryLocale = locales[config.primaryLanguage];
  const report = {
    missingKeys: {},
    addedKeys: 0,
    totalKeys: 0
  };
  
  // 遍历主要语言的所有模块
  for (const moduleName in primaryLocale) {
    const primaryModule = primaryLocale[moduleName];
    const primaryKeys = getAllKeys(primaryModule);
    report.totalKeys += primaryKeys.length;
    
    // 检查其他语言
    for (const language of config.languages) {
      if (language === config.primaryLanguage) continue;
      
      if (!report.missingKeys[language]) {
        report.missingKeys[language] = [];
      }
      
      // 确保目标语言有相应的模块
      if (!locales[language][moduleName]) {
        locales[language][moduleName] = {};
      }
      
      // 检查每个键
      for (const key of primaryKeys) {
        const primaryValue = getValueByPath(primaryModule, key);
        const targetValue = getValueByPath(locales[language][moduleName], key);
        
        if (targetValue === undefined) {
          report.missingKeys[language].push({
            module: moduleName,
            key,
            primaryValue
          });
          
          // 自动添加缺失的键
          if (config.autoAddMissingKeys) {
            setValueByPath(locales[language][moduleName], key, primaryValue);
            report.addedKeys++;
            
            if (config.verbose) {
              console.log(chalk.yellow(`在 ${language} 中添加缺失的键: ${moduleName}.${key} = "${primaryValue}"`));
            }
          }
        }
      }
    }
  }
  
  // 保存更新后的语言包
  if (config.autoAddMissingKeys) {
    for (const language of config.languages) {
      if (language === config.primaryLanguage) continue;
      saveLocale(language, locales[language]);
    }
  }
  
  // 生成报告
  if (config.generateReport) {
    fs.writeFileSync(config.reportPath, JSON.stringify(report, null, 2), 'utf8');
  }
  
  // 输出统计信息
  console.log(chalk.green(`检测完成! 总键数: ${report.totalKeys}`));
  for (const language in report.missingKeys) {
    console.log(chalk.yellow(`${language} 中缺失的键数: ${report.missingKeys[language].length}`));
  }
  
  if (config.autoAddMissingKeys) {
    console.log(chalk.green(`自动添加了 ${report.addedKeys} 个缺失的键`));
  }
}

// 执行同步
syncLocales();

module.exports = {
  syncLocales
};
