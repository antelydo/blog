<!-- 最新文章 -->
<template>
  <div class="latest-posts">
    <h3 class="section-title">{{ $t('posts.latestPosts') }}</h3>

    <div v-if="loading && !hasMore" class="loading-container">
      <el-skeleton :rows="5" animated />
    </div>

    <div v-else-if="error && posts.length === 0" class="error-container">
      <p class="error-message">{{ $t('posts.error') }}</p>
      <el-button type="primary" size="small" @click="fetchPosts">{{ $t('posts.retry') }}</el-button>
    </div>

    <template v-else>
      <!-- Sticky posts -->
      <article v-for="post in stickyPosts" :key="'sticky-' + post.id" class="post post-sticky">
        <div class="post-sticky-badge">{{ $t('posts.sticky') }}</div>
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
                <i class="fa fa-user"></i>
                <img :src="post.author.avatar || defaultThumbnail" :alt="post.author.name" class="author-avatar">
                {{ post.author.name }}
              </span>

              <span v-if="post.create_time" class="post-date">
                <i class="fa fa-calendar"></i>
                {{ formatDate(post.create_time) }}
              </span>

              <span v-if="post.category" class="post-category">
                <i class="fa fa-folder" style="color: #3498db;"></i>
                {{ post.category.name }}
              </span>

              <span v-if="post.views !== undefined" class="post-views">
                <i class="fa fa-eye"></i>
                {{ $t('posts.views', { count: post.views }) }}
              </span>

              <router-link v-if="post.comments !== undefined" :to="`/post/${post.id}#comments`" class="post-comments">
                <i class="fa fa-comment" style="color: #3498db;"></i>
                {{ post.comments >= 0 ? $t('posts.commentsCount', { count: post.comments}) : $t('posts.noComments') }}
              </router-link>

              <button
                v-if="post.likes !== undefined"
                class="like-button"
                :class="{ 'liked': post.is_liked, 'like-loading': isPostLikeLoading(post.id) }"
                @click="toggleLike(post)"
                :title="post.is_liked ? $t('posts.unlike') : $t('posts.like')"
              >
                <i class="fa" :class="post.is_liked ? 'fa-heart' : 'fa-heart-o'"></i>
                <span>{{ post.likes }}</span>
              </button>

              <button
                class="favorite-button"
                :class="{ 'favorited': post.is_favorited, 'favorite-loading': isPostFavoriteLoading(post.id) }"
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

      <!-- Regular posts -->
      <article v-for="post in regularPosts" :key="post.id" class="post">
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
                <i class="fa fa-user"></i>
                <img :src="post.author.avatar || defaultThumbnail" :alt="post.author.username" class="author-avatar">
                {{ post.author.username }}
              </span>

              <span v-if="post.create_time" class="post-date">
                <i class="fa fa-calendar"></i>
                {{ formatDate(post.create_time) }}
              </span>

              <span v-if="post.categories" class="post-category-container">
                <i class="fa fa-folder" style="color: #3498db; margin-right: 5px;"></i>
                <router-link
                  v-for="(category, index) in post.categories"
                  :key="'cat-'+index"
                  :to="`/category/${category.id}`"
                  class="category-tag"
                >
                  {{ category.name }}
                </router-link>
              </span>
              <span v-if="post.tags" class="post-tags-container">
                <i class="fa fa-tags" style="color: #e67e22; margin-right: 5px;"></i>
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
                <i class="fa fa-eye"></i>
                {{ $t('posts.views', { count: post.views }) }}
              </span>
              <router-link v-if="post.comments" :to="`/post/${post.id}#comments`" class="post-comments">
                <i class="fa fa-comment" style="color: #3498db;"></i>
                {{ post.comments > 0 ? $t('posts.commentsCount', { count: post.comments }) : $t('posts.noComments') }}
              </router-link>

              <button
                v-if="post.likes !== undefined"
                class="like-button"
                :class="{ 'liked': post.is_liked, 'like-loading': isPostLikeLoading(post.id) }"
                @click="toggleLike(post)"
                :title="post.is_liked ? $t('posts.unlike') : $t('posts.like')"
              >
                <i class="fa" :class="post.is_liked ? 'fa-heart' : 'fa-heart-o'"></i>
                <span>{{ post.likes }}</span>
              </button>

              <button
                class="favorite-button"
                :class="{ 'favorited': post.is_favorited, 'favorite-loading': isPostFavoriteLoading(post.id) }"
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

      <div v-if="loading && hasMore" class="loading-more">
        <el-skeleton :rows="1" animated />
      </div>

      <div v-if="hasMore && !loading" class="load-more">
        <button @click="loadMorePosts" class="load-more-btn">{{ $t('posts.loadMore') }}</button>
      </div>

      <div v-if="!hasMore && posts.length > 0" class="no-more-posts">
        {{ $t('posts.noMorePosts') }}
      </div>
    </template>
  </div>
