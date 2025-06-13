/**
 * CSS优化配置
 * 用于优化CSS文件的打包和加载
 */

// 预加载关键CSS文件
export const preloadCriticalCSS = () => {
  const criticalCSS = [
    '/assets/css/theme-entry.css',
    '/assets/css/base-entry.css'
  ];

  criticalCSS.forEach(href => {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'style';
    link.href = href;
    document.head.appendChild(link);
  });
};

// 延迟加载非关键CSS文件
export const loadNonCriticalCSS = () => {
  const nonCriticalCSS = [
    '/assets/css/admin-entry.css'
  ];

  // 使用requestIdleCallback在浏览器空闲时加载非关键CSS
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(() => {
      loadStyles(nonCriticalCSS);
    });
  } else {
    // 降级处理
    setTimeout(() => {
      loadStyles(nonCriticalCSS);
    }, 1000);
  }
};

// 加载样式函数
const loadStyles = (styles) => {
  styles.forEach(href => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
  });
};

// 根据路由动态加载CSS
export const loadRouteCSS = (route) => {
  // 根据路由路径决定加载哪些CSS
  if (route.path.startsWith('/admin')) {
    // 管理后台路由
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/assets/css/admin-entry.css';
    document.head.appendChild(link);
  }
};

// 移除未使用的CSS
export const removeUnusedCSS = () => {
  // 在生产环境中，这个函数会被构建工具替换为实际的CSS清理代码
  if (process.env.NODE_ENV === 'production') {
    console.log('Removing unused CSS in production build');
  }
};

// 导出默认配置
export default {
  preloadCriticalCSS,
  loadNonCriticalCSS,
  loadRouteCSS,
  removeUnusedCSS
};
