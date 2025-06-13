/**
 * Vite CSS优化配置
 * 用于优化CSS文件的打包和加载
 */

import { resolve } from 'path'

// CSS优化配置
export const cssOptimizationConfig = {
  // CSS代码分割配置
  cssCodeSplit: true,
  
  // CSS预处理器配置
  css: {
    // 使用优化的PostCSS配置
    postcss: './postcss.optimized.cjs',
    
    // 预处理器选项
    preprocessorOptions: {
      // SCSS配置
      scss: {
        // 导入全局变量
        additionalData: `@import "@/styles/_variables.scss";`,
      },
      // Less配置
      less: {
        javascriptEnabled: true,
        modifyVars: {
          'ant-prefix': 'ant'
        }
      }
    },
    
    // 开发环境中的CSS源码映射
    devSourcemap: true
  },
  
  // 构建优化配置
  build: {
    // CSS相关的构建配置
    cssCodeSplit: true,
    cssTarget: 'es2015',
    
    // 输出配置
    rollupOptions: {
      output: {
        // 将CSS文件分组
        assetFileNames: (assetInfo) => {
          // 根据文件类型和名称决定输出路径
          if (assetInfo.name.endsWith('.css')) {
            // 主题相关CSS
            if (assetInfo.name.includes('theme')) {
              return 'assets/css/theme-[hash].css';
            }
            // 管理后台相关CSS
            else if (assetInfo.name.includes('admin')) {
              return 'assets/css/admin-[hash].css';
            }
            // 基础CSS
            else {
              return 'assets/css/base-[hash].css';
            }
          }
          // 其他资源
          return 'assets/[ext]/[name]-[hash].[ext]';
        }
      }
    }
  }
};

// 导出默认配置
export default cssOptimizationConfig;
