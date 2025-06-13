/**
 * i18n 翻译自动验证工具
 * 
 * 此工具用于：
 * 1. 检测英文和中文翻译文件中缺失的键
 * 2. 自动同步所有翻译键，确保中英文文件包含相同的键
 * 3. 提供开发环境中的翻译键警告
 * 4. 验证特定翻译键是否存在
 */

// 直接导入i18n实例以获取已加载的翻译信息
import i18n from '@/plugins/i18n';
import { deepMerge, flattenObject, unflattenObject } from '@/utils/objectUtils';

/**
 * 获取所有翻译键的完整列表
 * @returns {Object} 包含所有扁平化键的对象
 */
export function getAllTranslationKeys() {
  const keys = {};
  const messages = i18n.global.messages.value;
  
  // 收集所有英文键
  const enKeys = flattenObject(messages.en || {});
  Object.keys(enKeys).forEach(key => {
    keys[key] = true;
  });
  
  // 收集所有中文键
  const zhKeys = flattenObject(messages.zh || {});
  Object.keys(zhKeys).forEach(key => {
    keys[key] = true;
  });
  
  return keys;
}

/**
 * 检查缺失的翻译键
 * @returns {Object} 包含每种语言缺失键的对象
 */
export function checkMissingTranslations() {
  const allKeys = getAllTranslationKeys();
  const result = {
    en: [],
    zh: []
  };
  
  const messages = i18n.global.messages.value;
  const enFlat = flattenObject(messages.en || {});
  const zhFlat = flattenObject(messages.zh || {});
  
  // 检查英文中缺失的键
  Object.keys(allKeys).forEach(key => {
    if (!enFlat[key]) {
      result.en.push(key);
    }
  });
  
  // 检查中文中缺失的键
  Object.keys(allKeys).forEach(key => {
    if (!zhFlat[key]) {
      result.zh.push(key);
    }
  });
  
  return result;
}

/**
 * 比较中英文翻译文件，找出需要添加或更新的翻译键
 * @returns {Object} 包含需要添加到各语言的键值对
 */
export function compareTranslations() {
  const messages = i18n.global.messages.value;
  const enFlat = flattenObject(messages.en || {});
  const zhFlat = flattenObject(messages.zh || {});
  
  const result = {
    en: {}, // 需要添加到英文的键值对
    zh: {}, // 需要添加到中文的键值对
    differentValues: [], // 值不一致的键（可能需要更新翻译）
    stats: {
      totalKeys: Object.keys({...enFlat, ...zhFlat}).length,
      enKeys: Object.keys(enFlat).length,
      zhKeys: Object.keys(zhFlat).length,
      missingInEn: 0,
      missingInZh: 0
    }
  };
  
  // 查找中文有但英文没有的键
  Object.keys(zhFlat).forEach(key => {
    if (!enFlat[key]) {
      result.en[key] = `[Need translation: ${zhFlat[key]}]`;
      result.stats.missingInEn++;
    }
  });
  
  // 查找英文有但中文没有的键
  Object.keys(enFlat).forEach(key => {
    if (!zhFlat[key]) {
      result.zh[key] = `[需要翻译: ${enFlat[key]}]`;
      result.stats.missingInZh++;
    }
  });
  
  // 检查同名键但值明显不同的情况（可能需要更新翻译）
  // 这里我们简单地检查字符串长度差异较大的情况
  Object.keys(enFlat).forEach(key => {
    if (zhFlat[key] && typeof enFlat[key] === 'string' && typeof zhFlat[key] === 'string') {
      const enLength = enFlat[key].length;
      const zhLength = zhFlat[key].length;
      
      // 如果长度差异过大，可能需要检查翻译是否准确
      // 这是一个简单的启发式检查，实际使用时可能需要更复杂的逻辑
      if (enLength > 0 && zhLength > 0 && (enLength / zhLength > 3 || zhLength / enLength > 3)) {
        result.differentValues.push({
          key,
          en: enFlat[key],
          zh: zhFlat[key]
        });
      }
    }
  });
  
  return result;
}

/**
 * 验证一个或多个翻译键是否存在于指定语言中
 * @param {string|Array} keys 要验证的键或键数组
 * @param {string} lang 要验证的语言代码 ('en' 或 'zh')
 * @returns {Object} 验证结果，包含存在和不存在的键
 */
export function validateTranslationKeys(keys, lang = 'en') {
  if (!keys) return { valid: false, message: 'No keys provided' };
  
  const keysToCheck = Array.isArray(keys) ? keys : [keys];
  const messages = i18n.global.messages.value;
  const langMessages = messages[lang] || {};
  const flatMessages = flattenObject(langMessages);
  
  const result = {
    valid: true,
    exists: [],
    missing: [],
    stats: {
      total: keysToCheck.length,
      found: 0,
      missing: 0
    }
  };
  
  keysToCheck.forEach(key => {
    if (flatMessages[key] !== undefined) {
      result.exists.push(key);
      result.stats.found++;
    } else {
      result.missing.push(key);
      result.stats.missing++;
      result.valid = false;
    }
  });
  
  return result;
}

/**
 * 日志输出缺失的翻译键
 */
export function logMissingTranslations() {
  const missing = checkMissingTranslations();
  
  if (missing.en.length > 0) {
    console.warn(`[i18n] Missing ${missing.en.length} English translations:`, missing.en);
  }
  
  if (missing.zh.length > 0) {
    console.warn(`[i18n] Missing ${missing.zh.length} Chinese translations:`, missing.zh);
  }
  
  return missing.en.length === 0 && missing.zh.length === 0;
}

/**
 * 检查当前页面上是否存在未翻译的文本
 * 此函数会查找DOM中包含i18n占位符的元素
 * @returns {Array} 包含未翻译文本的元素数组
 */
export function checkCurrentPageTranslations() {
  const currentLang = i18n.global.locale.value;
  const untranslatedElements = [];
  
  // 查找所有包含文本的元素
  const textElements = document.querySelectorAll('button, a, h1, h2, h3, h4, h5, p, span, label, div, th, td');
  
  textElements.forEach(el => {
    const text = el.innerText || el.textContent;
    
    // 检查是否包含未翻译的标记
    if (text && (
        text.includes('[Missing:') || 
        text.includes('[翻译缺失:') || 
        text.includes('[Need translation:') || 
        text.includes('[需翻译:')
      )) {
      untranslatedElements.push({
        element: el,
        text: text.trim()
      });
    }
  });
  
  // 在控制台中显示未翻译内容
  if (untranslatedElements.length > 0) {
    console.warn(`[i18n] Found ${untranslatedElements.length} untranslated elements on current page:`, untranslatedElements);
  }
  
  return untranslatedElements;
}

// 在开发模式下自动检查缺失的翻译键
if (process.env.NODE_ENV === 'development') {
  // 延迟执行以确保i18n已完全加载
  setTimeout(() => {
    logMissingTranslations();
    
    // 为语言切换添加检查
    window.addEventListener('language-changed', () => {
      // 给DOM更新留出时间
      setTimeout(() => {
        checkCurrentPageTranslations();
      }, 500);
    });
    
    // 导出到全局，方便开发调试
    window.i18nValidator = {
      checkMissingTranslations,
      checkCurrentPageTranslations,
      compareTranslations,
      validateTranslationKeys
    };
  }, 1500);
}

export default {
  checkMissingTranslations,
  logMissingTranslations,
  getAllTranslationKeys,
  checkCurrentPageTranslations,
  compareTranslations,
  validateTranslationKeys
}; 