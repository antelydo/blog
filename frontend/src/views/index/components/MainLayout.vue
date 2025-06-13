<!-- 主布局组件 -->
<template>
  <div class="blog-container">
    <Header />
    <main class="main-content">
      <slot></slot>
    </main>
    <Footer />
    <BackToTop />
    <!-- 移动端导航 -->
    <MobileNavigation />
    <!-- 访客ID显示组件，默认不显示，可以在开发环境中设置为true进行测试 -->
    <VisitorIdDisplay :showVisitorId="false" />
  </div>
</template>

<script>
import Header from './Header.vue';
import Footer from './Footer.vue';
import BackToTop from './BackToTop.vue';
import MobileNavigation from './MobileNavigation.vue';
import VisitorIdDisplay from '@/components/common/VisitorIdDisplay.vue';

export default {
  name: 'MainLayout',
  components: {
    Header,
    Footer,
    BackToTop,
    MobileNavigation,
    VisitorIdDisplay
  }
}
</script>

<style scoped>
.blog-container {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding-bottom: 20px;
}

@media (max-width: 768px) {
  .main-content {
    padding-bottom: 76px; /* 为底部导航栏留出空间 */
  }
}
</style>
