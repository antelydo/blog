<template>
  <div class="comment-list-container">
    <!-- 评论区头部 -->
    <div class="comment-header">
      <div class="comment-count">
        <i class="el-icon-chat-line-square"></i>
        <span>{{ totalComments || 0 }}</span>
        <span class="comment-text">条评论</span>
      </div>

      <div class="comment-actions">
        <button class="post-comment-btn" @click="toggleCommentForm">
          <i class="el-icon-edit"></i> 发表评论
        </button>

        <div class="comment-sort">
          <span class="sort-label">排序方式:</span>
          <span
            class="sort-option"
            :class="{ 'active': sortBy === 'newest', 'disabled': loading }"
            @click="!loading && changeSortOrder('newest')"
          >
            <i v-if="loading && sortBy === 'newest'" class="el-icon-loading"></i>
            <span>最新</span>
          </span>
          <span
            class="sort-option"
            :class="{ 'active': sortBy === 'hottest', 'disabled': loading }"
            @click="!loading && changeSortOrder('hottest')"
          >
            <i v-if="loading && sortBy === 'hottest'" class="el-icon-loading"></i>
            <span>最热</span>
          </span>
        </div>
      </div>
    </div>

    <!-- 评论表单 (置于顶部) -->
    <div class="comment-form-container" v-show="showCommentForm" :class="{ 'form-visible': showCommentForm }">
      <comment-form
        @submit="handleSubmitComment"
        placeholder="发一条友善的评论"
        submit-text="发布"
      />
    </div>

    <!-- 评论列表 -->
    <div v-if="loading" class="comment-loading">
      <div class="loading-indicator">
        <i class="el-icon-loading"></i>
        <span>加载中...</span>
      </div>
    </div>

    <div v-else-if="error" class="comment-error">
      <el-alert :title="error" type="error" :closable="false" />
    </div>

    <div v-else-if="comments.length === 0" class="comment-empty">
      <div class="empty-container">
        <i class="el-icon-chat-line-round empty-icon"></i>
        <p class="empty-text">还没有评论，来抢沙发吧！</p>
      </div>
    </div>

    <div v-else class="comment-list">
      <div v-for="comment in rootComments" :key="comment.id" class="comment-group">
        <!-- 使用递归组件渲染评论树 -->
        <comment-reply-tree
          :comment="comment"
          :default-avatar="defaultAvatar"
          :is-reply="false"
          :root-comment-id="comment.id"
          :get-replies-for-comment="getRepliesForComment"
          :is-replies-expanded="isRepliesExpanded"
          :toggle-replies="toggleReplies"
          :handle-reply="handleReply"
          :handle-submit-reply="handleSubmitReply"
          :has-more-replies="hasMoreReplies"
          :load-more-replies="loadMoreReplies"
        />
      </div>

      <!-- 加载更多按钮 -->
      <div v-if="(hasMore || totalComments.value > rootComments.length) && comments.length > 0" class="load-more">
        <button class="load-more-btn" @click="loadMore" :class="{ 'loading': loadingMore }" :disabled="loadingMore">
          <div class="btn-content">
            <i v-if="loadingMore" class="el-icon-loading"></i>
            <span v-else>查看更多评论</span>
          </div>
          <div class="btn-ripple" v-if="loadingMore"></div>
        </button>
        <div class="comment-count-info">
          <span>已加载 {{ rootComments.length }}/{{ totalComments }} 条评论</span>
        </div>
      </div>

      <div v-else-if="comments.length > 0" class="no-more">
        <div class="divider"></div>
        <span class="no-more-text">没有更多评论了</span>
        <div class="divider"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { ElMessage } from 'element-plus';
import CommentItem from './CommentItem.vue';
import CommentForm from './CommentForm.vue';
import CommentReplyTree from './CommentReplyTree.vue';
import { processDouyinStyleComments } from '@/utils/commentUtils';
import defaultAvatar from '@/assets/images/avatar.png';
import { getCommentList, addComment, replyComment } from '@/api/comment';

