import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import './assets/blog.css'
import '../../../assets/css/like-button.css' // 导入点赞按钮样式
import '../../../assets/css/favorite-button.css' // 导入收藏按钮样式
import { useBlogConfigStore } from '@/stores/blogConfig'

// Font Awesome CSS已通过CDN加载

// 创建应用实例
const app = createApp(App)

// 创建Pinia实例
const pinia = createPinia()

// 使用插件
app.use(pinia)
app.use(router)
app.use(ElementPlus, { size: 'default' })

// 注册Element Plus图标组件
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}

// 将router实例挂载到window对象，以便在API拦截器中使用
window.router = router

// 初始化博客配置
const blogConfigStore = useBlogConfigStore(pinia)

// 加载配置
blogConfigStore.fetchConfig().then(() => {
  // 配置加载完成后执行的操作
  console.log('Blog configuration loaded')

  // 更新页面标题
  document.title = blogConfigStore.siteTitle

  // 触发自定义事件，通知配置已加载完成
  window.dispatchEvent(new CustomEvent('blog-config-ready'))
}).catch(error => {
  console.error('Failed to load blog configuration:', error)
})

// 挂载应用
app.mount('#app')