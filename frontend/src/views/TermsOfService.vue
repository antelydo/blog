<!-- 服务条款页面 -->
<template>
  <div class="terms-container">
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

    <div class="terms-content">
      <h1>{{ $t('webUser.terms.title') }}</h1>
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.acceptance') }}</h2>
        <p>{{ $t('webUser.terms.acceptanceContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.userAccount') }}</h2>
        <p>{{ $t('webUser.terms.userAccountContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.userContent') }}</h2>
        <p>{{ $t('webUser.terms.userContentContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.privacy') }}</h2>
        <p>{{ $t('webUser.terms.privacyContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.termination') }}</h2>
        <p>{{ $t('webUser.terms.terminationContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.changes') }}</h2>
        <p>{{ $t('webUser.terms.changesContent') }}</p>
      </div>
      
      <div class="terms-section">
        <h2>{{ $t('webUser.terms.contact') }}</h2>
        <p>{{ $t('webUser.terms.contactContent') }}</p>
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
.terms-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f0f2f5;
  background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  background-size: cover;
  background-position: center;
  position: relative;
  box-sizing: border-box;
  padding: 0 20px;
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

.terms-content {
  max-width: 1024px;
  width: 100%;
  margin: 100px auto 40px;
  padding: 40px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  box-sizing: border-box;
}

.terms-content h1 {
  font-size: 32px;
  color: #303133;
  margin-bottom: 30px;
  text-align: center;
  line-height: 1.3;
}

.terms-section {
  margin-bottom: 30px;
  line-height: 1.6;
}

.terms-section h2 {
  font-size: 20px;
  color: #303133;
  margin-bottom: 15px;
  line-height: 1.4;
}

.terms-section p {
  font-size: 16px;
  line-height: 1.8;
  color: #606266;
  margin-bottom: 15px;
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
  .terms-content {
    margin: 100px auto 40px;
    padding: 35px;
  }
  
  .terms-content h1 {
    font-size: 28px;
  }
  
  .terms-section h2 {
    font-size: 19px;
  }
  
  .terms-section p {
    font-size: 15px;
  }
}

@media (max-width: 768px) {
  .terms-container {
    padding: 0 15px;
  }
  
  .terms-content {
    margin: 80px auto 40px;
    padding: 25px;
  }
  
  .terms-content h1 {
    font-size: 24px;
  }
  
  .terms-section h2 {
    font-size: 18px;
  }
  
  .terms-section p {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .terms-container {
    padding: 0 10px;
  }
  
  .terms-content {
    margin: 70px auto 40px;
    padding: 20px;
  }
  
  .terms-content h1 {
    font-size: 22px;
  }
  
  .terms-section h2 {
    font-size: 16px;
  }
  
  .terms-section p {
    font-size: 13px;
  }
}
</style> 