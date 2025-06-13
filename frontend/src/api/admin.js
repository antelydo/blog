import axios from 'axios';
import apiConfig from './config';

// 导出API URLs
export const apiUrls = apiConfig;

// 创建管理员专用的axios实例
const adminApi = axios.create({
  baseURL: apiConfig.BASE_URL,
  timeout: 30000,
  withCredentials: true
});

// 请求拦截器
adminApi.interceptors.request.use(
  config => {
      // 从localStorage获取管理员token
      const adminToken = localStorage.getItem('admin_token');

      // 添加调试日志
      console.debug({
        url: config.url,
        method: config.method,
        hasToken: !!adminToken,
        tokenPreview: adminToken ? adminToken.substring(0, 20) + '...' : null
      });

      //初始化uuid
      // //获取访客唯一标识UUID
      // const uuid = visitorId.getCurrentVisitorId();
      // config.headers['uuid'] = uuid;

      if (adminToken) {
        // 确保token格式正确，添加Bearer前缀
        const formattedToken = adminToken.startsWith('Bearer ') ? adminToken : `Bearer ${adminToken}`;
        config.headers['Authorization'] = formattedToken;
      } else {
        // 只在非登录页面显示警告
        const isLoginPage = window.location.pathname.includes('/admin/login');
        if (!isLoginPage && !config.url.includes('/login')) {
          console.warn('警告: 未找到管理员token，请求可能会被拒绝');
        }
      }

      // 创建取消令牌
      const controller = new AbortController();
      config.signal = controller.signal;

      // 生成请求的唯一键
      const requestKey = apiConfig.generateRequestKey(
        config.url,
        config.method,
        config.method === 'get' ? config.params : config.data
      );

      // 如果存在相同的请求，则取消之前的请求
      apiConfig.cancelDuplicateRequest(requestKey);

      // 将当前请求添加到映射表
      apiConfig.addPendingRequest(requestKey, controller);

      // 保存请求键到配置中，以便在响应拦截器中使用
      config.requestKey = requestKey;

      return config;
    },
    error => {
      // 添加更详细的错误日志
      console.error('请求拦截器错误:', {
        message: error.message,
        config: error.config,
        stack: error.stack
      });
      return Promise.reject(error);
    }
  );

  // 响应拦截器
  adminApi.interceptors.response.use(
    response => {
      // 请求完成后，从映射表中移除
      if (response.config.requestKey) {
        apiConfig.removePendingRequest(response.config.requestKey);
      }

      // 添加调试日志
      console.debug({
        url: response.config.url,
        status: response.status,
        data: response.data ? (typeof response.data === 'object' ? '对象' : response.data) : null,
        code: response.data ? response.data.code : null,
        message: response.data ? response.data.msg : null
      });

      // 检查响应中是否包含token过期的标识
      if (response.data && (response.data.code === 401 ||
          response.data.msg === 'token已过期' ||
          response.data.msg === 'token验证失败' ||
          response.data.msg === '请先登录' ||
          response.data.msg === 'please_login_first')) {

        // 检查是否是登录请求，如果是登录请求失败，不进行token清理和跳转
        if (response.config.url.includes('/login')) {
          return response.data;
        }

        // 不在控制台显示错误信息，直接处理

        // 清除token和管理员信息
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_info');

        // 使用路由跳转到管理员登录页
        if (window.router) {
          window.router.push('/admin/login');
        } else {
          window.location.href = '/admin/login';
        }

        // 防止后续逻辑继续处理，但不抛出错误信息
        return Promise.reject();
      }

      return response.data;
    },
    error => {
      // 添加更详细的错误日志
      console.error('管理员API请求错误:', {
        url: error.config ? error.config.url : '未知',
        message: error.message,
        response: error.response ? {
          status: error.response.status,
          data: error.response.data
        } : '无响应',
        isCancel: axios.isCancel(error),
        config: error.config
      });

      // 如果是取消的请求，返回一个空对象而不是拒绝Promise
      if (axios.isCancel(error)) {
        return Promise.resolve({});
      }

      // 请求出错后，从映射表中移除
      if (error.config && error.config.requestKey) {
        apiConfig.removePendingRequest(error.config.requestKey);
      }

      // 处理401未授权错误
      if (error.response && error.response.status === 401) {
        // 检查是否是登录请求，如果是登录请求失败，不进行token清理和跳转
        if (error.config.url.includes('/login')) {
          return Promise.reject(error);
        }

        // 未授权，清除管理员token并跳转到管理员登录页
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_info');

        // 使用路由跳转到管理员登录页
        if (window.router) {
          window.router.push('/admin/login');
        } else {
          window.location.href = '/admin/login';
        }

        return Promise.reject(new Error('未授权，请重新登录'));
      }

      // 处理网络错误
      if (!error.response) {
        console.warn('网络错误或请求被取消:', error.message);
        return Promise.resolve({});
      }

      return Promise.reject(error);
    }
  );

// 导出API配置已在文件开头完成

// 导出axios实例
export default adminApi;