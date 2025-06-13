<template>
  <div class="blog-container">
    <Header />
    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <h1 class="page-title">
            <i class="fa fa-folder-open page-icon"></i>
            {{ $t('categoryCloud.title') }}
          </h1>
          <p class="page-description">
            {{ $t('categoryCloud.description') }}
          </p>
        </div>

        <div class="row">
          <div class="col-12">
            <!-- 加载状态 -->
            <div v-if="loading" class="loading-container">
              <div class="category-cloud-skeleton">
                <div v-for="i in 12" :key="i" class="category-skeleton-block">
                  <div class="category-skeleton-bullet"></div>
                  <div class="category-skeleton-content">
                    <div class="category-skeleton-title"></div>
                    <div class="category-skeleton-description"></div>
                    <div class="category-skeleton-article"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 错误状态 -->
            <div v-else-if="error" class="error-container">
              <div class="error-state">
                <h3 class="error-title">
                  <i class="fa fa-exclamation-triangle error-icon"></i>
                  {{ error }}
                </h3>
                <p class="error-message">{{ $t('categoryCloud.tryAgainLater') }}</p>
                <div class="error-actions">
                  <button class="retry-button" @click="fetchCategories">
                    <i class="fa fa-refresh"></i> {{ $t('categoryCloud.retry') }}
                  </button>
                  <router-link to="/" class="back-home-btn">
                    <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 分类为空 -->
            <div v-else-if="categories.length === 0" class="empty-container">
              <div class="empty-state">
                <h3 class="empty-title">
                  <i class="fa fa-folder-open empty-icon"></i>
                  {{ $t('categoryCloud.noCategories') }}
                </h3>
                <p class="empty-message">{{ $t('categoryCloud.noCategoriesMessage') }}</p>
                <div class="empty-actions">
                  <router-link to="/" class="back-home-btn">
                    <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 分类列表 -->
            <div v-else class="category-cloud-container">
              <div class="category-stats">
                <div class="stat-item">
                  <div class="stat-value">{{ categories.length }}</div>
                  <div class="stat-label">{{ $t('categoryCloud.totalCategories') }}</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value">{{ totalPosts }}</div>
                  <div class="stat-label">{{ $t('categoryCloud.totalPosts') }}</div>
                </div>
              </div>

              <!-- 分类卡片 -->
              <div class="category-cloud-section">
                <h2 class="tag-cloud-title">
                  <i class="fa fa-folder-open"></i> {{ $t('categoryCloud.allCategories') }}
                </h2>
                <div class="category-cloud">
                  <div
                    v-for="category in categories"
                    :key="category.id"
                    class="category-block"
                  >
                    <div class="category-block-inner">
                      <div class="category-header">
                        <router-link
                          :to="{ name: 'Category', params: { category: category.id } }"
                          class="category-cloud-item"
                          :style="{ backgroundColor: getRandomColor(category.id, 0.1) }"
                        >
                          {{ category.name }}
                        </router-link>
                        <span class="category-count">×{{ category.post_count || 0 }}</span>
                      </div>
                      <div class="category-description" v-if="category.description">
                        {{ category.description }}
                      </div>
                      <div class="category-description" v-else>
                        {{ $t('categoryCloud.noDescription') }}
                      </div>
                      <div class="category-article-container">
                        <div class="category-article-title">{{ $t('categoryCloud.latestArticle') }}:</div>
                        <router-link
                          v-if="category.post && category.post.length > 0"
                          :to="`/post/${category.post[0].id}`"
                          class="category-article"
                        >
                          {{ category.post[0].title }}
                        </router-link>
                        <span v-else class="category-no-article">{{ $t('categoryCloud.noArticles') }}</span>
                      </div>
                  </div>
                </div>
              </div>

              <!-- 分类树形结构 -->
              <div class="category-tree-container">
                <h2 class="section-title">{{ $t('categoryCloud.categoryTree') }}</h2>
                <el-tree
                  :data="categoryTree"
                  :props="{
                    label: 'name',
                    children: 'children'
                  }"
                  node-key="id"
                  :expand-on-click-node="false"
                  default-expand-all
                >
                  <template #default="{ node, data }">
                    <div class="custom-tree-node">
                      <span class="node-label">{{ node.label }}</span>
                      <span class="node-count">
                        <el-tag size="small" type="info">{{ data.post_count }} {{ $t('categoryCloud.posts') }}</el-tag>
                      </span>
                      <span class="node-description" v-if="data.description">
                        {{ data.description }}
                      </span>
                      <span class="node-actions">
                        <router-link :to="{ name: 'Category', params: { category: data.id } }">
                          <el-button type="primary" size="small" text>
                            {{ $t('categoryCloud.view') }}
                          </el-button>
                        </router-link>
                      </span>
                    </div>
                  </template>
                </el-tree>
              </div>
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
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useHead } from '@vueuse/head';
import api from '@/api';
import { apiUrls } from '@/api';
import { useBlogConfigStore } from '@/stores/blogConfig';
import BackToTop from "@/views/index/components/BackToTop.vue";

