<!-- 标签页 -->
<template>
  <MainLayout>
    <div class="container">
      <div class="tag-header">
        <h1 class="tag-title">
          <i class="fa fa-tag tag-icon"></i>
          {{ tag ? tag.name : $t('webTag.loading') }}
        </h1>
        <div v-if="tag && tag.description" class="tag-description">{{ tag.description }}</div>
        <div v-if="tag && tag.post_count" class="tag-post-count">
          {{ $t('webTag.postCount', { count: tag.post_count }) }}
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
              <p class="error-message">{{ $t('webTag.tryAgainLater') }}</p>
              <div class="error-actions">
                <router-link to="/" class="back-home-btn">
                  <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                </router-link>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="posts.length === 0" class="empty-container">
            <div class="empty-state">
              <h3 class="empty-title">
                <i class="fa fa-tag empty-icon"></i>
                {{ $t('webTag.noPosts') }}
              </h3>
              <p class="empty-message">{{ $t('webTag.noPostsMessage', { tag: tag ? tag.name : '' }) }}</p>
              <div class="empty-actions">
                <router-link to="/" class="back-home-btn">
                  <i class="fa fa-home"></i> {{ $t('common.backToHome') }}
                </router-link>
              </div>
            </div>
          </div>

          <!-- Posts List -->
          <div v-else class="posts-list">
            <article v-for="post in posts" :key="post.id" class="post">
              <div class="post-content-wrap">
                <div class="post-thumb">
                  <router-link :to="`/post/${post.id}`">
                    <img :src="post.thumbnail || defaultThumbnail" :alt="post.title" class="post-thumbnail">
                  </router-link>
                </div>
                <div class="post-content-inner">
                  <h2 class="post-title">
                    <router-link :to="`/post/${post.id}`">
                      <span v-if="post.is_top" class="post-badge sticky-badge">{{ $t('posts.sticky') }}</span>
                      <span v-if="post.is_recommend" class="post-badge recommend-badge">{{ $t('posts.recommended') }}</span>
                      {{ post.title }}
                    </router-link>
                  </h2>
                  <div class="post-content">
                    <p>{{ post.description || post.excerpt }}</p>
                  </div>
                  <div class="post-meta">
                    <span v-if="post.author" class="post-author">
                      <i class="fa fa-user" style="color: #9b59b6;"></i>
                      <img v-if="post.author.avatar" :src="post.author.avatar || defaultThumbnail" :alt="post.author.nickname || post.author.username" class="author-avatar">
                      {{ post.author.nickname || post.author.username }}
                    </span>

                    <span v-if="post.create_time" class="post-date">
                      <i class="fa fa-calendar" style="color: #27ae60;"></i>
                      {{ formatDate(post.create_time) }}
                    </span>

                    <span v-if="post.categories && post.categories.length > 0" class="post-category-container">
                      <i class="fa fa-folder" style="color: #3498db;"></i>
                      <router-link
                        v-for="(cat, index) in post.categories"
                        :key="'cat-'+index"
                        :to="`/category/${cat.id}`"
                        class="category-tag"
                      >
                        {{ cat.name }}
                      </router-link>
                    </span>

                    <span v-if="post.tags && post.tags.length > 0" class="post-tags-container">
                      <i class="fa fa-tags" style="color: #e67e22;"></i>
                      <router-link
                        v-for="(tag, index) in post.tags"
                        :key="'tag-'+index"
                        :to="`/tag/${tag.id}`"
                        class="category-tag tag-item"
                      >
                        {{ tag.name }}
                      </router-link>
                    </span>

                    <span v-if="post.views" class="post-views">
                      <i class="fa fa-eye" style="color: #f39c12;"></i>
                      {{ $t('posts.views', { count: post.views }) }}
                    </span>

                    <router-link v-if="post.comments !== undefined" :to="`/post/${post.id}#comments`" class="post-comments">
                      <i class="fa fa-comment" style="color: #3498db;"></i>
                      {{ post.comments >= 0 ? $t('posts.commentsCount', { count: post.comments }) : $t('posts.noComments') }}
                    </router-link>

                    <button
                      v-if="post.likes !== undefined"
                      class="like-button"
                      :class="{ 'liked': post.is_liked, 'like-loading': post.likeLoading }"
                      @click="toggleLike(post)"
                      :title="post.is_liked ? $t('posts.unlike') : $t('posts.like')"
                    >
                      <i class="fa" :class="post.is_liked ? 'fa-heart' : 'fa-heart-o'"></i>
                      <span>{{ post.likes }}</span>
                    </button>

                    <button
                      class="favorite-button"
                      :class="{ 'favorited': post.is_favorited, 'favorite-loading': post.favoriteLoading }"
                      @click="toggleFavorite(post)"
                      :title="post.is_favorited ? $t('posts.unfavorite') : $t('posts.favorite')"
                      :data-post-id="post.id"
                    >
                      <i class="fa" :class="post.is_favorited ? 'fa-star' : 'fa-star-o'"></i>
                      <span v-if="post.favorites">{{ post.favorites }}</span>
                    </button>
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
          <div v-if="!loading && !error && posts.length > 0" class="pagination-container">
            <div v-if="currentPage < totalPages" class="load-more">
              <button @click="loadMorePosts" class="load-more-btn">{{ $t('posts.loadMore') }}</button>
            </div>

            <div v-else class="no-more-posts">
              {{ $t('posts.noMorePosts') }}
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
import { getPostList } from '@/api/post';
import { getTagDetail } from '@/api/tag';
import { formatDate } from '@/utils/date';
import defaultThumbnail from '@/assets/images/default.jpeg';
import { useHead } from 'unhead';
import { computed, watch, ref } from 'vue';
import { likePost, unlikePost } from '@/api/like';
import { addFavorite, cancelFavorite } from '@/api/favorite';
import { getCurrentVisitorId } from '@/utils/visitorId';
import { ElMessage } from 'element-plus';

