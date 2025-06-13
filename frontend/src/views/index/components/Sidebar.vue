<!-- 侧边栏 -->
<template>
  <div class="sidebar">

    <div class="widget widget-sticky">
      <h3 class="widget-title">{{ $t('sidebar.widgets.topPosts.title') }}</h3>
      <div class="widget-content">
        <ul class="post-list" v-if="topPosts.length > 0">
          <li v-for="post in topPosts" :key="post.id" class="sidebar-top-post-item">
            <router-link :to="`/post/${post.id}`" class="sidebar-top-post-link" :title="post.title">
              <div class="sidebar-top-img-container">
                <img :src="post.thumbnail || defaultThumbnail" :alt="post.title" class="sidebar-top-post-img">
              </div>
              <div class="sidebar-top-post-content">
                <div class="sidebar-top-post-title">{{ post.title }}</div>
                <div class="sidebar-top-post-date">{{ getRelativeTime(post.create_time) }}</div>
              </div>
            </router-link>
          </li>
        </ul>
        <div v-else-if="loading" class="loading-container">
          <el-skeleton :rows="4" animated />
        </div>
        <div v-else-if="loadError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.topPosts.error') }}</p>
          <el-button type="primary" size="small" @click="fetchTopPosts">{{ $t('sidebar.widgets.topPosts.retry') }}</el-button>
        </div>
        <div v-else class="empty-container">
          <p class="empty-message">{{ $t('sidebar.widgets.topPosts.empty') }}</p>
        </div>
      </div>
    </div>

    <div class="widget widget-topics-alt">
      <h3 class="widget-title">{{ $t('sidebar.widgets.mostLikedPosts.title') }}</h3>
      <div class="widget-content">
        <div class="topic-list-alt" v-if="mostLikedPosts.length > 0">
          <router-link
            v-for="post in mostLikedPosts"
            :key="post.id"
            :to="`/post/${post.id}`"
            class="sidebar-liked-post-item"
            :title="post.title + ' - ' + $t('sidebar.widgets.mostLikedPosts.likes', { count: post.likes || 0 })"
          >
            <div class="sidebar-liked-img-container">
              <img :src="post.thumbnail || defaultThumbnail" :alt="post.title" class="sidebar-liked-post-img">
            </div>
            <div class="sidebar-liked-post-info">
              <h4 class="sidebar-liked-post-title">{{ post.title }}</h4>
              <span class="sidebar-liked-post-count">{{ post.likes || 0 }}</span>
            </div>
          </router-link>
        </div>
        <div v-else-if="likedLoading" class="loading-container">
          <el-skeleton :rows="4" animated />
        </div>
        <div v-else-if="likedError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.mostLikedPosts.error') }}</p>
          <el-button type="primary" size="small" @click="fetchMostLikedPosts">{{ $t('sidebar.widgets.mostLikedPosts.retry') }}</el-button>
        </div>
        <div v-else class="empty-container">
          <p class="empty-message">{{ $t('sidebar.widgets.mostLikedPosts.empty') }}</p>
        </div>
      </div>
    </div>

    <div class="widget widget-random">
      <h3 class="widget-title">{{ $t('sidebar.widgets.randomPosts.title') }}</h3>
      <div class="widget-content">
        <ul class="post-list" v-if="randomPosts.length > 0">
          <li v-for="post in randomPosts" :key="post.id" class="sidebar-random-post-item">
            <div class="sidebar-random-img-container">
              <router-link :to="`/post/${post.id}`">
                <img :src="post.thumbnail || defaultThumbnail" :alt="post.title" class="sidebar-random-post-img">
              </router-link>
            </div>
            <div class="sidebar-random-post-info">
              <router-link :to="`/post/${post.id}`" class="sidebar-random-post-title">{{ post.title }}</router-link>
              <span class="sidebar-random-post-date">{{ getRelativeTime(post.create_time) }}</span>
            </div>
          </li>
        </ul>
        <div v-else-if="randomLoading" class="loading-container">
          <el-skeleton :rows="5" animated />
        </div>
        <div v-else-if="randomError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.randomPosts.error') }}</p>
          <el-button type="primary" size="small" @click="fetchRandomPosts">{{ $t('sidebar.widgets.randomPosts.retry') }}</el-button>
        </div>
        <div v-else class="empty-container">
          <p class="empty-message">{{ $t('sidebar.widgets.randomPosts.empty') }}</p>
        </div>
      </div>
    </div>

    <div class="widget widget-ad">
      <div class="widget-content">
        <div class="ad-placeholder">
          {{ $t('sidebar.widgets.ad.placeholder', 'Advertisement placeholder') }}
        </div>
      </div>
    </div>

    <div class="widget widget-tags">
      <h3 class="widget-title">{{ $t('sidebar.widgets.tags.title') }}</h3>
      <div class="widget-content">
        <div class="tag-cloud" v-if="hotTags.length > 0">
          <router-link
            v-for="tag in hotTags"
            :key="tag.id"
            :to="`/tag/${tag.id}`"
            class="tag-item"
            :title="tag.name + (tag.count ? ' (' + tag.count + ')' : '')"
          >
            {{ tag.name }} <span v-if="tag.count" class="tag-count">({{ tag.count }})</span>
          </router-link>
        </div>
        <div v-else-if="tagsLoading" class="loading-container">
          <el-skeleton :rows="3" animated />
        </div>
        <div v-else-if="tagsError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.tags.error') }}</p>
          <el-button type="primary" size="small" @click="fetchHotTags">{{ $t('sidebar.widgets.tags.retry') }}</el-button>
        </div>
        <div v-else class="empty-container">
          <p class="empty-message">{{ $t('sidebar.widgets.tags.empty') }}</p>
        </div>
      </div>
    </div>

    <div class="widget widget_ui_comments">
      <h3 class="widget-title">{{ $t('sidebar.widgets.latestComments.title') }}</h3>
      <div class="widget-content">
        <ul class="ui-comments-list" v-if="latestComments.length > 0">
          <li class="ui-comment-item" v-for="comment in latestComments" :key="comment.id">
            <a :href="`/post/${comment.post_id}#comment-${comment.id}`" class="sidebar-comment-avatar-container" :title="comment.username">
              <img :src="comment.avatar || defaultThumbnail" :alt="comment.username" class="sidebar-comment-avatar-img">
            </a>
            <div class="ui-comment-body">
              <div class="ui-comment-meta">
                <a :href="`/post/${comment.post_id}`" class="ui-comment-author" :title="$t('sidebar.widgets.latestComments.viewPost')">{{ comment.username }}</a>
                <span class="ui-comment-posttime">{{ getRelativeTime(comment.create_time) }}</span>
              </div>
              <div class="ui-comment-excerpt">
                <a :href="`/post/${comment.post_id}#comment-${comment.id}`" :title="comment.content">{{ comment.content }}</a>
              </div>
            </div>
          </li>
        </ul>
        <div v-else-if="commentsLoading" class="loading-container">
          <el-skeleton :rows="4" animated />
        </div>
        <div v-else-if="commentsError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.latestComments.error') }}</p>
          <el-button type="primary" size="small" @click="getLatestComments">{{ $t('sidebar.widgets.latestComments.retry') }}</el-button>
        </div>
        <div v-else class="empty-container">
          <p class="empty-message">{{ $t('sidebar.widgets.latestComments.empty') }}</p>
        </div>
      </div>
    </div>

    <div class="widget widget-stats">
      <h3 class="widget-title">{{ $t('sidebar.widgets.statistics.title') }}</h3>
      <div class="widget-content">
        <ul class="stats-list" v-if="statistics && !statsLoading && !statsError">
          <li class="stats-item">
            <i class="el-icon stats-icon"><Document /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.posts') }}</span>
            <span class="stats-value">{{ statistics.total_posts || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><ChatDotRound /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.comments') }}</span>
            <span class="stats-value">{{ statistics.total_comments || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><PriceTag /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.tags') }}</span>
            <span class="stats-value">{{ statistics.total_tags || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><Files /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.pages') }}</span>
            <span class="stats-value">{{ statistics.total_index_files || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><Collection /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.categories') }}</span>
            <span class="stats-value">{{ statistics.total_categories || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><Link /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.links') }}</span>
            <span class="stats-value">{{ statistics.total_links || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><User /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.users') }}</span>
            <span class="stats-value">{{ statistics.total_users || 0 }}</span>
          </li>
          <li class="stats-item">
            <i class="el-icon stats-icon"><Clock /></i>
            <span class="stats-label">{{ $t('sidebar.widgets.statistics.lastUpdated') }}</span>
            <span class="stats-value">{{ formatDate(new Date(), 'YYYY-MM-DD') }}</span>
          </li>
        </ul>
        <div v-else-if="statsLoading" class="loading-container">
          <el-skeleton :rows="8" animated />
        </div>
        <div v-else-if="statsError" class="error-container">
          <p class="error-message">{{ $t('sidebar.widgets.statistics.error', 'Failed to load statistics') }}</p>
          <el-button type="primary" size="small" @click="fetchWebStatistics">{{ $t('sidebar.widgets.statistics.retry', 'Retry') }}</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  Document,
  ChatDotRound,
  PriceTag,
  Files,
  Collection,
  Link,
  User,
  Clock,
  UserFilled
} from '@element-plus/icons-vue'
import axios from 'axios'
import { formatDate, getRelativeTime } from '@/utils/date'
import { getMoreLikePostList, getTopRecommendPostList, getRandomRecommendPostList} from '@/api/post'
import { getHotTagList } from '@/api/tag'
import defaultThumbnail from '@/assets/images/default.jpeg'
import { getNewCommentList } from '@/api/comment'
import { getWebStatistics } from '@/api/webStatistics'

export default {
  name: 'Sidebar',
  components: {
    Document,
    ChatDotRound,
    PriceTag,
    Files,
    Collection,
    Link,
    User,
    Clock,
    UserFilled
  },
  data() {
    return {
      topPosts: [],
      loading: false,
      loadError: false,
      defaultThumbnail: defaultThumbnail,
      mostLikedPosts: [],
      likedLoading: false,
      likedError: false,
      randomPosts: [],
      randomLoading: false,
      randomError: false,
      hotTags: [],
      tagsLoading: false,
      tagsError: false,
      latestComments: [],
      commentsLoading: false,
      commentsError: false,
      statistics: null,
      statsLoading: false,
      statsError: false
    }
  },
  mounted() {
    this.fetchTopPosts()
    this.fetchMostLikedPosts()
    this.fetchRandomPosts()
    this.fetchHotTags()
    this.getLatestComments()
    this.fetchWebStatistics()
  },
  methods: {
    async fetchTopPosts() {
      this.loading = true
      this.loadError = false
      try {
        // 请求置顶推荐文章，获取最新的4个置顶推且荐文章
        const response = await getTopRecommendPostList();

        if (response && response.code === 200) {
          this.topPosts = response.data.list || []
        } else {
          console.error('获取置顶文章失败:', response.msg || '未知错误')
          this.loadError = true
        }
      } catch (error) {
        console.error('获取置顶文章出错:', error)
        this.loadError = true
      } finally {
        this.loading = false
      }
    },
    async fetchMostLikedPosts() {
      this.likedLoading = true
      this.likedError = false
      try {
        // 使用专门的API函数获取点赞最多的文章 6个
        const response = await getMoreLikePostList()

        if (response && response.code === 200) {
          this.mostLikedPosts = response.data.list || []
        } else {
          console.error('获取点赞最多文章失败:', response.msg || '未知错误')
          this.likedError = true
        }
      } catch (error) {
        console.error('获取点赞最多文章出错:', error)
        this.likedError = true
      } finally {
        this.likedLoading = false
      }
    },
    async fetchRandomPosts() {
      this.randomLoading = true
      this.randomError = false
      try {
        // 使用专门的API函数获取随机推荐的6个文章
        const response = await getRandomRecommendPostList()

        if (response && response.code === 200) {
          this.randomPosts = response.data.list || []
        } else {
          console.error('获取随机推荐文章失败:', response.msg || '未知错误')
          this.randomError = true
        }
      } catch (error) {
        console.error('获取随机推荐文章出错:', error)
        this.randomError = true
      } finally {
        this.randomLoading = false
      }
    },
    async fetchHotTags() {
      this.tagsLoading = true
      this.tagsError = false
      try {
        // 使用专门的API函数获取热门标签
        const response = await getHotTagList({ limit: 100,page:1,status:1 })
        if (response && response.code === 200) {
          this.hotTags = response.data || []
        } else {
          console.error('获取热门标签失败:', response.msg || '未知错误')
          this.tagsError = true
        }
      } catch (error) {
        console.error('获取热门标签出错:', error)
        this.tagsError = true
      } finally {
        this.tagsLoading = false
      }
    },
    async getLatestComments() {
      this.commentsLoading = true
      this.commentsError = false
      try {
        const response = await getNewCommentList({ limit: 5 })
        this.latestComments = response.data || []
      } catch (error) {
        console.error('Error fetching latest comments:', error)
        this.commentsError = true
      } finally {
        this.commentsLoading = false
      }
    },
    async fetchWebStatistics() {
      this.statsLoading = true
      this.statsError = false
      try {
        const response = await getWebStatistics()
        if (response && response.code === 200) {
          this.statistics = response.data
        } else {
          console.error('获取网站统计信息失败:', response?.msg || '未知错误')
          this.statsError = true
        }
      } catch (error) {
        console.error('获取网站统计信息出错:', error)
        this.statsError = true
      } finally {
        this.statsLoading = false
      }
    },
    truncateText(text, length) {
      // ... existing code ...

    },
    // 添加日期格式化函数到methods中，使其在模板中可用
    formatDate(date, format) {
      return formatDate(date, format)
    },
    getRelativeTime(date) {
      return getRelativeTime(date)
    }
  }
}
</script>

<style scoped>
.sidebar {
  margin-bottom: 20px;
}

.widget-topics, .widget-topics-alt {
  margin-bottom: 20px;
}

.widget-title {
  padding: 15px;
  margin: 0;
  font-size: 16px;
  border-bottom: 1px solid var(--border-color, #eee);
  color: var(--heading-color, #333);
  font-weight: bold;
  position: relative;
  text-align: left;
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

.post-list {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}

.post-list li {
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px dashed var(--border-color, #eee);
  text-align: left;
}

.post-list li:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.post-list a {
  display: block;
  color: var(--text-color, #666);
  text-decoration: none;
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 5px;
  text-align: left;
}

.post-list a:hover {
  color: #09c;
}

.post-date {
  color: var(--light-text-color, #999);
  font-size: 12px;
}

.ad-placeholder {
  background-color: var(--bg-color-light, #f3f3f3);
  padding: 15px;
  border: 1px dashed var(--border-color, #ddd);
  text-align: center;
  font-style: italic;
  color: var(--light-text-color, #999);
}

.tag-cloud {
  display: flex;
  flex-wrap: wrap;
}

.tag-item {
  display: inline-block;
  background-color: var(--bg-color-light, #f3f3f3);
  color: var(--text-color, #666);
  padding: 5px 10px;
  margin: 0 5px 5px 0;
  border-radius: 3px;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.tag-item:hover {
  background-color: #09c;
  color: #fff;
}

.tag-count {
  font-size: 12px;
  color: var(--light-text-color, #999);
  margin-left: 2px;
  transition: color 0.3s ease;
}

.tag-item:hover .tag-count {
  color: rgba(255, 255, 255, 0.8);
}

.stats-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.stats-list li {
  margin-bottom: 8px;
  font-size: 13px;
  color: var(--text-color, #666);
  transition: color 0.3s ease;
}

.stats-list strong {
  color: var(--heading-color, #333);
  transition: color 0.3s ease;
}

@media (max-width: 768px) {
  .topic-item, .topic-item-alt {
    width: 100%;
  }
}

.widget-stats .stats-list {
  padding: 0;
  margin: 0;
  list-style: none;
}

.widget-stats .stats-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px dashed var(--border-color-lighter, rgba(0, 0, 0, 0.07));
  color: var(--text-color, #666);
  font-size: 14px;
}

.widget-stats .stats-item:last-child {
  border-bottom: none;
}

.widget-stats .stats-icon {
  margin-right: 10px;
  font-size: 16px;
  color: var(--primary-color, #09c);
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.widget-stats .stats-label {
  font-weight: 500;
  margin-right: 5px;
}

.widget-stats .stats-value {
  margin-left: auto;
  font-weight: 600;
  color: var(--heading-color, #333);
}

@media (max-width: 768px) {
  .widget-stats .stats-item {
    padding: 8px 0;
    font-size: 13px;
  }

  .widget-stats .stats-icon {
    font-size: 14px;
    width: 18px;
    height: 18px;
  }
}

.widget_ui_comments .ui-comments-list {
  padding: 0;
  margin: 0;
  list-style: none;
}

.widget_ui_comments .ui-comment-item {
  display: flex;
  padding: 15px 0;
  border-bottom: 1px solid var(--border-color-lighter, rgba(0, 0, 0, 0.05));
}

.widget_ui_comments .ui-comment-item:last-child {
  border-bottom: none;
}

.widget_ui_comments .ui-comment-body {
  flex: 1;
  line-height: 1.5;
  text-align: left;
}

.widget_ui_comments .ui-comment-meta {
  margin-bottom: 3px;
  font-size: 12px;
  color: var(--light-text-color, #999);
  text-align: left;
  display: flex;
  align-items: center;
}

.widget_ui_comments .ui-comment-author {
  margin-right: 10px;
  font-weight: bold;
  color: #09c;
  text-decoration: none;
}

.widget_ui_comments .ui-comment-author:hover {
  color: #07a;
}

.widget_ui_comments .ui-comment-posttime {
  color: var(--light-text-color, #bbb);
}

.widget_ui_comments .ui-comment-excerpt {
  font-size: 13px;
  color: var(--text-color, #777);
  text-align: left;
}

.widget_ui_comments .ui-comment-excerpt a {
  color: var(--text-color, #777);
  text-decoration: none;
}

.widget_ui_comments .ui-comment-excerpt a:hover {
  color: #09c;
}

@media (max-width: 768px) {
  .widget_ui_comments .ui-comment-item {
    padding: 10px 0;
  }
}

.loading-container {
  padding: 15px;
  min-height: 150px;
}

.error-container {
  padding: 20px;
  text-align: center;
  min-height: 150px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.error-message {
  color: var(--danger-color, #f56c6c);
  margin-bottom: 15px;
  font-size: 14px;
}

.empty-container {
  padding: 20px;
  text-align: center;
  min-height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-message {
  color: var(--light-text-color, #909399);
  font-size: 14px;
}

.sidebar-top-post-item {
  position: relative;
  overflow: hidden;
  padding: 8px 0;
}

.sidebar-top-post-link {
  display: flex !important;
  align-items: flex-start;
  text-decoration: none;
}

.sidebar-top-img-container {
  flex: 0 0 70px;
  margin-right: 10px;
  overflow: hidden;
  border-radius: 3px;
}

.sidebar-top-post-img {
  width: 70px;
  height: 50px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.sidebar-top-post-link:hover .sidebar-top-post-img {
  transform: scale(1.08);
}

.sidebar-top-post-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.sidebar-top-post-title {
  font-size: 14px;
  color: var(--text-color, #666);
  line-height: 1.4;
  margin-bottom: 5px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.sidebar-top-post-link:hover .sidebar-top-post-title {
  color: #09c;
}

.sidebar-top-post-date {
  color: var(--light-text-color, #999);
  font-size: 12px;
}

.topic-list-alt {
  display: flex;
  flex-wrap: wrap;
  padding: 15px 10px 5px;
  margin: 0 -5px;
}

.topic-item-alt {
  width: 50%;
  padding: 0 5px 15px;
  text-decoration: none;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

.topic-item-alt:hover {
  transform: translateY(-3px);
}

.topic-image-wrap {
  margin-bottom: 8px;
  overflow: hidden;
  border-radius: 3px;
  position: relative;
}

.topic-item-alt .topic-image {
  width: 100%;
  height: auto;
  margin-right: 0;
  aspect-ratio: 16/9;
  object-fit: cover;
  transition: transform 0.3s ease;
  display: block;
}

.topic-item-alt:hover .topic-image {
  transform: scale(1.05);
}

.topic-item-alt .topic-info {
  position: relative;
  text-align: left;
}

.topic-item-alt .topic-title {
  text-align: left;
  font-size: 13px;
  font-weight: 500;
  color: #333;
  margin: 0;
  padding: 0;
  display: block;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  max-height: 2.8em;
  line-height: 1.4;
}

.topic-item-alt .topic-count {
  position: absolute;
  top: -26px;
  right: 3px;
  background-color: rgba(0,0,0,.6);
  color: #fff;
  font-size: 12px;
  padding: 1px 6px;
  border-radius: 3px;
  line-height: 1.3;
}

.sidebar-liked-post-item {
  width: 50%;
  padding: 0 5px 15px;
  text-decoration: none;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

.sidebar-liked-post-item:hover {
  transform: translateY(-3px);
}

.sidebar-liked-img-container {
  margin-bottom: 8px;
  overflow: hidden;
  border-radius: 3px;
  position: relative;
}

.sidebar-liked-post-img {
  width: 100%;
  height: auto;
  margin-right: 0;
  aspect-ratio: 16/9;
  object-fit: cover;
  transition: transform 0.3s ease;
  display: block;
}

.sidebar-liked-post-item:hover .sidebar-liked-post-img {
  transform: scale(1.05);
}

.sidebar-liked-post-info {
  position: relative;
  text-align: left;
}

.sidebar-liked-post-title {
  text-align: left;
  font-size: 13px;
  font-weight: 500;
  color: var(--heading-color, #333);
  margin: 0;
  padding: 0;
  display: block;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  max-height: 2.8em;
  line-height: 1.4;
}

.sidebar-liked-post-count {
  position: absolute;
  top: -26px;
  right: 3px;
  background-color: rgba(0,0,0,.6);
  color: #fff;
  font-size: 12px;
  padding: 1px 6px;
  border-radius: 3px;
  line-height: 1.3;
}

.sidebar-random-post-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 10px;
}

.sidebar-random-img-container {
  flex: 0 0 70px;
  margin-right: 10px;
  overflow: hidden;
  border-radius: 3px;
  position: relative;
}

.sidebar-random-img-container a {
  display: block;
  overflow: hidden;
  border-radius: 3px;
}

.sidebar-random-post-img {
  width: 70px;
  height: 50px;
  object-fit: cover;
  display: block;
  transition: transform 0.3s;
  border-radius: 3px;
}

.sidebar-random-img-container:hover .sidebar-random-post-img {
  transform: scale(1.08);
}

.sidebar-random-post-info {
  flex: 1;
  min-width: 0;
}

.sidebar-random-post-title {
  margin: 0 0 5px;
  font-size: 13px;
  font-weight: normal;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  max-height: 2.8em;
  color: var(--text-color, #666);
  text-decoration: none;
}

.sidebar-random-post-title:hover {
  color: #09c;
}

.sidebar-random-post-date {
  display: block;
  font-size: 12px;
  color: var(--light-text-color, #999);
}

.sidebar-comment-avatar-container {
  flex: 0 0 40px;
  margin-right: 10px;
  overflow: hidden;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  background-color: #f0f0f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  position: relative;
}

.sidebar-comment-avatar-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  transition: transform 0.3s ease;
}

.sidebar-comment-avatar-container:hover .sidebar-comment-avatar-img {
  transform: scale(1.1);
}
/* Dark mode and system-following mode specific styles */
html.dark-theme .widget-title,
html.dark .widget-title,
html.system-dark-theme .widget-title,
html.dark.system-theme .widget-title {
  color: #ffffff;
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .sidebar-top-post-title,
html.dark .sidebar-top-post-title,
html.system-dark-theme .sidebar-top-post-title,
html.dark.system-theme .sidebar-top-post-title,
html.dark-theme .sidebar-liked-post-title,
html.dark .sidebar-liked-post-title,
html.system-dark-theme .sidebar-liked-post-title,
html.dark.system-theme .sidebar-liked-post-title,
html.dark-theme .sidebar-random-post-title,
html.dark .sidebar-random-post-title,
html.system-dark-theme .sidebar-random-post-title,
html.dark.system-theme .sidebar-random-post-title,
html.dark-theme .post-list a,
html.dark .post-list a,
html.system-dark-theme .post-list a,
html.dark.system-theme .post-list a,
html.dark-theme .widget_ui_comments .ui-comment-excerpt a,
html.dark .widget_ui_comments .ui-comment-excerpt a,
html.system-dark-theme .widget_ui_comments .ui-comment-excerpt a,
html.dark.system-theme .widget_ui_comments .ui-comment-excerpt a,
html.dark-theme .widget_ui_comments .ui-comment-excerpt,
html.dark .widget_ui_comments .ui-comment-excerpt,
html.system-dark-theme .widget_ui_comments .ui-comment-excerpt,
html.dark.system-theme .widget_ui_comments .ui-comment-excerpt,
html.dark-theme .stats-list li,
html.dark .stats-list li,
html.system-dark-theme .stats-list li,
html.dark.system-theme .stats-list li {
  color: #ffffff;
}

html.dark-theme .tag-item,
html.dark .tag-item,
html.system-dark-theme .tag-item,
html.dark.system-theme .tag-item {
  background-color: rgba(255, 255, 255, 0.1);
  color: #e0e0e0;
}

html.dark-theme .widget-stats .stats-value,
html.dark .widget-stats .stats-value,
html.system-dark-theme .widget-stats .stats-value,
html.dark.system-theme .widget-stats .stats-value {
  color: #ffffff;
}

html.dark-theme .widget-stats .stats-label,
html.dark .widget-stats .stats-label,
html.system-dark-theme .widget-stats .stats-label,
html.dark.system-theme .widget-stats .stats-label {
  color: #e0e0e0;
}

html.dark-theme .post-list li,
html.dark .post-list li,
html.system-dark-theme .post-list li,
html.dark.system-theme .post-list li {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .widget_ui_comments .ui-comment-item,
html.dark .widget_ui_comments .ui-comment-item,
html.system-dark-theme .widget_ui_comments .ui-comment-item,
html.dark.system-theme .widget_ui_comments .ui-comment-item {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .widget-stats .stats-item,
html.dark .widget-stats .stats-item,
html.system-dark-theme .widget-stats .stats-item,
html.dark.system-theme .widget-stats .stats-item {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

html.dark-theme .empty-message,
html.dark .empty-message,
html.system-dark-theme .empty-message,
html.dark.system-theme .empty-message {
  color: #b0b0b0;
}
</style>