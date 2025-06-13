/**
 * 全局配置文件
 * 集中管理应用程序的配置
 */

// 环境变量
const env = import.meta.env;

// 应用环境
export const APP_ENV = env.VITE_APP_ENV || 'development';

// API URL
export const API_URL = env.VITE_APP_API_URL || 'http://localhost:8000';

// 日志配置
export const LOG_CONFIG = {
  // 是否启用控制台日志
  enableConsoleLog: env.VITE_APP_ENABLE_CONSOLE_LOG !== 'false',
  // 日志级别: 'debug', 'info', 'warn', 'error'
  logLevel: env.VITE_APP_LOG_LEVEL || 'debug',
  // 是否在日志前添加时间戳
  showTimestamp: true,
  // 是否在日志前添加日志级别
  showLogLevel: true
};

// 导出默认配置
export default {
  APP_ENV,
  API_URL,
  LOG_CONFIG,
  
  // 是否是生产环境
  isProd: APP_ENV === 'production',
  // 是否是开发环境
  isDev: APP_ENV === 'development',
  // 是否是测试环境
  isTest: APP_ENV === 'test'
};
