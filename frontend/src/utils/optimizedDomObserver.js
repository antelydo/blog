/**
 * 优化的DOM变化观察器
 * 使用防抖技术减少不必要的DOM操作和重绘
 */

// 防抖函数
const debounce = (fn, delay) => {
  let timer = null
  return function(...args) {
    if (timer) {
      clearTimeout(timer)
    }
    timer = setTimeout(() => {
      fn.apply(this, args)
      timer = null
    }, delay)
  }
}

// 存储观察器实例
let observer = null

// 存储回调函数
const callbacks = new Set()

/**
 * 添加DOM变化回调
 * @param {Function} callback 回调函数
 */
export const addDomChangeCallback = (callback) => {
  if (typeof callback !== 'function') {
    console.error('回调必须是函数')
    return
  }
  
  callbacks.add(callback)
  
  // 如果观察器还没有初始化，则初始化
  if (!observer) {
    initObserver()
  }
}

/**
 * 移除DOM变化回调
 * @param {Function} callback 要移除的回调函数
 */
export const removeDomChangeCallback = (callback) => {
  callbacks.delete(callback)
  
  // 如果没有回调了，则断开观察器
  if (callbacks.size === 0 && observer) {
    observer.disconnect()
    observer = null
  }
}

/**
 * 初始化DOM观察器
 */
const initObserver = () => {
  // 如果已经存在观察器，则先断开连接
  if (observer) {
    observer.disconnect()
  }
  
  // 使用防抖处理DOM变化
  const handleDomChanges = debounce((mutations) => {
    // 检查是否有需要关注的变化
    let shouldNotify = false
    
    for (const mutation of mutations) {
      // 如果是添加或删除节点
      if (mutation.type === 'childList' && 
          (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0)) {
        shouldNotify = true
        break
      }
      
      // 如果是属性变化
      if (mutation.type === 'attributes') {
        shouldNotify = true
        break
      }
    }
    
    // 如果有需要关注的变化，通知所有回调
    if (shouldNotify) {
      callbacks.forEach(callback => {
        try {
          callback(mutations)
        } catch (error) {
          console.error('DOM变化回调执行错误:', error)
        }
      })
    }
  }, 100) // 100ms的防抖延迟
  
  // 创建MutationObserver实例
  observer = new MutationObserver(handleDomChanges)
  
  // 开始观察文档
  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class', 'style', 'data-theme']
  })
  
  console.log('DOM观察器已初始化')
}

/**
 * 手动触发所有回调
 */
export const triggerCallbacks = () => {
  callbacks.forEach(callback => {
    try {
      callback([])
    } catch (error) {
      console.error('手动触发DOM变化回调错误:', error)
    }
  })
}

/**
 * 清理观察器
 */
export const cleanup = () => {
  if (observer) {
    observer.disconnect()
    observer = null
  }
  callbacks.clear()
  console.log('DOM观察器已清理')
}

export default {
  addDomChangeCallback,
  removeDomChangeCallback,
  triggerCallbacks,
  cleanup
}
