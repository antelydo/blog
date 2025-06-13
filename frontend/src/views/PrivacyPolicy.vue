<!-- 隐私政策页面 -->
<template>
  <div class="privacy-container">
    <!-- Theme and Language toggles -->
    <div class="top-controls">
      <!-- Language switch dropdown -->
      <div class="language-switch">
        <el-dropdown trigger="click" @command="handleLanguageChange">
          <el-button class="language-button" circle>
            {{ currentLanguage }}
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="zh">
                <span>{{ $t('webUser.language.zh') }}</span>
              </el-dropdown-item>
              <el-dropdown-item command="en">
                <span>{{ $t('webUser.language.en') }}</span>
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
      
      <!-- Theme switch dropdown -->
      <div class="theme-switch">
        <el-dropdown trigger="click" @command="handleThemeChange">
          <el-button class="theme-button" :icon="themeIcon" circle />
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="light">
                <el-icon><Sunny /></el-icon>
                <span>{{ $t('webUser.theme.light') }}</span>
              </el-dropdown-item>
              <el-dropdown-item command="dark">
                <el-icon><Moon /></el-icon>
                <span>{{ $t('webUser.theme.dark') }}</span>
              </el-dropdown-item>
              <el-dropdown-item command="system">
                <el-icon><Monitor /></el-icon>
                <span>{{ $t('webUser.theme.system') }}</span>
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
    </div>

    <div class="privacy-content">
      <h1>{{ $t('webUser.privacy.title') }}</h1>
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.introduction') }}</h2>
        <p>{{ $t('webUser.privacy.introductionContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.information') }}</h2>
        <p>{{ $t('webUser.privacy.informationContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.use') }}</h2>
        <p>{{ $t('webUser.privacy.useContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.sharing') }}</h2>
        <p>{{ $t('webUser.privacy.sharingContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.security') }}</h2>
        <p>{{ $t('webUser.privacy.securityContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.cookies') }}</h2>
        <p>{{ $t('webUser.privacy.cookiesContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.rights') }}</h2>
        <p>{{ $t('webUser.privacy.rightsContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.changes') }}</h2>
        <p>{{ $t('webUser.privacy.changesContent') }}</p>
      </div>
      
      <div class="privacy-section">
        <h2>{{ $t('webUser.privacy.contact') }}</h2>
        <p>{{ $t('webUser.privacy.contactContent') }}</p>
      </div>
    </div>
    
    <div class="back-link">
      <el-button @click="goBack">{{ $t('webUser.common.back') }}</el-button>
    </div>
    
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { Sunny, Moon, Monitor } from '@element-plus/icons-vue';
import { useI18n } from 'vue-i18n';
import Footer from '@/components/common/Footer.vue';
import { useThemeStore } from '@/stores/theme'
import { useConfigStore } from '@/stores/config'
import { useLanguageStore } from '@/stores/language'

const router = useRouter();
const { t, locale } = useI18n();
const themeStore = useThemeStore();
const configStore = useConfigStore();
const languageStore = useLanguageStore();

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
  configStore.updatePageTitle()
  
  // 添加延迟强制刷新，确保所有组件都正确更新语言
  nextTick(() => {
    const event = new CustomEvent('language-changed', { detail: { lang } })
    window.dispatchEvent(event)
    
    // 确保路由标题也被更新
    const currentRoute = router?.currentRoute?.value
    if (currentRoute?.meta?.title) {
      document.title = configStore.getPageTitle(currentRoute.meta.title)
    }
  })
}

// Theme icon
const themeIcon = computed(() => {
  if (themeStore.theme === 'system') {
    return Monitor
  } else if (themeStore.theme === 'dark') {
    return Moon
  } else {
    return Sunny
  }
})

// Handle theme change
const handleThemeChange = (theme) => {
  themeStore.setTheme(theme)
}

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.privacy-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f0f2f5;
  background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  background-size: cover;
  background-position: center;
  position: relative;
  box-sizing: border-box;
}

/* Top controls for language and theme */
.top-controls {
  position: absolute;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
  z-index: 10;
}

.language-switch, .theme-switch {
  display: flex;
  align-items: center;
}

.language-button {
  font-size: 14px;
  font-weight: 500;
  height: 36px;
  width: 36px;
  padding: 0;
  background-color: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
  transition: all 0.3s;
}

.language-button:hover {
  background-color: var(--el-color-primary-light-7);
}

.theme-button {
  font-size: 18px;
  height: 36px;
  width: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
  transition: all 0.3s;
}

.theme-button:hover {
  background-color: var(--el-color-primary-light-7);
}

:deep(.el-dropdown-menu__item) {
  display: flex;
  align-items: center;
  gap: 8px;
}

.privacy-content {
  max-width: 1024px;
  margin: 100px auto 40px;
  padding: 40px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.privacy-content h1 {
  font-size: 32px;
  color: #303133;
  margin-bottom: 30px;
  text-align: center;
}

.privacy-section {
  margin-bottom: 30px;
}

.privacy-section h2 {
  font-size: 20px;
  color: #303133;
  margin-bottom: 15px;
}

.privacy-section p {
  font-size: 16px;
  line-height: 1.6;
  color: #606266;
}

.back-link {
  text-align: center;
  margin: 20px 0;
}

/* Footer styles */
:deep(.footer-wrapper) {
  margin-top: auto;
  width: 100%;
}

/* 响应式设计 */
@media (max-width: 1024px) {
  .privacy-content {
    margin: 100px 20px 40px;
    padding: 30px;
  }
}

@media (max-width: 768px) {
  .privacy-content {
    margin: 80px 15px 40px;
    padding: 20px;
  }
  
  .privacy-content h1 {
    font-size: 24px;
  }
  
  .privacy-section h2 {
    font-size: 18px;
  }
  
  .privacy-section p {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .privacy-content {
    margin: 80px 10px 40px;
    padding: 15px;
  }
  
  .privacy-content h1 {
    font-size: 22px;
  }
  
  .privacy-section h2 {
    font-size: 16px;
  }
  
  .privacy-section p {
    font-size: 13px;
  }
}
</style> 