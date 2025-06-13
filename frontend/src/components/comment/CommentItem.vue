<template>
  <div class="comment-item" :id="`comment-${comment.id}`" :class="{ 'comment-reply': isReply }">
    <!-- 评论主体区域 -->
    <div class="comment-main">
      <!-- 头像区域 -->
      <div class="comment-avatar">
        <img :src="comment.avatar || defaultAvatar" :alt="comment.name" class="avatar-img">
      </div>

      <!-- 评论内容区 -->
      <div class="comment-body">
        <!-- 用户信息区 -->
        <div class="comment-user-info">
          <div class="comment-author">{{ comment.author || comment.username || comment.nickname || comment.name || $t('webPost.anonymous') }}</div>
          <div class="author-badge" v-if="comment.is_author">作者</div>
        </div>

        <!-- 评论内容 -->
        <div class="comment-content">
          <!-- 如果是回复别人的评论，显示被回复人 -->
          <div v-if="comment.reply_to_name" class="reply-to-info">
            <span class="reply-to-text">@{{ comment.reply_to_name }}</span>
            <span v-if="comment.reply_level > 1" class="reply-chain-indicator">· {{ comment.reply_level }}级回复</span>
          </div>

          <!-- 评论正文 -->
          <div v-if="comment.content" v-html="formattedContent" class="content-text"></div>
          <div v-else class="empty-content">该评论没有内容</div>
        </div>

        <!-- 评论元信息 -->
        <div class="comment-meta-info">
          <div class="comment-meta-left">
            <div class="comment-time">{{ formatTime(comment.create_time) }}</div>

            <!-- 显示回复数量（只在有回复时显示） -->
            <div v-if="replyCount > 0" class="reply-count" @click="toggleReplies">
              <i class="el-icon-chat-dot-square"></i>
              <span>{{ isRepliesExpanded ? '收起回复' : `展开${replyCount}条回复` }}</span>
            </div>
          </div>

          <!-- 互动区 -->
          <div class="comment-actions">
            <!-- 点赞按钮（抖音风格） -->
            <div class="action-item like-btn" :class="{ 'liked': isLiked }" @click="toggleLike" role="button" tabindex="0">
              <div class="like-content">
                <div class="like-icon-container">
                  <div class="heart-icon" :class="{ 'is-liked': isLiked }">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor" />
                    </svg>
                  </div>
                  <div class="like-animation" v-if="showLikeAnimation"></div>
                </div>
                <span class="like-count" v-if="likeCount > 0 || isLiked">{{ likeCount }}</span>
              </div>
            </div>

            <!-- 回复按钮 -->
            <div class="action-item reply-btn" @click="handleReply">
              <el-icon><ChatLineRound /></el-icon>
              <span>{{ $t('webPost.reply') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 回复表单 -->
    <div class="comment-reply-form" v-if="showReplyForm">
      <slot name="reply-form" :comment="comment" :cancel-reply="cancelReply"></slot>
    </div>

    <!-- 抖音风格不需要嵌套回复列表，所有评论在同一层级 -->
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { formatCommentTime } from '@/utils/timeUtils';
import { countTotalReplies } from '@/utils/commentUtils';
import DOMPurify from 'dompurify';
import * as marked from 'marked';
import { likeComment, unlikeComment } from '@/api/commentLike';
import { ElMessage } from 'element-plus';
import { getCurrentVisitorId } from '@/utils/visitorId';
import { ChatLineRound } from '@element-plus/icons-vue';

export default {
  name: 'CommentItem',
  props: {
    comment: {
      type: Object,
      required: true
    },
    defaultAvatar: {
      type: String,
      required: true
    },
    isReply: {
      type: Boolean,
      default: false
    },
    replyCount: {
      type: Number,
      default: 0
    },
    isRepliesExpanded: {
      type: Boolean,
      default: false
    }
  },
  emits: ['reply', 'toggle-replies'],
  setup(props, { emit }) {
    const showReplyForm = ref(false);
    const isLiked = ref(false); // 是否已点赞
    const likeCount = ref(props.comment.like_count || 0); // 点赞数
    const showLikeAnimation = ref(false); // 点赞动画显示状态

    // 初始化时检查本地存储中的点赞状态
    onMounted(() => {
      const likedComments = JSON.parse(localStorage.getItem('likedComments') || '{}');
      if (likedComments[props.comment.id]) {
        isLiked.value = true;
      }
    });



    // 点赞功能
    const toggleLike = async () => {
      try {
        // 防止重复点击
        if (window._isLikingComment) {
          return;
        }
        window._isLikingComment = true;

        // 乐观更新 UI
        const previousLikeState = isLiked.value;
        const previousLikeCount = likeCount.value;

        // 更新点赞状态
        if (isLiked.value) {
          likeCount.value = Math.max(0, likeCount.value - 1); // 确保不会出现负数
        } else {
          likeCount.value++;
          // 显示点赞动画
          showLikeAnimation.value = true;
          setTimeout(() => {
            showLikeAnimation.value = false;
          }, 1000); // 动画持续1秒
        }

        // 切换点赞状态
        isLiked.value = !isLiked.value;

        // 将点赞状态保存到本地存储
        const likedComments = JSON.parse(localStorage.getItem('likedComments') || '{}');
        if (isLiked.value) {
          likedComments[props.comment.id] = true;
        } else {
          delete likedComments[props.comment.id];
        }
        localStorage.setItem('likedComments', JSON.stringify(likedComments));

        // 发送请求到后端更新点赞状态
        const commentId = props.comment.id;
        const apiCall = isLiked.value ? likeComment : unlikeComment;
        const response = await apiCall({ comment_id: commentId });

        if (response && response.code === 200) {
          console.log(`Comment ${commentId} ${isLiked.value ? 'liked' : 'unliked'} successfully, count: ${likeCount.value}`);
        } else {
          // 如果 API 调用失败，恢复之前的状态
          console.error('Failed to update like status:', response?.msg || 'Unknown error');
          isLiked.value = previousLikeState;
          likeCount.value = previousLikeCount;

          // 更新本地存储
          const likedComments = JSON.parse(localStorage.getItem('likedComments') || '{}');
          if (previousLikeState) {
            likedComments[props.comment.id] = true;
          } else {
            delete likedComments[props.comment.id];
          }
          localStorage.setItem('likedComments', JSON.stringify(likedComments));

          // 显示错误提示
          ElMessage.error(response?.msg || '操作失败，请稍后再试');
        }
      } catch (error) {
        console.error('Error toggling like:', error);
        ElMessage.error('网络错误，请稍后再试');
      } finally {
        window._isLikingComment = false;
      }
    };

    // 格式化评论内容（支持简单文本格式化）
    const formattedContent = computed(() => {
      console.log('Comment content:', props.comment.content);

      if (!props.comment.content) {
        console.log('No content found for comment:', props.comment.id);
        return '<span style="color:#999;">评论内容为空</span>';
      }

      try {
        // 简单格式化文本，处理换行和空格
        const formatted = props.comment.content
          .replace(/\n/g, '<br>')
          .replace(/\s\s/g, '&nbsp;&nbsp;');
        return formatted;
      } catch (error) {
        console.error('Error with comment content:', error);
        return `<span style="color:#999;">内容格式化错误</span>`;
      }
    });

    // 处理回复按钮点击
    const handleReply = () => {
      // 切换回复表单的显示状态
      showReplyForm.value = !showReplyForm.value;

      // 只在显示回复表单时触发事件
      if (showReplyForm.value) {
        emit('reply', props.comment);
      }
    };

    // 取消回复
    const cancelReply = () => {
      showReplyForm.value = false;
    };

    // 切换回复的展开/收起状态
    const toggleReplies = () => {
      emit('toggle-replies', props.comment.id);
    };



    // 格式化时间
    const formatTime = (time) => {
      if (!time) {
        return '未知时间';
      }

      // 打印原始时间字符串和评论信息，用于调试
      console.log('Comment time:', time, typeof time);
      console.log('Comment ID:', props.comment.id);

      // 直接使用 formatCommentTime 函数格式化时间
      try {
        // 如果时间是数字字符串，尝试将其转换为数字
        let timeValue = time;

        // 特殊处理大于当前时间的时间戳（可能是毫秒级被当成秒级）
        if (typeof time === 'number' || (typeof time === 'string' && !isNaN(Number(time)))) {
          timeValue = Number(time);

          // 检查是否是未来时间
          const now = new Date();
          const testDate = new Date(timeValue * 1000); // 假设是秒级时间戳

          if (testDate.getTime() > now.getTime()) {
            // 如果是未来时间，则可能是毫秒级被当成秒级
            console.log('Future date detected, treating as milliseconds:', timeValue);
            timeValue = timeValue / 1000; // 将毫秒级转换为秒级
          }

          // 如果是秒级时间戳，转换为毫秒级
          if (timeValue < 10000000000) {
            timeValue *= 1000;
          }
        }

        const formattedTime = formatCommentTime(timeValue);
        console.log('Formatted time:', formattedTime);
        return formattedTime;
      } catch (error) {
        console.error('格式化时间出错:', error, time);
        return time;
      }
    };

    return {
      showReplyForm,
      isLiked,
      likeCount,
      showLikeAnimation,  // 添加点赞动画状态变量
      formattedContent,
      handleReply,
      cancelReply,
      toggleReplies,
      toggleLike,
      formatTime
    };
  }
}
</script>

<style scoped>
/* 评论项基本样式 */
.comment-item {
  margin-bottom: 16px;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  width: 100%;
  box-sizing: border-box;
}

.comment-item:last-child {
  border-bottom: none;
}

.comment-item:target {
  background-color: #fafafa;
  transition: background-color 0.3s ease;
}

/* 回复评论样式 - 抖音风格 */
.comment-reply {
  margin-top: 8px;
  margin-bottom: 8px;
  position: relative;
  padding: 8px;
  border-bottom: none;
  background-color: var(--bg-color-light, #fafafa);
  border-radius: 8px;
  width: 100%;
  box-sizing: border-box;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* 评论主体区域 */
.comment-main {
  display: flex;
  align-items: flex-start;
}

/* 头像区域 */
.comment-avatar {
  margin-right: 12px;
  flex-shrink: 0;
}

.avatar-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid #f0f0f0;
  transition: all 0.2s ease, border-color 0.3s ease;
}

/* 回复评论的头像更小 */
.comment-reply .avatar-img {
  width: 32px;
  height: 32px;
}

/* 评论内容区 */
.comment-body {
  flex: 1;
  overflow: hidden;
  width: calc(100% - 52px); /* 头像宽度 + 右边距 */
  box-sizing: border-box;
}

/* 用户信息区 */
.comment-user-info {
  display: flex;
  align-items: center;
  margin-bottom: 4px;
  width: 100%;
  box-sizing: border-box;
}

.comment-author {
  font-weight: 500;
  color: #333;
  font-size: 14px;
  margin-right: 6px;
  transition: color 0.3s ease;
}

.author-badge {
  background-color: #fe2c55;
  color: white;
  font-size: 10px;
  padding: 1px 4px;
  border-radius: 2px;
  margin-left: 4px;
}

/* 评论内容 */
.comment-content {
  margin-bottom: 8px;
  line-height: 1.5;
  word-break: break-word;
  font-size: 15px;
  color: #111;
  text-align: left;
  width: 100%;
  box-sizing: border-box;
  transition: color 0.3s ease;
}

/* 回复评论的内容字体更小 */
.comment-reply .comment-content {
  font-size: 14px;
  margin-bottom: 6px;
}

/* 回复评论的用户信息区间距更小 */
.comment-reply .comment-user-info {
  margin-bottom: 3px;
}

/* 回复评论的作者名字体更小 */
.comment-reply .comment-author {
  font-size: 13px;
}

.reply-to-info {
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.reply-to-text {
  color: #2f88ff;
  font-weight: 500;
  transition: color 0.3s ease;
}

.reply-chain-indicator {
  color: #999;
  font-size: 12px;
  margin-left: 5px;
  transition: color 0.3s ease;
}

.content-text {
  white-space: pre-wrap;
  text-align: left;
  display: block;
}

.empty-content {
  color: #999;
  font-style: italic;
  font-size: 13px;
  transition: color 0.3s ease;
}

/* 评论元信息 */
.comment-meta-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  color: #999;
  margin-top: 6px;
  transition: color 0.3s ease;
}

.comment-meta-left {
  display: flex;
  align-items: center;
}

.comment-time {
  font-size: 12px;
  color: #999;
  margin-right: 12px;
  transition: color 0.3s ease;
}

/* 回复数量样式 */
.reply-count {
  font-size: 12px;
  color: #2f88ff;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.2s ease, color 0.3s ease;
}

.reply-count:hover {
  color: #1c7ed6;
}

.reply-count i {
  font-size: 14px;
  margin-right: 4px;
}

/* 回复评论的元信息区域更紧凑 */
.comment-reply .comment-meta-info {
  margin-top: 4px;
  font-size: 11px;
}

.comment-reply .comment-time {
  font-size: 11px;
  margin-right: 8px;
}



/* 互动区 */
.comment-actions {
  display: flex;
  align-items: center;
}

.action-item {
  display: flex;
  align-items: center;
  margin-left: 20px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s ease;
}

.action-item:first-child {
  margin-left: 0;
}

.action-item i, .action-item .el-icon {
  font-size: 16px;
  margin-right: 4px;
}

.action-item:hover {
  color: #fe2c55;
}

/* 回复评论的交互区域更紧凑 */
.comment-reply .action-item {
  margin-left: 12px;
}

.comment-reply .action-item i, .comment-reply .action-item .el-icon {
  font-size: 14px;
  margin-right: 3px;
}

/* 抖音风格点赞按钮 */
.like-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  color: #666;
  cursor: pointer;
}

.like-btn:hover .like-icon-container, .like-btn:focus .like-icon-container {
  background-color: rgba(254, 44, 85, 0.1);
  outline: none;
}

.like-btn:hover .heart-icon {
  color: #fe2c55;
}

.like-btn:focus {
  outline: none;
}

.like-icon-container {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 24px;
  height: 24px;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 50%;
  padding: 4px;
  transition: all 0.2s ease;
}

/* 抖音风格的桃心图标 */
.heart-icon {
  width: 20px;
  height: 20px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  color: #aaa;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.heart-icon svg {
  width: 16px;
  height: 16px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.heart-icon.is-liked {
  color: #fe2c55;
}

.heart-icon.is-liked svg {
  transform: scale(1.1);
}

/* 已由新的样式替代 */

.liked-icon {
  color: #fe2c55;
}

.like-content {
  display: flex;
  align-items: center;
}

.like-count {
  font-size: 12px;
  margin-left: 4px;
  transition: all 0.2s ease;
  min-width: 14px;
  color: #888;
}

.like-btn.liked .like-count {
  color: #fe2c55;
  font-weight: 500;
}

/* 点赞动画 - 抖音风格 */
.like-animation {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 30px;
  height: 30px;
  background: radial-gradient(circle, rgba(254, 44, 85, 0.8) 0%, rgba(254, 44, 85, 0) 70%);
  border-radius: 50%;
  opacity: 0;
  animation: likeAnimation 0.8s ease-out;
  pointer-events: none; /* 确保动画不会阻止点击 */
}

/* 添加抖音风格的心跳动画 */
@keyframes heartBeat {
  0% {
    transform: scale(1);
  }
  15% {
    transform: scale(1.3);
  }
  30% {
    transform: scale(0.95);
  }
  45% {
    transform: scale(1.2);
  }
  60% {
    transform: scale(0.95);
  }
  100% {
    transform: scale(1);
  }
}

.heart-icon.is-liked svg {
  animation: heartBeat 0.8s ease-in-out;
}

@keyframes likeAnimation {
  0% {
    transform: translate(-50%, -50%) scale(0);
    opacity: 0.8;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.5);
    opacity: 0;
  }
}

.reply-btn {
  color: #666;
}

.reply-btn:hover {
  color: #2f88ff;
}

/* 回复表单 */
.comment-reply-form {
  margin-top: 12px;
  margin-bottom: 12px;
  margin-left: 52px; /* 头像宽度 + 右边距 */
}

/* 回复表单样式 */
.comment-reply-form {
  margin-top: 12px;
  margin-left: 40px; /* 头像宽度 + 右边距 */
  padding: 12px 15px;
  background-color: var(--card-bg-color, #f8f8f8);
  border-radius: 8px;
  position: relative;
  width: calc(100% - 40px); /* 确保与回复块有一致的宽度 */
  box-sizing: border-box; /* 确保宽度计算包含内边距和边框 */
  max-width: 100%; /* 防止超出父容器 */
  text-align: left;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color, #eee);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

/* 确保嵌套的回复表单也有一致的宽度 */
.comment-replies-group .comment-reply-form {
  width: 100%;
  margin-left: 0;
}

/* 添加一个小三角指向回复表单 */
.comment-reply-form:before {
  content: '';
  position: absolute;
  top: -8px;
  left: 20px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 8px solid var(--card-bg-color, #f8f8f8);
  transition: border-color 0.3s ease;
}
</style>