</template>

<script>
import { getNewPostList } from '@/api/post';
import { likePost, unlikePost } from '@/api/like';
import { addFavorite, cancelFavorite } from '@/api/favorite';
import { useI18n } from 'vue-i18n';
import defaultThumbnail from '@/assets/images/default.jpeg';
import { ref, computed, onMounted, watch } from 'vue';
import { formatDate } from '@/utils/date';
import { useUserStore } from '@/stores/user';
import { ElMessage } from 'element-plus';
import { getCurrentVisitorId } from '@/utils/visitorId.js';

export default {
  name: 'LatestPosts',
  setup() {
    const { t } = useI18n();
    const userStore = useUserStore();

    const posts = ref([]);
    const loading = ref(false);
    const error = ref(false);
    const page = ref(1);
    const pageSize = ref(10);
    const hasMore = ref(true);
    // Check if user is logged in by checking if token exists and userInfo is not null
    const isLoggedIn = computed(() => {
      const hasToken = !!userStore.token;
      const hasUserInfo = !!userStore.userInfo;
      return hasToken && hasUserInfo;
    });

    // 创建一个加载状态管理对象
    const likeLoadingMap = ref({});

    // 创建一个方法来检查文章是否正在加载点赞状态
    const isPostLikeLoading = (postId) => {
      return !!likeLoadingMap.value[postId];
    };

    const stickyPosts = computed(() => {
      return posts.value.filter(post => post.is_sticky);
    });

    const regularPosts = computed(() => {
      return posts.value.filter(post => !post.is_sticky);
    });

    const fetchPosts = async (isLoadMore = false) => {
      if (loading.value) return;

      loading.value = true;
      error.value = false;

      try {
        const response = await getNewPostList({
          page: page.value,
          limit: pageSize.value,
          status: 1,
          is_check_like: 1,
          is_check_favorite: 1
        });

        if (response && response.code === 200) {
          const newPosts = response.data.list || [];

          if (isLoadMore) {
            posts.value = [...posts.value, ...newPosts];
          } else {
            posts.value = newPosts;
          }


          // Check if there are more posts to load
          hasMore.value = newPosts.length >= pageSize.value;

        } else {
          console.error('Failed to fetch posts:', response?.msg || 'Unknown error');
          error.value = true;
        }
      } catch (err) {
        console.error('Error fetching posts:', err);
        error.value = true;
      } finally {
        loading.value = false;
      }
    };


    const loadMorePosts = async () => {
      page.value++;
      await fetchPosts(true);
    };

    // 收藏加载状态映射
    const favoriteLoadingMap = ref({});

    // 检查文章是否正在加载收藏状态
    const isPostFavoriteLoading = (postId) => {
      return favoriteLoadingMap.value[postId] === true;
    };

    //点赞和取消点赞
    const toggleLike = (post) => {
      // 确保文章对象有ID
      if (!post || !post.id) {
        console.error('无效的文章对象:', post);
        return;
      }

      // 设置加载状态
      likeLoadingMap.value[post.id] = true;

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
            ElMessage.success(post.is_liked ? t('posts.likeSuccess') : t('posts.unlikeSuccess'));
          } else {
            // 显示错误消息
            ElMessage.error(response?.msg || t('posts.likeError'));
          }
        })
        .catch(error => {
          console.error('点赞操作失败:', error);
          ElMessage.error(t('posts.likeError'));
        })
        .finally(() => {
          // 清除加载状态
          delete likeLoadingMap.value[post.id];
        });

      // 添加错误处理，确保加载状态被清除
      setTimeout(() => {
        if (likeLoadingMap.value[post.id]) {
          delete likeLoadingMap.value[post.id];
        }
      }, 5000); // 5秒后自动清除，防止卡死
    };

    // 切换收藏状态
    const toggleFavorite = (post) => {
      // 检查用户是否登录
      if (!isLoggedIn.value) {
        ElMessage.warning(t('posts.loginToFavorite'));
        return;
      }

      // 如果正在加载中，不处理
      if (favoriteLoadingMap.value[post.id]) {
        return;
      }

      // 设置加载状态
      favoriteLoadingMap.value[post.id] = true;

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
            ElMessage.success(post.is_favorited ? t('posts.favoriteSuccess') : t('posts.unfavoriteSuccess'));
          } else {
            // 显示错误消息
            ElMessage.error(response?.msg || t('posts.favoriteError'));
          }
        })
        .catch(error => {
          console.error('收藏操作失败:', error);
          ElMessage.error(t('posts.favoriteError'));
        })
        .finally(() => {
          // 清除加载状态
          delete favoriteLoadingMap.value[post.id];
        });

      // 添加错误处理，确保加载状态被清除
      setTimeout(() => {
        if (favoriteLoadingMap.value[post.id]) {
          delete favoriteLoadingMap.value[post.id];
        }
      }, 5000); // 5秒后自动清除，防止卡死
    };

    onMounted(() => {
      fetchPosts();
    });

    return {
      posts,
      stickyPosts,
      regularPosts,
      loading,
      error,
      hasMore,
      fetchPosts,
      loadMorePosts,
      toggleLike,
      toggleFavorite,
      defaultThumbnail,
      formatDate,
      isLoggedIn,
      isPostLikeLoading,
      isPostFavoriteLoading
    };
  }
}
</script>

