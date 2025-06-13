import api from './index';
import { apiUrls } from './index';

/**
 * 获取网站统计信息
 * @returns {Promise}
 */
export const getWebStatistics = () => {
  return api.get(apiUrls.BLOG_API.WEB_STATISTICS);
};
