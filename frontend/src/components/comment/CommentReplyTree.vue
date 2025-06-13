<template>
  <div class="comment-reply-tree" :class="{ 'new-comment': comment._isNew }">
    <!-- 当前评论 -->
    <comment-item
      :comment="comment"
      :default-avatar="defaultAvatar"
      :is-reply="isReply"
      :reply-count="getRepliesForComment(comment.id).length"
      :is-replies-expanded="isRepliesExpanded(comment.id)"
      @reply="handleReply"
      @toggle-replies="toggleReplies"
    >
      <template #reply-form="slotProps">
        <comment-form
          :parent-id="slotProps.comment.id"
          :replying-to="slotProps.comment.name || slotProps.comment.author || slotProps.comment.username || slotProps.comment.nickname"
          :root-comment-id="rootCommentId"
          :reply-level="(slotProps.comment.reply_level || 0) + 1"
          @submit="handleSubmitReply"
          @cancel="slotProps.cancelReply"
          :placeholder="`回复 @${slotProps.comment.name || slotProps.comment.author || slotProps.comment.username || slotProps.comment.nickname}`"
          submit-text="回复"
        />
      </template>
    </comment-item>

    <!-- 该评论的回复 -->
    <div v-if="getRepliesForComment(comment.id).length > 0 && isRepliesExpanded(comment.id)" class="comment-replies-group">
      <!-- 递归渲染回复 -->
      <comment-reply-tree
        v-for="reply in getRepliesForComment(comment.id)"
        :key="reply.id"
        :comment="reply"
        :default-avatar="defaultAvatar"
        :is-reply="true"
        :root-comment-id="rootCommentId"
        :get-replies-for-comment="getRepliesForComment"
        :is-replies-expanded="isRepliesExpanded"
        :toggle-replies="toggleReplies"
        :handle-reply="handleReply"
        :handle-submit-reply="handleSubmitReply"
      />

      <!-- 查看更多回复按钮 -->
      <div v-if="hasMoreReplies && hasMoreReplies(comment.id)" class="view-more-replies" @click="() => loadMoreReplies && loadMoreReplies(comment.id)">
        查看更多回复...
      </div>
    </div>
  </div>
</template>

<script>
import CommentItem from './CommentItem.vue';
import CommentForm from './CommentForm.vue';

export default {
  name: 'CommentReplyTree',
  components: {
    CommentItem,
    CommentForm
  },
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
    rootCommentId: {
      type: [Number, String],
      required: true
    },
    getRepliesForComment: {
      type: Function,
      required: true
    },
    isRepliesExpanded: {
      type: Function,
      required: true
    },
    toggleReplies: {
      type: Function,
      required: true
    },
    handleReply: {
      type: Function,
      required: true
    },
    handleSubmitReply: {
      type: Function,
      required: true
    },
    hasMoreReplies: {
      type: Function,
      default: null
    },
    loadMoreReplies: {
      type: Function,
      default: null
    }
  }
};
</script>

<style scoped>
.comment-reply-tree {
  position: relative;
  transition: all 0.5s ease;
}

.new-comment {
  animation: fadeInSlide 0.8s ease-out;
  background-color: rgba(254, 44, 85, 0.05);
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(254, 44, 85, 0.1);
}

@keyframes fadeInSlide {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.comment-replies-group {
  margin-top: 8px;
  margin-left: 40px; /* 头像宽度 + 右边距 */
  position: relative;
  width: calc(100% - 40px); /* 确保所有层级的回复都有一致的宽度 */
  box-sizing: border-box; /* 确保宽度计算包含内边距和边框 */
  max-width: 100%; /* 防止超出父容器 */
}

/* 确保嵌套的回复组也有一致的宽度 */
.comment-replies-group .comment-replies-group {
  width: 100%;
  margin-left: 0;
}

/* 添加左侧的竖线指示器 */
.comment-replies-group:before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 1px;
  background-color: var(--border-color, #f0f0f0);
  z-index: 1;
  transition: background-color 0.3s ease;
}

/* 查看更多回复按钮 */
.view-more-replies {
  color: var(--primary-color, #2f88ff);
  font-size: 13px;
  margin-top: 10px;
  margin-bottom: 5px;
  cursor: pointer;
  padding: 6px 12px;
  background-color: rgba(47, 136, 255, 0.05);
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s ease, background-color 0.3s ease, color 0.3s ease;
  width: 100%;
  justify-content: center;
}

.view-more-replies:hover {
  background-color: rgba(47, 136, 255, 0.1);
  transition: background-color 0.3s ease;
  color: var(--primary-hover, #33c9ff);
}

.view-more-replies:before {
  content: '\2193'; /* 下箭头 */
  margin-right: 6px;
  font-size: 14px;
}
</style>
