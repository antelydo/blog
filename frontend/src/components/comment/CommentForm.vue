<template>
  <div class="comment-form">
    <!-- 回复提示 -->
    <div v-if="replyingTo" class="reply-info">
      <span class="reply-to-text">
        <i class="el-icon-chat-dot-round"></i>
        {{ $t('webPost.replyTo', { name: replyingTo }) }}
      </span>
      <button class="cancel-reply" @click="cancelReply">{{ $t('webPost.cancelReply') }}</button>
    </div>

    <el-form ref="formRef" :model="form" :rules="rules" label-position="left" :label-width="'100px'">
      <!-- 用户信息，只在未登录时显示 -->
      <div class="user-info" v-if="!isLoggedIn">
        <el-form-item prop="name" :label="$t('webPost.name')">
          <el-input
            v-model="form.name"
            :placeholder="$t('webPost.namePlaceholder')"
            maxlength="50"
            show-word-limit
          />
        </el-form-item>

        <el-form-item prop="email" :label="$t('webPost.email')">
          <el-input
            v-model="form.email"
            :placeholder="$t('webPost.emailPlaceholder')"
            maxlength="100"
            show-word-limit
          />
        </el-form-item>
      </div>

      <!-- 已登录用户提示 -->
      <div class="logged-in-info" v-if="isLoggedIn">
        <div class="user-avatar">
          <img :src="userInfo.avatar || defaultAvatar" :alt="userInfo.username || userInfo.nickname || userInfo.name || userInfo.id" class="avatar-img">
        </div>
        <div class="user-info-text">
          <div class="user-name">
            {{ userInfo.username || userInfo.nickname || userInfo.name || userInfo.id }}
          </div>
          <div class="user-status">
            已登录，无需填写姓名和邮箱
          </div>
        </div>
      </div>

      <!-- 评论内容 -->
      <el-form-item prop="content" :label="$t('webPost.comment')" class="content-form-item">
        <el-input
          v-model="form.content"
          type="textarea"
          :rows="5"
          :placeholder="placeholder || $t('webPost.commentPlaceholder')"
          maxlength="1000"
          show-word-limit
          @input="updateCharCount"
        />
        <div class="form-tips">
          <span class="markdown-tip">{{ $t('webPost.markdownTip') }}</span>
          <span class="char-count">{{ $t('webPost.wordCount', { count: charCount }) }}</span>
        </div>
      </el-form-item>

      <!-- 验证码，只在未登录且需要验证码时显示 -->
      <el-form-item v-if="showCaptcha && !isLoggedIn" prop="captcha" :label="$t('webPost.captcha')">
        <div class="captcha-container">
          <el-input
            v-model="form.captcha"
            :placeholder="$t('webPost.captchaRequired')"
            maxlength="10"
          />
          <div class="captcha-image" @click="refreshCaptcha">
            <img :src="captchaUrl" alt="Captcha" />
            <span class="refresh-tip">{{ $t('webPost.refreshCaptcha') }}</span>
          </div>
        </div>
      </el-form-item>

      <!-- 提交按钮 -->
      <el-form-item>
        <el-button
          type="primary"
          @click="submitForm"
          :loading="submitting"
          class="submit-button"
          :class="{ 'is-reply': !!replyingTo }"
        >
          <i class="el-icon-s-promotion"></i>
          {{ submitText || (replyingTo ? $t('webPost.reply') : $t('webPost.submitComment')) }}
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { ElMessage } from 'element-plus';
import { getCurrentVisitorId } from '@/utils/visitorId';
import defaultAvatar from '@/assets/images/avatar.png';
import { useUserStore } from '@/stores/user';

