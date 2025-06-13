<!-- 友链页面 -->
<template>
  <div class="friend-links-container">
    <div class="section-header">
      <h2>{{ $t('web_user.linkManagement.title') }}</h2>
      <p class="section-description">{{ $t('web_user.linkManagement.description') }}</p>
    </div>

    <!-- 链接类型筛选 -->
    <div class="filter-container" v-if="linkTypes.length > 0">
      <el-radio-group v-model="activeType" @change="handleTypeChange" size="large">
        <el-radio-button :value="0">{{ $t('web_user.linkManagement.allLinks') }}</el-radio-button>
        <el-radio-button
          v-for="type in linkTypes"
          :key="type.value"
          :value="type.value"
        >
          {{ type.label }}
        </el-radio-button>
      </el-radio-group>
    </div>

    <!-- 链接展示区域 -->
    <el-row :gutter="20" v-loading="loading">
      <el-col
        v-for="link in filteredLinks"
        :key="link.id"
        :xs="24"
        :sm="12"
        :md="8"
        :lg="6"
        :xl="4"
      >
        <el-card class="link-card" :body-style="{ padding: '0px' }" shadow="hover">
          <div class="card-content">
            <a :href="link.url" target="_blank" rel="noopener noreferrer" class="link-wrapper" :title="link.title + ' - ' + (link.description || '')">
              <div class="logo-container">
                <el-image
                  :src="link.logo"
                  fit="contain"
                  :alt="link.title"
                  class="link-logo"
                >
                  <template #error>
                    <div class="logo-fallback">
                      <el-icon><Link /></el-icon>
                    </div>
                  </template>
                </el-image>
              </div>
              <div class="link-info">
                <h3 class="link-title">{{ link.title }}</h3>
                <p class="link-description" v-if="link.description">{{ link.description }}</p>
              </div>
            </a>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 无数据展示 -->
    <el-empty
      v-if="filteredLinks.length === 0 && !loading"
      :description="$t('web_user.linkManagement.noData')"
    />

    <!-- 申请友链按钮 -->
    <div class="apply-container">
      <el-button
        type="primary"
        @click="showApplyDialog"
        size="large"
        icon="Plus"
      >
        {{ $t('web_user.linkManagement.applyLink') }}
      </el-button>
    </div>

    <!-- 申请友链对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="$t('web_user.linkManagement.applyLink')"
      width="500px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
        class="link-form"
      >
        <el-form-item
          :label="$t('web_user.linkManagement.form.title')"
          prop="title"
        >
          <el-input
            v-model="form.title"
            :placeholder="$t('web_user.linkManagement.form.titlePlaceholder')"
          />
        </el-form-item>

        <el-form-item
          :label="$t('web_user.linkManagement.form.url')"
          prop="url"
        >
          <el-input
            v-model="form.url"
            :placeholder="$t('web_user.linkManagement.form.urlPlaceholder')"
          />
        </el-form-item>

        <el-form-item
          :label="$t('web_user.linkManagement.form.email')"
          prop="email"
        >
          <el-input
            v-model="form.email"
            :placeholder="$t('web_user.linkManagement.form.emailPlaceholder')"
          />
        </el-form-item>

        <el-form-item
          :label="$t('web_user.linkManagement.form.logo')"
          prop="logo"
        >
          <el-upload
            class="avatar-uploader"
            :action="uploadUrl"
            :headers="uploadHeaders"
            :show-file-list="false"
            :on-success="handleLogoSuccess"
            :before-upload="beforeLogoUpload"
          >
            <img v-if="form.logo" :src="form.logo" class="avatar" />
            <div v-else class="avatar-uploader-icon">
              <el-icon><Plus /></el-icon>
              <div class="upload-text">{{ $t('web_user.linkManagement.form.uploadLogo') }}</div>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item
          :label="$t('web_user.linkManagement.form.description')"
          prop="description"
        >
          <el-input
            v-model="form.description"
            :placeholder="$t('web_user.linkManagement.form.descriptionPlaceholder')"
            type="textarea"
            :rows="3"
          />
        </el-form-item>

        <el-form-item
          :label="$t('web_user.linkManagement.form.type')"
          prop="type"
        >
          <el-select
            v-model="form.type"
            :placeholder="$t('web_user.linkManagement.form.typePlaceholder')"
            style="width: 100%"
          >
            <el-option
              v-for="item in linkTypes"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">
            {{ $t('common.cancel') }}
          </el-button>
          <el-button type="primary" @click="submitApplication">
            {{ $t('web_user.linkManagement.submit') }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { Link, Plus } from '@element-plus/icons-vue'
import { getLinkList, getLinkTypeList, applyLink, uploadLinkLogo } from '@/api/link'

const { t } = useI18n()

// 链接数据
const links = ref([])
const loading = ref(false)
const linkTypes = ref([])
const activeType = ref(0) // 0 表示全部类型

// 过滤后的链接
const filteredLinks = computed(() => {
  if (activeType.value === 0) {
    return links.value
  } else {
    return links.value.filter(link => link.type === activeType.value)
  }
})

// 获取链接类型
const fetchLinkTypes = async () => {
  try {
    const response = await getLinkTypeList()
    if (response && response.code === 200 && response.data) {
      // 适配不同返回格式
      if (Array.isArray(response.data)) {
        linkTypes.value = response.data.map(item => ({
          value: item.id,
          label: item.name
        }))
      } else if (response.data.list) {
        linkTypes.value = response.data.list.map(item => ({
          value: item.id,
          label: item.name
        }))
      } else if (typeof response.data === 'object' && !Array.isArray(response.data)) {
        // 处理对象格式 {1: "友情链接", 2: "技术博客"}
        linkTypes.value = Object.entries(response.data).map(([key, value]) => ({
          value: Number(key),
          label: value
        }))
      }
    }
  } catch (error) {
    console.error('获取链接类型失败:', error)
  }
}

// 获取友情链接列表
const fetchLinks = async () => {
  loading.value = true
  try {
    const response = await getLinkList({
      status: 1 // 只获取已启用的链接
    })

    if (response && response.code === 200 && response.data) {
      if (Array.isArray(response.data)) {
        links.value = response.data
      } else if (response.data.list) {
        links.value = response.data.list
      } else if (response.data.records) {
        links.value = response.data.records
      }
    } else {
      console.warn('获取友情链接失败:', response)
      links.value = []
    }
  } catch (error) {
    console.error('获取友情链接错误:', error)
    links.value = []
  } finally {
    loading.value = false
  }
}

// 类型变更处理
const handleTypeChange = (value) => {
  activeType.value = value
}

// 友链申请相关
const dialogVisible = ref(false)
const formRef = ref(null)
const form = reactive({
  title: '',
  url: '',
  email: '',
  logo: '',
  description: '',
  type: ''
})

// 表单验证规则
const rules = {
  title: [
    { required: true, message: t('web_user.linkManagement.validation.titleRequired'), trigger: 'blur' },
    { min: 2, max: 50, message: t('web_user.linkManagement.validation.titleLength'), trigger: 'blur' }
  ],
  url: [
    { required: true, message: t('web_user.linkManagement.validation.urlRequired'), trigger: 'blur' },
    { type: 'url', message: t('web_user.linkManagement.validation.urlFormat'), trigger: 'blur' }
  ],
  email: [
    { required: true, message: t('web_user.linkManagement.validation.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('web_user.linkManagement.validation.emailFormat'), trigger: 'blur' }
  ],
  logo: [
    { required: true, message: t('web_user.linkManagement.validation.logoRequired'), trigger: 'change' }
  ],
  type: [
    { required: true, message: t('web_user.linkManagement.validation.typeRequired'), trigger: 'change' }
  ]
}

// 上传相关
const uploadUrl = '/api/upload/link_logo'
const uploadHeaders = {
  Authorization: 'Bearer ' + localStorage.getItem('token')
}

// 显示申请对话框
const showApplyDialog = () => {
  dialogVisible.value = true
  Object.keys(form).forEach(key => {
    form[key] = ''
  })
}

// 上传Logo成功处理
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    form.logo = response.data.url
  } else {
    ElMessage.error(response.msg || t('common.uploadError'))
  }
}

// 上传前验证
const beforeLogoUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error(t('web_user.linkManagement.validation.imageTypeError'))
    return false
  }
  if (!isLt2M) {
    ElMessage.error(t('web_user.linkManagement.validation.imageSizeError'))
    return false
  }
  return true
}

