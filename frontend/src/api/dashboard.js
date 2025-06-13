import request from './apiService'

/**
 * Get user dashboard statistics
 * @returns {Promise} Promise object represents the statistics data
 */
export function getUserStats() {
  return request({
    url: '/api/user/statistics',
    method: 'get'
  })
}

/**
 * Get user registration trend data
 * @param {Object} params - Query parameters
 * @param {string} params.period - Time period (day, week, month, year)
 * @param {number} params.limit - Number of data points to return
 * @returns {Promise} Promise object represents the trend data
 */
export function getRegistrationTrend(params) {
  return request({
    url: '/api/user/trend/registration',
    method: 'get',
    params
  })
}

/**
 * Get user activity trend data
 * @param {Object} params - Query parameters
 * @param {string} params.period - Time period (day, week, month, year)
 * @param {number} params.limit - Number of data points to return
 * @returns {Promise} Promise object represents the trend data
 */
export function getActivityTrend(params) {
  return request({
    url: '/api/user/trend/activity',
    method: 'get',
    params
  })
}

export default {
  getUserStats,
  getRegistrationTrend,
  getActivityTrend
} 