/**
 * AI工具访问日志管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

/**
 * 获取访问日志列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getVisitLogList = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.LIST, {
    params
  });
};

/**
 * 获取访问日志详情
 * @param {Object} params 包含id的对象
 * @returns {Promise}
 */
export const getVisitLogInfo = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.INFO, {
    params
  });
};

/**
 * 获取访问统计数据
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getVisitStats = (params) => {
  return adminApi.get(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.STATS, {
    params
  });
};

/**
 * 删除访问日志
 * @param {Object} data 包含id的对象
 * @returns {Promise}
 */
export const deleteVisitLog = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.DELETE, data);
};

/**
 * 批量删除访问日志
 * @param {Object} data 包含ids的对象
 * @returns {Promise}
 */
export const batchDeleteVisitLog = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.BATCH_DELETE, data);
};

/**
 * 清空访问日志
 * @param {Object} data 包含tool_id和days的对象
 * @returns {Promise}
 */
export const clearVisitLog = (data) => {
  return adminApi.post(apiUrls.ADMIN_AI_TOOL_VISIT_LOG_API.CLEAR, data);
};

export default {
  getVisitLogList,
  getVisitLogInfo,
  getVisitStats,
  deleteVisitLog,
  batchDeleteVisitLog,
  clearVisitLog
};
