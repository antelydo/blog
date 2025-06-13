<!-- 首页轮播图 -->
<template>
  <div class="featured-slider">
    <!-- 轮播图 -->
    <div class="slider-container" v-if="slides.length > 0">
      <div class="slider-wrapper" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
        <div class="slide" v-for="(slide, index) in slides" :key="index">
          <div class="slide-content">
            <router-link :to="`/post/${slide.id}`">
              <img :src="slide.thumbnail || defaultThumbnail" :alt="slide.title" class="slide-image">
              <div class="slide-caption">
                <h3 class="slide-title">{{ slide.title }}</h3>
                <p v-if="slide.description || slide.excerpt" class="slide-description">{{ slide.description || slide.excerpt }}</p>
              </div>
            </router-link>
          </div>
        </div>
      </div>
      <div class="slider-nav">
        <button class="nav-prev" @click="prevSlide">&#10094;</button>
        <button class="nav-next" @click="nextSlide">&#10095;</button>
      </div>
      <div class="slider-dots">
        <span
          v-for="(slide, index) in slides"
          :key="index"
          class="dot"
          :class="{ active: index === activeSlide }"
          @click="goToSlide(index)">
        </span>
      </div>
    </div>

    <div v-else-if="loading" class="slider-loading">
      <el-skeleton :rows="3" animated />
    </div>

    <div v-else-if="error" class="slider-error">
      <p>{{ $t('banner.error') }}</p>
      <el-button type="primary" size="small" @click="fetchBannerPosts">{{ $t('banner.retry') }}</el-button>
    </div>

    <!-- 最热文章 -->
    <div v-if="featuredPosts.length > 0">
      <h3 class="section-title">{{ $t('banner.hotPosts') }}</h3>
      <div class="posts-featured">
        <router-link
          v-for="post in featuredPosts"
          :key="post.id"
          :to="`/post/${post.id}`"
          class="post-card"
        >
          <img :src="post.thumbnail || defaultThumbnail" :alt="post.title" class="post-image">
          <h4 class="post-title">{{ post.title }}</h4>
        </router-link>
      </div>
    </div>

  </div>
</template>

<script>
import { getBannerTopPostList, getTopRecommendPostList, getHotPostList } from '@/api/post';
import { useI18n } from 'vue-i18n';
import defaultThumbnail from '@/assets/images/default.jpeg';

export default {
  name: 'FeaturedSlider',
  setup() {
    const { t } = useI18n();
    return { t };
  },
  data() {
    return {
      activeSlide: 0,
      autoPlayInterval: null,
      loading: true,
      error: false,
      defaultThumbnail: defaultThumbnail,
      slides: [],
      featuredPosts: [],
      quotePost: null
    }
  },
  mounted() {
    this.fetchBannerPosts();
    this.fetchFeaturedPosts();

  },
  beforeUnmount() {
    this.stopAutoPlay();
  },
  methods: {
    // 获取首页banner展示的文章列表
    async fetchBannerPosts() {
      this.loading = true;
      this.error = false;
      try {
        const response = await getBannerTopPostList();
        if (response && response.code === 200) {
          this.slides = response.data.list || [];

          if (this.slides.length > 0) {
            this.startAutoPlay();
          }
        } else {
          console.error('Failed to fetch banner posts:', response.msg || 'Unknown error');
          this.error = true;
        }
      } catch (error) {
        console.error('Error fetching banner posts:', error);
        this.error = true;
      } finally {
        this.loading = false;
      }
    },
    // 获取热门文章列表
    async fetchFeaturedPosts() {
      try {
        const response = await getHotPostList({ limit: 3 });
        if (response && response.code === 200) {
          this.featuredPosts = response.data || [];
        }
      } catch (error) {
        console.error('Error fetching featured posts:', error);
      }
    },



    nextSlide() {
      this.activeSlide = (this.activeSlide + 1) % this.slides.length;
      this.resetAutoPlay();
    },

    prevSlide() {
      this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
      this.resetAutoPlay();
    },

    goToSlide(index) {
      this.activeSlide = index;
      this.resetAutoPlay();
    },

    startAutoPlay() {
      this.autoPlayInterval = setInterval(this.nextSlide, 5000);
    },

    stopAutoPlay() {
      clearInterval(this.autoPlayInterval);
    },

    resetAutoPlay() {
      this.stopAutoPlay();
      this.startAutoPlay();
    }
  }
}
</script>

