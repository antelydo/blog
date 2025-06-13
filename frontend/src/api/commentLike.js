import api from './index';
import { apiUrls } from './index';
import { getCurrentVisitorId } from '@/utils/visitorId';

/**
 * 点赞评论
 * @param {Object} data 包含comment_id的对象
 * @returns {Promise}
 */
export const likeComment = (data) => {
  // 如果没有提供 uuid，则自动获取
  if (!data.uuid && !data.user_id) {
    data.uuid = getCurrentVisitorId();
  }
  return api.post(apiUrls.BLOG_API.COMMENT_LIKE, data);
};

/**
 * 取消点赞评论
 * @param {Object} data 包含comment_id的对象
 * @returns {Promise}
 */
export const unlikeComment = (data) => {
  // 如果没有提供 uuid，则自动获取
  if (!data.uuid && !data.user_id) {
    data.uuid = getCurrentVisitorId();
  }
  return api.post(apiUrls.BLOG_API.COMMENT_UNLIKE, data);
};

/**
 * 获取评论点赞状态
 * @param {Array} commentIds 评论ID数组
 * @returns {Promise}
 */
export const getCommentLikeStatus = (commentIds) => {
  const uuid = getCurrentVisitorId();
  return api.post(apiUrls.BLOG_API.COMMENT_LIKE_STATUS, { 
    comment_ids: commentIds,
    uuid: uuid
  });
};
