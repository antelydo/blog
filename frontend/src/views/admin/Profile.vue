<!-- 用户个人资料 -->

<template>
  <div class="profile-container">
    <el-row :gutter="20" class="profile-row">
      <!-- 左侧个人信息卡片 -->
      <el-col :xs="24" :sm="8" class="profile-col">
        <el-card class="profile-info-card" shadow="hover">
          <div class="avatar-container">
            <el-upload
              class="avatar-uploader"
              :action="uploadUrl"
              :headers="uploadHeaders"
              :show-file-list="false"
              :on-success="handleAvatarSuccess"
              :before-upload="beforeAvatarUpload"
              name="avatar"
            >
              <div class="avatar-wrapper">
                <img v-if="form.avatar" :src="form.avatar" class="avatar" />
                <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                <div class="avatar-hover-text">{{ $t('profile.avatar.upload') }}</div>
              </div>
            </el-upload>
            <h3 class="username">{{ form.username }}</h3>
            <div class="role-tag">
              <el-tag :type="form.role === 'super_admin' ? 'danger' : 'primary'" effect="dark">
                {{ form.role === 'super_admin' ? $t('profile.role.superAdmin') : $t('profile.role.admin') }}
              </el-tag>
            </div>
          </div>

          <div class="info-section">
            <div class="section-header">
              <el-icon><User /></el-icon>
              <span>{{ $t('profile.info.basic') }}</span>
            </div>
            <div class="info-list">
              <div class="info-item">
                <label>{{ $t('profile.info.nickname') }}：</label>
                <span>{{ form.nickname || $t('profile.info.notSet') }}</span>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.email') }}：</label>
                <span>{{ form.email || $t('profile.info.notSet') }}</span>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.mobile') }}：</label>
                <span>{{ form.mobile || $t('profile.info.notSet') }}</span>
              </div>
            </div>
          </div>

          <div class="info-section">
            <div class="section-header">
              <el-icon><Timer /></el-icon>
              <span>{{ $t('profile.info.login') }}</span>
            </div>
            <div class="info-list">
              <div class="info-item">
                <label>{{ $t('profile.info.lastLogin') }}：</label>
                <span>{{ formatDate(form.last_login_time) }}</span>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.loginIp') }}：</label>
                <span>{{ form.last_login_ip || $t('profile.info.unknown') }}</span>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.loginCount') }}：</label>
                <span>{{ form.login_count || 0 }} {{ $t('profile.info.times') }}</span>
              </div>
            </div>
          </div>

          <div class="info-section">
            <div class="section-header">
              <el-icon><InfoFilled /></el-icon>
              <span>{{ $t('profile.info.account') }}</span>
            </div>
            <div class="info-list">
              <div class="info-item">
                <label>{{ $t('profile.info.accountStatus') }}：</label>
                <el-tag :type="form.status === 1 ? 'success' : 'danger'" size="small">
                  {{ form.status === 1 ? $t('profile.info.active') : $t('profile.info.disabled') }}
                </el-tag>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.createdAt') }}：</label>
                <span>{{ formatDate(form.create_time) }}</span>
              </div>
              <div class="info-item">
                <label>{{ $t('profile.info.updatedAt') }}：</label>
                <span>{{ formatDate(form.update_time) }}</span>
              </div>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- 右侧编辑表单 -->
      <el-col :xs="24" :sm="16" class="profile-col">
        <el-card class="profile-form-card" shadow="hover">
          <template #header>
            <div class="card-header">
              <span>{{ $t('profile.form.title') }}</span>
            </div>
          </template>

          <el-form
            ref="formRef"
            :model="form"
            :rules="rules"
            label-width="120px"
            class="profile-form"
          >
            <el-form-item :label="$t('profile.form.username')" prop="username">
              <el-input v-model="form.username" disabled />
            </el-form-item>

            <el-form-item :label="$t('profile.form.nickname')" prop="nickname">
              <el-input v-model="form.nickname" :placeholder="$t('profile.form.nicknamePlaceholder')" />
            </el-form-item>

            <el-form-item :label="$t('profile.form.bio')" prop="bio">
              <el-input
                v-model="form.bio"
                type="textarea"
                :rows="4"
                :placeholder="$t('profile.form.bioPlaceholder')"
                maxlength="500"
                show-word-limit
              />
            </el-form-item>

            <el-form-item :label="$t('profile.form.email')" prop="email">
              <el-input v-model="form.email" :placeholder="$t('profile.form.emailPlaceholder')">
                <template #prefix>
                  <el-icon><Message /></el-icon>
                </template>
              </el-input>
            </el-form-item>

            <el-form-item :label="$t('profile.form.mobile')" prop="mobile">
              <el-input v-model="form.mobile" :placeholder="$t('profile.form.mobilePlaceholder')">
                <template #prefix>
                  <el-icon><Iphone /></el-icon>
                </template>
              </el-input>
            </el-form-item>

            <el-form-item :label="$t('profile.form.remark')" prop="remark">
              <el-input
                v-model="form.remark"
                type="textarea"
                :rows="2"
                :placeholder="$t('profile.form.remarkPlaceholder')"
              />
            </el-form-item>

            <el-divider content-position="left">{{ $t('profile.form.passwordSection') }}</el-divider>

            <el-form-item :label="$t('profile.form.newPassword')" prop="newPassword" class="password-section">
              <el-input
                v-model="form.newPassword"
                type="password"
                :placeholder="$t('profile.form.newPasswordPlaceholder')"
                show-password
              >
                <template #prefix>
                  <el-icon><Lock /></el-icon>
                </template>
              </el-input>
            </el-form-item>

            <el-form-item :label="$t('profile.form.confirmPassword')" prop="confirmPassword" class="password-section">
              <el-input
                v-model="form.confirmPassword"
                type="password"
                :placeholder="$t('profile.form.confirmPasswordPlaceholder')"
                show-password
              >
                <template #prefix>
                  <el-icon><Key /></el-icon>
                </template>
              </el-input>
            </el-form-item>

            <el-form-item>
              <el-button type="primary" @click="handleSubmit" :loading="loading">
                {{ $t('profile.form.saveChanges') }}
              </el-button>
              <el-button @click="resetForm">{{ $t('profile.form.reset') }}</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { useAdminUserStore } from '@/stores/admin_user'