<style scoped>
.latest-posts {
  margin-bottom: 20px;
}

.section-title {
  font-size: 22px;
  margin: 0 0 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #eee;
  color: #333;
  font-weight: bold;
  position: relative;
  text-align: left;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.section-title:after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 50px;
  height: 2px;
  background-color: #09c;
}

.loading-container, .error-container {
  background-color: #f5f5f5;
  border-radius: 3px;
  padding: 20px;
  text-align: center;
  margin-bottom: 20px;
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.error-message {
  color: #f56c6c;
  margin-bottom: 10px;
}

.loading-more {
  padding: 20px;
  background-color: #fff;
  border-radius: 3px;
  margin-bottom: 20px;
}

.no-more-posts {
  text-align: center;
  color: #999;
  padding: 20px;
  font-size: 14px;
}

.post {
  background-color: #fff;
  border-radius: 3px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
  text-align: left;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.post-sticky {
  border-left: 4px solid #09c;
}

.post-sticky-badge {
  display: none; /* Hide the original sticky badge */
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

.post-title {
  margin: 0 0 10px;
  font-size: 20px;
  font-weight: bold;
  text-align: left;
  line-height: 1.4;
  transition: color 0.3s ease;
}

.post-title a {
  color: #333;
  text-decoration: none;
  transition: color 0.3s;
  text-align: left;
}

.post-title a:hover {
  color: #09c;
}

.post-content {
  margin-bottom: 15px;
  font-size: 16px;
  color: #666;
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
  color: #999;
  text-align: left;
  gap: 12px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed #eee;
  line-height: 1;
}

.post-meta span, .post-meta a {
  position: relative;
  display: inline-flex;
  align-items: center;
  height: 28%; /* 与按钮高度保持一致 */
}

.post-meta a {
  color: #09c;
  text-decoration: none;
}

.post-meta a:hover {
  color: #07a;
}

.post-like, .post-author, .post-date, .post-category, .post-views, .post-comments {
  white-space: nowrap;
}

.post-like i {
  margin-right: 5px;
  color: #e74c3c;
}

.post-meta i {
  margin-right: 5px;
}

.post-comments {
  color: #09c;
  text-decoration: none;
}

.post-comments:hover {
  text-decoration: underline;
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
}

.load-more-btn:hover {
  background-color: #007fad;
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

.post-sticky .post-content-wrap {
  padding-left: 0;
}

.post-author {
  display: flex;
  align-items: center;
}

.author-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  margin-right: 5px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.post-like-container {
  display: none; /* Hide the container since we moved the button */
}

.like-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  border: 1px solid rgba(231, 76, 60, 0.3);
  border-radius: 20px;
  padding: 0.25rem 0.6rem;
  height: 28px; /* 固定高度确保一致性 */
  min-width: 36px; /* 最小宽度 */
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.8rem;
  line-height: 1;
  position: relative;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.like-button:hover {
  background-color: #fef5f5;
  border-color: #e74c3c;
  box-shadow: 0 2px 5px rgba(231, 76, 60, 0.2);
  transform: translateY(-1px);
}

.like-button i {
  margin-right: 0.25rem;
  color: #e74c3c;
  font-size: 1em;
  line-height: 1;
  transition: transform 0.3s, color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 当没有数字时的样式 */
.like-button i:only-child {
  margin-right: 0;
}

.like-button span {
  color: #e74c3c;
  font-weight: 500;
  line-height: 1;
  transition: color 0.3s;
  display: inline-block;
  vertical-align: middle;
}

.like-button.liked {
  background-color: #fef2f2;
  border-color: #e74c3c;
  box-shadow: 0 2px 5px rgba(231, 76, 60, 0.3);
}

.like-button.liked i {
  color: #e74c3c;
  transform: scale(1.2);
  text-shadow: 0 0 5px rgba(231, 76, 60, 0.5);
}

.like-button.liked:hover {
  background-color: #fde8e8;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(231, 76, 60, 0.4);
}

.like-button.like-loading {
  opacity: 0.7;
  cursor: wait;
}

.like-button.like-loading i {
  animation: pulse 1s infinite;
}

/* 确保内容垂直居中 */
.like-button,
.like-button i,
.like-button span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

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

/* 收藏按钮样式 */
.favorite-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  border: 1px solid rgba(255, 193, 7, 0.3);
  border-radius: 20px; /* 圆角按钮 */
  padding: 0.25rem 0.6rem;
  height: 28px; /* 固定高度确保一致性 */
  min-width: 36px; /* 最小宽度 */
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

/* 当没有数字时的样式 */
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

/* 确保内容垂直居中 */
.favorite-button,
.favorite-button i,
.favorite-button span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* 点击效果 */
@keyframes star-click {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.5);
  }
  100% {
    transform: scale(1);
  }
}

.favorite-button:active i {
  animation: star-click 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* 收藏动画 */
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

.post-author, .post-category-container, .post-tags-container {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  background-color: transparent;
  border-radius: 0;
  padding: 0;
}

.post-author:hover, .post-category-container:hover, .post-tags-container:hover {
  background-color: transparent;
}

.category-tag {
  display: inline-block;
  background-color: #f5f7fa;
  border-radius: 15px;
  padding: 2px 8px;
  margin: 2px;
  font-size: 0.9em;
  color: #666;
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

.post-author i {
  color: #9b59b6;
}

.post-date i {
  color: #27ae60;
}

.post-views i {
  color: #f39c12;
}

/* 暗黑模式和系统模式下的样式 */
html.dark-theme .section-title,
html.dark .section-title,
html.system-dark-theme .section-title,
html.dark.system-theme .section-title {
  color: #ffffff !important;
  border-bottom-color: rgba(255, 255, 255, 0.1) !important;
  text-align: left !important;
}

html.dark-theme .post-title a,
html.dark .post-title a,
html.system-dark-theme .post-title a,
html.dark.system-theme .post-title a {
  color: #ffffff !important;
  text-align: left !important;
}

html.dark-theme .post-content,
html.dark .post-content,
html.system-dark-theme .post-content,
html.dark.system-theme .post-content {
  color: #e0e0e0 !important;
  text-align: left !important;
}

html.dark-theme .post-content p,
html.dark .post-content p,
html.system-dark-theme .post-content p,
html.dark.system-theme .post-content p {
  color: #e0e0e0 !important;
}

html.dark-theme .post,
html.dark .post,
html.system-dark-theme .post,
html.dark.system-theme .post {
  background-color: var(--card-bg-color, #232324) !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

html.dark-theme .post-meta,
html.dark .post-meta,
html.system-dark-theme .post-meta,
html.dark.system-theme .post-meta {
  color: #b8b8b8 !important;
  border-top-color: rgba(255, 255, 255, 0.1) !important;
}

html.dark-theme .post-meta span,
html.dark .post-meta span,
html.system-dark-theme .post-meta span,
html.dark.system-theme .post-meta span {
  color: #b8b8b8 !important;
}

html.dark-theme .category-tag,
html.dark .category-tag,
html.system-dark-theme .category-tag,
html.dark.system-theme .category-tag {
  background-color: rgba(255, 255, 255, 0.1) !important;
  color: #ffffff !important;
}

html.dark-theme .category-tag:hover,
html.dark .category-tag:hover,
html.system-dark-theme .category-tag:hover,
html.dark.system-theme .category-tag:hover {
  background-color: rgba(255, 255, 255, 0.2) !important;
}

html.dark-theme .tag-item,
html.dark .tag-item,
html.system-dark-theme .tag-item,
html.dark.system-theme .tag-item {
  background-color: rgba(255, 255, 255, 0.08) !important;
}

html.dark-theme .tag-item:hover,
html.dark .tag-item:hover,
html.system-dark-theme .tag-item:hover,
html.dark.system-theme .tag-item:hover {
  background-color: rgba(255, 255, 255, 0.15) !important;
}

html.dark-theme .no-more-posts,
html.dark .no-more-posts,
html.system-dark-theme .no-more-posts,
html.dark.system-theme .no-more-posts {
  color: #b8b8b8 !important;
}

html.dark-theme .loading-container,
html.dark .loading-container,
html.system-dark-theme .loading-container,
html.dark.system-theme .loading-container,
html.dark-theme .error-container,
html.dark .error-container,
html.system-dark-theme .error-container,
html.dark.system-theme .error-container {
  background-color: var(--card-bg-color, #232324) !important;
}

html.dark-theme .loading-more,
html.dark .loading-more,
html.system-dark-theme .loading-more,
html.dark.system-theme .loading-more {
  background-color: var(--card-bg-color, #232324) !important;
}

@media (max-width: 768px) {
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
    margin-bottom: 0;
    width: auto;
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

  .post-like-container {
    position: static;
    margin-top: 5px;
    text-align: right;
  }
}
</style>