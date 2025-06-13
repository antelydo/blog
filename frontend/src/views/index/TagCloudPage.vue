<!-- 标签云页面 -->
<template>
  <div class="blog-container">
    <Header/>
    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <h1 class="page-title">
            <i class="fa fa-tags page-icon"></i>
            {{ $t('webTagCloud.title') }}
          </h1>
          <p class="page-description">
            {{ $t('webTagCloud.description') }}
          </p>
        </div>

        <div class="row">
          <div class="col-12">
            <!-- Loading State -->
            <div v-if="loading" class="loading-container">
              <div class="tag-cloud-skeleton">
                <div v-for="i in 12" :key="i" class="tag-skeleton-block">
                  <div class="tag-skeleton-bullet"></div>
                  <div class="tag-skeleton-content">
                    <div class="tag-skeleton-title"></div>
                    <div class="tag-skeleton-article"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-container">
              <div class="error-state">
                <h3 class="error-title">
                  <i class="fa fa-exclamation-triangle error-icon"></i>
                  {{ error }}
                </h3>
                <p class="error-message">{{ $t('webTagCloud.tryAgainLater') }}</p>
                <div class="error-actions">
                  <button class="retry-button" @click="loadTags">
                    <i class="fa fa-refresh"></i> {{ $t('webTagCloud.retry') }}
                  </button>
                  <router-link to="/" class="back-home-btn">
                    <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="tags.length === 0" class="empty-container">
              <div class="empty-state">
                <h3 class="empty-title">
                  <i class="fa fa-tags empty-icon"></i>
                  {{ $t('webTagCloud.noTags') }}
                </h3>
                <p class="empty-message">{{ $t('webTagCloud.noTagsMessage') }}</p>
                <div class="empty-actions">
                  <router-link to="/" class="back-home-btn">
                    <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Tag Cloud -->
            <div v-else class="tag-cloud-container">
              <h1 class="tag-cloud-title">{{ $t('webTagCloud.title') }}</h1>
              <div class="tag-cloud">
                <div
                  v-for="tag in tags"
                  :key="tag.id"
                  class="tag-block"
                >
                  <div class="tag-block-inner">
                    <div class="tag-header">
                      <router-link
                        :to="`/tag/${tag.id}`"
                        class="tag-cloud-item"
                      >
                        {{ tag.name }}
                      </router-link>
                      <span class="tag-count">×{{ tag.post_count }}</span>
                    </div>
                    <router-link
                      v-if="tag.post && tag.post.length > 0"
                      :to="`/post/${tag.post[0].id}`"
                      class="tag-article"
                    >
                      {{ tag.post[0].title }}
                    </router-link>
                    <span v-else class="tag-no-article">{{ $t('webTagCloud.noArticles') }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="!loading && !error && tags.length > 0 && totalPages > 1" class="pagination-container">
              <div v-if="currentPage < totalPages" class="load-more">
                <button @click="loadMoreTags" class="load-more-btn">{{ $t('webTagCloud.loadMore') }}</button>
              </div>

              <div v-else class="no-more-tags">
                {{ $t('webTagCloud.noMoreTags') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <Footer />
    <BackToTop />
  </div>
</template>

<script>
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import BackToTop from './components/BackToTop.vue';
import './assets/blog.css';
import { getTagList } from '@/api/tag';
import { useHead } from '@unhead/vue';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: 'TagCloudPage',
  components: {
    Header,
    Footer,
    BackToTop
  },
  setup() {
    const { t } = useI18n();
    const blogConfigStore = useBlogConfigStore();

    // State
    const tags = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const currentPage = ref(1);
    const totalPages = ref(1);
    const pageSize = ref(25);
    const total = ref(0);

    // 计算SEO相关的值
    const siteName = computed(() => blogConfigStore.config.site_name || '');
    const siteDescription = computed(() => blogConfigStore.config.site_description || '');

    // Methods
    const loadTags = async (isLoadMore = false) => {
      try {
        loading.value = true;
        error.value = null;

        // In a real implementation, we'd need a proper API endpoint for tag clouds
        // with pagination, but for now we'll use the standard tag list
        const response = await getTagList({'status':1,'is_get_post':1,'is_get_post_count':1,'page':currentPage.value,'limit':pageSize.value});

        if (response && response.code === 200) {
          const allTags = response.data.list || [];

          // Sort tags by post_count in descending order
          allTags.sort((a, b) => b.post_count - a.post_count);

          if (isLoadMore) {
            tags.value = [...tags.value, ...allTags];
          } else {
            tags.value = allTags;
          }

          // Use the total count from the API response instead of the array length
          total.value = response.data.total || 0;
          totalPages.value = Math.ceil(total.value / pageSize.value) || 1;
          console.log('Tags loaded:', allTags.length, 'Total:', total.value, 'Pages:', totalPages.value);
        } else {
          console.error('Tag API error:', response?.msg || 'Unknown error');
          error.value = response?.msg || t('webTagCloud.error');
        }
      } catch (err) {
        console.error('Tag load error:', err);
        error.value = t('webTagCloud.error');
      } finally {
        loading.value = false;
      }
    };

    const loadMoreTags = () => {
      currentPage.value++;
      loadTags(true);
    };

    const calculateTagSize = (count) => {
      // Find the max post count to scale sizes
      const maxCount = Math.max(...tags.value.map(tag => tag.post_count));

      // Min and max font sizes
      const minSize = 14;
      const maxSize = 28;

      // Calculate size based on count relative to max
      let size;
      if (maxCount <= 1) {
        size = minSize;
      } else {
        // Scale between min and max size based on post count
        size = minSize + ((count / maxCount) * (maxSize - minSize));
      }

      return `${size}px`;
    };

    // Load tags when component is mounted
    onMounted(() => {
      loadTags();
    });

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `${t('webTagCloud.title')} - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => t('webTagCloud.metaDescription', { siteName: siteName.value }))
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `${t('webTagCloud.title')} - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => t('webTagCloud.metaDescription', { siteName: siteName.value }))
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
          content: computed(() => `${t('webTagCloud.title')} - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => t('webTagCloud.metaDescription', { siteName: siteName.value }))
        }
      ],
      link: [
        {
          rel: 'canonical',
          href: computed(() => `${window.location.origin}/tag_cloud`)
        }
      ]
    });

    return {
      tags,
      loading,
      error,
      currentPage,
      totalPages,
      total,
      loadTags,
      loadMoreTags,
      calculateTagSize
    };
  }
};
</script>

<style scoped>
.page-header {
  margin-bottom: 35px;
  padding: 25px 30px;
  border-radius: 8px;
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.page-icon {
  margin-right: 10px;
  color: var(--primary-color, #09c);
}

.page-title {
  font-size: 28px;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 700;
}

.page-description {
  font-size: 16px;
  color: #555;
  margin-bottom: 20px;
}

.tag-cloud-title {
  font-size: 28px;
  color: var(--heading-color, #333);
  padding-bottom: 15px;
  margin-bottom: 20px;
  position: relative;
  font-weight: 700;
  text-align: left;
  border-bottom: 1px solid var(--border-color, #f0f0f0);
  transition: color 0.3s ease, border-color 0.3s ease;
}

.tag-cloud-container {
  padding: 25px;
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.tag-cloud {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  margin: -10px;
}

.tag-block {
  width: 25%;
  padding: 10px;
  box-sizing: border-box;
}

.tag-block-inner {
  border: 1px solid var(--border-color, #eee);
  border-radius: 8px;
  padding: 15px;
  height: 100%;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  position: relative;
  background-color: var(--card-bg-color, #fff);
}

.tag-block-inner:hover {
  border-color: var(--border-color-hover, #ddd);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.tag-header {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 5px;
}

.tag-cloud-item {
  display: inline-block;
  background-color: var(--bg-color-light, #f2f6f9);
  color: var(--link-color, #337ab7);
  padding: 3px 10px;
  border-radius: 15px;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.tag-cloud-item:hover {
  background-color: var(--bg-color-hover, #e1ecf4);
  color: var(--link-color-hover, #2973af);
  text-decoration: none;
}

.tag-count {
  font-size: 0.9em;
  color: var(--light-text-color, #888);
  display: inline-block;
  transition: color 0.3s ease;
}

.tag-article {
  font-size: 13px;
  color: var(--text-color, #777);
  text-decoration: none;
  line-height: 1.5;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  max-height: 40px;
  text-align: left;
  transition: color 0.3s ease;
}

.tag-article:hover {
  color: var(--primary-color, #09c);
}

.tag-no-article {
  font-size: 13px;
  color: #aaa;
  font-style: italic;
  display: block;
}

.loading-container, .error-container, .empty-container {
  background-color: #fff;
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  margin-bottom: 30px;
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.error-state, .empty-state {
  max-width: 500px;
  margin: 0 auto;
}

.error-icon, .empty-icon {
  color: #f39c12;
  font-size: 1.2em;
  margin-right: 8px;
}

.error-title, .empty-title {
  font-size: 22px;
  color: #333;
  margin-bottom: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}

.error-message, .empty-message {
  font-size: 16px;
  color: #666;
  margin-bottom: 20px;
  line-height: 1.6;
}

.error-actions, .empty-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 20px;
}

.retry-button, .back-home-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  text-decoration: none;
}

.retry-button {
  background-color: #f8f9fa;
  color: #333;
  border: none;
}

.retry-button:hover {
  background-color: #e9ecef;
}

.back-home-btn {
  background-color: var(--primary-color, #09c);
  color: white;
  border: none;
}

.back-home-btn:hover {
  background-color: var(--primary-color-hover, #007ea6);
}

.retry-button i, .back-home-btn i {
  margin-right: 5px;
}

.pagination-container {
  margin-top: 30px;
  margin-bottom: 40px;
  text-align: center;
}

.load-more {
  margin-bottom: 20px;
}

.load-more-btn {
  padding: 10px 25px;
  background-color: var(--primary-color, #09c);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s ease;
}

.load-more-btn:hover {
  background-color: var(--primary-color-hover, #007ea6);
}

.no-more-tags {
  color: #999;
  font-size: 14px;
}

.loading-container {
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  padding: 25px;
  margin-bottom: 30px;
  min-height: 300px;
  display: block;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: background-color 0.3s ease;
}

.tag-cloud-skeleton {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 10px;
}

.tag-skeleton-block {
  width: calc(33.333% - 15px);
  margin-bottom: 15px;
  display: flex;
  align-items: flex-start;
}

.tag-skeleton-bullet {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #eee;
  margin-right: 10px;
  margin-top: 8px;
  flex-shrink: 0;
}

.tag-skeleton-content {
  flex-grow: 1;
}

.tag-skeleton-title {
  height: 16px;
  background-color: #eee;
  border-radius: 3px;
  width: 80%;
  margin-bottom: 8px;
  animation: pulse 1.5s infinite;
}

.tag-skeleton-article {
  height: 12px;
  background-color: #f5f5f5;
  border-radius: 3px;
  width: 90%;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}

@media (max-width: 1200px) {
  .tag-block {
    width: 33.333%;
  }
}

@media (max-width: 992px) {
  .tag-block {
    width: 50%;
  }

  .tag-skeleton-block {
    width: calc(50% - 15px);
  }
}

@media (max-width: 576px) {
  .tag-block {
    width: 100%;
  }

  .tag-skeleton-block {
    width: 100%;
  }

  .tag-cloud-title {
    font-size: 24px;
    padding-bottom: 10px;
    margin-bottom: 15px;
  }
}
</style>