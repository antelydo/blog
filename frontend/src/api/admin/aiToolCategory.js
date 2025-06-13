/**
 * AI工具分类管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取分类列表
 * @returns {Promise}
 */
export const getToolCategoryList = () => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.LIST);
};

/**
 * 获取分类详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getToolCategoryInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.INFO, {
    params
  });
};

/**
 * 创建分类
 * @param {Object} data 分类数据
 * @returns {Promise}
 */
export const createToolCategory = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.CREATE, data);
};

/**
 * 更新分类
 * @param {Object} data 分类数据
 * @returns {Promise}
 */
export const updateToolCategory = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.UPDATE, data);
};

/**
 * 删除分类
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteToolCategory = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.DELETE, data);
};

/**
 * 更新分类显示状态
 * @param {Object} data 包含id和is_show的对象
 * @returns {Promise}
 */
export const updateToolCategoryStatus = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_CATEGORY_API.UPDATE_STATUS, data);
};

export default {
  getToolCategoryList,
  getToolCategoryInfo,
  createToolCategory,
  updateToolCategory,
  deleteToolCategory,
  updateToolCategoryStatus
};
