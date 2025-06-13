/**
 * API缓存工具
 * 提供API请求缓存功能，减少重复请求
 */
import { setCache, getCache, removeCache } from './cache';

// 默认缓存前缀
const CACHE_PREFIX = 'api_cache_';

// 默认缓存时间（5分钟）
const DEFAULT_CACHE_TIME = 5 * 60 * 1000;

/**
 * 生成缓存键
 * @param {string} url 请求URL
 * @param {Object} params 请求参数
 * @returns {string} 缓存键
 */
function generateCacheKey(url, params = {}) {
  // 将参数排序，确保相同参数不同顺序生成相同的键
  const sortedParams = Object.keys(params)
    .sort()
    .reduce((result, key) => {
      result[key] = params[key];
      return result;
    }, {});
  
  // 将URL和参数组合成字符串
  const cacheKeyStr = `${url}_${JSON.stringify(sortedParams)}`;
  
  // 使用前缀和MD5哈希作为缓存键
  return `${CACHE_PREFIX}${cacheKeyStr}`;
}

/**
 * 缓存API请求结果
 * @param {string} url 请求URL
 * @param {Object} params 请求参数
 * @param {any} data 请求结果
 * @param {number} cacheTime 缓存时间（毫秒）
 */
export function cacheApiResult(url, params, data, cacheTime = DEFAULT_CACHE_TIME) {
  const cacheKey = generateCacheKey(url, params);
  
  setCache(cacheKey, data, { expiry: cacheTime });
}

/**
 * 获取缓存的API请求结果
 * @param {string} url 请求URL
 * @param {Object} params 请求参数
 * @returns {any|null} 缓存的请求结果，如果没有缓存则返回null
 */
export function getCachedApiResult(url, params) {
  const cacheKey = generateCacheKey(url, params);
  
  return getCache(cacheKey);
}

/**
 * 清除API请求缓存
 * @param {string} url 请求URL
 * @param {Object} params 请求参数
 */
export function clearApiCache(url, params) {
  const cacheKey = generateCacheKey(url, params);
  
  removeCache(cacheKey);
}

/**
 * 清除所有API请求缓存
 */
export function clearAllApiCache() {
  // 遍历localStorage，删除所有以CACHE_PREFIX开头的项
  Object.keys(localStorage).forEach(key => {
    if (key.startsWith(CACHE_PREFIX)) {
      localStorage.removeItem(key);
    }
  });
}

/**
 * 创建支持缓存的API请求函数
 * @param {Function} apiFunc 原始API请求函数
 * @param {Object} options 选项
 * @param {number} options.cacheTime 缓存时间（毫秒）
 * @param {boolean} options.forceRefresh 是否强制刷新
 * @returns {Function} 支持缓存的API请求函数
 */
export function createCachedApiRequest(apiFunc, options = {}) {
  const { cacheTime = DEFAULT_CACHE_TIME, forceRefresh = false } = options;
  
  return async function(params) {
    const url = apiFunc.name || 'anonymous_api';
    
    // 如果不强制刷新，尝试从缓存获取
    if (!forceRefresh) {
      const cachedResult = getCachedApiResult(url, params);
      if (cachedResult) {
        return Promise.resolve(cachedResult);
      }
    }
    
    // 缓存中没有或强制刷新，发起实际请求
    try {
      const result = await apiFunc(params);
      
      // 缓存结果
      cacheApiResult(url, params, result, cacheTime);
      
      return result;
    } catch (error) {
      // 请求失败，不缓存错误结果
      throw error;
    }
  };
}

export default {
  cacheApiResult,
  getCachedApiResult,
  clearApiCache,
  clearAllApiCache,
  createCachedApiRequest
}