<style scoped>
.featured-slider {
  margin-bottom: 20px;
}

.slider-container {
  position: relative;
  overflow: hidden;
  border-radius: 3px;
  margin-bottom: 20px;
}

.slider-wrapper {
  display: flex;
  transition: transform 0.5s ease;
}

.slide {
  flex: 0 0 100%;
  position: relative;
}

.slide-content {
  position: relative;
  height: 45vh;
}

.slide-content a {
  display: block;
  text-decoration: none;
  color: inherit;
}

.slide-image {
  width: 100%;
  height: auto;
  display: block;
}

.slide-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.7);
  color: #fff;
  padding: 15px;
}

.slide-title {
  margin: 0 0 5px;
  font-size: 22px;
  font-weight: bold;
  color: #fff;
}

.slide-description {
  margin: 0;
  font-size: 16px;
  line-height: 1.5;
}

.slider-nav button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: rgba(0, 0, 0, 0.3);
  color: #fff;
  border: none;
  padding: 10px 15px;
  font-size: 18px;
  cursor: pointer;
  z-index: 2;
}

.slider-nav .nav-prev {
  left: 10px;
}

.slider-nav .nav-next {
  right: 10px;
}

.slider-dots {
  position: absolute;
  bottom: 15px;
  right: 15px;
  display: flex;
  z-index: 2;
}

.dot {
  width: 10px;
  height: 10px;
  background-color: rgba(255, 255, 255, 0.5);
  border-radius: 50%;
  margin: 0 5px;
  cursor: pointer;
}

.dot.active {
  background-color: #fff;
}

