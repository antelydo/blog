/**
 * IP地理位置查询API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';


/**
 * 管理员查询单个IP地理位置（需要管理员权限）
 * @param {string} ip IP地址，如果不提供则查询当前用户IP
 * @returns {Promise} 返回包含地理位置信息的Promise
 */
export const adminQueryIpLocation = (ip = '') => {
  return adminApi.get(apiUrls.IP_API.ADMIN_QUERY, {
    params: { ip }
  });
};

/**
 * 管理员批量查询IP地理位置（需要管理员权限）
 * @param {Array<string>} ipList IP地址列表
 * @returns {Promise} 返回包含地理位置信息的Promise
 */
export const adminBatchQueryIpLocation = (ipList) => {
  return adminApi.post(apiUrls.IP_API.ADMIN_BATCH_QUERY, {
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
  adminQueryIpLocation,
  adminBatchQueryIpLocation,
  formatLocation
};