import { useI18n } from 'vue-i18n'
import {
  User, Message, Iphone, Lock, Key, Timer, Location, Plus,
  Calendar, Edit, InfoFilled, Document, View, Memo
} from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import axios from 'axios'
import apiConfig from '@/api/config'
// 已导入apiConfig，不需要再导入apiUrls

export default {
  name: 'AdminProfile',
  components: {
    User, Message, Iphone, Lock, Key, Timer, Location, Plus,
    Calendar, Edit, InfoFilled, Document, View, Memo
  },
  setup() {
    const adminUserStore = useAdminUserStore()
    const formRef = ref(null)
    const loading = ref(false)
    const { t } = useI18n()

    const form = reactive({
      username: '',
      nickname: '',
      avatar: '',
      email: '',
      mobile: '',
      bio: '',
      remark: '',
      role: '',
      status: 1,
      last_login_time: '',
      last_login_ip: '',
      login_count: 0,
      create_time: '',
      update_time: '',
      newPassword: '',
      confirmPassword: ''
    })

    // 创建专门的上传实例
    const uploadInstance = axios.create({
      baseURL: apiConfig.BASE_URL,
      timeout: 30000,
      withCredentials: true
    })

    // 上传相关配置
    const uploadUrl = '/api/admin/uploadAvatar'
    const uploadHeaders = computed(() => {
      const adminToken = localStorage.getItem('admin_token')
      return {
        Authorization: adminToken || ''
      }
    })

    const validatePass = (rule, value, callback) => {
      if (value === '') {
        callback()
      } else if (value.length < 6) {
        callback(new Error(t('profile.validation.passwordMin')))
      } else {
        if (form.confirmPassword !== '') {
          formRef.value.validateField('confirmPassword')
        }
        callback()
      }
    }

    const validatePass2 = (rule, value, callback) => {
      if (value === '') {
        callback()
      } else if (value !== form.newPassword) {
        callback(new Error(t('profile.validation.passwordNotMatch')))
      } else {
        callback()
      }
    }

    const rules = {
      nickname: [
        { required: true, message: t('profile.form.nicknamePlaceholder'), trigger: 'blur' },
        { max: 50, message: t('profile.validation.nicknameTooLong'), trigger: 'blur' }
      ],
      email: [
        { required: true, message: t('profile.form.emailPlaceholder'), trigger: 'blur' },
        { type: 'email', message: t('profile.validation.emailFormat'), trigger: 'blur' }
      ],
      mobile: [
        { pattern: /^1[3-9]\d{9}$/, message: t('profile.validation.mobileFormat'), trigger: 'blur' }
      ],
      bio: [
        { max: 500, message: t('profile.validation.nicknameTooLong'), trigger: 'blur' }
      ],
      newPassword: [
        { validator: validatePass, trigger: 'blur' }
      ],
      confirmPassword: [
        { validator: validatePass2, trigger: 'blur' }
      ]
    }

    // 头像上传前的验证
    const beforeAvatarUpload = (file) => {
      const isImage = file.type.startsWith('image/')
      const isLt2M = file.size / 1024 / 1024 < 2

      if (!isImage) {
        ElMessage.error(t('profile.error.fileTypeError'))
        return false
      }
      if (!isLt2M) {
        ElMessage.error(t('profile.error.fileSizeError'))
        return false
      }
      return true
    }

    // 头像上传成功的处理
    const handleAvatarSuccess = (res) => {
      if (res.code === 200) {
        form.avatar = res.data.avatar
        ElMessage.success(t('profile.success.avatarUpdated'))
      } else {
        ElMessage.error(res.msg || t('profile.error.avatarUploadFailed'))
      }
    }

    const loadProfile = async () => {
      try {
        // 尝试使用store获取个人信息
        const profile = await adminUserStore.getProfile()
        Object.assign(form, {
          username: profile.username,
          nickname: profile.nickname,
          avatar: profile.avatar,
          email: profile.email,
          mobile: profile.mobile,
          bio: profile.bio,
          remark: profile.remark,
          role: profile.role,
          status: profile.status,
          last_login_time: profile.last_login_time,
          last_login_ip: profile.last_login_ip,
          login_count: profile.login_count,
          create_time: profile.create_time,
          update_time: profile.update_time
        })
      } catch (error) {
        console.error('通过store加载个人信息失败，尝试直接调用API:', error)

        // 如果store方法失败，尝试直接使用axios
        try {
          // 创建不使用取消令牌和请求拦截器的axios实例
          const directAxios = axios.create({
            baseURL: apiConfig.BASE_URL,
            timeout: 30000,
            withCredentials: true
          })

          // 添加验证头
          const adminToken = localStorage.getItem('admin_token')
          if (adminToken) {
            const formattedToken = adminToken.startsWith('Bearer ') ? adminToken : `Bearer ${adminToken}`
            directAxios.defaults.headers.common['Authorization'] = formattedToken
          }

          // 直接请求API
          const response = await directAxios.post(apiConfig.ADMIN_API.INFO)

          if (response.data && response.data.code === 200) {
            const profile = response.data.data
            Object.assign(form, {
              username: profile.username,
              nickname: profile.nickname,
              avatar: profile.avatar,
              email: profile.email,
              mobile: profile.mobile,
              bio: profile.bio,
              remark: profile.remark,
              role: profile.role,
              status: profile.status,
              last_login_time: profile.last_login_time,
              last_login_ip: profile.last_login_ip,
              login_count: profile.login_count,
              create_time: profile.create_time,
              update_time: profile.update_time
            })
          } else {
            throw new Error(response.data?.msg || '获取个人信息失败')
          }
        } catch (fallbackError) {
          console.error('直接调用API加载个人信息也失败:', fallbackError)
          ElMessage.error(t('profile.error.loadFailed'))
        }
      }
    }

    const handleSubmit = async () => {
      if (!formRef.value) return

      try {
        await formRef.value.validate()
        loading.value = true

        const updateData = {
          nickname: form.nickname,
          email: form.email,
          mobile: form.mobile,
          bio: form.bio,
          remark: form.remark
        }

        if (form.newPassword) {
          updateData.password = form.newPassword
        }

        await userStore.updateProfile(updateData)
        ElMessage.success(t('profile.success.profileUpdated'))

        // 清空密码字段
        form.newPassword = ''
        form.confirmPassword = ''
      } catch (error) {
        console.error('更新个人信息失败:', error)
        ElMessage.error(error.message || t('profile.error.updateFailed'))
      } finally {
        loading.value = false
      }
    }

    const resetForm = () => {
      if (formRef.value) {
        formRef.value.resetFields()
        loadProfile() // 重新加载原始数据
      }
    }

    onMounted(() => {
      loadProfile()
    })

    return {
      formRef,
      form,
      rules,
      loading,
      uploadUrl,
      uploadHeaders,
      handleSubmit,
      resetForm,
      beforeAvatarUpload,
      handleAvatarSuccess,
      formatDate
    }
  }
}
</script>

