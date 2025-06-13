/**
 * 日期格式化工具函数
 */

/**
 * 格式化日期为字符串
 * @param {Date|number|string} date 日期对象、时间戳或日期字符串
 * @param {string} format 格式化模式，默认为 'YYYY-MM-DD HH:mm:ss'
 * @returns {string} 格式化后的日期字符串
 */
export const formatDate = (date, format = 'YYYY-MM-DD HH:mm:ss') => {
  if (!date) return '-'
  
  let dateObj
  if (typeof date === 'string') {
    // 尝试解析日期字符串
    dateObj = new Date(date)
  } else if (typeof date === 'number') {
    // 如果是秒级时间戳，转换为毫秒级
    dateObj = new Date(date < 9999999999 ? date * 1000 : date)
  } else if (date instanceof Date) {
    dateObj = date
  } else {
    // 无法识别的格式，返回默认值
    return '-'
  }
  
  // 检查日期是否有效
  if (isNaN(dateObj.getTime())) {
    return '-'
  }
  
  const map = {
    'YYYY': dateObj.getFullYear(),
    'MM': padStart(dateObj.getMonth() + 1, 2, '0'),
    'DD': padStart(dateObj.getDate(), 2, '0'),
    'HH': padStart(dateObj.getHours(), 2, '0'),
    'mm': padStart(dateObj.getMinutes(), 2, '0'),
    'ss': padStart(dateObj.getSeconds(), 2, '0')
  }
  
  return format.replace(/(YYYY|MM|DD|HH|mm|ss)/g, (matched) => map[matched])
}

/**
 * 字符串补齐函数
 * @param {number|string} val 需要补齐的值
 * @param {number} len 目标长度
 * @param {string} char 填充字符
 * @returns {string} 补齐后的字符串
 */
const padStart = (val, len, char) => {
  val = String(val)
  const diff = len - val.length
  
  if (diff <= 0) return val
  
  return Array(diff + 1).join(char) + val
}

/**
 * 获取相对时间描述
 * @param {Date|number|string} date 日期对象、时间戳或日期字符串
 * @returns {string} 相对时间描述，如"刚刚"、"5分钟前"等
 */
export const getRelativeTime = (date) => {
  if (!date) return '-'
  
  let dateObj
  if (typeof date === 'string') {
    dateObj = new Date(date)
  } else if (typeof date === 'number') {
    dateObj = new Date(date < 9999999999 ? date * 1000 : date)
  } else if (date instanceof Date) {
    dateObj = date
  } else {
    return '-'
  }
  
  // 检查日期是否有效
  if (isNaN(dateObj.getTime())) {
    return '-'
  }
  
  const now = new Date()
  const diff = now.getTime() - dateObj.getTime() // 差值，单位为毫秒
  
  // 判断时间差
  if (diff < 0) {
    return formatDate(dateObj) // 未来的时间，直接显示完整日期
  } else if (diff < 60 * 1000) {
    return '刚刚'
  } else if (diff < 60 * 60 * 1000) {
    return Math.floor(diff / (60 * 1000)) + '分钟前'
  } else if (diff < 24 * 60 * 60 * 1000) {
    return Math.floor(diff / (60 * 60 * 1000)) + '小时前'
  } else if (diff < 7 * 24 * 60 * 60 * 1000) {
    return Math.floor(diff / (24 * 60 * 60 * 1000)) + '天前'
  } else if (dateObj.getFullYear() === now.getFullYear()) {
    return `${padStart(dateObj.getMonth() + 1, 2, '0')}-${padStart(dateObj.getDate(), 2, '0')}`
  } else {
    return `${dateObj.getFullYear()}-${padStart(dateObj.getMonth() + 1, 2, '0')}-${padStart(dateObj.getDate(), 2, '0')}`
  }
}

export default {
  formatDate,
  getRelativeTime
}