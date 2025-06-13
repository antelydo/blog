import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// 引入压缩插件
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd())
  const buildTarget = process.env.VITE_BUILD_TARGET || 'app'

  const buildConfigs = {
    app: {
      outDir: 'dist',
      emptyOutDir: false,
      rollupOptions: {
        input: {
          main: resolve(__dirname, 'index.html'),
          app: resolve(__dirname, 'src/App.vue'),
        },
        external: [
          /ant-design-vue\/dist\/.*\.css/,
          /element-plus\/dist\/.*\.css/
        ],
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
      },
      // 增加警告限制，减少不必要的警告
      chunkSizeWarningLimit: 2500,
      // 优化构建速度
      reportCompressedSize: false,
      cssCodeSplit: true,
      minify: 'esbuild',
      sourcemap: false,
      assetsInlineLimit: 4096,
      cache: true
    }
  }

  const config = buildConfigs[buildTarget]
  if (!config) {
    throw new Error(`Invalid build target: ${buildTarget}`)
  }

  return {
    plugins: [
      vue(),
      // 自动导入组件
      AutoImport({
        imports: ['vue', 'vue-router', 'pinia'],
        dts: 'src/auto-imports.d.ts',
      }),
      // 自动注册组件
      Components({
        dts: true,
      }),
    ],
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src')
      }
    },
    build: {
      ...config
    },
    css: {
      postcss: './postcss.config.cjs',
      preprocessorOptions: {
        less: {
          javascriptEnabled: true,
          modifyVars: {
            'ant-prefix': 'ant'
          }
        }
      }
    },
    optimizeDeps: {
      include: [
        'vue',
        'vue-router',
        'pinia',
        'ant-design-vue',
        'element-plus'
      ]
    }
  }
})