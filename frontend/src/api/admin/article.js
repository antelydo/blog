/**
 * 文章管理相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';

// 文章管理
export const getArticles = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_LIST, params);
};

export const getArticleDetail = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_DETAIL, params);
};

export const createArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_CREATE, params);
};

export const updateArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_UPDATE, params);
};

export const deleteArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_DELETE, params);
};

export const toggleTopArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_TOP, params);
};

export const toggleRecommendArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_RECOMMEND, params);
};

export const toggleBannerTopArticle = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.POST_BANNER_TOP, params);
};


// 分类管理
export const getCategories = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.CATEGORY_LIST, params);
};

export const createCategory = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.CATEGORY_CREATE, params);
};

export const updateCategory = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.CATEGORY_UPDATE, params);
};

export const updateCatStatus = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.UPDATE_CAT_STATUS, params);
};

export const deleteCategory = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.CATEGORY_DELETE, params);
};

// 标签管理
export const getTags = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.TAG_LIST, { params });
};

export const createTag = (data) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.TAG_CREATE, data);
};

export const updateTag = (data) => {
  return adminApi.put(apiUrls.ADMIN_BLOG_API.TAG_UPDATE, data);
};

export const deleteTag = (data) => {
  return adminApi.delete(apiUrls.ADMIN_BLOG_API.TAG_DELETE, { data });
};

export const updateTagStatus = (data) => {
  return adminApi.put(apiUrls.ADMIN_BLOG_API.UPDATE_TAG_STATUS, data);
};

// 评论管理
export const getComments = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.COMMENT_LIST, params);
};

export const createComment = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.COMMENT_CREATE, params);
};

export const replyComment = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.COMMENT_REPLY, params);
};

export const updateComment = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.COMMENT_UPDATE, params);
};

export const deleteComment = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.COMMENT_DELETE, params);
};

export const updateCommentStatus = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.UPDATE_COMMENT_STATUS, params);
};

// 点赞管理
export const getLikes = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LIKE_LIST, params);
};

//删除点赞
export const deleteLike = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LIKE_DELETE, params);
};

export const getBlogStats = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.BLOG_STATS, params);
};

// 图片上传
export const uploadImage = (formData) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.UPLOAD_IMAGE, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });
};

export default {
  getArticles,
  getArticleDetail,
  createArticle,
  updateArticle,
  deleteArticle,
  toggleTopArticle,
  toggleRecommendArticle,
  getCategories,
  createCategory,
  updateCategory,
  deleteCategory,
  updateCatStatus,
  getTags,
  createTag,
  updateTag,
  deleteTag,
  updateTagStatus,
  getComments,
  createComment,
  replyComment,
  updateComment,
  deleteComment,
  updateCommentStatus,
  getLikes,
  deleteLike,
  getBlogStats,
  uploadImage
}; 