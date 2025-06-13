import api from './index';
import { apiUrls } from './index';

// 获取分类列表
export const getCatList = (data) => api.post(apiUrls.BLOG_API.GET_CAT_LIST, data);

// 获取分类详情
export const getCatDetail = (data) => api.post(apiUrls.BLOG_API.CATEGORY_DETAIL, data);

        
