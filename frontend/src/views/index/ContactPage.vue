<!-- 联系页 -->
<template>
  <div class="blog-container">
    <Header />
    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <h1 class="page-title">{{ $t('webContact.title') }}</h1>
          <p class="page-description">{{ $t('webContact.subtitle') }}</p>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="card main-card">
              <div class="widget-content p-20">
                <!-- 联系表单 -->
                <section class="contact-section">
                  <h2 class="section-title">{{ $t('webContact.form.title') }}</h2>
                  <div class="contact-form">
                    <form @submit.prevent="submitContactFormAction">
                      <div class="form-group">
                        <label for="name">{{ $t('webContact.form.name') }}</label>
                        <input
                          type="text"
                          id="name"
                          v-model="contactForm.name"
                          class="form-control"
                          required
                        />
                      </div>

                      <div class="form-group">
                        <label for="email">{{ $t('webContact.form.email') }}</label>
                        <input
                          type="email"
                          id="email"
                          v-model="contactForm.email"
                          class="form-control"
                          required
                        />
                      </div>

                      <div class="form-group">
                        <label for="subject">{{ $t('webContact.form.subject') }}</label>
                        <input
                          type="text"
                          id="subject"
                          v-model="contactForm.subject"
                          class="form-control"
                          required
                        />
                      </div>

                      <div class="form-group">
                        <label for="message">{{ $t('webContact.form.message') }}</label>
                        <textarea
                          id="message"
                          v-model="contactForm.message"
                          class="form-control"
                          rows="6"
                          required
                        ></textarea>
                      </div>

                      <div class="form-group">
                        <button
                          type="submit"
                          class="btn btn-primary"
                          :disabled="submitting"
                        >
                          {{ submitting ? $t('webContact.form.sending') : $t('webContact.form.send') }}
                        </button>
                      </div>
                    </form>

                    <div v-if="formSubmitted" class="alert alert-success mt-15">
                      {{ $t('webContact.form.success') }}
                    </div>
                  </div>
                </section>

                <!-- 联系信息 -->
                <section class="contact-info-section mt-30">
                  <h2 class="section-title">{{ $t('webContact.contact.title') }}</h2>
                  <div class="contact-info">

                    <div class="info-item">
                      <i class="fa fa-phone"></i>
                      <div class="info-content">
                        <h3>{{ $t('webContact.contact.phone') }}</h3>
                        <p><a v-if="config.contact_phone" :href="'tel:' + config.contact_phone">{{ config.contact_phone }}</a></p>
                      </div>
                    </div>

                    <div class="info-item">
                      <i class="fa fa-envelope"></i>
                      <div class="info-content">
                        <h3>{{ $t('webContact.contact.email') }}</h3>
                        <p><a v-if="config.contact_email" :href="'mailto:' + config.contact_email">{{ config.contact_email }}</a></p>
                      </div>
                    </div>

                    <div v-if="config.contact_address" class="info-item">
                      <i class="fa fa-map-marker"></i>
                      <div class="info-content">
                        <h3>{{ $t('webContact.contact.address') }}</h3>
                        <p>{{ config.contact_address }}</p>
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ElMessage } from 'element-plus';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import Sidebar from './components/Sidebar.vue';
import BackToTop from './components/BackToTop.vue';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { submitContactForm } from '@/api/contact';
import { useHead } from '@vueuse/head';
import './assets/blog.css';
// 已移除暗黑主题相关样式导入
// import './assets/dark-theme.css';
// import './assets/global-dark-theme.css';
// import './assets/dark-theme-typography.css';

