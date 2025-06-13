<template>
  <div class="ai-tool-edit-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>{{ isEdit ? $t('aiTool.toolEdit.editTitle') : $t('aiTool.toolEdit.createTitle') }}</span>
          <div class="header-actions">
            <el-button @click="goBack">{{ $t('aiTool.toolEdit.back') }}</el-button>
          </div>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="120px"
        v-loading="loading"
      >
        <!-- 基本信息 -->
        <el-divider>{{ $t('aiTool.toolEdit.basicInfo') }}</el-divider>

        <el-row :gutter="20">
          <el-col :span="16">
            <el-form-item :label="$t('aiTool.toolEdit.name')" prop="name">
              <el-input v-model="form.name" :placeholder="$t('aiTool.toolEdit.namePlaceholder')" />
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.slug')" prop="slug">
              <el-input v-model="form.slug" :placeholder="$t('aiTool.toolEdit.slugPlaceholder')" />
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.category')" prop="category_id">
              <el-tree-select
                v-model="form.category_id"
                :data="categoryOptions"
                :props="{ label: 'name', value: 'id', children: 'children' }"
                :render-after-expand="false"
                check-strictly
                default-expand-all
                :placeholder="$t('aiTool.toolEdit.categoryPlaceholder')"
              />
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.categories')">
              <el-select
                v-model="form.categories"
                multiple
                filterable
                value-key="id"
                :placeholder="$t('aiTool.toolEdit.categoriesPlaceholder')"
                style="width: 100%"
              >
                <el-option
                  v-for="item in flatCategoryOptions"
                  :key="item.id"
                  :label="item.label"
                  :value="{ id: item.id, name: item.name }"
                />
              </el-select>
              <div class="form-tip">
                {{ $t('aiTool.toolEdit.categoriesTip') }}
              </div>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.tags')">
              <el-select
                v-model="form.tags"
                multiple
                filterable
                allow-create
                default-first-option
                :placeholder="$t('aiTool.toolEdit.tagsPlaceholder')"
                value-key="id"
              >
                <el-option
                  v-for="item in tagOptions"
                  :key="item.id"
                  :label="item.name"
                  :value="{ id: item.id, name: item.name }"
                />
              </el-select>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.shortDescription')" prop="short_description">
              <el-input
                v-model="form.short_description"
                type="textarea"
                :rows="2"
                :placeholder="$t('aiTool.toolEdit.shortDescriptionPlaceholder')"
              />
            </el-form-item>
          </el-col>

          <el-col :span="8">
            <el-form-item :label="$t('aiTool.toolEdit.logo')" prop="logo">
              <el-upload
                class="avatar-uploader"
                :action="uploadUrl"
                :headers="uploadHeaders"
                :show-file-list="false"
                :on-success="handleLogoSuccess"
                :before-upload="beforeLogoUpload"
                :on-error="handleUploadError"
                name="file"
              >
                <img v-if="form.logo" :src="form.logo" class="avatar" />
                <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
              </el-upload>
              <div class="upload-tip">{{ $t('aiTool.toolEdit.logoTip') }}</div>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.banner')" prop="banner">
              <el-upload
                class="banner-uploader"
                :action="uploadUrl"
                :headers="uploadHeaders"
                :show-file-list="false"
                :on-success="handleBannerSuccess"
                :before-upload="beforeBannerUpload"
                :on-error="handleUploadError"
                name="file"
              >
                <img v-if="form.banner" :src="form.banner" class="banner" />
                <el-icon v-else class="banner-uploader-icon"><Plus /></el-icon>
              </el-upload>
              <div class="upload-tip">{{ $t('aiTool.toolEdit.bannerTip') }}</div>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.status')" prop="status">
              <el-select v-model="form.status" :placeholder="$t('aiTool.toolEdit.statusPlaceholder')">
                <el-option
                  v-for="item in statusOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.featured')">
              <div class="featured-options">
                <el-checkbox v-model="form.is_recommended">{{ $t('aiTool.toolEdit.recommend') }}</el-checkbox>
                <el-checkbox v-model="form.is_top">{{ $t('aiTool.toolEdit.top') }}</el-checkbox>
                <el-checkbox v-model="form.is_verified">{{ $t('aiTool.toolEdit.verified') }}</el-checkbox>
              </div>
            </el-form-item>

            <el-form-item :label="$t('aiTool.toolEdit.sortOrder')" prop="sort_order">
              <el-input-number v-model="form.sort_order" :min="0" :max="9999" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 详细信息 -->
        <el-divider>{{ $t('aiTool.toolEdit.detailInfo') }}</el-divider>

        <el-form-item :label="$t('aiTool.toolEdit.description')" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="6"
            :placeholder="$t('aiTool.toolEdit.descriptionPlaceholder')"
          />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.websiteUrl')" prop="website_url">
          <el-input v-model="form.website_url" :placeholder="$t('aiTool.toolEdit.websiteUrlPlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.pricingType')" prop="pricing_type">
          <el-radio-group v-model="form.pricing_type">
            <el-radio value="free">{{ $t('aiTool.toolEdit.pricingFree') }}</el-radio>
            <el-radio value="freemium">{{ $t('aiTool.toolEdit.pricingFreemium') }}</el-radio>
            <el-radio value="paid">{{ $t('aiTool.toolEdit.pricingPaid') }}</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.pricingInfo')" v-if="form.pricing_type !== 'free'">
          <el-input
            v-model="form.pricing_info"
            type="textarea"
            :rows="3"
            :placeholder="$t('aiTool.toolEdit.pricingInfoPlaceholder')"
          />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.company')" prop="company">
          <el-input v-model="form.company" :placeholder="$t('aiTool.toolEdit.companyPlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.version')" prop="version">
          <el-input v-model="form.version" :placeholder="$t('aiTool.toolEdit.versionPlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.license')" prop="license">
          <el-input v-model="form.license" :placeholder="$t('aiTool.toolEdit.licensePlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.platforms')">
          <div class="features-container">
            <div v-for="(platform, index) in platforms" :key="index" class="feature-item">
              <el-input v-model="platforms[index]" :placeholder="$t('aiTool.toolEdit.platformPlaceholder')" />
              <el-button type="danger" circle @click="removePlatform(index)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </div>
            <el-button type="primary" @click="addPlatform">
              <el-icon><Plus /></el-icon>
              {{ $t('aiTool.toolEdit.addPlatform') }}
            </el-button>
          </div>
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.languages')">
          <div class="features-container">
            <div v-for="(language, index) in languages" :key="index" class="feature-item">
              <el-input v-model="languages[index]" :placeholder="$t('aiTool.toolEdit.languagePlaceholder')" />
              <el-button type="danger" circle @click="removeLanguage(index)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </div>
            <el-button type="primary" @click="addLanguage">
              <el-icon><Plus /></el-icon>
              {{ $t('aiTool.toolEdit.addLanguage') }}
            </el-button>
          </div>
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.features')">
          <div class="features-container">
            <div v-for="(feature, index) in features" :key="index" class="feature-item">
              <el-input v-model="features[index]" :placeholder="$t('aiTool.toolEdit.featurePlaceholder')" />
              <el-button type="danger" circle @click="removeFeature(index)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </div>
            <el-button type="primary" @click="addFeature">
              <el-icon><Plus /></el-icon>
              {{ $t('aiTool.toolEdit.addFeature') }}
            </el-button>
          </div>
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.usageTips')">
          <div class="features-container">
            <div v-for="(tip, index) in usageTips" :key="index" class="feature-item">
              <el-input v-model="usageTips[index]" :placeholder="$t('aiTool.toolEdit.usageTipPlaceholder')" />
              <el-button type="danger" circle @click="removeUsageTip(index)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </div>
            <el-button type="primary" @click="addUsageTip">
              <el-icon><Plus /></el-icon>
              {{ $t('aiTool.toolEdit.addUsageTip') }}
            </el-button>
          </div>
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.screenshots')" prop="screenshots">
          <el-upload
            class="screenshot-uploader"
            :action="uploadUrl"
            :headers="uploadHeaders"
            list-type="picture-card"
            :on-success="handleScreenshotSuccess"
            :on-remove="handleScreenshotRemove"
            :file-list="screenshotList"
          >
            <el-icon><Plus /></el-icon>
          </el-upload>
        </el-form-item>

        <!-- SEO设置 -->
        <el-divider>{{ $t('aiTool.toolEdit.seoSettings') }}</el-divider>

        <el-form-item :label="$t('aiTool.toolEdit.seoTitle')" prop="seo_title">
          <el-input v-model="form.seo_title" :placeholder="$t('aiTool.toolEdit.seoTitlePlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.seoKeywords')" prop="seo_keywords">
          <el-input v-model="form.seo_keywords" :placeholder="$t('aiTool.toolEdit.seoKeywordsPlaceholder')" />
        </el-form-item>

        <el-form-item :label="$t('aiTool.toolEdit.seoDescription')" prop="seo_description">
          <el-input
            v-model="form.seo_description"
            type="textarea"
            :rows="3"
            :placeholder="$t('aiTool.toolEdit.seoDescriptionPlaceholder')"
          />
        </el-form-item>

        <!-- 提交按钮 -->
        <el-form-item>
          <el-button type="primary" @click="submitForm">{{ $t('aiTool.toolEdit.submit') }}</el-button>
          <el-button @click="resetForm">{{ $t('aiTool.toolEdit.reset') }}</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus, Delete } from '@element-plus/icons-vue'
