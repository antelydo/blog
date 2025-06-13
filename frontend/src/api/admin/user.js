/**
 * 用户管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

// 获取用户列表
export const getUsers = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.USER_LIST, params);
};

// 获取管理员列表
export const getAdmins = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.ADMIN_LIST, params);
};

// 获取管理员个人信息
export const getProfile = () => {
  return adminApi.post(apiUrls.ADMIN_API.INFO, {
    _t: new Date().getTime()
  });
};

// 更新用户状态
export const updateUserStatus = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.UPDATE_USER_STATUS, params);
};

// 编辑用户
export const editUser = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.EDIT_USER, params);
};

// 获取用户详情
export const getUserDetail = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.USER_DETAIL, params);
};

// 删除用户
export const deleteUser = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.DELETE_USER, params);
};

// 更新用户信息
export const updateUser = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.UPDATE_USER, params);
};

// 获取用户注册统计（30天）
export const getUserRegistrationStats = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.USER_GROWTH, {
    type: 'registration',
    days: 30,
    ...params
  });
}; 

// 获取用户活跃度统计（30天）
export const getUserActivityStats = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.ACCESS_STATS, {
    type: 'user_activity',
    days: 30,
    ...params
  });
};


 
/**
 * Get user statistics
 * @param {string} timeRange - Time range for the statistics (week, month)
 * @returns {Promise} - Promise containing user statistics data
 */
export function getUserStatistics(timeRange = 'week') {
  return adminApi.post('/admin/statistics', { 
    type: 'user',
    period: timeRange 
  });
}

/**
 * Get user growth trend data
 * @param {Object} params - Parameters for user growth data
 * @param {number} params.days - Number of days to retrieve (default: 30)
 * @param {string} params.type - Type of data to retrieve (default: undefined)
 * @returns {Promise} - Promise containing user growth trend data
 */
export function getUserGrowthTrend(params = {}) {
  return adminApi.post('/admin/userGrowth', { 
    days: params.days || 30,
    type: params.type || undefined,
    ...params
  });
}


// 更新管理员个人信息
export const updateProfile = (params) => {
  return adminApi.post(apiUrls.ADMIN_API.UPDATE, params);
};

export default {
  getUsers,
  getAdmins,
  getProfile,
  updateUserStatus,
  editUser,
  getUserDetail,
  deleteUser,
  updateUser,
  getUserRegistrationStats,
  getUserActivityStats,
  updateProfile,
  getUserStatistics,
  getUserGrowthTrend
}; 