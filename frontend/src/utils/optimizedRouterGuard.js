/**
 * 优化的路由守卫
 * 减少不必要的重新渲染和API请求
 */

import { triggerCallbacks } from './optimizedDomObserver'

// 存储上一个路由路径
let previousPath = null

// 存储路由变化回调
const routeChangeCallbacks = new Set()

/**
 * 添加路由变化回调
 * @param {Function} callback 回调函数
 */
export const addRouteChangeCallback = (callback) => {
  if (typeof callback !== 'function') {
    console.error('回调必须是函数')
    return
  }
  
  routeChangeCallbacks.add(callback)
}

/**
 * 移除路由变化回调
 * @param {Function} callback 要移除的回调函数
 */
export const removeRouteChangeCallback = (callback) => {
  routeChangeCallbacks.delete(callback)
}

/**
 * 处理路由变化
 * @param {Object} to 目标路由
 * @param {Object} from 来源路由
 */
export const handleRouteChange = (to, from) => {
  // 如果路径没有变化，不执行任何操作
  if (to.path === previousPath) return
  
  // 更新上一个路径
  previousPath = to.path
  
  // 使用requestAnimationFrame确保在下一帧处理
  requestAnimationFrame(() => {
    // 触发所有回调
    routeChangeCallbacks.forEach(callback => {
      try {
        callback(to, from)
      } catch (error) {
        console.error('路由变化回调执行错误:', error)
      }
    })
    
    // 触发DOM变化回调
    triggerCallbacks()
  })
}

/**
 * 设置页面标题
 * @param {Object} route 当前路由
 */
export const setPageTitle = (route) => {
  if (route.meta && route.meta.title) {
    // 使用全局页面标题设置函数
    if (window.getPageTitle) {
      document.title = window.getPageTitle(route.meta.title)
    } else {
      document.title = route.meta.title
    }
  }
}

/**
 * 初始化优化的路由守卫
 * @param {Object} router Vue Router实例
 */
export const initOptimizedRouterGuard = (router) => {
  if (!router) {
    console.error('路由实例不能为空')
    return
  }
  
  // 添加全局前置守卫
  router.beforeEach((to, from, next) => {
    // 检查是否是管理员路由
    const isAdminRoute = to.path.startsWith('/admin') && to.path !== '/admin/login'
    
    // 统一处理登录页面直接放行
    if (to.path === '/admin/login' || to.path === '/login') {
      return next()
    }
    
    // 获取token并检查其有效性
    let token = null
    let hasValidToken = false
    
    if (isAdminRoute) {
      // 管理员路由使用admin_token
      token = localStorage.getItem('admin_token')
      hasValidToken = !!token
    } else {
      // 普通路由使用token
      token = localStorage.getItem('token')
      hasValidToken = !!token
    }
    
    // 权限检查
    if (to.matched.some(record => record.meta.requiresAuth)) {
      if (!hasValidToken) {
        // 根据路由类型跳转到对应的登录页
        return next(isAdminRoute ? '/admin/login' : '/login')
      }
    }
    
    // 处理错误
    if (to.query.error) {
      const errorCode = parseInt(to.query.error)
      if (errorCode === 403) {
        return next({ name: 'forbidden' })
      } else if (errorCode === 500) {
        return next({ name: 'server-error' })
      }
    }
    
    // 没有特殊限制，允许访问
    next()
  })
  
  // 添加全局后置钩子
  router.afterEach((to, from) => {
    // 设置页面标题
    setPageTitle(to)
    
    // 处理路由变化
    handleRouteChange(to, from)
  })
  
  console.log('优化的路由守卫已初始化')
}

export default {
  addRouteChangeCallback,
  removeRouteChangeCallback,
  handleRouteChange,
  setPageTitle,
  initOptimizedRouterGuard
}
