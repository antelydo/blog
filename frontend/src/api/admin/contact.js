/**
 * 联系表单相关API
 */
import adminApi from '../admin';
import { apiUrls } from '../admin';


/**
 * 获取联系表单列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export const getContactList = (params) => {
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
    return adminApi.post(apiUrls.ADMIN_BLOG_API.GET_MESSAGES, requestParams)
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
        console.error('Failed to fetch contact list:', error);
        throw error;
      });
  };


// 获取联系表单列表
export const getMessages = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.GET_MESSAGES, params);
};

// 获取联系表单详情
export const getMessage = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.GET_MESSAGE, params);
};

// 更新联系表单状态
export const updateStatus = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.UPDATE_STATUS, params);
};

// 回复联系表单
export const replyMessage = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.REPLY_MESSAGE, params);
};          

// 删除联系表单
export const deleteMessage = (params) => {
  return adminApi.post(apiUrls.ADMIN_BLOG_API.DELETE_MESSAGE, params);
};      

export default {
  getContactList,
  getMessages,
  getMessage,
  updateStatus,
  replyMessage,
  deleteMessage
};





