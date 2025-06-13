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
    // 压缩CSS
    ...(process.env.NODE_ENV === 'production' ? { 'cssnano': {
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
      }],
    }} : {})
  }
}
