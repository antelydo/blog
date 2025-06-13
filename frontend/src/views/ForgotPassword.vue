<!-- 忘记密码页面 -->
<template>
  <div class="forgot-password-container">
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

    <div class="forgot-password-box">
      <div class="forgot-password-header">
        <h2>{{ $t('webUser.forgotPassword.title') }}</h2>
        <p class="forgot-password-subtitle">{{ $t('webUser.forgotPassword.instruction') }}</p>
      </div>
      <el-form
        ref="forgotPasswordFormRef"
        :model="forgotPasswordForm"
        :rules="formRules"
        class="forgot-password-form"
        @keyup.enter="handleSubmit"
      >
        <el-form-item prop="email">
          <el-input
            v-model="forgotPasswordForm.email"
            :placeholder="$t('webUser.forgotPassword.email')"
            :prefix-icon="Message"
          />
        </el-form-item>
        <el-form-item>
          <el-button
            :loading="loading"
            type="primary"
            class="submit-button"
            @click="handleSubmit"
          >
            {{ loading ? $t('webUser.forgotPassword.resetProcessing') : $t('webUser.forgotPassword.resetButton') }}
          </el-button>
        </el-form-item>
        <div class="login-link-container">
          <a href="#" class="login-link" @click.prevent="goToLogin">
            {{ $t('webUser.forgotPassword.backToLogin') }}
          </a>
        </div>
        <p v-if="error" class="error-message">{{ error }}</p>
        <p v-if="success" class="success-message">{{ success }}</p>
      </el-form>
    </div>
    <Footer />
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { Message, Sunny, Moon, Monitor } from '@element-plus/icons-vue';
import { useI18n } from 'vue-i18n';
import api, { apiUrls } from '../api';
import Footer from '@/components/common/Footer.vue';
import { useThemeStore } from '@/stores/theme'
import { useConfigStore } from '@/stores/config'
import { useLanguageStore } from '@/stores/language'

const router = useRouter();
const { t, locale } = useI18n();
const themeStore = useThemeStore();
const configStore = useConfigStore();
const languageStore = useLanguageStore();
const forgotPasswordFormRef = ref(null);
const loading = ref(false);
const error = ref('');
const success = ref('');

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

const forgotPasswordForm = reactive({
  email: ''
});

// 表单验证规则
const formRules = {
  email: [
    { required: true, message: t('webUser.validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('webUser.validation.invalidEmail'), trigger: 'blur' }
  ]
};

const goToLogin = () => {
  router.push('/login');
};

const handleSubmit = async () => {
  // 表单验证
  await forgotPasswordFormRef.value.validate(async (valid) => {
    if (!valid) return;

    loading.value = true;
    error.value = '';
    success.value = '';

    try {
      // 使用配置中的API地址
      const response = await api.post(apiUrls.USER_API.FORGOT_PASSWORD, {
        email: forgotPasswordForm.email
      });

      if (response.code === 200) {
        success.value = t('webUser.forgotPassword.resetSuccess');
        ElMessage.success(success.value);

        // 5秒后跳转到登录页
        setTimeout(() => {
          router.push('/login');
        }, 5000);
      } else {
        error.value = response.msg || t('webUser.forgotPassword.resetFailed');
        ElMessage.error(error.value);
      }
    } catch (err) {
      error.value = t('webUser.messages.serverError');
      ElMessage.error(error.value);
      console.error(err);
    } finally {
      loading.value = false;
    }
  });
};
</script>

<style scoped>
.forgot-password-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: var(--bg-color, #f0f2f5);
  background-image: linear-gradient(135deg, var(--bg-color, #f5f7fa) 0%, var(--card-bg-color, #c3cfe2) 100%);
  background-size: cover;
  background-position: center;
  position: relative;
  box-sizing: border-box;
  transition: background-color 0.3s ease, color 0.3s ease;
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

.forgot-password-box {
  width: 400px;
  padding: 35px;
  background-color: var(--card-bg-color, #fff);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  margin: auto;
  position: relative;
  top: 0;
  left: 0;
  right: 0;
  flex: 0 0 auto;
}

.forgot-password-header {
  text-align: center;
  margin-bottom: 30px;
}

.forgot-password-header h2 {
  font-size: 28px;
  color: var(--heading-color, #303133);
  margin: 0;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot-password-subtitle {
  font-size: 14px;
  color: var(--light-text-color, #909399);
  margin-top: 8px;
  margin-bottom: 0;
  line-height: 1.5;
  transition: color 0.3s ease;
}

.forgot-password-form {
  margin-top: 20px;
  width: 100%;
}

.forgot-password-form :deep(.el-input__wrapper) {
  padding: 4px 15px;
  border-radius: 6px;
  box-shadow: 0 0 0 1px #dcdfe6 inset;
}

.forgot-password-form :deep(.el-input__wrapper.is-focus) {
  box-shadow: 0 0 0 1px #409EFF inset;
}

.forgot-password-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.submit-button {
  width: 100%;
  padding: 12px 0;
  font-size: 16px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.login-link-container {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
}

.login-link {
  color: var(--el-color-primary);
  text-decoration: none;
  transition: color 0.3s;
}

.login-link:hover {
  color: var(--el-color-primary-light-3);
  text-decoration: underline;
}

.error-message {
  color: #F56C6C;
  margin-top: 10px;
  text-align: center;
}

.success-message {
  color: #67C23A;
  margin-top: 10px;
  text-align: center;
}

/* Footer styles */
:deep(.footer-wrapper) {
  margin-top: auto;
  width: 100%;
}

/* 响应式设计 */
@media (max-width: 480px) {
  .forgot-password-box {
    width: 100%;
    max-width: 360px;
    padding: 25px;
  }

  .forgot-password-header h2 {
    font-size: 24px;
  }

  .forgot-password-subtitle {
    font-size: 13px;
  }
}

@media (max-height: 600px) {
  .forgot-password-container {
    height: auto;
    min-height: 100vh;
  }
}
</style>