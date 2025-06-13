//C端文章评论接口
import api from './index';
import { apiUrls } from './index';

/**
 * 获取文章评论列表
 * @param {Array} params 参数
 * @returns {Promise}
 */
export const getCommentList = (params) => {
  // 根据排序方式设置排序参数
  let sortBy = 'create_time';
  let sortOrder = 'desc';

  // 如果指定了排序方式
  if (params.sort_by) {
    if (params.sort_by === 'newest') {
      sortBy = 'create_time';
      sortOrder = 'desc';
    } else if (params.sort_by === 'hottest') {
      sortBy = 'likes'; // 使用数据库中的实际字段名 'likes'
      sortOrder = 'desc';
    }
  }

  // 打印排序参数，方便调试
  console.log(`评论排序参数: sortBy=${sortBy}, sortOrder=${sortOrder}, 原始排序方式=${params.sort_by}`);

  return api.get(apiUrls.BLOG_API.COMMENT_LIST, {
    params: {
      post_id: params.post_id,
      page: params.page,
      page_size: params.page_size,
      parent_id: params.parent_id,
      sortBy: sortBy,
      sortOrder: sortOrder,
      is_get_com_like_total: params.is_get_com_like_total || 1 // 默认获取评论点赞总数
    }
  });
};

/**
 * 获取最新评论
 * @returns {Promise}
 */
export const getNewCommentList = () => {
  return api.get(apiUrls.BLOG_API.COMMENT_NEW,{params:{sortBy:'create_time',sortOrder:'desc',page:1,limit:5}});
};

/**
 * 发表评论
 * @param {Object} data 评论数据
 * @returns {Promise}
 */
export const addComment = (data) => {
  return api.post(apiUrls.BLOG_API.COMMENT_CREATE, data);
};

/**
 * 回复评论
 * @param {Object} data 评论数据
 * @returns {Promise}
 */
export const replyComment = (data) => {
  return api.post(apiUrls.BLOG_API.COMMENT_REPLY, data);
};