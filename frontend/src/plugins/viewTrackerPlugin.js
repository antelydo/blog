/**
 * 浏览时长跟踪插件
 * 自动跟踪用户在网站上的浏览时长
 */
import viewTracker from '@/utils/viewTracker';

export default {
  install(app, options = {}) {
    // 获取路由实例
    const router = options.router;
    
    if (!router) {
      console.warn('ViewTrackerPlugin: router is required for automatic page tracking');
      return;
    }
    
    // 当前页面信息
    let currentPageType = null;
    let currentPageId = null;
    
    // 路由变化时处理浏览时长跟踪
    router.beforeEach((to, from, next) => {
      // 如果已经在跟踪，先停止当前跟踪
      if (currentPageType) {
        viewTracker.stop();
      }
      
      next();
    });
    
    router.afterEach((to) => {
      // 根据路由确定页面类型和ID
      let pageType = 'site';
      let pageId = null;
      
      // 根据路由路径确定页面类型
      if (to.path.startsWith('/post/')) {
        pageType = 'post';
        pageId = parseInt(to.params.id) || null;
      } else if (to.path.startsWith('/category/')) {
        pageType = 'category';
        pageId = parseInt(to.params.category) || null;
      } else if (to.path.startsWith('/tag/')) {
        pageType = 'tag';
        pageId = parseInt(to.params.tag) || null;
      } else if (to.path === '/') {
        pageType = 'home';
      } else if (to.path.startsWith('/admin/')) {
        pageType = 'admin';
      }
      
      // 更新当前页面信息
      currentPageType = pageType;
      currentPageId = pageId;
      
      // 开始跟踪新页面
      viewTracker.start(pageType, pageId);
    });
    
    // 在应用关闭前发送最终的浏览时长数据
    window.addEventListener('beforeunload', () => {
      viewTracker.stop();
    });
    
    // 在页面可见性变化时处理
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'hidden') {
        // 页面隐藏时，发送当前的浏览时长
        viewTracker.stop();
      } else if (document.visibilityState === 'visible' && currentPageType) {
        // 页面重新可见时，重新开始跟踪
        viewTracker.start(currentPageType, currentPageId);
      }
    });
    
    // 提供全局访问
    app.config.globalProperties.$viewTracker = viewTracker;
    
    // 提供组合式API访问
    app.provide('viewTracker', viewTracker);
  }
};
