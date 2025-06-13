/**
 * AI工具收藏管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取收藏列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getFavoriteList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_FAVORITE_API.LIST, {
    params
  });
};

/**
 * 获取收藏详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getFavoriteInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_FAVORITE_API.INFO, {
    params
  });
};

/**
 * 删除收藏
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteFavorite = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_FAVORITE_API.DELETE, data);
};

/**
 * 批量删除收藏
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteFavorite = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_FAVORITE_API.BATCH_DELETE, data);
};

export default {
  getFavoriteList,
  getFavoriteInfo,
  deleteFavorite,
  batchDeleteFavorite
};
