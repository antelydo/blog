<template>
  <div class="sitemap-container">
    <div class="page-header">
      <div class="container">
        <h1 class="page-title">{{ $t('sitemap.title') }}</h1>
        <div class="breadcrumb">
          <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/' }">{{ $t('sitemap.home') }}</el-breadcrumb-item>
            <el-breadcrumb-item>{{ $t('sitemap.title') }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="sitemap-content">
        <!-- 加载状态 -->
        <div v-if="loading" class="loading-container">
          <el-skeleton :rows="10" animated />
        </div>

        <template v-else>
          <!-- 主要页面链接 -->
          <div class="sitemap-section">
            <h2 class="section-title">{{ $t('sitemap.mainPages') }}</h2>
            <ul class="sitemap-list">
              <li v-for="page in mainPages" :key="page.path">
                <router-link :to="page.path" :title="page.title + (page.description ? ' - ' + page.description : '')">{{ page.title }}</router-link>
                <span class="page-description" v-if="page.description">- {{ page.description }}</span>
              </li>
            </ul>
          </div>

          <!-- 分类页面 -->
          <div class="sitemap-section">
            <h2 class="section-title">{{ $t('sitemap.categories') }}</h2>
            <ul class="sitemap-list">
              <li v-for="category in categories" :key="category.id">
                <router-link :to="'/category/' + category.id" :title="category.name + (category.description ? ' - ' + category.description : '')">{{ category.name }}</router-link>
                <span class="page-description" v-if="category.description">- {{ category.description }}</span>

                <!-- 子分类 -->
                <ul v-if="category.children && category.children.length > 0" class="sitemap-sublist">
                  <li v-for="subCategory in category.children" :key="subCategory.id">
                    <router-link :to="'/category/' + subCategory.id" :title="subCategory.name + (subCategory.description ? ' - ' + subCategory.description : '')">{{ subCategory.name }}</router-link>
                    <span class="page-description" v-if="subCategory.description">- {{ subCategory.description }}</span>
                  </li>
                </ul>
              </li>
            </ul>
          </div>

          <!-- 最新文章 -->
          <div class="sitemap-section">
            <h2 class="section-title">{{ $t('sitemap.recentPosts') }}</h2>
            <ul class="sitemap-list">
              <li v-for="post in recentPosts" :key="post.id">
                <router-link :to="'/post/' + post.id" :title="post.title">{{ post.title }}</router-link>
                <span class="post-date">{{ formatDate(post.publish_time) }}</span>
              </li>
            </ul>
          </div>

          <!-- 标签云 -->
          <div class="sitemap-section">
            <h2 class="section-title">{{ $t('sitemap.tags') }}</h2>
            <div class="tag-cloud">
              <router-link
                v-for="tag in tags"
                :key="tag.id"
                :to="'/tag/' + tag.id"
                class="tag-item"
                :style="{ fontSize: getTagSize(tag.count) + 'px' }"
                :title="tag.name + ' (' + tag.count + ')'"
              >
                {{ tag.name }}
              </router-link>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import api, { apiUrls } from '../../api';
import { useBlogConfigStore } from '../../stores/blogConfig';
import { useHead } from '@vueuse/head';

export default {
  name: 'SitemapPage',
  setup() {
    const loading = ref(true);
    const categories = ref([]);
    const recentPosts = ref([]);
    const tags = ref([]);
    const blogConfigStore = useBlogConfigStore();

    // SEO设置
    useHead({
      title: computed(() => `网站地图 - ${blogConfigStore.siteTitle || '网站'}`),
      meta: [
        {
          name: 'description',
          content: '网站地图，帮助您快速了解本站的内容结构'
        }
      ]
    });

    // 主要页面列表
    const mainPages = ref([
      { path: '/', title: '首页', description: '网站首页' },
      { path: '/category_cloud', title: '分类导航', description: '所有分类' },
      { path: '/tag_cloud', title: '标签云', description: '所有标签' },
      { path: '/about', title: '关于我们', description: '了解我们的故事' },
      { path: '/contact', title: '联系我们', description: '与我们取得联系' },
    ]);

    // 获取分类数据
    const fetchCategories = async () => {
      try {
        const response = await api.get(apiUrls.BLOG_API.CATEGORY_LIST);
        if (response && response.code === 200) {
          categories.value = response.data || [];
        }
      } catch (error) {
        console.error('获取分类失败:', error);
      }
    };

    // 获取最新文章
    const fetchRecentPosts = async () => {
      try {
        const response = await api.get(apiUrls.BLOG_API.POST_LIST, {
          params: {
            page: 1,
            limit: 10,
            sort: 'publish_time',
            order: 'desc'
          }
        });
        if (response && response.code === 200) {
          recentPosts.value = response.data.list || [];
        }
      } catch (error) {
        console.error('获取最新文章失败:', error);
      }
    };

    // 获取标签
    const fetchTags = async () => {
      try {
        const response = await api.get(apiUrls.BLOG_API.TAG_LIST);
        if (response && response.code === 200) {
          tags.value = response.data.list || [];
        }
      } catch (error) {
        console.error('获取标签失败:', error);
      }
    };

    // 格式化日期
    const formatDate = (dateValue) => {
      if (!dateValue) return '';

      // 处理Unix时间戳（秒）
      let date;
      if (typeof dateValue === 'number' || (typeof dateValue === 'string' && !isNaN(dateValue) && dateValue.length === 10)) {
        date = new Date(Number(dateValue) * 1000);
      } else {
        date = new Date(dateValue);
      }

      // 检查日期是否有效
      if (isNaN(date.getTime())) return '';

      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };

    // 根据标签使用次数计算字体大小
    const getTagSize = (count) => {
      const minSize = 12;
      const maxSize = 24;
      const minCount = 1;

      // 检查tags.value是否是数组且不为空
      if (!tags.value || !Array.isArray(tags.value) || tags.value.length === 0) {
        return minSize;
      }

      // 找出最大计数
      const maxCount = Math.max(...tags.value.map(tag => tag.count || 1));

      // 计算字体大小
      if (maxCount === minCount) return minSize;

      const size = minSize + ((count - minCount) / (maxCount - minCount)) * (maxSize - minSize);
      return Math.round(size);
    };

    // 加载所有数据
    const loadAllData = async () => {
      loading.value = true;

      try {
        await Promise.all([
          fetchCategories(),
          fetchRecentPosts(),
          fetchTags()
        ]);
      } catch (error) {
        console.error('加载网站地图数据失败:', error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      // 设置页面标题
      document.title = `网站地图 - ${blogConfigStore.siteTitle || '网站'}`;

      // 加载数据
      loadAllData();
    });

    return {
      loading,
      mainPages,
      categories,
      recentPosts,
      tags,
      formatDate,
      getTagSize
    };
  }
}
</script>

<style scoped>
.sitemap-container {
  padding-bottom: 60px;
  background-color: var(--bg-color, #f5f7fa);
  min-height: calc(100vh - 300px);
  transition: background-color 0.3s ease;
}

.page-header {
  background-color: var(--card-bg-color, #ecf5ff);
  padding: 30px 0;
  margin-bottom: 30px;
  text-align: left;
  transition: background-color 0.3s ease;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  margin: 0 0 15px;
  color: var(--el-text-color-primary, #303133);
  text-align: left;
}

.breadcrumb {
  color: var(--el-text-color-secondary, #909399);
  text-align: left;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
  text-align: left;
}

.sitemap-content {
  background-color: var(--card-bg-color, #fff);
  border-radius: 4px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  padding: 30px;
  text-align: left;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.loading-container {
  padding: 20px;
  text-align: left;
}

.sitemap-section {
  margin-bottom: 40px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border-color, #e4e7ed);
  color: var(--heading-color, #303133);
  text-align: left;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.sitemap-list {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}

.sitemap-list li {
  margin-bottom: 12px;
  line-height: 1.5;
}

.sitemap-list a {
  color: var(--link-color, #409eff);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.sitemap-list a:hover {
  color: var(--link-color-hover, #79bbff);
  text-decoration: underline;
}

.page-description {
  color: var(--light-text-color, #909399);
  font-size: 14px;
  margin-left: 5px;
  transition: color 0.3s ease;
}

.sitemap-sublist {
  list-style: none;
  padding-left: 20px;
  margin: 10px 0 0;
  text-align: left;
}

.sitemap-sublist li {
  margin-bottom: 8px;
}

.post-date {
  color: var(--light-text-color, #909399);
  font-size: 13px;
  margin-left: 10px;
  transition: color 0.3s ease;
}

.tag-cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-start;
  text-align: left;
}

.tag-item {
  display: inline-block;
  padding: 5px 10px;
  background-color: var(--el-color-primary-light-9, #ecf5ff);
  color: var(--el-color-primary, #409eff);
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.3s;
}

.tag-item:hover {
  background-color: var(--el-color-primary-light-8, #d9ecff);
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .page-header {
    padding: 20px 0;
  }

  .page-title {
    font-size: 24px;
  }

  .sitemap-content {
    padding: 20px 15px;
  }

  .section-title {
    font-size: 18px;
  }
}
</style>