export default {
    name: 'TagPage',
    components: {
      MainLayout,
      Sidebar
    },
    data() {
      return {
        tag: null,
        posts: [],
        loading: true,
        error: null,
        currentPage: 1,
        totalPages: 1,
        pageSize: 10,
        total: 0,
        defaultThumbnail: defaultThumbnail
      };
    },

    computed: {
      tagId() {
        return parseInt(this.$route.params.tag) || 0;
      }
    },
    watch: {
      // Watch for route param changes to reload data when navigating between tags
      '$route.params.tag'(newId) {
        this.currentPage = 1;
        this.posts = [];
        this.loadData();
      },
      // 监听标签变化，更新SEO元数据
      tag: {
        handler(newTag) {
          if (newTag) {
            this.updateSeoMetadata();
          }
        },
        deep: true
      }
    },
    methods: {
      async loadTag() {
        try {
          if (!this.tagId) {
            this.error = this.$t('webTag.invalidId');
            this.loading = false;
            return;
          }

          const response = await getTagDetail(this.tagId);

          if (response.code === 200 && response.data) {
            this.tag = response.data;
          } else {
            this.error = response.msg || this.$t('webTag.notFound');
          }
        } catch (error) {
          console.error('Error fetching tag:', error);
          this.error = this.$t('webTag.fetchError');
        }
      },

      async loadPosts(isLoadMore = false) {
        try {
          this.loading = true;

          const response = await getPostList({
            tag_id: this.tagId,
            page: this.currentPage,
            limit: this.pageSize,
            is_check_like: 1,
            is_check_favorite: 1,
            status: 1 // Only published posts
          });

          if (response.code === 200) {
            const newPosts = response.data.list || [];

            // Add isLiked, likeLoading, is_favorited, and favoriteLoading properties to each post
            newPosts.forEach(post => {
              post.isLiked = false;
              post.likeLoading = false;
              post.is_favorited = false;
              post.favoriteLoading = false;
            });

            if (isLoadMore) {
              this.posts = [...this.posts, ...newPosts];
            } else {
              this.posts = newPosts;
            }

            this.total = response.data.total || 0;
            this.totalPages = Math.ceil(this.total / this.pageSize) || 1;
          } else {
            this.error = response.msg || this.$t('webTag.postsLoadError');
          }
        } catch (error) {
          console.error('Error fetching posts:', error);
          this.error = this.$t('webTag.postsLoadError');
        } finally {
          this.loading = false;
        }
      },

      async loadData() {
        this.loading = true;
        this.error = null;

        try {
          await this.loadTag();
          if (!this.error) {
            await this.loadPosts();
          }
        } catch (error) {
          console.error('Error loading data:', error);
          this.error = this.$t('common.unexpectedError');
        } finally {
          this.loading = false;
        }
      },

      loadMorePosts() {
        this.currentPage++;
        this.loadPosts(true);
      },

      //点赞取消点赞
      toggleLike(post) {
        // 确保文章对象有ID
        if (!post || !post.id) {
          console.error('无效的文章对象:', post);
          return;
        }
        // 设置加载状态
        post.likeLoading = true;
        // 准备请求数据
        const requestData = { post_id: post.id };

        // 根据当前点赞状态决定是点赞还是取消点赞
        const action = post.is_liked ? unlikePost : likePost;

        // 发送请求
        action(requestData)
          .then(response => {
            if (response && response.code === 200) {
              // 更新点赞状态
              post.is_liked = !post.is_liked;
              post.likes = post.is_liked ? (post.likes || 0) + 1 : Math.max((post.likes || 0) - 1, 0);

              // 显示成功消息
              ElMessage.success(post.is_liked ? '点赞成功' : '取消点赞成功');
            } else {
              // 显示错误消息
              ElMessage.error(response?.msg || '操作失败，请重试');
            }
          })
          .catch(error => {
            console.error('点赞操作失败:', error);
            ElMessage.error('操作失败，请重试');
          })
          .finally(() => {
            // 清除加载状态
            post.likeLoading = false;
          });

        // 添加错误处理，确保加载状态被清除
        setTimeout(() => {
          if (post.likeLoading) {
            post.likeLoading = false;
          }
        }, 5000); // 5秒后自动清除，防止卡死
      },

      // 切换收藏状态
      toggleFavorite(post) {
        // 检查用户是否登录
        const token = localStorage.getItem('token');
        if (!token) {
          ElMessage.warning(this.$t('posts.loginToFavorite'));
          return;
        }

        // 如果正在加载中，不处理
        if (post.favoriteLoading) {
          return;
        }

        // 设置加载状态
        post.favoriteLoading = true;

        // 根据当前收藏状态选择操作
        const action = post.is_favorited ? cancelFavorite : addFavorite;
        const requestData = { post_id: post.id };

        // 发送请求
        action(requestData)
          .then(response => {
            if (response && response.code === 200) {
              // 更新收藏状态
              post.is_favorited = !post.is_favorited;
              post.favorites = post.is_favorited ? (post.favorites || 0) + 1 : Math.max((post.favorites || 0) - 1, 0);

              // 添加视觉反馈
              if (post.is_favorited) {
                // 找到当前文章的收藏按钮
                const favoriteButtons = document.querySelectorAll(`.favorite-button[data-post-id="${post.id}"]`);
                favoriteButtons.forEach(button => {
                  // 添加一个临时的动画类
                  button.classList.add('favorite-animation');
                  // 在动画结束后移除该类
                  setTimeout(() => {
                    button.classList.remove('favorite-animation');
                  }, 700);
                });
              }

              // 显示成功消息
              ElMessage.success(post.is_favorited ? this.$t('posts.favoriteSuccess') : this.$t('posts.unfavoriteSuccess'));
            } else {
              // 显示错误消息
              ElMessage.error(response?.msg || this.$t('posts.favoriteError'));
            }
          })
          .catch(error => {
            console.error('收藏操作失败:', error);
            ElMessage.error(this.$t('posts.favoriteError'));
          })
          .finally(() => {
            // 清除加载状态
            post.favoriteLoading = false;
          });

        // 添加错误处理，确保加载状态被清除
        setTimeout(() => {
          if (post.favoriteLoading) {
            post.favoriteLoading = false;
          }
        }, 5000); // 5秒后自动清除，防止卡死
      },

      formatDate,

      // 添加更新SEO元数据的方法
      updateSeoMetadata() {
        // 确保tag对象已加载
        if (this.tag) {
          const title = `${this.tag.name || '标签'} - ${this.$t('webTag.tag')}`;
          const description = this.tag.description ||
            `${this.tag.name}标签下的文章列表，共有${this.tag.post_count || 0}篇文章`;

          // 设置文档标题
          document.title = title;

          // 设置meta描述
          this.updateMetaTag('description', description);

          // 设置meta关键词
          this.updateMetaTag('keywords', `${this.tag.name},${this.tag.name}标签,文章列表`);

          // 设置Open Graph标签
          this.updateMetaTag('og:title', title, 'property');
          this.updateMetaTag('og:description', description, 'property');
          this.updateMetaTag('og:type', 'website', 'property');
          this.updateMetaTag('og:url', window.location.href, 'property');

          // 设置Twitter Card标签
          this.updateMetaTag('twitter:card', 'summary', 'name');
          this.updateMetaTag('twitter:title', title, 'name');
          this.updateMetaTag('twitter:description', description, 'name');

          // 设置canonical链接
          this.updateCanonicalLink();
        }
      },

      // 辅助方法：更新meta标签
      updateMetaTag(name, content, attributeName = 'name') {
        let meta = document.querySelector(`meta[${attributeName}="${name}"]`);

        if (!meta) {
          meta = document.createElement('meta');
          meta.setAttribute(attributeName, name);
          document.head.appendChild(meta);
        }

        meta.setAttribute('content', content);
      },

      // 辅助方法：更新canonical链接
      updateCanonicalLink() {
        let link = document.querySelector('link[rel="canonical"]');

        if (!link) {
          link = document.createElement('link');
          link.setAttribute('rel', 'canonical');
          document.head.appendChild(link);
        }

        link.setAttribute('href', window.location.href);
      }
    },
    created() {
      this.loadData();
    },
    mounted() {
      // 添加SEO元数据
      this.updateSeoMetadata();
    }
};
</script>

