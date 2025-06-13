/**
 * 缓存工具
 * 提供本地缓存功能，支持过期时间和加密存储
 */
import { encryptData, decryptData } from './security';

// 默认缓存过期时间（1小时）
const DEFAULT_EXPIRY = 3600000;

// 从环境变量获取缓存过期时间（秒）
const ENV_CACHE_EXPIRY = import.meta.env.VITE_APP_CACHE_EXPIRY 
  ? parseInt(import.meta.env.VITE_APP_CACHE_EXPIRY) * 1000 
  : DEFAULT_EXPIRY;

/**
 * 设置缓存
 * @param {string} key 缓存键
 * @param {any} value 缓存值
 * @param {Object} options 选项
 * @param {number} options.expiry 过期时间（毫秒）
 * @param {boolean} options.encrypt 是否加密
 */
export function setCache(key, value, options = {}) {
  if (!key) return;
  
  const { expiry = ENV_CACHE_EXPIRY, encrypt = false } = options;
  
  const cacheData = {
    value,
    timestamp: Date.now(),
    expiry
  };
  
  let dataToStore = JSON.stringify(cacheData);
  
  // 如果需要加密
  if (encrypt) {
    dataToStore = encryptData(dataToStore);
  }
  
  try {
    localStorage.setItem(key, dataToStore);
  } catch (error) {
    console.error('缓存设置失败:', error);
    // 如果存储失败（例如存储空间已满），尝试清理过期缓存
    clearExpiredCache();
    // 再次尝试存储
    try {
      localStorage.setItem(key, dataToStore);
    } catch (retryError) {
      console.error('缓存设置重试失败:', retryError);
    }
  }
}

/**
 * 获取缓存
 * @param {string} key 缓存键
 * @param {Object} options 选项
 * @param {boolean} options.encrypt 是否加密
 * @param {any} options.defaultValue 默认值
 * @returns {any} 缓存值
 */
export function getCache(key, options = {}) {
  if (!key) return options.defaultValue || null;
  
  const { encrypt = false, defaultValue = null } = options;
  
  let dataStr;
  try {
    dataStr = localStorage.getItem(key);
  } catch (error) {
    console.error('获取缓存失败:', error);
    return defaultValue;
  }
  
  if (!dataStr) return defaultValue;
  
  // 如果是加密数据，先解密
  if (encrypt) {
    try {
      dataStr = decryptData(dataStr);
    } catch (error) {
      console.error('缓存解密失败:', error);
      return defaultValue;
    }
  }
  
  try {
    const cacheData = JSON.parse(dataStr);
    
    // 检查缓存是否过期
    if (cacheData.timestamp && cacheData.expiry) {
      const now = Date.now();
      if (now - cacheData.timestamp > cacheData.expiry) {
        // 缓存已过期，删除并返回默认值
        localStorage.removeItem(key);
        return defaultValue;
      }
    }
    
    return cacheData.value;
  } catch (error) {
    console.error('解析缓存数据失败:', error);
    return defaultValue;
  }
}

/**
 * 移除缓存
 * @param {string} key 缓存键
 */
export function removeCache(key) {
  if (!key) return;
  
  try {
    localStorage.removeItem(key);
  } catch (error) {
    console.error('移除缓存失败:', error);
  }
}

/**
 * 清除所有缓存
 */
export function clearCache() {
  try {
    localStorage.clear();
  } catch (error) {
    console.error('清除缓存失败:', error);
  }
}

/**
 * 清除过期缓存
 */
export function clearExpiredCache() {
  try {
    const now = Date.now();
    
    // 遍历所有缓存项
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      const dataStr = localStorage.getItem(key);
      
      if (!dataStr) continue;
      
      try {
        const cacheData = JSON.parse(dataStr);
        
        // 检查是否是缓存数据格式
        if (cacheData.timestamp && cacheData.expiry) {
          // 检查是否过期
          if (now - cacheData.timestamp > cacheData.expiry) {
            localStorage.removeItem(key);
          }
        }
      } catch (parseError) {
        // 不是JSON格式，跳过
        continue;
      }
    }
  } catch (error) {
    console.error('清除过期缓存失败:', error);
  }
}

/**
 * 获取缓存状态
 * @returns {Object} 缓存状态
 */
export function getCacheStatus() {
  try {
    const total = localStorage.length;
    let size = 0;
    let expired = 0;
    const now = Date.now();
    
    // 遍历所有缓存项
    for (let i = 0; i < total; i++) {
      const key = localStorage.key(i);
      const value = localStorage.getItem(key);
      
      // 计算大小
      size += (key.length + value.length) * 2; // 每个字符占2字节
      
      // 检查是否过期
      try {
        const cacheData = JSON.parse(value);
        if (cacheData.timestamp && cacheData.expiry) {
          if (now - cacheData.timestamp > cacheData.expiry) {
            expired++;
          }
        }
      } catch (parseError) {
        // 不是JSON格式，跳过
        continue;
      }
    }
    
    return {
      total,
      size: formatSize(size),
      expired,
      usagePercentage: Math.round((size / (5 * 1024 * 1024)) * 100) // 假设5MB限制
    };
  } catch (error) {
    console.error('获取缓存状态失败:', error);
    return {
      total: 0,
      size: '0 B',
      expired: 0,
      usagePercentage: 0
    };
  }
}

/**
 * 格式化大小
 * @param {number} bytes 字节数
 * @returns {string} 格式化后的大小
 */
function formatSize(bytes) {
  if (bytes < 1024) {
    return bytes + ' B';
  } else if (bytes < 1024 * 1024) {
    return (bytes / 1024).toFixed(2) + ' KB';
  } else {
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  }
}

export default {
  setCache,
  getCache,
  removeCache,
  clearCache,
  clearExpiredCache,
  getCacheStatus
}
