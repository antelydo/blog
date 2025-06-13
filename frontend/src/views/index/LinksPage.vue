<!-- 友链页 -->
<template>
  <MainLayout>
    <div class="container">
        <div class="page-header">
          <h1 class="page-title">{{ t('webLink.linkManagement.title') }}</h1>
          <p class="page-description">{{ t('webLink.linkManagement.description') }}</p>
        </div>

        <!-- 搜索和筛选 -->
        <div class="filter-container">
          <div class="search-box">
            <input
              type="text"
              v-model="searchQuery"
              :placeholder="t('webLink.linkManagement.search.titlePlaceholder')"
              class="search-input"
              @input="filterLinks"
            >
            <button class="search-button" @click="filterLinks">
              <i class="fa fa-search"></i>
            </button>
          </div>

          <div class="category-tabs">
            <button
              class="tab-button"
              :class="{ active: activeCategory === 'all' }"
              @click="setCategory('all')"
            >
              {{ t('webLink.linkManagement.allLinks') }}
            </button>
            <button
              class="tab-button"
              :class="{ active: activeCategory === 'tech' }"
              @click="setCategory('tech')"
            >
              技术博客
            </button>
            <button
              class="tab-button"
              :class="{ active: activeCategory === 'friendly' }"
              @click="setCategory('friendly')"
            >
              友情站点
            </button>
            <button
              class="tab-button"
              :class="{ active: activeCategory === 'partner' }"
              @click="setCategory('partner')"
            >
              合作伙伴
            </button>
          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <!-- 加载状态 -->
            <div v-if="loading" class="loading-container">
              <div class="loading-spinner"></div>
              <p>{{ t('common.loading') }}</p>
            </div>

            <div v-else class="card main-card">
              <div class="widget-content p-20">
                <!-- 技术博客部分 -->
                <transition name="fade" mode="out-in">
                  <div v-if="showSection('tech')" key="tech">
                    <h2 class="section-title">技术博客</h2>
                    <div v-if="filteredTechLinks.length > 0" class="links-grid">
                      <a v-for="(link, index) in filteredTechLinks" :key="'tech-'+index"
                         :href="link.url" target="_blank" rel="noopener noreferrer"
                         class="link-card"
                         :title="(link.title || link.name) + ' - ' + (link.description || '')">
                        <div class="link-logo" v-if="link.logo">
                          <img :src="link.logo" :alt="link.title || link.name">
                        </div>
                        <div v-else class="link-logo link-logo-placeholder">
                          {{ getLogo(link.title || link.name) }}
                        </div>
                        <div class="link-info">
                          <h3 class="link-title">{{ link.title || link.name }}</h3>
                          <p class="link-desc">{{ link.description }}</p>
                        </div>
                        <div class="link-arrow">
                          <i class="fa fa-external-link"></i>
                        </div>
                      </a>
                    </div>
                    <div v-else class="no-results">
                      <p>{{ t('webLink.linkManagement.noData') }}</p>
                    </div>
                  </div>
                </transition>

                <!-- 友情站点部分 -->
                <transition name="fade" mode="out-in">
                  <div v-if="showSection('friendly')" key="friendly">
                    <h2 class="section-title" :class="{'mt-20': showSection('tech')}">友情站点</h2>
                    <div v-if="filteredFriendlyLinks.length > 0" class="links-grid">
                      <a v-for="(link, index) in filteredFriendlyLinks" :key="'friendly-'+index"
                         :href="link.url" target="_blank" rel="noopener noreferrer"
                         class="link-card"
                         :title="(link.title || link.name) + ' - ' + (link.description || '')">
                        <div class="link-logo" v-if="link.logo">
                          <img :src="link.logo" :alt="link.title || link.name">
                        </div>
                        <div v-else class="link-logo link-logo-placeholder">
                          {{ getLogo(link.title || link.name) }}
                        </div>
                        <div class="link-info">
                          <h3 class="link-title">{{ link.title || link.name }}</h3>
                          <p class="link-desc">{{ link.description }}</p>
                        </div>
                        <div class="link-arrow">
                          <i class="fa fa-external-link"></i>
                        </div>
                      </a>
                    </div>
                    <div v-else class="no-results">
                      <p>{{ t('webLink.linkManagement.noData') }}</p>
                    </div>
                  </div>
                </transition>

                <!-- 合作伙伴部分 -->
                <transition name="fade" mode="out-in">
                  <div v-if="showSection('partner')" key="partner">
                    <h2 class="section-title" :class="{'mt-20': showSection('tech') || showSection('friendly')}">合作伙伴</h2>
                    <div v-if="filteredPartnerLinks.length > 0" class="links-grid">
                      <a v-for="(link, index) in filteredPartnerLinks" :key="'partner-'+index"
                         :href="link.url" target="_blank" rel="noopener noreferrer"
                         class="link-card"
                         :title="(link.title || link.name) + ' - ' + (link.description || '')">
                        <div class="link-logo" v-if="link.logo">
                          <img :src="link.logo" :alt="link.title || link.name">
                        </div>
                        <div v-else class="link-logo link-logo-placeholder">
                          {{ getLogo(link.title || link.name) }}
                        </div>
                        <div class="link-info">
                          <h3 class="link-title">{{ link.title || link.name }}</h3>
                          <p class="link-desc">{{ link.description }}</p>
                        </div>
                        <div class="link-arrow">
                          <i class="fa fa-external-link"></i>
                        </div>
                      </a>
                    </div>
                    <div v-else class="no-results">
                      <p>{{ t('webLink.linkManagement.noData') }}</p>
                    </div>
                  </div>
                </transition>

                <!-- 搜索或筛选结果为空时 -->
                <div v-if="isFiltering && noResults" class="no-results-global">
                  <div class="no-results-icon">
                    <i class="fa fa-search"></i>
                  </div>
                  <h3>{{ t('webLink.linkManagement.noData') }}</h3>
                  <p>请尝试使用其他关键词或清除搜索条件</p>
                  <button class="reset-button" @click="resetFilters">清除搜索</button>
                </div>

                <!-- 友链申请表单 -->
                <transition name="fade">
                  <div class="apply-section mt-20">
                    <div class="apply-header">
                      <h3>{{ t('webLink.linkManagement.applyLink') }}</h3>
                      <button class="toggle-form-button" @click="showApplyForm = !showApplyForm">
                        {{ showApplyForm ? '收起表单' : '展开表单' }}
                      </button>
                    </div>

                    <p>如果您希望与我们互换友情链接，请满足以下条件：</p>
                    <ul class="apply-rules">
                      <li>网站内容健康，无违法内容</li>
                      <li>网站已稳定运行3个月以上</li>
                      <li>网站有一定的更新频率和质量保证</li>
                      <li>相关度较高的博客或网站</li>
                    </ul>

                    <transition name="slide">
                      <div v-if="showApplyForm" class="apply-form-container">
                        <div class="apply-form">
                          <div class="form-group">
                            <label for="site-name">{{ t('webLink.linkManagement.form.title') }} <span class="required">*</span></label>
                            <input
                              type="text"
                              id="site-name"
                              v-model="applyForm.name"
                              :placeholder="t('webLink.linkManagement.form.titlePlaceholder')"
                              maxlength="50"
                              :class="{'error-input': formErrors.name && applyForm.name.length > 0}"
                            >
                            <div class="error-msg" v-if="formErrors.name && applyForm.name.length > 0">
                              {{ formErrors.name }}
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="site-url">{{ t('webLink.linkManagement.form.url') }} <span class="required">*</span></label>
                            <input
                              type="url"
                              id="site-url"
                              v-model="applyForm.url"
                              :placeholder="t('webLink.linkManagement.form.urlPlaceholder')"
                              :class="{'error-input': formErrors.url && applyForm.url.length > 0}"
                            >
                            <div class="error-msg" v-if="formErrors.url && applyForm.url.length > 0">
                              {{ formErrors.url }}
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="site-desc">{{ t('webLink.linkManagement.form.description') }} <span class="required">*</span></label>
                            <textarea
                              id="site-desc"
                              v-model="applyForm.description"
                              :placeholder="t('webLink.linkManagement.form.descriptionPlaceholder')"
                              maxlength="100"
                              :class="{'error-input': formErrors.description && applyForm.description.length > 0}"
                            ></textarea>
                            <div class="char-count">{{ applyForm.description.length }}/100</div>
                            <div class="error-msg" v-if="formErrors.description && applyForm.description.length > 0">
                              {{ formErrors.description }}
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="site-logo">{{ t('webLink.linkManagement.form.logo') }}</label>
                            <input
                              type="url"
                              id="site-logo"
                              v-model="applyForm.logo"
                              :placeholder="t('webLink.linkManagement.form.logoPlaceholder')"
                            >
                            <div class="logo-preview" v-if="applyForm.logo">
                              <img :src="applyForm.logo" alt="Logo预览" @error="handleLogoError">
                              <div class="preview-label">{{ t('webLink.linkManagement.form.logoTip') }}</div>
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="contact-email">{{ t('webLink.linkManagement.form.email') }} <span class="required">*</span></label>
                            <input
                              type="email"
                              id="contact-email"
                              v-model="applyForm.email"
                              :placeholder="t('webLink.linkManagement.form.emailPlaceholder')"
                              :class="{'error-input': formErrors.email && applyForm.email.length > 0}"
                            >
                            <div class="error-msg" v-if="formErrors.email && applyForm.email.length > 0">
                              {{ formErrors.email }}
                            </div>
                          </div>
                          <div class="form-actions">
                            <button
                              class="submit-button"
                              :disabled="!formValid || submitting"
                              @click="validateAndSubmit"
                            >
                              {{ submitting ? t('common.submitting') : t('webLink.linkManagement.submit') }}
                            </button>
                            <button class="reset-form-button" @click="resetApplyForm">{{ t('common.reset') }}</button>
                          </div>
                        </div>
                      </div>
                    </transition>

                    <p v-if="!showApplyForm" class="mt-15">{{ t('common.or') }}</p>
                    <p v-if="!showApplyForm">{{ t('webLink.footer.contactUs') }}：<a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a></p>
                  </div>
                </transition>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <Sidebar />
          </div>
        </div>
      </div>
  </MainLayout>
