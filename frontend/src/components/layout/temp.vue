
<!-- 管理员布局组件 -->
<template>
  <div class="admin-layout">
    <el-container class="layout-container">
      <!-- 侧边栏 -->
      <el-aside class="aside" :class="{ collapsed: isCollapse }">
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
          </el-sub-menu>

          <!-- 友情链接管理菜单 -->
          <el-menu-item index="/admin/link">
            <el-icon><Link /></el-icon>
            <span>{{ t('layout.friendLinkManagement') }}</span>
          </el-menu-item>

          <el-sub-menu index="4">
            <template #title>
              <el-icon><Setting /></el-icon>
              <span>{{ t('layout.systemSettings') }}</span>
            </template>
            <el-menu-item index="/admin/settings">{{ t('layout.websiteSettings') }}</el-menu-item>
            <el-menu-item index="/admin/activity-log">{{ t('layout.activityLog') }}</el-menu-item>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { User, Setting, Fold, Expand, Document, Link } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useLanguageStore } from '@/stores/language'
import { useI18n } from 'vue-i18n'
import Footer from '@/components/common/Footer.vue'
import Breadcrumb from '@/components/layout/Breadcrumb.vue'
import adminApi, { apiUrls } from '@/api/admin'

const router = useRouter()
const route = useRoute()
const languageStore = useLanguageStore()
const { t, locale } = useI18n()

// 管理员信息
const adminInfo = ref({
  username: 'Admin',
  avatar: ''
})

