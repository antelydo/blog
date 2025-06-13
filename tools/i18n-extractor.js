/**
 * 国际化语言包提取工具
 * 用于从代码中提取翻译键并与语言包进行比对
 */

const fs = require('fs');
const path = require('path');
const glob = require('glob'); // 需要安装：npm install glob
const chalk = require('chalk');

// 配置
const config = {
  // 源代码目录
  srcDir: path.resolve(__dirname, '../frontend/src'),
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 主要语言（作为参考基准）
  primaryLanguage: 'zh',
  // 需要处理的语言
  languages: ['zh', 'en'],
  // 要扫描的文件类型
  filePatterns: ['**/*.vue', '**/*.js', '**/*.jsx', '**/*.ts', '**/*.tsx'],
  // 翻译函数名称
  translationFunctions: ['t', '$t', 'i18n.t', 'i18n.global.t'],
  // 是否自动添加缺失的键
  autoAddMissingKeys: true,
  // 是否生成报告
  generateReport: true,
  // 报告输出路径
  reportPath: path.resolve(__dirname, '../i18n-extraction-report.json'),
  // 是否在控制台输出详细信息
  verbose: true
};

/**
 * 从代码中提取翻译键
 * @returns {Set} - 提取到的翻译键集合
 */
function extractTranslationKeys() {
  const keys = new Set();
  
  // 获取所有匹配的文件
  const files = [];
  for (const pattern of config.filePatterns) {
    const matches = glob.sync(pattern, { cwd: config.srcDir, absolute: true });
    files.push(...matches);
  }
  
  // 正则表达式匹配翻译函数调用
  const functionPatterns = config.translationFunctions.map(fn => 
    new RegExp(`${fn}\\(\\s*['"\`]([^'"\`]+)['"\`]`, 'g')
  );
  
  // 扫描每个文件
  for (const file of files) {
    const content = fs.readFileSync(file, 'utf8');
    
    for (const pattern of functionPatterns) {
      let match;
      while ((match = pattern.exec(content)) !== null) {
        keys.add(match[1]);
      }
    }
  }
  
  return keys;
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
 * 检查键是否存在于语言包中
 * @param {Object} locale - 语言包对象
 * @param {String} key - 翻译键
 * @returns {Boolean} - 是否存在
 */
function keyExistsInLocale(locale, key) {
  const parts = key.split('.');
  let current = locale;
  
  for (const part of parts) {
    if (!current || !current[part]) {
      return false;
    }
    current = current[part];
  }
  
  return true;
}

/**
 * 将键添加到语言包中
 * @param {Object} locale - 语言包对象
 * @param {String} key - 翻译键
 * @param {String} value - 翻译值
 */
function addKeyToLocale(locale, key, value) {
  const parts = key.split('.');
  const moduleName = parts[0];
  
  if (!locale[moduleName]) {
    locale[moduleName] = {};
  }
  
  let current = locale[moduleName];
  for (let i = 1; i < parts.length - 1; i++) {
    const part = parts[i];
    if (!current[part]) {
      current[part] = {};
    }
    current = current[part];
  }
  
  current[parts[parts.length - 1]] = value;
}

/**
 * 保存语言包
 * @param {String} language - 语言代码
 * @param {Object} locale - 语言包对象
 */
function saveLocale(language, locale) {
  const localePath = path.join(config.localesDir, language);
  
  // 确保目录存在
  if (!fs.existsSync(localePath)) {
    fs.mkdirSync(localePath, { recursive: true });
  }
  
  for (const moduleName in locale) {
    const filePath = path.join(localePath, `${moduleName}.js`);
    const content = `export default ${JSON.stringify(locale[moduleName], null, 2)}`;
    fs.writeFileSync(filePath, content, 'utf8');
  }
}

/**
 * 提取并比对翻译键
 */
function extractAndCompareKeys() {
  console.log(chalk.blue('开始从代码中提取翻译键...'));
  
  // 提取翻译键
  const extractedKeys = extractTranslationKeys();
  console.log(chalk.green(`从代码中提取到 ${extractedKeys.size} 个翻译键`));
  
  // 加载所有语言包
  const locales = {};
  for (const language of config.languages) {
    locales[language] = loadLocale(language);
  }
  
  // 比对翻译键
  const report = {
    extractedKeys: Array.from(extractedKeys),
    missingKeys: {},
    addedKeys: 0
  };
  
  for (const language of config.languages) {
    report.missingKeys[language] = [];
    
    for (const key of extractedKeys) {
      if (!keyExistsInLocale(locales[language], key)) {
        report.missingKeys[language].push(key);
        
        // 自动添加缺失的键
        if (config.autoAddMissingKeys) {
          // 如果是主要语言，使用键作为值
          if (language === config.primaryLanguage) {
            addKeyToLocale(locales[language], key, key.split('.').pop());
          } 
          // 否则，使用主要语言的值
          else if (keyExistsInLocale(locales[config.primaryLanguage], key)) {
            const primaryValue = getValueFromLocale(locales[config.primaryLanguage], key);
            addKeyToLocale(locales[language], key, primaryValue);
          }
          // 如果主要语言也没有，使用键作为值
          else {
            addKeyToLocale(locales[language], key, key.split('.').pop());
          }
          
          report.addedKeys++;
          
          if (config.verbose) {
            console.log(chalk.yellow(`在 ${language} 中添加缺失的键: ${key}`));
          }
        }
      }
    }
  }
  
  // 保存更新后的语言包
  if (config.autoAddMissingKeys) {
    for (const language of config.languages) {
      saveLocale(language, locales[language]);
    }
  }
  
  // 生成报告
  if (config.generateReport) {
    fs.writeFileSync(config.reportPath, JSON.stringify(report, null, 2), 'utf8');
  }
  
  // 输出统计信息
  for (const language in report.missingKeys) {
    console.log(chalk.yellow(`${language} 中缺失的键数: ${report.missingKeys[language].length}`));
  }
  
  if (config.autoAddMissingKeys) {
    console.log(chalk.green(`自动添加了 ${report.addedKeys} 个缺失的键`));
  }
}

/**
 * 从语言包中获取值
 * @param {Object} locale - 语言包对象
 * @param {String} key - 翻译键
 * @returns {String} - 翻译值
 */
function getValueFromLocale(locale, key) {
  const parts = key.split('.');
  let current = locale;
  
  for (const part of parts) {
    if (!current || !current[part]) {
      return key.split('.').pop();
    }
    current = current[part];
  }
  
  return current;
}

// 执行提取和比对
extractAndCompareKeys();

module.exports = {
  extractAndCompareKeys
};
