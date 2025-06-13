import api from './index';
import { apiUrls } from './index';

/**
 * 获取友情链接列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getLinkList = (params = {}) => {
  // 确保参数格式正确
  const requestParams = {
    page: params.page || 1,
    limit: params.limit || 20,
    ...params
  };

  // 移除空值参数
  Object.keys(requestParams).forEach(key => {
    if (requestParams[key] === '' || requestParams[key] === null || requestParams[key] === undefined) {
      delete requestParams[key];
    }
  });

  // 使用GET方法获取公开的友情链接列表
  return api.get(apiUrls.BLOG_API.LINK_LIST, { params: requestParams })
    .then(response => {
      // 确保返回的数据格式正确 - 接受code=0或code=200作为成功状态
      if (response && (response.code === 0 || response.code === 200)) {
        return response;
      }
      
      // 如果返回格式不符合预期，尝试转换
      if (Array.isArray(response)) {
        return {
          code: 0,
          data: response
        };
      }
      
      if (response && response.list) {
        return {
          code: 0,
          data: {
            list: response.list,
            total: response.total || response.list.length
          }
        };
      }
      
      if (response && response.records) {
        return {
          code: 0,
          data: {
            list: response.records,
            total: response.total || response.records.length
          }
        };
      }
      
      // 如果无法转换，返回原始响应
      return response;
    })
    .catch(error => {
      console.error('Failed to fetch link list:', error);
      throw error;
    });
};

/**
 * 获取友情链接详情
 * @param {Number} id 友情链接ID 
 * @returns {Promise}
 */
export const getLinkDetail = (id) => {
  return api.get(apiUrls.BLOG_API.LINK_DETAIL + `/${id}`);
};

/**
 * 申请友情链接
 * @param {Object} data 友情链接数据
 * @returns {Promise}
 */
export const applyLink = (data) => {
  return api.post(apiUrls.BLOG_API.LINK_CREATE, data);
};

/**
 * 上传友情链接LOGO
 * @param {FormData} formData 包含文件的FormData对象
 * @returns {Promise}
 */
export const uploadLinkLogo = (formData) => {
  return api.post(apiUrls.BLOG_API.UPLOAD_LINK_LOGO, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });
};

/**
 * 获取友情链接类型列表
 * @returns {Promise}
 */
export const getLinkTypeList = () => {
  return api.get(apiUrls.BLOG_API.LINK_TYPE_LIST);
};  


export default {
  getLinkList,
  getLinkDetail,
  applyLink,
  uploadLinkLogo,
  getLinkTypeList
}; 