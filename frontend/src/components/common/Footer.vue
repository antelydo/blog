<!-- 页脚 -->
<template>
  <div class="footer">
    <div class="footer-content">
      <div class="footer-info">
        <span v-if="siteConfig.site_name">{{ siteConfig.site_name }}</span>
        <span v-if="siteConfig.site_icp" class="icp-info">
          <a :href="'https://beian.miit.gov.cn/'" target="_blank" rel="noopener noreferrer" :title="'ICP备案信息: ' + siteConfig.site_icp">{{ siteConfig.site_icp }}</a>
        </span>
        <span v-if="siteConfig.site_public_security_record" class="security-record-info">
          <a href="http://www.beian.gov.cn/portal/index" target="_blank" rel="noopener noreferrer" :title="'公安备案信息: ' + siteConfig.site_public_security_record">
            <img src="../../assets/images/gaba.png" alt="公安备案" class="gongan-icon" />
            {{ siteConfig.site_public_security_record }}
          </a>
        </span>
        <span v-if="siteConfig.copyright" class="copyright">{{ siteConfig.copyright }}</span>
        <span v-if="siteConfig.version" class="version">v{{ siteConfig.version }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api, { apiUrls } from '@/api'

// 网站配置信息
const siteConfig = ref({
  site_name: 'AichatUi',
  site_icp: '',  // 修改字段名称，与后端返回的数据结构匹配
  site_public_security_record: '', // 公安备案号
  copyright: '© ' + new Date().getFullYear() + ' AichatUi All Rights Reserved',
  version: '1.0.0'
})

// 获取网站配置
const fetchSiteConfig = async () => {
  try {
    const response = await api.post(apiUrls.ADMIN_API.CONFIG)
    if (response && response.code === 200) {
      const data = response.data

      // 将后端返回的配置数组转换为对象格式
      if (Array.isArray(data)) {
        data.forEach(item => {
          if (item && item.name && siteConfig.value.hasOwnProperty(item.name)) {
            siteConfig.value[item.name] = item.value || ''
          }
        })
      } else if (typeof data === 'object') {
        // 如果是对象格式，直接赋值
        Object.keys(siteConfig.value).forEach(key => {
          if (data[key] !== undefined) {
            siteConfig.value[key] = data[key]
          }
        })
      }

      // 确保版权信息包含当前年份
      if (siteConfig.value.copyright && !siteConfig.value.copyright.includes(new Date().getFullYear())) {
        siteConfig.value.copyright = '© ' + new Date().getFullYear() + ' ' + siteConfig.value.site_name + ' All Rights Reserved'
      }
    }
  } catch (error) {
    console.error('获取网站配置失败:', error)
    // 确保即使API调用失败，也显示基本信息
    siteConfig.value.site_name = 'AichatUi'
    siteConfig.value.copyright = '© ' + new Date().getFullYear() + ' AichatUi All Rights Reserved'
  }
}

onMounted(() => {
  fetchSiteConfig()
})
</script>

<style scoped>
.footer {
  width: 100%;
  background-color: var(--el-bg-color);
  padding: 15px 0;
  text-align: center;
  font-size: 14px;
  color: var(--el-text-color-regular);
  border-top: 1px solid var(--el-border-color-light);
  margin-top: auto; /* 确保Footer始终在底部 */
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  will-change: background-color, color, border-color;
}

/* 明确设置不同主题下的颜色以便快速切换 */
:global(.dark-theme) .footer {
  background-color: #232324;
  color: #909399;
  border-top-color: rgba(255, 255, 255, 0.05);
}

:global(.system-dark-theme) .footer,
:global(html.dark.system-theme) .footer {
  background-color: #141414;
  color: #909399;
  border-top-color: rgba(255, 255, 255, 0.05);
}

:global(.light-theme) .footer {
  background-color: #ffffff;
  color: #909399;
  border-top-color: #ebeef5;
}

:global(.system-light-theme) .footer,
:global(html.light.system-theme) .footer {
  background-color: #ffffff;
  color: #909399;
  border-top-color: #ebeef5;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.footer-info {
  display: flex;
  flex-wrap: nowrap;
  justify-content: center;
  align-items: center;
  gap: 15px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.footer-info span {
  color: var(--el-text-color-regular);
}

.footer-info a {
  color: var(--el-text-color-regular);
  text-decoration: none;
  transition: color 0.3s;
}

.footer-info a:hover {
  color: var(--el-color-primary);
}

.icp-info {
  display: inline-flex;
  align-items: center;
}

.security-record-info {
  display: inline-flex;
  align-items: center;
}

.security-record-info a {
  display: flex;
  align-items: center;
  gap: 5px;
}

.gongan-icon {
  width: 14px;
  height: 14px;
  margin-right: 2px;
}

.copyright {
  display: inline-flex;
  align-items: center;
}

.version {
  display: inline-flex;
  align-items: center;
  color: var(--el-text-color-secondary);
}

/* 响应式调整 */
@media (max-width: 768px) {
  .footer-info {
    flex-direction: column;
    gap: 8px;
  }

  .footer {
    padding: 10px 0;
    font-size: 12px;
  }
}
</style>