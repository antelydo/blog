/**
 * AI工具点赞管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取点赞列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getLikeList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_LIKE_API.LIST, {
    params
  });
};

/**
 * 获取点赞详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getLikeInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_LIKE_API.INFO, {
    params
  });
};

/**
 * 删除点赞
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteLike = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_LIKE_API.DELETE, data);
};

/**
 * 批量删除点赞
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteLike = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_LIKE_API.BATCH_DELETE, data);
};

export default {
  getLikeList,
  getLikeInfo,
  deleteLike,
  batchDeleteLike
};
