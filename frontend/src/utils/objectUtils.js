/**
 * 对象操作工具函数集
 * 提供深度合并、扁平化和还原扁平化对象等功能
 */

/**
 * 深度合并多个对象
 * @param {Object} target 目标对象，会被修改
 * @param {...Object} sources 源对象
 * @returns {Object} 合并后的对象
 */
export function deepMerge(target, ...sources) {
  if (!sources.length) return target;
  
  const source = sources.shift();
  
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!target[key]) {
          target[key] = {};
        }
        deepMerge(target[key], source[key]);
      } else {
        target[key] = source[key];
      }
    });
  }
  
  return deepMerge(target, ...sources);
}

/**
 * 检查值是否为对象
 * @param {*} item 要检查的值
 * @returns {boolean} 是否为对象
 */
export function isObject(item) {
  return (item && typeof item === 'object' && !Array.isArray(item));
}

/**
 * 将嵌套对象扁平化为单层对象，键使用点符号表示嵌套
 * @param {Object} obj 要扁平化的对象
 * @param {string} [parentKey=''] 父级键名（用于递归）
 * @returns {Object} 扁平化后的对象
 * 
 * 示例：
 * { a: { b: 1, c: 2 } } => { 'a.b': 1, 'a.c': 2 }
 */
export function flattenObject(obj, parentKey = '') {
  const result = {};
  
  Object.keys(obj).forEach(key => {
    const newKey = parentKey ? `${parentKey}.${key}` : key;
    
    if (isObject(obj[key]) && Object.keys(obj[key]).length > 0) {
      Object.assign(result, flattenObject(obj[key], newKey));
    } else {
      result[newKey] = obj[key];
    }
  });
  
  return result;
}

/**
 * 将扁平化的对象还原为嵌套对象
 * @param {Object} flatObj 扁平化的对象，键使用点符号表示嵌套
 * @returns {Object} 还原后的嵌套对象
 * 
 * 示例：
 * { 'a.b': 1, 'a.c': 2 } => { a: { b: 1, c: 2 } }
 */
export function unflattenObject(flatObj) {
  const result = {};
  
  Object.keys(flatObj).forEach(key => {
    const value = flatObj[key];
    const keys = key.split('.');
    let current = result;
    
    for (let i = 0; i < keys.length; i++) {
      const k = keys[i];
      
      if (i === keys.length - 1) {
        // 最后一个键，设置值
        current[k] = value;
      } else {
        // 中间键，确保存在
        current[k] = current[k] || {};
        current = current[k];
      }
    }
  });
  
  return result;
}

/**
 * 检查对象是否为空
 * @param {Object} obj 要检查的对象
 * @returns {boolean} 是否为空对象
 */
export function isEmptyObject(obj) {
  return isObject(obj) && Object.keys(obj).length === 0;
}

/**
 * 获取对象的深度
 * @param {Object} obj 要检查的对象
 * @returns {number} 对象的嵌套深度
 */
export function getObjectDepth(obj) {
  if (!isObject(obj)) return 0;
  
  let maxDepth = 0;
  
  Object.keys(obj).forEach(key => {
    if (isObject(obj[key])) {
      const depth = getObjectDepth(obj[key]) + 1;
      maxDepth = Math.max(maxDepth, depth);
    }
  });
  
  return maxDepth + 1;
}

export default {
  deepMerge,
  isObject,
  flattenObject,
  unflattenObject,
  isEmptyObject,
  getObjectDepth
}; 