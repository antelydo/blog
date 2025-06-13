/**
 * 全局语言处理工具
 * 提供集中的语言变更处理功能
 */
import { useConfigStore } from '@/stores/config'
import { nextTick } from 'vue'

/**
 * 处理语言变更
 * @param {string} lang 新语言代码 ('zh'|'en')
 */
export function handleLanguageChange(lang) {
  // 确保在DOM更新后触发事件
  nextTick(() => {
    // 更新页面标题
    const configStore = useConfigStore()
    configStore.updatePageTitle()
    
    // 触发语言变更事件
    const event = new CustomEvent('language-changed', { detail: { lang } })
    window.dispatchEvent(event)
    
    // 刷新可能需要更新的组件
    refreshComponents()
  })
}

/**
 * 刷新可能需要国际化更新的关键组件
 */
function refreshComponents() {
  // 查找需要刷新的UI组件
  const componentsToRefresh = document.querySelectorAll(
    '.el-menu, .el-dialog, .el-table, .el-form, .el-card, .el-dropdown, .el-button, .el-input'
  )
  
  // 应用刷新类，触发CSS重新渲染
  componentsToRefresh.forEach(comp => {
    if (comp) {
      comp.classList.add('i18n-refresh')
      setTimeout(() => {
        comp.classList.remove('i18n-refresh')
      }, 50)
    }
  })
}

/**
 * 初始化语言处理器
 */
export function initLanguageHandler() {
  // 为语言切换事件添加全局监听器
  window.addEventListener('language-changed', (event) => {
    // 更新页面标题
    updatePageTitleAfterLanguageChange();
    
    // 可以在这里添加其他与语言切换相关的全局处理逻辑
    console.log(`Language changed to: ${event.detail?.lang || 'unknown'}`);
  });
}

// 语言切换后更新页面标题
export function updatePageTitleAfterLanguageChange() {
  // 延迟执行，确保i18n已经加载完成
  setTimeout(() => {
    // 通过router获取当前路由
    const currentRoute = window.router?.currentRoute?.value;
    if (currentRoute?.meta?.title) {
      // 使用配置store更新页面标题
      try {
        // 记录调试信息
        console.log('[DEBUG] 语言切换后更新标题:', {
          路由: currentRoute.path,
          标题Key: currentRoute.meta.title,
          当前语言: window.i18n?.global?.locale?.value || '未知'
        });
        
        // 尝试直接从window获取getPageTitle
        if (typeof window.getPageTitle === 'function') {
          const title = window.getPageTitle(currentRoute.meta.title);
          document.title = title;
          console.log('[DEBUG] 使用window.getPageTitle更新标题:', title);
          return;
        }
        
        // 尝试使用configStore
        const configStore = useConfigStore();
        if (configStore && typeof configStore.getPageTitle === 'function') {
          const title = configStore.getPageTitle(currentRoute.meta.title);
          document.title = title;
          console.log('[DEBUG] 使用configStore.getPageTitle更新标题:', title);
          return;
        }
        
        // 尝试使用i18n直接翻译
        if (window.i18n) {
          const key = `route.${currentRoute.meta.title.toLowerCase()}`;
          const title = window.i18n.global.t(key);
          if (title && title !== key) {
            document.title = `${title} - ${window.i18n.global.t('site.title')}`;
            console.log('[DEBUG] 使用i18n直接翻译更新标题:', title);
            return;
          }
        }
        
        // 最后的备选方案
        console.warn('[DEBUG] 无法找到合适的方法更新标题，使用默认标题');
        document.title = currentRoute.meta.title;
      } catch (e) {
        console.error('更新页面标题时出错:', e);
        // 出错时尝试使用简单方式更新标题
        document.title = currentRoute.meta.title;
      }
    }
  }, 100);
}

export default {
  handleLanguageChange,
  refreshComponents,
  initLanguageHandler
} 