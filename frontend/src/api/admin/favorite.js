/**
 * 文章收藏管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取收藏列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getFavorites = (params) => {
  return adminApi.get(apiUrls.ADMIN_FAVORITE_API.FAVORITE_LIST, {
    params
  });
};

/**
 * 删除收藏
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteFavorite = (data) => {
  return adminApi.post(apiUrls.ADMIN_FAVORITE_API.FAVORITE_DELETE, data);
};

/**
 * 获取收藏统计数据
 * @returns {Promise}
 */
export const getFavoriteStats = () => {
  return adminApi.get(apiUrls.ADMIN_FAVORITE_API.FAVORITE_STATS);
};

export default {
  getFavorites,
  deleteFavorite,
  getFavoriteStats
};
