/**
 * AI工具评论管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取评论列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getToolCommentList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_COMMENT_API.LIST, {
    params
  });
};

/**
 * 获取评论详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getTooCommentInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_COMMENT_API.INFO, {
    params
  });
};

/**
 * 回复评论
 * @param {Object} data 包含parent_id和content的对象
 * @returns {Promise}
 */
export const replyToolComment = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_API.REPLY, data);
};

/**
 * 更新评论状态
 * @param {Object} data 包含id和status的对象
 * @returns {Promise}
 */
export const updateToolCommentStatus = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_API.UPDATE_STATUS, data);
};

/**
 * 批量更新评论状态
 * @param {Object} data 包含ids和status的对象
 * @returns {Promise}
 */
export const batchUpdateToolCommentStatus = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_API.BATCH_UPDATE_STATUS, data);
};

/**
 * 删除评论
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteToolComment = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_API.DELETE, data);
};

/**
 * 批量删除评论
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteToolComment = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_COMMENT_API.BATCH_DELETE, data);
};

export default {
  getToolCommentList,
  getTooCommentInfo,
  replyToolComment,
  updateToolCommentStatus,
  batchUpdateToolCommentStatus,
  deleteToolComment,
  batchDeleteToolComment
};
