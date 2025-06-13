import request from './index';
import config from './config';

/**
 * 搜索内容
 * @param {Object} params 搜索参数
 * @param {string} params.query 搜索关键词
 * @param {number} params.page 页码
 * @param {number} params.limit 每页数量
 * @returns {Promise} 搜索结果
 */
export function search(params) {
  return request({
    url: config.BLOG_API.SEARCH,
    method: 'get',
    params
  });
} 