export default {
  name: 'CommentForm',
  props: {
    parentId: {
      type: [Number, String],
      default: 0
    },
    replyingTo: {
      type: String,
      default: ''
    },
    rootCommentId: {
      type: [Number, String],
      default: 0
    },
    replyLevel: {
      type: Number,
      default: 0
    },
    placeholder: {
      type: String,
      default: ''
    },
    submitText: {
      type: String,
      default: ''
    },
    showCaptcha: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit', 'cancel'],
  setup(props, { emit }) {
    const { t } = useI18n();
    const formRef = ref(null);
    const submitting = ref(false);
    const charCount = ref(0);
    const captchaUrl = ref('');

    // 初始化时重置全局状态
    window._isSubmittingComment = null;
    console.log('CommentForm: Initialized _isSubmittingComment to null');

    // 获取用户状态
    const userStore = useUserStore();

    // 使用 localStorage 中的 token 或 user_info 判断是否登录
    const isLoggedIn = computed(() => {
      // 先检查 localStorage 中的 user_info
      const userInfoStr = localStorage.getItem('user_info');
      if (userInfoStr) {
        try {
          const userInfoObj = JSON.parse(userInfoStr);
          if (userInfoObj && userInfoObj.id) {
            return true;
          }
        } catch (e) {
          console.error('Error parsing user_info from localStorage:', e);
        }
      }

      // 再检查 token
      const token = localStorage.getItem('token');
      if (token) {
        return true;
      }

      // 最后检查 userStore
      return userStore.isLoggedIn;
    });

    // 获取用户信息
    const userInfo = computed(() => {
      // 先从 localStorage 中获取
      const userInfoStr = localStorage.getItem('user_info');
      if (userInfoStr) {
        try {
          const userInfoObj = JSON.parse(userInfoStr);
          if (userInfoObj && userInfoObj.id) {
            return userInfoObj;
          }
        } catch (e) {
          console.error('Error parsing user_info from localStorage:', e);
        }
      }

      // 如果 localStorage 中没有，则使用 userStore
      return userStore.userInfo || {};
    });

    // 表单数据
    const form = reactive({
      name: localStorage.getItem('comment_name') || '',
      email: localStorage.getItem('comment_email') || '',
      website: localStorage.getItem('comment_website') || '',
      content: '',
      parent_id: props.parentId || 0,
      captcha: ''
    });

    // 表单验证规则
    const rules = computed(() => {
      // 基本规则，内容始终需要验证
      const baseRules = {
        content: [
          { required: true, message: t('webPost.commentRequired'), trigger: 'blur' },
          { min: 2, max: 1000, message: t('webPost.commentTooLong'), trigger: 'blur' }
        ],
        captcha: [
          { required: props.showCaptcha && !isLoggedIn.value, message: t('webPost.captchaRequired'), trigger: 'blur' }
        ]
      };

      // 如果未登录，需要验证姓名和邮箱
      if (!isLoggedIn.value) {
        baseRules.name = [
          { required: true, message: t('webPost.nameRequired'), trigger: 'blur' },
          { min: 2, max: 50, message: t('webPost.nameRequired'), trigger: 'blur' }
        ];

        baseRules.email = [
          { required: true, message: t('webPost.emailRequired'), trigger: 'blur' },
          { type: 'email', message: t('webPost.validEmailRequired'), trigger: 'blur' }
        ];
      } else {
        // 已登录用户不需要验证姓名和邮箱
        baseRules.name = [];
        baseRules.email = [];
      }

      return baseRules;
    });

    // 更新字符计数
    const updateCharCount = (val) => {
      charCount.value = val ? val.length : 0;
    };

    // 刷新验证码
    const refreshCaptcha = () => {
      if (props.showCaptcha) {
        captchaUrl.value = `/api/captcha?t=${new Date().getTime()}`;
      }
    };

    // 提交表单
    const submitForm = async () => {
      if (!formRef.value) return;

      try {
        // 验证表单
        await formRef.value.validate();

        // 设置提交状态
        submitting.value = true;

        // 准备提交数据
        let commentData = {};

        // 添加回复链信息
        const replyChainInfo = {
          parent_id: props.parentId || 0,
          root_comment_id: props.rootCommentId || 0,
          reply_level: props.replyLevel || 0,
          reply_to_name: props.replyingTo || ''
        };

        if (isLoggedIn.value) {
          // 已登录用户使用用户信息
          console.log('CommentForm: User is logged in, using user info');
          commentData = {
            content: form.content,
            // 使用用户ID或其他身份标识
            user_id: userInfo.value.id,
            ...replyChainInfo
          };

          // 显示当前登录用户信息
          console.log('CommentForm: Using logged in user info:', {
            user_id: userInfo.value.id,
            username: userInfo.value.username || userInfo.value.nickname || userInfo.value.name || 'Unknown',
            loginSource: localStorage.getItem('user_info') ? 'localStorage' : 'userStore'
          });
        } else {
          // 未登录用户需要提供姓名和邮箱
          console.log('CommentForm: User is not logged in, validating name and email');

          // 验证姓名和邮箱
          if (!form.name.trim()) {
            ElMessage.error('请输入您的姓名');
            submitting.value = false;
            return;
          }

          if (!form.email.trim() || !/^\S+@\S+\.\S+$/.test(form.email)) {
            ElMessage.error('请输入有效的邮箱地址');
            submitting.value = false;
            return;
          }

          // 保存用户信息到本地存储
          localStorage.setItem('comment_name', form.name);
          localStorage.setItem('comment_email', form.email);

          commentData = {
            user_name: form.name,
            email: form.email,
            content: form.content,
            ...replyChainInfo
          };

          // 验证码只对未登录用户显示
          if (props.showCaptcha) {
            if (!form.captcha.trim()) {
              ElMessage.error('请输入验证码');
              submitting.value = false;
              return;
            }
            commentData.captcha = form.captcha;
          }
        }

        // 强制重置提交状态，确保可以提交
        if (submitting.value) {
          console.log('CommentForm: Found submitting=true, but allowing submission anyway');
          submitting.value = false; // 重置状态而不是阻止提交
        }

        // 强制重置全局状态，确保可以提交
        if (window._isSubmittingComment) {
          console.log('CommentForm: Found stale global state, resetting _isSubmittingComment from', window._isSubmittingComment, 'to null');
          window._isSubmittingComment = null;
        }

        // 设置按钮加载状态
        submitting.value = true;
        console.log('CommentForm: Setting submitting to true at', new Date().toISOString());

        // 添加定时器，确保状态不会卡死
        setTimeout(() => {
          if (submitting.value) {
            console.log('CommentForm: Auto-resetting submitting after 5 seconds');
            submitting.value = false;
          }
        }, 5000);

        // 添加全局标记，防止重复提交
        window._isSubmittingComment = 'preparing';
        console.log('CommentForm: Setting _isSubmittingComment to "preparing"');

        // 添加调试信息，显示当前的表单数据
        console.log('CommentForm: Form data being submitted:', JSON.stringify(commentData));

        // 直接触发提交事件，不再添加延迟
        console.log('CommentForm: Emitting submit event with data:', commentData);
        emit('submit', commentData);

        // 添加超时处理，防止按钮一直处于加载状态
        setTimeout(() => {
          if (submitting.value) {
            submitting.value = false;
            window._isSubmittingComment = null;
            console.log('CommentForm: Timeout - resetting submitting state');
            ElMessage.warning('提交超时，请重试');
          }
        }, 10000); // 10秒超时

        // 延迟重置表单，确保提交已完成
        setTimeout(() => {
          // 重置表单
          form.content = '';
          form.captcha = '';
          charCount.value = 0;

          // 如果是回复，触发取消回复事件
          if (props.replyingTo) {
            emit('cancel');
          }
        }, 500);

        // 刷新验证码
        refreshCaptcha();
      } catch (error) {
        console.error('Form validation failed:', error);
        ElMessage.error('表单验证失败，请检查输入');
      } finally {
        // 重置按钮状态
        console.log('CommentForm: Resetting submitting to false in finally block at', new Date().toISOString());
        submitting.value = false;

        // 确保在出错时也能重置全局状态
        setTimeout(() => {
          if (window._isSubmittingComment === 'preparing') {
            console.log('CommentForm: Resetting stale preparing state in finally block');
            window._isSubmittingComment = null;
          }

          // 再次确认按钮状态已重置
          if (submitting.value) {
            console.log('CommentForm: Force resetting submitting to false in finally timeout');
            submitting.value = false;
          }
        }, 500);
      }
    };

    // 取消回复
    const cancelReply = () => {
      emit('cancel');
    };

    // 组件挂载时刷新验证码
    onMounted(() => {
      // 重置所有状态
      submitting.value = false;
      window._isSubmittingComment = null;
      console.log('CommentForm: Reset all submission states on mount');

      // 输出登录状态信息，方便调试
      console.log('CommentForm: User login status:', {
        isLoggedIn: isLoggedIn.value,
        userInfo: userInfo.value,
        token: localStorage.getItem('token') ? 'exists' : 'not found',
        userInfoInLocalStorage: localStorage.getItem('user_info') ? 'exists' : 'not found'
      });

      if (props.showCaptcha) {
        refreshCaptcha();
      }

      // 添加页面卸载事件，确保在页面刷新或离开时重置状态
      window.addEventListener('beforeunload', () => {
        window._isSubmittingComment = null;
        console.log('CommentForm: Reset _isSubmittingComment on page unload');
      });
    });

    return {
      formRef,
      form,
      rules,
      submitting,
      charCount,
      captchaUrl,
      isLoggedIn,
      userInfo,
      defaultAvatar,
      updateCharCount,
      refreshCaptcha,
      submitForm,
      cancelReply
    };
  }
}
</script>

<style scoped>
/* 评论表单容器 */
.comment-form {
  background-color: #f8f8f8;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  width: 100%;
  box-sizing: border-box;
  text-align: left;
  line-height: 1.5;
}

.comment-form :deep(.el-form) {
  width: 100%;
  display: flex;
  flex-direction: column;
}

/* 回复提示区 */
.reply-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  padding: 8px 12px;
  background-color: rgba(47, 136, 255, 0.1);
  border-radius: 8px;
  font-size: 14px;
  color: #333;
  line-height: 1.4;
  border: 1px solid rgba(47, 136, 255, 0.2);
}