.slider-loading, .slider-error {
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

.slider-error p {
  color: #f56c6c;
  margin-bottom: 10px;
}

.section-title {
  font-size: 22px;
  margin: 0 0 15px;
  padding-left: 10px;
  border-left: 4px solid #09c;
  line-height: 1.4;
  color: #333;
  transition: color 0.3s ease, border-color 0.3s ease;
  text-align: left;
}

/* Rest of the existing styles */
.featured-topics {
  margin-bottom: 20px;
  background-color: #fff;
  border-radius: 3px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.topics-title {
  font-size: 18px;
  margin: 0 0 15px;
  color: #333;
  font-weight: bold;
}

.topics-list {
  display: flex;
  flex-wrap: wrap;
}

.topic-item {
  display: flex;
  align-items: center;
  padding: 6px 12px;
  margin: 0 10px 10px 0;
  background-color: #f8f8f8;
  border-radius: 3px;
  color: #666;
  text-decoration: none;
  font-size: 16px;
}

.topic-item:hover {
  background-color: #09c;
  color: #fff;
}

.topic-count {
  display: inline-block;
  background-color: #09c;
  color: #fff;
  width: 22px;
  height: 22px;
  line-height: 22px;
  text-align: center;
  border-radius: 50%;
  margin-right: 5px;
  font-size: 14px;
}

.topic-item:hover .topic-count {
  background-color: #fff;
  color: #09c;
}

.featured-quote {
  background-color: var(--card-bg-color, #fff);
  border-radius: 3px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.quote-title {
  margin: 0 0 10px;
  font-size: 18px;
  color: #333;
  font-weight: bold;
}

.quote-content {
  margin: 0;
  font-size: 16px;
  color: #666;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .slide-caption {
    padding: 10px;
  }

  .slide-title {
    font-size: 18px;
  }

  .slide-description {
    font-size: 14px;
  }

  .slider-nav button {
    padding: 8px 12px;
    font-size: 16px;
  }

  .post-card .post-title {
    font-size: 15px;
  }
}


.posts-featured {
  display: flex;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.post-card {
  width: calc(33.33% - 10px);
  margin-right: 15px;
  margin-bottom: 15px;
  text-decoration: none;
  border-radius: 3px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  background-color: var(--card-bg-color, #fff);
  transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s ease, color 0.3s ease;
  display: flex;
  flex-direction: column;
}

.post-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.post-card:last-child {
  margin-right: 0;
}

.post-image {
  width: 100%;
  height: auto;
  display: block;
  aspect-ratio: 16/9;
  object-fit: cover;
}

.post-card .post-title {
  padding: 12px;
  margin: 0;
  font-size: 16px;
  color: var(--heading-color, #333);
  font-weight: bold;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  max-height: 20em;
  line-height: 1.5;
  text-align: left;
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

.post-sticky {
  border-left: 4px solid #09c;
}

.post-sticky-badge {
  position: absolute;
  top: 0;
  right: 0;
  background-color: #09c;
  color: #fff;
  font-size: 12px;
  padding: 3px 8px;
  border-radius: 0 0 0 3px;
}

.post-title {
  margin: 0 0 10px;
  font-size: 18px;
  font-weight: bold;
  text-align: left;
  line-height: 1.3;
}

.post-title a {
  color: #333;
  text-decoration: none;
  transition: color 0.3s;
}

.post-title a:hover {
  color: #09c;
}

.post-content {
  margin-bottom: 15px;
  font-size: 14px;
  color: #666;
  line-height: 1.6;
  text-align: left;
}

.post-content p {
  margin: 0;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  font-size: 12px;
  color: var(--light-text-color, #999);
  text-align: left;
  gap: 15px;
  transition: color 0.3s ease;
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
  color: #07a;
}

.post-like, .post-author, .post-date, .post-category, .post-views, .post-comments {
  white-space: nowrap;
}

.post-like i {
  margin-right: 3px;
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
}

.post-thumb {
  flex: 0 0 220px;
  margin-right: 20px;
  overflow: hidden;
  border-radius: 3px;
}

.post-thumbnail {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.3s;
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

/* 暗黑模式和系统模式下的样式 */
html.dark-theme .section-title,
html.dark .section-title,
html.system-dark-theme .section-title,
html.dark.system-theme .section-title {
  color: #ffffff !important;
  border-left-color: var(--primary-color, #09c) !important;
  text-align: left !important;
}

html.dark-theme .post-card,
html.dark .post-card,
html.system-dark-theme .post-card,
html.dark.system-theme .post-card {
  background-color: var(--card-bg-color, #232324) !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

html.dark-theme .post-card .post-title,
html.dark .post-card .post-title,
html.system-dark-theme .post-card .post-title,
html.dark.system-theme .post-card .post-title {
  color: #ffffff !important;
  text-align: left !important;
}

html.dark-theme .posts-featured,
html.dark .posts-featured,
html.system-dark-theme .posts-featured,
html.dark.system-theme .posts-featured {
  color: #ffffff !important;
}

html.dark-theme .slider-loading,
html.dark .slider-loading,
html.system-dark-theme .slider-loading,
html.dark.system-theme .slider-loading,
html.dark-theme .slider-error,
html.dark .slider-error,
html.system-dark-theme .slider-error,
html.dark.system-theme .slider-error {
  background-color: var(--card-bg-color, #232324) !important;
}

@media (max-width: 768px) {
  .posts-featured {
    flex-direction: column;
  }

  .post-card {
    width: 100%;
    margin-right: 0;
  }

  .post {
    padding: 15px;
  }

  .post-title {
    font-size: 16px;
  }

  .post-meta {
    gap: 10px;
    line-height: 1.8;
  }

  .post-content-wrap {
    flex-direction: column;
  }

  .post-thumb {
    flex: 0 0 auto;
    margin-right: 0;
    margin-bottom: 15px;
    width: 100%;
  }
}
</style>