<!-- 文章详情页 -->
<template>
  <div class="blog-container">
    <Header />
    <main class="main-content">
      <div class="container">
        <div class="row">
          <div class="col-md-8">
            <!-- Loading State -->
            <div v-if="loading" class="loading-container">
              <el-skeleton :rows="5" animated />
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-container">
              <p class="error-message">{{ error }}</p>
              <el-button type="primary" size="small" @click="loadData">{{ $t('common.retry') }}</el-button>
            </div>

            <!-- Post Content -->
            <article v-else class="post-content card mb-4">
              <header class="post-header p-4 pb-2">
                <h1 class="post-title mb-4">
                  <span v-if="post.is_top" class="post-badge sticky-badge">{{ $t('posts.sticky') }}</span>
                  <span v-if="post.is_recommend" class="post-badge recommend-badge">{{ $t('posts.recommended') }}</span>
                  {{ post.title }}
                </h1>
                <div class="post-meta">
                  <div class="post-author">
                    <i class="fa fa-user" style="color: #9b59b6;"></i>
                    <img
                      v-if="post.author?.avatar"
                      :src="post.author.avatar || defaultAvatar"
                      :alt="post.author?.username || post.author?.nickname"
                      class="author-avatar"
                    />
                    <span>{{ post.author?.username || post.author?.nickname }}</span>
                  </div>
                  <div class="post-date">
                    <i class="fa fa-calendar" style="color: #27ae60;"></i>
                    <span>{{ formatDate(post.publish_time || post.create_time) }}</span>
                  </div>
                  <div v-if="post.categories && post.categories.length" class="post-category-container">
                    <i class="fa fa-folder" style="color: #3498db;"></i>
                    <router-link
                      v-for="(category, index) in post.categories"
                      :key="`cat-${index}`"
                      :to="`/category/${category.id}`"
                      class="category-tag"
                    >
                      {{ category.name }}
                    </router-link>
                  </div>
                  <div v-else-if="post.category" class="post-category-container">
                    <i class="fa fa-folder" style="color: #3498db;"></i>
                    <router-link
                      :to="`/category/${post.category.id}`"
                      class="category-tag"
                    >
                      {{ post.category.name || post.category.title }}
                    </router-link>
                  </div>
                  <div v-if="post.tags && post.tags.length > 0" class="post-tags-container">
                    <i class="fa fa-tags" style="color: #e67e22;"></i>
                    <router-link
                      v-for="tag in post.tags"
                      :key="tag.id"
                      :to="`/tag/${tag.id}`"
                      class="category-tag tag-item"
                    >
                      {{ tag.name }}
                    </router-link>
                  </div>
                  <div class="post-views">
                    <i class="fa fa-eye" style="color: #f39c12;"></i>
                    <span>{{ $t('webPost.views', { count: post.views || 0 }) }}</span>
                  </div>
                  <a :href="`#comments`" class="post-comments">
                    <i class="fa fa-comment" style="color: #3498db;"></i>
                    <span>{{ $t('webPost.comments', { count: post.comments || 0 }) }}</span>
                  </a>
                  <button
                    class="like-button"
                    :class="{ 'liked': post.is_liked, 'like-loading': likeLoading }"
                    @click="handleLike(post)"
                    :title="post.is_liked ? $t('posts.unlike') : $t('posts.like')"
                  >
                    <i class="fa" :class="post.is_liked ? 'fa-heart' : 'fa-heart-o'"></i>
                    <span>{{ post.likes || 0 }}</span>
                  </button>
                  <button
                    class="favorite-button"
                    :class="{ 'favorited': post.is_favorited, 'favorite-loading': favoriteLoading }"
                    @click="handleFavorite(post)"
                    :title="post.is_favorited ? $t('posts.unfavorite') : $t('posts.favorite')"
                    :data-post-id="post.id"
                  >
                    <i class="fa" :class="post.is_favorited ? 'fa-star' : 'fa-star-o'"></i>
                    <span>{{ post.favorites || 0 }}</span>
                  </button>
                </div>
              </header>

              <div class="featured-image" v-if="post.thumbnail">
                <img :src="post.thumbnail" :alt="post.title" class="img-fluid featured-thumbnail" @click="showLightbox(0)">
              </div>

              <div class="post-body p-4" v-html="post.content" ref="postContent"></div>

              <footer class="post-footer p-4 pt-2">
                <div class="post-actions">

                </div>

                <div v-if="post.author && (post.author.username || post.author.nickname)" class="author-bio mt-4 p-3">
                  <div class="author-avatar-1">
                    <img :src="post.author.avatar || defaultAvatar" :alt="post.author.username || post.author.nickname" class="img-fluid rounded-circle">
                  </div>
                  <div class="author-info">
                    <h3 class="author-name">{{ post.author.username || post.author.nickname }}</h3>
                    <div class="author-description">{{ post.author.bio || $t('webPost.authorBio') }}</div>
                  </div>
                </div>
              </footer>
            </article>

            <!-- Post Navigation -->
            <div v-if="!loading && !error" class="post-navigation mb-4">

              <div class="nav-previous" v-if="post.prev_post && post.prev_post.id">
                <router-link :to="`/post/${post.prev_post.id}`" class="nav-link">
                  <span class="nav-direction"><i class="fa fa-angle-left"></i> {{ $t('webPost.prev') }}</span>
                  <span class="nav-title">{{ post.prev_post.title }}</span>
                </router-link>
              </div>
              <div class="nav-next" v-if="post.next_post && post.next_post.id ">
                <router-link :to="`/post/${post.next_post.id}`" class="nav-link">
                  <span class="nav-direction">{{ $t('webPost.next') }} <i class="fa fa-angle-right"></i></span>
                  <span class="nav-title">{{ post.next_post.title }}</span>
                </router-link>
              </div>

            </div>

            <!-- Related Posts -->
            <div v-if="!loading && !error && post.related_posts && post.related_posts.length > 0" class="related-posts mb-4">
              <h3 class="section-title">{{ $t('webPost.relatedPosts') }}</h3>
              <div class="row">
                <div class="col-md-4" v-for="(relatedPost, index) in post.related_posts" :key="`related-${relatedPost.id}`">
                  <div class="related-post card">
                    <div class="related-post-image">
                      <img
                        :src="relatedPost.thumbnail || defaultThumbnail"
                        :alt="relatedPost.title"
                        class="img-fluid"
                        @click.stop="showRelatedImage(index)"
                      >
                      <router-link :to="`/post/${relatedPost.id}`" class="related-post-link"></router-link>
                    </div>
                    <div class="related-post-content p-3">
                      <h4 class="related-post-title">
                        <router-link :to="`/post/${relatedPost.id}`">{{ relatedPost.title }}</router-link>
                      </h4>
                      <div class="related-post-meta">
                        <span class="related-post-date">{{ formatDate(relatedPost.publish_time || relatedPost.create_time) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Comments Section -->
            <div v-if="!loading && !error" class="comments-section mb-4">
              <div class="comments-header">
                <h3 class="comments-title">
                  <i class="el-icon-chat-line-square"></i>
                  {{ $t('webPost.comments', { count: post.comments || 0 }) }}
                </h3>
                <div class="comments-divider"></div>
              </div>

              <comment-list
                :post-id="postId"
                :initial-comments="comments || []"
                :initial-loading="commentsLoading"
                :initial-error="commentsError"
                @load-comments="handleLoadComments"
                @submit-comment="handleSubmitComment"
                @submit-reply="handleSubmitReply"
              />
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

    <!-- Lightbox组件 -->
    <vue-easy-lightbox
      :visible="lightboxVisible"
      :imgs="lightboxImages"
      :index="lightboxIndex"
      @hide="onHideLightbox"
      moveDisabled
      teleport="body"
      escDisabled
    ></vue-easy-lightbox>
  </div>
</template>

<script>
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import Sidebar from './components/Sidebar.vue';
import BackToTop from './components/BackToTop.vue';
import './assets/blog.css';
import { getPostDetail, getRelatedPostList } from '@/api/post';
import { formatDate } from '@/utils/date';
import defaultThumbnail from '@/assets/images/default.jpeg';
import defaultAvatar from '@/assets/images/avatar.png';
import VueEasyLightbox from 'vue-easy-lightbox';
import { useHead } from '@vueuse/head';
import { computed, watch, ref, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { useArticleSEO, formatISODate } from '@/utils/seo';
import viewTracker from '@/utils/viewTracker';
import { likePost, unlikePost } from '@/api/like';
import { addFavorite, cancelFavorite } from '@/api/favorite';
import { getCommentList, addComment, replyComment } from '@/api/comment';
import { ElMessage } from 'element-plus';
import CommentList from '@/components/comment/CommentList.vue';

export default {
  name: 'PostPage',
  components: {
    Header,
    Footer,
    Sidebar,
    BackToTop,
    VueEasyLightbox,
    CommentList
  },
  data() {
    return {
      post: {},
      loading: true,
      error: null,
      prevPost: null,
      nextPost: null,
      // 已移除未使用的评论相关数据
      defaultThumbnail,
      defaultAvatar,
      // 图片灯箱相关数据
      lightboxVisible: false,
      lightboxImages: [],
      lightboxIndex: 0,
      // 添加图片观察器
      imageObserver: null,
      // 相关文章图片
      relatedImages: [],
      charCount: 0,
      likeLoading: false,
      favoriteLoading: false,
      // 评论相关
      comments: [],
      commentsLoading: false,
      commentsError: null
    };
  },

  computed: {
    postId() {
      return parseInt(this.$route.params.id) || 0;
    }
  },
  watch: {
    // Watch for route param changes to reload data when navigating between posts
    '$route.params.id'(newId) {
      this.loadData();
    },
    // 监听文章变化，更新SEO元数据
    post: {
      handler(newPost) {
        if (newPost && newPost.title) {
          this.updateSeoMetadata();
        }
      },
      deep: true
    }
  },
  methods: {
    async loadData() {
      this.loading = true;
      this.error = null;

      try {
        if (!this.postId) {
          this.error = this.$t('webPost.notFound');
          this.loading = false;
          return;
        }

        const response = await getPostDetail({id:this.postId,is_get_prev_next:1,is_check_like:1,is_get_comment:0,is_check_favorite:1 });
        if (response.code === 200 && response.data) {
          this.post = response.data;

          // Load related posts if not included in the response
          if (!this.post.related_posts || this.post.related_posts.length === 0) {
            this.loadRelatedPosts();
          }

          // Load prev and next posts
          this.loadPrevNextPosts();

          // 记录图片数据，但等到mounted和nextTick后再处理DOM
          if (this.post.thumbnail) {
            this.lightboxImages = [this.post.thumbnail];
          } else {
            this.lightboxImages = [];
          }

          // 初始化相关文章图片
          this.setupRelatedImages();

          // 加载文章评论
          console.log('Post data:', this.post);
          if (this.post.comment && this.post.comment.length > 0) {
            console.log('Using comments from post data:', this.post.comment);
            this.comments = this.post.comment;
            // 设置评论总数
            this.commentTotal = this.post.comment.length;
          } else {
            console.log('Loading comments separately');
            this.loadComments();
          }

          // 内容图片等DOM可用后处理
          this.setupImagesWhenReady();
        } else {
          this.error = response.msg || this.$t('webPost.notFound');
        }
      } catch (error) {
        console.error('Error fetching post:', error);
        this.error = this.$t('webPost.fetchError');
      } finally {
        this.loading = false;
      }
    },

    // 添加一个新方法确保DOM准备好后再处理图片
    setupImagesWhenReady() {
      // 在nextTick中多次尝试，确保DOM已加载
      const checkAndSetup = () => {
        this.$nextTick(() => {
          if (this.$refs.postContent) {
            console.log('DOM ready, setting up images');
            this.setupContentImages();
            this.observeContentChanges();
          } else {
            console.log('DOM not ready yet, will retry');
            // 如果还没准备好，延迟重试
            setTimeout(checkAndSetup, 100);
          }
        });
      };

      // 开始检查
      checkAndSetup();
    },

    // 监控内容变化，处理动态加载的图片
    observeContentChanges() {
      if (!this.$refs.postContent) return;

      // 清理之前的观察器
      if (this.imageObserver) {
        this.imageObserver.disconnect();
      }

      // 创建一个观察器实例，观察DOM变化
      this.imageObserver = new MutationObserver((mutations) => {
        let needsUpdate = false;

        mutations.forEach(mutation => {
          if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            mutation.addedNodes.forEach(node => {
              // 检查新添加的节点是否包含图片
              if (node.nodeName === 'IMG') {
                needsUpdate = true;
              } else if (node.querySelectorAll) {
                const images = node.querySelectorAll('img');
                if (images.length > 0) {
                  needsUpdate = true;
                }
              }
            });
          }
        });

        // 如果发现新图片，更新图片处理
        if (needsUpdate) {
          console.log('Content changed, updating images');
          this.setupContentImages();
        }
      });

      // 开始观察
      this.imageObserver.observe(this.$refs.postContent, {
        childList: true,
        subtree: true
      });
    },

    // 设置文章内容中图片的点击事件
    setupContentImages() {
      console.log('Setting up content images...');
      if (!this.$refs.postContent) {
        console.error('Post content ref not found, cannot setup images');
        return;
      }

      // 确保元素已挂载
      if (!this.$refs.postContent.querySelectorAll) {
        console.error('Post content element is not a valid DOM node');
        return;
      }

      const contentElement = this.$refs.postContent;
      const images = contentElement.querySelectorAll('img');

      console.log('Found content images:', images.length);

      if (images.length === 0) return;

      // 清理现有图片列表并重新构建
      this.lightboxImages = this.post.thumbnail ? [this.post.thumbnail] : [];
      const contentImageUrls = Array.from(images).map(img => img.src || '');
      this.lightboxImages = [...this.lightboxImages, ...contentImageUrls];

      console.log('Lightbox images updated:', this.lightboxImages.length);

      // 简化图片点击处理
      images.forEach((img, index) => {
        // 先清除所有事件和样式
        const oldClone = img.cloneNode(true);
        img.parentNode.replaceChild(oldClone, img);
        const newImg = oldClone;

        // 重新添加样式
        newImg.classList.add('post-content-image');
        newImg.style.cursor = 'pointer';
        newImg.style.maxWidth = '100%';
        newImg.style.height = 'auto';
        newImg.style.borderRadius = '8px';
        newImg.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
        newImg.style.transition = 'all 0.3s ease';

        // 添加点击事件处理
        const imageIndex = this.post.thumbnail ? index + 1 : index;
        newImg.onclick = (e) => {
          console.log('Image clicked with index:', imageIndex);
          e.preventDefault();
          e.stopPropagation();
          // 直接调用显示灯箱
          this.lightboxVisible = true;
          this.lightboxIndex = imageIndex;
          return false;
        };
      });
    },

    // 显示图片灯箱
    showLightbox(index) {
      console.log('Show lightbox called with index:', index);
      this.lightboxIndex = index;
      this.lightboxVisible = true;
      // 强制在下一个事件循环中执行，以确保UI更新
      setTimeout(() => {
        if (!this.lightboxVisible) {
          console.log('Forcing lightbox visibility');
          this.lightboxVisible = true;
        }
      }, 0);
    },

    // 关闭图片灯箱
    onHideLightbox() {
      console.log('Hiding lightbox');
      this.lightboxVisible = false;
    },

    async loadRelatedPosts() {
      try {
        const response = await getRelatedPostList(this.postId);
        if (response.code === 200 && response.data) {
          this.post.related_posts = response.data;
        }
      } catch (error) {
        console.error('Error fetching related posts:', error);
      }
    },

    async loadPrevNextPosts() {
      // This functionality would require API support for fetching previous and next posts
      // For now, leaving as placeholders
      this.prevPost = null;
      this.nextPost = null;
    },

    //點贊
    handleLike(post) {
      // 确保文章对象有ID
      if (!post || !post.id) {
        console.error('无效的文章对象:', post);
        return;
      }

      // 设置加载状态
      this.likeLoading = true;

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
          this.likeLoading = false;
        });

      // 添加错误处理，确保加载状态被清除
      setTimeout(() => {
        if (this.likeLoading) {
          this.likeLoading = false;
        }
      }, 5000); // 5秒后自动清除，防止卡死
    },

    //收藏
    handleFavorite(post) {
      // 确保文章对象有ID
      if (!post || !post.id) {
        console.error('无效的文章对象:', post);
        return;
      }

      // 检查用户是否登录
      const token = localStorage.getItem('token');
      if (!token) {
        ElMessage.warning(this.$t('posts.loginToFavorite'));
        return;
      }

      // 设置加载状态
      this.favoriteLoading = true;

      // 准备请求数据
      const requestData = { post_id: post.id };

      // 根据当前收藏状态决定是收藏还是取消收藏
      const action = post.is_favorited ? cancelFavorite : addFavorite;

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
          this.favoriteLoading = false;
        });

      // 添加错误处理，确保加载状态被清除
      setTimeout(() => {
        if (this.favoriteLoading) {
          this.favoriteLoading = false;
        }
      }, 5000); // 5秒后自动清除，防止卡死
    },

    // 已移除未使用的评论相关方法

    // 加载文章评论 - 现在由CommentList组件处理
    async loadComments() {
      // 初始化评论状态
      this.commentsLoading = false;
      this.commentsError = null;
      this.comments = [];
      this.commentTotal = 0;
    },

    // 已移除未使用的评论提交相关方法

    // 处理加载评论事件 - 直接传递给CommentList组件
    handleLoadComments(data) {
      // 这里只需要传递事件，具体处理由CommentList组件完成
      if (!data || !data.postId) return;

      // 更新状态
      this.commentsLoading = true;

      // 如果有回调函数，调用API获取评论
      if (typeof data.callback === 'function') {
        // 准备API参数
        const params = {
          post_id: data.postId,
          page: data.page || 1,
          page_size: data.pageSize || 10
        };

        // 如果指定了parent_id，添加到参数中
        if (data.parent_id !== undefined) {
          params.parent_id = data.parent_id; // 只加载指定 parent_id 的评论
        }

        // 如果指定了排序方式，添加到参数中
        if (data.sort_by) {
          params.sort_by = data.sort_by; // 添加排序方式
        }

        // 调用API获取评论
        getCommentList(params)
          .then(response => {
            if (response && response.code === 200) {
              // 处理成功响应
              data.callback({
                success: true,
                data: {
                  list: response.data.list || [],
                  total: response.data.total || 0,
                  hasMore: response.data.has_more || false
                }
              });
            } else {
              // 处理失败响应
              data.callback({
                success: false,
                message: response?.msg || '加载评论失败'
              });
            }
          })
          .catch(error => {
            console.error('Error loading comments:', error);
            data.callback({
              success: false,
              message: '加载评论失败'
            });
          });
      }
    },

    // 处理提交评论事件 - 直接传递给CommentList组件
    handleSubmitComment(data) {
      console.log('PostPage: handleSubmitComment called');
      // 这里只需要传递事件，具体处理由CommentList组件完成
      if (!data) return;

      // 添加postId
      data.post_id = this.postId;

      console.log('PostPage: Calling API with data:', data);

      // 显示加载状态
      this.commentsLoading = true;

      // 调用API发表评论
      // 添加调试日志，确保正确调用API
      console.log('PostPage: About to call addComment API with data:', JSON.stringify(data));
      addComment(data)
        .then(response => {
          if (response && response.code === 200) {
            // 处理成功响应 - 不显示消息，由 CommentList 组件处理
            console.log('PostPage: Comment submitted successfully, need_audit:', response.data.need_audit);

            // 如果评论不需要审核，更新文章评论数
            if (!response.data.need_audit) {
              this.post.comments = (this.post.comments || 0) + 1;
            }

            // 调用回调函数
            if (typeof data.callback === 'function') {
              data.callback({
                success: true,
                needAudit: response.data.need_audit,
                data: null // 这里没有返回评论数据，需要重新加载
              });
            }

            // 不需要重新加载评论列表，回调函数会处理
          } else {
            // 处理失败响应
            ElMessage.error(response?.msg || '发表评论失败');

            if (typeof data.callback === 'function') {
              data.callback({
                success: false,
                message: response?.msg || '发表评论失败'
              });
            }
          }
        })
        .catch(error => {
          console.error('Error submitting comment:', error);
          ElMessage.error('发表评论失败，请重试');

          if (typeof data.callback === 'function') {
            data.callback({
              success: false,
              message: '发表评论失败，请重试'
            });
          }
        })
        .finally(() => {
          this.commentsLoading = false;
        });
    },

    // 处理提交回复事件 - 直接传递给CommentList组件
    handleSubmitReply(data) {
      console.log('PostPage: handleSubmitReply called');
      // 这里只需要传递事件，具体处理由CommentList组件完成
      if (!data) return;

      // 添加postId
      data.post_id = this.postId;

      // 显示加载状态
      this.commentsLoading = true;

      // 调用API发表回复
      replyComment(data)
        .then(response => {
          if (response && response.code === 200) {
            // 处理成功响应 - 不显示消息，由 CommentList 组件处理
            console.log('PostPage: Reply submitted successfully, need_audit:', response.data.need_audit);

            // 如果回复不需要审核，更新文章评论数
            if (!response.data.need_audit) {
              this.post.comments = (this.post.comments || 0) + 1;
            }

            // 调用回调函数
            if (typeof data.callback === 'function') {
              data.callback({
                success: true,
                needAudit: response.data.need_audit,
                data: null // 这里没有返回评论数据，需要重新加载
              });
            }

            // 不需要重新加载评论列表，回调函数会处理
          } else {
            // 处理失败响应
            ElMessage.error(response?.msg || '发表回复失败');

            if (typeof data.callback === 'function') {
              data.callback({
                success: false,
                message: response?.msg || '发表回复失败'
              });
            }
          }
        })
        .catch(error => {
          console.error('Error submitting reply:', error);
          ElMessage.error('发表回复失败，请重试');

          if (typeof data.callback === 'function') {
            data.callback({
              success: false,
              message: '发表回复失败，请重试'
            });
          }
        })
        .finally(() => {
          this.commentsLoading = false;
        });
    },

    shareOnFacebook() {
      const url = encodeURIComponent(window.location.href);
      const title = encodeURIComponent(this.post.title);
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&t=${title}`, '_blank');
    },

    shareOnTwitter() {
      const url = encodeURIComponent(window.location.href);
      const text = encodeURIComponent(`${this.post.title}`);
      window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
    },

    shareOnLinkedIn() {
      const url = encodeURIComponent(window.location.href);
      const title = encodeURIComponent(this.post.title);
      const summary = encodeURIComponent(this.post.description || '');
      window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}&summary=${summary}`, '_blank');
    },

    formatDate,

    // 设置相关文章图片
    setupRelatedImages() {
      this.relatedImages = [];
      if (this.post.related_posts && this.post.related_posts.length > 0) {
        this.relatedImages = this.post.related_posts.map(post => post.thumbnail || this.defaultThumbnail);
        console.log('Setup related images:', this.relatedImages.length);
      }
    },

    // 显示相关文章图片
    showRelatedImage(index) {
      const startIndex = this.lightboxImages.length;

      // 如果还没有加载相关图片到灯箱，先添加
      if (!this.lightboxImages.includes(this.relatedImages[0])) {
        this.lightboxImages = [...this.lightboxImages, ...this.relatedImages];
        console.log('Added related images to lightbox:', this.lightboxImages.length);
      }

      this.lightboxIndex = startIndex + index;
      this.lightboxVisible = true;
    },

    updateCharCount() {
      this.charCount = this.newComment.content.length;
    },

    // 添加更新SEO元数据的方法
    updateSeoMetadata() {
      // This method is now replaced by useHead in the setup function
      // Left as an empty method for backward compatibility
    },

    // 辅助方法：更新meta标签
    updateMetaTag(name, content, attributeName = 'name') {
      // This method is now replaced by useHead in the setup function
      // Left as an empty method for backward compatibility
    },

    // 辅助方法：更新canonical链接
    updateCanonicalLink() {
      // This method is now replaced by useHead in the setup function
      // Left as an empty method for backward compatibility
    },

    // 添加文章结构化数据
    addStructuredData() {
      // This method is now replaced by useHead in the setup function
      // Left as an empty method for backward compatibility
    }
  },
  created() {
    this.loadData();
  },



  beforeUnmount() {
    // 组件卸载前停止跟踪并发送浏览时长数据
    viewTracker.stop();
  },
  // 组件销毁时清理观察器
  beforeDestroy() {
    if (this.imageObserver) {
      this.imageObserver.disconnect();
      this.imageObserver = null;
    }
  },
  mounted() {
    console.log('Component mounted, checking refs');

    // 文章加载完成后开始跟踪浏览时长
    if (this.postId) {
      viewTracker.start('post', this.postId);
    }

    // DOM挂载后，重新检查图片处理
    this.$nextTick(() => {
      if (this.$refs.postContent) {
        this.setupContentImages();
        this.observeContentChanges();
      }
    });

    // 添加全局处理以确保Vue-Easy-Lightbox正常工作
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.lightboxVisible) {
        e.preventDefault();
        this.lightboxVisible = false;
      }
    });

    // 更新SEO元数据
    this.updateSeoMetadata();
  },
  setup() {
    const route = useRoute();
    const postId = computed(() => parseInt(route.params.id) || 0);

    // Blog config for site info
    const blogConfigStore = useBlogConfigStore();
    const siteName = computed(() => blogConfigStore.config.site_name || '');

    // Post data for SEO
    const post = ref(null);

    // Compute SEO properties
    const title = computed(() => {
      if (post.value && post.value.title) {
        return `${post.value.title} - ${siteName.value}`;
      }
      return siteName.value;
    });

    const description = computed(() => {
      if (post.value) {
        return post.value.description || post.value.excerpt ||
          `${post.value.title}，作者：${post.value.author?.nickname || post.value.author?.username || ''}`;
      }
      return '';
    });

    const keywords = computed(() => {
      if (!post.value) return '';

      const keywords = [post.value.title];

      // Add category keywords
      if (post.value.categories && post.value.categories.length) {
        post.value.categories.forEach(cat => {
          if (cat.name) keywords.push(cat.name);
        });
      } else if (post.value.category && post.value.category.name) {
        keywords.push(post.value.category.name);
      }

      // Add tag keywords
      if (post.value.tags && post.value.tags.length) {
        post.value.tags.forEach(tag => {
          if (tag.name) keywords.push(tag.name);
        });
      }

      return keywords.join(',');
    });

    const authorName = computed(() => {
      if (!post.value) return '';
      return post.value.author ?
        (post.value.author.nickname || post.value.author.username || '') :
        '';
    });

    const imageUrl = computed(() => {
      if (post.value && post.value.thumbnail) {
        return post.value.thumbnail;
      }
      return '';
    });

    const publishDate = computed(() => {
      if (!post.value) return '';
      return formatISODate(post.value.publish_time || post.value.create_time);
    });

    const modifiedDate = computed(() => {
      if (!post.value) return '';
      return formatISODate(post.value.update_time || post.value.publish_time || post.value.create_time);
    });

    // Use the article SEO utility
    useHead(useArticleSEO({
      title,
      description,
      keywords,
      image: imageUrl,
      authorName,
      publishDate,
      modifiedDate,
      post
    }));

    return {
      postSetup: post,
      siteName
    };
  }
};
</script>

