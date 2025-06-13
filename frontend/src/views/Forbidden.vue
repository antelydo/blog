<!-- 禁止访问页面 -->
<template>
  <div class="error-container">
    <div class="error-content">
      <h1 class="error-code">403</h1>
      <h2 class="error-title">{{ $t('errors.forbidden.title') }}</h2>
      <p class="error-message">{{ $t('errors.forbidden.message') }}</p>
      <div class="error-actions">
        <el-button type="primary" @click="goHome">{{ $t('errors.actions.goHome') }}</el-button>
        <el-button @click="goBack">{{ $t('errors.actions.goBack') }}</el-button>
        <el-button @click="goLogin">{{ $t('errors.actions.login') }}</el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goHome = () => {
  router.push('/');
};

const goLogin = () => {
  router.push('/login');
};

const goBack = () => {
  router.go(-1);
};
</script>

<style scoped>
.error-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: var(--el-bg-color);
}

.error-content {
  text-align: center;
  padding: 2rem;
}

.error-code {
  font-size: 8rem;
  color: var(--el-color-primary);
  margin: 0;
  line-height: 1;
}

.error-title {
  font-size: 2rem;
  margin: 1rem 0;
}

.error-message {
  font-size: 1.2rem;
  color: var(--el-text-color-secondary);
  margin-bottom: 2rem;
}

.error-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}
</style> 