export default {
  name: 'CommentList',
  components: {
    CommentItem,
    CommentForm,
    CommentReplyTree
  },
  props: {
    postId: {
      type: [Number, String],
      required: true
    },
    initialComments: {
      type: Array,
      default: () => []
    },
    initialLoading: {
      type: Boolean,
      default: false
    },
    initialError: {
      type: String,
      default: ''
    }
  },
  emits: ['load-comments', 'submit-comment', 'submit-reply'],
  setup(props, { emit }) {
    const comments = ref(props.initialComments || []);
    const loading = ref(props.initialLoading);
    const error = ref(props.initialError);
    const page = ref(1);
    const pageSize = ref(10);
    const hasMore = ref(true); // 是否有更多一级评论
    const loadingMore = ref(false);
    const totalComments = ref(0);
    const showCommentForm = ref(false); // 控制评论表单的显示/隐藏
    const sortBy = ref('newest'); // 排序方式：'newest' 或 'hottest'

    // 处理评论列表，使用抖音风格（所有评论在同一层级）
    const processComments = () => {
      // 将评论数组转换为抖音风格
      comments.value = processDouyinStyleComments(comments.value);
    };

    // 初始化时处理评论列表
    if (props.initialComments && props.initialComments.length > 0) {
      comments.value = processDouyinStyleComments(props.initialComments);
    }

    // 记录已展开的回复
    const expandedReplies = ref(new Set());

    // 初始化展开状态：默认所有回复都展开，只有当回复超过20条时才默认收起
    const initializeExpandedReplies = () => {
      // 清空已展开的回复集合
      expandedReplies.value.clear();

      // 递归处理评论及其回复
      const processCommentReplies = (commentId) => {
        const replies = getRepliesForComment(commentId);

        // 如果回复数量不超过20条，则默认展开
        if (replies.length <= 20) {
          expandedReplies.value.add(commentId);
        }
        // 否则默认收起

        // 递归处理每个回复的回复
        replies.forEach(reply => {
          processCommentReplies(reply.id);
        });
      };

      // 遍历所有一级评论
      comments.value.forEach(comment => {
        if (!comment.is_reply) { // 只处理一级评论
          processCommentReplies(comment.id);
        }
      });
    };

    // 获取根评论（非回复的评论，即一级评论）
    const rootComments = computed(() => {
      // 先过滤出所有一级评论
      const rootCommentsArray = comments.value.filter(comment => !comment.is_reply);

      // 根据当前页码和每页显示数量返回对应的一级评论
      // 这里显示所有已加载的评论，实现移步分页效果
      const start = 0; // 从第一条开始
      const end = rootCommentsArray.length; // 显示所有已加载的评论

      console.log(`显示一级评论: ${start} - ${end}, 总数: ${rootCommentsArray.length}, 当前页: ${page.value}`);
      return rootCommentsArray;
    });

    // 一级评论的总数
    const rootCommentsTotal = ref(0);

    // 获取指定评论的回复
    const getRepliesForComment = (commentId) => {
      // 只返回直接回复该评论的评论，或者根评论 ID 为该评论的评论
      return comments.value.filter(comment =>
        comment.is_reply && (
          // 直接回复该评论
          comment.parent_id === commentId ||
          // 或者根评论 ID 为该评论
          (comment.root_comment_id === commentId && comment.parent_id !== commentId)
        )
      );
    };

    // 检查是否有更多回复
    const hasMoreReplies = (commentId) => {
      // 这里可以根据实际情况判断是否有更多回复
      // 例如，如果当前显示的回复数量小于该评论的总回复数
      const replies = getRepliesForComment(commentId);
      const comment = comments.value.find(c => c.id === commentId);
      return comment && comment.reply_count && replies.length < comment.reply_count;
    };

    // 加载更多回复
    const loadMoreReplies = async (commentId) => {
      try {
        // 调用API获取指定评论的更多回复
        const response = await getCommentList({
          post_id: props.postId,
          parent_id: commentId, // 加载指定评论的直接回复
          page: 1,
          page_size: 50 // 一次加载较多的回复
        });

        if (response && response.code === 200) {
          // 处理回复数据
          const replies = response.data.list || [];

          if (replies.length > 0) {
            // 为每个回复设置根评论 ID
            const processedReplies = replies.map(reply => ({
              ...reply,
              root_comment_id: commentId,
              is_reply: true
            }));

            // 将回复添加到现有评论中，然后重新处理成抖音风格
            const newComments = [...comments.value, ...processedReplies];
            comments.value = processDouyinStyleComments(newComments);

            // 自动展开该评论的回复
            expandedReplies.value.add(commentId);
          }
        }
      } catch (error) {
        console.error('Error loading more replies:', error);
      }
    };

    // 加载评论
    const loadComments = async (isLoadMore = false) => {
      if (!isLoadMore) {
        loading.value = true;
        page.value = 1;
        // 初始化时默认设置为有更多评论，等待数据加载后再判断
        hasMore.value = true;
        console.log(`加载评论，排序方式: ${sortBy.value}`);
      } else {
        loadingMore.value = true;
      }

      error.value = '';

      try {
        // 触发加载评论事件
        emit('load-comments', {
          postId: props.postId,
          page: page.value,
          pageSize: pageSize.value, // 每页显示 10 条评论
          parent_id: 0, // 只加载一级评论（parent_id = 0 的评论）
          sort_by: sortBy.value, // 排序方式: newest 或 hottest
          callback: (result) => {
            if (result.success) {
              if (isLoadMore) {
                // 追加评论
                const newComments = [...comments.value, ...result.data.list];
                comments.value = processDouyinStyleComments(newComments);
              } else {
                // 替换评论
                comments.value = processDouyinStyleComments(result.data.list);
              }

              // 设置评论总数
              totalComments.value = result.data.total || comments.value.length;

              // 设置一级评论总数（只计算 parent_id = 0 的评论）
              if (result.data.rootTotal !== undefined) {
                // 如果 API 返回了一级评论总数，直接使用
                rootCommentsTotal.value = result.data.rootTotal;
              } else {
                // 否则计算一级评论的总数
                const currentRootComments = comments.value.filter(comment => !comment.is_reply);
                rootCommentsTotal.value = currentRootComments.length;

                // 如果返回的数据中有 has_more 字段，则表示还有更多一级评论
                if (result.data.hasMore) {
                  rootCommentsTotal.value += 10; // 至少还有一页
                }
              }

              // 当一级评论总数大于已加载的一级评论数量时，显示"查看更多评论"按钮
              const loadedRootComments = comments.value.filter(comment => !comment.is_reply);

              // 如果返回的数据中有 has_more 字段，优先使用该字段
              if (result.data.hasMore !== undefined) {
                hasMore.value = result.data.hasMore;
              } else {
                // 否则根据总数和已加载数量判断
                hasMore.value = rootCommentsTotal.value > loadedRootComments.length;
              }

              // 确保当总评论数大于页面大小时显示加载更多按钮
              if (totalComments.value > pageSize.value && loadedRootComments.length < totalComments.value) {
                hasMore.value = true;
              }

              if (isLoadMore) {
                page.value++;
              }

              // 初始化展开状态
              initializeExpandedReplies();
            } else {
              error.value = result.message || 'Failed to load comments';
            }

            loading.value = false;
            loadingMore.value = false;
          }
        });
      } catch (err) {
        console.error('Error loading comments:', err);
        error.value = 'Failed to load comments';
        loading.value = false;
        loadingMore.value = false;
      }
    };

    // 加载更多评论
    const loadMore = async () => {
      if (hasMore.value && !loadingMore.value) {
        loadingMore.value = true;
        page.value++; // 增加页码，加载下一页

        try {
          // 调用API获取更多一级评论
          console.log(`加载更多评论，页码: ${page.value}, 排序方式: ${sortBy.value}`);
          const response = await getCommentList( {
            post_id: props.postId,
            page: page.value,
            page_size: pageSize.value,
            parent_id: 0, // 只获取一级评论（parent_id = 0 的评论）
            sort_by: sortBy.value // 排序方式: newest 或 hottest
          });

          if (response && response.code === 200) {
            // 处理评论数据
            const newComments = response.data.list || [];

            if (newComments.length > 0) {
              // 将新评论添加到现有评论中，然后重新处理成抖音风格
              // 注意：这里不能简单地将新评论添加到现有评论中
              // 而是需要先过滤出所有非回复的评论，然后只保留前 N 页的一级评论
              // 再加上所有的回复

              // 先过滤出所有的回复
              const allReplies = comments.value.filter(comment => comment.is_reply);

              // 再过滤出所有的一级评论
              const oldRootComments = comments.value.filter(comment => !comment.is_reply);

              // 将新的一级评论添加到现有的一级评论中
              const mergedRootComments = [...oldRootComments, ...newComments.filter(comment => !comment.is_reply)];

              // 将新的回复添加到现有的回复中
              const allNewReplies = [...allReplies, ...newComments.filter(comment => comment.is_reply)];

              // 合并所有评论
              const allComments = [...mergedRootComments, ...allNewReplies];

              // 添加加载动画标记
              const newCommentsWithAnimation = newComments.filter(comment => !comment.is_reply).map(comment => ({
                ...comment,
                _isNew: true // 添加标记，用于动画效果
              }));

              // 将新评论与旧评论合并
              const mergedCommentsWithAnimation = [
                ...oldRootComments,
                ...newCommentsWithAnimation,
                ...allReplies
              ];

              // 处理成抖音风格
              comments.value = processDouyinStyleComments(mergedCommentsWithAnimation);

              // 更新总数
              totalComments.value = response.data.total || allComments.length;

              // 延迟一段时间后移除动画标记
              setTimeout(() => {
                comments.value = comments.value.map(comment => ({
                  ...comment,
                  _isNew: false
                }));
              }, 1000);

              // 平滑滚动到新加载的评论
              setTimeout(() => {
                // 找到第一个新加载的评论
                const firstNewComment = document.querySelector('.new-comment');
                if (firstNewComment) {
                  // 平滑滚动到该评论
                  firstNewComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
              }, 200);

              // 更新一级评论总数
              if (response.data.rootTotal !== undefined) {
                rootCommentsTotal.value = response.data.rootTotal;
              } else {
                // 计算一级评论的总数
                const filteredRootComments = allComments.filter(comment => !comment.is_reply);
                rootCommentsTotal.value = filteredRootComments.length;

                // 如果还有更多一级评论，增加一级评论总数
                if (response.data.hasMore) {
                  rootCommentsTotal.value += 10; // 至少还有一页
                }
              }

              // 当一级评论总数大于已加载的一级评论数量时，显示"查看更多评论"按钮
              const loadedRootComments = allComments.filter(comment => !comment.is_reply);

              // 如果返回的数据中有 has_more 字段，优先使用该字段
              if (response.data.hasMore !== undefined) {
                hasMore.value = response.data.hasMore;
              } else {
                // 否则根据总数和已加载数量判断
                hasMore.value = rootCommentsTotal.value > loadedRootComments.length;
              }

              // 确保当总评论数大于页面大小时显示加载更多按钮
              if (totalComments.value > pageSize.value && loadedRootComments.length < totalComments.value) {
                hasMore.value = true;
              }

              // 初始化展开状态
              initializeExpandedReplies();
            } else {
              // 没有更多评论
              hasMore.value = false;
            }
          } else {
            console.error('Error loading more comments:', response?.msg);
            hasMore.value = false;
          }
        } catch (error) {
          console.error('Error loading more comments:', error);
          hasMore.value = false;
        } finally {
          loadingMore.value = false;
        }
      }
    };

    // 处理评论提交
    const handleSubmitComment = (commentData) => {
      console.log('CommentList: handleSubmitComment called with data:', commentData);

      // 强制重置全局状态，确保可以提交
      if (window._isSubmittingComment) {
        console.log('CommentList: Found stale global state, resetting _isSubmittingComment from', window._isSubmittingComment, 'to null');
        window._isSubmittingComment = null;
      }

      // 设置正在处理状态
      window._isSubmittingComment = 'processing';
      console.log('CommentList: Set _isSubmittingComment to "processing"');

      // 显示加载状态
      loading.value = true;

      // 添加标记，防止重复加载
      const submitId = Date.now();
      window._lastCommentSubmitId = submitId;
      console.log('CommentList: Set _lastCommentSubmitId to', submitId);

      emit('submit-comment', {
        ...commentData,
        postId: props.postId,
        callback: (result) => {
          console.log('CommentList: Received callback with result:', result);

          // 重置按钮状态
          const formElement = document.querySelector('.comment-form');
          if (formElement) {
            const submitButton = formElement.querySelector('.submit-button');
            if (submitButton && submitButton.__vue__) {
              submitButton.__vue__.submitting = false;
              console.log('CommentList: Reset button submitting state');
            }
          }

          // 重置全局标记
          window._isSubmittingComment = null;
          console.log('CommentList: Reset _isSubmittingComment to null');

          if (result.success) {
            // 如果评论需要审核，不添加到列表
            if (result.needAudit) {
              // 提交成功后隐藏评论表单
              showCommentForm.value = false;
              ElMessage({
                message: '评论已提交，等待审核通过后显示',
                type: 'success',
                duration: 3000
              });
              loading.value = false;
              return;
            }

            // 提交成功后隐藏评论表单
            showCommentForm.value = false;

            // 显示成功提示，并添加动画效果
            setTimeout(() => {
              ElMessage({
                message: '评论发表成功',
                type: 'success',
                duration: 2000,
                onClose: () => {
                  // 添加滚动到新评论的效果
                  if (rootComments.value.length > 0) {
                    const firstComment = document.querySelector('.comment-item');
                    if (firstComment) {
                      firstComment.classList.add('highlight-comment');
                      setTimeout(() => {
                        firstComment.classList.remove('highlight-comment');
                      }, 3000);
                    }
                  }
                }
              });
            }, 500);

            // 添加新评论到列表
            if (result.data) {
              const newComments = [result.data, ...comments.value];
              comments.value = processDouyinStyleComments(newComments);
              totalComments.value++;

              // 初始化展开状态
              initializeExpandedReplies();
              loading.value = false;
            } else {
              // 如果没有返回评论数据，添加延迟后重新加载评论
              setTimeout(() => {
                // 检查是否是最后一次提交，防止重复加载
                if (window._lastCommentSubmitId === submitId) {
                  console.log('CommentList: Loading comments after submit');
                  loadComments();
                } else {
                  console.log('CommentList: Skipping load, not the last submit');
                }
              }, 1000);
            }
          } else {
            // 显示错误提示
            ElMessage({
              message: result.message || '发表评论失败，请重试',
              type: 'error',
              duration: 3000
            });
            loading.value = false;
          }
        }
      });
    };

    // 处理回复提交
    const handleSubmitReply = (replyData) => {
      console.log('CommentList: handleSubmitReply called');

      // 强制重置全局状态，确保可以提交
      if (window._isSubmittingComment) {
        console.log('CommentList: Found stale global state in reply, resetting _isSubmittingComment from', window._isSubmittingComment, 'to null');
        window._isSubmittingComment = null;
      }

      // 设置正在处理状态
      window._isSubmittingComment = 'processing';
      console.log('CommentList: Set _isSubmittingComment to "processing" for reply');

      // 显示加载状态
      loadingMore.value = true;

      // 添加标记，防止重复加载
      const submitId = Date.now();
      window._lastReplySubmitId = submitId;

      emit('submit-reply', {
        ...replyData,
        postId: props.postId,
        callback: (result) => {
          // 重置全局标记
          window._isSubmittingComment = null;
          console.log('CommentList: Reset _isSubmittingComment to null in reply callback');

          if (result.success) {
            // 如果回复需要审核，不添加到列表
            if (result.needAudit) {
              ElMessage({
                message: '回复已提交，等待审核通过后显示',
                type: 'success',
                duration: 3000
              });
              loadingMore.value = false;
              return;
            }

            // 显示成功提示，并添加动画效果
            setTimeout(() => {
              ElMessage({
                message: '回复发表成功',
                type: 'success',
                duration: 2000
              });
            }, 500);

            // 添加新回复到列表
            if (result.data) {
              // 将回复添加到现有评论中，然后重新处理成抖音风格
              const newComments = [...comments.value, result.data];
              comments.value = processDouyinStyleComments(newComments);
              totalComments.value++;

              // 自动展开该评论的回复
              if (replyData.root_comment_id) {
                expandedReplies.value.add(replyData.root_comment_id);
              } else if (replyData.parent_id) {
                expandedReplies.value.add(replyData.parent_id);
              }

              // 初始化展开状态
              initializeExpandedReplies();
              loadingMore.value = false;
            } else {
              // 如果没有返回回复数据，添加延迟后重新加载评论
              setTimeout(() => {
                // 检查是否是最后一次提交，防止重复加载
                if (window._lastReplySubmitId === submitId) {
                  console.log('CommentList: Loading comments after reply');
                  loadComments();
                } else {
                  console.log('CommentList: Skipping load, not the last reply');
                }
              }, 1000);
            }
          } else {
            // 显示错误提示
            ElMessage({
              message: result.message || '发表回复失败，请重试',
              type: 'error',
              duration: 3000
            });
            loadingMore.value = false;
          }
        }
      });
    };

    // 处理回复按钮点击
    const handleReply = (comment) => {
      // 这个函数只是传递事件，实际处理在CommentItem组件中
    };

    // 判断指定评论的回复是否已展开
    const isRepliesExpanded = (commentId) => {
      return expandedReplies.value.has(commentId);
    };

    // 切换回复的展开/收起状态
    const toggleReplies = (commentId) => {
      if (expandedReplies.value.has(commentId)) {
        expandedReplies.value.delete(commentId);
      } else {
        expandedReplies.value.add(commentId);
      }
    };

    // 处理查看回复事件
    const handleViewReplies = async (data) => {
      if (!data || !data.commentId) return;

      // 设置加载状态
      loading.value = true;

      try {
        // 调用API获取指定评论的回复
        const response = await getCommentList( {
          post_id: props.postId,
          parent_id: data.commentId, // 加载指定评论的直接回复
          page: 1,
          page_size: 50 // 一次加载较多的回复
        });

        if (response && response.code === 200) {
          // 处理回复数据
          const replies = response.data.list || [];

          if (replies.length > 0) {
            // 为每个回复设置根评论 ID
            const processedReplies = replies.map(reply => ({
              ...reply,
              root_comment_id: data.commentId,
              is_reply: true
            }));

            // 将回复添加到现有评论中，然后重新处理成抖音风格
            const newComments = [...comments.value, ...processedReplies];
            comments.value = processDouyinStyleComments(newComments);

            // 更新评论总数
            totalComments.value += processedReplies.length;

            // 自动展开该评论的回复
            expandedReplies.value.add(data.commentId);
          }
        }
      } catch (error) {
        console.error('Error loading replies:', error);
      } finally {
        loading.value = false;
      }
    };

    // 组件挂载时加载评论
    onMounted(() => {
      // 重置全局状态
      window._isSubmittingComment = null;
      console.log('CommentList: Reset _isSubmittingComment to null on mount');

      if (!props.initialComments || props.initialComments.length === 0) {
        loadComments();
      } else {
        comments.value = props.initialComments;
        totalComments.value = props.initialComments.length;

        // 判断是否有更多评论
        const rootCommentsCount = props.initialComments.filter(comment => !comment.is_reply).length;
        hasMore.value = totalComments.value > rootCommentsCount || totalComments.value > pageSize.value;

        // 初始化展开状态
        initializeExpandedReplies();
      }
    });

    // 监听 comments 变化，自动调用 initializeExpandedReplies 函数
    watch(comments, () => {
      initializeExpandedReplies();
    });

    // 切换评论表单的显示/隐藏
    const toggleCommentForm = () => {
      showCommentForm.value = !showCommentForm.value;
    };

    // 切换评论排序方式
    const changeSortOrder = (order) => {
      if (sortBy.value === order) return; // 如果点击当前排序方式，不做任何操作

      console.log(`切换排序方式：${sortBy.value} -> ${order}`);

      // 显示切换排序方式的提示
      if (order === 'newest') {
        ElMessage.success('已切换为按时间排序');
      } else if (order === 'hottest') {
        ElMessage.success('已切换为按热度排序');
      }

      sortBy.value = order;
      page.value = 1; // 重置页码
      comments.value = []; // 清空当前评论列表
      loadComments(); // 重新加载评论
    };

    return {
      comments,
      rootComments,
      rootCommentsTotal,
      loading,
      error,
      hasMore,
      loadingMore,
      totalComments,
      defaultAvatar,
      expandedReplies,
      processComments,
      loadComments,
      loadMore,
      handleSubmitComment,
      handleSubmitReply,
      handleReply,
      handleViewReplies,
      getRepliesForComment,
      hasMoreReplies,
      loadMoreReplies,
      isRepliesExpanded,
      toggleReplies,
      initializeExpandedReplies,
      // 评论表单显示/隐藏控制
      showCommentForm,
      toggleCommentForm,
      // 排序方式
      sortBy,
      changeSortOrder
    };
  }
}
</script>

<style scoped>
/* 评论区容器 */
.comment-list-container {
  margin-top: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background-color: var(--card-bg-color, #fff);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* 评论区头部 */
.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color, #f0f0f0);
  text-align: left;
  transition: border-color 0.3s ease;
}

/* 评论操作区 */
.comment-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* 发表评论按钮 */
.post-comment-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  background-color: #fe2c55;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(254, 44, 85, 0.2);
}

.post-comment-btn i {
  margin-right: 6px;
}

.post-comment-btn:hover {
  background-color: #e62548;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(254, 44, 85, 0.3);
}

.post-comment-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(254, 44, 85, 0.2);
}

