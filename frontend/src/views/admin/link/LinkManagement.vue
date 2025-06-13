<!-- 友链管理 -->
<template>
  <div class="link-management">
    <div class="page-header">
      <h2>{{ $t('linkManagement.title') }}</h2>
    </div>

    <!-- 搜索栏 -->
    <div class="search-bar">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item :label="$t('linkManagement.search.title')">
          <el-input
            v-model="searchForm.title"
            :placeholder="$t('linkManagement.search.titlePlaceholder')"
            clearable
            @keyup.enter="handleSearch"
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item :label="$t('linkManagement.search.status')">
          <el-select
            v-model="searchForm.status"
            :placeholder="$t('linkManagement.search.statusPlaceholder')"
            clearable
            style="width: 120px"
          >
            <el-option
              v-for="item in statusOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        
        <!-- 新增链接类型筛选 -->
        <el-form-item :label="$t('linkManagement.search.type')">
          <el-select
            v-model="searchForm.type"
            :placeholder="$t('linkManagement.search.typePlaceholder')"
            clearable
            style="width: 140px"
          >
            <el-option
              v-for="item in linkTypeOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            {{ $t('common.search') }}
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            {{ $t('common.reset') }}
          </el-button>
          <el-button type="primary" @click="handleAdd">
            <el-icon><Plus /></el-icon>
            {{ $t('linkManagement.addLink') }}
          </el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 数据表格 -->
    <el-table
      v-loading="loading"
      :data="tableData"
      border
      style="width: 100%"
    >
      <el-table-column
        prop="id"
        :label="$t('linkManagement.table.id')"
        width="80"
      />
      <el-table-column
        prop="title"
        :label="$t('linkManagement.table.title')"
        min-width="120"
      />

      <el-table-column
        prop="type_name"
        :label="$t('linkManagement.table.type')"
        min-width="200"
      />

      <el-table-column
        prop="url"
        :label="$t('linkManagement.table.url')"
        min-width="200"
      />

      <el-table-column
        prop="email"
        :label="$t('linkManagement.table.email')"
        min-width="200"
      />
      
      
      <el-table-column
        prop="description"
        :label="$t('linkManagement.table.description')"
        min-width="200"
      />

      <el-table-column
        prop="logo"
        :label="$t('linkManagement.table.logo')"
        width="120"
      >
        <template #default="{ row }">
          <el-image
            :src="row.logo"
            :preview-src-list="[row.logo]"
            :initial-index="0"
            fit="contain"
            class="link-logo"
            preview-teleported
          />
        </template>
      </el-table-column>
      <el-table-column
        prop="sort"
        :label="$t('linkManagement.table.sort')"
        width="100"
      />
      <el-table-column
        prop="status"
        :label="$t('linkManagement.table.status')"
        width="100"
      >
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'info'">
            {{ row.status === 1 ? $t('linkManagement.status.enabled') : $t('linkManagement.status.disabled') }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        :label="$t('common.operations')"
        width="200"
        fixed="right"
      >
        <template #default="{ row }">
          <el-button
            type="primary"
            size="small"
            @click="handleEdit(row)"
            style="margin-right: 10px;"
          >
            <el-icon><Edit /></el-icon>
            {{ $t('common.edit') }}
          </el-button>
          <el-button
            type="danger"
            size="small"
            @click="handleDelete(row)"
          >
            <el-icon><Delete /></el-icon>
            {{ $t('common.delete') }}
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :page-sizes="[10, 20, 50, 100]"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>

    <!-- 编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? $t('linkManagement.addLink') : $t('linkManagement.editLink')"
      width="750px"
      top="5vh"
      :destroy-on-close="true"
      @closed="formRef?.clearValidate()"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
        class="link-form"
      >
        <el-form-item
          :label="$t('linkManagement.form.title')"
          prop="title"
        >
          <el-input 
            v-model="form.title" 
            :placeholder="$t('linkManagement.form.titlePlaceholder')"
          />
        </el-form-item>
        
        <!-- 新增链接类型选择 -->
        <el-form-item
          :label="$t('linkManagement.form.type')"
          prop="type"
        >
          <el-select
            v-model="form.type"
            :placeholder="$t('linkManagement.form.typePlaceholder')"
            style="width: 100%"
          >
            <el-option
              v-for="item in linkTypeOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item
          :label="$t('linkManagement.form.url')"
          prop="url"
        >
          <el-input 
            v-model="form.url" 
            :placeholder="$t('linkManagement.form.urlPlaceholder')"
          />
        </el-form-item>
        <el-form-item
          :label="$t('linkManagement.form.logo')"
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
              <div class="upload-text">{{ $t('linkManagement.form.uploadLogo') }}</div>
            </div>
          </el-upload>
          <div class="upload-tip">{{ $t('linkManagement.form.logoTip') }}</div>
        </el-form-item>
        <el-form-item
          :label="$t('linkManagement.form.sort')"
          prop="sort"
        >
          <el-input-number 
            v-model="form.sort" 
            :min="0" 
            :max="999" 
            controls-position="right"
            style="width: 180px"
          />
        </el-form-item>
        <el-form-item
          :label="$t('linkManagement.form.status')"
          prop="status"
        >
          <el-switch
            v-model="form.status"
            :active-value="1"
            :inactive-value="2"
            :active-text="$t('linkManagement.status.enabled')"
            :inactive-text="$t('linkManagement.status.disabled')"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">
            {{ $t('common.cancel') }}
          </el-button>
          <el-button type="primary" @click="handleSubmit" size="large">
            {{ $t('common.confirm') }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Refresh, Edit, Delete } from '@element-plus/icons-vue'
import { getLinkList, createLink, updateLink, deleteLink, getLinkTypeList } from '@/api/admin/link'

const { t } = useI18n()

// 搜索表单
const searchForm = reactive({
  title: '',
  status: '',
  type: '' // 新增链接类型筛选
})

// 状态选项
const statusOptions = [
  { value: 1, label: t('linkManagement.status.enabled') },
  { value: 2, label: t('linkManagement.status.disabled') }
]

// 链接类型选项
const linkTypeOptions = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const formRef = ref(null)
const form = reactive({
  id: null,
  title: '',
  url: '',
  logo: '',
  sort: 0,
  status: 1,
  type: '' // 新增链接类型字段
})

// 表单验证规则
const rules = {
  title: [
    { required: true, message: t('linkManagement.validation.titleRequired'), trigger: 'blur' },
    { min: 2, max: 50, message: t('linkManagement.validation.titleLength'), trigger: 'blur' }
  ],
  type: [
    { required: true, message: t('linkManagement.validation.typeRequired'), trigger: 'change' }
  ],
  url: [
    { required: true, message: t('linkManagement.validation.urlRequired'), trigger: 'blur' },
    { type: 'url', message: t('linkManagement.validation.urlFormat'), trigger: 'blur' }
  ],
  logo: [
    { required: true, message: t('linkManagement.validation.logoRequired'), trigger: 'change' }
  ],
  sort: [
    { required: true, message: t('linkManagement.validation.sortRequired'), trigger: 'blur' }
  ]
}

// 上传相关
const uploadUrl = '/api/admin/upload/link_logo'
const uploadHeaders = {
  Authorization: 'Bearer ' + localStorage.getItem('admin_token')
}

// 模拟数据（仅在API加载失败时使用）
const mockData = [
  {
    id: 1,
    title: 'GitHub',
    url: 'https://github.com',
    logo: 'https://github.com/favicon.ico',
    sort: 1,
    status: 1
  },
  {
    id: 2,
    title: 'Vue.js',
    url: 'https://vuejs.org',
    logo: 'https://vuejs.org/logo.png',
    sort: 2,
    status: 1
  },
  {
    id: 3,
    title: 'Element Plus',
    url: 'https://element-plus.org',
    logo: 'https://element-plus.org/images/element-plus-logo.svg',
    sort: 3,
    status: 1
  }
];

// 获取列表数据
const fetchData = async () => {
  loading.value = true
  try {
    const response = await getLinkList({
      page: currentPage.value,
      limit: pageSize.value,
      ...searchForm
    });
    
    // Debug log the full response
    
    // Handle different response formats
    if (response) {
      // Case 1: Standard response format with code 0 or 200 and data
      if (response.code === 200 && response.data) {
        if (Array.isArray(response.data)) {
          // If data is directly an array
          tableData.value = response.data;
          total.value = response.data.length;
        } else if (response.data.list) {
          // If data contains list and total
          tableData.value = response.data.list;
          total.value = response.data.total || response.data.list.length;
        } else if (response.data.records) {
          // Some APIs use 'records' instead of 'list'
          tableData.value = response.data.records;
          total.value = response.data.total || response.data.records.length;
        } else {
          // If data is an object but not in expected format
          console.warn('Unexpected data format, trying to use data directly:', response.data);
          // Try to use data object directly
          tableData.value = [response.data];
          total.value = 1;
        }
      }
      // Case 2: Direct array response
      else if (Array.isArray(response)) {
        tableData.value = response;
        total.value = response.length;
      }
      // Case 3: Response with list property
      else if (response.list) {
        tableData.value = response.list;
        total.value = response.total || response.list.length;
      }
      // Case 4: Response with records property (alternate API format)
      else if (response.records) {
        tableData.value = response.records;
        total.value = response.total || response.records.length;
      }
      // Case 5: Error response
      else {
        console.error('Invalid response format:', response);
        ElMessage.error(response.msg || t('common.fetchError'));
        tableData.value = [];
        total.value = 0;
      }
    } else {
      // Case 6: Empty response
      console.warn('Empty response received');
      tableData.value = [];
      total.value = 0;
    }
  } catch (error) {
    console.error('Failed to fetch link list:', error);
    ElMessage.error(t('common.fetchError'));
    tableData.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
};

// 搜索
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

// 重置搜索
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  handleSearch()
}

// 分页处理
const handleSizeChange = (val) => {
  pageSize.value = val
  fetchData()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  fetchData()
}

// 获取链接类型列表
const fetchLinkTypes = async () => {
  const response = await getLinkTypeList()
  if (response && response.code === 200 && response.data) {
    // 根据API返回格式进行适配
    if (Array.isArray(response.data)) {
      linkTypeOptions.value = response.data.map(item => ({
        value: item.id,
        label: item.name
      }))
    } else if (response.data.list) {
      linkTypeOptions.value = response.data.list.map(item => ({
        value: item.id,
        label: item.name
      }))
    } else if (typeof response.data === 'object' && !Array.isArray(response.data)) {
      // 处理对象格式
      linkTypeOptions.value = Object.entries(response.data).map(([key, value]) => ({
        value: Number(key),
        label: value
      }))
    }
  } else {
    console.warn('获取链接类型列表失败:', response)
  }
}

// 添加链接
const handleAdd = () => {
  dialogType.value = 'add'
  Object.keys(form).forEach(key => {
    form[key] = key === 'status' ? 1 : key === 'sort' ? 0 : ''
  })
  dialogVisible.value = true
}

// 编辑链接
const handleEdit = (row) => {
  dialogType.value = 'edit'
  Object.keys(form).forEach(key => {
    form[key] = row[key]
  })
  dialogVisible.value = true
}

// 删除链接
const handleDelete = (row) => {
  ElMessageBox.confirm(
    t('linkManagement.deleteConfirm'),
    t('common.warning'),
    {
      confirmButtonText: t('common.confirm'),
      cancelButtonText: t('common.cancel'),
      type: 'warning'
    }
  ).then(async () => {
    try {
      const response = await deleteLink(row.id)
      if (response.code === 200) {
        ElMessage.success(t('linkManagement.deleteSuccess'))
        fetchData()
      } else {
        ElMessage.error(response.msg || t('linkManagement.error.delete'))
      }
    } catch (error) {
      console.error('删除友情链接失败:', error)
      ElMessage.error(t('common.deleteError'))
    }
  })
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      try {
        const api = dialogType.value === 'add' ? createLink : updateLink
        const response = await api(form)
        if (response.code === 200) {
          ElMessage.success(
            dialogType.value === 'add'
              ? t('linkManagement.saveSuccess')
              : t('linkManagement.saveSuccess')
          )
          dialogVisible.value = false
          fetchData()
        } else {
          ElMessage.error(response.msg || t('linkManagement.error.' + dialogType.value))
        }
      } catch (error) {
        console.error('操作失败:', error)
        ElMessage.error(t('linkManagement.error.' + dialogType.value))
      }
    }
  })
}

