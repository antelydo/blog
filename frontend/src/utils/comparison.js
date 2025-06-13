import { isDeepStrictEqual } from 'node:util'

// 主要比较函数，用于整个项目
export function compareObjects(obj1, obj2) {
  return isDeepStrictEqual(obj1, obj2)
}

// 浏览器环境的后备方案（因为浏览器中无法使用 node:util）
export function isDeepEqual(obj1, obj2) {
  if (obj1 === obj2) return true
  
  if (typeof obj1 !== 'object' || typeof obj2 !== 'object' || 
      obj1 === null || obj2 === null) {
    return obj1 === obj2
  }

  const keys1 = Object.keys(obj1)
  const keys2 = Object.keys(obj2)

  if (keys1.length !== keys2.length) return false

  return keys1.every(key => {
    if (!Object.prototype.hasOwnProperty.call(obj2, key)) return false
    return isDeepEqual(obj1[key], obj2[key])
  })
}

// 导出一个推荐使用的统一函数
export const isEqual = typeof isDeepStrictEqual !== 'undefined' 
  ? compareObjects 
  : isDeepEqual