export default {
  name: 'ContactPage',
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
    const submitting = ref(false);
    const formSubmitted = ref(false);

    const contactForm = reactive({
      name: '',
      email: '',
      subject: '',
      message: ''
    });

    const siteName = computed(() => configStore.config.site_name || '');

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `联系我们 - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => `欢迎联系${siteName.value}，我们期待您的反馈和建议。`)
        },
        {
          name: 'keywords',
          content: '联系我们,联系方式,反馈建议,服务咨询'
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `联系我们 - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => `欢迎联系${siteName.value}，我们期待您的反馈和建议。`)
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
          content: computed(() => `联系我们 - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => `欢迎联系${siteName.value}，我们期待您的反馈和建议。`)
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

    // 获取网站配置信息
    onMounted(async () => {
      try {
        // 从配置存储中获取配置数据
        await configStore.fetchConfig();
        config.value = configStore.config;
      } catch (error) {
        console.error('获取配置数据出错:', error);
      }
    });

    const submitContactFormAction = async () => {
      if (submitting.value) return;

      submitting.value = true;

      try {
        // 发送表单数据到API
        const response = await submitContactForm(contactForm);

        if (response.code === 200) {
          // 重置表单
          contactForm.name = '';
          contactForm.email = '';
          contactForm.subject = '';
          contactForm.message = '';

          // 显示成功消息
          formSubmitted.value = true;

          // 5秒后隐藏成功消息
          setTimeout(() => {
            formSubmitted.value = false;
          }, 5000);
        } else {
          ElMessage.error(response.msg || t('webContact.form.failed'));
        }
      } catch (error) {
        console.error('提交表单时出错:', error);
        ElMessage.error(t('webContact.form.failed'));
      } finally {
        submitting.value = false;
      }
    };

    return {
      contactForm,
      submitting,
      formSubmitted,
      submitContactFormAction,
      config,
      t,
      siteName
    };
  }
};
</script>

<style scoped>
/* 页面标题样式 */
.page-header {
  margin-bottom: 35px;
  padding: 25px 30px;
  border-radius: 8px;
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* 暗黑模式和跟随系统模式下的页面标题样式 */
html.dark-theme .page-header,
html.dark .page-header,
html.system-dark-theme .page-header,
html.dark.system-theme .page-header {
  background-color: var(--card-bg-color) !important;
  color: var(--text-color) !important;
}

.page-title {
  font-size: 28px;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 700;
}

/* 暗黑模式和跟随系统模式下的页面标题颜色 */
html.dark-theme .page-title,
html.dark .page-title,
html.system-dark-theme .page-title,
html.dark.system-theme .page-title {
  color: #ffffff !important;
}

.page-description {
  font-size: 16px;
  color: var(--text-color, #555);
  margin-bottom: 20px;
  transition: color 0.3s ease;
}

/* 暗黑模式和跟随系统模式下的页面描述颜色 */
html.dark-theme .page-description,
html.dark .page-description,
html.system-dark-theme .page-description,
html.dark.system-theme .page-description {
  color: #ffffff !important;
}

.contact-form {
  margin-top: 20px;
}

.form-group {
  margin-bottom: 20px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: var(--heading-color);
  text-align: left;
}

/* 暗黑模式和跟随系统模式下的表单标签颜色 */
html.dark-theme label,
html.dark label,
html.system-dark-theme label,
html.dark.system-theme label {
  color: #ffffff !important;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  font-family: var(--font-family);
  font-size: 14px;
  transition: border-color var(--transition-speed), background-color var(--transition-speed), color var(--transition-speed);
}

/* 暗黑模式和跟随系统模式下的表单控件样式 */
html.dark-theme .form-control,
html.dark .form-control,
html.system-dark-theme .form-control,
html.dark.system-theme .form-control {
  background-color: rgba(255, 255, 255, 0.05) !important;
  color: #ffffff !important;
  border-color: var(--border-color) !important;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

textarea.form-control {
  resize: vertical;
}

.btn {
  display: inline-block;
  padding: 10px 20px;
  border: none;
  border-radius: var(--border-radius);
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color var(--transition-speed), color var(--transition-speed);
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.alert {
  padding: 15px;
  border-radius: var(--border-radius);
  margin-top: 15px;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

/* 暗黑模式和跟随系统模式下的成功提示样式 */
html.dark-theme .alert-success,
html.dark .alert-success,
html.system-dark-theme .alert-success,
html.dark.system-theme .alert-success {
  background-color: rgba(21, 87, 36, 0.2) !important;
  color: #ffffff !important;
  border-color: rgba(195, 230, 203, 0.3) !important;
}

.mt-15 {
  margin-top: 15px;
}

.mt-30 {
  margin-top: 30px;
}

.contact-info {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  padding: 15px;
  background-color: var(--card-bg-color, #fff);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* 暗黑模式和跟随系统模式下的联系信息项背景颜色 */
html.dark-theme .info-item,
html.dark .info-item,
html.system-dark-theme .info-item,
html.dark.system-theme .info-item {
  background-color: var(--card-bg-color) !important;
  color: #ffffff !important;
}

.info-item i {
  font-size: 24px;
  color: var(--primary-color);
  margin-right: 15px;
  margin-top: 5px;
}

.info-content h3 {
  font-size: 16px;
  margin-bottom: 5px;
  color: var(--heading-color);
  text-align: left;
}

/* 暗黑模式和跟随系统模式下的联系信息标题颜色 */
html.dark-theme .info-content h3,
html.dark .info-content h3,
html.system-dark-theme .info-content h3,
html.dark.system-theme .info-content h3 {
  color: #ffffff !important;
}

.info-content p {
  margin-bottom: 5px;
  color: var(--text-color);
  text-align: left;
}

/* 暗黑模式和跟随系统模式下的联系信息内容颜色 */
html.dark-theme .info-content p,
html.dark .info-content p,
html.system-dark-theme .info-content p,
html.dark.system-theme .info-content p,
html.dark-theme .info-content p a,
html.dark .info-content p a,
html.system-dark-theme .info-content p a,
html.dark.system-theme .info-content p a {
  color: #ffffff !important;
}

.map-container {
  margin-top: 20px;
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--box-shadow);
}

.map-image {
  width: 100%;
  height: auto;
}
.contact-section .section-title,
.contact-info-section .section-title {
  text-align: left;
}

/* 暗黑模式和跟随系统模式下的区块标题颜色 */
html.dark-theme .section-title,
html.dark .section-title,
html.system-dark-theme .section-title,
html.dark.system-theme .section-title {
  color: #ffffff !important;
}
/* 暗黑模式和跟随系统模式下的主卡片样式 */
html.dark-theme .main-card,
html.dark .main-card,
html.system-dark-theme .main-card,
html.dark.system-theme .main-card {
  background-color: var(--card-bg-color) !important;
  color: #ffffff !important;
  border-color: var(--border-color) !important;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .contact-info {
    grid-template-columns: 1fr;
  }
}
</style>