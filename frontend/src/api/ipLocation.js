/**
 * IP地理位置查询API
 */
import api from './index';
import { apiUrls } from './index';

/**
 * 查询单个IP地理位置
 * @param {string} ip IP地址，如果不提供则查询当前用户IP
 * @returns {Promise} 返回包含地理位置信息的Promise
 */
export const queryIpLocation = (ip = '') => {
  return api.get( apiUrls.IP_API.QUERY, {
    params: { ip }
  });
};

/**
 * 批量查询IP地理位置
 * @param {Array<string>} ipList IP地址列表
 * @returns {Promise} 返回包含地理位置信息的Promise
 */
export const batchQueryIpLocation = (ipList) => {
  return api.post(apiUrls.IP_API.BATCH_QUERY, {
    ip_list: ipList
  });
};

/**
 * 格式化地理位置信息
 * @param {Object} locationData 地理位置数据
 * @returns {string} 格式化后的地理位置文本
 */
export const formatLocation = (locationData) => {
  if (!locationData || !locationData.data) {
    return '未知位置';
  }

  const { country, regionName, city } = locationData.data;
  let location = '';

  if (country) location += country;
  if (regionName && regionName !== country) location += ` ${regionName}`;
  if (city) location += ` ${city}`;

  return location || '未知位置';
};

export default {
  queryIpLocation,
  batchQueryIpLocation,
  formatLocation
};
