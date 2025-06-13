/**
 * i18n 翻译自动生成与修复工具 (浏览器环境版本)
 * 
 * 此工具用于：
 * 1. 自动生成缺失的翻译键的占位符
 * 2. 同步中英文翻译文件的键结构
 * 3. 临时修复运行时的翻译
 */

import { compareTranslations, getAllTranslationKeys } from './i18nValidator';
import { deepMerge, unflattenObject } from '@/utils/objectUtils';
import i18n from '@/plugins/i18n';

/**
 * 为缺失的翻译键生成占位符文本
 * @param {string} language 目标语言 (en 或 zh)
 * @param {string} key 翻译键
 * @param {string} sourceValue 源语言的值
 * @returns {string} 占位符文本
 */
export function generatePlaceholder(language, key, sourceValue = '') {
  if (language === 'en') {
    return `[Need translation: ${sourceValue}]`;
  } else {
    return `[需要翻译: ${sourceValue}]`;
  }
}

/**
 * 自动修复翻译文件中缺失的键（运行时内存中修复，不修改文件）
 * 仅在开发环境中使用
 * @returns {Object} 包含修复结果的对象
 */
export function autoFixMissingTranslations() {
  if (process.env.NODE_ENV !== 'development') {
    console.warn('[i18n] 自动修复翻译仅在开发环境中可用');
    return { success: false, reason: '只能在开发环境中使用' };
  }
  
  const comparison = compareTranslations();
  const messages = i18n.global.messages.value;
  
  // 深拷贝当前翻译
  const newEn = JSON.parse(JSON.stringify(messages.en || {}));
  const newZh = JSON.parse(JSON.stringify(messages.zh || {}));
  
  // 添加缺失的英文翻译
  const enMissing = unflattenObject(comparison.en);
  deepMerge(newEn, enMissing);
  
  // 添加缺失的中文翻译
  const zhMissing = unflattenObject(comparison.zh);
  deepMerge(newZh, zhMissing);
  
  // 更新i18n实例中的翻译
  i18n.global.setLocaleMessage('en', newEn);
  i18n.global.setLocaleMessage('zh', newZh);
  
  console.log('[i18n] 已临时修复翻译缺失', {
    en: Object.keys(comparison.en).length,
    zh: Object.keys(comparison.zh).length
  });
  
  return { 
    success: true, 
    fixed: {
      en: Object.keys(comparison.en).length,
      zh: Object.keys(comparison.zh).length
    },
    differentValues: comparison.differentValues.length
  };
}

/**
 * 导出缺失的翻译键为JSON字符串
 * 此功能可用于手动复制翻译内容到文件
 * @returns {Object} 包含JSON字符串的对象
 */
export function exportMissingTranslations() {
  const comparison = compareTranslations();
  
  // 英文缺失键的嵌套对象
  const enMissing = unflattenObject(comparison.en);
  
  // 中文缺失键的嵌套对象
  const zhMissing = unflattenObject(comparison.zh);
  
  // 生成JSON字符串
  const enJson = JSON.stringify(enMissing, null, 2);
  const zhJson = JSON.stringify(zhMissing, null, 2);
  
  // 打印到控制台，方便开发者复制
  console.group('[i18n] 缺失的翻译键');
  
  if (Object.keys(comparison.en).length > 0) {
    console.log('英文缺失键:');
    console.log(enJson);
  } else {
    console.log('没有英文缺失键');
  }
  
  if (Object.keys(comparison.zh).length > 0) {
    console.log('中文缺失键:');
    console.log(zhJson);
  } else {
    console.log('没有中文缺失键');
  }
  
  console.groupEnd();
  
  return {
    success: true,
    en: enJson,
    zh: zhJson,
    stats: comparison.stats
  };
}

// 在开发环境中自动修复翻译
if (process.env.NODE_ENV === 'development') {
  // 延迟执行以确保i18n已完全加载
  setTimeout(() => {
    autoFixMissingTranslations();
    
    // 导出到全局，方便开发调试
    window.i18nAutoFixer = {
      autoFixMissingTranslations,
      generatePlaceholder,
      exportMissingTranslations
    };
  }, 2000);
}

export default {
  autoFixMissingTranslations,
  generatePlaceholder,
  exportMissingTranslations
}; 