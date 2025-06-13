/**
 * 点赞功能组合式API
 * 提供点赞和取消点赞的功能
 */
import { ref, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { useI18n } from 'vue-i18n';
import { likePost, unlikePost } from '@/api/like';
import { useUserStore } from '@/stores/user';

/**
 * 使用点赞功能
 * @returns {Object} 点赞相关方法和状态
 */
export function useLike() {
  const { t } = useI18n();
  const userStore = useUserStore();
  const likeLoadingMap = ref({});
  
  // 检查用户是否已登录
  const isLoggedIn = computed(() => {
    const hasToken = !!userStore.token;
    const hasUserInfo = !!userStore.userInfo;
    return hasToken && hasUserInfo;
  });
  

  
  /**
   * 切换点赞状态
   * @param {Object} post 文章对象
   * @param {Function} onSuccess 成功回调函数
   * @returns {Promise<void>}
   */
  const toggleLike = async (post, onSuccess = null) => {
    // 检查是否正在加载
    if (likeLoadingMap.value[post.id]) return;
    
    // 设置加载状态
    likeLoadingMap.value[post.id] = true;
    
    try {
      // 准备请求数据
      const requestData = { post_id: post.id };

      
      let response;
      
      if (post.is_liked) {
        // 取消点赞
        response = await unlikePost(requestData);
        if (response && response.code === 200) {
          post.is_liked = false;
          post.likes = Math.max(0, post.likes - 1);
          ElMessage.success(t('posts.unlikeSuccess'));
          if (onSuccess) onSuccess(post);
        } else {
          ElMessage.error(response?.msg || t('posts.unlikeError'));
        }
      } else {
        // 点赞
        response = await likePost(requestData);
        if (response && response.code === 200) {
          post.is_liked = true;
          post.likes++;
          ElMessage.success(t('posts.likeSuccess'));
          if (onSuccess) onSuccess(post);
        } else {
          ElMessage.error(response?.msg || t('posts.likeError'));
        }
      }
    } catch (error) {
      console.error('点赞操作失败:', error);
      ElMessage.error(t('posts.likeError'));
    } finally {
      // 清除加载状态
      delete likeLoadingMap.value[post.id];
    }
  };
  
  /**
   * 检查特定文章是否正在加载点赞状态
   * @param {Number|String} postId 文章ID
   * @returns {Boolean} 是否正在加载
   */
  const isLikeLoading = (postId) => {
    return !!likeLoadingMap.value[postId];
  };
  
  return {
    toggleLike,
    isLikeLoading,
    likeLoadingMap,
    isLoggedIn
  };
}