.reply-to-text {
  display: flex;
  align-items: center;
  font-weight: 500;
  color: #2f88ff;
}

.reply-to-text i {
  margin-right: 6px;
  font-size: 16px;
}

.cancel-reply {
  background: none;
  border: none;
  color: #fe2c55;
  cursor: pointer;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
  font-weight: 500;
  line-height: 1.2;
  margin-left: 8px;
  white-space: nowrap;
}

.cancel-reply:hover {
  background-color: rgba(254, 44, 85, 0.1);
  color: #ff4d6a;
}

/* 用户信息区 */
.user-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
  width: 100%;
}

/* 已登录用户信息 */
.logged-in-info {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  padding: 12px;
  background-color: rgba(47, 136, 255, 0.05);
  border-radius: 8px;
  border: 1px solid rgba(47, 136, 255, 0.1);
}

.logged-in-info .user-avatar {
  margin-right: 12px;
}

.logged-in-info .avatar-img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.logged-in-info .user-info-text {
  display: flex;
  flex-direction: column;
}

.logged-in-info .user-name {
  font-weight: 500;
  color: #333;
  font-size: 14px;
  margin-bottom: 4px;
}

.logged-in-info .user-status {
  font-size: 12px;
  color: #666;
}

/* 表单提示 */
.form-tips {
  display: flex;
  justify-content: space-between;
  margin-top: 6px;
  font-size: 12px;
  color: #999;
}

