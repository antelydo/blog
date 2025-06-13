// C端用户相关接口
import api from './index';
import { apiUrls } from './index';
/**
 * Get userinfo
 * @returns {Promise}
 */
export function getUserInfo() {
  return api.get(apiUrls.USER_API.INFO);
}

/**
 * Get user list
 * @param {Object} params - Query parameters
 * @returns {Promise}
 */
export function getUserList(params) {
    return api.get(apiUrls.USER_API.LIST, params);
}

/**
 * Get user detail by id
 * @param {string|number} id - User ID
 * @returns {Promise}
 */
export function getUserDetail(id) {
  return api.get(apiUrls.USER_API.INFO, { id });
}

/**
 * Create new user
 * @param {Object} data - User data
 * @returns {Promise}
 */
export function createUser(data) {
  return api.post(apiUrls.USER_API.CREATE, data);
}

/**
 * Update user
 * @param {Object} data - User data to update
 * @returns {Promise}
 */
export function updateUser(data) {
  return api.post(apiUrls.USER_API.UPDATE, data);
}

/**
 * Delete user
 * @param {string|number} id - User ID
 * @returns {Promise}
 */
export function deleteUser(id) {
  return api.delete(apiUrls.USER_API.DELETE, { id });

}

/**
 * Change user status
 * @param {string|number} id - User ID
 * @param {string} status - New status
 * @returns {Promise}
 */
export function changeUserStatus(id, status) {
  return api.post(apiUrls.USER_API.STATUS, { id, status });
}

/**
 * Update user avatar
 * @param {Object} data - FormData containing the avatar file
 * @returns {Promise}
 */
export function updateUserAvatar(data) {
  return api.post(apiUrls.USER_API.UPLOAD_AVATAR, data, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });
}

/**
 * Get user login logs
 * @param {number} page - Page number
 * @param {number} limit - Items per page
 * @returns {Promise}
 */
export function getLoginLogs(page = 1, limit = 10) {
  return api.get(apiUrls.USER_API.LOGIN_LOGS, {
    params: { page, limit }
  });
}

/**
 * Get user login statistics
 * @returns {Promise}
 */
export function getLoginStats() {
  return api.get(apiUrls.USER_API.LOGIN_STATS);
}

/**
 * Update user password
 * @param {Object} data - FormData containing the password data
 * @returns {Promise}
 */
export function updateUserPassword(data) {
  return api.post(apiUrls.USER_API.CHANGE_PASSWORD, data);
}
