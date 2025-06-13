/**
 * IP地理位置查询工具
 * 使用IP-API服务 (https://ip-api.com/)
 */
import axios from 'axios';

// IP-API服务地址
const IP_API_URL = 'http://ip-api.com/json/';

/**
 * 查询IP地理位置信息
 * @param {string} ip IP地址，如果不提供则查询当前用户IP
 * @returns {Promise} 返回包含地理位置信息的Promise
 */
export const getIpLocation = async (ip = '') => {
  try {
    console.log(IP_API_URL,'responseresponseresponse');
    // 构建请求URL
    const url = `${IP_API_URL}${ip}?lang=zh-CN`;

    // 发送请求
    const response = await axios.get(url);
    console.log(response,'responseresponseresponse');
    // 检查响应状态
    if (response.data && response.data.status === 'success') {
      return {
        success: true,
        data: {
          ip: response.data.query,
          country: response.data.country,
          countryCode: response.data.countryCode,
          region: response.data.regionName,
          regionCode: response.data.region,
          city: response.data.city,
          zip: response.data.zip,
          lat: response.data.lat,
          lon: response.data.lon,
          timezone: response.data.timezone,
          isp: response.data.isp,
          org: response.data.org,
          as: response.data.as
        }
      };
    } else {
      return {
        success: false,
        message: '查询失败',
        data: response.data
      };
    }
  } catch (error) {
    console.error('IP地理位置查询失败:', error);
    return {
      success: false,
      message: error.message || '查询失败',
      error
    };
  }
};

/**
 * 批量查询IP地理位置信息
 * 注意：免费版API有请求频率限制，建议使用付费版或添加延迟
 * @param {Array<string>} ipList IP地址列表
 * @param {number} delay 请求间隔延迟(毫秒)
 * @returns {Promise<Array>} 返回包含地理位置信息的数组
 */
export const batchGetIpLocation = async (ipList, delay = 1000) => {
  const results = [];

  for (const ip of ipList) {
    // 查询单个IP
    const result = await getIpLocation(ip);
    results.push({
      ip,
      ...result
    });

    // 添加延迟，避免请求过于频繁
    if (delay > 0 && ipList.indexOf(ip) < ipList.length - 1) {
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }

  return results;
};

/**
 * 格式化地理位置信息为显示文本
 * @param {Object} locationData 地理位置数据
 * @returns {string} 格式化后的地理位置文本
 */
export const formatLocation = (locationData) => {
  if (!locationData || !locationData.success) {
    return '未知位置';
  }

  const { country, region, city } = locationData.data;
  let location = '';

  if (country) location += country;
  if (region && region !== country) location += ` ${region}`;
  if (city) location += ` ${city}`;

  return location || '未知位置';
};

export default {
  getIpLocation,
  batchGetIpLocation,
  formatLocation
};
