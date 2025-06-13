/**
 * CSS加载优化工具
 * 用于按需加载CSS文件，提高页面加载性能
 */

// 已加载的CSS文件缓存
const loadedCssFiles = new Set();

/**
 * 预加载关键CSS文件
 * 在页面加载时调用，确保关键CSS文件被优先加载
 *
 * 注意：由于我们已经在main.js中直接导入了这些CSS文件，
 * 所以这个函数实际上不需要再预加载这些文件。
 * 我们将其修改为只预加载那些没有直接导入的CSS文件。
 */
export const preloadCriticalCSS = () => {
  // 只预加载那些没有直接导入的CSS文件
  const criticalCSS = [
    // 这里可以添加那些需要预加载但没有直接导入的CSS文件
    // 例如：'/assets/css/some-critical-file.css'
  ];

  if (criticalCSS.length === 0) {
    return; // 如果没有需要预加载的文件，直接返回
  }

  criticalCSS.forEach(href => {
    if (!loadedCssFiles.has(href)) {
      const link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'style';
      link.href = href;
      link.onload = function() {
        // 预加载完成后，将其转换为stylesheet
        this.rel = 'stylesheet';
      };
      document.head.appendChild(link);
      loadedCssFiles.add(href);
    }
  });
};

/**
 * 根据路由加载CSS文件
 * 在路由切换时调用，根据当前路由加载对应的CSS文件
 * @param {Object} route - 当前路由对象
 */
export const loadRouteCSS = (route) => {
  // 获取正确的基础路径
  const basePath = import.meta.env.BASE_URL || '/';
  const stylesPath = `${basePath}src/styles`;

  // 管理后台路由
  if (route.path.startsWith('/admin')) {
    loadCSS(`${stylesPath}/admin-entry.css`);
  }

  // 根据具体路由加载特定CSS
  // 已移除认证页面暗黑主题样式加载
  // if (route.name === 'Login' || route.name === 'Register' || route.name === 'ForgotPassword') {
  //   loadCSS(`${stylesPath}/auth-dark-theme.css`);
  // }
};

/**
 * 加载CSS文件
 * @param {string} href - CSS文件路径
 */
export const loadCSS = (href) => {
  // 如果已经加载过，则不重复加载
  if (loadedCssFiles.has(href)) {
    return;
  }

  // 创建link元素并添加到head
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  document.head.appendChild(link);

  // 标记为已加载
  loadedCssFiles.add(href);

  // 添加加载事件监听
  link.addEventListener('load', () => {
    console.log(`CSS文件加载完成: ${href}`);
  });

  // 添加错误事件监听
  link.addEventListener('error', () => {
    console.error(`CSS文件加载失败: ${href}`);
    // 从已加载集合中移除
    loadedCssFiles.delete(href);
  });
};

/**
 * 延迟加载非关键CSS文件
 * 在页面主要内容加载完成后调用，加载非关键CSS文件
 */
export const loadNonCriticalCSS = () => {
  // 使用requestIdleCallback在浏览器空闲时加载非关键CSS
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(() => {
      // 这里可以添加非关键CSS文件
    });
  } else {
    // 降级处理
    setTimeout(() => {
      // 这里可以添加非关键CSS文件
    }, 1000);
  }
};

/**
 * 初始化CSS加载优化
 * 在应用启动时调用，设置CSS加载策略
 * @param {Object} router - Vue Router实例
 */
export const initCSSOptimization = (router) => {
  // 不再预加载关键CSS，因为它们已经在main.js中直接导入了

  // 添加路由守卫，根据路由加载CSS
  router.beforeEach((to, from, next) => {
    loadRouteCSS(to);
    next();
  });

  // 页面加载完成后，延迟加载非关键CSS
  window.addEventListener('load', () => {
    loadNonCriticalCSS();
  });
};

export default {
  preloadCriticalCSS,
  loadRouteCSS,
  loadCSS,
  loadNonCriticalCSS,
  initCSSOptimization
};
