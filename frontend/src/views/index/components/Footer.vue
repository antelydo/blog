<!-- Footer component -->
<template>
  <div class="footer-wrapper" :class="{'force-dark-theme': isDarkMode}">


    <!-- Main footer -->
    <footer class="site-footer" :class="{'force-dark-theme': isDarkMode}">
      <div class="container">


        <!-- Footer copyright -->
        <div class="footer-copyright">
          <p>© 2010-{{ currentYear }} {{ siteName }} {{ siteCopyright }} <router-link to="/sitemap">{{ t('footer.copyright.sitemap') }}</router-link></p>
          <div class="beian-info" v-if="site_icp || site_public_security_record">
            <a v-if="site_icp" :href="'https://beian.miit.gov.cn/'" target="_blank" rel="noopener noreferrer" class="icp-link">{{ site_icp }}</a>
            <span v-if="site_icp && site_public_security_record" class="beian-separator">|</span>
            <a v-if="site_public_security_record" href="http://www.beian.gov.cn/portal/index" target="_blank" rel="noopener noreferrer" class="security-record-link">
              <img src="../../../assets/images/gaba.png" alt="公安备案" class="gongan-icon" />
              {{ site_public_security_record }}
            </a>
          </div>
        </div>
      </div>

         <!-- Footer links -->
         <div class="footer-links">
          <router-link to="/links" class="footer-link"><i class="fa fa-link"></i> {{ t('footer.links.friendlyLinks') }}</router-link>
          <span class="link-separator">|</span>
          <router-link to="/about" class="footer-link"><i class="fa fa-info-circle"></i> {{ t('footer.links.aboutSite') }}</router-link>
          <span class="link-separator">|</span>
          <router-link to="/contact" class="footer-link"><i class="fa fa-envelope"></i> {{ t('footer.links.contactUs') }}</router-link>
        </div>

    </footer>


  </div>
</template>

<script>
import { useBlogConfigStore } from '../../../stores/blogConfig';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useThemeStore } from '../../../stores/theme';

export default {
  name: 'SiteFooter',
  setup() {
    const blogConfigStore = useBlogConfigStore();
    const { t } = useI18n();

    // 判断是否为暗黑模式
    const themeStore = useThemeStore();
    const isDarkMode = computed(() => {
      return themeStore.theme === 'dark' ||
             (themeStore.theme === 'system' &&
              window.matchMedia &&
              window.matchMedia('(prefers-color-scheme: dark)').matches);
    });

    return {
      t,
      currentYear: new Date().getFullYear(),
      siteName: computed(() => blogConfigStore.config.site_name),
      siteCopyright: computed(() => blogConfigStore.config.site_copyright),
      site_public_security_record:  computed(() => blogConfigStore.config.site_public_security_record),
      site_icp:  computed(() => blogConfigStore.config.site_icp),
      isDarkMode
    };
  },
  mounted() {
    // 添加页脚结构化数据
    this.addStructuredData()
  },
  methods: {
    // 添加页脚结构化数据
    addStructuredData() {
      // 移除已有的结构化数据
      const existingScript = document.getElementById('footer-structured-data');
      if (existingScript) {
        existingScript.remove();
      }

      // 创建页脚结构化数据
      const structuredData = {
        "@context": "https://schema.org",
        "@type": "WPFooter",
        "copyrightYear": new Date().getFullYear(),
        "copyrightHolder": {
          "@type": "Organization",
          "name": this.siteName || document.title,
          "url": window.location.origin
        }
      };

      // 创建并添加脚本标签
      const script = document.createElement('script');
      script.id = 'footer-structured-data';
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify(structuredData);
      document.head.appendChild(script);
    },
    goToTop() {
    // ... existing code ...
    }
  },
}
</script>