export default {
  name: 'CategoryCloud',
  components: {
    Header,
    Footer,
    BackToTop
  },
  setup() {
    const { t } = useI18n();
    const blogConfigStore = useBlogConfigStore();

    // 数据
    const categories = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // 计算总文章数
    const totalPosts = computed(() => {
      return categories.value.reduce((sum, category) => sum + category.post_count, 0);
    });

    // 构建分类树
    const categoryTree = computed(() => {
      // 复制分类数组，避免修改原始数据
      const categoriesCopy = JSON.parse(JSON.stringify(categories.value));

      // 创建ID到分类的映射
      const categoryMap = {};
      categoriesCopy.forEach(category => {
        categoryMap[category.id] = category;
        category.children = [];
      });

      // 构建树结构
      const rootCategories = [];

      categoriesCopy.forEach(category => {
        if (category.parent_id && categoryMap[category.parent_id]) {
          // 如果有父分类，添加到父分类的children中
          categoryMap[category.parent_id].children.push(category);
        } else {
          // 否则作为根分类
          rootCategories.push(category);
        }
      });

      return rootCategories;
    });

    // 获取分类数据
    const fetchCategories = async () => {
      loading.value = true;
      error.value = null;

      try {
        const response = await api.get(apiUrls.BLOG_API.GET_CAT_LIST, {
          params: {
            page: 1,
            limit: 100,
            status: 1,
            is_get_post_count: 1, // 分类下文章统计
            is_get_post: 1 // 获取单个最新的文章
          }
        });

        if (response && response.code === 200) {
          categories.value = response.data.list || [];

          // 处理分类描述，确保描述不超过100个字符
          categories.value.forEach(category => {
            if (category.description && category.description.length > 100) {
              category.description = category.description.substring(0, 100) + '...';
            }
          });
        } else {
          error.value = response?.msg || t('common.unknownError');
        }
      } catch (err) {
        console.error('获取分类列表失败:', err);
        error.value = t('common.networkError');
      } finally {
        loading.value = false;
      }
    };

    // 获取随机颜色
    const getRandomColor = (id, alpha = 1) => {
      // 使用固定的颜色数组，确保每个分类的颜色一致
      const colors = [
        { r: 103, g: 194, b: 58 },  // 绿色 #67C23A
        { r: 230, g: 162, b: 60 },  // 橙色 #E6A23C
        { r: 245, g: 108, b: 108 }, // 红色 #F56C6C
        { r: 144, g: 147, b: 153 }, // 灰色 #909399
        { r: 64, g: 158, b: 255 },  // 蓝色 #409EFF
        { r: 146, g: 84, b: 222 },  // 紫色 #9254DE
        { r: 54, g: 207, b: 201 },  // 青色 #36CFC9
        { r: 255, g: 156, b: 110 }, // 桔色 #FF9C6E
        { r: 115, g: 209, b: 61 },  // 浅绿 #73D13D
        { r: 64, g: 169, b: 255 },  // 浅蓝 #40A9FF
        { r: 255, g: 163, b: 158 }, // 浅红 #FFA39E
        { r: 212, g: 177, b: 6 },   // 金色 #D4B106
        { r: 24, g: 144, b: 255 },  // 深蓝 #1890FF
        { r: 19, g: 194, b: 194 },  // 深青 #13C2C2
        { r: 114, g: 46, b: 209 }   // 深紫 #722ED1
      ];

      // 使用ID作为索引，确保同一个分类总是获得相同的颜色
      const color = colors[id % colors.length];

      // 返回带透明度的颜色
      return `rgba(${color.r}, ${color.g}, ${color.b}, ${alpha})`;
    };

    // 根据文章数量获取相对大小
    const getRelativeSize = (count) => {
      const min = 14; // 最小字体大小
      const max = 24; // 最大字体大小

      // 找出最大文章数
      const maxCount = Math.max(...categories.value.map(c => c.post_count));

      if (maxCount === 0) return `${min}px`;

      // 计算相对大小
      const size = min + (count / maxCount) * (max - min);
      return `${Math.round(size)}px`;
    };

    // 设置页面元数据
    useHead({
      title: computed(() => `${t('categoryCloud.title')} - ${blogConfigStore.config.site_name}`),
      meta: [
        {
          name: 'description',
          content: computed(() => t('categoryCloud.metaDescription'))
        },
        {
          name: 'keywords',
          content: computed(() => {
            const categoryNames = categories.value.map(c => c.name).join(', ');
            return `${t('categoryCloud.metaKeywords')}, ${categoryNames}`;
          })
        }
      ]
    });

    // 生命周期钩子
    onMounted(() => {
      fetchCategories();
    });

    return {
      categories,
      loading,
      error,
      totalPosts,
      categoryTree,
      fetchCategories,
      getRandomColor,
      getRelativeSize
    };
  }
};
</script>

