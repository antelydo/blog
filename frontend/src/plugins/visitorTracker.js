/**
 * 访客追踪插件
 * 用于初始化和管理访客唯一标识
 */
import { initVisitorId, getCurrentVisitorId } from '@/utils/visitorId';

export default {
  /**
   * 安装插件
   * @param {Object} app Vue应用实例
   * @param {Object} options 插件选项
   */
  install: (app, options = {}) => {
    // 在Vue实例上添加访客ID方法
    app.config.globalProperties.$visitorId = {
      /**
       * 获取当前访客ID
       * @returns {String|null} 访客ID或null
       */
      get: () => getCurrentVisitorId(),

      /**
       * 初始化访客ID
       * @returns {String} 访客ID
       */
      init: () => initVisitorId()
    };

    // 将访客ID方法添加到provide中，便于组合式API使用
    app.provide('visitorId', {
      get: getCurrentVisitorId,
      init: initVisitorId
    });

    // 自动初始化访客ID，但延迟执行以避免与其他初始化冲突
    setTimeout(() => {
      try {
        // 先清除localStorage中可能存在的无效数据
        try {
          const storedId = localStorage.getItem('uuid');
          if (storedId && typeof storedId === 'string' && storedId.includes('Malformed')) {
            localStorage.removeItem('uuid');
            localStorage.removeItem('a_uuid');
            console.log('已清除无效的访客ID');
          }
        } catch (clearError) {
          console.warn('清除无效访客ID失败:', clearError);
        }

        // 初始化访客ID
        const visitorId = initVisitorId();
        console.log('访客ID初始化完成');
      } catch (error) {
        console.error('访客ID初始化失败:', error);
      }
    }, 1000); // 延迟1秒执行，避免与页面其他初始化冲突
  }
};
