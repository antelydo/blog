import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// 环境变量
const isProduction = process.env.NODE_ENV === 'production'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  build: {
    sourcemap: false,
    minify: 'esbuild',
    emptyOutDir: false,
    // 优化构建速度
    reportCompressedSize: false,
    // 启用构建缓存
    cache: true,
    lib: {
      entry: resolve(__dirname, 'src/vendor.js'),
      formats: ['es'],
      fileName: 'vendor'
    },
    rollupOptions: {
      output: {
        manualChunks: {
          // 核心库
          'vue-core': ['vue', 'vue-router', 'pinia', 'vue-i18n'],
          // UI库
          'element-plus': ['element-plus', '@element-plus/icons-vue'],
          'ant-design': ['ant-design-vue', '@ant-design/icons-vue'],
          // 工具库
          'utils': ['axios', 'crypto-js', 'uuid'],
          // 图表库
          'charts': ['chart.js', 'echarts', 'vue-chartjs'],
          // 编辑器
          'editors': ['@toast-ui/editor', '@wangeditor/editor', '@wangeditor/editor-for-vue'],
          // 其他大型依赖
          'xlsx': ['xlsx']
        },
        // 优化输出文件名
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[ext]/[name]-[hash].[ext]'
      }
    }
  }
})