<style scoped>
.profile-container {
  padding: 20px;
  min-height: calc(100vh - 150px);
  background-color: var(--el-bg-color-page);
  display: flex;
}

.profile-row {
  flex: 1;
  margin: 0 !important;
  height: calc(100vh - 150px);
}

.profile-col {
  height: 100%;
}

.profile-info-card,
.profile-form-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.profile-info-card :deep(.el-card__body) {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 0;
}

.avatar-container {
  text-align: center;
  padding: 16px 0;
  background: linear-gradient(to bottom, var(--el-color-primary-light-8), transparent);
  border-radius: 8px 8px 0 0;
}

.avatar-uploader {
  display: inline-block;
}

.avatar-wrapper {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.avatar-hover-text {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  padding: 8px;
  font-size: 12px;
  opacity: 0;
  transition: opacity 0.3s;
  text-align: center;
}

.avatar-wrapper:hover .avatar {
  transform: scale(1.05);
}

.avatar-wrapper:hover .avatar-hover-text {
  opacity: 1;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 120px;
  height: 120px;
  line-height: 120px;
  text-align: center;
  border: 1px dashed var(--el-border-color);
  border-radius: 50%;
}

.username {
  margin: 10px 0 6px;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary);
  text-align: center;
}

.role-tag {
  margin: 6px 0 0;
  text-align: center;
}