<style>
/* 不使用scoped，确保样式应用到动态内容 */
.post-body img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  cursor: pointer !important;
}

.post-body img:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transform: translateY(-2px);
}

/* 确保灯箱组件可见，层级高于所有其他元素 */
.vel-modal {
  z-index: 10000 !important;
}
</style>

<style scoped>
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

.post-title {
  font-size: 2rem;
  color: var(--heading-color);
  line-height: 1.3;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  text-align: left;
  gap: 12px;
  margin-bottom: 20px;
  padding-top: 10px;
  border-top: 1px dashed var(--border-color, #eee);
  font-size: 14px;
  color: var(--light-text-color, #718096);
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

.post-author, .post-date, .post-category-container, .post-tags-container, .post-views, .post-comments {
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.post-meta i {
  margin-right: 5px;
  font-size: 16px;
}

.author-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  margin: 0 5px;
  border: 1px solid var(--border-color, rgba(0, 0, 0, 0.05));
  transition: border-color 0.3s ease;
}

.post-comments {
  color: var(--link-color, #09c);
  text-decoration: none;
  transition: color 0.3s ease;
}

.post-comments:hover {
  text-decoration: underline;
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
  background-color: var(--bg-color-light, #f8f4ec);
  transition: background-color 0.3s ease;
}

.tag-item:hover {
  background-color: var(--bg-color-hover, #f0e9d8);
}

.post-badge {
  display: inline-block;
  font-size: 12px;
  padding: 2px 8px;
  margin-right: 8px;
  border-radius: 3px;
  color: white;
  vertical-align: middle;
}

.sticky-badge {
  background-color: #e74c3c;
}

.recommend-badge {
  background-color: #3498db;
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
  transition: all 0.3s, border-color 0.3s ease, background-color 0.3s ease;
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

.post-body {
  line-height: 1.8;
  color: var(--text-color);
}

.post-body h2 {
  font-size: 1.5rem;
  margin: 1.5rem 0 1rem;
  color: var(--heading-color);
}

.post-body p {
  margin-bottom: 1.2rem;
}

.post-body ul, .post-body ol {
  margin-bottom: 1.2rem;
  padding-left: 1.5rem;
}

.post-body li {
  margin-bottom: 0.5rem;
}

/* Deep selectors for post body images */
:deep(.post-body img) {
  max-width: 100%;
  height: auto;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

:deep(.post-body img:hover) {
  transform: scale(1.02);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.post-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border-color-light);
  padding-top: 1rem;
  margin-top: 1rem;
}

.post-share {
  display: flex;
  align-items: center;
}

.share-label {
  margin-right: 0.5rem;
  font-size: 0.9rem;
  color: var(--text-color-secondary);
}

.share-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  margin-right: 0.5rem;
  color: #fff;
  text-decoration: none;
  transition: opacity var(--transition-speed);
}

.share-link:hover {
  opacity: 0.8;
}

.share-link.facebook {
  background-color: #3b5998;
}

.share-link.twitter {
  background-color: #1da1f2;
}

.share-link.linkedin {
  background-color: #0077b5;
}

.author-bio {
  display: flex;
  background-color: var(--light-bg-color);
  border-radius: 5px;
}

.author-avatar {
  flex: 0 0 10px;
  margin-right: 0.2rem;
}
.author-avatar-1 {
  flex: 0 0 80px;
  margin-right: 0.5rem;
}

 .author-avatar-1 img {
  width: 80px;
  height: 80px;
    border-radius: 3px;
}

.author-name {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
  color: var(--heading-color);
}

.author-description {
  font-size: 0.9rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.author-social {
  display: flex;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  background-color: var(--primary-color);
  color: #fff;
  margin-right: 0.5rem;
  text-decoration: none;
  transition: background-color var(--transition-speed);
}

.social-link:hover {
  background-color: var(--primary-color-hover);
}

.post-navigation {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
  transition: color 0.3s ease;
}

.nav-link {
  display: flex;
  flex-direction: column;
  padding: 1rem;
  background-color: var(--card-bg-color, #fff);
  border-radius: 5px;
  text-decoration: none;
  transition: transform var(--transition-speed), background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.nav-link:hover {
  transform: translateY(-3px);
  background-color: var(--bg-color-hover, #f8f8f8);
}

.nav-direction {
  font-size: 0.85rem;
  color: var(--primary-color);
  margin-bottom: 0.25rem;
}

.nav-title {
  color: var(--heading-color);
  font-weight: 500;
}

.section-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: var(--heading-color);
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 0.5rem;
}

.related-post {
  height: 100%;
  transition: transform var(--transition-speed);
  background-color: var(--card-bg-color, #fff);
  transition: transform var(--transition-speed), background-color 0.3s ease, color 0.3s ease;
}

.related-post:hover {
  transform: translateY(-3px);
}

.related-post-image {
  display: block;
  overflow: hidden;
  position: relative;
}

.related-post-link {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}

.related-post-image img {
  transition: transform var(--transition-speed);
  height: 150px;
  object-fit: cover;
  width: 100%;
  border-radius: 8px;
  position: relative;
  z-index: 0;
  cursor: pointer;
}

.related-post-image img:hover {
  transform: scale(1.05);
}

.related-post-title {
  font-size: 1rem;
  margin: 1rem 0 0.5rem;
}

.related-post-title a {
  color: var(--heading-color);
  text-decoration: none;
  transition: color var(--transition-speed);
}

.related-post-title a:hover {
  color: var(--primary-color);
}

.related-post-meta {
  font-size: 0.8rem;
  color: var(--light-text-color, var(--text-color-secondary));
  transition: color 0.3s ease;
}

.comment-container {
  display: flex;
  width: 100%;
}

.comment-avatar {
  flex: 0 0 50px;
  margin-right: 1rem;
}

.comment-avatar img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--border-color, #eee);
  transition: border-color 0.3s ease;
}

.comment-content {
  flex: 1;
  color: var(--text-color);
  transition: color 0.3s ease;
}

.comment-meta {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.comment-author {
  font-weight: 600;
  color: var(--heading-color);
  margin-right: 0.8rem;
  transition: color 0.3s ease;
}

.comment-date {
  font-size: 0.85rem;
  color: var(--light-text-color, var(--text-color-secondary));
  transition: color 0.3s ease;
}

.comment-text {
  margin-bottom: 0.6rem;
  line-height: 1.6;
  word-break: break-word;
  color: var(--text-color);
  transition: color 0.3s ease;
}

.comment-actions, .reply-actions {
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: flex-end;
  width: 100%;
  text-align: right;
}

.reply-button {
  background-color: transparent;
  border: none;
  color: var(--primary-color);
  cursor: pointer;
  padding: 0;
  font-size: 0.9rem;
  transition: color var(--transition-speed);
  text-decoration: none;
  margin-left: auto;
}

.reply-button:hover {
  color: var(--primary-color-hover);
  text-decoration: underline;
}

.comment-replies {
  margin-left: 0;
  border-left: 2px solid var(--border-color, #eee);
  padding-left: 1rem;
  transition: border-color 0.3s ease;
}

.reply {
  margin-bottom: 1rem;
}

.reply:last-child {
  margin-bottom: 0;
}

.reply-container {
  display: flex;
  width: 100%;
}

.reply-avatar {
  flex: 0 0 50px;
  margin-right: 1rem;
}

.reply-avatar img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--border-color, #eee);
  transition: border-color 0.3s ease;
}

.reply-content {
  flex: 1;
}

.reply-meta {
  display: flex;
  align-items: center;
  margin-bottom: 0.4rem;
  flex-wrap: wrap;
}

.reply-author {
  font-weight: 600;
  color: var(--heading-color);
  margin-right: 0.4rem;
}

.reply-to {
  color: var(--primary-color);
  margin-right: 0.4rem;
}

.reply-date {
  font-size: 0.85rem;
  color: var(--light-text-color, var(--text-color-secondary));
  transition: color 0.3s ease;
}

.reply-text {
  margin-bottom: 0.4rem;
  line-height: 1.6;
  word-break: break-word;
  color: var(--text-color);
  transition: color 0.3s ease;
}

.form-title {
  display: flex;
  align-items: center;
  font-size: 1.25rem;
  color: var(--heading-color);
  border-bottom: 1px solid var(--border-color, #f0f0f0);
  padding-bottom: 0.8rem;
  margin-bottom: 1.2rem;
  justify-content: flex-start;
  text-align: left;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.cancel-reply-button {
  background: transparent;
  border: none;
  color: var(--light-text-color, #999);
  cursor: pointer;
  padding: 2px 5px;
  font-size: 0.9rem;
  border-radius: 50%;
  transition: color 0.3s ease, background-color 0.3s ease;
}

.cancel-reply-button:hover {
  background-color: var(--bg-color-hover, #f5f5f5);
  color: var(--text-color, #666);
}

@media (max-width: 768px) {
  .post-navigation {
    flex-direction: column;
    gap: 1rem;
  }

  .nav-link {
    max-width: 100%;
  }

  .post-actions, .author-bio {
    flex-direction: column;
  }

  .post-share {
    margin-bottom: 1rem;
  }

  .author-avatar {
    margin-bottom: 1rem;
    margin-right: 0;
  }
}

@media (max-width: 576px) {
  .post-meta {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
  }

  .comment-container,
  .reply-container {
    flex-direction: column;
  }

  .comment-avatar,
  .reply-avatar {
    margin-bottom: 0.75rem;
    margin-right: 0;
    align-self: flex-start;
  }

  .comment-replies {
    margin-left: 0;
    padding-left: 0.75rem;
  }
}

.featured-image {
  margin-bottom: 20px;
  text-align: center;
  overflow: hidden;
  border-radius: 8px;
}

.featured-thumbnail {
  max-width: 100%;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.featured-thumbnail:hover {
  transform: scale(1.02);
}

/* 评论表单样式优化 */
.comment-form {
  background-color: var(--card-bg-color, #fff);
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease, background-color 0.3s ease, color 0.3s ease;
}

.form-title {
  display: flex;
  align-items: center;
  font-size: 1.25rem;
  color: var(--heading-color);
  border-bottom: 1px solid var(--border-color, #f0f0f0);
  padding-bottom: 0.8rem;
  margin-bottom: 1.2rem;
  justify-content: flex-start;
  text-align: left;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.reply-title {
  color: var(--primary-color);
}

.form-label {
  font-weight: 500;
  color: var(--heading-color);
  margin-bottom: 0.5rem;
  display: block;
}

.form-label i {
  margin-right: 6px;
  color: var(--primary-color);
}

.form-control {
  display: block;
  width: 100%;
  padding: 0.625rem 0.875rem;
  font-size: 1rem;
  line-height: 1.5;
  color: var(--text-color);
  background-color: var(--bg-color-light, #fff);
  border: 1px solid var(--border-color, #e0e0e0);
  border-radius: 5px;
  transition: all 0.3s ease, background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(0, 153, 204, 0.15);
  outline: none;
}

.form-control::placeholder {
  color: #aaa;
  font-size: 0.9rem;
}

.comment-textarea {
  resize: vertical;
  min-height: 120px;
}

.char-count {
  text-align: right;
  margin-top: 4px;
  font-size: 0.8rem;
  color: #999;
}

.char-count-limit {
  color: #e74c3c;
}

.form-tips {
  font-size: 0.85rem;
  margin-top: 8px;
}

.submit-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: var(--primary-color);
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 0.625rem 1.25rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-button:hover {
  background-color: var(--primary-color-hover);
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 153, 204, 0.3);
}

.submit-button:active {
  transform: translateY(0);
}

.submit-button i {
  margin-right: 8px;
}

.cancel-reply-button {
  background: transparent;
  border: none;
  color: var(--light-text-color, #999);
  cursor: pointer;
  padding: 2px 5px;
  font-size: 0.9rem;
  border-radius: 50%;
  margin-left: 8px;
  transition: all 0.2s ease;
}

.cancel-reply-button:hover {
  background-color: var(--bg-color-hover, #f5f5f5);
  color: var(--text-color, #666);
  transition: background-color 0.3s ease, color 0.3s ease;
}

@media (max-width: 576px) {
  .form-title {
    font-size: 1.1rem;
  }

  .submit-button {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    width: 100%;
    margin-top: 1rem;
  }

  .form-group.mb-0 {
    flex-direction: column;
  }

  .form-tips {
    margin-bottom: 0.5rem;
  }
}

.comment-text, .reply-text {
  margin-bottom: 0.6rem;
  line-height: 1.6;
  word-break: break-word;
  text-align: left;
}

.reply-text {
  margin-bottom: 0.4rem;
  line-height: 1.6;
  word-break: break-word;
  text-align: left;
}

.comment-form-container {
  text-align: left;
}

.comment-meta, .reply-meta {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
  justify-content: flex-start;
  text-align: left;
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
</style>