<style scoped>
/* Pre-footer section */
.footer-wrapper {
  margin-top: 30px;
  font-family: 'Microsoft YaHei', Arial, sans-serif;
  background-color: var(--bg-color, #f8f8f8);
  color: var(--text-color, #666);
  transition: all 0.3s ease;
  will-change: background-color, color;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  transform: translateZ(0);
  position: relative;
}

.pre-footer {
  background-color: #09c;
  color: #fff;
  padding: 30px 0;
  text-align: center;
}

.pre-footer-title {
  font-size: 24px;
  margin-bottom: 10px;
  font-weight: bold;
}

.pre-footer-desc {
  font-size: 14px;
  max-width: 800px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Main footer */
.site-footer {
  background-color: var(--card-bg-color, #fff);
  padding: 40px 0 20px;
  color: var(--text-color, #666);
  border-top: 1px solid var(--border-color, #eee);
  transition: all 0.3s ease;
  will-change: background-color, color, border-color;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  transform: translateZ(0);
  position: relative;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
  width: 100%;
  box-sizing: border-box;
}

.footer-widgets {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 20px;
  justify-content: space-between;
}

.widget {
  flex: 1;
  padding: 0 15px;
  margin-bottom: 20px;
  min-width: 200px;
}

.widget-title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  margin-bottom: 15px;
  position: relative;
  padding-bottom: 8px;
  border-bottom: 1px solid #eee;
}

.widget-title:after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 40px;
  height: 1px;
  background-color: #09c;
}

.widget-content {
  font-size: 14px;
  color: #666;
  line-height: 1.6;
}

.contact-links {
  display: flex;
  align-items: center;
}

.contact-link {
  display: inline-block;
  background-color: #09c;
  color: #fff;
  padding: 8px 15px;
  border-radius: 3px;
  text-decoration: none;
  font-size: 14px;
  transition: background-color 0.3s;
  font-weight: bold;
}

.contact-link:hover {
  background-color: #007fad;
}

.friend-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.friend-links li {
  margin-bottom: 10px;
  padding-left: 0;
}

.friend-links a {
  color: var(--text-color, #666);
  text-decoration: none;
  transition: color 0.3s;
  position: relative;
  display: inline-block;
}

.friend-links a::before {
  content: '•';
  margin-right: 5px;
  color: #09c;
  font-weight: bold;
}

.friend-links a:hover {
  color: #09c;
}

.custom-widget .widget-content {
  padding: 15px;
  border: 1px dashed var(--border-color, #eee);
  font-style: italic;
  color: var(--light-text-color, #999);
  background: var(--bg-color, #fafafa);
  border-radius: 3px;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.footer-copyright {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid var(--border-color, #eee);
  color: var(--light-text-color, #999);
  font-size: 13px;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.footer-copyright a {
  color: var(--primary-color, #09c);
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-copyright a:hover {
  text-decoration: underline;
}

.beian-info {
  margin-top: 8px;
  margin-bottom: 8px;
  font-size: 12px;
  color: var(--light-text-color, #999);
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
}

.beian-info a {
  color: var(--light-text-color, #999);
  text-decoration: none;
  transition: color 0.3s ease;
}

.beian-info a:hover {
  color: var(--primary-color, #09c);
}

.beian-separator {
  margin: 0 5px;
}

.security-record-link {
  display: inline-flex;
  align-items: center;
}

.gongan-icon {
  width: 14px;
  height: 14px;
  margin-right: 4px;
}

/* Floating action buttons */
.floating-actions {
  position: fixed;
  right: 20px;
  bottom: 100px;
  z-index: 9999;
}

.action-item {
  display: block;
  width: 40px;
  height: 40px;
  line-height: 40px;
  text-align: center;
  margin-bottom: 1px;
  color: #fff;
  position: relative;
  border: none;
  border-radius: 2px;
  transition: all 0.3s;
  cursor: pointer;
  background-color: #666;
  box-shadow: 0 0 6px rgba(0, 0, 0, 0.12);
  padding: 0;
  font-size: 16px;
  font-family: inherit;
  z-index: 10000; /* Ensure it's clickable */
  pointer-events: auto; /* Ensure clicks register */
}

.action-item:hover {
  background-color: var(--bg-color-hover, #555);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
  transition: background-color 0.3s ease;
}

.action-item i {
  font-size: 18px;
}

.action-text {
  position: absolute;
  right: 42px;
  top: 0;
  background-color: inherit;
  padding: 0 15px;
  white-space: nowrap;
  height: 40px;
  line-height: 40px;
  font-size: 13px;
  display: none;
  border-radius: 2px 0 0 2px;
  box-shadow: -1px 0 3px rgba(0, 0, 0, 0.1);
}

.action-item:hover .action-text {
  display: block;
}

/* Action button colors */
.action-item.qq {
  background-color: #12b7f5;
}

.action-item.phone {
  background-color: #f5b60e;
}

.action-item.wechat {
  background-color: #7bb32e;
}

.action-item.chat {
  background-color: #f56c6c;
}

.action-item.top {
  background-color: #666;
}

/* Responsive styles */
@media (max-width: 768px) {
  .pre-footer-title {
    font-size: 20px;
  }

  .pre-footer-desc {
    font-size: 13px;
  }

  .footer-widgets {
    flex-direction: column;
  }

  .widget {
    width: 100%;
    padding: 0;
  }

  .floating-actions {
    right: 10px;
    bottom: 60px;
  }

  .action-item {
    width: 36px;
    height: 36px;
    line-height: 36px;
  }

  .action-text {
    right: 38px;
    height: 36px;
    line-height: 36px;
    font-size: 12px;
  }

  /* 修复手机端footer显示问题 */
  .site-footer {
    padding: 20px 0 10px;
  }

  .footer-copyright {
    padding-top: 15px;
    font-size: 12px;
  }

  .footer-links {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding-top: 10px;
    margin-bottom: 5px;
  }

  .footer-link {
    font-size: 12px;
    margin: 0 5px;
  }

  .beian-info {
    flex-direction: column;
    gap: 5px;
  }

  .beian-separator {
    display: none;
  }

  .security-record-link {
    margin-top: 5px;
  }
}

/* Footer links */
.footer-links {
  text-align: center;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-top: 1px solid var(--border-color, #eee);
  padding-top: 15px;
  transition: border-color 0.3s ease;
}

.footer-link {
  color: var(--light-text-color, #666);
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s;
  margin: 0 10px;
  display: inline-flex;
  align-items: center;
}

.footer-link i {
  margin-right: 4px;
  font-size: 14px;
}

.footer-link:hover {
  color: #09c;
  text-decoration: underline;
}

.link-separator {
  color: var(--light-text-color, #999);
  margin: 0 5px;
  transition: color 0.3s ease;
}
</style>