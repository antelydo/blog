/**
 * 日志工具类
 * 根据环境配置决定是否输出日志
 */

// 默认配置
let config = {
  // 是否启用控制台日志
  enableConsoleLog: true,
  // 日志级别: 'debug', 'info', 'warn', 'error'
  logLevel: 'debug',
  // 是否在日志前添加时间戳
  showTimestamp: true,
  // 是否在日志前添加日志级别
  showLogLevel: true
};

// 日志级别权重
const LOG_LEVELS = {
  debug: 0,
  info: 1,
  warn: 2,
  error: 3
};

/**
 * 更新日志配置
 * @param {Object} newConfig 新配置
 */
export function updateLoggerConfig(newConfig) {
  config = { ...config, ...newConfig };
}

/**
 * 获取当前日志配置
 * @returns {Object} 当前配置
 */
export function getLoggerConfig() {
  return { ...config };
}

/**
 * 格式化日志消息
 * @param {string} level 日志级别
 * @param {Array} args 日志参数
 * @returns {Array} 格式化后的日志参数
 */
function formatLogMessage(level, args) {
  const formattedArgs = [...args];
  
  // 添加前缀
  let prefix = '';
  
  // 添加时间戳
  if (config.showTimestamp) {
    const now = new Date();
    prefix += `[${now.toISOString()}] `;
  }
  
  // 添加日志级别
  if (config.showLogLevel) {
    prefix += `[${level.toUpperCase()}] `;
  }
  
  // 如果有前缀且第一个参数是字符串，则将前缀添加到第一个参数
  if (prefix && typeof formattedArgs[0] === 'string') {
    formattedArgs[0] = prefix + formattedArgs[0];
  } else if (prefix) {
    // 否则将前缀作为单独的参数添加
    formattedArgs.unshift(prefix);
  }
  
  return formattedArgs;
}

/**
 * 检查是否应该记录该级别的日志
 * @param {string} level 日志级别
 * @returns {boolean} 是否应该记录
 */
function shouldLog(level) {
  return config.enableConsoleLog && LOG_LEVELS[level] >= LOG_LEVELS[config.logLevel];
}

/**
 * 创建日志函数
 * @param {string} level 日志级别
 * @param {Function} originalMethod 原始控制台方法
 * @returns {Function} 新的日志函数
 */
function createLogMethod(level, originalMethod) {
  return function(...args) {
    if (shouldLog(level)) {
      originalMethod.apply(console, formatLogMessage(level, args));
    }
  };
}

// 保存原始的控制台方法
const originalConsole = {
  log: console.log,
  info: console.info,
  warn: console.warn,
  error: console.error,
  debug: console.debug
};

// 创建日志对象
const logger = {
  // 日志方法
  log: createLogMethod('debug', originalConsole.log),
  info: createLogMethod('info', originalConsole.info),
  warn: createLogMethod('warn', originalConsole.warn),
  error: createLogMethod('error', originalConsole.error),
  debug: createLogMethod('debug', originalConsole.debug),
  
  // 恢复原始控制台方法
  restoreConsole: function() {
    console.log = originalConsole.log;
    console.info = originalConsole.info;
    console.warn = originalConsole.warn;
    console.error = originalConsole.error;
    console.debug = originalConsole.debug;
  },
  
  // 覆盖控制台方法
  overrideConsole: function() {
    console.log = this.log;
    console.info = this.info;
    console.warn = this.warn;
    console.error = this.error;
    console.debug = this.debug;
  }
};

export default logger;
