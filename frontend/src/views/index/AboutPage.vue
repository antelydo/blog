<!-- 关于页 -->
<template>
  <div class="blog-container">
    <Header />
    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <h1 class="page-title">{{ $t('webAbout.title') }}</h1>
          <p class="page-description">{{ $t('webAbout.subtitle') }}</p>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="card main-card">
              <div class="widget-content p-20" v-loading="loading">
                <!-- 网站简介 -->
                <section class="about-section">
                  <h2 class="section-title">{{ $t('webAbout.introduction.title') }}</h2>
                  <div class="about-content">
                    <div v-if="config.about_us_introduction" v-html="formatContent(config.about_us_introduction)"></div>
                    <p v-else>{{ $t('webAbout.introduction.content') }}</p>
                  </div>
                </section>




                <!-- 发展历程 -->
                <section class="about-section">
                  <h2 class="section-title">{{ $t('webAbout.history.title') }}</h2>
                  <div v-if="config.about_us_history" class="about-content">
                    <div v-html="formatContent(config.about_us_history)"></div>
                  </div>
                  <div v-else class="timeline">
                    <div v-for="(item, index) in $t('webAbout.history.timeline')" :key="index" class="timeline-item">
                      <div class="timeline-dot"></div>
                      <div class="timeline-content">
                        <h3 class="timeline-date">{{ item.year }}</h3>
                        <div class="timeline-body">
                          <p>{{ item.event }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <!-- 使命与愿景 -->
                <section class="about-section">
                  <h2 class="section-title">{{ $t('webAbout.mission.title') }} & {{ $t('webAbout.vision.title') }}</h2>
                  <div class="mission-vision">
                    <div class="mission-box">
                      <h3>{{ $t('webAbout.mission.title') }}</h3>
                      <div v-if="config.about_us_mission" v-html="formatContent(config.about_us_mission)"></div>
                      <p v-else>{{ $t('webAbout.mission.content') }}</p>
                    </div>

                    <div class="vision-box">
                      <h3>{{ $t('webAbout.vision.title') }}</h3>
                      <div v-if="config.about_us_vision" v-html="formatContent(config.about_us_vision)"></div>
                      <p v-else>{{ $t('webAbout.vision.content') }}</p>
                    </div>
                  </div>
                </section>

                <!-- 联系我们 -->
                <section class="about-section">
                  <h2 class="section-title">{{ $t('webAbout.contact.title') }}</h2>
                  <div class="contact-info">
                    <div class="contact-item">
                      <div class="contact-icon">
                        <i class="fa fa-envelope"></i>
                      </div>
                      <div class="contact-detail">
                        <h3>{{ $t('webAbout.contact.email') }}</h3>
                        <p><a :href="'mailto:' + (config.contact_email)">{{ config.contact_email}}</a></p>
                      </div>
                    </div>

                    <div class="contact-item">
                      <div class="contact-icon">
                        <i class="fa fa-phone"></i>
                      </div>
                      <div class="contact-detail">
                        <h3>{{ $t('webAbout.contact.phone') }}</h3>
                        <p>{{config.contact_phone}}</p>
                      </div>
                    </div>

                  </div>
                </section>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <Sidebar />
          </div>
        </div>
      </div>
    </main>
    <Footer />
    <BackToTop />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import Sidebar from './components/Sidebar.vue';
import BackToTop from './components/BackToTop.vue';
import { useBlogConfigStore } from '@/stores/blogConfig';
import './assets/blog.css';
import { useHead } from '@vueuse/head';

export default {
  name: 'AboutPage',
  components: {
    Header,
    Footer,
    Sidebar,
    BackToTop
  },
  setup() {
    const { t } = useI18n();
    const configStore = useBlogConfigStore();
    const config = ref({});
    const loading = ref(true);
    const siteName = computed(() => configStore.config.site_name || '');
    const siteDescription = computed(() => configStore.config.site_description || '');

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `关于我们 - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => `关于${siteName.value}的详细介绍。${siteDescription.value}`)
        },
        {
          name: 'keywords',
          content: '关于我们,网站介绍,网站历史,团队介绍'
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `关于我们 - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => `关于${siteName.value}的详细介绍。${siteDescription.value}`)
        },
        {
          property: 'og:type',
          content: 'website'
        },
        {
          property: 'og:url',
          content: computed(() => window.location.href)
        },
        // Twitter Card标签
        {
          name: 'twitter:card',
          content: 'summary'
        },
        {
          name: 'twitter:title',
          content: computed(() => `关于我们 - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => `关于${siteName.value}的详细介绍。${siteDescription.value}`)
        },
        // 其他重要SEO标签
        {
          name: 'robots',
          content: 'index, follow'
        }
      ],
      link: [
        {
          rel: 'canonical',
          href: computed(() => window.location.href)
        }
      ]
    });

    // 格式化内容，支持换行符转换为HTML
    const formatContent = (content) => {
      if (!content) return '';

      // 检查内容是否已经包含HTML标签
      if (content.includes('<') && content.includes('>')) {
        // 如果已经包含HTML标签，则直接返回
        return content;
      }

      // 将换行符转换为HTML的<br>
      // 首先，将多个连续的换行符转换为段落
      let formatted = content.replace(/\n\s*\n/g, '</p><p>');

      // 然后，将单个换行符转换为<br>
      formatted = formatted.replace(/\n/g, '<br>');

      // 如果内容被处理了，则加上p标签包裹
      if (formatted !== content) {
        formatted = '<p>' + formatted + '</p>';
        // 修复任何可能的空段落
        formatted = formatted.replace(/<p><\/p>/g, '');
      }

      return formatted;
    };

    onMounted(async () => {
      try {
        // 从配置存储中获取配置数据
        await configStore.fetchConfig();
        config.value = configStore.config;

      } catch (error) {
        console.error('获取配置数据出错:', error);
      } finally {
        loading.value = false;
      }
    });

    return {
      t,
      config,
      loading,
      formatContent,
      siteName,
      siteDescription
    };
  }
};
</script>

<style scoped>
.page-header {
  margin-bottom: 20px;
  text-align: center;
  padding: 40px 0 20px;
  border-bottom: 1px solid var(--border-color);
  background-color: var(--card-bg-color);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
}

.page-title {
  font-size: 28px;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 700;
}

.page-description {
  color: var(--light-text-color);
  font-size: 16px;
}

.main-card {
  min-height: 400px;
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
  overflow: hidden;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.about-section {
  margin-bottom: 40px;
}

.about-content {
  line-height: 1.8;
  color: var(--text-color);
}

.about-content p {
  margin-bottom: 15px;
  text-align: justify;
}

/* 团队成员样式 */
.team-members {
  margin-top: 30px;
}

.team-member {
  display: flex;
  margin-bottom: 30px;
  padding-bottom: 30px;
  border-bottom: 1px dashed var(--border-color);
}

.team-member:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.member-avatar {
  width: 120px;
  height: 120px;
  flex-shrink: 0;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 20px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  border: 3px solid #fff;
}

.member-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.member-info {
  flex: 1;
}

.member-name {
  font-size: 20px;
  color: var(--heading-color);
  margin: 0 0 5px;
}

.member-role {
  font-size: 14px;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 500;
}

.member-desc {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text-color);
}

/* 时间线样式 */
.timeline {
  position: relative;
  padding: 20px 0;
  margin-top: 30px;
}

.timeline:before {
  content: '';
  position: absolute;
  top: 0;
  left: 15px;
  height: 100%;
  width: 2px;
  background: var(--primary-color);
}

.timeline-item {
  position: relative;
  margin-bottom: 30px;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: 12px;
  top: 5px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--primary-color);
  border: 2px solid var(--card-bg-color, #fff);
  box-shadow: 0 0 0 4px rgba(9, 204, 204, 0.2);
  transition: border-color 0.3s ease;
}

.timeline-content {
  margin-left: 40px;
  background: var(--card-bg-color, white);
  padding: 15px;
  border-radius: var(--border-radius);
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  position: relative;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.timeline-content:before {
  content: '';
  position: absolute;
  left: -10px;
  top: 10px;
  width: 0;
  height: 0;
  border-top: 8px solid transparent;
  border-bottom: 8px solid transparent;
  border-right: 10px solid var(--card-bg-color, white);
  transition: border-right-color 0.3s ease;
}

.timeline-date {
  font-size: 16px;
  color: var(--primary-color);
  margin: 0 0 10px;
  font-weight: 600;
}

.timeline-body h4 {
  margin: 0 0 5px;
  color: var(--heading-color);
}

.timeline-body p {
  margin: 0;
  color: var(--text-color);
  font-size: 14px;
}

/* 使命与愿景样式 */
.mission-vision {
  display: flex;
  gap: 20px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.mission-box, .vision-box {
  flex: 1;
  min-width: 250px;
  padding: 20px;
  border-radius: var(--border-radius);
  border-top: 4px solid var(--primary-color);
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.mission-box h3, .vision-box h3 {
  color: var(--primary-color);
  margin: 0 0 15px;
  font-size: 18px;
  position: relative;
  padding-bottom: 10px;
}

.mission-box h3:after, .vision-box h3:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background-color: var(--primary-color);
}

.mission-box p, .vision-box p {
  margin: 0;
  line-height: 1.6;
  font-size: 14px;
  color: var(--text-color);
}

/* 联系信息样式 */
.contact-info {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.contact-item {
  padding: 20px;
  border-radius: var(--border-radius);
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  align-items: flex-start;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.contact-icon {
  width: 40px;
  height: 40px;
  background-color: var(--primary-color);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 18px;
  flex-shrink: 0;
}

.contact-detail {
  flex: 1;
}

.contact-detail h3 {
  font-size: 16px;
  color: var(--heading-color);
  margin: 0 0 8px;
}

.contact-detail p {
  margin: 0;
  font-size: 14px;
  color: var(--text-color);
}

.contact-detail a {
  color: var(--primary-color);
  text-decoration: none;
  transition: color 0.3s;
}

.contact-detail a:hover {
  text-decoration: underline;
}

.qrcode {
  margin-top: 10px;
  text-align: center;
}

.qrcode img {
  max-width: 120px;
  border: 1px solid var(--border-color, #eee);
  padding: 5px;
  background: var(--card-bg-color, white);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .team-member {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .member-avatar {
    margin-right: 0;
    margin-bottom: 15px;
  }

  .mission-vision {
    flex-direction: column;
  }

  .contact-info {
    grid-template-columns: 1fr;
  }

  .page-title {
    font-size: 24px;
  }

  .section-title {
    font-size: 18px;
  }
}

@media (max-width: 480px) {
  .member-avatar {
    width: 100px;
    height: 100px;
  }

  .timeline:before {
    left: 10px;
  }

  .timeline-dot {
    left: 7px;
  }

  .timeline-content {
    margin-left: 30px;
    padding: 10px;
  }

  .contact-item {
    padding: 15px;
  }

  .page-title {
    font-size: 22px;
  }
}
</style>