</template>

<script>
import { ElMessage, ElMessageBox } from 'element-plus';
import { ref, computed, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import MainLayout from './components/MainLayout.vue';
import Sidebar from './components/Sidebar.vue';
import { getLinkList, getLinkTypeList, applyLink, uploadLinkLogo } from '@/api/link';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { useHead } from '@vueuse/head';
import './assets/blog.css';
// 已移除暗黑主题相关样式导入
// import './assets/dark-theme.css';
// import './assets/global-dark-theme.css';
// import './assets/dark-theme-typography.css';

export default {
  name: 'LinksPage',
  components: {
    MainLayout,
    Sidebar
  },
  setup() {
    const {t} = useI18n();
    const blogConfigStore = useBlogConfigStore();
    const contactEmail = computed(() => blogConfigStore.contactEmail);
    const siteName = computed(() => blogConfigStore.config.site_name || '');

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `友情链接 - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => `${siteName.value}的友情链接页面，展示我们推荐的优质网站和合作伙伴。`)
        },
        {
          name: 'keywords',
          content: '友情链接,合作伙伴,推荐网站,网站导航'
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `友情链接 - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => `${siteName.value}的友情链接页面，展示我们推荐的优质网站和合作伙伴。`)
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
          content: computed(() => `友情链接 - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => `${siteName.value}的友情链接页面，展示我们推荐的优质网站和合作伙伴。`)
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

    // 搜索和筛选状态
    const searchQuery = ref('');
    const activeCategory = ref('all');
    const isFiltering = ref(false);
    const loading = ref(false);

    // 申请表单
    const showApplyForm = ref(false);
    const submitting = ref(false);
    const applyForm = reactive({
      name: '',
      url: '',
      description: '',
      logo: '',
      email: ''
    });

    // 表单错误信息
    const formErrors = reactive({
      name: '',
      url: '',
      description: '',
      email: ''
    });

    // 链接数据
    const techLinks = ref([]);
    const friendlyLinks = ref([]);
    const partnerLinks = ref([]);
    const linkTypes = ref({});

    // 加载链接类型
    const fetchLinkTypes = async () => {
      try {
        const response = await getLinkTypeList();
        if (response && response.code === 200 && response.data) {
          // 适配不同返回格式
          if (Array.isArray(response.data)) {
            response.data.forEach(item => {
              linkTypes.value[item.id] = item.name;
            });
          } else if (response.data.list) {
            response.data.list.forEach(item => {
              linkTypes.value[item.id] = item.name;
            });
          } else if (typeof response.data === 'object' && !Array.isArray(response.data)) {
            // 处理对象格式 {1: "友情链接", 2: "技术博客"}
            linkTypes.value = response.data;
          }
        }
      } catch (error) {
        console.error('获取链接类型失败:', error);
      }
    };

    // 加载所有链接
    const fetchLinks = async () => {
      loading.value = true;
      try {
        const response = await getLinkList({
          status: 1, // 只获取已启用的链接
          page: 1,
          limit: 1000
        });

        if (response && (response.code === 200) && response.data) {
          let linkData = [];

          if (Array.isArray(response.data)) {
            linkData = response.data;
          } else if (response.data.list) {
            linkData = response.data.list;
          } else if (response.data.records) {
            linkData = response.data.records;
          }

          // 根据类型分组链接
          techLinks.value = linkData.filter(link => link.type === 2); // 假设2是技术博客
          friendlyLinks.value = linkData.filter(link => link.type === 1); // 假设1是友情链接
          partnerLinks.value = linkData.filter(link => link.type === 3); // 假设3是合作伙伴
        } else {
          console.warn('获取友情链接失败:', response);
          techLinks.value = [];
          friendlyLinks.value = [];
          partnerLinks.value = [];
        }
      } catch (error) {
        console.error('获取友情链接错误:', error);
        techLinks.value = [];
        friendlyLinks.value = [];
        partnerLinks.value = [];
      } finally {
        loading.value = false;
      }
    };

    // 筛选后的链接
    const filteredTechLinks = computed(() => {
      if (!searchQuery.value) return techLinks.value;
      return techLinks.value.filter(link =>
        link.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    const filteredFriendlyLinks = computed(() => {
      if (!searchQuery.value) return friendlyLinks.value;
      return friendlyLinks.value.filter(link =>
        link.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    const filteredPartnerLinks = computed(() => {
      if (!searchQuery.value) return partnerLinks.value;
      return partnerLinks.value.filter(link =>
        link.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        link.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // 判断是否显示某个分类
    const showSection = (section) => {
      if (activeCategory.value === 'all') return true;
      return activeCategory.value === section;
    };

    // 设置当前分类
    const setCategory = (category) => {
      activeCategory.value = category;
    };

    // 筛选链接
    const filterLinks = () => {
      isFiltering.value = !!searchQuery.value;
    };

    // 重置筛选
    const resetFilters = () => {
      searchQuery.value = '';
      activeCategory.value = 'all';
      isFiltering.value = false;
    };

    // 没有搜索结果
    const noResults = computed(() => {
      return filteredTechLinks.value.length === 0 &&
             filteredFriendlyLinks.value.length === 0 &&
             filteredPartnerLinks.value.length === 0;
    });

    // 验证表单规则
    const validateForm = () => {
      let isValid = true;

      // 重置错误信息
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = '';
      });

      // 验证网站名称
      if (!applyForm.name.trim()) {
        formErrors.name = t('webLink.linkManagement.validation.titleRequired');
        isValid = false;
      } else if (applyForm.name.trim().length < 2) {
        formErrors.name = t('webLink.linkManagement.validation.titleLength');
        isValid = false;
      }

      // 验证网站地址
      if (!applyForm.url.trim()) {
        formErrors.url = t('webLink.linkManagement.validation.urlRequired');
        isValid = false;
      } else if (!/^https?:\/\/.+/.test(applyForm.url.trim())) {
        formErrors.url = t('webLink.linkManagement.validation.urlFormat');
        isValid = false;
      }

      // 验证网站简介
      if (!applyForm.description.trim()) {
        formErrors.description = t('webLink.linkManagement.validation.titleRequired');
        isValid = false;
      } else if (applyForm.description.trim().length < 10) {
        formErrors.description = '网站简介至少需要10个字符';
        isValid = false;
      }

      // 验证联系邮箱
      if (!applyForm.email.trim()) {
        formErrors.email = t('webLink.linkManagement.validation.emailRequired');
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(applyForm.email.trim())) {
        formErrors.email = t('webLink.linkManagement.validation.emailFormat');
        isValid = false;
      }

      return isValid;
    };

    // 处理Logo加载错误
    const handleLogoError = (e) => {
      e.target.src = ''; // 清空图片
      e.target.classList.add('logo-error');
      e.target.parentNode.classList.add('error-preview');

      // 添加错误图标
      const errorIcon = document.createElement('div');
      errorIcon.className = 'logo-error-icon';
      errorIcon.innerHTML = '<i class="fa fa-exclamation-circle"></i>';
      e.target.parentNode.appendChild(errorIcon);

      // 显示错误提示
      const errorMsg = document.createElement('div');
      errorMsg.className = 'logo-error-msg';
      errorMsg.textContent = t('webLink.linkManagement.validation.imageTypeError');
      e.target.parentNode.appendChild(errorMsg);
    };

    // 验证并提交表单
    const validateAndSubmit = () => {
      if (validateForm()) {
        submitApplyForm();
      }
    };

    // 表单验证
    const formValid = computed(() => {
      return applyForm.name.trim() &&
             applyForm.url.trim() &&
             applyForm.description.trim() &&
             applyForm.email.trim();
    });

    // 重置表单
    const resetApplyForm = () => {
      applyForm.name = '';
      applyForm.url = '';
      applyForm.description = '';
      applyForm.logo = '';
      applyForm.email = '';

      // 重置错误信息
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = '';
      });
    };

    // 获取网站名称的首字母作为占位图
    const getLogo = (name) => {
      if (!name) return '';
      return name.substring(0, 1).toUpperCase();
    };

    // 提交表单
    const submitApplyForm = async () => {
      if (!formValid.value) return;

      submitting.value = true;

      try {
        // 准备提交数据
        const formData = {
          title: applyForm.name.trim(),
          url: applyForm.url.trim(),
          description: applyForm.description.trim(),
          logo: applyForm.logo.trim(),
          email: applyForm.email.trim(),
          type: 1, // 默认为友情链接类型
          status: 0 // 默认为待审核状态
        };

        const response = await applyLink(formData);

        if (response && (response.code === 200)) {
          // 成功提示
          ElMessage.success(t('webLink.linkManagement.applySuccess'));
          resetApplyForm();
          showApplyForm.value = false;
        } else {
          // 失败提示
          const errorMsg = response?.msg || t('webLink.linkManagement.applyFailed');
          ElMessage.error(errorMsg);
        }
      } catch (error) {
        console.error('申请友情链接失败:', error);
        ElMessage.error(t('webLink.linkManagement.applyFailed'));
      } finally {
        submitting.value = false;
      }
    };

    // 初始化
    onMounted(() => {
      fetchLinkTypes();
      fetchLinks();
      if (!blogConfigStore.initialized) {
        blogConfigStore.fetchConfig();
      }
    });

    return {
      // 数据
      techLinks,
      friendlyLinks,
      partnerLinks,
      loading,
      contactEmail,
      // 计算属性
      filteredTechLinks,
      filteredFriendlyLinks,
      filteredPartnerLinks,
      noResults,
      formValid,
      // 状态
      searchQuery,
      activeCategory,
      isFiltering,
      showApplyForm,
      submitting,
      applyForm,
      formErrors,
      // 方法
      showSection,
      setCategory,
      filterLinks,
      resetFilters,
      validateAndSubmit,
      resetApplyForm,
      getLogo,
      // i18n
      t,
      handleLogoError,
      siteName
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

/* 加载状态样式 */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  background-color: var(--card-bg-color);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  min-height: 400px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: var(--primary-color);
  animation: spin 1s ease-in-out infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-container p {
  color: var(--light-text-color);
  font-size: 16px;
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

/* 过滤和搜索 */
.filter-container {
  margin-bottom: 20px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background-color: var(--card-bg-color);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
}

.search-box {
  display: flex;
  flex: 1;
  min-width: 200px;
  position: relative;
}

.search-input {
  width: 100%;
  padding: 10px 40px 10px 15px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 14px;
  transition: border-color var(--transition-speed);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.search-button {
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
  width: 40px;
  background: none;
  border: none;
  color: var(--light-text-color);
  cursor: pointer;
  transition: color var(--transition-speed);
}

.search-button:hover {
  color: var(--primary-color);
}

.category-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tab-button {
  padding: 8px 15px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background-color: var(--bg-color);
  color: var(--text-color);
  cursor: pointer;
  transition: all var(--transition-speed);
  font-size: 14px;
}

.tab-button:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.tab-button.active {
  background-color: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

/* 主卡片 */
.main-card {
  min-height: 400px;
}

.links-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 30px;
}

.link-card {
  display: flex;
  align-items: center;
  padding: 15px;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  transition: all var(--transition-speed);
  background-color: var(--card-bg-color, #fff);
  text-decoration: none;
  color: var(--text-color);
  position: relative;
  overflow: hidden;
}

.link-card::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background-color: var(--primary-color);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

.link-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  border-color: var(--primary-color);
}

.link-card:hover::before {
  transform: scaleX(1);
}

.link-logo {
  width: 50px;
  height: 50px;
  min-width: 50px;
  margin-right: 15px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f9f9f9;
  border: 1px solid var(--border-color);
}

.link-logo img {
  max-width: 40px;
  max-height: 40px;
  object-fit: contain;
}

.link-logo-placeholder {
  background-color: var(--primary-color);
  color: white;
  font-size: 20px;
  font-weight: bold;
  border: none;
}

.link-info {
  flex: 1;
}

.link-title {
  font-size: 16px;
  font-weight: bold;
  margin: 0 0 5px;
  color: var(--heading-color);
  transition: color var(--transition-speed);
}

.link-card:hover .link-title {
  color: var(--primary-color);
}

.link-desc {
  font-size: 13px;
  color: var(--light-text-color);
  margin: 0;
  line-height: 1.4;
}

.link-arrow {
  opacity: 0;
  margin-left: 10px;
  color: var(--primary-color);
  transition: opacity var(--transition-speed);
}

.link-card:hover .link-arrow {
  opacity: 1;
}

/* 无结果提示 */
.no-results {
  text-align: center;
  padding: 20px;
  color: var(--light-text-color);
  font-style: italic;
}

.no-results-global {
  text-align: center;
  padding: 40px 20px;
  color: var(--light-text-color);
}

.no-results-icon {
  font-size: 40px;
  color: var(--border-color);
  margin-bottom: 15px;
}

.reset-button {
  margin-top: 15px;
  padding: 8px 16px;
  border: 1px solid var(--primary-color);
  background-color: transparent;
  color: var(--primary-color);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all var(--transition-speed);
}

.reset-button:hover {
  background-color: var(--primary-color);
  color: white;
}

/* 友链申请 */
.apply-section {
  padding: 20px;
  background-color: var(--bg-color-light, #f9f9f9);
  border-radius: var(--border-radius);
  border-left: 3px solid var(--primary-color);
  text-align: left;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.apply-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  text-align: left;
}

.apply-header h3 {
  margin: 0;
  color: var(--primary-color);
}

.toggle-form-button {
  padding: 5px 10px;
  background-color: transparent;
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 13px;
  transition: all var(--transition-speed);
}

.toggle-form-button:hover {
  background-color: var(--primary-color);
  color: white;
}

.apply-rules {
  list-style: disc;
  padding-left: 20px;
  margin: 15px 0;
}

.apply-rules li {
  margin-bottom: 8px;
}

.apply-form-container {
  margin-top: 20px;
  text-align: left;
}

.apply-form {
  background-color: var(--card-bg-color, white);
  padding: 20px;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow, 0 2px 6px rgba(0, 0, 0, 0.05));
  text-align: left;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.form-group {
  margin-bottom: 15px;
  text-align: left;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: var(--heading-color);
  text-align: left;
}

.required {
  color: #f56c6c;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 14px;
  transition: border-color var(--transition-speed);
}

.form-group textarea {
  height: 100px;
  resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
}

/* 新增样式 */
.error-input {
  border-color: #f56c6c !important;
  background-color: #fef0f0;
}

.error-msg {
  color: #f56c6c;
  font-size: 12px;
  margin-top: 5px;
}

.char-count {
  text-align: right;
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.logo-preview {
  margin-top: 10px;
  padding: 10px;
  border: 1px solid #eee;
  border-radius: var(--border-radius);
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.logo-preview img {
  max-width: 80px;
  max-height: 80px;
  object-fit: contain;
}

.preview-label {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.error-preview {
  border-color: #f56c6c;
  background-color: #fef0f0;
}

.logo-error {
  display: none;
}

.logo-error-icon {
  color: #f56c6c;
  font-size: 24px;
  margin-bottom: 5px;
}

.logo-error-msg {
  color: #f56c6c;
  font-size: 12px;
  text-align: center;
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.submit-button {
  padding: 10px 20px;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: background-color var(--transition-speed);
}

.submit-button:hover:not(:disabled) {
  background-color: var(--primary-hover);
}

.submit-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.reset-form-button {
  padding: 10px 20px;
  background-color: transparent;
  border: 1px solid #ccc;
  color: var(--text-color);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all var(--transition-speed);
}

.reset-form-button:hover {
  border-color: #999;
  color: #666;
}

/* 动画效果 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease-out;
  max-height: 800px;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}

/* 响应式设计 */
@media (max-width: 992px) {
  .filter-container {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    width: 100%;
  }

  .category-tabs {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .links-grid {
    grid-template-columns: 1fr;
  }

  .page-title {
    font-size: 24px;
  }

  .page-description {
    font-size: 14px;
  }

  .page-header {
    padding: 30px 0 20px;
  }

  .form-actions {
    flex-direction: column;
  }

  .tab-button {
    flex: 1;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .page-header {
    padding: 20px 0 15px;
  }

  .page-title {
    font-size: 22px;
  }

  .category-tabs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
  }
}

/* 暗黑模式和跟随系统模式的样式 */
html.dark-theme .apply-section,
html.dark .apply-section,
html.system-dark-theme .apply-section,
html.dark.system-theme .apply-section {
  background-color: var(--bg-color-light);
  color: var(--text-color);
}

html.dark-theme .apply-form,
html.dark .apply-form,
html.system-dark-theme .apply-form,
html.dark.system-theme .apply-form {
  background-color: var(--card-bg-color);
  color: var(--text-color);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

html.dark-theme .apply-section p,
html.dark .apply-section p,
html.system-dark-theme .apply-section p,
html.dark.system-theme .apply-section p,
html.dark-theme .apply-rules li,
html.dark .apply-rules li,
html.system-dark-theme .apply-rules li,
html.dark.system-theme .apply-rules li {
  color: var(--text-color);
}

html.dark-theme .form-group input,
html.dark .form-group input,
html.system-dark-theme .form-group input,
html.dark.system-theme .form-group input,
html.dark-theme .form-group textarea,
html.dark .form-group textarea,
html.system-dark-theme .form-group textarea,
html.dark.system-theme .form-group textarea {
  background-color: rgba(255, 255, 255, 0.05);
  color: var(--text-color);
  border-color: var(--border-color);
}

html.dark-theme .logo-preview,
html.dark .logo-preview,
html.system-dark-theme .logo-preview,
html.dark.system-theme .logo-preview {
  background-color: var(--card-bg-color);
  border-color: var(--border-color);
}

html.dark-theme .preview-label,
html.dark .preview-label,
html.system-dark-theme .preview-label,
html.dark.system-theme .preview-label,
html.dark-theme .char-count,
html.dark .char-count,
html.system-dark-theme .char-count,
html.dark.system-theme .char-count {
  color: var(--text-color-secondary, #a0a0a0);
}
</style>