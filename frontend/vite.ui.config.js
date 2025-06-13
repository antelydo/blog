import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  build: {
    sourcemap: false,
    minify: 'esbuild',
    emptyOutDir: false,
    outDir: 'dist',
    lib: {
      entry: resolve(__dirname, 'src/components/index.js'),
      formats: ['es'],
      fileName: 'ui'
    },
    rollupOptions: {
      external: ['vue', 'element-plus', '@element-plus/icons-vue'],
      output: {
        manualChunks: {
          'element-plus': ['element-plus'],
          'element-icons': ['@element-plus/icons-vue']
        }
      }
    }
  }
})