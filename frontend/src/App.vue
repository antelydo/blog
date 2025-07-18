<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, provide, watch } from 'vue'
import { useConfigStore } from './stores/config'
import { useBlogConfigStore } from './stores/blogConfig'
import { useRoute } from 'vue-router'
// 移除Footer导入，因为它已经在AdminLayout中引入

// 获取当前路由
const route = useRoute()

// 根据路由决定使用哪个配置store
const isAdminRoute = ref(route.path.startsWith('/admin'))
const configStore = ref(isAdminRoute.value ? useConfigStore() : useBlogConfigStore())

// 简化的路由监听，避免可能的循环引用
watch(() => route.path, (newPath) => {
  const newIsAdminRoute = newPath.startsWith('/admin')
  if (newIsAdminRoute !== isAdminRoute.value) {
    isAdminRoute.value = newIsAdminRoute

    // 简化store切换逻辑，避免可能的循环引用
    try {
      // 更新页面标题
      if (route.meta && route.meta.title) {
        document.title = route.meta.title || (newIsAdminRoute ? '管理后台' : '首页')
      } else {
        document.title = newIsAdminRoute ? '管理后台' : '首页'
      }
    } catch (error) {
      console.error('路由变化处理错误:', error)
      document.title = newIsAdminRoute ? '管理后台' : '首页'
    }
  }
})



// 将配置store提供给子组件
provide('configStore', configStore)
provide('isAdminRoute', isAdminRoute)

onMounted(() => {
  // 立即设置网站图标（高优先级）
  const faviconUrl = configStore.value.config.Favicon || configStore.value.config.site_favicon
  if (faviconUrl) {
    // 移除所有现有的图标链接
    const existingIcons = document.querySelectorAll("link[rel*='icon']")
    existingIcons.forEach(icon => {
      // 保留预设的图标
      if (icon.getAttribute('href').includes('/favicon')) {
        return
      }
      icon.parentNode.removeChild(icon)
    })

    // 生成随机字符串，防止缓存
    const randomString = Math.random().toString(36).substring(2, 15)

    // 创建新的图标链接
    const link = document.createElement('link')
    link.type = 'image/svg+xml'
    link.rel = 'icon'
    link.href = `${faviconUrl}?v=${randomString}`
    document.head.appendChild(link)

    // 将图标URL保存到localStorage
    try {
      localStorage.setItem('current_favicon', faviconUrl)
    } catch (e) {
      console.warn('保存图标URL到localStorage失败:', e)
    }
  } else {
    // 如果没有自定义图标，使用默认图标
    const link = document.createElement('link')
    link.type = 'image/svg+xml'
    link.rel = 'icon'
    link.href = '/favicon.svg'
    document.head.appendChild(link)
  }

  // 优化页面加载性能
  // 预加载关键资源
  const preloadResources = () => {
    // 预加载Logo图片
    const logoUrl = configStore.value.config.site_logo
    if (logoUrl) {
      const imgPreload = new Image()
      imgPreload.src = logoUrl
    }

    // 设置网站图标
    const faviconUrl = configStore.value.config.Favicon || configStore.value.config.site_favicon
    if (faviconUrl) {
      const link = document.querySelector("link[rel*='icon']") || document.createElement('link')
      link.type = 'image/x-icon'
      link.rel = 'shortcut icon'
      link.href = faviconUrl
      document.getElementsByTagName('head')[0].appendChild(link)
    }

    // 加载关键CSS
    const loadCriticalCSS = (href) => {
      // 检查是否已经加载
      if (document.querySelector(`link[href="${href}"]`)) return

      const link = document.createElement('link')
      link.rel = 'stylesheet'
      link.href = href
      document.head.appendChild(link)
    }

    // 预加载字体图标
    const preloadFont = (url, type = 'woff2') => {
      // 检查是否已经预加载
      if (document.querySelector(`link[href="${url}"]`)) return

      const fontLink = document.createElement('link')
      fontLink.rel = 'preload'
      fontLink.href = url
      fontLink.as = 'font'
      fontLink.type = `font/${type}`
      fontLink.crossOrigin = 'anonymous'
      document.head.appendChild(fontLink)
    }

    // 预加载常用图标字体
    preloadFont('/fonts/element-icons.woff', 'woff')

    // 当用户闲置时预加载其他资源
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(() => {
        // 预加载常用图片
        const commonImages = [
          '/images/default-avatar.png',
          '/images/default-cover.jpg'
        ]

        commonImages.forEach(imgUrl => {
          const img = new Image()
          img.src = imgUrl
        })
      })
    }
  }

  // 在配置加载完成后预加载资源
  if (configStore.value.initialized) {
    preloadResources()
  } else {
    // 监听配置加载完成事件
    window.addEventListener('blog-config-ready', preloadResources, { once: true })
  }

  // 添加语言变更事件监听器
  window.addEventListener('language-changed', (event) => {
    // 触发语言变更后的DOM更新
    nextTick(() => {
      // 强制刷新可能需要更新的组件
      const i18nElements = document.querySelectorAll('.el-menu, .el-form, .el-table, .el-card, .el-pagination')
      i18nElements.forEach(el => {
        if (el) {
          el.classList.add('i18n-refresh')
          setTimeout(() => {
            el.classList.remove('i18n-refresh')
          }, 50)
        }
      })

      // 强制重绘整个应用
      // 使用简单的方法强制重绘DOM
      document.body.style.display = 'none';
      // 触发回流
      void document.body.offsetHeight;
      // 恢复显示
      document.body.style.display = '';
    })
  })

  // 初始化配置
  configStore.value.fetchConfig()
})

