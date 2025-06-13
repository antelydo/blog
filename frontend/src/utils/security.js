/**
 * 安全工具函数
 * 提供安全相关的工具函数，如XSS防护、CSRF防护等
 */
import CryptoJS from 'crypto-js'

/**
 * 转义HTML特殊字符，防止XSS攻击
 * @param {string} str 需要转义的字符串
 * @returns {string} 转义后的字符串
 */
export function escapeHtml(str) {
  if (!str || typeof str !== 'string') return str
  
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

/**
 * 反转义HTML特殊字符
 * @param {string} str 需要反转义的字符串
 * @returns {string} 反转义后的字符串
 */
export function unescapeHtml(str) {
  if (!str || typeof str !== 'string') return str
  
  return str
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
}

/**
 * 生成CSRF令牌
 * @returns {string} CSRF令牌
 */
export function generateCsrfToken() {
  const timestamp = new Date().getTime()
  const random = Math.random().toString(36).substring(2, 15)
  const token = `${timestamp}:${random}`
  
  // 存储到localStorage
  localStorage.setItem('csrf_token', token)
  
  return token
}

/**
 * 验证CSRF令牌
 * @param {string} token 需要验证的令牌
 * @returns {boolean} 验证结果
 */
export function validateCsrfToken(token) {
  const storedToken = localStorage.getItem('csrf_token')
  
  if (!storedToken || !token) {
    return false
  }
  
  // 验证令牌是否匹配
  if (token !== storedToken) {
    return false
  }
  
  // 验证令牌是否过期（1小时）
  const parts = storedToken.split(':')
  if (parts.length !== 2) {
    return false
  }
  
  const timestamp = parseInt(parts[0])
  const now = new Date().getTime()
  
  // 令牌有效期为1小时
  if (now - timestamp > 3600000) {
    return false
  }
  
  return true
}

/**
 * 加密敏感数据
 * @param {string} data 需要加密的数据
 * @param {string} key 加密密钥
 * @returns {string} 加密后的数据
 */
export function encryptData(data, key) {
  if (!data) return data
  
  const secretKey = key || process.env.VITE_APP_ENCRYPT_KEY || 'default-secure-key'
  return CryptoJS.AES.encrypt(JSON.stringify(data), secretKey).toString()
}

/**
 * 解密数据
 * @param {string} encryptedData 加密的数据
 * @param {string} key 解密密钥
 * @returns {any} 解密后的数据
 */
export function decryptData(encryptedData, key) {
  if (!encryptedData) return null
  
  try {
    const secretKey = key || process.env.VITE_APP_ENCRYPT_KEY || 'default-secure-key'
    const bytes = CryptoJS.AES.decrypt(encryptedData, secretKey)
    return JSON.parse(bytes.toString(CryptoJS.enc.Utf8))
  } catch (error) {
    console.error('解密数据失败:', error)
    return null
  }
}

/**
 * 安全地存储数据到localStorage
 * @param {string} key 存储键
 * @param {any} value 存储值
 * @param {boolean} encrypt 是否加密
 */
export function secureStorage(key, value, encrypt = false) {
  if (!key) return
  
  let dataToStore = value
  
  // 如果需要加密
  if (encrypt && value) {
    dataToStore = encryptData(value)
  }
  
  localStorage.setItem(key, dataToStore)
}

/**
 * 安全地从localStorage获取数据
 * @param {string} key 存储键
 * @param {boolean} decrypt 是否解密
 * @returns {any} 存储的数据
 */
export function getSecureStorage(key, decrypt = false) {
  if (!key) return null
  
  const data = localStorage.getItem(key)
  
  if (!data) return null
  
  // 如果需要解密
  if (decrypt) {
    return decryptData(data)
  }
  
  return data
}

/**
 * 清除敏感数据
 */
export function clearSensitiveData() {
  // 清除令牌
  localStorage.removeItem('token')
  localStorage.removeItem('admin_token')
  localStorage.removeItem('csrf_token')
  
  // 清除会话存储
  sessionStorage.clear()
  
  // 清除所有cookie
  document.cookie.split(';').forEach(cookie => {
    const eqPos = cookie.indexOf('=')
    const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim()
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`
  })
}

/**
 * 检测是否在iframe中运行
 * @returns {boolean} 是否在iframe中
 */
export function isInIframe() {
  try {
    return window.self !== window.top
  } catch (e) {
    return true
  }
}

/**
 * 添加安全响应头
 * 注意：这些头部需要在服务器端设置才能生效
 * 此函数仅用于文档目的
 */
export function getSecurityHeaders() {
  return {
    'Content-Security-Policy': "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;",
    'X-Content-Type-Options': 'nosniff',
    'X-Frame-Options': 'DENY',
    'X-XSS-Protection': '1; mode=block',
    'Referrer-Policy': 'strict-origin-when-cross-origin',
    'Permissions-Policy': 'geolocation=(), microphone=(), camera=()'
  }
}

/**
 * 检测是否为机器人
 * @returns {boolean} 是否为机器人
 */
export function isBot() {
  const botPattern = '(googlebot|bingbot|yandex|baiduspider|twitterbot|facebookexternalhit|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest|slackbot|vkShare|W3C_Validator)'
  const botRegex = new RegExp(botPattern, 'i')
  return botRegex.test(navigator.userAgent)
}

/**
 * 检测是否为安全环境
 * @returns {boolean} 是否为安全环境
 */
export function isSecureContext() {
  return window.isSecureContext
}

/**
 * 检测是否启用了第三方Cookie
 * @returns {Promise<boolean>} 是否启用了第三方Cookie
 */
export async function checkThirdPartyCookies() {
  return new Promise(resolve => {
    const iframe = document.createElement('iframe')
    iframe.style.display = 'none'
    document.body.appendChild(iframe)
    
    iframe.onload = () => {
      try {
        iframe.contentWindow.document.cookie = 'testCookie=1'
        const isCookieSet = iframe.contentWindow.document.cookie.indexOf('testCookie=') !== -1
        document.body.removeChild(iframe)
        resolve(isCookieSet)
      } catch (e) {
        document.body.removeChild(iframe)
        resolve(false)
      }
    }
    
    iframe.src = 'about:blank'
  })
}

export default {
  escapeHtml,
  unescapeHtml,
  generateCsrfToken,
  validateCsrfToken,
  encryptData,
  decryptData,
  secureStorage,
  getSecureStorage,
  clearSensitiveData,
  isInIframe,
  getSecurityHeaders,
  isBot,
  isSecureContext,
  checkThirdPartyCookies
}
