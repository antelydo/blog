import api from './index';
import { apiUrls } from './index';

/**
 * 点赞文章
 * @param {Object} data 包含post_id的对象
 * @returns {Promise}
 */
export const likePost = (data) => api.post(apiUrls.BLOG_API.POST_LIKE, data);

/**
 * 取消点赞文章
 * @param {Object} data 包含post_id的对象
 * @returns {Promise}
 */
export const unlikePost = (data) => api.post(apiUrls.BLOG_API.POST_UNLIKE, data);

/**
 * 获取用户点赞状态
 * @param {Array} postIds 文章ID数组
 * @returns {Promise}
 */
export const getLikeStatus = (postIds) => api.post(apiUrls.BLOG_API.LIKE_STATUS, { post_ids: postIds });

/**
 * 获取访客点赞状态（兼容旧版本）
 * @param {Array} postIds 文章ID数组
 * @returns {Promise}
 */
export const getGuestLikeStatus = (postIds,uuid) => api.post(apiUrls.BLOG_API.GUEST_LIKE_STATUS, { post_ids: postIds,uuid:uuid });


// 兼容旧版本
export const like = likePost;
