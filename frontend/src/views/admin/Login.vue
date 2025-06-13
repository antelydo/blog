<!-- 管理员登录页面 -->
<template>
  <div class="admin-login-container">
    <div class="login-box">
      <div class="login-header">
        <h2>管理员登录</h2>
        <p class="login-subtitle">欢迎回来，请登录管理员账号</p>
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
            placeholder="请输入管理员用户名"
            :prefix-icon="User"
          />
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            v-model="loginForm.password"
            type="password"
            placeholder="请输入密码"
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
            {{ loading ? '登录中...' : '登录' }}
          </el-button>
        </el-form-item>
      </el-form>
    </div>
    <Footer />
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { User, Lock } from '@element-plus/icons-vue'
import adminApi from '@/api/admin'
import apiConfig from '@/api/config'
import Footer from '@/components/common/Footer.vue'

const router = useRouter()
const loginFormRef = ref(null)
const loading = ref(false)

// 登录表单
const loginForm = reactive({
  username: '',
  password: ''
})

// 表单验证规则
const loginRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度应为3-20个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 20, message: '密码长度应为6-20个字符', trigger: 'blur' }
  ]
}

// 登录处理
const handleLogin = async () => {
  // 表单验证
  await loginFormRef.value.validate(async (valid) => {
    if (!valid) return

    loading.value = true

    try {
      // 调用管理员登录API
      const response = await adminApi.post(apiConfig.ADMIN_API.LOGIN, loginForm)

      if (response.code === 200) {
        // 确保token带有Bearer前缀
        const token = response.data.token.startsWith('Bearer ')
          ? response.data.token
          : `Bearer ${response.data.token}`;

        // 调试日志
          responseCode: response.code,
          hasToken: !!response.data.token,
          tokenPrefix: response.data.token.substring(0, 10) + '...',
          formattedToken: token.substring(0, 20) + '...'
        });

        // 保存token
        localStorage.setItem('admin_token', token);

        // 保存用户信息，确保不包含token字段以避免重复
        const adminInfo = {...response.data};
        localStorage.setItem('admin_info', JSON.stringify(adminInfo));

        ElMessage.success('登录成功');

        // 跳转到管理后台首页
        try {
          await router.push('/admin/dashboard');
        } catch (navigationError) {
          console.error('路由跳转错误:', navigationError);
          // 尝试使用window.location作为备选方案
          window.location.href = '/admin/dashboard';
        }
      } else {
        ElMessage.error(response.msg || '登录失败');
      }
    } catch (error) {
      console.error('登录错误:', error);
      ElMessage.error('服务器错误，请稍后再试');
    } finally {
      loading.value = false;
    }
  });
}
</script>

<style scoped>
.admin-login-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f0f2f5;
  background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  background-size: cover;
  background-position: center;
  position: relative;
}

.login-box {
  width: 400px;
  padding: 35px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  margin: 20px auto;
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
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

.login-form {
  margin-top: 20px;
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

/* 响应式设计 */
@media screen and (max-width: 768px) {
  .login-box {
    width: 360px;
    padding: 30px;
  }

  .login-header h2 {
    font-size: 24px;
  }

  .login-subtitle {
    font-size: 14px;
  }
}

@media screen and (max-width: 480px) {
  .login-box {
    width: 320px;
    padding: 25px;
  }

  .login-header {
    margin-bottom: 25px;
  }

  .login-header h2 {
    font-size: 22px;
  }
}
</style>