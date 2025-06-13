import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// 环境变量
const isProduction = process.env.NODE_ENV === 'production'

export default defineConfig(({ mode }) => {
  // 加载环境变量
  const env = loadEnv(mode, process.cwd())
  const apiUrl = env.VITE_APP_API_URL || 'http://127.0.0.1:8000'

  return {
  plugins: [
    vue(),
    // 可以在这里添加其他插件，如压缩插件等
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  // 添加开发服务器配置，包括代理
  server: {
    host: '0.0.0.0',
    port: 5174,
    strictPort: false,
    cors: true,
    proxy: {
      // 代理所有 /api 请求到后端服务器
      '/api': {
        target: apiUrl,
        changeOrigin: true,
        secure: false,
        // 启用 WebSocket 代理
        ws: true,
        // 配置代理如何处理 cookie
        configure: (proxy, options) => {
          // 代理会自动处理 cookie
          proxy.on('proxyReq', (proxyReq, req, res) => {
            // 保留原始 cookie
            if (req.headers.cookie) {
              proxyReq.setHeader('Cookie', req.headers.cookie);
            }
          });

          // 确保响应中的 Set-Cookie 被正确传递
          proxy.on('proxyRes', (proxyRes, req, res) => {
            const setCookie = proxyRes.headers['set-cookie'];
            if (setCookie) {
              // 确保 Set-Cookie 头被保留
              res.setHeader('Access-Control-Expose-Headers', 'Set-Cookie');
            }
          });
        }
      }
    }
  },
  build: {
    // 禁用源码映射以减少内存使用
    sourcemap: false,
    // 使用 esbuild 压缩，更快且内存占用更少
    minify: 'esbuild',
    // esbuild 压缩配置
    target: 'es2015',
    cssCodeSplit: true,
    assetsInlineLimit: 4096,
    // 增加警告限制，减少不必要的警告
    chunkSizeWarningLimit: 2500,
    // 优化构建速度
    reportCompressedSize: false,
    // 启用构建缓存
    cache: true,
    rollupOptions: {
      output: {
        // 更细粒度的代码分割
        manualChunks: {
          // 核心库
          'vue-core': ['vue', 'vue-router', 'pinia'],
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
}})