.markdown-tip {
  font-style: italic;
}

/* 验证码区域 */
.captcha-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.captcha-image {
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
}

.captcha-image:hover {
  opacity: 0.8;
}

.captcha-image img {
  height: 40px;
  border: 1px solid #eee;
  border-radius: 4px;
}

.refresh-tip {
  position: absolute;
  bottom: -20px;
  left: 0;
  font-size: 12px;
  color: #999;
  white-space: nowrap;
}

/* 覆盖 Element UI 的样式 */
:deep(.el-button--primary) {
  background-color: #fe2c55;
  border-color: #fe2c55;
  font-weight: 500;
  border-radius: 4px;
  transition: all 0.2s ease;
}

:deep(.el-button--primary:hover) {
  background-color: #f22c50;
  border-color: #f22c50;
  box-shadow: 0 2px 8px rgba(254, 44, 85, 0.3);
}

:deep(.el-textarea__inner) {
  border-radius: 8px;
  border-color: #eee;
  padding: 12px;
  font-size: 14px;
  transition: all 0.2s ease;
  width: 100%;
}

:deep(.el-textarea) {
  width: 100%;
}

:deep(.el-textarea__inner:focus) {
  border-color: #fe2c55;
  box-shadow: 0 0 0 2px rgba(254, 44, 85, 0.1);
}

:deep(.el-input__inner) {
  border-radius: 8px;
  border-color: #eee;
  height: 40px;
  transition: all 0.2s ease;
  width: 100%;
}

:deep(.el-input) {
  width: 100%;
}

:deep(.el-input__inner:focus) {
  border-color: #fe2c55;
  box-shadow: 0 0 0 2px rgba(254, 44, 85, 0.1);
}

/* 表单标签左对齐 */
:deep(.el-form-item__label) {
  text-align: left !important;
  font-weight: 500;
  color: #333;
  line-height: 40px; /* 与输入框高度一致 */
  font-size: 14px;
  padding: 0;
  margin: 0;
  height: 40px;
  display: flex;
  align-items: center;
}

:deep(.el-form--label-left .el-form-item__label) {
  text-align: left;
  display: flex;
  align-items: center;
  padding-right: 12px;
}

/* 已合并到上面的样式中 */

:deep(.el-form-item) {
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  width: 100%;
}

:deep(.el-form-item:last-child) {
  margin-bottom: 0;
}

:deep(.el-form-item__content) {
  line-height: 40px;
  display: flex;
  align-items: center;
  width: 100%;
  flex: 1;
}

/* 处理文本域的特殊情况 */
:deep(.content-form-item) {
  align-items: flex-start;
}

:deep(.content-form-item .el-form-item__label) {
  line-height: 1.5;
  padding-top: 10px;
  height: auto;
}

:deep(.content-form-item .el-form-item__content) {
  flex-direction: column;
  align-items: stretch;
}

/* 提交按钮样式 */
:deep(.el-button--primary) {
  background-color: #fe2c55;
  border-color: #fe2c55;
}

:deep(.el-button--primary:hover) {
  background-color: #ff4d6a;
  border-color: #ff4d6a;
}

.submit-button {
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 20px;
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(254, 44, 85, 0.2);
}

.submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(254, 44, 85, 0.3);
}

.submit-button:active {
  transform: translateY(0);
}

.submit-button.is-reply {
  background-color: #2f88ff;
  border-color: #2f88ff;
}

.submit-button.is-reply:hover {
  background-color: #4a9bff;
  border-color: #4a9bff;
}

.submit-button i {
  font-size: 16px;
  margin-right: 4px;
}

.submit-button.loading {
  pointer-events: none;
}
</style>
