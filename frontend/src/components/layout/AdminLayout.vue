<!-- 管理员布局组件 -->
<template>
  <div class="admin-layout">
    <el-container class="layout-container">
      <!-- 侧边栏 -->
      <el-aside :width="isCollapse ? '64px' : '220px'" class="aside">
        <div class="logo-container">
          <h1 class="logo" v-if="!isCollapse">{{ t('layout.appName') }}</h1>
          <h1 class="logo-collapsed" v-else>{{ t('layout.appNameShort') }}</h1>
        </div>
        <el-menu
          :default-active="activeMenu"
          class="el-menu-vertical"
          :background-color="menuBackgroundColor"
          :text-color="menuTextColor"
          :active-text-color="menuActiveTextColor"
          :collapse="isCollapse"
          router
        >
          <el-sub-menu index="1">
            <template #title>
              <el-icon><Monitor /></el-icon>
              <span>{{ t('layout.dashboard') }}</span>
            </template>
            <el-menu-item index="/admin/dashboard">{{ t('layout.overview') }}</el-menu-item>
          </el-sub-menu>

          <!-- 用户管理菜单 -->
          <el-sub-menu index="2">
            <template #title>
              <el-icon><User /></el-icon>
              <span>{{ t('layout.userManagement') }}</span>
            </template>
            <el-menu-item index="/admin/users">{{ t('layout.userList') }}</el-menu-item>
          </el-sub-menu>

          <!-- 文章管理菜单 -->
          <el-sub-menu index="3">
            <template #title>
              <el-icon><Document /></el-icon>
              <span>{{ t('layout.articleManagement') }}</span>
            </template>
            <el-menu-item index="/admin/article/list">{{ t('layout.articleList') }}</el-menu-item>
            <el-menu-item index="/admin/category">{{ t('layout.categoryManagement') }}</el-menu-item>
            <el-menu-item index="/admin/tag">{{ t('layout.tagManagement') }}</el-menu-item>
            <el-menu-item index="/admin/comment">{{ t('layout.commentManagement') }}</el-menu-item>
            <el-menu-item index="/admin/like">{{ t('layout.likeManagement') }}</el-menu-item>
            <el-menu-item index="/admin/favorite">{{ t('layout.favoriteManagement') }}</el-menu-item>
          </el-sub-menu>

          <!-- AI工具管理菜单 -->
          <el-sub-menu index="4">
            <template #title>
              <el-icon><Tools /></el-icon>
              <span>{{ t('layout.aiToolManagement') }}</span>
            </template>
            <el-menu-item index="/admin/aiTool-list">{{ t('layout.aiToolList') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-category">{{ t('layout.aiToolCategoryManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-tag">{{ t('layout.aiToolTagManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-comment">{{ t('layout.aiToolCommentManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-comment-like">{{ t('layout.aiToolCommentLikeManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-favorite">{{ t('layout.aiToolFavoriteManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-like">{{ t('layout.aiToolLikeManagement') }}</el-menu-item>
            <el-menu-item index="/admin/aiTool-visit-log">{{ t('layout.aiToolVisitLogManagement') }}</el-menu-item>
          </el-sub-menu>

          <!-- 工具管理菜单 -->
          <el-sub-menu index="5">
            <template #title>
              <el-icon><Tools /></el-icon>
              <span>{{ t('layout.toolsManagement') }}</span>
            </template>
            <el-menu-item index="/admin/ipLocation">{{ t('layout.ipLocationTool') }}</el-menu-item>
          </el-sub-menu>

          <!-- 友情链接管理菜单 -->
          <el-menu-item index="/admin/link">
            <el-icon><Link /></el-icon>
            <span>{{ t('layout.friendLinkManagement') }}</span>
          </el-menu-item>

          <!-- 留言管理菜单 -->
          <el-sub-menu index="6">
            <template #title>
              <el-icon><ChatDotRound /></el-icon>
              <span>{{ t('layout.contactManagement') }}</span>
            </template>
            <el-menu-item index="/admin/contact/messages">{{ t('layout.contactMessages') }}</el-menu-item>
          </el-sub-menu>

          <el-sub-menu index="7">
            <template #title>
              <el-icon><Setting /></el-icon>
              <span>{{ t('layout.systemSettings') }}</span>
            </template>
            <el-menu-item index="/admin/settings">{{ t('layout.websiteSettings') }}</el-menu-item>
            <el-menu-item index="/admin/system-config">{{ t('layout.systemConfig') }}</el-menu-item>
            <el-menu-item index="/admin/activity-log">{{ t('layout.activityLog') }}</el-menu-item>
            <el-menu-item index="/admin/view-duration-stats">{{ t('layout.viewDurationStats') }}</el-menu-item>
          </el-sub-menu>
        </el-menu>

        <!-- 添加侧边栏折叠按钮 -->
        <div class="sidebar-collapse-btn" @click="toggleSidebar">
          <el-icon>
            <Fold v-if="!isCollapse" />
            <Expand v-else />
          </el-icon>
          <span v-if="!isCollapse">{{ t('layout.collapseMenu') }}</span>
        </div>
      </el-aside>

      <el-container>
        <!-- 顶部导航 -->
        <el-header class="header">
          <div class="header-left">
            <el-icon class="toggle-icon" @click="toggleSidebar">
              <Fold v-if="!isCollapse" />
              <Expand v-else />
            </el-icon>
            <breadcrumb />
          </div>
          <div class="header-right">
            <!-- 语言切换 -->
            <div class="language-switch">
              <el-dropdown trigger="click" @command="handleLanguageChange">
                <el-button class="language-button" circle>
                  {{ currentLanguage }}
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="zh">
                      <span>中文</span>
                    </el-dropdown-item>
                    <el-dropdown-item command="en">
                      <span>English</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>

            <el-dropdown trigger="click">
              <div class="avatar-container">
                <el-avatar
                  :size="32"
                  :src="adminInfo.avatar || 'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png'"
                />
                <span class="username">{{ adminInfo.username }}</span>
              </div>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="router.push('/admin/profile')">{{ t('layout.profile') }}</el-dropdown-item>
                  <el-dropdown-item divided @click="handleLogout">{{ t('layout.logout') }}</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </el-header>

        <!-- 主内容区 -->
        <el-main class="main-content">
          <router-view />
        </el-main>
        <!-- 页脚 -->
        <el-footer class="footer footer-container">
          <Footer />
        </el-footer>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { User, Setting, Fold, Expand, Document, Link, ChatDotRound, Tools } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useLanguageStore } from '@/stores/language'
import { useI18n } from 'vue-i18n'
import Footer from '@/components/common/Footer.vue'
import Breadcrumb from '@/components/layout/Breadcrumb.vue'
import adminApi from '@/api/admin'
import apiConfig from '@/api/config'
import { useConfigStore } from '@/stores/config'

const router = useRouter()
const route = useRoute()
const languageStore = useLanguageStore()
const { t, locale } = useI18n()
// 侧边栏折叠状态

const isCollapse = ref(false)

// 管理员信息
const adminInfo = ref({
  username: 'Admin',
  avatar: ''
})

// 获取管理员信息
const getAdminInfo = async () => {
  try {
    const response = await adminApi.post(apiConfig.ADMIN_API.INFO)
    if (response.code === 200 && response.data) {
      // 保存到本地存储
      localStorage.setItem('admin_info', JSON.stringify(response.data))
    }
  } catch (error) {
    // 不显示错误信息，静默处理
    // 如果是因为 token 验证失败，已经在 admin.js 中处理了跳转
  }
}

// 从本地存储获取管理员信息
const loadAdminInfo = () => {
  const savedInfo = localStorage.getItem('admin_info')
  if (savedInfo) {
    try {
      const parsedInfo = JSON.parse(savedInfo)
      adminInfo.value = parsedInfo
    } catch (e) {
      console.error('解析管理员信息失败:', e)
    }
  }
}




// 从本地存储恢复折叠状态和管理员信息
onMounted(() => {
  const aside = document.querySelector('.aside')
  const savedCollapseState = localStorage.getItem('sidebarCollapse')
  if (savedCollapseState !== null) {
    isCollapse.value = savedCollapseState === 'true'
  }
  const asideWidth = isCollapse.value ? '64px' : '220px';
  const asideStyle = aside ? window.getComputedStyle(aside) : {}
  aside.style.width = asideWidth;
  //console.log(isCollapse.value,'isCollapse.valueisCollapse.valueisCollapse.valueisCollapse.value')
  aside.setAttribute('data-original-width', isCollapse.value)
  aside.setAttribute('data-original-border-right', asideStyle.borderRight || '')


  // 加载管理员信息
  loadAdminInfo()
  // 获取最新的管理员信息
  getAdminInfo()
  // 初始化语言
  languageStore.initLanguage()
})



// 保存折叠状态到本地存储
const saveCollapseState = () => {
  localStorage.setItem('sidebarCollapse', isCollapse.value)
}

// 当前激活的菜单
const activeMenu = computed(() => {
  return route.path
})

// 切换侧边栏折叠状态
const toggleSidebar = () => {
  isCollapse.value = !isCollapse.value
  const aside = document.querySelector('.aside')
  const asideWidth = isCollapse.value ? '64px' : '220px';
  const asideStyle = aside ? window.getComputedStyle(aside) : {}
  aside.style.width = asideWidth;
  aside.setAttribute('data-original-width', asideWidth)
  aside.setAttribute('data-original-border-right', asideStyle.borderRight || '')
  saveCollapseState()
}

// 退出登录
const handleLogout = async () => {
  // 先清除本地存储的token和管理员信息
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_info')

  try {
    // 尝试调用后端退出接口，但不依赖其成功
    await adminApi.post(apiConfig.ADMIN_API.LOGOUT)
  } catch (error) {
    // 忽略错误，不显示错误消息
    console.log('已在前端清除登录状态')
  } finally {
    // 无论请求成功与否，都跳转到登录页并显示成功消息
    router.push('/admin/login')
    ElMessage.success(t('layout.logoutSuccess'))
  }
}



// Current language display
const currentLanguage = computed(() => {
  return locale.value === 'zh' ? '中' : 'En'
})

// Handle language change
const handleLanguageChange = (lang) => {
  locale.value = lang
  localStorage.setItem('language', lang)
  document.documentElement.setAttribute('lang', lang)

  // 更新语言设置
  languageStore.setLanguage(lang)

  // 更新页面标题，从configStore导入
  const configStore = useConfigStore()
  configStore.updatePageTitle()

  // 添加延迟强制刷新，确保所有组件都正确更新语言
  nextTick(() => {
    const event = new CustomEvent('language-changed', { detail: { lang } })
    window.dispatchEvent(event)

    // 如果有任何组件未能正确更新，可以强制重新渲染一些关键组件
    const layoutComponents = document.querySelectorAll('.el-menu, .el-dialog, .el-table, .el-form')
    layoutComponents.forEach(comp => {
      comp.classList.add('i18n-refresh')
      setTimeout(() => {
        comp.classList.remove('i18n-refresh')
      }, 50)
    })
  })
}

// 菜单背景颜色
const menuBackgroundColor = computed(() => {
  return '#ffffff'
})

// 菜单文本颜色
const menuTextColor = computed(() => {
  return '#303133'
})

// 菜单激活文本颜色
const menuActiveTextColor = computed(() => {
  return '#409EFF'
})
</script>

<style scoped>
.admin-layout {
  height: 100vh;
  overflow: hidden;
}

.layout-container {
  height: 100%;
}

.aside {
  background-color: var(--sidebar-bg-color, #ffffff);
  transition: width 0.15s, background-color 0.15s;
  overflow-x: hidden;
  overflow-y: auto;
  position: relative;
  will-change: width, background-color;
  box-sizing: border-box;
  position: sticky;
  left: 0;
  top: 0;
  z-index: 10;
  /* border-right-width: 1px;
  border-right-style: solid; */
}

/* 为侧边栏根据主题模式提供不同背景色 */
:global(.light-theme) .aside {
  --sidebar-bg-color: #ffffff;
  border-right: 1px solid #eee;
}

:global(.dark-theme) .aside {
  --sidebar-bg-color: #ffffff;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.logo-container {
  background-color: var(--logo-bg-color, #e8ebef);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  padding: 0 10px;
  transition: all 0.15s;
  overflow: hidden;
  will-change: background-color;
}

/* 为logo容器根据主题模式提供不同背景色 */
:global(.light-theme) .logo-container {
  --logo-bg-color: #ffffff;
  border-bottom: 1px solid #eee;
  border-right: 1px solid #eee;
}

:global(.dark-theme) .logo-container {
  --logo-bg-color: #232324;
}

.logo {
  color: var(--logo-text-color, #000);
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  transition: all 0.3s;
}

:global(.light-theme) .logo,
:global(.light-theme) .logo-collapsed {
  --logo-text-color: #000;
}

:global(.dark-theme) .logo,
:global(.dark-theme) .logo-collapsed {
  --logo-text-color: #000;
}

:global(.system-dark-theme) .logo,
:global(.system-dark-theme) .logo-collapsed,
:global(html.dark.system-theme) .logo,
:global(html.dark.system-theme) .logo-collapsed {
  --logo-text-color: #000;
}

.logo-collapsed {
  color: var(--logo-text-color, #000);
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  transition: all 0.3s;
}

.el-menu-vertical:not(.el-menu--collapse) {
  width: 220px;
}

.el-menu-vertical.el-menu--collapse {
  width: 64px;
}

/* 侧边栏折叠按钮样式 */
.sidebar-collapse-btn {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bfcbd9;
  cursor: pointer;
  transition: all 0.3s;
  background-color: #2b3649;
  border-top: 1px solid #1f2d3d;
  display: none;
}

.sidebar-collapse-btn:hover {
  color: #409EFF;
  background-color: #263445;
}

.sidebar-collapse-btn .el-icon {
  font-size: 18px;
  margin-right: 8px;
}

.sidebar-collapse-btn span {
  font-size: 14px;
}

.header {
  background-color: #fff;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

/* 暗黑模式头部导航 */
:global(.dark-theme) .header {
  background-color: #232324;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}

/* 系统暗黑模式头部导航 */
:global(.system-dark-theme) .header,
:global(html.dark.system-theme) .header {
  background-color: #141414;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.header-left {
  display: flex;
  align-items: center;
}

.toggle-icon {
  font-size: 20px;
  cursor: pointer;
  margin-right: 20px;
  transition: all 0.3s;
}

.toggle-icon:hover {
  color: #409EFF;
}

.header-right {
  display: flex;
  align-items: center;
}

.avatar-container {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 0 8px;
  height: 100%;
  transition: all 0.3s;
}

.avatar-container:hover {
  background: rgba(0, 0, 0, 0.025);
}

.username {
  margin-left: 8px;
  font-size: 14px;
  color: #606266;
}

/* 暗黑模式用户名颜色 */
:global(.dark-theme) .username {
  color: #e0e0e0;
}

/* 系统暗黑模式用户名颜色 - 添加蓝色调 */
:global(.system-dark-theme) .username,
:global(html.dark.system-theme) .username {
  color: #a4c0f4;
}

.main-content {
  padding: 20px;
  overflow-y: auto;
  background-color: #f0f2f5;
  transition: all 0.3s;
}

/* 暗黑模式主内容区 */
:global(.dark-theme) .main-content {
  background-color: #232324;
}

/* 系统暗黑模式主内容区 */
:global(.system-dark-theme) .main-content,
:global(html.dark.system-theme) .main-content {
  background-color: #141414;
}

.footer {
  padding: 0;
  height: auto;
  line-height: normal;
}

.footer-container {
  width: 100%;
  margin-top: auto; /* 确保Footer始终在底部 */
  background-color: #f0f2f5;
  background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* 暗黑模式页脚 */
:global(.dark-theme) .footer-container {
  background-color: #232324;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 系统暗黑模式页脚 */
:global(.system-dark-theme) .footer-container,
:global(html.dark.system-theme) .footer-container {
  background-color: #141414;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 添加菜单折叠动画 */
.el-menu-vertical {
  transition: width 0.3s, background-color 0.3s;
  will-change: width;
  border-right: none !important;
}

/* 优化折叠时的图标样式 */
.el-menu--collapse .el-sub-menu__title .el-icon {
  margin-right: 0;
}

/* 优化折叠时的菜单项样式 */
.el-menu--collapse .el-menu-item {
  padding-left: 20px !important;
}

/* 响应式布局 */
@media screen and (max-width: 768px) {
  .aside {
    position: fixed;
    height: 100%;
    z-index: 1000;
    transform: translateX(0);
    transition: transform 0.3s;
  }

  .aside.collapsed {
    transform: translateX(-100%);
  }

  .main-content {
    margin-left: 0;
  }
}

:deep(.el-avatar) {
  background: #fff;
  border: 1px solid #e6e6e6;

  &:hover {
    transform: scale(1.1);
  }
}

.theme-switch {
  margin-right: 16px;
}

.theme-button {
  padding: 8px;
  font-size: 18px;
  color: var(--el-text-color-regular);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.theme-button:hover {
  color: var(--el-color-primary);
  background: var(--el-color-primary-light-9);
}

:deep(.el-dropdown-menu__item) {
  display: flex;
  align-items: center;
  gap: 8px;
}

:deep(.el-dropdown-menu__item .el-icon) {
  margin-right: 4px;
}

.language-switch {
  margin-right: 15px;
}

.language-button {
  font-size: 14px;
  font-weight: 500;
  height: 32px;
  width: 32px;
  padding: 0;
  background-color: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
  transition: all 0.3s;
}

.language-button:hover {
  background-color: var(--el-color-primary-light-7);
}

.theme-switch {
  margin-right: 15px;
}

/* 添加特殊样式触发菜单重绘而不改变结构 */
.theme-menu-refresh {
  animation: menu-refresh 0.2s;
}

@keyframes menu-refresh {
  0% { opacity: 0.8; }
  100% { opacity: 1; }
}
</style>