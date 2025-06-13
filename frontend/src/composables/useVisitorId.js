/**
 * 访客ID组合式API
 * 用于在组件中使用访客ID
 */
import { ref, inject, onMounted } from 'vue';
import { initVisitorId, getCurrentVisitorId } from '@/utils/visitorId';

/**
 * 使用访客ID
 * @returns {Object} 访客ID相关方法和状态
 */
export function useVisitorId() {
  // 尝试从provide/inject获取访客ID服务
  const injectedVisitorId = inject('visitorId', null);

  // 访客ID状态
  const visitorId = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  /**
   * 初始化访客ID
   * @returns {String} 访客ID
   */
  const initialize = () => {
    if (isLoading.value) return visitorId.value;

    isLoading.value = true;
    error.value = null;

    try {
      // 如果有注入的服务，使用注入的服务
      if (injectedVisitorId) {
        visitorId.value = injectedVisitorId.init();
      } else {
        // 否则直接使用工具函数
        visitorId.value = initVisitorId();
      }
      return visitorId.value;
    } catch (err) {
      error.value = err;
      console.error('初始化访客ID失败:', err);
      return null;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * 获取当前访客ID
   * @returns {String|null} 访客ID或null
   */
  const getVisitorId = () => {
    if (visitorId.value) return visitorId.value;

    // 如果有注入的服务，使用注入的服务
    if (injectedVisitorId) {
      return injectedVisitorId.get();
    }

    // 否则直接使用工具函数
    return getCurrentVisitorId();
  };

  // 组件挂载时自动初始化
  onMounted(() => {
    // 先尝试获取现有ID
    const currentId = getVisitorId();
    if (currentId) {
      visitorId.value = currentId;
    } else {
      // 如果没有，则初始化
      initialize();
    }
  });

  return {
    visitorId,
    isLoading,
    error,
    initialize,
    getVisitorId
  };
}
