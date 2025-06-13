// 预加载网站图标脚本
// 这个脚本会在Vue应用初始化之前运行，直接从localStorage获取图标URL

/**
 * 从localStorage中获取网站图标并设置
 */
export function preloadFavicon() {
  try {
    // 尝试从localStorage获取网站配置
    const configStr = localStorage.getItem('website_config');
    if (configStr) {
      const config = JSON.parse(configStr);
      
      // 优先使用Favicon字段，其次使用site_favicon字段
      const faviconUrl = config.Favicon || config.site_favicon;
      
      if (faviconUrl) {
        // 移除现有的图标链接
        const existingLink = document.querySelector("link[rel*='icon']");
        if (existingLink) {
          existingLink.parentNode.removeChild(existingLink);
        }
        
        // 创建新的图标链接
        const link = document.createElement('link');
        link.type = 'image/x-icon';
        link.rel = 'shortcut icon';
        link.href = faviconUrl;
        document.head.appendChild(link);
        
        return true; // 成功设置图标
      }
    }
    return false; // 没有找到图标URL
  } catch (error) {
    console.error('预加载网站图标失败:', error);
    return false;
  }
}

// 自动执行预加载
preloadFavicon();
