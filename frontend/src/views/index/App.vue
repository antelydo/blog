<template>
  <div class="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useBlogConfigStore } from '@/stores/blogConfig'
import { useRoute } from 'vue-router'

// 获取当前路由
const route = useRoute()

// 获取博客配置
const blogConfigStore = useBlogConfigStore()

onMounted(() => {

  // 预加载关键资源
  const preloadResources = () => {
    // 预加载Logo图片
    const logoUrl = blogConfigStore.siteLogo
    if (logoUrl) {
      const imgPreload = new Image()
      imgPreload.src = logoUrl
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

      // 添加onload事件，确保预加载资源被使用
      fontLink.onload = () => {
        console.log(`Font preloaded: ${url}`)
        // 可以在这里触发字体已加载的事件
        window.dispatchEvent(new CustomEvent('font-preloaded', { detail: { url } }))
      }

      document.head.appendChild(fontLink)
    }

    // 预加载常用图标字体
    preloadFont('/fonts/element-icons.woff', 'woff')
    // Font Awesome字体已在index.html中预加载
  }

  // 在配置加载完成后预加载资源
  if (blogConfigStore.initialized) {
    preloadResources()
  } else {
    // 监听配置加载完成事件
    window.addEventListener('blog-config-ready', preloadResources, { once: true })
  }
})

// 监听路由变化，更新页面标题
watch(() => route.meta.title, (newTitle) => {
  if (newTitle) {
    document.title = `${newTitle} - ${blogConfigStore.siteName}`
  }
})

// 监听网站配置变化，更新页面标题
watch(() => blogConfigStore.siteName, (newSiteName) => {
  if (route.meta.title) {
    document.title = `${route.meta.title} - ${newSiteName}`
  } else {
    document.title = newSiteName
  }
})
</script>

<style>
/* 前端C端页面特定的主题样式 */
:root {
  --primary-color: #09c;
  --primary-hover: #007fad;
  --secondary-color: #1c2033;
  --text-color: #555;
  --light-text-color: #999;
  --link-color: #09c;
  --border-color: #eee;
  --bg-color: #f8f8f8;
  --card-bg-color: #fff;
  --heading-color: #333;
  --font-family: 'Microsoft Yahei', Arial, sans-serif;
  --box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  --transition-speed: 0.3s;
  --border-radius: 3px;
  --container-width: 1200px;
}

/* 应用全局样式 */
body {
  color: var(--text-color);
  background-color: var(--bg-color);
}

.app {
  width: 100%;
  min-height: 100vh;
  position: relative;
}
</style>
