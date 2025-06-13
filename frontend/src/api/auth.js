import api from './index';
import adminApi from './admin';
import config from './config';

/**
 * 管理员登录
 * @param {Object} data - 登录信息对象
 * @param {string} data.username - 用户名
 * @param {string} data.password - 密码
 * @returns {Promise} - 返回响应数据
 */
export const login = (data) => {
  return adminApi.post(config.ADMIN_API.LOGIN, data);
};

/**
 * 用户登录
 * @param {Object} data - 登录信息对象
 * @param {string} data.username - 用户名
 * @param {string} data.password - 密码
 * @returns {Promise} - 返回响应数据
 */
export const userLogin = (data) => {
  return api.post(config.AUTH_API.USER_LOGIN, data);
};

/**
 * 退出登录
 * @param {string} type - 用户类型：'admin' 或 'user'
 * @returns {Promise} - 返回响应数据
 */
export const logout = (type = 'user') => {
  const apiInstance = type === 'admin' ? adminApi : api;
  const endpoint = type === 'admin' ? config.ADMIN_API.LOGOUT : config.USER_API.LOGOUT;
  
  return apiInstance.post(endpoint);
};

export default {
  login,
  userLogin,
  logout
}; 