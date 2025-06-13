/**
 * 优化初始化工具
 * 统一初始化所有优化工具
 */

// 延迟导入模块，避免循环引用
const loadModule = (modulePath) => {
  try {
    return require(modulePath)
  } catch (error) {
    console.error(`加载模块 ${modulePath} 时出错:`, error)
    return {}
  }
}

/**
 * 初始化所有优化工具
 * @param {Object} router Vue Router实例
 */
export const initOptimizations = (router) => {
  try {
    // 延迟加载主题应用器
    const themeApplier = loadModule('./themeApplier')
    if (themeApplier.initThemeApplier) {
      themeApplier.initThemeApplier()
    }

    // 延迟加载路由守卫
    if (router) {
      const routerGuard = loadModule('./optimizedRouterGuard')
      if (routerGuard.initOptimizedRouterGuard) {
        routerGuard.initOptimizedRouterGuard(router)
      }
    }

    // 设置全局错误处理
    setupGlobalErrorHandling()

    console.log('所有优化工具已初始化')
  } catch (error) {
    console.error('初始化优化工具时出错:', error)

    // 如果出错，仍然设置全局错误处理
    setupGlobalErrorHandling()
  }
}

/**
 * 设置全局错误处理
 */
const setupGlobalErrorHandling = () => {
  // 全局未捕获的Promise错误处理
  window.addEventListener('unhandledrejection', (event) => {
    console.error('未处理的Promise拒绝:', event.reason)
  })

  // 全局错误处理
  window.addEventListener('error', (event) => {
    // 过滤掉ResizeObserver的无害警告
    if (event.message && event.message.includes('ResizeObserver loop')) {
      event.preventDefault()
      return
    }

    console.error('全局错误:', event.message)
  })
}

/**
 * 清理所有优化工具
 */
export const cleanupOptimizations = () => {
  try {
    // 延迟加载主题应用器
    const themeApplier = loadModule('./themeApplier')
    if (themeApplier.cleanupThemeApplier) {
      themeApplier.cleanupThemeApplier()
    }

    // 延迟加载DOM观察器
    const domObserver = loadModule('./optimizedDomObserver')
    if (domObserver.cleanup) {
      domObserver.cleanup()
    }

    console.log('所有优化工具已清理')
  } catch (error) {
    console.error('清理优化工具时出错:', error)
  }
}

export default {
  initOptimizations,
  cleanupOptimizations
}
