/**
 * 性能优化工具
 * 提供性能监控和优化相关的工具函数
 */

// 性能计时器
const timers = {}

/**
 * 开始计时
 * @param {string} name 计时器名称
 */
export function startTimer(name) {
  if (!name) return
  timers[name] = {
    start: performance.now(),
    end: null,
    duration: null
  }
}

/**
 * 结束计时
 * @param {string} name 计时器名称
 * @returns {number|null} 耗时（毫秒）
 */
export function endTimer(name) {
  if (!name || !timers[name]) return null
  
  timers[name].end = performance.now()
  timers[name].duration = timers[name].end - timers[name].start
  
  return timers[name].duration
}

/**
 * 获取计时结果
 * @param {string} name 计时器名称
 * @returns {number|null} 耗时（毫秒）
 */
export function getTimerDuration(name) {
  if (!name || !timers[name] || timers[name].duration === null) return null
  
  return timers[name].duration
}

/**
 * 获取所有计时结果
 * @returns {Object} 所有计时器的结果
 */
export function getAllTimers() {
  return { ...timers }
}

/**
 * 清除所有计时器
 */
export function clearTimers() {
  Object.keys(timers).forEach(key => {
    delete timers[key]
  })
}

/**
 * 获取页面性能指标
 * @returns {Object} 页面性能指标
 */
export function getPerformanceMetrics() {
  if (!window.performance || !window.performance.timing) {
    return null
  }
  
  const timing = window.performance.timing
  
  return {
    // DNS解析时间
    dns: timing.domainLookupEnd - timing.domainLookupStart,
    // TCP连接时间
    tcp: timing.connectEnd - timing.connectStart,
    // 请求响应时间
    request: timing.responseEnd - timing.requestStart,
    // DOM解析时间
    dom: timing.domComplete - timing.domLoading,
    // 页面加载时间
    load: timing.loadEventEnd - timing.navigationStart,
    // 首次内容绘制时间
    fcp: getFCP(),
    // 首次有意义绘制时间
    fmp: getFMP(),
    // 首次交互时间
    tti: getTTI(),
    // 总体评分
    score: calculatePerformanceScore()
  }
}

/**
 * 获取首次内容绘制时间（FCP）
 * @returns {number|null} 首次内容绘制时间（毫秒）
 */
function getFCP() {
  if (!window.performance || !window.performance.getEntriesByType) {
    return null
  }
  
  const paintEntries = window.performance.getEntriesByType('paint')
  const fcpEntry = paintEntries.find(entry => entry.name === 'first-contentful-paint')
  
  return fcpEntry ? fcpEntry.startTime : null
}

/**
 * 获取首次有意义绘制时间（FMP）
 * 注意：这是一个估计值，因为FMP没有标准定义
 * @returns {number|null} 首次有意义绘制时间（毫秒）
 */
function getFMP() {
  if (!window.performance || !window.performance.timing) {
    return null
  }
  
  // 使用domInteractive作为FMP的估计值
  return window.performance.timing.domInteractive - window.performance.timing.navigationStart
}

/**
 * 获取首次交互时间（TTI）
 * 注意：这是一个估计值，因为TTI的准确计算需要更复杂的逻辑
 * @returns {number|null} 首次交互时间（毫秒）
 */
function getTTI() {
  if (!window.performance || !window.performance.timing) {
    return null
  }
  
  // 使用domInteractive作为TTI的估计值
  return window.performance.timing.domInteractive - window.performance.timing.navigationStart
}

/**
 * 计算性能评分（0-100）
 * @returns {number} 性能评分
 */
function calculatePerformanceScore() {
  if (!window.performance || !window.performance.timing) {
    return 0
  }
  
  const timing = window.performance.timing
  const loadTime = timing.loadEventEnd - timing.navigationStart
  
  // 基于加载时间计算评分
  // 小于1秒：90-100分
  // 1-2秒：80-90分
  // 2-3秒：70-80分
  // 3-4秒：60-70分
  // 大于4秒：60分以下
  if (loadTime < 1000) {
    return 90 + (1000 - loadTime) / 100
  } else if (loadTime < 2000) {
    return 80 + (2000 - loadTime) / 100
  } else if (loadTime < 3000) {
    return 70 + (3000 - loadTime) / 100
  } else if (loadTime < 4000) {
    return 60 + (4000 - loadTime) / 100
  } else {
    return Math.max(0, 60 - (loadTime - 4000) / 1000)
  }
}

/**
 * 预加载资源
 * @param {string|Array<string>} urls 需要预加载的资源URL
 * @param {string} type 资源类型：'image'|'script'|'style'|'font'
 */
export function preloadResources(urls, type = 'image') {
  if (!urls) return
  
  const urlList = Array.isArray(urls) ? urls : [urls]
  
  urlList.forEach(url => {
    if (!url) return
    
    switch (type) {
      case 'image':
        const img = new Image()
        img.src = url
        break
      case 'script':
        const script = document.createElement('link')
        script.rel = 'preload'
        script.as = 'script'
        script.href = url
        document.head.appendChild(script)
        break
      case 'style':
        const style = document.createElement('link')
        style.rel = 'preload'
        style.as = 'style'
        style.href = url
        document.head.appendChild(style)
        break
      case 'font':
        const font = document.createElement('link')
        font.rel = 'preload'
        font.as = 'font'
        font.href = url
        font.crossOrigin = 'anonymous'
        document.head.appendChild(font)
        break
      default:
        break
    }
  })
}

/**
 * 延迟加载图片
 * 使用Intersection Observer API实现图片懒加载
 */