.info-section {
  padding: 12px;
  border-bottom: 1px solid var(--el-border-color-lighter);
}

.info-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  padding-left: 8px;
  color: var(--el-text-color-primary);
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
}

.section-header .el-icon {
  margin-right: 6px;
  font-size: 16px;
  color: var(--el-color-primary);
  flex-shrink: 0;
}

.section-header span {
  font-size: 14px;
}

.info-list {
  padding: 0 8px;
}

.info-item {
  display: flex;
  align-items: center;
  margin: 12px 0;
  line-height: 1.8;
}

.info-item label {
  color: var(--el-text-color-secondary);
  width: 65px;
  flex-shrink: 0;
  white-space: nowrap;
  font-size: 13px;
  text-align: left;
}

.info-item span {
  flex: 1;
  color: var(--el-text-color-primary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 13px;
  text-align: center;
  padding-right: 65px;
}

.info-item .el-tag {
  margin: 0 auto;
  padding-right: 65px;
}

.el-divider {
  margin: 40px 0 24px;
}

.el-divider__text {
  font-size: 14px;
  color: var(--el-text-color-regular);
  background-color: var(--el-bg-color);
  padding: 0 16px;
}

.password-section {
  margin-top: 16px;
}

.profile-form :deep(.el-form-item.is-required) .el-form-item__label::before {
  display: none;
}

.profile-form {
  padding: 0 20px;
}

.profile-form-card :deep(.el-card__body) {
  flex: 1;
  overflow-y: auto;
  padding: 20px 0;
}

/* 移除不必要的样式 */
.bio-item,
.bio-text,
.remark-item,
.remark-text {
  display: none;
}

@media (max-width: 768px) {
  .profile-container {
    padding: 10px;
  }

  .info-section {
    padding: 10px;
  }

  .info-item label {
    width: 65px;
  }
}
</style>