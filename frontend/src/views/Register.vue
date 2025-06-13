<!-- 用户注册页面 -->
<template>
  <div class="register-container">
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

    <div class="register-box">
      <div class="register-header">
        <h2>{{ $t('webUser.register.title') }}</h2>
      </div>
      <el-form
        ref="registerFormRef"
        :model="registerForm"
        :rules="registerRules"
        class="register-form"
        @keyup.enter="handleRegister"
      >
        <el-form-item prop="username">
          <el-input
            v-model="registerForm.username"
            :placeholder="$t('webUser.register.username')"
            :prefix-icon="User"
          />
        </el-form-item>
        <el-form-item prop="email">
          <el-input
            v-model="registerForm.email"
            :placeholder="$t('webUser.register.email')"
            :prefix-icon="Message"
          />
        </el-form-item>
        <el-form-item prop="phone">
          <el-input
            v-model="registerForm.phone"
            :placeholder="$t('webUser.register.phone')"
            :prefix-icon="Phone"
          />
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            v-model="registerForm.password"
            type="password"
            :placeholder="$t('webUser.register.password')"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>
        <el-form-item prop="confirmPassword">
          <el-input
            v-model="registerForm.confirmPassword"
            type="password"
            :placeholder="$t('webUser.register.confirmPassword')"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>
        <el-form-item>
          <el-button
            :loading="loading"
            type="primary"
            class="register-button"
            @click="handleRegister"
          >
            {{ loading ? $t('webUser.register.registerProcessing') : $t('webUser.register.registerButton') }}
          </el-button>
        </el-form-item>
        <div class="register-options">
          <el-checkbox v-model="agreement" class="agreement-checkbox">
            {{ $t('webUser.register.agree') }}
            <a href="#" class="terms-link" @click.prevent="goToTerms">{{ $t('webUser.register.termsOfService') }}</a>
            &
            <a href="#" class="privacy-link" @click.prevent="goToPrivacy">{{ $t('webUser.register.privacyPolicy') }}</a>
          </el-checkbox>
        </div>
        <div class="login-link-container">
          <span>{{ $t('webUser.register.alreadyHaveAccount') }}</span>
          <a href="#" class="login-link" @click.prevent="goToLogin">
            {{ $t('webUser.login.login') }}
          </a>
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
import { User, Lock, Phone, Message, Sunny, Moon, Monitor } from '@element-plus/icons-vue';
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
const registerFormRef = ref(null);
const loading = ref(false);
const error = ref('');
const agreement = ref(false);

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

const registerForm = reactive({
  username: '',
  email: '',
  phone: '',
  password: '',
  confirmPassword: ''
});

// 表单验证规则
const registerRules = {
  username: [
    { required: true, message: t('webUser.validation.usernameRequired'), trigger: 'blur' },
    { min: 3, max: 20, message: t('webUser.validation.usernameLength'), trigger: 'blur' }
  ],
  email: [
    { required: true, message: t('webUser.validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('webUser.validation.invalidEmail'), trigger: 'blur' }
  ],
  phone: [
    { required: true, message: t('webUser.validation.phoneRequired'), trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: t('webUser.validation.invalidPhone'), trigger: 'blur' }
  ],
  password: [
    { required: true, message: t('webUser.validation.passwordRequired'), trigger: 'blur' },
    { min: 6, max: 20, message: t('webUser.validation.passwordLength'), trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: t('webUser.validation.passwordRequired'), trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        if (value !== registerForm.password) {
          callback(new Error(t('webUser.validation.passwordsMustMatch')));
        } else {
          callback();
        }
      },
      trigger: 'blur'
    }
  ]
};

const goToLogin = () => {
  router.push('/login');
};

const goToTerms = () => {
  router.push('/terms-of-service');
};

const goToPrivacy = () => {
  router.push('/privacy-policy');
};

const handleRegister = async () => {
  // 表单验证
  await registerFormRef.value.validate(async (valid) => {
    if (!valid) return;

    if (!agreement.value) {
      ElMessage.warning(t('webUser.validation.termsRequired'));
      return;
    }

    loading.value = true;
    error.value = '';

    try {
      // 使用配置中的API地址
      const response = await api.post(apiUrls.USER_API.REGISTER, {
        username: registerForm.username,
        email: registerForm.email,
        mobile: registerForm.phone,
        password: registerForm.password,
        confirm_password:registerForm.confirmPassword
      });

      if (response.code === 200) {
        ElMessage.success(t('webUser.register.registerSuccess'));

        // 注册成功后跳转到登录页
        router.push('/login');
      } else {
        error.value = response.msg || t('webUser.register.registerFailed');
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
.register-container {
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

.register-box {
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

.register-header {
  text-align: center;
  margin-bottom: 30px;
}

.register-header h2 {
  font-size: 28px;
  color: var(--heading-color, #303133);
  margin: 0;
  font-weight: 500;
  transition: color 0.3s ease;
}

.register-form {
  margin-top: 20px;
  width: 100%;
}

.register-form :deep(.el-input__wrapper) {
  padding: 4px 15px;
  border-radius: 6px;
  box-shadow: 0 0 0 1px #dcdfe6 inset;
}

.register-form :deep(.el-input__wrapper.is-focus) {
  box-shadow: 0 0 0 1px #409EFF inset;
}

.register-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.register-button {
  width: 100%;
  padding: 12px 0;
  font-size: 16px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.register-options {
  margin-bottom: 15px;
  font-size: 14px;
}

.login-link-container {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
  color: #606266;
}

.login-link {
  color: var(--el-color-primary);
  text-decoration: none;
  margin-left: 5px;
  transition: color 0.3s;
}

.login-link:hover {
  color: var(--el-color-primary-light-3);
  text-decoration: underline;
}

.terms-link, .privacy-link {
  color: var(--el-color-primary);
  text-decoration: none;
  transition: color 0.3s;
}

.terms-link:hover, .privacy-link:hover {
  color: var(--el-color-primary-light-3);
  text-decoration: underline;
}

.agreement-checkbox {
  display: flex;
  align-items: center;
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
  .register-box {
    width: 100%;
    max-width: 360px;
    padding: 25px;
  }

  .register-header h2 {
    font-size: 24px;
  }
}

@media (max-height: 600px) {
  .register-container {
    height: auto;
    min-height: 100vh;
  }
}
</style>