<style scoped>
  .tag-header {
    position: relative;
    margin-bottom: 35px;
    padding: 25px 30px;
    border-radius: 8px;
    background-color: var(--card-bg-color, #fff);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    text-align: center;
    overflow: hidden;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  .tag-title {
    font-size: 32px;
    color: var(--heading-color, #333);
    margin-bottom: 15px;
    font-weight: 700;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
  }

  .tag-icon {
    margin-right: 12px;
    color: var(--primary-color, #09c);
    font-size: 0.9em;
  }

  .tag-description {
    font-size: 16px;
    color: var(--text-color, #666);
    margin-bottom: 10px;
    line-height: 1.5;
    transition: color 0.3s ease;
  }

  .tag-post-count {
    font-size: 14px;
    color: var(--light-text-color, #999);
    transition: color 0.3s ease;
  }

  .loading-container, .error-container, .empty-container {
    background-color: var(--card-bg-color, #fff);
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    margin-bottom: 30px;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
  }

  .error-state, .empty-state {
    max-width: 500px;
    margin: 0 auto;
  }

  .error-icon, .empty-icon {
    font-size: 1em;
    color: #f39c12;
    margin-right: 8px;
  }

  .empty-icon {
    color: #999;
  }

  .error-title, .empty-title {
    font-size: 24px;
    color: var(--heading-color, #333);
    margin-bottom: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
  }

  .error-message, .empty-message {
    font-size: 16px;
    color: var(--text-color, #666);
    margin-bottom: 25px;
    line-height: 1.6;
    transition: color 0.3s ease;
  }

  .error-actions, .empty-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 10px;
  }

  .back-home-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-color, #09c);
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .back-home-btn:hover {
    background-color: var(--primary-color-hover, #007fad);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 153, 204, 0.3);
  }

  .back-home-btn i {
    margin-right: 6px;
  }

  .loading-more {
    padding: 20px;
    background-color: var(--card-bg-color, #fff);
    border-radius: 3px;
    margin-bottom: 20px;
    transition: background-color 0.3s ease;
  }

  .no-more-posts {
    text-align: center;
    color: var(--light-text-color, #999);
    padding: 20px;
    font-size: 14px;
    transition: color 0.3s ease;
  }

  .post {
    background-color: var(--card-bg-color, #fff);
    border-radius: 3px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    text-align: left;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  .post-badge {
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

  .post-content-wrap {
    display: flex;
    flex-wrap: nowrap;
    position: relative;
  }

  .post-thumb {
    flex: 0 0 180px;
    margin-right: 20px;
    overflow: visible;
    position: relative;
  }

  .post-thumbnail {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
    border-radius: 8px;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .post-thumb:hover .post-thumbnail {
    transform: scale(1.05);
  }

  .post-content-inner {
    flex: 1;
    min-width: 0;
  }

  .post-title {
    margin: 0 0 10px;
    font-size: 20px;
    font-weight: bold;
    text-align: left;
    line-height: 1.4;
  }

  .post-title a {
    color: var(--heading-color, #333);
    text-decoration: none;
    transition: color 0.3s;
  }

  .post-title a:hover {
    color: #09c;
  }

  .post-content {
    margin-bottom: 15px;
    font-size: 16px;
    color: var(--text-color, #666);
    line-height: 1.6;
    text-align: left;
    transition: color 0.3s ease;
  }

  .post-content p {
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .post-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    font-size: 14px;
    color: var(--light-text-color, #999);
    text-align: left;
    gap: 12px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed var(--border-color, #eee);
    transition: color 0.3s ease, border-color 0.3s ease;
  }

  .post-meta span, .post-meta a {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .post-meta a {
    color: var(--link-color, #09c);
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .post-meta a:hover {
    color: var(--link-color-hover, #07a);
  }

  .post-meta i {
    margin-right: 5px;
  }

  .author-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 5px;
    border: 1px solid var(--border-color-light, rgba(0, 0, 0, 0.05));
    transition: border-color 0.3s ease;
  }

  .post-author, .post-category-container, .post-tags-container {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
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
    background-color: #e6eaf0;
    cursor: pointer;
    color: #333;
  }

  .tag-item {
    background-color: #f8f4ec;
  }

  .tag-item:hover {
    background-color: #f0e9d8;
  }

  .load-more {
    text-align: center;
    margin-top: 30px;
  }

  .load-more-btn {
    background-color: #09c;
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
  }

  .load-more-btn:hover {
    background-color: #007fad;
  }

  .like-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    border: 1px solid var(--border-color, #eee);
    border-radius: 20px;
    padding: 0.2rem 0.5rem;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9em;
    line-height: 1;
    height: 24px;
  }

  .like-button:hover {
    background-color: var(--bg-color-hover, #f5f5f5);
  }

  .like-button i {
    margin-right: 0.25rem;
    color: #e74c3c;
    font-size: 0.9em;
    line-height: 1;
  }

  .like-button span {
    color: #e74c3c;
    font-weight: normal;
    line-height: 1;
  }

  .pagination-container {
    margin-top: 20px;
  }

  @media (max-width: 768px) {
    .tag-title {
      font-size: 22px;
    }

    .tag-description {
      font-size: 14px;
    }

    .error-container, .empty-container {
      padding: 30px 15px;
    }

    .error-icon, .empty-icon {
      font-size: 1em;
      margin-right: 6px;
    }

    .error-title, .empty-title {
      font-size: 20px;
      margin-bottom: 10px;
    }

    .error-message, .empty-message {
      font-size: 14px;
      margin-bottom: 20px;
    }

    .error-actions, .empty-actions {
      flex-direction: column;
      gap: 10px;
    }

    .post {
      padding: 12px;
    }

    .post-title {
      font-size: 18px;
      margin-bottom: 5px;
    }

    .post-content p {
      -webkit-line-clamp: 1;
      font-size: 14px;
    }

    .post-meta {
      gap: 8px;
      font-size: 12px;
      margin-top: 5px;
      padding-top: 5px;
    }

    .post-thumb {
      flex: 0 0 120px;
      margin-right: 15px;
      height: auto;
      overflow: visible;
    }

    .post-thumbnail {
      height: 80px;
      border-radius: 6px;
      box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .post-content-wrap {
      flex-direction: row;
    }
  }

/* Favorite Button Styles */
.favorite-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  border: 1px solid rgba(255, 193, 7, 0.3);
  border-radius: 20px;
  padding: 0.25rem 0.6rem;
  height: 28px;
  min-width: 36px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  margin-left: 0.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.favorite-button:hover {
  background-color: #fffde7;
  border-color: #ffc107;
  box-shadow: 0 2px 5px rgba(255, 193, 7, 0.2);
  transform: translateY(-1px);
}

.favorite-button i {
  margin-right: 0.25rem;
  color: #ffc107;
  font-size: 1em;
  line-height: 1;
  transition: transform 0.3s, color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.favorite-button span {
  color: #ffc107;
  font-weight: 500;
  line-height: 1;
  transition: color 0.3s;
  display: inline-block;
  vertical-align: middle;
}

.favorite-button i:only-child {
  margin-right: 0;
}

.favorite-button.favorited {
  background-color: #fffde7;
  border-color: #ffc107;
  box-shadow: 0 2px 5px rgba(255, 193, 7, 0.3);
}

.favorite-button.favorited i {
  color: #ffc107;
  transform: scale(1.2);
  text-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
}

.favorite-button.favorited:hover {
  background-color: #fff8e1;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(255, 193, 7, 0.4);
}

.favorite-button.favorited:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at center, rgba(255, 193, 7, 0.2) 0%, rgba(255, 193, 7, 0) 70%);
  opacity: 0;
  transform: scale(0.5);
  transition: opacity 0.3s, transform 0.3s;
}

.favorite-button.favorited:hover:before {
  opacity: 1;
  transform: scale(1.5);
}

.favorite-button.favorite-loading {
  opacity: 0.7;
  cursor: wait;
}

.favorite-button.favorite-loading i {
  animation: pulse 1s infinite;
}

.favorite-button,
.favorite-button i,
.favorite-button span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Animation Keyframes */
@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes favorite-pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(255, 193, 7, 0.3);
  }
  100% {
    box-shadow: 0 0 0 12px rgba(255, 193, 7, 0);
  }
}

@keyframes favorite-star {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  15% {
    transform: scale(0.8);
    opacity: 0.8;
  }
  30% {
    transform: scale(1.5) rotate(20deg);
    opacity: 1;
  }
  50% {
    transform: scale(1.2) rotate(-15deg);
    opacity: 1;
  }
  70% {
    transform: scale(1.4) rotate(5deg);
    opacity: 1;
  }
  100% {
    transform: scale(1) rotate(0);
    opacity: 1;
  }
}

@keyframes favorite-sparkle {
  0%, 100% {
    opacity: 0;
    transform: scale(0);
  }
  25% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.5);
  }
  75% {
    opacity: 0.3;
    transform: scale(1.75);
  }
}

.favorite-animation {
  animation: favorite-pulse 0.7s cubic-bezier(0.215, 0.610, 0.355, 1.000);
  position: relative;
}

.favorite-animation i {
  animation: favorite-star 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  color: #ffc107 !important;
}

.favorite-animation::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at center, rgba(255, 193, 7, 0.6) 0%, rgba(255, 193, 7, 0) 70%);
  border-radius: 50%;
  z-index: -1;
  animation: favorite-sparkle 0.7s cubic-bezier(0.215, 0.610, 0.355, 1.000);
  pointer-events: none;
}
  /* 暗黑模式和跟随系统模式样式 */
html.dark-theme .tag-header,
html.dark .tag-header,
html.system-dark-theme .tag-header,
html.dark.system-theme .tag-header {
  background-color: var(--bg-color, #232324);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

html.dark-theme .tag-title,
html.dark .tag-title,
html.system-dark-theme .tag-title,
html.dark.system-theme .tag-title {
  color: #ffffff;
}

html.dark-theme .tag-description,
html.dark .tag-description,
html.system-dark-theme .tag-description,
html.dark.system-theme .tag-description,
html.dark-theme .post-content p,
html.dark .post-content p,
html.system-dark-theme .post-content p,
html.dark.system-theme .post-content p {
  color: #ffffff;
}

html.dark-theme .tag-post-count,
html.dark .tag-post-count,
html.system-dark-theme .tag-post-count,
html.dark.system-theme .tag-post-count {
  color: #b0b0b0;
}

html.dark-theme .post-title a,
html.dark .post-title a,
html.system-dark-theme .post-title a,
html.dark.system-theme .post-title a {
  color: #ffffff;
}

html.dark-theme .post,
html.dark .post,
html.system-dark-theme .post,
html.dark.system-theme .post {
  background-color: var(--bg-color, #232324);
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

html.dark-theme .post-meta,
html.dark .post-meta,
html.system-dark-theme .post-meta,
html.dark.system-theme .post-meta {
  border-top-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .error-container,
html.dark .error-container,
html.system-dark-theme .error-container,
html.dark.system-theme .error-container,
html.dark-theme .empty-container,
html.dark .empty-container,
html.system-dark-theme .empty-container,
html.dark.system-theme .empty-container {
  background-color: var(--bg-color, #232324);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

html.dark-theme .error-title,
html.dark .error-title,
html.system-dark-theme .error-title,
html.dark.system-theme .error-title,
html.dark-theme .empty-title,
html.dark .empty-title,
html.system-dark-theme .empty-title,
html.dark.system-theme .empty-title {
  color: #ffffff;
}

html.dark-theme .error-message,
html.dark .error-message,
html.system-dark-theme .error-message,
html.dark.system-theme .error-message,
html.dark-theme .empty-message,
html.dark .empty-message,
html.system-dark-theme .empty-message,
html.dark.system-theme .empty-message {
  color: #e0e0e0;
}

html.dark-theme .category-tag,
html.dark .category-tag,
html.system-dark-theme .category-tag,
html.dark.system-theme .category-tag {
  background-color: rgba(255, 255, 255, 0.1);
  color: #e0e0e0;
}

html.dark-theme .category-tag:hover,
html.dark .category-tag:hover,
html.system-dark-theme .category-tag:hover,
html.dark.system-theme .category-tag:hover {
  background-color: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

html.dark-theme .like-button,
html.dark .like-button,
html.system-dark-theme .like-button,
html.dark.system-theme .like-button {
  border-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .like-button:hover,
html.dark .like-button:hover,
html.system-dark-theme .like-button:hover,
html.dark.system-theme .like-button:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .empty-icon,
html.dark .empty-icon,
html.system-dark-theme .empty-icon,
html.dark.system-theme .empty-icon {
  color: #b0b0b0;
}

html.dark-theme .author-avatar,
html.dark .author-avatar,
html.system-dark-theme .author-avatar,
html.dark.system-theme .author-avatar {
  border-color: rgba(255, 255, 255, 0.1);
}
</style>