.comment-count {
  display: flex;
  align-items: center;
  font-size: 16px;
  font-weight: 600;
  color: #333;
  transition: color 0.3s ease;
}

.comment-count i {
  font-size: 20px;
  margin-right: 8px;
  color: #fe2c55;
}

.comment-count span {
  margin-right: 4px;
}

.comment-text {
  color: #666;
  font-weight: normal;
  transition: color 0.3s ease;
}

.comment-sort {
  display: flex;
  align-items: center;
  font-size: 14px;
}

.sort-label {
  color: #999;
  margin-right: 8px;
  transition: color 0.3s ease;
}

.sort-option {
  margin-left: 12px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s ease, color 0.3s ease, background-color 0.3s ease;
  padding: 4px 8px;
  border-radius: 4px;
  position: relative;
}

.sort-option:hover {
  color: #fe2c55;
  background-color: rgba(254, 44, 85, 0.05);
}

.sort-option.active {
  color: #fe2c55;
  font-weight: 500;
  background-color: rgba(254, 44, 85, 0.1);
}

.sort-option.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 16px;
  height: 2px;
  background-color: #fe2c55;
  border-radius: 2px;
}

.sort-option.disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.sort-option .el-icon-loading {
  font-size: 12px;
  margin-right: 4px;
  color: #fe2c55;
}

