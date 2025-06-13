// 网站配置 Store
// 用于在应用程序中集中管理从服务器获取的网站配置信息

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import adminApi from '@/api/admin'
import apiConfig from '@/api/config'
import i18n from '@/plugins/i18n'
import axios from 'axios'

export const useConfigStore = defineStore('config', () => {
  // 初始配置数据
  const config = ref({
    site_name: 'Admin System',
    site_title: '后台管理系统',
    site_description: '',
    site_keywords: '',
    site_logo: '',
    site_favicon: '',
    site_copyright: '',
    site_icp: ''
  })

  // 加载状态
  const loading = ref(false)
  const initialized = ref(false)

  // 缓存配置请求的Promise
  let configPromise = null

  // 根据当前语言获取本地化的网站标题
  const localizedSiteTitle = computed(() => {
    const currentLocale = i18n.global.locale.value
    return currentLocale === 'zh' ? config.value.site_title : config.value.site_name
  })

  // 获取网站配置
  const fetchConfig = async () => {
    // 如果已经初始化，直接返回
    if (initialized.value) {
      return config.value
    }

    // 如果正在加载，返回缓存的Promise
    if (configPromise) {
      return configPromise
    }

    // 如果已经加载过但失败了，重置状态
    if (loading.value) {
      loading.value = false
    }

    loading.value = true

    // 创建新的Promise并缓存
    configPromise = (async () => {
      try {
        const response = await adminApi.get(apiConfig.ADMIN_API.CONFIG)

        // 检查响应是否为空（可能是请求被取消）
        if (!response) {
          console.warn('配置请求被取消或没有响应')
          return config.value
        }

        if (response && response.code === 200) {
          const data = response.data

          // 将后端返回的配置数组转换为对象格式
          if (Array.isArray(data)) {
            data.forEach(item => {
              if (item && item.name && config.value.hasOwnProperty(item.name)) {
                config.value[item.name] = item.value || ''
              }
            })
          } else if (typeof data === 'object') {
            // 如果是对象格式，直接赋值
            Object.keys(config.value).forEach(key => {
              if (data[key] !== undefined) {
                config.value[key] = data[key]
              }
            })
          }

          // 如果网站标题为空，使用网站名称
          if (!config.value.site_title) {
            config.value.site_title = config.value.site_name
          }

          // 更新网站图标
          updateFavicon(config.value.site_favicon)

          initialized.value = true

          // 更新页面标题
          updatePageTitle()

          // 触发配置初始化完成事件
          window.dispatchEvent(new CustomEvent('config-store-ready'))

          return config.value
        }
      } catch (error) {
        // 添加更详细的错误日志
        console.error('获取网站配置失败:', {
          error: error.message,
          stack: error.stack,
          response: error.response
        })

        // 如果是请求被取消，不显示错误
        if (axios.isCancel(error)) {
          console.warn('配置请求被取消')
          return config.value
        }

        // 如果是网络错误，使用默认配置
        if (!error.response) {
          console.warn('网络错误，使用默认配置')
          initialized.value = true
          return config.value
        }

        throw error
      } finally {
        loading.value = false
        configPromise = null
      }
    })()

    return configPromise
  }

  // 更新网站图标
  const updateFavicon = (faviconUrl) => {
    if (!faviconUrl) return

    const link = document.querySelector("link[rel*='icon']") || document.createElement('link')
    link.type = 'image/x-icon'
    link.rel = 'shortcut icon'
    link.href = faviconUrl
    document.getElementsByTagName('head')[0].appendChild(link)
  }

  // 获取国际化的路由标题
  const getRouteTitle = (routeTitleKey) => {
    if (!routeTitleKey) return ''

    // 尝试从路由国际化文件中获取标题
    try {
      // 首先尝试使用原始键（不转换大小写）
      const originalKey = `route.${routeTitleKey}`
      let translated = i18n.global.t(originalKey)

      // 如果找到了翻译，直接返回
      if (translated && translated !== originalKey) {
        return translated
      }

      // 转换路由名称为i18n key，尝试多种格式以确保兼容性
      // 1. 全小写
      const lowerCaseKey = `route.${routeTitleKey.toLowerCase()}`
      translated = i18n.global.t(lowerCaseKey)

      // 调试输出
      console.log(`[DEBUG] 路由标题翻译:
        原始Key: ${originalKey},
        小写Key: ${lowerCaseKey},
        当前语言: ${i18n.global.locale.value},
        翻译结果: ${translated}`)

      // 2. 如果全小写找不到，尝试保持原有的大小写
      if (translated === lowerCaseKey) {
        // 3. 尝试下划线命名法（适用于可能是驼峰命名法的键）
        const snakeCaseKey = `route.${routeTitleKey.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase()}`
        translated = i18n.global.t(snakeCaseKey)

        // 4. 如果下划线命名法也找不到，尝试驼峰命名法
        if (translated === snakeCaseKey) {
          // 转换为驼峰式命名
          const camelCaseKey = `route.${routeTitleKey.charAt(0).toLowerCase() + routeTitleKey.slice(1)}`
          translated = i18n.global.t(camelCaseKey)

          // 如果驼峰命名法也找不到，则尝试保持原始键的大小写
          if (translated === camelCaseKey) {
            // 使用直接匹配查找键（不区分大小写）
            const keys = Object.keys(i18n.global.messages.value[i18n.global.locale.value]?.route || {})
            const matchedKey = keys.find(key => key.toLowerCase() === routeTitleKey.toLowerCase())

            if (matchedKey) {
              translated = i18n.global.t(`route.${matchedKey}`)
            } else {
              // 如果什么都没找到，返回原始标题（无处理）
              console.warn(`[翻译警告] 找不到路由标题: ${routeTitleKey} 的翻译`)
              return routeTitleKey
            }
          }
        }
      }

      // 如果找到了翻译
      if (translated && translated !== lowerCaseKey) {
        return translated
      }
    } catch (e) {
      console.warn('Translation key not found for route title:', routeTitleKey, e)
    }

    // 如果没有找到翻译，返回原始标题
    return routeTitleKey
  }

  // 获取页面标题（结合路由标题和网站名称）
  const getPageTitle = (routeTitle) => {
    // 如果没有提供路由标题，则只返回网站标题
    if (!routeTitle) {
      return localizedSiteTitle.value
    }

    // 获取路由的国际化标题
    const translatedTitle = getRouteTitle(routeTitle)

    // 页面标题 - 网站名称
    return `${translatedTitle} - ${localizedSiteTitle.value}`
  }

  // 更新当前页面标题
  const updatePageTitle = () => {
    const route = window.router?.currentRoute?.value
    if (route && route.meta && route.meta.title) {
      document.title = getPageTitle(route.meta.title)

      // 添加事件监听，在语言变更时更新标题
      window.addEventListener('language-changed', () => {
        document.title = getPageTitle(route.meta.title)
      }, { once: true })
    } else {
      document.title = localizedSiteTitle.value
    }
  }

  return {
    config,
    loading,
    initialized,
    localizedSiteTitle,
    fetchConfig,
    getPageTitle,
    updatePageTitle
  }
})