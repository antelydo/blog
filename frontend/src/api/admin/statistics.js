/**
 * 统计数据相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

// 获取基础统计数据
export const getStatistics = () => {
  return adminApi.post(apiUrls.ADMIN_API.STATISTICS);
};

// 获取用户增长趋势数据
export const getUserGrowthTrend = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.USER_GROWTH, params);
};

// 获取系统访问统计数据
export const getVisitStatistics = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.ACCESS_STATS, params);
};

export default {
  getStatistics,
  getUserGrowthTrend,
  getVisitStatistics
};