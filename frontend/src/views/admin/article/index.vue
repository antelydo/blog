<!-- 文章管理 -->
<template>
  <div class="article-management">
    <router-view></router-view>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'ArticleManagement'
});
</script>

<style scoped>
.article-management {
  width: 100%;
  height: 100%;
}
</style> 