export function setupLazyLoading() {
  if (!('IntersectionObserver' in window)) {
    // 如果浏览器不支持Intersection Observer，则直接加载所有图片
    document.querySelectorAll('img[data-src]').forEach(img => {
      img.src = img.dataset.src
    })
    return
  }
  
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target
        img.src = img.dataset.src
        observer.unobserve(img)
      }
    })
  })
  
  document.querySelectorAll('img[data-src]').forEach(img => {
    observer.observe(img)
  })
}

/**
 * 检测浏览器是否支持WebP格式
 * @returns {Promise<boolean>} 是否支持WebP
 */
export function supportsWebP() {
  return new Promise(resolve => {
    const webP = new Image()
    webP.onload = webP.onerror = function() {
      resolve(webP.height === 2)
    }
    webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA'
  })
}

/**
 * 检测浏览器是否支持AVIF格式
 * @returns {Promise<boolean>} 是否支持AVIF
 */
export function supportsAVIF() {
  return new Promise(resolve => {
    const avif = new Image()
    avif.onload = avif.onerror = function() {
      resolve(avif.height === 2)
    }
    avif.src = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A='
  })
}

/**
 * 根据浏览器支持情况选择最优图片格式
 * @param {Object} options 图片选项
 * @param {string} options.jpg JPG格式图片URL
 * @param {string} options.webp WebP格式图片URL
 * @param {string} options.avif AVIF格式图片URL
 * @returns {Promise<string>} 最优图片URL
 */
export async function getBestImageFormat(options) {
  if (!options) return null
  
  const { jpg, webp, avif } = options
  
  if (avif && await supportsAVIF()) {
    return avif
  }
  
  if (webp && await supportsWebP()) {
    return webp
  }
  
  return jpg
}

/**
 * 检测网络状态
 * @returns {Object} 网络状态信息
 */
export function getNetworkInfo() {
  if (!navigator.connection) {
    return {
      type: 'unknown',
      effectiveType: 'unknown',
      downlink: null,
      rtt: null,
      saveData: false
    }
  }
  
  const connection = navigator.connection
  
  return {
    // 网络类型
    type: connection.type || 'unknown',
    // 有效网络类型
    effectiveType: connection.effectiveType || 'unknown',
    // 下行速度（Mbps）
    downlink: connection.downlink || null,
    // 往返时间（ms）
    rtt: connection.rtt || null,
    // 是否启用了省流量模式
    saveData: connection.saveData || false
  }
}

/**
 * 根据网络状态调整资源加载策略
 * @param {Function} callback 回调函数，接收网络状态参数
 */
export function adaptToNetworkConditions(callback) {
  if (!navigator.connection) {
    // 如果不支持NetworkInformation API，则假设网络良好
    callback({
      type: 'unknown',
      effectiveType: '4g',
      downlink: 10,
      rtt: 50,
      saveData: false
    })
    return
  }
  
  const updateNetworkInfo = () => {
    const networkInfo = getNetworkInfo()
    callback(networkInfo)
  }
  
  // 初始调用
  updateNetworkInfo()
  
  // 监听网络变化
  navigator.connection.addEventListener('change', updateNetworkInfo)
  
  // 返回清理函数
  return () => {
    navigator.connection.removeEventListener('change', updateNetworkInfo)
  }
}

/**
 * 检测设备性能
 * @returns {Object} 设备性能信息
 */
export function getDevicePerformance() {
  return {
    // CPU核心数
    cpuCores: navigator.hardwareConcurrency || null,
    // 设备内存（GB）
    memory: navigator.deviceMemory || null,
    // 是否为低端设备
    isLowEndDevice: isLowEndDevice(),
    // 是否为移动设备
    isMobile: isMobileDevice()
  }
}

/**
 * 检测是否为低端设备
 * @returns {boolean} 是否为低端设备
 */
function isLowEndDevice() {
  // 如果设备内存小于2GB或CPU核心数小于4，则认为是低端设备
  return (
    (navigator.deviceMemory && navigator.deviceMemory < 2) ||
    (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4)
  )
}

/**
 * 检测是否为移动设备
 * @returns {boolean} 是否为移动设备
 */
function isMobileDevice() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
}

/**
 * 根据设备性能调整应用配置
 * @param {Object} options 配置选项
 * @param {Object} options.low 低端设备配置
 * @param {Object} options.medium 中端设备配置
 * @param {Object} options.high 高端设备配置
 * @returns {Object} 适合当前设备的配置
 */
export function adaptToDevicePerformance(options) {
  if (!options) return {}
  
  const { low, medium, high } = options
  
  const devicePerformance = getDevicePerformance()
  
  // 低端设备
  if (devicePerformance.isLowEndDevice || 
      (devicePerformance.memory && devicePerformance.memory < 2) ||
      (devicePerformance.cpuCores && devicePerformance.cpuCores < 4)) {
    return low || {}
  }
  
  // 高端设备
  if ((devicePerformance.memory && devicePerformance.memory >= 4) &&
      (devicePerformance.cpuCores && devicePerformance.cpuCores >= 8)) {
    return high || medium || {}
  }
  
  // 中端设备
  return medium || {}
}

export default {
  startTimer,
  endTimer,
  getTimerDuration,
  getAllTimers,
  clearTimers,
  getPerformanceMetrics,
  preloadResources,
  setupLazyLoading,
  supportsWebP,
  supportsAVIF,
  getBestImageFormat,
  getNetworkInfo,
  adaptToNetworkConditions,
  getDevicePerformance,
  adaptToDevicePerformance
}
