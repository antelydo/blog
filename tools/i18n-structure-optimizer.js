/**
 * 国际化语言包结构优化工具
 * 用于优化和统一语言包的结构
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
  // 是否按字母顺序排序键
  sortKeys: true,
  // 是否合并相似模块
  mergeSimilarModules: true,
  // 相似度阈值（0-1之间，越大表示越相似）
  similarityThreshold: 0.7,
  // 是否在控制台输出详细信息
  verbose: true
};

/**
 * 递归排序对象的键
 * @param {Object} obj - 要排序的对象
 * @returns {Object} - 排序后的对象
 */
function sortObjectKeys(obj) {
  if (typeof obj !== 'object' || obj === null) {
    return obj;
  }
  
  if (Array.isArray(obj)) {
    return obj.map(sortObjectKeys);
  }
  
  const sortedObj = {};
  const keys = Object.keys(obj).sort();
  
  for (const key of keys) {
    sortedObj[key] = sortObjectKeys(obj[key]);
  }
  
  return sortedObj;
}

/**
 * 计算两个模块的相似度
 * @param {Object} module1 - 第一个模块
 * @param {Object} module2 - 第二个模块
 * @returns {Number} - 相似度（0-1之间）
 */
function calculateModuleSimilarity(module1, module2) {
  const keys1 = new Set(Object.keys(module1));
  const keys2 = new Set(Object.keys(module2));
  
  const intersection = new Set([...keys1].filter(x => keys2.has(x)));
  const union = new Set([...keys1, ...keys2]);
  
  return intersection.size / union.size;
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
 * 优化语言包结构
 */
function optimizeLocaleStructure() {
  console.log(chalk.blue('开始优化语言包结构...'));
  
  // 加载所有语言包
  const locales = {};
  for (const language of config.languages) {
    locales[language] = loadLocale(language);
  }
  
  // 处理每种语言
  for (const language of config.languages) {
    const locale = locales[language];
    
    // 排序键
    if (config.sortKeys) {
      for (const moduleName in locale) {
        locale[moduleName] = sortObjectKeys(locale[moduleName]);
      }
      
      if (config.verbose) {
        console.log(chalk.green(`已对 ${language} 的所有模块键进行排序`));
      }
    }
    
    // 合并相似模块
    if (config.mergeSimilarModules) {
      const moduleNames = Object.keys(locale);
      const mergedModules = new Set();
      
      for (let i = 0; i < moduleNames.length; i++) {
        const moduleName1 = moduleNames[i];
        if (mergedModules.has(moduleName1)) continue;
        
        for (let j = i + 1; j < moduleNames.length; j++) {
          const moduleName2 = moduleNames[j];
          if (mergedModules.has(moduleName2)) continue;
          
          const similarity = calculateModuleSimilarity(locale[moduleName1], locale[moduleName2]);
          
          if (similarity >= config.similarityThreshold) {
            // 合并模块
            for (const key in locale[moduleName2]) {
              if (!locale[moduleName1][key]) {
                locale[moduleName1][key] = locale[moduleName2][key];
              }
            }
            
            mergedModules.add(moduleName2);
            delete locale[moduleName2];
            
            if (config.verbose) {
              console.log(chalk.yellow(`在 ${language} 中合并了模块 ${moduleName2} 到 ${moduleName1} (相似度: ${similarity.toFixed(2)})`));
            }
          }
        }
      }
    }
    
    // 保存更新后的语言包
    saveLocale(language, locale);
  }
  
  console.log(chalk.green('语言包结构优化完成!'));
}

// 执行优化
optimizeLocaleStructure();

module.exports = {
  optimizeLocaleStructure
};
