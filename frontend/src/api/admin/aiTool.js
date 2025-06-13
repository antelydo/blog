/**
 * AI工具管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取工具列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getToolList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_API.LIST, {
    params
  });
};

/**
 * 获取工具详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getToolInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_API.INFO, {
    params
  });
};

/**
 * 创建工具
 * @param {Object} data 工具数据
 * @returns {Promise}
 */
export const createTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.CREATE, data);
};

/**
 * 更新工具
 * @param {Object} data 工具数据
 * @returns {Promise}
 */
export const updateTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.UPDATE, data);
};

/**
 * 删除工具
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.DELETE, data);
};

/**
 * 更新工具推荐状态
 * @param {Object} data 包含id和is_recommended的对象
 * @returns {Promise}
 */
export const recommendTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.RECOMMEND, data);
};

/**
 * 更新工具置顶状态
 * @param {Object} data 包含id和is_top的对象
 * @returns {Promise}
 */
export const topTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.TOP, data);
};

/**
 * 更新工具状态
 * @param {Object} data 包含id和status的对象
 * @returns {Promise}
 */
export const updateToolStatus = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.UPDATE_STATUS, data);
};

/**
 * 批量操作工具
 * @param {Object} data 包含ids和action的对象
 * @returns {Promise}
 */
export const batchTool = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_API.BATCH, data);
};

export default {
  getToolList,
  getToolInfo,
  createTool,
  updateTool,
  deleteTool,
  recommendTool,
  topTool,
  updateToolStatus,
  batchTool
};
