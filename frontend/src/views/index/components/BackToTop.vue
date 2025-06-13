<!---返回顶部-->
<template>
  <div>
    <!-- 用于锚点滚动的辅助元素 -->
    <div id="page-top"></div>

    <div class="back-to-top" @click="scrollToTop" aria-label="Back to top" :class="{ 'clicking': isClicking }">
      <i class="fa fa-arrow-up"></i>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BackToTop',
  data() {
    return {
      showButton: true,
      isClicking: false
    }
  },
  mounted() {
    console.log('BackToTop component mounted');
    // 确保顶部锚点元素存在
    this.ensureTopElement();
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    handleScroll() {
      this.showButton = true;
    },

    // 确保顶部元素存在于DOM中
    ensureTopElement() {
      let topElement = document.getElementById('page-top');
      if (!topElement) {
        topElement = document.createElement('div');
        topElement.id = 'page-top';
        document.body.prepend(topElement);
      }
    },

    scrollToTop() {
      console.log('ScrollToTop clicked - current scroll position:', window.scrollY);

      // 添加点击视觉反馈
      this.isClicking = true;
      setTimeout(() => {
        this.isClicking = false;
      }, 300);

      // 确保顶部元素存在
      this.ensureTopElement();

      // 方法1: 使用scrollIntoView
      try {
        const topElement = document.getElementById('page-top');
        if (topElement) {
          topElement.scrollIntoView({ behavior: 'smooth' });
          console.log('Used scrollIntoView method');
          return;
        }
      } catch (err) {
        console.error('scrollIntoView failed:', err);
      }

      // 方法2: 使用简单设置
      try {
        window.scrollTo(0, 0);
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
        console.log('Used direct scrollTo method');
        return;
      } catch (err) {
        console.error('Direct scroll failed:', err);
      }

      // 方法3: jQuery方法模拟
      try {
        $('html, body').animate({ scrollTop: 0 }, 'slow');
        console.log('Used jQuery-style animation');
        return;
      } catch (err) {
        console.log('jQuery method not available');
      }

      // 方法4: 原生动画 - 最后尝试
      try {
        const duration = 500; // ms
        const start = window.pageYOffset;
        const startTime = performance.now();

        function step(timestamp) {
          const elapsed = timestamp - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const ease = t => t * (2 - t); // 简单的easeOut函数

          window.scrollTo(0, start * (1 - ease(progress)));

          if (progress < 1) {
            window.requestAnimationFrame(step);
          }
        }

        window.requestAnimationFrame(step);
        console.log('Used native animation method');
      } catch (err) {
        console.error('Animation method failed:', err);

        // 最后的备用方案
        window.scrollTo(0, 0);
      }
    }
  }
}
</script>

<style scoped>
#page-top {
  position: absolute;
  top: 0;
  left: 0;
  width: 1px;
  height: 1px;
}

.back-to-top {
  position: fixed;
  right: 20px;
  bottom: 20px;
  width: 50px;
  height: 50px;
  background-color: rgba(0, 153, 204, 1);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  z-index: 9999;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
}

/* 移动端样式调整，避免与底部导航栏重叠 */
@media (max-width: 768px) {
  .back-to-top {
    bottom: 70px; /* 为底部导航栏留出空间 */
    right: 15px;
    width: 40px;
    height: 40px;
  }

  .back-to-top i {
    font-size: 20px;
  }
}

.back-to-top:hover {
  background-color: #007fad;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

.back-to-top.clicking {
  transform: scale(0.95);
  background-color: #006699;
}

.back-to-top i {
  color: white;
  font-size: 24px;
}
</style>