import { useI18n } from 'vue-i18n'
// 导入环境变量中的API基础URL
import { getToolInfo, createTool, updateTool } from '@/api/admin/aiTool'
import { getToolCategoryList } from '@/api/admin/aiToolCategory'
import { getAllToolTags } from '@/api/admin/aiToolTag'

export default {
  name: 'AiToolEdit',
  components: {
    Plus,
    Delete
  },
  setup() {
    const { t } = useI18n()
    const route = useRoute()
    const router = useRouter()

    // 定义上传API地址
    const uploadUrl = import.meta.env.VITE_APP_API_URL + '/api/admin/upload'

    // 是否为编辑模式
    const isEdit = computed(() => !!route.params.id)

    // 加载状态
    const loading = ref(false)

    // 表单引用
    const formRef = ref(null)

    // 表单数据
    const form = reactive({
      id: 0,
      category_id: '',
      name: '',
      slug: '',
      logo: '',
      banner: '',
      short_description: '',
      description: '',
      features: [],
      platforms: [],
      languages: [],
      usage_tips: [],
      company: '',
      version: '',
      license: '',
      website_url: '',
      pricing_type: 'free',
      pricing_info: '',
      is_verified: false,
      is_recommended: false,
      is_top: false,
      status: 'draft',
      sort_order: 0,
      seo_title: '',
      seo_keywords: '',
      seo_description: '',
      tags: [],
      categories: []
    })

    // 功能特点
    const features = ref([])

    // 平台
    const platforms = ref([])

    // 语言
    const languages = ref([])

    // 使用技巧
    const usageTips = ref([])

    // 截图列表
    const screenshotList = ref([])

    // 分类选项
    const categoryOptions = ref([])

    // 扁平化的分类选项，用于多选，并添加层级路径
    const flatCategoryOptions = computed(() => {
      const flattenCategories = (categories, result = [], prefix = '') => {
        categories.forEach(category => {
          const fullName = prefix ? `${prefix} > ${category.name}` : category.name
          result.push({
            id: category.id,
            name: fullName,
            value: category.id,  // 为Select组件添加value属性
            label: fullName     // 为Select组件添加label属性
          })
          if (category.children && category.children.length > 0) {
            flattenCategories(category.children, result, fullName)
          }
        })
        return result
      }
      return flattenCategories(categoryOptions.value)
    })

    // 标签选项
    const tagOptions = ref([])

    // 状态选项
    const statusOptions = [
      { value: 'published', label: t('aiTool.toolEdit.statusPublished') },
      { value: 'draft', label: t('aiTool.toolEdit.statusDraft') },
      { value: 'pending', label: t('aiTool.toolEdit.statusPending') }
    ]

    // 上传头信息
    const uploadHeaders = computed(() => {
      return {
        Authorization: localStorage.getItem('admin_token') || ''
      }
    })

    // 表单验证规则
    const rules = {
      category_id: [
        { required: true, message: t('aiTool.toolEdit.categoryRequired'), trigger: 'change' }
      ],
      name: [
        { required: true, message: t('aiTool.toolEdit.nameRequired'), trigger: 'blur' },
        { max: 100, message: t('aiTool.toolEdit.nameLength'), trigger: 'blur' }
      ],
      slug: [
        { required: true, message: t('aiTool.toolEdit.slugRequired'), trigger: 'blur' },
        { max: 100, message: t('aiTool.toolEdit.slugLength'), trigger: 'blur' },
        { pattern: /^[a-z0-9-]+$/, message: t('aiTool.toolEdit.slugFormat'), trigger: 'blur' }
      ],
      short_description: [
        { max: 255, message: t('aiTool.toolEdit.shortDescriptionLength'), trigger: 'blur' }
      ],
      website_url: [
        { pattern: /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w.-]*)*\/?$/, message: t('aiTool.toolEdit.websiteUrlFormat'), trigger: 'blur' },
        { max: 255, message: t('aiTool.toolEdit.websiteUrlLength'), trigger: 'blur' }
      ],
      status: [
        { required: true, message: t('aiTool.toolEdit.statusRequired'), trigger: 'change' }
      ],
      pricing_type: [
        { required: true, message: t('aiTool.toolEdit.pricingTypeRequired'), trigger: 'change' }
      ],
      seo_title: [
        { max: 100, message: t('aiTool.toolEdit.seoTitleLength'), trigger: 'blur' }
      ],
      seo_keywords: [
        { max: 255, message: t('aiTool.toolEdit.seoKeywordsLength'), trigger: 'blur' }
      ],
      seo_description: [
        { max: 255, message: t('aiTool.toolEdit.seoDescriptionLength'), trigger: 'blur' }
      ]
    }

    // 获取工具详情
    const fetchToolInfo = async () => {
      if (!isEdit.value) return

      loading.value = true
      try {
        const res = await getToolInfo({ id: route.params.id })

        if (res.code === 200 && res.data) {
          // 填充表单数据
          Object.keys(form).forEach(key => {
            if (key in res.data) {
              form[key] = res.data[key]
            }
          })

          // 处理标签
          if (res.data.tags && Array.isArray(res.data.tags)) {
            form.tags = res.data.tags
            console.log('Tags loaded:', form.tags)
          } else {
            form.tags = []
          }

          // 处理分类
          if (res.data.categories && Array.isArray(res.data.categories)) {
            // 确保分类数据格式正确
            form.categories = res.data.categories.map(item => ({
              id: typeof item.id === 'string' ? parseInt(item.id, 10) : item.id,
              name: item.name
            }))
            console.log('Categories loaded:', form.categories)
          } else {
            form.categories = []
          }

          // 处理功能特点
          if (res.data.features) {
            try {
              // 如果是字符串，尝试解析JSON
              if (typeof res.data.features === 'string') {
                features.value = JSON.parse(res.data.features) || []
              } else if (Array.isArray(res.data.features)) {
                features.value = res.data.features
              } else {
                features.value = []
              }
              console.log('Features loaded:', features.value)
            } catch (error) {
              console.error('Error parsing features:', error)
              features.value = []
            }
          }

          // 处理平台
          if (res.data.platforms) {
            try {
              // 如果是字符串，尝试解析JSON
              if (typeof res.data.platforms === 'string') {
                platforms.value = JSON.parse(res.data.platforms) || []
              } else if (Array.isArray(res.data.platforms)) {
                platforms.value = res.data.platforms
              } else {
                platforms.value = []
              }
              console.log('Platforms loaded:', platforms.value)
            } catch (error) {
              console.error('Error parsing platforms:', error)
              platforms.value = []
            }
          }

          // 处理语言
          if (res.data.languages) {
            try {
              // 如果是字符串，尝试解析JSON
              if (typeof res.data.languages === 'string') {
                languages.value = JSON.parse(res.data.languages) || []
              } else if (Array.isArray(res.data.languages)) {
                languages.value = res.data.languages
              } else {
                languages.value = []
              }
              console.log('Languages loaded:', languages.value)
            } catch (error) {
              console.error('Error parsing languages:', error)
              languages.value = []
            }
          }

          // 处理使用技巧
          if (res.data.usage_tips) {
            try {
              // 如果是字符串，尝试解析JSON
              if (typeof res.data.usage_tips === 'string') {
                usageTips.value = JSON.parse(res.data.usage_tips) || []
              } else if (Array.isArray(res.data.usage_tips)) {
                usageTips.value = res.data.usage_tips
              } else {
                usageTips.value = []
              }
              console.log('Usage tips loaded:', usageTips.value)
            } catch (error) {
              console.error('Error parsing usage tips:', error)
              usageTips.value = []
            }
          }

          // 处理截图
          if (res.data.screenshots) {
            try {
              let screenshotArray = []

              // 如果是字符串，尝试解析JSON
              if (typeof res.data.screenshots === 'string') {
                screenshotArray = JSON.parse(res.data.screenshots) || []
              } else if (Array.isArray(res.data.screenshots)) {
                screenshotArray = res.data.screenshots
              }

              screenshotList.value = screenshotArray.map((url, index) => ({
                name: `screenshot-${index + 1}`,
                url
              }))
              console.log('Screenshots loaded:', screenshotList.value)
            } catch (error) {
              console.error('Error parsing screenshots:', error)
              screenshotList.value = []
            }
          }
        } else {
          ElMessage.error(res.msg || t('aiTool.toolEdit.fetchFailed'))
          goBack()
        }
      } catch (error) {
        console.error(t('aiTool.toolEdit.fetchFailed'), error)
        ElMessage.error(t('aiTool.toolEdit.networkError'))
        goBack()
      } finally {
        loading.value = false
      }
    }

    // 获取分类选项
    const fetchCategories = async () => {
      try {
        const res = await getToolCategoryList()

        if (res.code === 200) {
          categoryOptions.value = res.data || []
        }
      } catch (error) {
        console.error(t('aiTool.toolEdit.fetchCategoriesFailed'), error)
      }
    }

    // 获取标签选项
    const fetchTags = async () => {
      try {
        const res = await getAllToolTags()

        if (res.code === 200) {
          tagOptions.value = res.data || []
        }
      } catch (error) {
        console.error(t('aiTool.toolEdit.fetchTagsFailed'), error)
      }
    }

    // 添加功能特点
    const addFeature = () => {
      features.value.push('')
    }

    // 移除功能特点
    const removeFeature = (index) => {
      features.value.splice(index, 1)
    }

    // 添加平台
    const addPlatform = () => {
      platforms.value.push('')
    }

    // 移除平台
    const removePlatform = (index) => {
      platforms.value.splice(index, 1)
    }

    // 添加语言
    const addLanguage = () => {
      languages.value.push('')
    }

    // 移除语言
    const removeLanguage = (index) => {
      languages.value.splice(index, 1)
    }

    // 添加使用技巧
    const addUsageTip = () => {
      usageTips.value.push('')
    }

    // 移除使用技巧
    const removeUsageTip = (index) => {
      usageTips.value.splice(index, 1)
    }

    // Logo上传成功处理
    const handleLogoSuccess = (res, file) => {
      console.log('Logo upload response:', res);
      // 兼容不同的响应格式
      if (res.code === 200 && res.data && res.data.url) {
        form.logo = res.data.url
      } else if (res.code === 200 && res.url) {
        form.logo = res.url
      } else if (res.url) {
        form.logo = res.url
      } else {
        ElMessage.error(t('aiTool.toolEdit.uploadFailed'))
      }
    }

    // Logo上传前检查
    const beforeLogoUpload = (file) => {
      const isImage = file.type.startsWith('image/')
      const isLt2M = file.size / 1024 / 1024 < 2

      if (!isImage) {
        ElMessage.error(t('aiTool.toolEdit.uploadImageOnly'))
        return false
      }

      if (!isLt2M) {
        ElMessage.error(t('aiTool.toolEdit.uploadSizeLimit'))
        return false
      }

      return true
    }

    // Banner上传成功处理
    const handleBannerSuccess = (res, file) => {
      console.log('Banner upload response:', res);
      console.log('Banner upload file:', file);
      // 兼容不同的响应格式
      if (res.code === 200 && res.data && res.data.url) {
        form.banner = res.data.url
        console.log('Banner URL set from res.data.url:', form.banner);
        ElMessage.success(t('aiTool.toolEdit.uploadSuccess'))
      } else if (res.code === 200 && res.url) {
        form.banner = res.url
        console.log('Banner URL set from res.url:', form.banner);
        ElMessage.success(t('aiTool.toolEdit.uploadSuccess'))
      } else if (res.url) {
        form.banner = res.url
        console.log('Banner URL set directly from res.url:', form.banner);
        ElMessage.success(t('aiTool.toolEdit.uploadSuccess'))
      } else {
        console.error('Banner upload failed, response:', res);
        ElMessage.error(t('aiTool.toolEdit.uploadFailed'))
      }
    }

    // Banner上传前检查
    const beforeBannerUpload = (file) => {
      const isImage = file.type.startsWith('image/')
      const isLt2M = file.size / 1024 / 1024 < 2

      if (!isImage) {
        ElMessage.error(t('aiTool.toolEdit.uploadImageOnly'))
        return false
      }

      if (!isLt2M) {
        ElMessage.error(t('aiTool.toolEdit.uploadSizeLimit'))
        return false
      }

      return true
    }

    // 截图上传成功处理
    const handleScreenshotSuccess = (res, file, fileList) => {
      console.log('Screenshot upload response:', res);
      console.log('Screenshot file:', file);
      console.log('Screenshot fileList:', fileList);

      let url = '';
      // 兼容不同的响应格式
      if (res.code === 200 && res.data && res.data.url) {
        url = res.data.url;
        file.url = url;
      } else if (res.code === 200 && res.url) {
        url = res.url;
        file.url = url;
      } else if (res.url) {
        url = res.url;
        file.url = url;
      } else {
        ElMessage.error(t('aiTool.toolEdit.uploadFailed'));
        return;
      }

      // 更新截图列表
      screenshotList.value = fileList.map(item => {
        // 如果没有url属性，但有response，尝试从响应中提取url
        if (!item.url && item.response) {
          const response = item.response;
          if (response.code === 200 && response.data && response.data.url) {
            item.url = response.data.url;
          } else if (response.code === 200 && response.url) {
            item.url = response.url;
          } else if (response.url) {
            item.url = response.url;
          }
        }
        return {
          name: item.name,
          url: item.url
        };
      });

      console.log('Updated screenshotList:', screenshotList.value);
      ElMessage.success(t('aiTool.toolEdit.uploadSuccess'));
    }

    // 截图移除处理
    const handleScreenshotRemove = (file, fileList) => {
      console.log('Screenshot removed:', file);
      console.log('Remaining fileList:', fileList);

      // 更新截图列表，确保每个项都有url属性
      screenshotList.value = fileList.map(item => ({
        name: item.name,
        url: item.url || (item.response && item.response.data && item.response.data.url) ||
             (item.response && item.response.url) || ''
      })).filter(item => item.url); // 过滤掉没有url的项

      console.log('Updated screenshotList after removal:', screenshotList.value);
    }

    // 上传错误处理
    const handleUploadError = (err, file, fileList) => {
      console.error('Upload error:', err);
      console.error('File:', file);
      console.error('File list:', fileList);
      ElMessage.error(t('aiTool.toolEdit.uploadFailed') + ': ' + (err.message || err))
    }

    // 提交表单
    const submitForm = () => {
      formRef.value.validate(async (valid) => {
        if (valid) {
          // 处理功能特点 - 过滤并转换为JSON字符串
          const filteredFeatures = features.value.filter(item => item && typeof item === 'string' && item.trim() !== '')
          form.features = JSON.stringify(filteredFeatures)

          // 处理平台 - 过滤并转换为JSON字符串
          const filteredPlatforms = platforms.value.filter(item => item && typeof item === 'string' && item.trim() !== '')
          form.platforms = JSON.stringify(filteredPlatforms)

          // 处理语言 - 过滤并转换为JSON字符串
          const filteredLanguages = languages.value.filter(item => item && typeof item === 'string' && item.trim() !== '')
          form.languages = JSON.stringify(filteredLanguages)

          // 处理使用技巧 - 过滤并转换为JSON字符串
          const filteredUsageTips = usageTips.value.filter(item => item && typeof item === 'string' && item.trim() !== '')
          form.usage_tips = JSON.stringify(filteredUsageTips)

          // 处理截图 - 过滤并转换为JSON字符串
          const screenshotUrls = screenshotList.value
            .filter(item => item && item.url) // 过滤掉没有url的项
            .map(item => item.url)
          form.screenshots = JSON.stringify(screenshotUrls)

          // 处理时间戳字段
          if (form.create_time && typeof form.create_time === 'string') {
            // 将日期字符串转换为时间戳
            form.create_time = Math.floor(new Date(form.create_time).getTime() / 1000)
          }

          if (form.update_time && typeof form.update_time === 'string') {
            form.update_time = Math.floor(new Date(form.update_time).getTime() / 1000)
          }

          if (form.publish_time && typeof form.publish_time === 'string') {
            form.publish_time = Math.floor(new Date(form.publish_time).getTime() / 1000)
          }

          // 如果是新建工具，设置当前时间为创建时间
          if (!isEdit.value && !form.create_time) {
            form.create_time = Math.floor(Date.now() / 1000)
          }

          // 更新时间总是设置为当前时间
          form.update_time = Math.floor(Date.now() / 1000)

          // 如果状态为已发布且没有发布时间，设置当前时间为发布时间
          if (form.status === 'published' && !form.publish_time) {
            form.publish_time = Math.floor(Date.now() / 1000)
          }

          console.log('Features to submit:', form.features)
          console.log('Screenshots to submit:', form.screenshots)
          console.log('Time fields:', {
            create_time: form.create_time,
            update_time: form.update_time,
            publish_time: form.publish_time
          })

          // 打印表单数据，方便调试
          console.log('Submitting form with categories:', form.categories)
          console.log('Tags:', form.tags)
          console.log('Complete form data:', JSON.parse(JSON.stringify(form)))

          loading.value = true
          try {
            const apiMethod = isEdit.value ? updateTool : createTool
            const res = await apiMethod(form)

            if (res.code === 200) {
              ElMessage.success(isEdit.value ? t('aiTool.toolEdit.updateSuccess') : t('aiTool.toolEdit.createSuccess'))

              if (!isEdit.value && res.data && res.data.id) {
                // 创建成功后跳转到编辑页面
                router.push(`/admin/aiTool-edit/${res.data.id}`)
              } else {
                // 编辑成功后刷新数据
                fetchToolInfo()
              }
            } else {
              ElMessage.error(res.msg || (isEdit.value ? t('aiTool.toolEdit.updateError') : t('aiTool.toolEdit.createError')))
            }
          } catch (error) {
            console.error(isEdit.value ? t('aiTool.toolEdit.updateError') : t('aiTool.toolEdit.createError'), error)
            ElMessage.error(t('aiTool.toolEdit.networkError'))
          } finally {
            loading.value = false
          }
        } else {
          return false
        }
      })
    }

    // 重置表单
    const resetForm = () => {
      if (!isEdit.value) {
        formRef.value.resetFields()
        features.value = []
        screenshotList.value = []
      } else {
        fetchToolInfo()
      }
    }

    // 返回列表页
    const goBack = () => {
      router.push('/admin/aiTool-list')
    }

    // 生成别名
    const generateSlug = (name) => {
      if (!name) return ''

      // 转为小写
      let slug = name.toLowerCase()
      // 替换空格为短横线
      slug = slug.replace(/\s+/g, '-')
      // 移除特殊字符
      slug = slug.replace(/[^a-z0-9-]/g, '')
      // 移除连续的短横线
      slug = slug.replace(/-+/g, '-')
      // 移除首尾短横线
      slug = slug.replace(/^-|-$/g, '')

      return slug
    }

    // 监听名称变化，自动生成别名
    watch(() => form.name, (newVal, oldVal) => {
      if (!isEdit.value && !form.slug && newVal) {
        form.slug = generateSlug(newVal)
      }
    })

    onMounted(() => {
      fetchCategories()
      fetchTags()
      fetchToolInfo()
    })

    return {
      isEdit,
      loading,
      formRef,
      form,
      rules,
      features,
      platforms,
      languages,
      usageTips,
      screenshotList,
      categoryOptions,
      flatCategoryOptions,
      tagOptions,
      statusOptions,
      uploadHeaders,
      uploadUrl,
      addFeature,
      removeFeature,
      addPlatform,
      removePlatform,
      addLanguage,
      removeLanguage,
      addUsageTip,
      removeUsageTip,
      handleLogoSuccess,
      beforeLogoUpload,
      handleBannerSuccess,
      beforeBannerUpload,
      handleScreenshotSuccess,
      handleScreenshotRemove,
      handleUploadError,
      submitForm,
      resetForm,
      goBack
    }
  }
}
</script>

<style scoped>
.ai-tool-edit-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.featured-options {
  display: flex;
  gap: 20px;
}

.features-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar-uploader {
  width: 178px;
  height: 178px;
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: var(--el-transition-duration-fast);
}

.avatar-uploader:hover {
  border-color: var(--el-color-primary);
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}

.avatar {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: cover;
}

.banner-uploader {
  width: 100%;
  height: 120px;
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: var(--el-transition-duration-fast);
}

.banner-uploader:hover {
  border-color: var(--el-color-primary);
}

.banner-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 100%;
  height: 120px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}

.banner {
  width: 100%;
  height: 120px;
  display: block;
  object-fit: cover;
}

.upload-tip,
.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

/* 树形选择器样式 */
.el-tree-select {
  width: 100%;
}

.el-tree-node__label {
  font-size: 14px;
}

.el-tree-node.is-current > .el-tree-node__content {
  background-color: #f0f7ff;
}

/* 选中节点的样式 */
.el-select__tags-text {
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.el-select__tags .el-tag {
  margin-right: 4px;
  margin-bottom: 2px;
}

/* 确保多选标签正确显示 */
.el-select .el-select__tags {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex-wrap: wrap;
  display: flex;
}
</style>