// 上传相关方法
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    form.logo = response.data.url
  } else {
    ElMessage.error(response.msg || t('common.uploadError'))
  }
}

const beforeLogoUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error(t('common.uploadImageOnly'))
    return false
  }
  if (!isLt2M) {
    ElMessage.error(t('common.uploadSizeLimit'))
    return false
  }
  return true
}

// 初始化
onMounted(() => {
  fetchData()
  fetchLinkTypes() // 获取链接类型列表
})
</script>

<style>
/* Global styles for image viewer - not scoped to fix conflicts */
.el-image-viewer__wrapper {
  position: fixed !important;
  z-index: 3000 !important;
}

.el-image-viewer__img {
  max-width: 80vw !important;
  max-height: 80vh !important;
}

.el-image-viewer__actions {
  background-color: rgba(0, 0, 0, 0.5) !important;
}
</style>

<style scoped>
.link-management {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  font-size: 20px;
  color: var(--el-text-color-primary);
}

.search-bar {
  margin-bottom: 20px;
  padding: 20px;
  background-color: var(--el-bg-color);
  border-radius: 4px;
  box-shadow: var(--el-box-shadow-light);
  display: flex;
  justify-content: flex-end;
}

.search-form {
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-form :deep(.el-form-item__label) {
  color: var(--el-text-color-regular);
}

.search-form :deep(.el-input__inner),
.search-form :deep(.el-select__wrapper) {
  background-color: var(--el-bg-color);
  border-color: var(--el-border-color);
  color: var(--el-text-color-primary);
}

.search-form :deep(.el-input__inner::placeholder) {
  color: var(--el-text-color-placeholder);
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.avatar-uploader {
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  width: 178px;
  height: 178px;
  transition: all 0.3s;
}

.avatar-uploader:hover {
  border-color: var(--el-color-primary);
  box-shadow: 0 0 10px rgba(64, 158, 255, 0.2);
}

.avatar-uploader-icon {
  font-size: 28px;
  color: var(--el-text-color-secondary);
  width: 178px;
  height: 178px;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.avatar {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: contain;
  transition: all 0.3s;
}

.link-form {
  padding: 20px;
}

.link-form :deep(.el-form-item) {
  margin-bottom: 22px;
}

.link-form :deep(.el-input),
.link-form :deep(.el-select) {
  width: 100%;
}

.upload-text {
  margin-top: 10px;
  text-align: center;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.upload-tip {
  margin-top: 10px;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  padding: 10px 20px;
}

.dialog-footer .el-button {
  min-width: 100px;
}

:deep(.el-dialog__header) {
  padding: 20px;
  margin-right: 0;
  border-bottom: 1px solid var(--el-border-color-light);
}

:deep(.el-dialog__body) {
  padding: 0;
}

:deep(.el-dialog__headerbtn) {
  top: 20px;
  right: 20px;
}

/* 表格样式优化 */
:deep(.el-table) {
  background-color: var(--el-bg-color);
  color: var(--el-text-color-primary);
}

:deep(.el-table__header) {
  background-color: var(--el-bg-color-page);
}

:deep(.el-table__row) {
  background-color: var(--el-bg-color);
}

:deep(.el-table__row:hover) {
  background-color: var(--el-bg-color-overlay);
}

:deep(.el-table__cell) {
  background-color: transparent;
}

:deep(.el-tag) {
  background-color: var(--el-bg-color);
}

/* 分页器样式优化 */
:deep(.el-pagination) {
  --el-pagination-button-bg-color: var(--el-bg-color);
  --el-pagination-hover-color: var(--el-color-primary);
}

.link-logo {
  width: 50px;
  height: 50px;
  cursor: pointer;
  transition: all 0.3s;
}

.link-logo:hover {
  transform: scale(1.1);
}

/* 修复图片预览组件的样式 */
:deep(.el-image-viewer__wrapper) {
  position: fixed;
  z-index: 3000;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.5);
}

:deep(.el-image-viewer__canvas) {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

:deep(.el-image-viewer__img) {
  max-width: 80vw;
  max-height: 80vh;
  object-fit: contain;
}

:deep(.el-image-viewer__actions) {
  background-color: rgba(0, 0, 0, 0.5);
}

:deep(.el-image-viewer__actions__inner) {
  background-color: transparent;
}

:deep(.el-image-viewer__btn) {
  color: #fff;
}

:deep(.el-image-viewer__btn:hover) {
  color: #409EFF;
}
</style> 