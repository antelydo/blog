import api from './index';
import { apiUrls } from './index';

/**
 * 添加收藏
 * @param {Object} data 包含post_id的对象
 * @returns {Promise}
 */
export const addFavorite = (data) => {
  return api.post(apiUrls.BLOG_API.FAVORITE_ADD, data);
};

/**
 * 取消收藏
 * @param {Object} data 包含post_id的对象
 * @returns {Promise}
 */
export const cancelFavorite = (data) => {
  return api.post(apiUrls.BLOG_API.FAVORITE_CANCEL, data);
};

/**
 * 获取收藏状态
 * @param {Object} data 包含post_ids的对象
 * @returns {Promise}
 */
export const getFavoriteStatus = (data) => {
  return api.post(apiUrls.BLOG_API.FAVORITE_STATUS, data);
};

/**
 * 获取用户收藏列表
 * @param {Object} params 分页参数
 * @returns {Promise}
 */
export const getUserFavorites = (params) => {
  return api.get(apiUrls.BLOG_API.FAVORITE_LIST, {
    params
  });
};

export default {
  addFavorite,
  cancelFavorite,
  getFavoriteStatus,
  getUserFavorites
};