onBeforeUnmount(() => {
  // 移除语言变更事件监听器
  window.removeEventListener('language-changed', () => {})
})



// 简化的标题更新逻辑，避免可能的循环引用
watch(() => route.meta.title, (newTitle) => {
  if (newTitle) {
    document.title = newTitle
  }
})

// 简化的配置监听，避免可能的循环引用
watch(() => configStore.value.config.site_title, (newSiteTitle) => {
  try {
    if (route.meta.title) {
      document.title = route.meta.title
    } else if (newSiteTitle) {
      document.title = newSiteTitle
    } else {
      document.title = isAdminRoute.value ? '管理后台' : '首页'
    }
  } catch (error) {
    console.error('配置变化处理错误:', error)
    document.title = isAdminRoute.value ? '管理后台' : '首页'
  }
})

// 检查App.vue中的代码结构并添加全局语言切换监听器
</script>

<template>
  <div class="app">
    <router-view />
    <!-- 移除Footer组件，避免重复显示 -->
  </div>
</template>

<style>
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* 主题过渡 */
.theme-transition,
.theme-transition * {
  transition: background-color 0.3s ease,
              color 0.3s ease,
              border-color 0.3s ease,
              background-image 0.3s ease,
              box-shadow 0.3s ease !important;
}

/* 亮色主题(默认) */
:root {
  --text-color: #333;
  --bg-color: #fff;
  --border-color: #dcdfe6;

  /* 应用自定义变量 */
  --app-bg-color: #ffffff;
  --app-text-color: #303133;
  --app-border-color: #ebeef5;
  --app-shadow-color: rgba(0, 0, 0, 0.1);
  --app-primary-color: #409EFF;
  --app-success-color: #67C23A;
  --app-warning-color: #E6A23C;
  --app-danger-color: #F56C6C;
  --app-info-color: #909399;
}

/* 浅色模式 */
html.light-theme {
  --text-color: #333;
  --bg-color: #fff;
  --border-color: #dcdfe6;

  --app-bg-color: #ffffff;
  --app-text-color: #303133;
  --app-border-color: #ebeef5;
  --app-shadow-color: rgba(0, 0, 0, 0.1);
}

/* 系统浅色模式 - 添加淡蓝色调 */
html.system-light-theme {
  --text-color: #334155;
  --bg-color: #f8fafc;
  --border-color: #e0e7ff;

  --app-bg-color: #f0f7ff;
  --app-text-color: #334155;
  --app-border-color: rgba(100, 150, 255, 0.2);
  --app-shadow-color: rgba(30, 64, 175, 0.08);
}

/* 暗色主题 */
html.dark-theme {
  --text-color: #e0e0e0;
  --bg-color: #232324;
  --border-color: #333;

  --app-bg-color: #232324;
  --app-text-color: #e0e0e0;
  --app-border-color: rgba(255, 255, 255, 0.1);
  --app-shadow-color: rgba(0, 0, 0, 0.3);
}

/* 系统暗黑模式 */
html.system-dark-theme {
  --text-color: #e0e0e0;
  --bg-color: #141414;
  --border-color: #333;

  --app-bg-color: #141414;
  --app-text-color: #e0e0e0;
  --app-border-color: rgba(255, 255, 255, 0.1);
  --app-shadow-color: rgba(0, 0, 0, 0.3);
}

/* Element Plus 暗模式兼容 */
html.dark {
  color-scheme: dark;
}

body {
  color: var(--text-color);
  background-color: var(--bg-color);
}

.app {
  width: 100vw;
  height: 100vh;
  position: relative;
  overflow-x: hidden;
}

/* 移除Element Plus按钮的焦点轮廓 */
.el-button:focus,
.el-button:focus-visible {
  outline: none !important;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.3) !important;
}

/* 移除所有按钮的焦点轮廓 */
button:focus,
button:focus-visible {
  outline: none !important;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.3) !important;
}

/* 主题过渡动画 */
*, *::before, *::after {
  transition: background-color 0.3s ease,
              color 0.3s ease,
              border-color 0.3s ease,
              box-shadow 0.3s ease;
}

.theme-refresh {
  animation: theme-refresh-flash 0.1s;
}

@keyframes theme-refresh-flash {
  0% { opacity: 0.8; }
  100% { opacity: 1; }
}

/* 隐藏ant-design-vue的控制台警告，直到所有组件都更新完成 */
.ant-design-vue-warning-hide {
  display: none !important;
}
</style>

<style scoped>
.logo {
  height: 6em;
  padding: 1.5em;
  will-change: filter;
  transition: filter 300ms;
}
.logo:hover {
  filter: drop-shadow(0 0 2em #646cffaa);
}
.logo.vue:hover {
  filter: drop-shadow(0 0 2em #42b883aa);
}
</style>
