// C端博客配置 Store
// 用于在博客前台应用中集中管理从服务器获取的网站配置信息

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { apiUrls } from '../api'

export const useBlogConfigStore = defineStore('blogConfig', () => {
  // 初始配置数据
  const config = ref({
    site_name: '',
    site_title: '',
    site_description: '',
    site_keywords: '',
    site_logo: '',
    site_favicon: '',
    Favicon: '',
    site_copyright: '',
    site_public_security_record: '',
    site_icp: '',
    contact_email: '',
    nav_links: [],
    footer_links: [],
    social_links: [],
    about_us_introduction: '',
    about_us_history: '',
    about_us_mission: '',
    about_us_vision: '',
    contact_phone: '',
    contact_address: '',
    contact_form_enabled: false,
    contact_email_notification: false,
    contact_notification_email: '',
    register_enabled: false,
    comment_enabled: false,
    comment_need_audit: false,
    maintenance_mode: false,
    maintenance_message: '',
  })

  // 加载状态
  const loading = ref(false)
  const initialized = ref(false)
  // 网站logo
  const siteLogo = computed(() => config.value.site_logo)

  // 网站图标
  const siteFavicon = computed(() => config.value.Favicon || config.value.site_favicon)

  // 网站名称
  const siteName = computed(() => config.value.site_name)

  // 网站标题
  const siteTitle = computed(() => config.value.site_title)

  // 网站描述
  const siteDescription = computed(() => config.value.site_description)

  // 联系邮箱
  const contactEmail = computed(() => config.value.contact_email || 'contact@example.com')

  // 获取网站配置
  const fetchConfig = async () => {
    if (loading.value) return

    loading.value = true

    try {
      // 调用博客配置接口
      const response = await api.get(apiUrls.BLOG_API.BLOG_CONFIG)

      // 打印完整的原始响应
      //console.log('完整API响应:', response);

      if (response && response.code === 200) {
        // 尝试从各种可能的位置获取数据
        let extractedData = null;

        // 检查响应中data字段
        if (response.data) {
        //  console.log('响应中存在data字段:', response.data);
          extractedData = response.data;
        }
        // 检查响应本身是否包含配置数据
        else if (response.site_name || response.site_title) {
       //   console.log('配置数据可能在响应根级别:', response);
          extractedData = response;
        }

        // 如果没有提取到数据
        if (!extractedData) {
          console.warn('未能从响应中提取数据，尝试寻找其他可能位置');

          // 检查响应中的所有字段，寻找可能包含对象的字段
          for (const key in response) {
            const value = response[key];
            if (typeof value === 'object' && value !== null) {
             //(`检查响应字段 ${key}:`, value);

              // 检查这个字段是否包含配置数据
              if (value.site_name || value.site_title ||
                  (Array.isArray(value) && value.length > 0)) {
              //  console.log(`可能在 ${key} 字段中找到配置数据:`, value);
                extractedData = value;
                break;
              }

              // 再深入一层
              for (const subKey in value) {
                const subValue = value[subKey];
                if (typeof subValue === 'object' && subValue !== null) {
               //  console.log(`检查 ${key}.${subKey}:`, subValue);
                  if (subValue.site_name || subValue.site_title) {
                 //   console.log(`可能在 ${key}.${subKey} 中找到配置数据:`, subValue);
                    extractedData = subValue;
                    break;
                  }
                }
              }
            }
          }
        }

        // 如果仍然没有提取到数据，检查是否为字符串JSON
        if (!extractedData && typeof response.data === 'string') {
          try {
         //   console.log('尝试解析字符串数据为JSON');
            const parsedData = JSON.parse(response.data);
         //   console.log('解析结果:', parsedData);
            if (typeof parsedData === 'object' && parsedData !== null) {
              extractedData = parsedData;
            }
          } catch (e) {
            console.warn('字符串解析失败:', e);
          }
        }

        // 现在合并提取到的数据
        if (extractedData) {
       //   console.log('提取到的数据:', extractedData);
      //    console.log('合并前的配置:', { ...config.value });

          // 首先检查extractedData是否是数组
          if (Array.isArray(extractedData)) {
         //   console.log('数据是数组格式，尝试处理');
            extractedData.forEach(item => {
              if (item && item.name && item.value !== undefined) {
                if (config.value.hasOwnProperty(item.name)) {
          //       console.log(`从数组项更新字段 ${item.name}:`, item.value);
                  config.value[item.name] = item.value;
                }
              }
            });
          }
          // 对象格式处理
          else if (typeof extractedData === 'object') {
            // 直接合并所有匹配的字段
            for (const key in config.value) {
              // 检查提取的数据是否直接包含该字段
              if (extractedData[key] !== undefined) {
                config.value[key] = extractedData[key];
         //       console.log(`更新字段 ${key}:`, extractedData[key]);
              }
            }

            // 检查特殊字段名称映射（API可能使用不同的字段名）
            const fieldMappings = {
              'name': 'site_name',
              'title': 'site_title',
              'description': 'site_description',
              'keywords': 'site_keywords',
              'logo': 'site_logo',
              'favicon': 'site_favicon',
              'Favicon': 'Favicon',
              'copyright': 'site_copyright',
              'icp': 'site_icp'
            };

            for (const apiField in fieldMappings) {
              const configField = fieldMappings[apiField];
              if (extractedData[apiField] !== undefined) {
           //     console.log(`通过字段映射更新 ${configField}:`, extractedData[apiField]);
                config.value[configField] = extractedData[apiField];
              }
            }

            // 检查嵌套对象
            for (const key in extractedData) {
              const value = extractedData[key];
              if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
          //      console.log(`检查嵌套对象 ${key}:`, value);
                for (const configKey in config.value) {
                  if (value[configKey] !== undefined) {
          //          console.log(`从嵌套对象更新 ${configKey}:`, value[configKey]);
                    config.value[configKey] = value[configKey];
                  }
                }
              }
            }
          }

        //  console.log('合并后的配置:', { ...config.value });
        } else {
          console.warn('无法找到可用的配置数据');
        }

        // 如果网站标题为空，使用网站名称
        if (!config.value.site_title && config.value.site_name) {
          config.value.site_title = config.value.site_name;
     //     console.log('使用网站名称作为标题:', config.value.site_title);
        }

        // 更新网站图标
        updateFavicon(config.value.Favicon || config.value.site_favicon)

        initialized.value = true

        // 将配置保存到localStorage，便于预加载脚本使用
        try {
          localStorage.setItem('website_config', JSON.stringify(config.value))
        } catch (e) {
          console.warn('将网站配置保存到localStorage失败:', e)
        }

        // 更新页面标题
        updatePageTitle()
      } else {
        console.warn('API请求成功但格式不正确:', response);
        // 设置默认值
        setDefaultConfig();
      }
    } catch (error) {
      console.error('获取博客配置失败:', error);
      // 设置默认值
      setDefaultConfig();
    } finally {
      loading.value = false
    }
  }

  // 设置默认配置
  const setDefaultConfig = () => {
    config.value.site_name = '';
    config.value.site_title = '';
    config.value.site_description = '';
    config.value.site_logo = '';
    config.value.site_copyright = '';
    initialized.value = true;
  }

  // 更新网站图标
  const updateFavicon = (faviconUrl) => {
    if (!faviconUrl) {
      // 检查是否有Favicon字段
      if (config.value.Favicon) {
        faviconUrl = config.value.Favicon
      } else {
        return
      }
    }

    const link = document.querySelector("link[rel*='icon']") || document.createElement('link')
    link.type = 'image/x-icon'
    link.rel = 'shortcut icon'
    link.href = faviconUrl
    document.getElementsByTagName('head')[0].appendChild(link)
  }

  // 更新当前页面标题
  const updatePageTitle = () => {
    const route = window.router?.currentRoute?.value
    if (route && route.meta && route.meta.title) {
      document.title = `${route.meta.title} - ${config.value.site_title}`
    } else {
      document.title = config.value.site_title
    }
  }

  // 获取页面标题（结合路由标题和网站名称）
  const getPageTitle = (routeTitle) => {
    // 如果没有提供路由标题，则只返回网站标题
    if (!routeTitle) {
      return config.value.site_title
    }

    // 页面标题 - 网站名称
    return `${routeTitle} - ${config.value.site_title}`
  }

  return {
    config,
    loading,
    initialized,
    siteName,
    siteTitle,
    siteLogo,
    siteDescription,
    siteFavicon,
   contactEmail,
    fetchConfig,
    updatePageTitle,
    getPageTitle
  }
})