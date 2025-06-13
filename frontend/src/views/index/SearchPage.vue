<!-- 搜索页 -->
<template>
  <MainLayout>
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">
          <i class="fa fa-search search-icon"></i>
          {{ $t('search.searchResults') }}
        </h1>
        <p class="page-description">
          {{ $t('search.searchingFor', { query: searchQuery }) }}
        </p>

        <!-- 搜索框 -->
        <div class="search-container">
          <form @submit.prevent="handleSearch" class="search-form">
            <input
              type="text"
              v-model="searchQuery"
              :placeholder="$t('search.searchPlaceholder')"
              class="search-input"
              autofocus
            >
            <button type="submit" class="search-button">
              <i class="fa fa-search"></i>
            </button>
          </form>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <!-- Loading State -->
          <div v-if="loading" class="loading-container">
            <el-skeleton :rows="5" animated />
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="error-container">
            <div class="error-state">
              <h3 class="error-title">
                <i class="fa fa-exclamation-triangle error-icon"></i>
                {{ error }}
              </h3>
              <p class="error-message">{{ $t('search.tryAgainLater') }}</p>
              <div class="error-actions">
                <button class="retry-button" @click="handleSearch">
                  <i class="fa fa-refresh"></i> {{ $t('search.retry') }}
                </button>
                <router-link to="/" class="back-home-btn">
                  <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                </router-link>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="searchResults && searchResults.length === 0" class="empty-container">
            <div class="empty-state">
              <h3 class="empty-title">
                <i class="fa fa-search empty-icon"></i>
                {{ $t('search.noResults') }}
              </h3>
              <p class="empty-message">{{ $t('search.noResultsMessage', { query: searchQuery }) }}</p>

              <!-- Add highlight test here -->
              <div class="search-highlight-demo">
                <p>{{ $t('search.testingHighlight') }}:</p>
                <div class="highlight-examples">
                  <p><b>{{ $t('search.typePost') }}:</b> <span v-html="highlightSearch('This is an article about ' + searchQuery + ' technology.')"></span></p>
                  <p><b>{{ $t('search.typeCategory') }}:</b> <span v-html="highlightSearch('Category: ' + searchQuery)"></span></p>
                  <p><b>{{ $t('search.typeTag') }}:</b> <span v-html="highlightSearch('Tag: ' + searchQuery)"></span></p>
                </div>
              </div>

              <div class="empty-actions">
                <button class="search-tips-btn" @click="showSearchTips = !showSearchTips">
                  <i class="fa fa-lightbulb-o"></i> {{ $t('search.searchTips') }}
                </button>
                <router-link to="/" class="back-home-btn">
                  <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                </router-link>
              </div>

              <!-- 搜索技巧 -->
              <div v-if="showSearchTips" class="search-tips">
                <h4 class="tips-title">
                  <i class="fa fa-lightbulb-o"></i>
                  {{ $t('search.tipsTitle') }}
                </h4>
                <ul class="tips-list">
                  <li class="tip-item">
                    <i class="fa fa-check-circle tip-icon"></i>
                    <span>{{ $t('search.tip1') }}</span>
                  </li>
                  <li class="tip-item">
                    <i class="fa fa-search tip-icon"></i>
                    <span>{{ $t('search.tip2') }}</span>
                  </li>
                  <li class="tip-item">
                    <i class="fa fa-refresh tip-icon"></i>
                    <span>{{ $t('search.tip3') }}</span>
                  </li>
                  <li class="tip-item">
                    <i class="fa fa-minus-circle tip-icon"></i>
                    <span>{{ $t('search.tip4') }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Results List -->
          <div v-else-if="searchResults && searchResults.length > 0" class="search-results">
            <div class="results-summary">
              {{ $t('search.resultsFound', { count: total }) }}
            </div>

            <article v-for="result in searchResults" :key="result.id" class="result-item">
              <div class="result-content-wrap">
                <div class="result-thumb" v-if="result.thumbnail">
                  <router-link :to="getResultUrl(result)">
                    <img :src="result.thumbnail || defaultThumbnail" :alt="result.title" class="result-thumbnail">
                  </router-link>
                </div>
                <div class="result-content-inner">
                  <h2 class="result-title">
                    <router-link :to="getResultUrl(result)">
                      <span v-if="result.is_top" class="result-badge sticky-badge">{{ $t('posts.sticky') }}</span>
                      <span v-if="result.is_recommend" class="result-badge recommend-badge">{{ $t('posts.recommended') }}</span>
                      <span v-html="highlightSearch(result.title)"></span>
                    </router-link>
                  </h2>
                  <div class="result-excerpt">
                    <p v-html="highlightSearch(result.description || result.excerpt)"></p>
                  </div>
                  <div class="result-meta">
                    <span v-if="result.type" class="result-type">
                      <i class="fa fa-file-text-o" style="color: #3498db;"></i>
                      {{ getTypeName(result.type) }}
                    </span>

                    <span v-if="result.create_time" class="result-date">
                      <i class="fa fa-calendar" style="color: #27ae60;"></i>
                      {{ formatDate(result.create_time) }}
                    </span>

                    <span v-if="result.categories && result.categories.length > 0" class="result-category-container">
                      <i class="fa fa-folder" style="color: #3498db;"></i>
                      <router-link
                        v-for="(cat, index) in result.categories"
                        :key="'cat-'+index"
                        :to="`/category/${cat.id}`"
                        class="category-tag"
                      >
                        <span v-html="highlightSearch(cat.name)"></span>
                      </router-link>
                    </span>

                    <span v-if="result.tags && result.tags.length > 0" class="result-tags-container">
                      <i class="fa fa-tags" style="color: #e67e22;"></i>
                      <router-link
                        v-for="(tag, index) in result.tags"
                        :key="'tag-'+index"
                        :to="`/tag/${tag.id}`"
                        class="category-tag tag-item"
                      >
                        <span v-html="highlightSearch(tag.name)"></span>
                      </router-link>
                    </span>

                    <span v-if="result.views !== undefined" class="result-views">
                      <i class="fa fa-eye" style="color: #f39c12;"></i>
                      {{ $t('posts.views', { count: result.views }) }}
                    </span>

                    <span v-if="result.comments !== undefined" class="result-comments">
                      <i class="fa fa-comment" style="color: #3498db;"></i>
                      {{ result.comments >= 0 ? $t('posts.commentsCount', { count: result.comments }) : $t('posts.noComments') }}
                    </span>

                    <span v-if="result.likes !== undefined" class="result-likes">
                      <i class="fa fa-heart" style="color: #e74c3c;"></i>
                      {{ result.likes || 0 }}
                    </span>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <!-- Loading More -->
          <div v-if="loading && currentPage > 1" class="loading-more">
            <el-skeleton :rows="1" animated />
          </div>

          <!-- Pagination -->
          <div v-if="!loading && !error && searchResults && searchResults.length > 0" class="pagination-container">
            <div v-if="currentPage < totalPages" class="load-more">
              <button @click="loadMoreResults" class="load-more-btn">{{ $t('search.loadMore') }}</button>
            </div>

            <div v-else class="no-more-results">
              {{ $t('search.noMoreResults') }}
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
import MainLayout from './components/MainLayout.vue';
import Sidebar from './components/Sidebar.vue';
import './assets/blog.css';
import { search } from '@/api/search';
import { formatDate } from '@/utils/date';
import defaultThumbnailImage from '@/assets/images/default.jpeg';
import { useHead } from '@vueuse/head';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

export default {
  name: 'SearchPage',
  components: {
    MainLayout,
    Sidebar
  },
  setup() {
    const { t } = useI18n();
    const route = useRoute();
    const router = useRouter();
    const blogConfigStore = useBlogConfigStore();

    // State
    const searchQuery = ref('');
    const searchResults = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const currentPage = ref(1);
    const totalPages = ref(1);
    const pageSize = ref(10);
    const total = ref(0);
    const defaultThumbnail = defaultThumbnailImage;
    const showSearchTips = ref(false);

    // 计算SEO相关的值
    const siteName = computed(() => blogConfigStore.config.site_name || '');

    // 初始化查询
    if (route.query.q) {
      searchQuery.value = route.query.q;
    }

    // Methods
    const handleSearch = async () => {
      const trimmedQuery = searchQuery.value.trim();

      if (!trimmedQuery) {
        error.value = t('search.emptyQuery');
        return;
      }

      // Sanitize the query
      const sanitizedQuery = sanitizeSearchQuery(trimmedQuery);

      if (!sanitizedQuery) {
        error.value = t('search.invalidQuery');
        return;
      }

      // Update searchQuery with sanitized version for display
      searchQuery.value = sanitizedQuery;

      // 更新 URL
      router.push({ path: '/search', query: { q: sanitizedQuery } });

      currentPage.value = 1;
      searchResults.value = [];
      await performSearch();
    };

    const performSearch = async (isLoadMore = false) => {
      try {
        loading.value = true;
        error.value = null;

        // Validate search query
        if (!searchQuery.value) {
          error.value = t('search.emptyQuery');
          loading.value = false;
          return;
        }

        const response = await search({
          query: searchQuery.value,
          page: currentPage.value,
          limit: pageSize.value
        });

        if (response && response.code === 200) {
          const newResults = response.data?.list || [];

          // Process results if needed
          newResults.forEach(result => {
            // Ensure thumbnail exists
            if (!result.thumbnail) {
              result.thumbnail = defaultThumbnail;
            }

            // Ensure excerpt exists
            if (!result.description && !result.excerpt) {
              result.excerpt = t('search.noExcerpt');
            }
          });

          if (isLoadMore) {
            searchResults.value = [...searchResults.value, ...newResults];
          } else {
            searchResults.value = newResults;
          }

          total.value = response.data?.total || 0;
          totalPages.value = Math.ceil(total.value / pageSize.value) || 1;
        } else {
          console.error('Search API error:', response?.msg || 'Unknown error');
          error.value = response?.msg || t('search.error');
          searchResults.value = []; // Initialize with empty array on error
        }
      } catch (err) {
        console.error('Search error:', err);
        error.value = t('search.error');
        searchResults.value = []; // Initialize with empty array on error
      } finally {
        loading.value = false;
      }
    };

    const loadMoreResults = () => {
      currentPage.value++;
      performSearch(true);
    };

    const getResultUrl = (result) => {
      switch (result.type) {
        case 'post':
          return `/post/${result.id}`;
        case 'page':
          return `/page/${result.slug}`;
        case 'category':
          return `/category/${result.id}`;
        case 'tag':
          return `/tag/${result.id}`;
        default:
          return `/post/${result.id}`;
      }
    };

    const getTypeName = (type) => {
      switch (type) {
        case 'post':
          return t('search.typePost');
        case 'page':
          return t('search.typePage');
        case 'category':
          return t('search.typeCategory');
        case 'tag':
          return t('search.typeTag');
        default:
          return t('search.typePost');
      }
    };

    const sanitizeSearchQuery = (query) => {
      if (!query) return '';
      // Remove excessive spaces and trim
      return query.trim().replace(/\s+/g, ' ');
    };

    const highlightSearch = (text) => {
      if (!text || !searchQuery.value) return text;

      try {
        // Create a safe version of the search query for regex
        const escapeRegExp = (string) => {
          return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        };

        const sanitizedQuery = sanitizeSearchQuery(searchQuery.value);
        if (!sanitizedQuery) return text;

        const safeQuery = escapeRegExp(sanitizedQuery);
        const regex = new RegExp(`(${safeQuery})`, 'gi');

        return text.replace(regex, '<span class="search-highlight">$1</span>');
      } catch (error) {
        console.error('Error highlighting search text:', error);
        return text;
      }
    };

    // 监听路由变化，重新加载搜索
    watch(() => route.query.q, (newQuery) => {
      if (newQuery) {
        searchQuery.value = sanitizeSearchQuery(newQuery);
        handleSearch();
      }
    });

    // 添加结构化数据
    const addStructuredData = () => {
      // 移除已有的结构化数据
      const existingScript = document.getElementById('search-structured-data');
      if (existingScript) {
        existingScript.remove();
      }

      // 创建搜索页结构化数据
      const structuredData = {
        "@context": "https://schema.org",
        "@type": "SearchResultsPage",
        "mainEntity": {
          "@type": "ItemList",
          "itemListElement": searchResults.value.map((result, index) => ({
            "@type": "ListItem",
            "position": index + 1,
            "url": `${window.location.origin}${getResultUrl(result)}`,
            "name": result.title
          }))
        }
      };

      // 创建并添加脚本标签
      const script = document.createElement('script');
      script.id = 'search-structured-data';
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify(structuredData);
      document.head.appendChild(script);
    };

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `${searchQuery.value ? `"${searchQuery.value}" - ` : ''}${t('search.searchResults')} - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => searchQuery.value ?
            t('search.metaDescription', { query: searchQuery.value, siteName: siteName.value }) :
            t('search.defaultMetaDescription', { siteName: siteName.value }))
        },
        {
          name: 'robots',
          content: 'noindex, follow'
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `${searchQuery.value ? `"${searchQuery.value}" - ` : ''}${t('search.searchResults')} - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => searchQuery.value ?
            t('search.metaDescription', { query: searchQuery.value, siteName: siteName.value }) :
            t('search.defaultMetaDescription', { siteName: siteName.value }))
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
          content: computed(() => `${searchQuery.value ? `"${searchQuery.value}" - ` : ''}${t('search.searchResults')} - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => searchQuery.value ?
            t('search.metaDescription', { query: searchQuery.value, siteName: siteName.value }) :
            t('search.defaultMetaDescription', { siteName: siteName.value }))
        }
      ],
      link: [
        {
          rel: 'canonical',
          href: computed(() => `${window.location.origin}/search${searchQuery.value ? `?q=${encodeURIComponent(searchQuery.value)}` : ''}`)
        }
      ]
    });

    // 执行初始搜索
    if (searchQuery.value) {
      handleSearch();
    }

    // 当搜索结果更新时添加结构化数据
    watch(searchResults, () => {
      if (searchResults.value.length > 0) {
        addStructuredData();
      }
    });

    return {
      searchQuery,
      searchResults,
      loading,
      error,
      currentPage,
      totalPages,
      total,
      defaultThumbnail,
      showSearchTips,
      handleSearch,
      loadMoreResults,
      getResultUrl,
      getTypeName,
      highlightSearch,
      formatDate,
      sanitizeSearchQuery
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

.search-icon {
  margin-right: 10px;
  color: var(--primary-color, #09c);
}

.page-title {
  font-size: 28px;
  color: var(--heading-color, #333);
  margin-bottom: 10px;
  font-weight: 700;
  transition: color 0.3s ease;
}

.page-description {
  font-size: 16px;
  color: var(--text-color, #555);
  margin-bottom: 20px;
  transition: color 0.3s ease;
}

.search-container {
  max-width: 600px;
  margin: 0 auto;
}

.search-form {
  position: relative;
  display: flex;
}

.search-input {
  flex: 1;
  padding: 12px 15px;
  font-size: 16px;
  border: 2px solid var(--border-color, #eee);
  border-radius: 4px;
  background-color: var(--bg-color-light, #fff);
  color: var(--text-color, #333);
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color, #09c);
  box-shadow: 0 0 5px rgba(0, 153, 204, 0.2);
}

.search-button {
  position: absolute;
  right: 2px;
  top: 2px;
  bottom: 2px;
  background-color: var(--primary-color, #09c);
  color: white;
  border: none;
  border-radius: 0 3px 3px 0;
  padding: 0 15px;
  font-size: 18px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.search-button:hover {
  background-color: var(--primary-color-hover, #007ea6);
}

.loading-container, .error-container, .empty-container {
  background-color: var(--card-bg-color, #f5f5f5);
  border-radius: 3px;
  padding: 20px;
  text-align: center;
  margin-bottom: 20px;
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.error-state, .empty-state {
  max-width: 500px;
  margin: 0 auto;
}

.error-icon, .empty-icon {
  color: var(--warning-color, #f39c12);
  font-size: 1.2em;
  margin-right: 8px;
  transition: color 0.3s ease;
}

.error-title, .empty-title {
  font-size: 22px;
  color: var(--heading-color, #333);
  margin-bottom: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s ease;
}

.error-message, .empty-message {
  font-size: 16px;
  color: var(--text-color, #666);
  margin-bottom: 20px;
  line-height: 1.6;
  transition: color 0.3s ease;
}

.error-actions, .empty-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 20px;
}

.search-tips {
  margin-top: 25px;
  padding: 20px;
  background-color: var(--card-bg-color, #f8f9fa);
  border-radius: 8px;
  text-align: left;
  width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.tips-title {
  font-size: 18px;
  color: var(--heading-color, #333);
  margin-bottom: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  transition: color 0.3s ease;
}

.tips-title i {
  margin-right: 8px;
  color: var(--warning-color, #f39c12);
  font-size: 20px;
  transition: color 0.3s ease;
}

.tips-list {
  padding-left: 10px;
  list-style-type: none;
}

.tip-item {
  margin-bottom: 12px;
  color: var(--text-color, #555);
  font-size: 15px;
  display: flex;
  align-items: flex-start;
  transition: color 0.3s ease;
}

.tip-item:last-child {
  margin-bottom: 0;
}

.tip-icon {
  margin-right: 10px;
  color: var(--primary-color, #3498db);
  min-width: 16px;
  margin-top: 3px;
  transition: color 0.3s ease;
}

.retry-button, .search-tips-btn, .back-home-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 15px;
  background-color: var(--bg-color-light, #eee);
  color: var(--text-color, #333);
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  text-decoration: none;
}

.retry-button:hover, .search-tips-btn:hover {
  background-color: var(--bg-color-hover, #ddd);
}

.back-home-btn {
  background-color: var(--primary-color, #09c);
  color: white;
}

.back-home-btn:hover {
  background-color: var(--primary-color-hover, #007ea6);
}

.retry-button i, .search-tips-btn i, .back-home-btn i {
  margin-right: 5px;
}

.results-summary {
  margin-bottom: 20px;
  color: var(--light-text-color, #666);
  font-size: 14px;
  transition: color 0.3s ease;
  text-align: left;
}

.result-item {
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.3s ease, color 0.3s ease;
  text-align: left;
}

.result-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.result-content-wrap {
  display: flex;
  gap: 20px;
}

.result-thumb {
  flex: 0 0 180px;
  overflow: visible;
  position: relative;
}

.result-thumbnail {
  width: 180px;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.result-thumb:hover .result-thumbnail {
  transform: scale(1.05);
}

.result-content-inner {
  flex: 1;
  min-width: 0;
  text-align: left;
}

.result-title {
  font-size: 20px;
  font-weight: bold;
  margin-top: 0;
  margin-bottom: 12px;
  line-height: 1.4;
  text-align: left;
}

.result-title a {
  color: var(--heading-color, #333);
  text-decoration: none;
  transition: color 0.3s ease;
}

.result-title a:hover {
  color: var(--primary-color, #09c);
}

.result-badge {
  display: inline-block;
  font-size: 0.65em;
  padding: 2px 6px;
  border-radius: 12px;
  margin-right: 8px;
  font-weight: normal;
  vertical-align: middle;
  line-height: 1.2;
}

.sticky-badge {
  background-color: #e74c3c;
  color: #fff;
}

.recommend-badge {
  background-color: #3498db;
  color: #fff;
}

.result-excerpt {
  margin-bottom: 15px;
  color: var(--text-color, #666);
  font-size: 15px;
  line-height: 1.6;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  transition: color 0.3s ease;
  text-align: left;
}

.result-excerpt .highlight {
  color: #e74c3c !important;
  font-weight: bold !important;
  padding: 0 1px;
  background-color: transparent !important;
}

.result-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  font-size: 14px;
  color: var(--light-text-color, #999);
  gap: 12px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed var(--border-color, #eee);
  transition: color 0.3s ease, border-color 0.3s ease;
  text-align: left;
  justify-content: flex-start;
}

.result-type, .result-date, .result-views, .result-comments, .result-likes {
  display: inline-flex;
  align-items: center;
}

.result-meta i {
  margin-right: 5px;
}

.result-category-container, .result-tags-container {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 5px;
}

.category-tag {
  display: inline-block;
  background-color: var(--bg-color-light, #f5f7fa);
  border-radius: 15px;
  padding: 2px 8px;
  margin: 2px;
  font-size: 0.9em;
  color: var(--text-color, #666);
  text-decoration: none;
  transition: all 0.3s;
}

.category-tag:hover {
  background-color: var(--bg-color-hover, #e6eaf0);
  cursor: pointer;
  color: var(--heading-color, #333);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.tag-item {
  background-color: #f8f4ec;
}

.tag-item:hover {
  background-color: #f0e9d8;
}

.loading-more {
  padding: 20px;
  background-color: #fff;
  border-radius: 3px;
  margin-bottom: 20px;
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

.no-more-results {
  color: #999;
  font-size: 14px;
}

.highlight,
span.highlight,
.search-results .highlight,
.search-results .result-title .highlight,
.search-results .result-excerpt .highlight,
.search-results .category-tag .highlight,
.search-results .tag-item .highlight {
  color: red !important;
  font-weight: bold !important;
  background-color: transparent !important;
  text-decoration: none !important;
  display: inline !important;
}

@media (max-width: 768px) {
  .result-content-wrap {
    flex-direction: column;
    gap: 15px;
  }

  .result-thumb {
    flex: 0 0 auto;
    width: 100%;
  }

  .result-thumbnail {
    width: 100%;
    height: 160px;
    border-radius: 6px;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .result-title {
    font-size: 18px;
    margin-bottom: 8px;
  }

  .result-excerpt {
    font-size: 14px;
    margin-bottom: 10px;
  }

  .result-meta {
    gap: 8px;
    font-size: 12px;
    margin-top: 5px;
    padding-top: 5px;
  }

  .page-title {
    font-size: 24px;
  }

  .page-description {
    font-size: 14px;
  }

  .search-input {
    padding: 10px 15px;
    font-size: 14px;
  }

  .search-button {
    padding: 0 12px;
    font-size: 16px;
  }

  .error-actions, .empty-actions {
    flex-direction: column;
    gap: 10px;
  }

  .retry-button, .search-tips-btn, .back-home-btn {
    width: 100%;
  }

  .search-tips {
    padding: 15px;
    margin-top: 20px;
  }

  .tips-title {
    font-size: 16px;
    margin-bottom: 12px;
  }

  .tip-item {
    font-size: 14px;
    margin-bottom: 10px;
  }

  .highlight {
    padding: 0;
  }
}

/* Add CSS for highlight demo */
.search-highlight-demo {
  margin: 20px 0;
  padding: 15px;
  background-color: var(--card-bg-color, #f8f9fa);
  border-radius: 8px;
  text-align: left;
  color: var(--text-color, #333);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.highlight-examples p {
  margin: 10px 0;
  font-size: 15px;
  color: var(--text-color, #333);
  transition: color 0.3s ease;
}

/* Enhanced highlight styling with increased specificity */
:deep(.search-highlight) {
  background-color: rgba(255, 230, 0, 0.3) !important;
  color: var(--primary-color, #e74c3c) !important;
  font-weight: bold !important;
  border-radius: 2px !important;
  padding: 0 2px !important;
  display: inline !important;
}

.result-title :deep(.search-highlight),
.result-excerpt :deep(.search-highlight) {
  background-color: rgba(255, 230, 0, 0.3) !important;
  color: var(--primary-color, #e74c3c) !important;
  font-weight: bold !important;
  padding: 0 2px !important;
}

.category-tag :deep(.search-highlight),
.tag-item :deep(.search-highlight) {
  background-color: rgba(255, 230, 0, 0.15) !important;
  color: var(--primary-color, #e74c3c) !important;
  font-weight: bold !important;
}

/* 暗黑模式和系统模式下的高亮样式调整 */
html.dark-theme :deep(.search-highlight),
html.dark :deep(.search-highlight),
html.system-dark-theme :deep(.search-highlight),
html.dark.system-theme :deep(.search-highlight) {
  background-color: rgba(255, 230, 0, 0.2) !important;
  color: var(--primary-color, #00b8ff) !important;
}

/* 暗黑模式和系统模式下的按钮样式调整 */
html.dark-theme .retry-button,
html.dark-theme .search-tips-btn,
html.dark .retry-button,
html.dark .search-tips-btn,
html.system-dark-theme .retry-button,
html.system-dark-theme .search-tips-btn,
html.dark.system-theme .retry-button,
html.dark.system-theme .search-tips-btn {
  background-color: var(--bg-color-mute, #2c2c2c);
  color: var(--text-color-primary, #ffffff);
}

html.dark-theme .retry-button:hover,
html.dark-theme .search-tips-btn:hover,
html.dark .retry-button:hover,
html.dark .search-tips-btn:hover,
html.system-dark-theme .retry-button:hover,
html.system-dark-theme .search-tips-btn:hover,
html.dark.system-theme .retry-button:hover,
html.dark.system-theme .search-tips-btn:hover {
  background-color: var(--bg-color-hover, #444);
}

/* 暗黑模式和系统模式下的文字颜色统一为白色 */
html.dark-theme .page-title,
html.dark-theme .page-description,
html.dark-theme .error-title,
html.dark-theme .error-message,
html.dark-theme .empty-title,
html.dark-theme .empty-message,
html.dark-theme .results-summary,
html.dark-theme .result-title a,
html.dark-theme .result-excerpt,
html.dark-theme .result-meta,
html.dark-theme .no-more-results,
html.dark-theme .search-input::placeholder,
html.dark-theme .search-input,
html.dark .page-title,
html.dark .page-description,
html.dark .error-title,
html.dark .error-message,
html.dark .empty-title,
html.dark .empty-message,
html.dark .results-summary,
html.dark .result-title a,
html.dark .result-excerpt,
html.dark .result-meta,
html.dark .no-more-results,
html.dark .search-input::placeholder,
html.dark .search-input,
html.system-dark-theme .page-title,
html.system-dark-theme .page-description,
html.system-dark-theme .error-title,
html.system-dark-theme .error-message,
html.system-dark-theme .empty-title,
html.system-dark-theme .empty-message,
html.system-dark-theme .results-summary,
html.system-dark-theme .result-title a,
html.system-dark-theme .result-excerpt,
html.system-dark-theme .result-meta,
html.system-dark-theme .no-more-results,
html.system-dark-theme .search-input::placeholder,
html.system-dark-theme .search-input,
html.dark.system-theme .page-title,
html.dark.system-theme .page-description,
html.dark.system-theme .error-title,
html.dark.system-theme .error-message,
html.dark.system-theme .empty-title,
html.dark.system-theme .empty-message,
html.dark.system-theme .results-summary,
html.dark.system-theme .result-title a,
html.dark.system-theme .result-excerpt,
html.dark.system-theme .result-meta,
html.dark.system-theme .no-more-results,
html.dark.system-theme .search-input::placeholder,
html.dark.system-theme .search-input {
  color: #ffffff !important;
}

/* 暗黑模式和系统模式下的搜索结果项样式调整 */
html.dark-theme .result-item,
html.dark .result-item,
html.system-dark-theme .result-item,
html.dark.system-theme .result-item {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

html.dark-theme .result-item:hover,
html.dark .result-item:hover,
html.system-dark-theme .result-item:hover,
html.dark.system-theme .result-item:hover {
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
}

html.dark-theme .result-meta,
html.dark .result-meta,
html.system-dark-theme .result-meta,
html.dark.system-theme .result-meta {
  border-top: 1px dashed rgba(255, 255, 255, 0.1);
}

html.dark-theme .category-tag,
html.dark .category-tag,
html.system-dark-theme .category-tag,
html.dark.system-theme .category-tag {
  background-color: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

html.dark-theme .category-tag:hover,
html.dark .category-tag:hover,
html.system-dark-theme .category-tag:hover,
html.dark.system-theme .category-tag:hover {
  background-color: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

html.dark-theme .tag-item,
html.dark .tag-item,
html.system-dark-theme .tag-item,
html.dark.system-theme .tag-item {
  background-color: rgba(255, 255, 255, 0.08);
}

html.dark-theme .tag-item:hover,
html.dark .tag-item:hover,
html.system-dark-theme .tag-item:hover,
html.dark.system-theme .tag-item:hover {
  background-color: rgba(255, 255, 255, 0.15);
}

/* 暗黑模式和系统模式下的搜索输入框样式调整 */
html.dark-theme .search-input,
html.dark .search-input,
html.system-dark-theme .search-input,
html.dark.system-theme .search-input {
  background-color: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}

html.dark-theme .search-input:focus,
html.dark .search-input:focus,
html.system-dark-theme .search-input:focus,
html.dark.system-theme .search-input:focus {
  border-color: var(--primary-color, #00b8ff);
  box-shadow: 0 0 5px rgba(0, 184, 255, 0.3);
}

/* 暗黑模式和系统模式下的加载更多按钮样式调整 */
html.dark-theme .load-more-btn,
html.dark .load-more-btn,
html.system-dark-theme .load-more-btn,
html.dark.system-theme .load-more-btn {
  background-color: var(--primary-color, #00b8ff);
  color: #ffffff;
}

html.dark-theme .load-more-btn:hover,
html.dark .load-more-btn:hover,
html.system-dark-theme .load-more-btn:hover,
html.dark.system-theme .load-more-btn:hover {
  background-color: var(--primary-hover, #33c9ff);
}

@media (max-width: 768px) {
  :deep(.search-highlight) {
    padding: 0 1px !important;
  }
}
</style>