/* 评论表单 */
.comment-form-container {
  margin-bottom: 24px;
  background-color: #f8f8f8;
  border-radius: 8px;
  width: 100%;
  box-sizing: border-box;
  text-align: left;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: all 0.5s ease;
  padding: 0 16px;
}

.comment-form-container.form-visible {
  max-height: 500px;
  opacity: 1;
  padding: 16px;
  margin-top: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* 暗黑模式和跟随系统模式下评论表单样式 */
html.dark .comment-form-container,
html[data-theme="dark"] .comment-form-container {
  background-color: var(--card-bg-color, var(--el-bg-color, #1d1e1f));
  padding-left: 20px;
}

html.dark .comment-form-container.form-visible,
html[data-theme="dark"] .comment-form-container.form-visible {
  padding: 16px 16px 16px 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* 评论列表 */
.comment-list {
  margin-bottom: 20px;
}

/* 加载中状态 */
.comment-loading {
  display: flex;
  justify-content: center;
  padding: 30px 0;
}

.loading-indicator {
  display: flex;
  align-items: center;
  color: #999;
  font-size: 14px;
}

.loading-indicator i {
  margin-right: 8px;
  font-size: 18px;
  color: #fe2c55;
}

/* 空评论状态 */
.comment-empty {
  padding: 40px 0;
  text-align: center;
}

.empty-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon {
  font-size: 48px;
  color: #ddd;
  margin-bottom: 16px;
}

.empty-text {
  color: #999;
  font-size: 15px;
}

/* 评论组样式 */
.comment-group {
  margin-bottom: 24px;
  position: relative;
  width: 100%;
  box-sizing: border-box;
}

/* 评论回复组样式 */
.comment-replies-group {
  margin-top: 8px;
  margin-left: 40px; /* 头像宽度 + 右边距 */
  position: relative;
}

/* 查看更多回复按钮 */
.view-more-replies {
  color: #2f88ff;
  font-size: 13px;
  margin-top: 10px;
  margin-bottom: 5px;
  cursor: pointer;
  padding: 6px 12px;
  background-color: rgba(47, 136, 255, 0.05);
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s ease;
  margin-left: 0;
  width: 100%;
  justify-content: center;
}

.view-more-replies:hover {
  background-color: rgba(47, 136, 255, 0.1);
}

.view-more-replies:before {
  content: '\2193'; /* 下箭头 */
  margin-right: 6px;
  font-size: 14px;
}

/* 加载更多按钮 */
.load-more {
  text-align: center;
  margin: 30px 0;
  padding: 15px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  position: relative;
  transition: all 0.5s ease;
}

.load-more::before {
  content: '';
  position: absolute;
  top: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(254, 44, 85, 0.2), transparent);
  border-radius: 3px;
}

.load-more::after {
  content: '';
  position: absolute;
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(254, 44, 85, 0.2), transparent);
  border-radius: 3px;
}

.load-more-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  background-color: #f8f8f8;
  color: #333;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  border: 1px solid #e0e0e0;
  min-width: 160px;
  position: relative;
  overflow: hidden;
  outline: none;
}

.btn-content {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.load-more-btn:hover {
  background-color: #f0f0f0;
  color: #fe2c55;
  border-color: #fe2c55;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.load-more-btn:active {
  transform: translateY(0);
}

.load-more-btn:focus {
  box-shadow: 0 0 0 3px rgba(254, 44, 85, 0.2);
}

.load-more-btn.loading {
  cursor: not-allowed;
  background-color: #f8f8f8;
  color: #999;
  border-color: #e0e0e0;
  transform: none;
  box-shadow: none;
}

.load-more-btn.loading .el-icon-loading {
  color: #fe2c55;
  animation: spin 1s infinite linear;
}

.btn-ripple {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle, rgba(254, 44, 85, 0.1) 0%, rgba(254, 44, 85, 0) 70%);
  transform: scale(0);
  animation: ripple 1.5s infinite ease-out;
  z-index: 1;
}

@keyframes ripple {
  0% {
    transform: scale(0.5);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.5);
    opacity: 0.1;
  }
  100% {
    transform: scale(2.5);
    opacity: 0;
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.comment-count-info {
  font-size: 13px;
  color: #999;
  margin-top: 5px;
  padding: 4px 10px;
  background-color: rgba(0, 0, 0, 0.02);
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  transition: all 0.3s ease;
}

.comment-count-info span {
  position: relative;
}

.comment-count-info span::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: #ddd;
  transform: scaleX(0);
  transition: transform 0.3s ease;
  transform-origin: left;
}

.load-more:hover .comment-count-info span::after {
  transform: scaleX(1);
}

/* 没有更多评论 */
.no-more {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 30px 0;
  color: #999;
  font-size: 13px;
}

.divider {
  height: 1px;
  background-color: var(--border-color, #f0f0f0);
  flex: 1;
  margin: 0 15px;
  transition: background-color 0.3s ease;
}

.no-more-text {
  white-space: nowrap;
  transition: color 0.3s ease;
}

/* 高亮评论样式 */
@keyframes highlightComment {
  0% {
    background-color: rgba(254, 44, 85, 0.1);
    box-shadow: 0 0 10px rgba(254, 44, 85, 0.2);
  }
  70% {
    background-color: rgba(254, 44, 85, 0.1);
    box-shadow: 0 0 10px rgba(254, 44, 85, 0.2);
  }
  100% {
    background-color: transparent;
    box-shadow: none;
  }
}

.highlight-comment {
  animation: highlightComment 3s ease-out;
  border-radius: 8px;
}
</style>
