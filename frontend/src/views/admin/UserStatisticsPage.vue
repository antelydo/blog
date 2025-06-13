<!-- 用户统计页面 -->
<template>
  <div class="user-statistics-page">
    <el-card class="page-header">
      <h1>{{ $t('user.statistics.title') }}</h1>
      <p>{{ $t('user.statistics.description') }}</p>
    </el-card>
    
    <UserStatistics />
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import UserStatistics from '@/components/UserStatistics.vue';

export default defineComponent({
  name: 'UserStatisticsPage',
  components: {
    UserStatistics
  }
});
</script>

<style scoped>
.user-statistics-page {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h1 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 24px;
  font-weight: 500;
}

.page-header p {
  margin: 0;
  color: #606266;
}
</style> 