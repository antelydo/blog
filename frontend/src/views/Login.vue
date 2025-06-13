<!-- 用户登录页面 -->
<template>
  <div class="login-container">
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

    <div class="login-box">
      <div class="login-header">
        <h2>{{ $t('webUser.login.userLogin') }}</h2>
        <p class="login-subtitle">{{ $t('webUser.login.welcome') }}</p>
      </div>
      <el-form
        ref="loginFormRef"
        :model="loginForm"
        :rules="loginRules"
        class="login-form"
        @keyup.enter="handleLogin"
      >
        <el-form-item prop="username">
          <el-input
            v-model="loginForm.username"
            :placeholder="$t('webUser.login.username')"
            :prefix-icon="User"
          />
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            v-model="loginForm.password"
            type="password"
            :placeholder="$t('webUser.login.password')"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>
        <el-form-item>
          <el-button
            :loading="loading"
            type="primary"
            class="login-button"
            @click="handleLogin"
          >
            {{ loading ? $t('webUser.login.loggingIn') : $t('webUser.login.login') }}
          </el-button>
        </el-form-item>
        <div class="login-options">
          <el-checkbox v-model="rememberMe">{{ $t('webUser.login.rememberMe') }}</el-checkbox>
          <div class="options-links">
            <a href="#" class="register-link" @click.prevent="handleRegister">
              {{ $t('webUser.login.register') }}
            </a>
            <span class="divider">|</span>
            <a href="#" class="forgot-password" @click.prevent="handleForgotPassword">
              {{ $t('webUser.login.forgotPassword') }}
            </a>
          </div>
        </div>
        <p v-if="error" class="error-message">{{ error }}</p>
      </el-form>
    </div>
    <Footer />
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { User, Lock, Sunny, Moon, Monitor } from '@element-plus/icons-vue';
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
const loginFormRef = ref(null);
const loading = ref(false);
const error = ref('');
const rememberMe = ref(false);

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

    // 如果有任何组件未能正确更新，可以强制重新渲染一些关键组件
    const loginComponents = document.querySelectorAll('.el-form, .el-input, .el-button')
    loginComponents.forEach(comp => {
      comp.classList.add('i18n-refresh')
      setTimeout(() => {
        comp.classList.remove('i18n-refresh')
      }, 50)
    })
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

const loginForm = reactive({
  username: '',
  password: ''
});

// 表单验证规则
const loginRules = {
  username: [
    { required: true, message: t('webUser.validation.usernameRequired'), trigger: 'blur' },
    { min: 3, max: 20, message: t('webUser.validation.usernameLength'), trigger: 'blur' }
  ],
  password: [
    { required: true, message: t('webUser.validation.passwordRequired'), trigger: 'blur' },
    { min: 6, max: 20, message: t('webUser.validation.passwordLength'), trigger: 'blur' }
  ]
};

// 检查是否有保存的登录信息
const checkSavedCredentials = () => {
  const savedUsername = localStorage.getItem('saved_username');
  if (savedUsername) {
    loginForm.username = savedUsername;
    rememberMe.value = true;
  }
};

// 页面加载时检查保存的登录信息
checkSavedCredentials();

const handleLogin = async () => {
  // 表单验证
  await loginFormRef.value.validate(async (valid) => {
    if (!valid) return;

    loading.value = true;
    error.value = '';

    try {
      // 使用配置中的API地址
      const response = await api.post(apiUrls.USER_API.LOGIN, loginForm, {
        // 添加跨域凭证支持
        withCredentials: true
      });

      if (response.code === 200) {
        // 保存token
        localStorage.setItem('token', response.data.token);

        // 如果选择了记住我，保存用户名
        if (rememberMe.value) {
          localStorage.setItem('saved_username', loginForm.username);
        } else {
          localStorage.removeItem('saved_username');
        }

        ElMessage.success(t('webUser.messages.loginSuccess'));

        // 跳转到首页
        router.push('/');
      } else {
        error.value = response.msg || t('webUser.messages.loginFailed');
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

// Handle register button click
const handleRegister = () => {
  router.push('/register');
};

// Handle forgot password button click
const handleForgotPassword = () => {
  router.push('/forgot-password');
};
</script>

<style scoped>
.login-container {
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

.login-box {
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

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-header h2 {
  font-size: 28px;
  color: var(--heading-color, #303133);
  margin: 0;
  font-weight: 500;
  transition: color 0.3s ease;
}

.login-subtitle {
  font-size: 14px;
  color: var(--light-text-color, #909399);
  margin-top: 8px;
  margin-bottom: 0;
  transition: color 0.3s ease;
}

.login-form {
  margin-top: 20px;
  width: 100%;
}

.login-form :deep(.el-input__wrapper) {
  padding: 4px 15px;
  border-radius: 6px;
  box-shadow: 0 0 0 1px #dcdfe6 inset;
}

.login-form :deep(.el-input__wrapper.is-focus) {
  box-shadow: 0 0 0 1px #409EFF inset;
}

.login-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.login-button {
  width: 100%;
  padding: 12px 0;
  font-size: 16px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.login-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  font-size: 14px;
}

.options-links {
  display: flex;
  gap: 8px;
  align-items: center;
}

.divider {
  color: #dcdfe6;
  font-size: 12px;
}

.forgot-password, .register-link {
  color: var(--el-color-primary);
  text-decoration: none;
  transition: color 0.3s;
  cursor: pointer;
}

.forgot-password:hover, .register-link:hover {
  color: var(--el-color-primary-light-3);
  text-decoration: underline;
}

.error-message {
  color: #F56C6C;
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
  .login-box {
    width: 100%;
    max-width: 360px;
    padding: 25px;
  }

  .login-header h2 {
    font-size: 24px;
  }

  .login-subtitle {
    font-size: 13px;
  }
}

@media (max-height: 600px) {
  .login-container {
    height: auto;
    min-height: 100vh;
  }
}
</style>