// 提交申请
const submitApplication = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      try {
        const response = await applyLink(form)
        if (response.code === 200) {
          ElMessage.success(t('web_user.linkManagement.applySuccess'))
          dialogVisible.value = false
        } else {
          ElMessage.error(response.msg || t('web_user.linkManagement.applyFailed'))
        }
      } catch (error) {
        console.error('申请友情链接失败:', error)
        ElMessage.error(t('web_user.linkManagement.applyFailed'))
      }
    }
  })
}

// 初始化
onMounted(() => {
  fetchLinks()
  fetchLinkTypes()
})
</script>

<style scoped>
.friend-links-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.section-header {
  text-align: center;
  margin-bottom: 30px;
}

.section-header h2 {
  font-size: 28px;
  color: var(--el-text-color-primary);
  margin-bottom: 10px;
}

.section-description {
  color: var(--el-text-color-secondary);
  font-size: 16px;
}

.filter-container {
  margin-bottom: 30px;
  display: flex;
  justify-content: center;
}

.link-card {
  margin-bottom: 20px;
  transition: all 0.3s;
  height: 100%;
}

.link-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.card-content {
  padding: 15px;
}

.link-wrapper {
  display: block;
  text-decoration: none;
  color: inherit;
}

.logo-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100px;
  margin-bottom: 15px;
}

.link-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.logo-fallback {
  width: 60px;
  height: 60px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: var(--el-color-info-light-9);
  border-radius: 4px;
  color: var(--el-text-color-secondary);
  font-size: 24px;
}

.link-info {
  text-align: center;
  padding: 10px 0;
}

.link-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: var(--el-text-color-primary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.link-description {
  font-size: 14px;
  color: var(--el-text-color-secondary);
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-height: 1.4;
  height: 40px;
}

.apply-container {
  margin: 40px 0;
  text-align: center;
}

/* Form styles */
.avatar-uploader {
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  width: 120px;
  height: 120px;
  transition: all 0.3s;
}

.avatar-uploader:hover {
  border-color: var(--el-color-primary);
}

.avatar-uploader-icon {
  font-size: 28px;
  color: var(--el-text-color-secondary);
  width: 120px;
  height: 120px;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.avatar {
  width: 120px;
  height: 120px;
  display: block;
  object-fit: contain;
}

.upload-text {
  margin-top: 8px;
  font-size: 12px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .section-header h2 {
    font-size: 24px;
  }

  .section-description {
    font-size: 14px;
  }

  .filter-container {
    overflow-x: auto;
    justify-content: flex-start;
    padding-bottom: 10px;
  }
}
</style>