// 获取管理员信息
const getAdminInfo = async () => {
  try {
    const response = await adminApi.post(apiUrls.ADMIN_API.INFO)
    if (response.code === 200 && response.data) {
      // 保存到本地存储
      localStorage.setItem('admin_info', JSON.stringify(response.data))
    }
  } catch (error) {
    console.error('获取管理员信息失败:', error)
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

// 侧边栏折叠状态
const isCollapse = ref(false)

// 从本地存储恢复折叠状态和管理员信息
onMounted(() => {
  // 读取本地存储中的折叠状态
  try {
    const savedCollapseState = localStorage.getItem('sidebarCollapse')
    if (savedCollapseState !== null) {
      isCollapse.value = savedCollapseState === 'true'
    }
  } catch (error) {
    console.error('读取侧边栏状态失败:', error)
    // 默认设置为展开状态
    isCollapse.value = false
  }

  // 加载管理员信息
  loadAdminInfo()
  // 获取最新的管理员信息
  getAdminInfo()
  // 初始化语言
  languageStore.initLanguage()
})

// 组件卸载时清理
onBeforeUnmount(() => {
  // 清理工作
})

// 保存折叠状态到本地存储
const saveCollapseState = () => {
  try {
    localStorage.setItem('sidebarCollapse', String(isCollapse.value))
  } catch (error) {
    console.error('保存侧边栏状态失败:', error)
  }
}

// 当前激活的菜单
const activeMenu = computed(() => {
  return route.path
})

// 切换侧边栏折叠状态
const toggleSidebar = () => {
  isCollapse.value = !isCollapse.value
  saveCollapseState()
}

// 退出登录
const handleLogout = async () => {
  try {
    const response = await adminApi.post(apiUrls.ADMIN_API.LOGOUT)
    if (response.code === 200) {
      // 清除管理员token和信息
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
      // 跳转到管理员登录页
      router.push('/admin/login')
      ElMessage.success(t('layout.logoutSuccess'))
    } else {
      ElMessage.error(response.msg || t('layout.logoutFailed'))
    }
  } catch (error) {
    console.error('退出登录失败:', error)
    ElMessage.error(t('layout.logoutFailed'))
    // 即使请求失败也清除本地信息并跳转
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_info')
    router.push('/admin/login')
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
}

// 菜单背景颜色
const menuBackgroundColor = computed(() => {
  return '#ffffff'
})

// 菜单文本颜色
const menuTextColor = computed(() => {
  return '#606266'
})

// 菜单激活文本颜色
const menuActiveTextColor = computed(() => {
  return '#409eff'
})












  // 按钮颜色
  root.style.setProperty('--button-bg-color', customColors.value.buttonBg)
  root.style.setProperty('--el-button-bg-color', customColors.value.buttonBg)
  root.style.setProperty('--button-text-color', customColors.value.buttonText)
  root.style.setProperty('--el-button-text-color', customColors.value.buttonText)
  root.style.setProperty('--button-hover-bg-color', customColors.value.buttonHoverBg)
  root.style.setProperty('--el-button-hover-bg-color', customColors.value.buttonHoverBg)
  root.style.setProperty('--button-default-bg-color', customColors.value.defaultButtonBg)
  root.style.setProperty('--el-button-default-bg-color', customColors.value.defaultButtonBg)
  root.style.setProperty('--button-default-text-color', customColors.value.defaultButtonText)
  root.style.setProperty('--el-button-default-text-color', customColors.value.defaultButtonText)
  root.style.setProperty('--button-default-hover-bg-color', customColors.value.buttonDefaultHoverBg)
  root.style.setProperty('--el-button-default-hover-bg-color', customColors.value.buttonDefaultHoverBg)

  // 表格文本颜色
  root.style.setProperty('--table-text-color', customColors.value.tableTextColor)
  root.style.setProperty('--el-table-text-color', customColors.value.tableTextColor)

  // 链接颜色
  root.style.setProperty('--link-color', customColors.value.link)
  root.style.setProperty('--el-link-text-color', customColors.value.link)
  root.style.setProperty('--link-hover-color', customColors.value.linkHover)
  root.style.setProperty('--el-link-hover-text-color', customColors.value.linkHover)

  // 图标颜色
  root.style.setProperty('--icon-color', customColors.value.iconColor)
  root.style.setProperty('--el-icon-color', customColors.value.iconColor)

  // 边框颜色
  root.style.setProperty('--border-color', customColors.value.borderColor)
  root.style.setProperty('--el-border-color', customColors.value.borderColor)
  root.style.setProperty('--border-color-light', customColors.value.borderColorLight)
  root.style.setProperty('--el-border-color-light', customColors.value.borderColorLight)
  root.style.setProperty('--border-color-lighter', customColors.value.borderColorLighter)
  root.style.setProperty('--el-border-color-lighter', customColors.value.borderColorLighter)

  // 调整主题颜色的各种明暗变体
  generatePrimaryVariants(customColors.value.primary)

  // 存储当前颜色到本地存储
  saveThemeToStorage()
}

// 保存主题到本地存储
const saveThemeToStorage = () => {
  localStorage.setItem('color_theme', currentColorTheme.value)
  localStorage.setItem('custom_colors', JSON.stringify(customColors.value))

  // 触发主题变更事件
  document.dispatchEvent(new CustomEvent('theme-colors-changed', {
    detail: { colors: customColors.value }
  }))
}

// 生成主色调的明暗变体
const generatePrimaryVariants = (primaryColor) => {
  // 使用 CSS 变量生成主色调的变体
  const root = document.documentElement

  // 转换主色调为 RGB 格式
  let r = 0, g = 0, b = 0
  if (primaryColor.startsWith('#')) {
    const hex = primaryColor.substring(1)
    r = parseInt(hex.substring(0, 2), 16)
    g = parseInt(hex.substring(2, 4), 16)
    b = parseInt(hex.substring(4, 6), 16)
  } else if (primaryColor.startsWith('rgb')) {
    const match = primaryColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/)
    if (match) {
      r = parseInt(match[1])
      g = parseInt(match[2])
      b = parseInt(match[3])
    }
  }

  // 生成 9 个不同透明度的变体
  for (let i = 1; i <= 9; i++) {
    const alpha = i / 10
    root.style.setProperty(`--el-color-primary-light-${i}`, `rgba(${r}, ${g}, ${b}, ${alpha})`)
  }

  // 设置主色调的 hover 和 active 状态颜色
  const darken = (color, percent) => {
    const f = parseInt(color, 16)
    const t = percent < 0 ? 0 : 255
    const p = percent < 0 ? percent * -1 : percent
    return (Math.round((t - f) * p) + f).toString(16).padStart(2, '0')
  }

  if (primaryColor.startsWith('#')) {
    const primary = primaryColor.substring(1)
    const hoverColor = `#${darken(primary.substring(0, 2), -0.1)}${darken(primary.substring(2, 4), -0.1)}${darken(primary.substring(4, 6), -0.1)}`
    const activeColor = `#${darken(primary.substring(0, 2), -0.2)}${darken(primary.substring(2, 4), -0.2)}${darken(primary.substring(4, 6), -0.2)}`

    root.style.setProperty('--el-color-primary-hover', hoverColor)
    root.style.setProperty('--el-color-primary-active', activeColor)
  }
}

// 重置为默认颜色
const resetColors = () => {
  // 根据当前主题模式选择适当的默认主题
  const isDarkMode = themeStore.theme === 'dark' ||
                    (themeStore.theme === 'system' && themeStore.isSystemDarkMode)

  const defaultThemeName = isDarkMode ? 'default_dark' : 'default'
  const defaultTheme = presetThemes.value.find(theme => theme.name === defaultThemeName)

  if (defaultTheme) {
    currentColorTheme.value = defaultThemeName
    customColors.value = { ...defaultTheme.colors }
    applyCustomColors()
    ElMessage.success('已重置为默认配色')
  }
}

// 初始化配色方案
const initColorTheme = () => {
  // 加载自定义保存的主题
  loadCustomThemes()

  // 从本地存储加载配色方案
  const savedTheme = localStorage.getItem('color_theme')
  const savedColors = localStorage.getItem('custom_colors')

  if (savedTheme) {
    currentColorTheme.value = savedTheme

    // 检查是否为自定义保存的主题
    if (savedTheme.startsWith('custom_')) {
      const customTheme = customSavedThemes.value.find(t => t.name === savedTheme)
      if (customTheme) {
        customColors.value = { ...customTheme.colors }
        showDeleteButton.value = true
        applyCustomColors()
        return
      }
    }

    if (savedTheme !== 'custom' && savedColors) {
      try {
        // 检查当前主题模式，选择对应的颜色方案
        const currentTheme = getCurrentTheme()
        if (currentTheme) {
          customColors.value = { ...currentTheme.colors }
        } else {
          customColors.value = JSON.parse(savedColors)
        }
        applyCustomColors()
      } catch (e) {
        console.error('解析保存的配色方案失败', e)
        resetColors()
      }
    } else {
      // 应用预设主题
      const currentTheme = getCurrentTheme()
      if (currentTheme) {
        customColors.value = { ...currentTheme.colors }
        applyCustomColors()
      } else {
        resetColors()
      }
    }
  } else {
    // 默认主题
    resetColors()
  }
}

// 过滤预设配色方案
const filteredPresetThemes = computed(() => {
  if (themeDisplayMode.value === 'all') {
    return presetThemes.value
  } else if (themeDisplayMode.value === 'light') {
    return presetThemes.value.filter(theme => theme.mode === 'light')
  } else if (themeDisplayMode.value === 'dark') {
    return presetThemes.value.filter(theme => theme.mode === 'dark')
  } else {
    return []
  }
})

// 主题显示模式
const themeDisplayMode = ref('all')


</script>

<style>
.theme-mode-selector {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  gap: 10px;
}

.theme-mode-selector span {
  font-size: 14px;
  color: var(--el-text-color-regular);
}

:deep(.el-radio-button__inner) {
  padding: 5px 12px;
  font-size: 12px;
}

.preset-themes {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
  width: 100%;
}


/* 设置logo-container的背景色，提取到非scoped样式中以确保全局应用 */
.admin-layout .logo-container {
  background-color: #ffffff !important;
  border-bottom: 1px solid #eee;
  border-right: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  padding: 0 10px;
  transition: all 0.3s;
  overflow: hidden;
}

/* 深色模式下的logo容器 */
html.dark-theme .admin-layout .logo-container {
  background-color: #232324 !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

html.system-dark-theme .admin-layout .logo-container,
html.dark.system-theme .admin-layout .logo-container {
  background-color: #141414 !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}
</style>

<style scoped>
.admin-layout {
  height: 100vh;
  overflow: hidden;
}

.layout-container {
  height: 100%;
}

.aside {
  background-color: #f0f2f5;
  transition: width 0.28s ease;
  overflow-x: hidden;
  overflow-y: auto;
  position: relative;
  will-change: width, background-color;
  box-sizing: border-box;
  position: sticky;
  left: 0;
  top: 0;
  z-index: 10;
  border-right: 1px solid #eee;
  width: 220px;  /* 添加默认宽度 */
}

/* 为侧边栏根据主题模式提供不同背景色 */
:deep(.dark-theme) .aside {
  background-color: #232324;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

:deep(.system-dark-theme) .aside,
:deep(html.dark.system-theme) .aside {
  background-color: #141414;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

/* 当折叠时 */
.aside.collapsed {
  width: 64px !important;
}

.logo {
  color: var(--logo-text-color, #fff);
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  transition: all 0.3s;
}

:deep(.light-theme) .logo,
:deep(.system-light-theme) .logo,
:deep(.light-theme) .logo-collapsed,
:deep(.system-light-theme) .logo-collapsed {
  --logo-text-color: var(--el-color-primary, #409EFF);
}

:deep(.dark-theme) .logo,
:deep(.dark-theme) .logo-collapsed {
  --logo-text-color: #fff;
}

:deep(.system-dark-theme) .logo,
:deep(.system-dark-theme) .logo-collapsed,
:deep(html.dark.system-theme) .logo,
:deep(html.dark.system-theme) .logo-collapsed {
  --logo-text-color: #fff;
}

.logo-collapsed {
  color: var(--logo-text-color, #fff);
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
  background-color: transparent;
  border-top: 1px solid var(--border-color-lighter);
  padding: 0 16px;
  z-index: 11;
}

.sidebar-collapse-btn:hover {
  color: var(--primary-color);
  background-color: var(--button-default-hover-bg-color);
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
:deep(.dark-theme) .header {
  background-color: #232324;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}

/* 系统暗黑模式头部导航 */
:deep(.system-dark-theme) .header,
:deep(html.dark.system-theme) .header {
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
:deep(.dark-theme) .username {
  color: #e0e0e0;
}

/* 系统暗黑模式用户名颜色 - 添加蓝色调 */
:deep(.system-dark-theme) .username,
:deep(html.dark.system-theme) .username {
  color: #a4c0f4;
}

.main-content {
  padding: 20px;
  overflow-y: auto;
  background-color: #f0f2f5;
  transition: all 0.3s;
}

/* 暗黑模式主内容区 */
:deep(.dark-theme) .main-content {
  background-color: #232324;
}

/* 系统暗黑模式主内容区 */
:deep(.system-dark-theme) .main-content,
:deep(html.dark.system-theme) .main-content {
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
:deep(.dark-theme) .footer-container {
  background-color: #232324;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 系统暗黑模式页脚 */
:deep(.system-dark-theme) .footer-container,
:deep(html.dark.system-theme) .footer-container {
  background-color: #141414;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 添加菜单折叠动画 */
.el-menu-vertical {
  transition: width 0.28s ease;
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

.theme-color-switch {
  margin-right: 15px;
}

.theme-color-button {
  padding: 8px;
  font-size: 18px;
  color: var(--el-text-color-regular);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.theme-color-button:hover {
  color: var(--el-color-primary);
  background: var(--el-color-primary-light-9);
}

/* 自定义配色面板样式 */
.color-theme-container {
  padding: 20px;
}

.theme-section {
  margin-bottom: 20px;
}

.theme-section h3 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: 500;
  color: var(--el-text-color-primary);
}

.preset-themes {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
  width: 100%;
}

.theme-radio {
  width: 100%;
  height: 100%;
  margin-right: 0;
}

/* Hide the default radio button circle but keep it functional */
.theme-radio :deep(.el-radio__input) {
  position: absolute;
  top: 5px;
  right: 5px;
  z-index: 2;
  display: none;
}

/* Style for the entire theme item container */
.preset-theme-item {
  width: 80px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  border-radius: 4px;
  padding: 8px 4px;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* Active theme styling */
.preset-theme-item.active {
  background-color: var(--el-color-primary-light-9);
  box-shadow: 0 0 0 2px var(--el-color-primary);
}

/* Add a checkmark to indicate selection */
.preset-theme-item.active::after {
  content: "✓";
  position: absolute;
  top: 4px;
  right: 4px;
  color: var(--el-color-primary);
  font-weight: bold;
  z-index: 1;
}

.preset-theme-item:hover {
  background-color: var(--el-fill-color-light);
  box-shadow: 0 0 0 1px var(--el-color-primary-light-5);
}

.theme-preview {
  width: 64px;
  height: 40px;
  margin: 0 auto 8px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--el-border-color-lighter);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.preview-header {
  height: 8px;
}

.preview-body {
  height: 32px;
  padding: 4px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.preview-button {
  width: 60%;
  height: 6px;
  border-radius: 2px;
  margin: 0 auto;
}

.preview-text {
  width: 70%;
  height: 2px;
  margin: 0 auto;
}

.preview-link {
  width: 70%;
  height: 2px;
  margin: 0 auto;
}

.preset-theme-item span {
  font-size: 12px;
  display: block;
  color: var(--el-text-color-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preset-theme-item:hover {
  background-color: var(--el-fill-color-light);
}

.preset-theme-item:hover .theme-preview {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.el-form-item {
  margin-bottom: 16px;
}

.el-form-item .el-input {
  width: calc(100% - 45px);
  margin-left: 8px;
}

.el-color-picker {
  vertical-align: middle;
}

.theme-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}

/* 添加特殊样式触发菜单重绘而不改变结构 */
.theme-menu-refresh {
  opacity: 0.99;
}

.live-preview {
  margin-bottom: 20px;
}

.preview-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
}

.preview-card {
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.preview-card-header {
  padding: 8px;
  background-color: var(--el-color-primary-light-9);
}

.preview-card-title {
  font-size: 14px;
  font-weight: 500;
}

.preview-card-body {
  padding: 16px;
}

.preview-buttons {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.preview-buttons.hover-demo {
  margin-top: 8px;
}

.mt-2 {
  margin-top: 8px;
}

.preview-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.3s;
}

.preview-btn.primary {
  background-color: var(--button-bg-color);
  color: var(--button-text-color);
}

.preview-btn.default {
  background-color: var(--button-default-bg-color);
  color: var(--button-default-text-color);
  border: 1px solid var(--border-color);
}

.preview-btn.primary-hover {
  background-color: var(--button-hover-bg-color);
  color: var(--button-text-color);
}

.preview-btn.default-hover {
  color: var(--primary-color);
  border: 1px solid var(--primary-color);
  background-color: var(--button-default-hover-bg-color);
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-table-container {
  margin-top: 20px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid var(--border-color);
  font-size: 13px;
  transition: all 0.3s ease;
}

.preview-table-header {
  display: flex;
  background-color: var(--bg-color-soft);
  font-weight: 600;
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 2;
  transition: background-color 0.3s;
}

.preview-table-row {
  display: flex;
  border-bottom: 1px solid var(--border-color-lighter);
  transition: all 0.3s ease;
}

.preview-table-row:last-child {
  border-bottom: none;
}

.preview-table-row.preview-hover,
.preview-table-row:hover {
  background-color: var(--button-default-hover-bg-color);
  transform: translateZ(0);
}

.preview-table-cell {
  flex: 1;
  padding: 10px 15px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  text-align: left;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}

.preview-table-cell .preview-link {
  cursor: pointer;
  color: var(--link-color);
  position: relative;
  display: inline-block;
  transition: all 0.2s ease;
}

.preview-table-cell .preview-link:hover {
  color: var(--primary-color);
  text-decoration: none;
}

.preview-table-cell .preview-link:hover::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: var(--primary-color);
  opacity: 0.8;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

<template>
  <div class="admin-layout">
    <el-container class="layout-container">
      <!-- 侧边栏 -->
      <el-aside class="aside" :class="{ collapsed: isCollapse }">
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
          </el-sub-menu>

          <!-- 友情链接管理菜单 -->
          <el-menu-item index="/admin/link">
            <el-icon><Link /></el-icon>
            <span>{{ t('layout.friendLinkManagement') }}</span>
          </el-menu-item>

          <el-sub-menu index="4">
            <template #title>
              <el-icon><Setting /></el-icon>
              <span>{{ t('layout.systemSettings') }}</span>
            </template>
            <el-menu-item index="/admin/settings">{{ t('layout.websiteSettings') }}</el-menu-item>
            <el-menu-item index="/admin/activity-log">{{ t('layout.activityLog') }}</el-menu-item>
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
            <div class="theme-switch">
              <el-dropdown trigger="click" @command="handleThemeChange">
                <el-button class="theme-button" :icon="themeIcon" circle />
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="light">
                      <el-icon><Sunny /></el-icon>
                      <span>{{ t('layout.lightMode') }}</span>
                    </el-dropdown-item>
                    <el-dropdown-item command="dark">
                      <el-icon><Moon /></el-icon>
                      <span>{{ t('layout.darkMode') }}</span>
                    </el-dropdown-item>
                    <el-dropdown-item command="system">
                      <el-icon><Monitor /></el-icon>
                      <span>{{ t('layout.systemMode') }}</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>

            <!-- 添加自定义配色方案按钮 -->
            <div class="theme-color-switch">
              <el-button class="theme-color-button" @click="showColorThemePanel = true" circle>
                <el-icon><Brush /></el-icon>
              </el-button>
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

    <!-- 自定义配色面板 -->
    <el-drawer
      v-model="showColorThemePanel"
      title="自定义配色方案"
      size="400px"
      direction="rtl"
      :with-header="true"
      :close-on-click-modal="true"
      :before-close="closeColorPanel"
    >
      <div class="color-theme-container">
        <div class="theme-section">
          <h3>预设配色方案</h3>
          <el-tabs v-model="themeDisplayMode" type="card">
            <el-tab-pane label="全部主题" name="all">
              <el-radio-group v-model="currentColorTheme" class="preset-themes">
                <div
                  v-for="(theme, index) in filteredPresetThemes"
                  :key="index"
                  class="preset-theme-item"
                  :class="{
                    'active': currentColorTheme === theme.name,
                    'dark-variant': theme.mode === 'dark'
                  }"
                >
                  <el-radio
                    :label="theme.name"
                    @change="() => applyPresetTheme(theme)"
                    class="theme-radio"
                  >
                    <div class="theme-preview" :class="{ 'dark-preview': theme.mode === 'dark' }">
                      <div class="preview-header" :style="{ backgroundColor: theme.colors.primary }"></div>
                      <div class="preview-body" :style="{ backgroundColor: theme.colors.background }">
                        <div class="preview-button" :style="{ backgroundColor: theme.colors.buttonBg, color: theme.colors.buttonText }"></div>
                        <div class="preview-text" :style="{ color: theme.colors.textPrimary }"></div>
                        <div class="preview-link" :style="{ color: theme.colors.link }"></div>
                      </div>
                    </div>
                    <div class="theme-label-container">
                      <span class="theme-label">{{ theme.label }}</span>
                      <span v-if="theme.mode === 'dark'" class="theme-mode-badge">暗黑</span>
                      <span v-else class="theme-mode-badge light">亮色</span>
                    </div>
                  </el-radio>
                </div>
              </el-radio-group>
            </el-tab-pane>
            <el-tab-pane label="亮色主题" name="light">
              <el-radio-group v-model="currentColorTheme" class="preset-themes">
                <div
                  v-for="(theme, index) in filteredPresetThemes"
                  :key="index"
                  class="preset-theme-item"
                  :class="{ 'active': currentColorTheme === theme.name }"
                >
                  <el-radio
                    :label="theme.name"
                    @change="() => applyPresetTheme(theme)"
                    class="theme-radio"
                  >
                    <div class="theme-preview">
                      <div class="preview-header" :style="{ backgroundColor: theme.colors.primary }"></div>
                      <div class="preview-body" :style="{ backgroundColor: theme.colors.background }">
                        <div class="preview-button" :style="{ backgroundColor: theme.colors.buttonBg, color: theme.colors.buttonText }"></div>
                        <div class="preview-text" :style="{ color: theme.colors.textPrimary }"></div>
                        <div class="preview-link" :style="{ color: theme.colors.link }"></div>
                      </div>
                    </div>
                    <div class="theme-label-container">
                      <span class="theme-label">{{ theme.label }}</span>
                    </div>
                  </el-radio>
                </div>
              </el-radio-group>
            </el-tab-pane>
            <el-tab-pane label="暗黑主题" name="dark">
              <el-radio-group v-model="currentColorTheme" class="preset-themes">
                <div
                  v-for="(theme, index) in filteredPresetThemes"
                  :key="index"
                  class="preset-theme-item dark-variant"
                  :class="{ 'active': currentColorTheme === theme.name }"
                >
                  <el-radio
                    :label="theme.name"
                    @change="() => applyPresetTheme(theme)"
                    class="theme-radio"
                  >
                    <div class="theme-preview dark-preview">
                      <div class="preview-header" :style="{ backgroundColor: theme.colors.primary }"></div>
                      <div class="preview-body" :style="{ backgroundColor: theme.colors.background }">
                        <div class="preview-button" :style="{ backgroundColor: theme.colors.buttonBg, color: theme.colors.buttonText }"></div>
                        <div class="preview-text" :style="{ color: theme.colors.textPrimary }"></div>
                        <div class="preview-link" :style="{ color: theme.colors.link }"></div>
                      </div>
                    </div>
                    <div class="theme-label-container">
                      <span class="theme-label">{{ theme.label }}</span>
                    </div>
                  </el-radio>
                </div>
              </el-radio-group>
            </el-tab-pane>
          </el-tabs>
        </div>

        <div class="theme-section">
          <h3>自定义配色</h3>

          <!-- 添加实时预览区域 -->
          <div class="live-preview">
            <div class="preview-title">实时预览</div>
            <div class="preview-card" :style="{ backgroundColor: customColors.background }">
              <div class="preview-card-header" :style="{ backgroundColor: customColors.primary }">
                <span class="preview-card-title" :style="{ color: customColors.buttonText }">标题</span>
              </div>
              <div class="preview-card-body">
                <p :style="{ color: customColors.textPrimary }">主要文本内容</p>
                <p :style="{ color: customColors.textRegular }">常规文本内容</p>
                <p :style="{ color: customColors.textSecondary }">次要文本内容</p>
                <div class="preview-buttons">
                  <button class="preview-btn primary" :style="{ backgroundColor: customColors.buttonBg, color: customColors.buttonText }">主要按钮</button>
                  <button class="preview-btn default" :style="{ backgroundColor: customColors.defaultButtonBg, color: customColors.defaultButtonText }">默认按钮</button>
                </div>
                <div class="preview-buttons hover-demo mt-2">
                  <button class="preview-btn primary-hover" :style="{ backgroundColor: customColors.buttonHoverBg, color: customColors.buttonText, borderColor: customColors.buttonHoverBg }">悬停效果</button>
                  <button class="preview-btn default-hover" :style="{ borderColor: customColors.primary, color: customColors.primary, backgroundColor: customColors.buttonDefaultHoverBg }">默认悬停</button>
                </div>

                <!-- 表格预览 -->
                <div class="preview-table-container mt-4">
                  <div class="preview-table" :style="{ borderColor: customColors.borderColor }">
                    <div class="preview-table-header" :style="{ backgroundColor: customColors.bgColorSoft, color: customColors.textPrimary, borderColor: customColors.borderColor }">
                      <div class="preview-table-cell">ID</div>
                      <div class="preview-table-cell">姓名</div>
                      <div class="preview-table-cell">操作</div>
                    </div>
                    <div class="preview-table-row" :style="{ borderColor: customColors.borderColorLighter }">
                      <div class="preview-table-cell" :style="{ color: customColors.tableTextColor }">1</div>
                      <div class="preview-table-cell" :style="{ color: customColors.tableTextColor }">张三</div>
                      <div class="preview-table-cell">
                        <span class="preview-link" :style="{ color: customColors.link }">编辑</span>
                      </div>
                    </div>
                    <div class="preview-table-row preview-hover" :style="{ backgroundColor: customColors.buttonDefaultHoverBg, borderColor: customColors.borderColorLighter }">
                      <div class="preview-table-cell" :style="{ color: customColors.tableTextColor }">2</div>
                      <div class="preview-table-cell" :style="{ color: customColors.tableTextColor }">李四</div>
                      <div class="preview-table-cell">
                        <span class="preview-link" :style="{ color: customColors.link }">编辑</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="preview-link-text">
                  <a href="javascript:;" :style="{ color: customColors.link }">链接</a>
                </div>
                <div class="preview-icon" :style="{ color: customColors.iconColor }">
                  <i class="el-icon">
                    <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-7122a6e2=""><path fill="currentColor" d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm0 64a384 384 0 1 0 0 768 384 384 0 0 0 0-768zm0 64a320 320 0 1 1 0 640 320 320 0 0 1 0-640zm0 64a256 256 0 1 0 0 512 256 256 0 0 0 0-512zm0 64a192 192 0 1 1 0 384 192 192 0 0 1 0-384zm0 64a128 128 0 1 0 0 256 128 128 0 0 0 0-256zm0 64a64 64 0 1 1 0 128 64 64 0 0 1 0-128z"></path></svg>
                  </i>
                </div>
              </div>
            </div>
          </div>

          <el-divider />

          <el-form label-position="top" :model="customColors">
            <el-tabs v-model="activeColorTab" class="color-tabs">
              <el-tab-pane label="基础配色" name="1">
                <div class="color-settings-container">
                  <el-form-item label="主题色">
                    <el-color-picker v-model="customColors.primary" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.primary" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="背景色">
                    <el-color-picker v-model="customColors.background" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.background" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="背景色(柔和)">
                    <el-color-picker v-model="customColors.bgColorSoft" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.bgColorSoft" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="背景色(浅色)">
                    <el-color-picker v-model="customColors.bgColorMute" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.bgColorMute" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="主要文字色">
                    <el-color-picker v-model="customColors.textPrimary" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.textPrimary" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="常规文字色">
                    <el-color-picker v-model="customColors.textRegular" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.textRegular" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="次要文字色">
                    <el-color-picker v-model="customColors.textSecondary" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.textSecondary" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="表格文字色">
                    <el-color-picker v-model="customColors.tableTextColor" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.tableTextColor" @change="applyCustomColors" />
                  </el-form-item>
                </div>
              </el-tab-pane>

              <el-tab-pane label="按钮样式" name="button">
                <div class="color-settings-container">
                  <el-form-item label="主要按钮背景色">
                    <el-color-picker v-model="customColors.buttonBg" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.buttonBg" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="主要按钮文字颜色">
                    <el-color-picker v-model="customColors.buttonText" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.buttonText" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="主要按钮悬停颜色">
                    <el-color-picker v-model="customColors.buttonHoverBg" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.buttonHoverBg" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="默认按钮背景色">
                    <el-color-picker v-model="customColors.defaultButtonBg" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.defaultButtonBg" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="默认按钮文字颜色">
                    <el-color-picker v-model="customColors.defaultButtonText" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.defaultButtonText" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="默认按钮悬停背景">
                    <el-color-picker v-model="customColors.buttonDefaultHoverBg" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.buttonDefaultHoverBg" @change="applyCustomColors" />
                  </el-form-item>
                </div>
              </el-tab-pane>

              <el-tab-pane label="链接和边框" name="misc">
                <div class="color-settings-container">
                  <el-form-item label="链接颜色">
                    <el-color-picker v-model="customColors.link" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.link" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="链接悬停颜色">
                    <el-color-picker v-model="customColors.linkHover" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.linkHover" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="图标颜色">
                    <el-color-picker v-model="customColors.iconColor" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.iconColor" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="边框颜色">
                    <el-color-picker v-model="customColors.borderColor" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.borderColor" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="边框颜色(浅色)">
                    <el-color-picker v-model="customColors.borderColorLight" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.borderColorLight" @change="applyCustomColors" />
                  </el-form-item>

                  <el-form-item label="边框颜色(更浅)">
                    <el-color-picker v-model="customColors.borderColorLighter" show-alpha @change="applyCustomColors" />
                    <el-input v-model="customColors.borderColorLighter" @change="applyCustomColors" />
                  </el-form-item>
                </div>
              </el-tab-pane>
            </el-tabs>
          </el-form>

          <div class="theme-actions">
            <el-button type="primary" @click="openSaveThemeDialog">保存配色方案</el-button>
            <el-button @click="resetColors">重置</el-button>
            <el-button type="danger" v-if="showDeleteButton" @click="deleteCustomTheme">删除</el-button>
          </div>
        </div>
      </div>
    </el-drawer>

    <!-- 保存主题对话框 -->
    <el-dialog
      v-model="showSaveThemeDialog"
      title="保存配色方案"
      width="30%"
      :close-on-click-modal="false"
    >
      <el-form :model="saveThemeForm" label-position="top">
        <el-form-item label="配色方案名称" required>
          <el-input v-model="saveThemeForm.name" placeholder="请输入配色方案名称"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="showSaveThemeDialog = false">取消</el-button>
          <el-button type="primary" @click="confirmSaveTheme">保存</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Monitor, User, Setting, Fold, Expand, Document, Sunny, Moon, Link, Brush } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useThemeStore } from '@/stores/theme'
import { useLanguageStore } from '@/stores/language'
import { useI18n } from 'vue-i18n'
import Footer from '@/components/common/Footer.vue'
import Breadcrumb from '@/components/layout/Breadcrumb.vue'
import adminApi, { apiUrls } from '@/api/admin'

const router = useRouter()
const route = useRoute()
const themeStore = useThemeStore()
const languageStore = useLanguageStore()
const { t, locale } = useI18n()

// 管理员信息
const adminInfo = ref({
  username: 'Admin',
  avatar: ''
})

// 获取管理员信息
const getAdminInfo = async () => {
  try {
    const response = await adminApi.post(apiUrls.ADMIN_API.INFO)
    if (response.code === 200 && response.data) {
      // 保存到本地存储
      localStorage.setItem('admin_info', JSON.stringify(response.data))
    }
  } catch (error) {
    console.error('获取管理员信息失败:', error)
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

// 侧边栏折叠状态
const isCollapse = ref(false)

// 从本地存储恢复折叠状态和管理员信息
onMounted(() => {
  // 读取本地存储中的折叠状态
  try {
    const savedCollapseState = localStorage.getItem('sidebarCollapse')
    if (savedCollapseState !== null) {
      isCollapse.value = savedCollapseState === 'true'
    }
  } catch (error) {
    console.error('读取侧边栏状态失败:', error)
    // 默认设置为展开状态
    isCollapse.value = false
  }

  // 加载管理员信息
  loadAdminInfo()
  // 获取最新的管理员信息
  getAdminInfo()
  // 初始化主题
  themeStore.initialize()
  // 初始化语言
  languageStore.initLanguage()
  initColorTheme()
})

// 组件卸载时清理主题监听器
onBeforeUnmount(() => {
  themeStore.cleanup()
})

// 保存折叠状态到本地存储
const saveCollapseState = () => {
  try {
    localStorage.setItem('sidebarCollapse', String(isCollapse.value))
  } catch (error) {
    console.error('保存侧边栏状态失败:', error)
  }
}

// 当前激活的菜单
const activeMenu = computed(() => {
  return route.path
})

// 切换侧边栏折叠状态
const toggleSidebar = () => {
  isCollapse.value = !isCollapse.value
  saveCollapseState()
}

// 退出登录
const handleLogout = async () => {
  try {
    const response = await adminApi.post(apiUrls.ADMIN_API.LOGOUT)
    if (response.code === 200) {
      // 清除管理员token和信息
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
      // 跳转到管理员登录页
      router.push('/admin/login')
      ElMessage.success(t('layout.logoutSuccess'))
    } else {
      ElMessage.error(response.msg || t('layout.logoutFailed'))
    }
  } catch (error) {
    console.error('退出登录失败:', error)
    ElMessage.error(t('layout.logoutFailed'))
    // 即使请求失败也清除本地信息并跳转
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_info')
    router.push('/admin/login')
  }
}

// 主题图标
const themeIcon = computed(() => {
  if (themeStore.theme === 'system') {
    return Monitor
  } else if (themeStore.theme === 'dark') {
    return Moon
  } else {
    return Sunny
  }
})

// 处理主题切换
const handleThemeChange = (theme) => {

  // 保存侧边栏的当前宽度
  const aside = document.querySelector('.aside')
  const asideWidth = aside ? aside.offsetWidth : (isCollapse.value ? '64px' : '220px')
  const asideStyle = aside ? window.getComputedStyle(aside) : {}

  // 设置主题状态前先获取所有内容区域
  const mainContent = document.querySelector('.main-content')
  const cards = document.querySelectorAll('.el-card')
  const tables = document.querySelectorAll('.el-table')
  const forms = document.querySelectorAll('.el-form')

  // 立即应用临时样式，使用CSS变量
  if (theme === 'light') {
    // 浅色模式
    if (mainContent) mainContent.style.setProperty('--immediate-bg-color', '#f5f7fa')
    cards.forEach(card => card.style.setProperty('--immediate-bg-color', '#ffffff'))
    tables.forEach(table => table.style.setProperty('--immediate-bg-color', '#ffffff'))
  } else if (theme === 'dark') {
    // 深色模式
    if (mainContent) mainContent.style.setProperty('--immediate-bg-color', '#232324')
    cards.forEach(card => card.style.setProperty('--immediate-bg-color', '#232324'))
    tables.forEach(table => table.style.setProperty('--immediate-bg-color', '#232324'))
  } else if (theme === 'system') {
    // 系统模式，根据系统偏好设置
    const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    const bgColor = isSystemDark ? '#141414' : '#f5f7fa'
    const cardBgColor = isSystemDark ? '#141414' : '#ffffff'

    if (mainContent) mainContent.style.setProperty('--immediate-bg-color', bgColor)
    cards.forEach(card => card.style.setProperty('--immediate-bg-color', cardBgColor))
    tables.forEach(table => table.style.setProperty('--immediate-bg-color', cardBgColor))
  }

  // 设置主题状态
  themeStore.setTheme(theme)

  // 强制组件刷新以立即反映菜单颜色变化
  nextTick(() => {
    // 刷新菜单样式
    const menuEl = document.querySelector('.el-menu')
    if (menuEl) {
      menuEl.classList.add('theme-menu-refresh')
      setTimeout(() => {
        menuEl.classList.remove('theme-menu-refresh')
      }, 10)
    }

    // 50ms后移除临时样式
    setTimeout(() => {
      if (mainContent) mainContent.style.removeProperty('--immediate-bg-color')
      cards.forEach(card => card.style.removeProperty('--immediate-bg-color'))
      tables.forEach(table => table.style.removeProperty('--immediate-bg-color'))
    }, 50)
  })
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
}

// 菜单背景颜色
const menuBackgroundColor = computed(() => {
  if (themeStore.theme === 'system' && themeStore.isSystemDarkMode) {
    // 系统暗色模式
    return '#141414'
  } else if (themeStore.theme === 'system' && !themeStore.isSystemDarkMode) {
    // 系统亮色模式
    return '#ffffff'
  } else if (themeStore.theme === 'dark') {
    // 手动暗色模式
    return '#232324'
  } else {
    // 手动亮色模式
    return '#ffffff'
  }
})

// 菜单文本颜色
const menuTextColor = computed(() => {
  if (themeStore.theme === 'system' && themeStore.isSystemDarkMode) {
    // 系统暗色模式
    return '#e0e0e0'
  } else if (themeStore.theme === 'system' && !themeStore.isSystemDarkMode) {
    // 系统亮色模式
    return '#606266'
  } else if (themeStore.theme === 'dark') {
    // 手动暗色模式
    return '#e0e0e0'
  } else {
    // 手动亮色模式
    return '#606266'
  }
})

// 菜单激活文本颜色
const menuActiveTextColor = computed(() => {
  if (themeStore.theme === 'dark') {
    return customColors.value.primary || '#409eff'
  } else {
    return customColors.value.primary || '#409eff'
  }
})

// 自定义配色系统
const showColorThemePanel = ref(false)
const currentColorTheme = ref('default')
const showSaveThemeDialog = ref(false)
const saveThemeForm = ref({
  name: ''
})
const showDeleteButton = ref(false)
const activeColorTab = ref('1')

// 监听面板打开状态，确保每次打开时都显示基础配色标签页
watch(showColorThemePanel, (newVal) => {
  if (newVal === true) {
    // 当面板打开时重置为基础配色标签页
    activeColorTab.value = '1'
  }
})

// 自定义保存的主题列表
const customSavedThemes = ref([])

// 预设配色方案
const presetThemes = ref([
  {
    name: 'default',
    label: '默认配色',
    mode: 'light',
    colors: {
      primary: '#409EFF',
      background: '#ffffff',
      bgColorSoft: '#f5f7fa',
      bgColorMute: '#f0f2f5',
      textPrimary: '#303133',
      textRegular: '#606266',
      textSecondary: '#909399',
      buttonBg: '#409EFF',
      buttonText: '#ffffff',
      buttonHoverBg: '#66b1ff',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#606266',
      buttonDefaultHoverBg: '#ecf5ff',
      link: '#409EFF',
      linkHover: '#79bbff',
      iconColor: '#409EFF',
      borderColor: '#dcdfe6',
      borderColorLight: '#e4e7ed',
      borderColorLighter: '#ebeef5',
      tableTextColor: '#606266'
    }
  },
  {
    name: 'default_dark',
    label: '默认配色(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#409EFF',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#409EFF',
      buttonText: '#ffffff',
      buttonHoverBg: '#66b1ff',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#409EFF',
      linkHover: '#79bbff',
      iconColor: '#409EFF',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  },
  {
    name: 'forest',
    label: '森林绿',
    mode: 'light',
    colors: {
      primary: '#2C8D46',
      background: '#f2f7f4',
      bgColorSoft: '#e8f5ee',
      bgColorMute: '#dff0e5',
      textPrimary: '#2c3e50',
      textRegular: '#34495e',
      textSecondary: '#7f8c8d',
      buttonBg: '#2C8D46',
      buttonText: '#ffffff',
      buttonHoverBg: '#48a162',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#34495e',
      buttonDefaultHoverBg: '#e8f5ee',
      link: '#2C8D46',
      linkHover: '#4fb06b',
      iconColor: '#2C8D46',
      borderColor: '#c2e1cd',
      borderColorLight: '#d7ebdf',
      borderColorLighter: '#e8f5ee',
      tableTextColor: '#34495e'
    }
  },
  {
    name: 'forest_dark',
    label: '森林绿(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#2C8D46',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#2C8D46',
      buttonText: '#ffffff',
      buttonHoverBg: '#48a162',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#2C8D46',
      linkHover: '#4fb06b',
      iconColor: '#2C8D46',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  },
  {
    name: 'ocean',
    label: '海洋蓝',
    mode: 'light',
    colors: {
      primary: '#1976D2',
      background: '#f2f7fa',
      bgColorSoft: '#e3f2fd',
      bgColorMute: '#bbdefb',
      textPrimary: '#263238',
      textRegular: '#37474f',
      textSecondary: '#78909c',
      buttonBg: '#1976D2',
      buttonText: '#ffffff',
      buttonHoverBg: '#4791db',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#37474f',
      buttonDefaultHoverBg: '#e3f2fd',
      link: '#1976D2',
      linkHover: '#4a95e5',
      iconColor: '#1976D2',
      borderColor: '#90caf9',
      borderColorLight: '#bbdefb',
      borderColorLighter: '#e3f2fd',
      tableTextColor: '#37474f'
    }
  },
  {
    name: 'ocean_dark',
    label: '海洋蓝(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#1976D2',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#1976D2',
      buttonText: '#ffffff',
      buttonHoverBg: '#4791db',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#1976D2',
      linkHover: '#4a95e5',
      iconColor: '#1976D2',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  },
  {
    name: 'sunset',
    label: '夕阳红',
    mode: 'light',
    colors: {
      primary: '#E65100',
      background: '#fff8f2',
      bgColorSoft: '#fff3e0',
      bgColorMute: '#ffe0b2',
      textPrimary: '#3e2723',
      textRegular: '#4e342e',
      textSecondary: '#8d6e63',
      buttonBg: '#E65100',
      buttonText: '#ffffff',
      buttonHoverBg: '#f57c00',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#4e342e',
      buttonDefaultHoverBg: '#fff3e0',
      link: '#E65100',
      linkHover: '#ff7a33',
      iconColor: '#E65100',
      borderColor: '#ffcc80',
      borderColorLight: '#ffe0b2',
      borderColorLighter: '#fff3e0',
      tableTextColor: '#4e342e'
    }
  },
  {
    name: 'sunset_dark',
    label: '夕阳红(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#E65100',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#E65100',
      buttonText: '#ffffff',
      buttonHoverBg: '#f57c00',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#E65100',
      linkHover: '#ff7a33',
      iconColor: '#E65100',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  },
  {
    name: 'lavender',
    label: '薰衣草',
    mode: 'light',
    colors: {
      primary: '#7E57C2',
      background: '#f7f4fb',
      bgColorSoft: '#f3e5f5',
      bgColorMute: '#e1bee7',
      textPrimary: '#4a148c',
      textRegular: '#6a1b9a',
      textSecondary: '#9c27b0',
      buttonBg: '#7E57C2',
      buttonText: '#ffffff',
      buttonHoverBg: '#9575cd',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#6a1b9a',
      buttonDefaultHoverBg: '#f3e5f5',
      link: '#7E57C2',
      linkHover: '#9f80d7',
      iconColor: '#7E57C2',
      borderColor: '#ce93d8',
      borderColorLight: '#e1bee7',
      borderColorLighter: '#f3e5f5',
      tableTextColor: '#6a1b9a'
    }
  },
  {
    name: 'lavender_dark',
    label: '薰衣草(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#7E57C2',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#7E57C2',
      buttonText: '#ffffff',
      buttonHoverBg: '#9575cd',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#7E57C2',
      linkHover: '#9f80d7',
      iconColor: '#7E57C2',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  },
  {
    name: 'gray',
    label: '商务灰',
    mode: 'light',
    colors: {
      primary: '#607D8B',
      background: '#f5f7f8',
      bgColorSoft: '#eceff1',
      bgColorMute: '#cfd8dc',
      textPrimary: '#263238',
      textRegular: '#37474f',
      textSecondary: '#607d8b',
      buttonBg: '#607D8B',
      buttonText: '#ffffff',
      buttonHoverBg: '#78909c',
      defaultButtonBg: '#ffffff',
      defaultButtonText: '#37474f',
      buttonDefaultHoverBg: '#eceff1',
      link: '#607D8B',
      linkHover: '#8eacbb',
      iconColor: '#607D8B',
      borderColor: '#b0bec5',
      borderColorLight: '#cfd8dc',
      borderColorLighter: '#eceff1',
      tableTextColor: '#37474f'
    }
  },
  {
    name: 'gray_dark',
    label: '商务灰(暗黑)',
    mode: 'dark',
    colors: {
      primary: '#607D8B',
      background: '#232324',
      bgColorSoft: '#2a2a2b',
      bgColorMute: '#303030',
      textPrimary: '#e0e0e0',
      textRegular: '#c0c0c0',
      textSecondary: '#a0a0a0',
      buttonBg: '#607D8B',
      buttonText: '#ffffff',
      buttonHoverBg: '#78909c',
      defaultButtonBg: '#2a2a2b',
      defaultButtonText: '#c0c0c0',
      buttonDefaultHoverBg: '#383838',
      link: '#607D8B',
      linkHover: '#8eacbb',
      iconColor: '#607D8B',
      borderColor: '#3e3e3f',
      borderColorLight: '#484849',
      borderColorLighter: '#505050',
      tableTextColor: '#c0c0c0'
    }
  }
])

// 当前自定义颜色
const customColors = ref({
  primary: '#409EFF',
  background: '#ffffff',
  bgColorSoft: '#f5f7fa',
  bgColorMute: '#f0f2f5',
  textPrimary: '#303133',
  textRegular: '#606266',
  textSecondary: '#909399',
  buttonBg: '#409EFF',
  buttonText: '#ffffff',
  buttonHoverBg: '#66b1ff',
  defaultButtonBg: '#ffffff',
  defaultButtonText: '#606266',
  buttonDefaultHoverBg: '#ecf5ff',
  link: '#409EFF',
  linkHover: '#79bbff',
  iconColor: '#409EFF',
  borderColor: '#dcdfe6',
  borderColorLight: '#e4e7ed',
  borderColorLighter: '#ebeef5',
  tableTextColor: '#606266'
})

// 关闭配色面板
const closeColorPanel = () => {
  showColorThemePanel.value = false
}

// 打开保存主题对话框
const openSaveThemeDialog = () => {
  saveThemeForm.value.name = ''
  showSaveThemeDialog.value = true
}

// 确认保存主题
const confirmSaveTheme = () => {
  if (!saveThemeForm.value.name.trim()) {
    ElMessage.warning('请输入配色方案名称')
    return
  }

  // 创建新的自定义主题
  const newTheme = {
    name: 'custom_' + Date.now(),
    label: saveThemeForm.value.name,
    colors: { ...customColors.value }
  }

  // 添加到自定义主题列表
  const savedThemes = JSON.parse(localStorage.getItem('custom_saved_themes') || '[]')
  savedThemes.push(newTheme)
  localStorage.setItem('custom_saved_themes', JSON.stringify(savedThemes))

  // 更新当前主题状态
  customSavedThemes.value = savedThemes
  currentColorTheme.value = newTheme.name

  // 设置当前为自定义主题
  localStorage.setItem('color_theme', newTheme.name)

  // 显示删除按钮
  showDeleteButton.value = true

  // 关闭对话框
  showSaveThemeDialog.value = false

  ElMessage.success(`配色方案"${saveThemeForm.value.name}"已保存`)

  // 更新预设主题列表以包含自定义主题
  loadCustomThemes()
}

// 删除自定义主题
const deleteCustomTheme = () => {
  if (!currentColorTheme.value.startsWith('custom_')) {
    return
  }

  // 从存储中移除
  const savedThemes = JSON.parse(localStorage.getItem('custom_saved_themes') || '[]')
  const updatedThemes = savedThemes.filter(theme => theme.name !== currentColorTheme.value)
  localStorage.setItem('custom_saved_themes', JSON.stringify(updatedThemes))

  // 更新自定义主题列表
  customSavedThemes.value = updatedThemes

  // 重置为默认主题
  resetColors()

  // 隐藏删除按钮
  showDeleteButton.value = false

  ElMessage.success('自定义配色方案已删除')

  // 更新预设主题列表
  loadCustomThemes()
}

// 加载自定义主题
const loadCustomThemes = () => {
  const savedThemes = JSON.parse(localStorage.getItem('custom_saved_themes') || '[]')
  customSavedThemes.value = savedThemes

  // 创建一个新的预设主题数组，包含默认预设和自定义主题
  const defaultPresets = presetThemes.value.filter(theme => !theme.name.startsWith('custom_'))
  presetThemes.value = [...defaultPresets, ...savedThemes]
}

// 应用预设主题
const applyPresetTheme = (theme) => {
  // 设置当前主题（这会自动更新radio选择）
  currentColorTheme.value = theme.name

  // 应用颜色
  customColors.value = { ...theme.colors }
  applyCustomColors()

  // 记录当前选择的主题模式
  localStorage.setItem('color_theme_mode', theme.mode || 'light')

  // 如果是自定义主题，显示删除按钮
  showDeleteButton.value = theme.name.startsWith('custom_')

  ElMessage.success(`已应用"${theme.label}"配色方案`)
}

// 获取当前活动主题
const getCurrentTheme = () => {
  const savedThemeName = currentColorTheme.value
  const isDarkMode = themeStore.theme === 'dark' ||
                    (themeStore.theme === 'system' && themeStore.isSystemDarkMode)

  // 尝试直接查找保存的主题名称
  const directMatch = presetThemes.value.find(t => t.name === savedThemeName)
  if (directMatch) {
    // 检查主题模式是否与当前系统/应用模式匹配
    if ((isDarkMode && directMatch.mode === 'dark') ||
        (!isDarkMode && directMatch.mode !== 'dark')) {
      return directMatch
    }
  }

  // 如果没有找到匹配或者模式不匹配，尝试找出对应的主题变体
  if (isDarkMode) {
    // 查找对应的暗黑变体
    if (savedThemeName.endsWith('_dark')) {
      return directMatch // 已经是暗黑模式
    } else {
      // 尝试查找对应的暗黑版本
      const darkVariant = presetThemes.value.find(t => t.name === `${savedThemeName}_dark`)
      if (darkVariant) return darkVariant
    }
  } else {
    // 查找对应的亮色变体
    if (!savedThemeName.endsWith('_dark')) {
      return directMatch // 已经是亮色模式
    } else {
      // 尝试查找对应的亮色版本
      const lightName = savedThemeName.replace('_dark', '')
      const lightVariant = presetThemes.value.find(t => t.name === lightName)
      if (lightVariant) return lightVariant
    }
  }

  // 如果没有找到对应的变体，返回与当前模式匹配的默认主题
  return presetThemes.value.find(t =>
    t.name === (isDarkMode ? 'default_dark' : 'default')
  ) || presetThemes.value[0]
}

// 监听主题模式变化，自动切换颜色方案
watch(() => themeStore.theme, (newTheme) => {
  const isDarkMode = newTheme === 'dark' ||
                    (newTheme === 'system' && themeStore.isSystemDarkMode)

  // 只在颜色主题面板关闭时自动切换
  if (!showColorThemePanel.value) {
    const currentTheme = getCurrentTheme()
    if (currentTheme) {
      customColors.value = { ...currentTheme.colors }
      applyCustomColors()
    }
  }
})

// 监听系统暗黑模式状态变化
watch(() => themeStore.isSystemDarkMode, (newIsDark) => {
  // 只在系统模式且颜色主题面板关闭时自动切换
  if (themeStore.theme === 'system' && !showColorThemePanel.value) {
    const currentTheme = getCurrentTheme()
    if (currentTheme) {
      customColors.value = { ...currentTheme.colors }
      applyCustomColors()
    }
  }
})

// 应用自定义颜色
const applyCustomColors = () => {
  const root = document.documentElement

  // 主色调
  root.style.setProperty('--el-color-primary', customColors.value.primary)
  root.style.setProperty('--primary-color', customColors.value.primary)

  // 背景色
  root.style.setProperty('--bg-color', customColors.value.background)
  root.style.setProperty('--el-bg-color', customColors.value.background)
  root.style.setProperty('--el-bg-color-page', customColors.value.background)
  root.style.setProperty('--bg-color-soft', customColors.value.bgColorSoft)
  root.style.setProperty('--bg-color-mute', customColors.value.bgColorMute)

  // 文本颜色
  root.style.setProperty('--text-color-primary', customColors.value.textPrimary)
  root.style.setProperty('--el-text-color-primary', customColors.value.textPrimary)
  root.style.setProperty('--text-color-regular', customColors.value.textRegular)
  root.style.setProperty('--el-text-color-regular', customColors.value.textRegular)
  root.style.setProperty('--text-color-secondary', customColors.value.textSecondary)
  root.style.setProperty('--el-text-color-secondary', customColors.value.textSecondary)

  // 按钮颜色
  root.style.setProperty('--button-bg-color', customColors.value.buttonBg)
  root.style.setProperty('--el-button-bg-color', customColors.value.buttonBg)
  root.style.setProperty('--button-text-color', customColors.value.buttonText)
  root.style.setProperty('--el-button-text-color', customColors.value.buttonText)
  root.style.setProperty('--button-hover-bg-color', customColors.value.buttonHoverBg)
  root.style.setProperty('--el-button-hover-bg-color', customColors.value.buttonHoverBg)
  root.style.setProperty('--button-default-bg-color', customColors.value.defaultButtonBg)
  root.style.setProperty('--el-button-default-bg-color', customColors.value.defaultButtonBg)
  root.style.setProperty('--button-default-text-color', customColors.value.defaultButtonText)
  root.style.setProperty('--el-button-default-text-color', customColors.value.defaultButtonText)
  root.style.setProperty('--button-default-hover-bg-color', customColors.value.buttonDefaultHoverBg)
  root.style.setProperty('--el-button-default-hover-bg-color', customColors.value.buttonDefaultHoverBg)

  // 表格文本颜色
  root.style.setProperty('--table-text-color', customColors.value.tableTextColor)
  root.style.setProperty('--el-table-text-color', customColors.value.tableTextColor)

  // 链接颜色
  root.style.setProperty('--link-color', customColors.value.link)
  root.style.setProperty('--el-link-text-color', customColors.value.link)
  root.style.setProperty('--link-hover-color', customColors.value.linkHover)
  root.style.setProperty('--el-link-hover-text-color', customColors.value.linkHover)

  // 图标颜色
  root.style.setProperty('--icon-color', customColors.value.iconColor)
  root.style.setProperty('--el-icon-color', customColors.value.iconColor)

  // 边框颜色
  root.style.setProperty('--border-color', customColors.value.borderColor)
  root.style.setProperty('--el-border-color', customColors.value.borderColor)
  root.style.setProperty('--border-color-light', customColors.value.borderColorLight)
  root.style.setProperty('--el-border-color-light', customColors.value.borderColorLight)
  root.style.setProperty('--border-color-lighter', customColors.value.borderColorLighter)
  root.style.setProperty('--el-border-color-lighter', customColors.value.borderColorLighter)

  // 调整主题颜色的各种明暗变体
  generatePrimaryVariants(customColors.value.primary)

  // 存储当前颜色到本地存储
  saveThemeToStorage()
}

// 保存主题到本地存储
const saveThemeToStorage = () => {
  localStorage.setItem('color_theme', currentColorTheme.value)
  localStorage.setItem('custom_colors', JSON.stringify(customColors.value))

  // 触发主题变更事件
  document.dispatchEvent(new CustomEvent('theme-colors-changed', {
    detail: { colors: customColors.value }
  }))
}

// 生成主色调的明暗变体
const generatePrimaryVariants = (primaryColor) => {
  // 使用 CSS 变量生成主色调的变体
  const root = document.documentElement

  // 转换主色调为 RGB 格式
  let r = 0, g = 0, b = 0
  if (primaryColor.startsWith('#')) {
    const hex = primaryColor.substring(1)
    r = parseInt(hex.substring(0, 2), 16)
    g = parseInt(hex.substring(2, 4), 16)
    b = parseInt(hex.substring(4, 6), 16)
  } else if (primaryColor.startsWith('rgb')) {
    const match = primaryColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/)
    if (match) {
      r = parseInt(match[1])
      g = parseInt(match[2])
      b = parseInt(match[3])
    }
  }

  // 生成 9 个不同透明度的变体
  for (let i = 1; i <= 9; i++) {
    const alpha = i / 10
    root.style.setProperty(`--el-color-primary-light-${i}`, `rgba(${r}, ${g}, ${b}, ${alpha})`)
  }

  // 设置主色调的 hover 和 active 状态颜色
  const darken = (color, percent) => {
    const f = parseInt(color, 16)
    const t = percent < 0 ? 0 : 255
    const p = percent < 0 ? percent * -1 : percent
    return (Math.round((t - f) * p) + f).toString(16).padStart(2, '0')
  }

  if (primaryColor.startsWith('#')) {
    const primary = primaryColor.substring(1)
    const hoverColor = `#${darken(primary.substring(0, 2), -0.1)}${darken(primary.substring(2, 4), -0.1)}${darken(primary.substring(4, 6), -0.1)}`
    const activeColor = `#${darken(primary.substring(0, 2), -0.2)}${darken(primary.substring(2, 4), -0.2)}${darken(primary.substring(4, 6), -0.2)}`

    root.style.setProperty('--el-color-primary-hover', hoverColor)
    root.style.setProperty('--el-color-primary-active', activeColor)
  }
}

// 重置为默认颜色
const resetColors = () => {
  // 根据当前主题模式选择适当的默认主题
  const isDarkMode = themeStore.theme === 'dark' ||
                    (themeStore.theme === 'system' && themeStore.isSystemDarkMode)

  const defaultThemeName = isDarkMode ? 'default_dark' : 'default'
  const defaultTheme = presetThemes.value.find(theme => theme.name === defaultThemeName)

  if (defaultTheme) {
    currentColorTheme.value = defaultThemeName
    customColors.value = { ...defaultTheme.colors }
    applyCustomColors()
    ElMessage.success('已重置为默认配色')
  }
}

// 初始化配色方案
const initColorTheme = () => {
  // 加载自定义保存的主题
  loadCustomThemes()

  // 从本地存储加载配色方案
  const savedTheme = localStorage.getItem('color_theme')
  const savedColors = localStorage.getItem('custom_colors')

  if (savedTheme) {
    currentColorTheme.value = savedTheme

    // 检查是否为自定义保存的主题
    if (savedTheme.startsWith('custom_')) {
      const customTheme = customSavedThemes.value.find(t => t.name === savedTheme)
      if (customTheme) {
        customColors.value = { ...customTheme.colors }
        showDeleteButton.value = true
        applyCustomColors()
        return
      }
    }

    if (savedTheme !== 'custom' && savedColors) {
      try {
        // 检查当前主题模式，选择对应的颜色方案
        const currentTheme = getCurrentTheme()
        if (currentTheme) {
          customColors.value = { ...currentTheme.colors }
        } else {
          customColors.value = JSON.parse(savedColors)
        }
        applyCustomColors()
      } catch (e) {
        console.error('解析保存的配色方案失败', e)
        resetColors()
      }
    } else {
      // 应用预设主题
      const currentTheme = getCurrentTheme()
      if (currentTheme) {
        customColors.value = { ...currentTheme.colors }
        applyCustomColors()
      } else {
        resetColors()
      }
    }
  } else {
    // 默认主题
    resetColors()
  }
}

// 过滤预设配色方案
const filteredPresetThemes = computed(() => {
  if (themeDisplayMode.value === 'all') {
    return presetThemes.value
  } else if (themeDisplayMode.value === 'light') {
    return presetThemes.value.filter(theme => theme.mode === 'light')
  } else if (themeDisplayMode.value === 'dark') {
    return presetThemes.value.filter(theme => theme.mode === 'dark')
  } else {
    return []
  }
})

// 主题显示模式
const themeDisplayMode = ref('all')


</script>

<style>
.theme-mode-selector {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  gap: 10px;
}

.theme-mode-selector span {
  font-size: 14px;
  color: var(--el-text-color-regular);
}

:deep(.el-radio-button__inner) {
  padding: 5px 12px;
  font-size: 12px;
}

.preset-themes {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
  width: 100%;
}


/* 设置logo-container的背景色，提取到非scoped样式中以确保全局应用 */
.admin-layout .logo-container {
  background-color: #ffffff !important;
  border-bottom: 1px solid #eee;
  border-right: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  padding: 0 10px;
  transition: all 0.3s;
  overflow: hidden;
}

/* 深色模式下的logo容器 */
html.dark-theme .admin-layout .logo-container {
  background-color: #232324 !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

html.system-dark-theme .admin-layout .logo-container,
html.dark.system-theme .admin-layout .logo-container {
  background-color: #141414 !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}
</style>

<style scoped>
.admin-layout {
  height: 100vh;
  overflow: hidden;
}

.layout-container {
  height: 100%;
}

.aside {
  background-color: #f0f2f5;
  transition: width 0.28s ease;
  overflow-x: hidden;
  overflow-y: auto;
  position: relative;
  will-change: width, background-color;
  box-sizing: border-box;
  position: sticky;
  left: 0;
  top: 0;
  z-index: 10;
  border-right: 1px solid #eee;
  width: 220px;  /* 添加默认宽度 */
}

/* 为侧边栏根据主题模式提供不同背景色 */
:deep(.dark-theme) .aside {
  background-color: #232324;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

:deep(.system-dark-theme) .aside,
:deep(html.dark.system-theme) .aside {
  background-color: #141414;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

/* 当折叠时 */
.aside.collapsed {
  width: 64px !important;
}

.logo {
  color: var(--logo-text-color, #fff);
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  transition: all 0.3s;
}

:deep(.light-theme) .logo,
:deep(.system-light-theme) .logo,
:deep(.light-theme) .logo-collapsed,
:deep(.system-light-theme) .logo-collapsed {
  --logo-text-color: var(--el-color-primary, #409EFF);
}

:deep(.dark-theme) .logo,
:deep(.dark-theme) .logo-collapsed {
  --logo-text-color: #fff;
}

:deep(.system-dark-theme) .logo,
:deep(.system-dark-theme) .logo-collapsed,
:deep(html.dark.system-theme) .logo,
:deep(html.dark.system-theme) .logo-collapsed {
  --logo-text-color: #fff;
}

.logo-collapsed {
  color: var(--logo-text-color, #fff);
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
  background-color: transparent;
  border-top: 1px solid var(--border-color-lighter);
  padding: 0 16px;
  z-index: 11;
}

.sidebar-collapse-btn:hover {
  color: var(--primary-color);
  background-color: var(--button-default-hover-bg-color);
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
:deep(.dark-theme) .header {
  background-color: #232324;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}

/* 系统暗黑模式头部导航 */
:deep(.system-dark-theme) .header,
:deep(html.dark.system-theme) .header {
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
:deep(.dark-theme) .username {
  color: #e0e0e0;
}

/* 系统暗黑模式用户名颜色 - 添加蓝色调 */
:deep(.system-dark-theme) .username,
:deep(html.dark.system-theme) .username {
  color: #a4c0f4;
}

.main-content {
  padding: 20px;
  overflow-y: auto;
  background-color: #f0f2f5;
  transition: all 0.3s;
}

/* 暗黑模式主内容区 */
:deep(.dark-theme) .main-content {
  background-color: #232324;
}

/* 系统暗黑模式主内容区 */
:deep(.system-dark-theme) .main-content,
:deep(html.dark.system-theme) .main-content {
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
:deep(.dark-theme) .footer-container {
  background-color: #232324;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 系统暗黑模式页脚 */
:deep(.system-dark-theme) .footer-container,
:deep(html.dark.system-theme) .footer-container {
  background-color: #141414;
  background-image: none;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* 添加菜单折叠动画 */
.el-menu-vertical {
  transition: width 0.28s ease;
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

.theme-color-switch {
  margin-right: 15px;
}

.theme-color-button {
  padding: 8px;
  font-size: 18px;
  color: var(--el-text-color-regular);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.theme-color-button:hover {
  color: var(--el-color-primary);
  background: var(--el-color-primary-light-9);
}

/* 自定义配色面板样式 */
.color-theme-container {
  padding: 20px;
}

.theme-section {
  margin-bottom: 20px;
}

.theme-section h3 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: 500;
  color: var(--el-text-color-primary);
}

.preset-themes {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
  width: 100%;
}

.theme-radio {
  width: 100%;
  height: 100%;
  margin-right: 0;
}

/* Hide the default radio button circle but keep it functional */
.theme-radio :deep(.el-radio__input) {
  position: absolute;
  top: 5px;
  right: 5px;
  z-index: 2;
  display: none;
}

/* Style for the entire theme item container */
.preset-theme-item {
  width: 80px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  border-radius: 4px;
  padding: 8px 4px;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* Active theme styling */
.preset-theme-item.active {
  background-color: var(--el-color-primary-light-9);
  box-shadow: 0 0 0 2px var(--el-color-primary);
}

/* Add a checkmark to indicate selection */
.preset-theme-item.active::after {
  content: "✓";
  position: absolute;
  top: 4px;
  right: 4px;
  color: var(--el-color-primary);
  font-weight: bold;
  z-index: 1;
}

.preset-theme-item:hover {
  background-color: var(--el-fill-color-light);
  box-shadow: 0 0 0 1px var(--el-color-primary-light-5);
}

.theme-preview {
  width: 64px;
  height: 40px;
  margin: 0 auto 8px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--el-border-color-lighter);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.preview-header {
  height: 8px;
}

.preview-body {
  height: 32px;
  padding: 4px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.preview-button {
  width: 60%;
  height: 6px;
  border-radius: 2px;
  margin: 0 auto;
}

.preview-text {
  width: 70%;
  height: 2px;
  margin: 0 auto;
}

.preview-link {
  width: 70%;
  height: 2px;
  margin: 0 auto;
}

.preset-theme-item span {
  font-size: 12px;
  display: block;
  color: var(--el-text-color-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preset-theme-item:hover {
  background-color: var(--el-fill-color-light);
}

.preset-theme-item:hover .theme-preview {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.el-form-item {
  margin-bottom: 16px;
}

.el-form-item .el-input {
  width: calc(100% - 45px);
  margin-left: 8px;
}

.el-color-picker {
  vertical-align: middle;
}

.theme-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}

/* 添加特殊样式触发菜单重绘而不改变结构 */
.theme-menu-refresh {
  opacity: 0.99;
}

.live-preview {
  margin-bottom: 20px;
}

.preview-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
}

.preview-card {
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.preview-card-header {
  padding: 8px;
  background-color: var(--el-color-primary-light-9);
}

.preview-card-title {
  font-size: 14px;
  font-weight: 500;
}

.preview-card-body {
  padding: 16px;
}

.preview-buttons {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.preview-buttons.hover-demo {
  margin-top: 8px;
}

.mt-2 {
  margin-top: 8px;
}

.preview-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.3s;
}

.preview-btn.primary {
  background-color: var(--button-bg-color);
  color: var(--button-text-color);
}

.preview-btn.default {
  background-color: var(--button-default-bg-color);
  color: var(--button-default-text-color);
  border: 1px solid var(--border-color);
}

.preview-btn.primary-hover {
  background-color: var(--button-hover-bg-color);
  color: var(--button-text-color);
}

.preview-btn.default-hover {
  color: var(--primary-color);
  border: 1px solid var(--primary-color);
  background-color: var(--button-default-hover-bg-color);
}

.preview-link-text {
  margin-top: 16px;
}

.preview-link-text a {
  color: var(--el-color-primary);
  text-decoration: none;
}

.preview-icon {
  margin-top: 16px;
}

.preview-icon i {
  font-size: 18px;
}

.mt-4 {
  margin-top: 16px;
}

.preview-table-container {
  margin-top: 20px;
  border-radius: 4px;
  overflow: hidden;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid var(--border-color);
  font-size: 12px;
}

.preview-table-header {
  display: flex;
  background-color: var(--bg-color-soft);
  font-weight: bold;
  border-bottom: 1px solid var(--border-color);
}

.preview-table-row {
  display: flex;
  border-bottom: 1px solid var(--border-color-lighter);
}

.preview-table-row.preview-hover {
  background-color: var(--button-default-hover-bg-color);
}

.preview-table-cell {
  flex: 1;
  padding: 6px 10px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  text-align: left;
}

.preview-table-cell .preview-link {
  cursor: pointer;
  color: var(--link-color);
}

/* 优化色彩选择器样式 */
:deep(.el-tabs__item) {
  font-size: 14px;
  padding: 0 15px;
}

:deep(.el-tabs__active-bar) {
  background-color: var(--primary-color);
}

:deep(.el-tabs__item.is-active) {
  color: var(--primary-color);
  font-weight: 600;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

:deep(.el-color-picker) {
  vertical-align: middle;
  margin-right: 8px;
}

:deep(.el-input__wrapper) {
  width: calc(100% - 50px);
}

/* 动画效果增强 */
.preview-card {
  transition: all 0.3s ease;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.preview-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.color-settings-container {
  margin-bottom: 20px;
}

.color-tabs .el-tabs__nav {
  margin-bottom: 16px;
}

:deep(.el-form-item) {
  margin-bottom: 16px;
}

:deep(.el-form-item .el-input) {
  width: calc(100% - 45px);
  margin-left: 8px;
}

:deep(.el-color-picker) {
  vertical-align: middle;
  margin-right: 8px;
}

:deep(.el-input__wrapper) {
  width: calc(100% - 50px);
}

:deep(.el-tabs__nav-wrap::after) {
  height: 1px;
  background-color: var(--border-color-lighter, var(--el-border-color-lighter));
}

:deep(.el-tabs__active-bar) {
  height: 2px;
  background-color: var(--primary-color, var(--el-color-primary));
}

:deep(.el-tabs__item) {
  font-size: 14px;
  padding: 0 20px;
  height: 36px;
  line-height: 36px;
}

:deep(.el-tabs__item.is-active) {
  color: var(--primary-color, var(--el-color-primary));
  font-weight: 600;
}

.theme-label-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.theme-mode-badge {
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 2px;
  margin-left: 4px;
  background-color: var(--el-color-info-light-8);
  color: var(--el-color-info);
}

.theme-mode-badge.light {
  background-color: #f4f4f5;
  color: #909399;
}

.dark-variant .theme-mode-badge {
  background-color: #333;
  color: #e0e0e0;
}

.dark-preview {
  border: 1px solid #444;
}

.theme-preview {
  width: 64px;
  height: 40px;
  margin: 0 auto 8px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--el-border-color-lighter);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.preset-theme-item.dark-variant {
  background-color: rgba(0, 0, 0, 0.04);
}

.preset-theme-item.dark-variant.active {
  background-color: var(--el-color-primary-light-9);
}

.theme-label {
  font-size: 12px;
  display: block;
  margin-top: 4px;
  color: var(--el-text-color-primary);
  word-break: keep-all;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 75%;
}

/* Theme tabs styles */
:deep(.el-tabs__nav) {
  margin-bottom: 15px;
}

:deep(.el-tabs--card > .el-tabs__header .el-tabs__item.is-active) {
  border-bottom-color: var(--el-color-primary-light-9);
  background-color: var(--el-color-primary-light-9);
}

:deep(.el-tabs--card > .el-tabs__header) {
  border-bottom: 1px solid var(--el-border-color-light);
}

.preset-themes {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
  width: 100%;
}

:deep(.el-radio-button__inner) {
  padding: 5px 12px;
  font-size: 12px;
}
</style>
