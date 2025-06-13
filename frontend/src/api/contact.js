import api from './index';
import { apiUrls } from './index';

/**
 * 提交联系表单
 * @returns {Promise}
 */
export const submitContactForm = (data) => {
    return api.post(apiUrls.BLOG_API.SUBMIT_CONTACT_FORM,data);
  };  


export default {
    submitContactForm
}