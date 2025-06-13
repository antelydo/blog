// 导入 Element Plus 样式
// 使用相对路径导入，避免路径解析问题
try {
  // 使用相对路径导入公共目录中的CSS
  const cssUrl = new URL('/css/element-plus.css', window.location.origin).href;

  // 创建link元素并添加到head
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = cssUrl;

  // 添加到文档头部
  document.head.appendChild(link);

  console.log('Element Plus 样式已加载:', cssUrl);
} catch (e) {
  console.error('导入Element Plus样式时发生错误:', e);
}

// 导出一个空对象，以便可以在其他文件中导入
export default {}
