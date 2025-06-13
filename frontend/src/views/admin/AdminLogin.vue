<!-- 管理员登录页面 -->

<template>
  <div class="admin-login-container">
    <!-- 顶部控件区域 -->
    <div class="top-controls">
      <!-- 语言切换按钮 -->
      <el-dropdown trigger="click" @command="handleLanguageChange">
        <el-button class="language-button" circle>
          {{ currentLanguage }}
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="zh">简体中文</el-dropdown-item>
            <el-dropdown-item command="en">English</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>


    </div>

    <!-- 登录框 -->
    <div class="login-box">
      <div class="login-header">
        <h2>{{ t('auth.login.backendAdmin') }}</h2>
        <p class="login-subtitle">{{ t('auth.login.adminPortal') }}</p>
      </div>

      <el-form ref="loginFormRef" :model="loginForm" :rules="loginRules" class="login-form" @keyup.enter="handleLogin">
        <el-form-item prop="username">
          <el-input v-model="loginForm.username" :placeholder="t('auth.login.adminUsername')" :prefix-icon="User" size="large" />
        </el-form-item>

        <el-form-item prop="password">
          <el-input v-model="loginForm.password" :placeholder="t('auth.login.password')" :prefix-icon="Lock" type="password" show-password size="large" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" class="login-button" :loading="loading" @click="handleLogin" size="large">
            {{ loading ? t('auth.login.loggingIn') : t('auth.login.login') }}
          </el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 页脚 -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { User, Lock } from '@element-plus/icons-vue'
import Footer from '@/components/common/Footer.vue'
import { login } from '@/api/auth'

const router = useRouter()
const { t, locale } = useI18n()

// 登录表单相关
const loginFormRef = ref(null)
const loginForm = ref({
  username: '',
  password: ''
})

const loginRules = {
  username: [
    { required: true, message: t('auth.validation.usernameRequired'), trigger: 'blur' },
    { min: 3, max: 20, message: t('auth.validation.usernameLength'), trigger: 'blur' }
  ],
  password: [
    { required: true, message: t('auth.validation.passwordRequired'), trigger: 'blur' },
    { min: 6, max: 20, message: t('auth.validation.passwordLength'), trigger: 'blur' }
  ]
}

const loading = ref(false)

const handleLogin = async () => {
  if (!loginFormRef.value) return

  try {
    await loginFormRef.value.validate()

    loading.value = true
    // 调用登录API
    const response = await login(loginForm.value)

    if (response.code === 200) {
      // 确保token带有Bearer前缀
      const token = response.data.token.startsWith('Bearer ')
        ? response.data.token
        : `Bearer ${response.data.token}`

      // 保存token和用户信息
      localStorage.setItem('admin_token', token)
      localStorage.setItem('admin_info', JSON.stringify(response.data))

      ElMessage.success(t('auth.messages.loginSuccess'))
      router.push('/admin/dashboard')
    } else {
      ElMessage.error(response.msg || t('auth.login.loginFailed'))
    }
  } catch (error) {
    console.error('登录错误:', error)
    ElMessage.error(t('auth.login.loginFailed'))
  } finally {
    loading.value = false
  }
}

// 语言切换相关
const currentLanguage = computed(() => {
  return locale.value === 'zh' ? '中' : 'En'
})

const handleLanguageChange = (lang) => {
  locale.value = lang
  localStorage.setItem('locale', lang)
}


</script>

<style lang="scss" scoped>
$theme-transition-time: 0.15s;

/* 登录容器自适应全屏 */
.admin-login-container {
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* 使内容分布在容器两端，登录框在中间，Footer在底部 */
  align-items: center;
  min-height: 100vh; /* 使用min-height确保在小屏幕上也能完整显示 */
  width: 100vw;
  margin: 0;
  padding: 0;
  overflow-x: hidden; /* 只防止水平滚动条 */
  transition: background-color $theme-transition-time, background-image $theme-transition-time;
  will-change: background-color, background-image;

  /* 根据主题自动切换背景 */
  background-color: var(--el-bg-color, #f0f2f5);
  background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  background-size: cover;
  background-position: center;
  position: relative;
}



/* 顶部控件栏 */
.top-controls {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
  padding: 20px;
  gap: 10px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 10;
}

.language-button {
  width: 40px;
  height: 40px;
  background-color: var(--el-bg-color-overlay, rgba(255, 255, 255, 0.9));
  color: var(--el-text-color-primary, #303133);
  transition: all 0.3s;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.2);
  }
}

/* 登录框样式 */
.login-box {
  width: 400px;
  padding: 35px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  transition: all $theme-transition-time ease;
  margin: auto 0; /* 垂直居中 */
}

.login-header h2 {
  font-size: 28px;
  color: #303133;
  margin: 0;
  font-weight: 500;
}

.login-subtitle {
  font-size: 14px;
  color: #909399;
  margin-top: 8px;
  margin-bottom: 0;
}

.login-form :deep(.el-input__wrapper) {
  padding: 4px 15px;
  border-radius: 6px;
  box-shadow: 0 0 0 1px #dcdfe6 inset;
  transition: all $theme-transition-time ease;
}

/* 优化字体 */
.login-form :deep(.el-input__inner) {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  letter-spacing: 0.3px;
  transition: color $theme-transition-time ease;
  color: #606266;
}

.login-button {
  width: 100%;
  padding: 12px 0;
  font-size: 16px;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.3s ease;
  letter-spacing: 0.5px;
}

/* 自适应布局 */
@media screen and (max-width: 768px) {
  .login-box {
    width: 85%;
    max-width: 380px;
    padding: 25px 20px;
  }

  .login-header h2 {
    font-size: 24px;
  }
}

@media screen and (max-width: 480px) {
  .login-box {
    width: 90%;
    padding: 20px 15px;
  }

  .login-header h2 {
    font-size: 22px;
  }

  .login-subtitle {
    font-size: 13px;
  }
}
</style>