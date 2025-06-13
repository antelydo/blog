//C端文章标签接口
import api from './index';
import { apiUrls } from './index';

/**
 * 获取所有标签
 * @returns {Promise}
 */
export const getTagList = (params) => {
    return api.get(apiUrls.BLOG_API.TAG_LIST,{params:params});
};

/** 
 * 获取标签详情
 * @param {Number} id 标签ID
 * @returns {Promise}
 */
export const getTagDetail = (id) => {
  return api.get(apiUrls.BLOG_API.TAG_DETAIL,{params:{id:id}});
};

/**
 * 获取热门标签
 * @returns {Promise}
 */
export const getHotTagList = () => {
  return api.get(apiUrls.BLOG_API.TAG_HOT,{params:{status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:20}});
};

/**
 * 获取最新标签
 * @returns {Promise}
 */
export const getNewTagList = () => {
  return api.get(apiUrls.BLOG_API.TAG_NEW,{params:{status:1,sortBy:'sort',sortOrder:'desc',page:1,limit:20 }});
};





