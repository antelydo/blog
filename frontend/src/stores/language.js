import { defineStore } from 'pinia'
import { ref } from 'vue'
import i18n from '@/plugins/i18n'

export const useLanguageStore = defineStore('language', () => {
  // Get saved language from localStorage, default to 'zh'
  const savedLanguage = localStorage.getItem('language') || 'zh'
  const language = ref(savedLanguage)

  // Set language
  const setLanguage = (newLanguage) => {
    // Update language in state
    language.value = newLanguage
    
    // Update i18n locale
    i18n.global.locale.value = newLanguage
    
    // Save to localStorage
    localStorage.setItem('language', newLanguage)
    
    // Update html lang attribute
    document.documentElement.setAttribute('lang', newLanguage)
    
    // 延迟一下等待i18n更新完成
    setTimeout(() => {
      // 触发自定义事件，通知其他组件语言已更改
      const event = new CustomEvent('language-changed', { detail: { lang: newLanguage } })
      window.dispatchEvent(event)
      
      // 更新当前路由的页面标题
      updateRouteTitle()
    }, 10)
  }

  // 更新当前路由的页面标题
  const updateRouteTitle = () => {
    const currentRoute = window.router?.currentRoute?.value
    if (currentRoute?.meta?.title && window.getPageTitle) {
      document.title = window.getPageTitle(currentRoute.meta.title)
    }
  }

  // Initialize language settings
  const initLanguage = () => {
    // Set initial language
    i18n.global.locale.value = language.value
    document.documentElement.setAttribute('lang', language.value)
    
    // 初始化时也触发一次语言变更事件，确保页面标题正确设置
    const event = new CustomEvent('language-changed', { detail: { lang: language.value } })
    window.dispatchEvent(event)
  }

  // Get language name
  const getLanguageName = (lang) => {
    const names = {
      'zh': '中文',
      'en': 'English'
    }
    return names[lang] || lang
  }

  return {
    language,
    setLanguage,
    initLanguage,
    getLanguageName
  }
}) 