/**
 * i18n测试工具
 * 用于测试翻译键是否正确加载
 */

const fs = require('fs');
const path = require('path');
// 不使用chalk，使用简单的控制台颜色
const colors = {
  blue: (text) => `\x1b[34m${text}\x1b[0m`,
  green: (text) => `\x1b[32m${text}\x1b[0m`,
  red: (text) => `\x1b[31m${text}\x1b[0m`,
  yellow: (text) => `\x1b[33m${text}\x1b[0m`
};

// 配置
const config = {
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 需要测试的语言
  languages: ['zh', 'en'],
  // 需要测试的翻译键
  testKeys: [
    'common.iconCategories.ui',
    'common.iconCategories.weather',
    'common.iconCategories.communication',
    'common.iconCategories.business',
    'common.iconUi',
    'common.iconWeather',
    'common.iconCommunication',
    'common.iconBusiness'
  ]
};

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
 * 根据键路径获取对象中的值
 * @param {Object} obj - 要获取值的对象
 * @param {String} path - 键路径，如 'a.b.c'
 * @returns {*} - 获取到的值，如果不存在则返回undefined
 */
function getValueByPath(obj, path) {
  const keys = path.split('.');
  let current = obj;

  for (const key of keys) {
    if (current === undefined || current === null) {
      return undefined;
    }
    current = current[key];
  }

  return current;
}

/**
 * 测试翻译键
 */
function testTranslationKeys() {
  console.log(colors.blue('开始测试翻译键...'));

  // 加载所有语言包
  const locales = {};
  for (const language of config.languages) {
    locales[language] = loadLocale(language);
  }

  // 测试每个翻译键
  for (const key of config.testKeys) {
    console.log(colors.yellow(`测试翻译键: ${key}`));

    for (const language of config.languages) {
      const parts = key.split('.');
      const moduleName = parts[0];
      const restPath = parts.slice(1).join('.');

      if (!locales[language][moduleName]) {
        console.error(colors.red(`  [${language}] 模块 ${moduleName} 不存在`));
        continue;
      }

      const value = getValueByPath(locales[language][moduleName], restPath);

      if (value === undefined) {
        console.error(colors.red(`  [${language}] 翻译键 ${key} 不存在`));
      } else {
        console.log(colors.green(`  [${language}] ${key} = "${value}"`));
      }
    }

    console.log('');
  }

  console.log(colors.blue('翻译键测试完成'));
}

// 执行测试
testTranslationKeys();
