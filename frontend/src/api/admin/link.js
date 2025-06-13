import adminApi from '../admin';
import { apiUrls } from '../admin';
/**
 * 获取友情链接列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getLinkList = (params) => {
  // 确保参数格式正确
  const requestParams = {
    page: params.page || 1,
    limit: params.limit || 10,
    ...params
  };

  // 移除空值参数
  Object.keys(requestParams).forEach(key => {
    if (requestParams[key] === '' || requestParams[key] === null || requestParams[key] === undefined) {
      delete requestParams[key];
    }
  });

  // 使用POST方法发送请求
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LINK_LIST, requestParams)
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
 * 获取友情链接类型列表
 * @returns {Promise}
 */
export function getLinkTypeList() {
  return adminApi.get(apiUrls.ADMIN_BLOG_API.LINK_GET_LINK_TYPE_LIST);
}

/**
 * 添加友情链接
 * @param {Object} data - 友情链接数据
 * @returns {Promise}
 */
export function createLink(data) {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LINK_CREATE, data);
}

/**
 * 更新友情链接
 * @param {Object} data - 友情链接数据
 * @returns {Promise}
 */
export function updateLink(data) {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LINK_UPDATE, data);
}

/**
 * 删除友情链接
 * @param {Number} id - 友情链接ID
 * @returns {Promise}
 */
export function deleteLink(id) {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LINK_DELETE, { id });
}

/**
 * 上传友情链接LOGO
 * @param {FormData} formData - 包含文件的FormData对象
 * @returns {Promise}
 */
export function uploadLogo(formData) {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.UPLOAD_LINK_LOGO, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });
}

/**
 * 更新友情链接排序
 * @param {Array} items - 排序项数组
 * @returns {Promise}
 */
export function updateSort(items) {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.LINK_UPDATE_SORT, items);
} 

export default {
  getLinkList,
  getLinkTypeList,
  createLink,
  updateLink,
  deleteLink,
  uploadLogo,
  updateSort
}; 