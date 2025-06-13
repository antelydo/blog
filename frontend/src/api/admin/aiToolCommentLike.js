/**
 * AI工具评价点赞管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取评价点赞列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getCommentLikeList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_COMMENT_LIKE_API.LIST, {
    params
  });
};

/**
 * 获取评价点赞详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getCommentLikeInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_COMMENT_LIKE_API.INFO, {
    params
  });
};

/**
 * 删除评价点赞
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteCommentLike = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_LIKE_API.DELETE, data);
};

/**
 * 批量删除评价点赞
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteCommentLike = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_LIKE_API.BATCH_DELETE, data);
};

/**
 * 获取评价点赞统计数据
 * @returns {Promise}
 */
export const getCommentLikeStatistics = () => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_COMMENT_LIKE_API.STATISTICS);
};

export default {
  getCommentLikeList,
  getCommentLikeInfo,
  deleteCommentLike,
  batchDeleteCommentLike,
  getCommentLikeStatistics
};
