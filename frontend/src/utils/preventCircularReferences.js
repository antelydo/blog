/**
 * 防止循环引用工具
 * 用于检测和防止可能导致堆栈溢出的循环引用
 */

// 存储已经处理过的对象引用
const processedObjects = new WeakMap();

/**
 * 检测对象中是否存在循环引用
 * @param {Object} obj 要检测的对象
 * @param {Array} stack 当前处理的对象栈
 * @returns {boolean} 是否存在循环引用
 */
export const detectCircularReferences = (obj, stack = []) => {
  // 如果不是对象或为null，则不可能有循环引用
  if (obj === null || typeof obj !== 'object') {
    return false;
  }

  // 检查当前对象是否已经在处理栈中
  if (stack.includes(obj)) {
    console.warn('检测到循环引用:', obj);
    return true;
  }

  // 将当前对象添加到处理栈
  stack.push(obj);

  // 递归检查所有属性
  for (const key in obj) {
    if (Object.prototype.hasOwnProperty.call(obj, key)) {
      if (detectCircularReferences(obj[key], [...stack])) {
        return true;
      }
    }
  }

  return false;
};

/**
 * 防止对象被循环引用
 * @param {Object} obj 要保护的对象
 * @returns {Object} 处理后的对象
 */
export const preventCircularReferences = (obj) => {
  // 如果不是对象或为null，直接返回
  if (obj === null || typeof obj !== 'object') {
    return obj;
  }

  // 检查对象是否已经处理过
  if (processedObjects.has(obj)) {
    return processedObjects.get(obj);
  }

  // 创建一个新对象或数组
  const result = Array.isArray(obj) ? [] : {};

  // 将新对象存入WeakMap，防止重复处理
  processedObjects.set(obj, result);

  // 处理所有属性
  for (const key in obj) {
    if (Object.prototype.hasOwnProperty.call(obj, key)) {
      result[key] = preventCircularReferences(obj[key]);
    }
  }

  return result;
};

/**
 * 创建一个防循环引用的代理对象 - 简化版本
 * @param {Object} obj 要代理的对象
 * @returns {Object} 安全的对象副本
 */
export const createSafeProxy = (obj) => {
  // 如果不是对象或为null，直接返回
  if (obj === null || typeof obj !== 'object') {
    return obj;
  }

  // 不再使用Proxy，而是创建一个浅拷贝
  try {
    // 对于响应式对象，先获取其原始值
    const rawObj = obj.value ? { ...obj.value } : { ...obj };

    // 过滤掉可能导致循环的属性
    const safeObj = {};

    // 只保留基本类型和简单对象
    for (const key in rawObj) {
      const value = rawObj[key];

      // 跳过函数和复杂对象
      if (typeof value === 'function' || (value && typeof value === 'object' && value.constructor !== Object && value.constructor !== Array)) {
        continue;
      }

      // 对于简单对象和数组，进行浅拷贝
      if (value && typeof value === 'object') {
        safeObj[key] = Array.isArray(value) ? [...value] : { ...value };
      } else {
        safeObj[key] = value;
      }
    }

    return safeObj;
  } catch (error) {
    console.error('创建安全对象失败:', error);
    // 如果出错，返回一个空对象
    return {};
  }
};

export default {
  detectCircularReferences,
  preventCircularReferences,
  createSafeProxy
};
