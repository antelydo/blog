import axios from 'axios';
import apiConfig from './config';
import  visitorId from '../utils/visitorId';

// 创建axios实例
const api = axios.create({
  baseURL: apiConfig.BASE_URL,
  timeout: 10000,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// 请求拦截器
api.interceptors.request.use(
  config => {
    // 从localStorage获取token
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    //获取访客唯一标识UUID
    const uuid = visitorId.getCurrentVisitorId();
    config.headers['uuid'] = uuid;

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
    return Promise.reject(error);
  }
);

// 响应拦截器
api.interceptors.response.use(
  response => {
    // 请求完成后，从映射表中移除
    if (response.config.requestKey) {
      apiConfig.removePendingRequest(response.config.requestKey);
    }

    // 检查响应中是否包含token过期的标识
    if (response.data && (response.data.code === 401 || response.data.msg === 'token已过期' || response.data.msg === 'token验证失败' ||
        response.data.msg === '请先登录' || response.data.msg === 'please_login_first')) {

      // 检查是否是管理员路径
      const isAdminPath = window.location.pathname.includes('/admin');

      // 清除相应的token
      if (isAdminPath) {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_info');
      } else {
        localStorage.removeItem('token');
        localStorage.removeItem('user_info');
      }

      // 使用路由跳转到相应的登录页
      if (window.router) {
        window.router.push(isAdminPath ? '/admin/login' : '/login');
      } else {
        window.location.href = isAdminPath ? '/admin/login' : '/login';
      }
    }

    return response.data;
  },
  error => {
    // 如果是取消的请求，不做处理
    if (axios.isCancel(error)) {
      return new Promise(() => {});
    }

    // 请求出错后，从映射表中移除
    if (error.config && error.config.requestKey) {
      apiConfig.removePendingRequest(error.config.requestKey);
    }

    // 处理HTTP错误状态码
    if (error.response) {
      const status = error.response.status;

      // 处理401未授权错误
      if (status === 401) {

        // 检查是否是管理员路径
        const isAdminPath = window.location.pathname.includes('/admin');

        // 清除相应的token
        if (isAdminPath) {
          localStorage.removeItem('admin_token');
          localStorage.removeItem('admin_info');
        } else {
          localStorage.removeItem('token');
          localStorage.removeItem('user_info');
        }

        // 使用路由跳转到相应的登录页
        if (window.router) {
          window.router.push(isAdminPath ? '/admin/login' : '/login');
        } else {
          window.location.href = isAdminPath ? '/admin/login' : '/login';
        }

        return Promise.reject(new Error('未授权，请重新登录'));
      }

      // 处理403禁止访问错误
      else if (status === 403) {

        if (window.router) {
          window.router.push('/403');
        } else {
          window.location.href = '/403';
        }

        return Promise.reject(new Error('访问被拒绝'));
      }

      // 处理500服务器错误
      else if (status >= 500) {

        if (window.router) {
          window.router.push('/500');
        } else {
          window.location.href = '/500';
        }

        return Promise.reject(new Error('服务器错误，请稍后再试'));
      }
    }

    // 处理网络错误或者请求被中断
    else if (error.request) {
      // 可选：跳转到网络错误页面
    }

    return Promise.reject(error);
  }
);

// 创建防抖请求函数
const debounceMap = new Map();

/**
 * 防抖请求函数
 * @param {Function} requestFn 请求函数
 * @param {number} delay 延迟时间，默认300ms
 * @returns {Function} 防抖后的请求函数
 */
export const debounceRequest = (requestFn, delay = 300) => {
  return (...args) => {
    const key = JSON.stringify(args);
    if (debounceMap.has(key)) {
      clearTimeout(debounceMap.get(key));
    }

    return new Promise((resolve, reject) => {
      const timer = setTimeout(async () => {
        try {
          const result = await requestFn(...args);
          resolve(result);
          debounceMap.delete(key);
        } catch (error) {
          reject(error);
          debounceMap.delete(key);
        }
      }, delay);

      debounceMap.set(key, timer);
    });
  };
};

// 导出API配置，方便直接使用接口地址
export const apiUrls = apiConfig;

// 导出axios实例
export default api;