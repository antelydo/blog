<!-- 基础组件Mixin，为所有组件提供主题相关功能 -->
<script>
export default {
  name: 'BaseComponentMixin',
  inject: {
    // 尝试注入由App.vue提供的主题变更计数器
    // 如果不存在则使用本地默认值
    themeChanged: {
      default: () => ({ value: 0 })
    }
  },
  data() {
    return {
      componentKey: 0,
      lastThemeChanged: 0
    }
  },
  watch: {
    // 监听主题变更，强制组件重新渲染
    themeChanged: {
      handler(newVal) {
        if (newVal !== this.lastThemeChanged) {
          this.lastThemeChanged = newVal
          this.refreshComponent()
        }
      },
      immediate: true
    }
  },
  methods: {
    // 刷新组件的方法
    refreshComponent() {
      this.componentKey++
      this.$nextTick(() => {
        // 强制应用当前主题样式
        const root = this.$el
        if (root) {
          // 尝试获取当前主题
          const isDarkTheme = document.documentElement.classList.contains('dark-theme') || 
                             (document.documentElement.classList.contains('system-dark-theme'))
          
          // 应用主题相关类名
          if (isDarkTheme) {
            root.classList.add('theme-force-update-dark')
            setTimeout(() => {
              root.classList.remove('theme-force-update-dark')
            }, 50)
          } else {
            root.classList.add('theme-force-update-light')
            setTimeout(() => {
              root.classList.remove('theme-force-update-light')
            }, 50)
          }
        }
      })
    }
  }
}
</script>

<style scoped>
/* 强制更新样式类 */
:deep(.theme-force-update-dark) {
  background-color: var(--el-bg-color, #232324) !important;
  color: var(--el-text-color-primary, #e0e0e0) !important;
  transition: none !important;
}

:deep(.theme-force-update-light) {
  background-color: var(--el-bg-color, #ffffff) !important;
  color: var(--el-text-color-primary, #303133) !important;
  transition: none !important;
}
</style> 