/**
 * 优化的PostCSS配置
 * 提高CSS处理性能，减少打包体积
 */

module.exports = {
  plugins: {
    // 处理嵌套规则
    'postcss-nested': {},
    
    // 添加浏览器前缀
    'autoprefixer': {
      overrideBrowserslist: ['last 2 versions', '> 1%', 'not dead']
    },
    
    // 处理自定义属性
    'postcss-custom-properties': {
      preserve: false
    },
    
    // 压缩CSS - 仅在生产环境中启用
    ...(process.env.NODE_ENV === 'production' ? { 
      'cssnano': {
        preset: ['default', {
          // 移除注释
          discardComments: {
            removeAll: true,
          },
          // 合并重复规则
          mergeRules: true,
          // 移除重复规则
          discardDuplicates: true,
          // 移除空规则
          discardEmpty: true,
          // 优化选择器
          minifySelectors: true,
          // 优化字体权重
          minifyFontValues: true,
          // 规范化URL
          normalizeUrl: true,
          // 优化渐变
          reduceTransforms: true,
          // 合并长度值
          mergeLonghand: true,
          // 优化边距和填充
          discardOverridden: true,
          // 减少位置值
          reducePositions: true,
          // 减少初始值
          normalizeWhitespace: true,
          // 优化过滤器
          minifyParams: true,
          // 优化计算值
          calc: true,
          // 优化颜色值
          colormin: true,
          // 转换值
          convertValues: true,
          // 丢弃未使用的@规则
          discardUnused: true,
          // 合并相邻规则
          mergeIdents: true,
          // 减少变换
          reduceIdents: true,
          // 使用简写属性
          mergeIdents: true,
          // Z-index优化
          zindex: false, // 禁用z-index优化，避免破坏层叠顺序
        }],
      }
    } : {}),
    
    // 优化媒体查询 - 仅在生产环境中启用
    ...(process.env.NODE_ENV === 'production' ? {
      'postcss-sort-media-queries': {
        sort: 'mobile-first' // 使用移动优先策略排序媒体查询
      }
    } : {}),
    
    // 优化@import - 仅在生产环境中启用
    ...(process.env.NODE_ENV === 'production' ? {
      'postcss-import': {
        // 允许@import内容内联
        path: ['src/styles']
      }
    } : {})
  }
};
