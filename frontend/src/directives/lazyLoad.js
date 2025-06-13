/**
 * 图片懒加载指令
 * 使用方法：v-lazy="图片URL"
 */

export default {
  mounted(el, binding) {
    // 创建一个IntersectionObserver实例
    const observer = new IntersectionObserver((entries) => {
      // 当被观察的元素进入视口时
      if (entries[0].isIntersecting) {
        // 设置图片的src属性为绑定的值
        el.src = binding.value;
        // 图片加载完成后，停止观察
        el.addEventListener('load', () => {
          observer.unobserve(el);
        });
        // 图片加载失败时，也停止观察
        el.addEventListener('error', () => {
          observer.unobserve(el);
          // 可以设置一个默认的错误图片
          el.src = '/placeholder.png';
        });
      }
    });
    
    // 开始观察元素
    observer.observe(el);
    
    // 保存observer实例，以便在组件卸载时清理
    el._lazy_observer = observer;
  },
  
  // 当指令的值更新时
  updated(el, binding) {
    // 如果值发生变化，更新图片的data-src属性
    if (binding.oldValue !== binding.value) {
      el.dataset.src = binding.value;
    }
  },
  
  // 当指令所在组件卸载时
  unmounted(el) {
    // 清理observer，避免内存泄漏
    if (el._lazy_observer) {
      el._lazy_observer.unobserve(el);
      delete el._lazy_observer;
    }
  }
}
