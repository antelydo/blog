/**
 * AI工具标签管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取标签列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getToolTagList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_TAG_API.LIST, {
    params
  });
};

/**
 * 获取所有标签（不分页）
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getAllToolTags = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_TAG_API.ALL, {
    params
  });
};

/**
 * 获取标签详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getToolTagInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_TAG_API.INFO, {
    params
  });
};

/**
 * 创建标签
 * @param {Object} data 标签数据
 * @returns {Promise}
 */
export const createToolTag = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.CREATE, data);
};

/**
 * 更新标签
 * @param {Object} data 标签数据
 * @returns {Promise}
 */
export const updateToolTag = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.UPDATE, data);
};

/**
 * 删除标签
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteToolTag = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.DELETE, data);
};

/**
 * 批量删除标签
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteToolTag = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.BATCH_DELETE, data);
};

/**
 * 合并标签
 * @param {Object} data 包含source_ids和target_id的对象
 * @returns {Promise}
 */
export const mergeToolTags = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.MERGE, data);
};

/**
 * 更新标签显示状态
 * @param {Object} data 包含id和is_show的对象
 * @returns {Promise}
 */
export const updateToolTagStatus = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_TAG_API.UPDATE_STATUS, data);
};

export default {
  getToolTagList,
  getAllToolTags,
  getToolTagInfo,
  createToolTag,
  updateToolTag,
  deleteToolTag,
  batchDeleteToolTag,
  mergeToolTags,
  updateToolTagStatus
};
