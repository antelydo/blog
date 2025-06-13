/**
 * 访客唯一标识管理工具
 * 根据用户浏览器和网络信息创建唯一标识，并存储在localStorage中
 */
import CryptoJS from 'crypto-js';
import { v4 as uuidv4 } from 'uuid';

// 存储键名
const VISITOR_ID_KEY = 'uuid';
const ADMIN_VISITOR_ID_KEY = 'a_uuid';

/**
 * 获取浏览器指纹信息
 * @returns {String} 浏览器指纹信息
 */
function getBrowserFingerprint() {
  const navigator = window.navigator;
  const screen = window.screen;

  // 收集浏览器信息
  const fingerprint = {
    userAgent: navigator.userAgent,
    language: navigator.language,
    platform: navigator.platform,
    screenWidth: screen.width,
    screenHeight: screen.height,
    screenDepth: screen.colorDepth,
    timezone: new Date().getTimezoneOffset(),
    plugins: Array.from(navigator.plugins || []).map(p => p.name).join(','),
    doNotTrack: navigator.doNotTrack,
    cookieEnabled: navigator.cookieEnabled,
    localStorage: !!window.localStorage,
    sessionStorage: !!window.sessionStorage
  };

  return JSON.stringify(fingerprint);
}

/**
 * 使用SHA-256生成字符串的哈希值
 * @param {String} str 要哈希的字符串
 * @returns {String} 哈希结果
 */
function hashString(str) {
  return CryptoJS.SHA256(str).toString(CryptoJS.enc.Hex);
}

/**
 * 加密UUID
 * @param {String} uuid 原始UUID
 * @param {String} fingerprint 浏览器指纹
 * @returns {String} 加密后的UUID
 */
function encryptUUID(uuid, fingerprint) {
  // 使用浏览器指纹生成密钥
  const salt = CryptoJS.lib.WordArray.random(128/8);
  const key = CryptoJS.PBKDF2(fingerprint, salt, {
    keySize: 256/32,
    iterations: 1000
  });

  // 使用AES加密
  const iv = CryptoJS.lib.WordArray.random(128/8);
  const encrypted = CryptoJS.AES.encrypt(uuid, key, {
    iv: iv,
    padding: CryptoJS.pad.Pkcs7,
    mode: CryptoJS.mode.CBC
  });

  // 将salt、iv和加密数据组合并转换为Base64
  const result = salt.toString() + iv.toString() + encrypted.toString();
  return result;
}

/**
 * 解密UUID
 * @param {String} encryptedUUID 加密的UUID
 * @param {String} fingerprint 浏览器指纹
 * @returns {String} 解密后的UUID
 */
function decryptUUID(encryptedUUID, fingerprint) {
  try {
    // 检查加密的UUID是否有效
    if (!encryptedUUID || encryptedUUID.length < 65) {
      console.warn('无效的加密UUID格式');
      return null;
    }

    // 提取salt、iv和加密数据
    const saltStr = encryptedUUID.substr(0, 32);
    const ivStr = encryptedUUID.substr(32, 32);
    const encryptedStr = encryptedUUID.substr(64);

    // 验证十六进制字符串
    if (!/^[0-9a-fA-F]+$/.test(saltStr) || !/^[0-9a-fA-F]+$/.test(ivStr)) {
      console.warn('无效的salt或iv格式');
      return null;
    }

    const salt = CryptoJS.enc.Hex.parse(saltStr);
    const iv = CryptoJS.enc.Hex.parse(ivStr);

    // 重新生成密钥
    const key = CryptoJS.PBKDF2(fingerprint, salt, {
      keySize: 256/32,
      iterations: 1000
    });

    // 解密
    const decrypted = CryptoJS.AES.decrypt(encryptedStr, key, {
      iv: iv,
      padding: CryptoJS.pad.Pkcs7,
      mode: CryptoJS.mode.CBC
    });

    // 验证解密结果
    const result = decrypted.toString(CryptoJS.enc.Utf8);
    if (!result) {
      console.warn('解密结果为空');
      return null;
    }

    return result;
  } catch (error) {
    console.error('UUID解密失败:', error);
    return null;
  }
}

/**
 * 获取存储键名
 * @returns {String} 存储键名
 */
function getStorageKey() {
  // 根据路由路径判断是否为管理后台
  const isAdmin = window.location.pathname.startsWith('/admin');
  return isAdmin ? ADMIN_VISITOR_ID_KEY : VISITOR_ID_KEY;
}

/**
 * 获取访客ID
 * @returns {String} 访客ID
 */
