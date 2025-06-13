/**
 * 优化的API请求工具
 * 使用缓存和防抖技术减少不必要的请求
 */

import api from '@/api'

// 请求缓存
const requestCache = new Map()

// 防抖映射
const debounceMap = new Map()

// 正在进行的请求
const pendingRequests = new Map()

/**
 * 生成请求的唯一键
 * @param {string} url 请求URL
 * @param {string} method 请求方法
 * @param {Object} data 请求数据
 * @returns {string} 请求的唯一键
 */
const generateRequestKey = (url, method, data) => {
  return `${method}:${url}:${JSON.stringify(data || {})}`
}

/**
 * 缓存请求结果
 * @param {string} key 请求的唯一键
 * @param {Object} response 响应数据
 * @param {number} ttl 缓存时间（毫秒）
 */
const cacheResponse = (key, response, ttl = 60000) => {
  const expireTime = Date.now() + ttl
  requestCache.set(key, {
    data: response,
    expireTime
  })
}

/**
 * 获取缓存的响应
 * @param {string} key 请求的唯一键
 * @returns {Object|null} 缓存的响应数据或null
 */
const getCachedResponse = (key) => {
  if (!requestCache.has(key)) return null
  
  const cache = requestCache.get(key)
  
  // 检查缓存是否过期
  if (cache.expireTime < Date.now()) {
    requestCache.delete(key)
    return null
  }
  
  return cache.data
}

/**
 * 清除特定请求的缓存
 * @param {string} url 请求URL
 * @param {string} method 请求方法
 * @param {Object} data 请求数据
 */
export const clearRequestCache = (url, method, data) => {
  const key = generateRequestKey(url, method, data)
  requestCache.delete(key)
}

/**
 * 清除所有缓存
 */
export const clearAllCache = () => {
  requestCache.clear()
}

/**
 * 防抖请求
 * @param {Function} requestFn 请求函数
 * @param {number} delay 延迟时间（毫秒）
 * @returns {Function} 防抖后的请求函数
 */
export const debounceRequest = (requestFn, delay = 300) => {
  return (...args) => {
    const key = JSON.stringify(args)
    
    if (debounceMap.has(key)) {
      clearTimeout(debounceMap.get(key))
    }
    
    return new Promise((resolve, reject) => {
      const timer = setTimeout(async () => {
        try {
          const result = await requestFn(...args)
          resolve(result)
          debounceMap.delete(key)
        } catch (error) {
          reject(error)
          debounceMap.delete(key)
        }
      }, delay)
      
      debounceMap.set(key, timer)
    })
  }
}

/**
 * 带缓存的GET请求
 * @param {string} url 请求URL
 * @param {Object} config 请求配置
 * @param {number} ttl 缓存时间（毫秒）
 * @returns {Promise} 请求Promise
 */
export const cachedGet = async (url, config = {}, ttl = 60000) => {
  const key = generateRequestKey(url, 'get', config.params)
  
  // 检查缓存
  const cachedResponse = getCachedResponse(key)
  if (cachedResponse) {
    return cachedResponse
  }
  
  // 检查是否有相同的请求正在进行
  if (pendingRequests.has(key)) {
    return pendingRequests.get(key)
  }
  
  // 创建请求Promise
  const requestPromise = api.get(url, config)
    .then(response => {
      // 缓存响应
      cacheResponse(key, response, ttl)
      // 移除进行中的请求
      pendingRequests.delete(key)
      return response
    })
    .catch(error => {
      // 移除进行中的请求
      pendingRequests.delete(key)
      throw error
    })
  
  // 添加到进行中的请求
  pendingRequests.set(key, requestPromise)
  
  return requestPromise
}

/**
 * 带防抖的POST请求
 * @param {string} url 请求URL
 * @param {Object} data 请求数据
 * @param {Object} config 请求配置
 * @returns {Promise} 请求Promise
 */
export const debouncedPost = debounceRequest(
  (url, data, config = {}) => api.post(url, data, config),
  300
)

/**
 * 带防抖的PUT请求
 * @param {string} url 请求URL
 * @param {Object} data 请求数据
 * @param {Object} config 请求配置
 * @returns {Promise} 请求Promise
 */
export const debouncedPut = debounceRequest(
  (url, data, config = {}) => api.put(url, data, config),
  300
)

export default {
  cachedGet,
  debouncedPost,
  debouncedPut,
  clearRequestCache,
  clearAllCache,
  debounceRequest
}
