//C端文章
import api from './index';
import { apiUrls } from './index';


/**
 * 获取文章列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getPostList = (params = {}) => {
  return api.get(apiUrls.BLOG_API.POST_LIST, { params });
};

/**
 * 获取文章详情
 * @param {Number} id 文章ID
 * @returns {Promise}
 */
export const getPostDetail = (params) => {
  return api.get(apiUrls.BLOG_API.POST_DETAIL,{params:{id:params.id,is_get_prev_next:params.is_get_prev_next,is_check_like:params.is_check_like,is_get_comment:params.is_get_comment,is_check_favorite:params.is_check_favorite}});
};

/**
 * 获取置顶推荐文档
 * @returns {Promise}
 */
export const getTopRecommendPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{is_top:1,is_recommend:1,status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:4}});
};

/**
 * 获取置顶文章列表
 * @returns {Promise}
 */
export const getTopPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{is_top:1,status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:4}});
};

/**
 * 获取推荐文章列表
 * @returns {Promise}
 */
export const getRecommendPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{is_recommend:1,status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:4}});
};

/**
 * 获取热门文章列表
 * @returns {Promise}
 */
export const getHotPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_HOT,{params:{limit:3}});
};

/**
 * 获取最新文章列表
 * @returns {Promise}
 */
export const getNewPostList = (params) => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{status:params.status,sortBy:'publish_time',sortOrder:'desc',page:params.page,limit:params.limit,is_check_like:params.is_check_like??0,is_check_favorite:params.is_check_favorite??0}});
};

/**
 * 获取点赞最多的文章
 * @returns {Promise}
 */
export const getMoreLikePostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{status:1,sortBy:'likes',sortOrder:'desc',page:1,limit:6}});
};

/**
 * 获取浏览最多的文章
 * @returns {Promise}
 */
export const getMoreViewPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{status:1,sortBy:'views',sortOrder:'desc',page:1,limit:6}});
};  

/**
 * 获取评论最多的文章
 * @returns {Promise}
 */
export const getMoreCommentPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{status:1,sortBy:'comments',sortOrder:'desc',page:1,limit:6}});
};

/**
 * 获取随机推荐文章
 * @returns {Promise}
 */
export const getRandomRecommendPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{is_recommend:1,status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:6}});
};  

/**
 * 获取首页banner展示的文章列表
 * @returns {Promise}
 */
export const getBannerTopPostList = () => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{is_banner_top:1,status:1,sortBy:'publish_time',sortOrder:'desc',page:1,limit:6}});
};

/**
 * 获取相关文章
 * @returns {Promise}
 */
export const getRelatedPostList = (id) => {
  return api.get(apiUrls.BLOG_API.POST_LIST,{params:{id:id, status:1,sortBy:'publish_time',sortOrder:'desc',page:1,limit:3}});
};  


export default {
    getPostList,
    getPostDetail,
    getRecommendPostList,
    getHotPostList,
    getNewPostList,
    getMoreLikePostList
}