export function getVisitorId() {
  try {
    const storageKey = getStorageKey();
    const storedId = localStorage.getItem(storageKey);
    const fingerprint = getBrowserFingerprint();

    // 如果已存在访客ID，则尝试验证
    if (storedId) {
      try {
        // 尝试解密验证
        const decrypted = decryptUUID(storedId, fingerprint);
        if (decrypted) {
          return storedId;
        }
        // 如果解密失败，清除旧的ID并生成新的
        console.log('访客ID验证失败，将重新生成');
        localStorage.removeItem(storageKey);
      } catch (decryptError) {
        console.error('访客ID解密过程出错:', decryptError);
        localStorage.removeItem(storageKey);
      }
    }

    // 生成新的访客ID
    return createVisitorId(fingerprint);
  } catch (error) {
    console.error('获取访客ID时出错:', error);
    // 返回一个简单的后备ID
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }
}

/**
 * 创建新的访客ID
 * @param {String} fingerprint 浏览器指纹
 * @returns {String} 新的访客ID
 */
function createVisitorId(fingerprint) {
  try {
    // 获取访客当前浏览器的网络信息
    let networkInfo = '';
    try {
      // 使用浏览器的连接信息作为网络标识
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      if (connection) {
        networkInfo = [
          connection.effectiveType || '',  // 网络类型（4G/3G等）
          connection.downlink || '',      // 下行速度
          connection.rtt || '',          // 网络延迟
          connection.saveData || false    // 浏览器的数据保存模式
        ].join('-');
      }

      // 添加当前时间戳的小时部分，增加随机性但不影响同一用户的识别
      const now = new Date();
      const timeSignature = `${now.getHours()}-${Math.floor(now.getMinutes() / 15)}`;

      // 如果没有获取到连接信息，使用屏幕信息和时间戳
      if (!networkInfo) {
        networkInfo = `${screen.width}x${screen.height}-${screen.colorDepth}-${timeSignature}`;
      } else {
        networkInfo += `-${timeSignature}`;
      }
    } catch (error) {
      console.warn('获取网络信息失败，将使用基本浏览器信息:', error);
      // 如果出错，使用基本的浏览器信息
      const now = new Date();
      networkInfo = `fallback-${screen.width}x${screen.height}-${now.getHours()}`;
    }

    // 使用uuid库生成标准的UUID v4
    const rawUUID = uuidv4();
    // 结合网络信息和浏览器指纹生成唯一标识
    const combinedInfo = `${rawUUID}-${networkInfo}-${fingerprint}`;
    // 使用SHA-256生成哈希
    const hash = hashString(combinedInfo);

    // 加密UUID
    const encryptedUUID = encryptUUID(hash, fingerprint);

    // 存储到localStorage
    const storageKey = getStorageKey();
    localStorage.setItem(storageKey, encryptedUUID);

    return encryptedUUID;
  } catch (error) {
    console.error('创建访客ID失败:', error);
    // 出错时使用简单的UUID作为后备方案
    try {
      // 生成一个新的UUID
      const fallbackUUID = uuidv4();
      // 使用基本的浏览器信息作为指纹
      const basicFingerprint = navigator.userAgent + screen.width + screen.height;
      // 使用基本的加密方式
      const encryptedFallback = CryptoJS.AES.encrypt(fallbackUUID, basicFingerprint).toString();

      const storageKey = getStorageKey();
      localStorage.setItem(storageKey, encryptedFallback);
      return encryptedFallback;
    } catch (fallbackError) {
      // 如果连后备方案也失败，生成一个简单的随机字符串
      console.error('后备UUID生成失败:', fallbackError);
      const simpleId = Date.now().toString(36) + Math.random().toString(36).substr(2);
      const storageKey = getStorageKey();
      localStorage.setItem(storageKey, simpleId);
      return simpleId;
    }
  }
}

/**
 * 初始化访客ID
 * 如果localStorage中不存在，则创建新的访客ID
 * @returns {String} 访客ID
 */
export function initVisitorId() {
  try {
    // 清除可能存在的无效ID
    const storageKey = getStorageKey();
    const storedId = localStorage.getItem(storageKey);

    // 如果存储的ID格式明显不正确，直接清除
    if (storedId && (storedId.length < 64 || !storedId.match(/^[0-9a-fA-F]{64,}/))) {
      console.log('检测到无效的访客ID格式，将重置');
      localStorage.removeItem(storageKey);
    }

    return getVisitorId();
  } catch (error) {
    console.error('初始化访客ID失败:', error);
    // 返回一个简单的后备ID
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }
}

/**
 * 获取当前访客ID（同步方法）
 * 如果尚未初始化，返回null
 * @returns {String|null} 访客ID或null
 */
export function getCurrentVisitorId() {
  const storageKey = getStorageKey();
  return localStorage.getItem(storageKey);
}

/**
 * 清除访客ID
 */
export function clearVisitorId() {
  const storageKey = getStorageKey();
  localStorage.removeItem(storageKey);
}

export default {
  initVisitorId,
  getCurrentVisitorId,
  clearVisitorId
};
