import api, { debounceRequest, apiUrls } from '@/api';

/**
 * 用户登录
 * @param {string} username 用户名
 * @param {string} password 密码
 * @returns {Promise} 登录结果
 */
export const login = async (username, password) => {
  try {
    const result = await api.post(apiUrls.USER_API.LOGIN, { username, password });
    return result;
  } catch (error) {
    console.error('登录失败:', error);
    throw error;
  }
};

/**
 * 搜索用户（带防抖功能）
 * @param {string} keyword 搜索关键词
 * @returns {Promise} 搜索结果
 */
export const searchUsers = debounceRequest((keyword) => {
  return api.get(apiUrls.ADMIN_API.USER_LIST, { params: { keyword } });
}, 500);

// 可以添加更多API服务方法
export default {
  login,
  searchUsers
};