<style scoped>
.blog-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  padding: 30px 0;
  background-color: var(--bg-color, #f5f7fa);
  transition: background-color 0.3s ease;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.page-header {
  margin-bottom: 35px;
  padding: 25px 30px;
  border-radius: 8px;
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.page-title {
  font-size: 28px;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-icon {
  margin-right: 10px;
  color: var(--primary-color, #09c);
}

.page-description {
  font-size: 16px;
  color: #555;
  margin-bottom: 20px;
  max-width: 800px;
  margin: 0 auto;
  line-height: 1.6;
}

/* 加载状态 */
.loading-container {
  min-height: 400px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.category-cloud-skeleton {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  width: 100%;
}

.category-skeleton-block {
  display: flex;
  background: var(--bg-color, #fff);
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
  height: 120px;
}

.category-skeleton-bullet {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #e0e0e0;
  margin-right: 12px;
  flex-shrink: 0;
  animation: pulse 1.5s infinite;
}

.category-skeleton-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.category-skeleton-title {
  height: 20px;
  background: #e0e0e0;
  width: 70%;
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

.category-skeleton-description {
  height: 40px;
  background: #e0e0e0;
  width: 100%;
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

.category-skeleton-article {
  height: 16px;
  background: #e0e0e0;
  width: 90%;
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 0.6; }
  50% { opacity: 0.8; }
  100% { opacity: 0.6; }
}

/* 错误状态 */
.error-container, .empty-container {
  min-height: 400px;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.error-state, .empty-state {
  max-width: 500px;
  padding: 30px;
  background: var(--bg-color, #fff);
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
}

.error-title, .empty-title {
  font-size: 1.5rem;
  color: var(--text-color-primary, #303133);
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.error-icon {
  color: #F56C6C;
  margin-right: 10px;
}

.empty-icon {
  color: var(--text-color-secondary, #909399);
  margin-right: 10px;
}

.error-message, .empty-message {
  color: var(--text-color-secondary, #606266);
  margin-bottom: 20px;
  line-height: 1.6;
}

.error-actions, .empty-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
}

.retry-button, .back-home-btn {
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.retry-button {
  background-color: var(--primary-color, #409EFF);
  color: white;
  border: none;
}

.retry-button:hover {
  background-color: var(--primary-color-light, #66b1ff);
}

.back-home-btn {
  background-color: var(--bg-color, #fff);
  color: var(--text-color-primary, #303133);
  border: 1px solid var(--border-color, #DCDFE6);
}

.back-home-btn:hover {
  color: var(--primary-color, #409EFF);
  border-color: var(--primary-color-light, #c6e2ff);
}

.retry-button i, .back-home-btn i {
  margin-right: 5px;
}

/* 分类列表 */
.category-cloud-container, .category-cloud-section {
  padding: 25px;
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
  transition: background-color 0.3s ease, color 0.3s ease;
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

.tag-cloud-title i {
  margin-right: 10px;
  color: var(--primary-color, #09c);
}

.category-stats {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
  gap: 40px;
}

.stat-item {
  text-align: center;
  background: var(--bg-color, #fff);
  padding: 20px 30px;
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
  min-width: 150px;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: bold;
  color: var(--primary-color, #409EFF);
  margin-bottom: 5px;
}

.stat-label {
  font-size: 1rem;
  color: var(--text-color-secondary, #606266);
}

.category-cloud {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  margin: -10px;
}

.category-block {
  width: 25%;
  padding: 10px;
  box-sizing: border-box;
}

.category-block-inner {
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

.category-block-inner:hover {
  border-color: var(--border-color-hover, #ddd);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.category-header {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.category-cloud-item {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 20px;
  color: var(--primary-color, #409EFF);
  font-weight: 500;
  text-decoration: none;
  margin-right: 10px;
  transition: all 0.3s;
}

.category-cloud-item:hover {
  color: white;
  background-color: var(--primary-color, #409EFF) !important;
}

.category-count {
  font-size: 0.9rem;
  color: var(--text-color-secondary, #909399);
}

.category-description {
  color: var(--text-color-regular, #606266);
  font-size: 0.9rem;
  margin-bottom: 15px;
  line-height: 1.6;
  flex: 1;
}

.category-article-container {
  padding-top: 10px;
  border-top: 1px dashed var(--border-color-light, #E4E7ED);
}

.category-article-title {
  font-size: 0.85rem;
  color: var(--text-color-secondary, #909399);
  margin-bottom: 5px;
  text-align: left;
}

.category-article {
  color: var(--text-color-primary, #303133);
  text-decoration: none;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.3s;
  text-align: left;
  display: block;
}

.category-article:hover {
  color: var(--primary-color, #409EFF);
}

.category-no-article {
  color: var(--text-color-secondary, #909399);
  font-size: 0.9rem;
  font-style: italic;
  padding-top: 10px;
  border-top: 1px dashed var(--border-color-light, #E4E7ED);
}

/* 分类树形结构 */
.category-tree-container {
  background: var(--bg-color, #fff);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}

.section-title {
  font-size: 1.5rem;
  margin-bottom: 20px;
  color: var(--text-color-primary, #303133);
  text-align: center;
  position: relative;
  padding-bottom: 10px;
}

.section-title:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 3px;
  background-color: var(--primary-color, #409EFF);
  border-radius: 3px;
}

.custom-tree-node {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  width: 100%;
  padding: 8px 0;
}

.node-label {
  font-weight: 500;
  color: var(--text-color-primary, #303133);
  margin-right: 10px;
}

.node-count {
  margin-right: 15px;
}

.node-description {
  flex: 1;
  color: var(--text-color-secondary, #606266);
  font-size: 0.9rem;
  margin: 5px 0 5px 24px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.node-actions {
  margin-left: auto;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .page-title {
    font-size: 2rem;
  }

  .page-description {
    font-size: 1rem;
  }

  .category-stats {
    flex-direction: column;
    gap: 20px;
  }

  .category-block {
    width: 100%;
  }

  .stat-item {
    min-width: auto;
    width: 100%;
  }

  .custom-tree-node {
    flex-direction: column;
    align-items: flex-start;
  }

  .node-actions {
    margin-left: 0;
    margin-top: 5px;
  }
}

@media (min-width: 576px) and (max-width: 767px) {
  .category-block {
    width: 50%;
  }
}

@media (min-width: 768px) and (max-width: 991px) {
  .category-block {
    width: 33.333%;
  }
}
</style>
