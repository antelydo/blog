<template>
  <div class="ai-tool-category-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.category.management')" class="category-card" :headStyle="{textAlign: 'left'}">

      <!-- 操作按钮 -->
      <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <a-space>
          <a-button type="primary" @click="handleAdd">
            <template #icon><PlusOutlined /></template>
            {{ $t('aiTool.category.add') }}
          </a-button>
          <a-button @click="handleRefresh">
            <template #icon><ReloadOutlined /></template>
            {{ $t('aiTool.category.refresh') }}
          </a-button>
          <a-button type="primary" @click="expandAllNodes">
            <template #icon><ExpandOutlined /></template>
            {{ $t('aiTool.category.expandAll') }}
          </a-button>
          <a-button @click="collapseAll">
            <template #icon><CompressOutlined /></template>
            {{ $t('aiTool.category.collapseAll') }}
          </a-button>
        </a-space>
      </div>

      <!-- 分类表格 -->
      <a-table
        :dataSource="categoryTree"
        :columns="columns"
        :rowKey="record => record.id"
        :loading="loading"
        :pagination="false"
        bordered
        size="middle"
        v-model:expandedRowKeys="expandedKeys"
        childrenColumnName="children"
        :indentSize="16"
        :defaultExpandAllRows="false"
      >
        <template #bodyCell="{ column, record }">
          <!-- ID列 -->
          <template v-if="column.key === 'id'">
            <span :class="['id-column', { 'id-column-child': getNestedLevel(record) >= 2 }]">{{ record.id }}</span>
          </template>

          <!-- 图标 -->
          <template v-if="column.key === 'icon'">
            <div class="category-icon">
              <component :is="validateIcon(record.icon)" v-if="record.icon" />
              <folder-outlined v-else />
            </div>
          </template>

          <!-- 分类名称 -->
          <template v-if="column.key === 'name'">
            <div class="category-name">
              <span>{{ record.name }}</span>
            </div>
          </template>

          <!-- 描述 -->
          <template v-if="column.key === 'description'">
            <a-tooltip :title="record.description">
              <div class="description-cell">{{ record.description || '-' }}</div>
            </a-tooltip>
          </template>

          <!-- 显示状态 -->
          <template v-if="column.key === 'is_show'">
            <a-switch
              :checked="Boolean(record.is_show)"
              @change="(checked) => handleStatusChange(record, checked)"
            />
          </template>

          <!-- 操作按钮 -->
          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip :title="$t('aiTool.category.edit')">
                <a-button type="primary" shape="circle" size="small" @click="handleEdit(record)">
                  <template #icon><EditOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.category.addSubcategory')">
                <a-button shape="circle" size="small" @click="handleAddSub(record)">
                  <template #icon><SubnodeOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.category.delete')">
                <a-button type="primary" danger shape="circle" size="small" @click="handleDelete(record)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <!-- 添加/编辑分类对话框 -->
    <a-modal
      :open="dialogVisible"
      :title="isEdit ? $t('aiTool.category.editTitle') : $t('aiTool.category.addTitle')"
      :width="700"
      @cancel="dialogVisible = false"
      @ok="submitForm"
      :confirmLoading="submitLoading"
    >
      <a-form
        ref="formRef"
        :model="form"
        :rules="rules"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-item :label="$t('aiTool.category.parentCategory')" name="parent_id">
          <a-tree-select
            v-model:value="form.parent_id"
            :tree-data="categoryOptions"
            :field-names="{ children: 'children', label: 'name', value: 'id' }"
            :tree-default-expand-all="true"
            :placeholder="$t('aiTool.category.selectParent')"
            style="width: 100%"
            allow-clear
          />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.name')" name="name">
          <a-input v-model:value="form.name" :placeholder="$t('aiTool.category.namePlaceholder')" @blur="handleNameBlur" />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.slug')" name="slug">
          <a-input v-model:value="form.slug" :placeholder="$t('aiTool.category.slugPlaceholder')" />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.icon')" name="icon">
          <div class="icon-selector-container">
            <a-input v-model:value="form.icon" :placeholder="$t('aiTool.category.iconPlaceholder')" style="width: calc(100% - 110px)" />
            <a-button type="primary" @click="showIconSelector" style="margin-left: 10px">
              {{ $t('common.selectIcon') }}
            </a-button>
          </div>
          <template #extra>
            <icon-preview :icon-name="form.icon" />
          </template>
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.description')" name="description">
          <a-textarea
            v-model:value="form.description"
            :rows="3"
            :placeholder="$t('aiTool.category.descriptionPlaceholder')"
          />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.sortOrder')" name="sort_order">
          <a-input-number v-model:value="form.sort_order" :min="0" :max="9999" style="width: 150px" />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.isShow')" name="is_show" :wrapper-col="{ span: 18 }">
          <a-switch v-model:checked="form.is_show" />
        </a-form-item>

        <a-divider>{{ $t('aiTool.category.seoSettings') }}</a-divider>

        <a-form-item :label="$t('aiTool.category.seoTitle')" name="seo_title">
          <a-input v-model:value="form.seo_title" :placeholder="$t('aiTool.category.seoTitlePlaceholder')" />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.seoKeywords')" name="seo_keywords">
          <a-input v-model:value="form.seo_keywords" :placeholder="$t('aiTool.category.seoKeywordsPlaceholder')" />
        </a-form-item>
        <a-form-item :label="$t('aiTool.category.seoDescription')" name="seo_description">
          <a-textarea
            v-model:value="form.seo_description"
            :rows="3"
            :placeholder="$t('aiTool.category.seoDescriptionPlaceholder')"
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 删除确认对话框 -->
    <a-modal
      :open="deleteDialogVisible"
      :title="$t('aiTool.category.deleteConfirmTitle')"
      @cancel="deleteDialogVisible = false"
      @ok="confirmDelete"
      :okButtonProps="{ danger: true }"
      :okText="$t('aiTool.category.confirm')"
      :cancelText="$t('aiTool.category.cancel')"
    >
      <p>{{ deleteDialogContent }}</p>
    </a-modal>

    <!-- 图标选择器对话框 -->
    <a-modal
      :open="iconSelectorVisible"
      :title="$t('common.selectIcon')"
      :width="1000"
      @cancel="iconSelectorVisible = false"
      @ok="confirmIconSelection"
      :okText="$t('common.confirm')"
      :cancelText="$t('common.cancel')"
      :bodyStyle="{ padding: '16px' }"
    >
      <icon-selector v-model="selectedIcon" />
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, h, nextTick } from 'vue'
import { message, Modal } from 'ant-design-vue'
import { useI18n } from 'vue-i18n'
import { formatDate } from '@/utils/date'
import {
  createToolCategory,
  getToolCategoryList,
  deleteToolCategory,
  updateToolCategoryStatus,
  updateToolCategory
} from '@/api/admin/aiToolCategory'

// 导入图标选择器组件
import IconSelector from '@/components/common/IconSelector.vue'
import IconPreview from '@/components/common/IconPreview.vue'

// 导入Ant Design Vue图标
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined,
  ExpandOutlined,
  CompressOutlined,
  SubnodeOutlined,
  FolderOutlined,
  AppstoreOutlined,
  SettingOutlined,
  UpOutlined,
  DownOutlined,
  RightOutlined
} from '@ant-design/icons-vue'

// 导入所有图标
import * as AntIcons from '@ant-design/icons-vue'

const { t } = useI18n()

// 调试国际化
console.log('[Category] 国际化测试:', {
  'common.selectIcon': t('common.selectIcon'),
  'common.confirm': t('common.confirm'),
  'common.cancel': t('common.cancel')
})

// 分类数据
const loading = ref(false)
const categories = ref([])
const categoryTree = ref([])
const categoryOptions = ref([])

// 图标选择器相关
const iconSelectorVisible = ref(false)
const selectedIcon = ref('')

// 展开/折叠相关
const expandedKeys = ref([])

// 对话框相关
const dialogVisible = ref(false)
const deleteDialogVisible = ref(false)
const deleteDialogContent = ref('')
const isEdit = ref(false)
const formRef = ref(null)
const submitLoading = ref(false)
const currentCategory = ref(null)

// 表单数据
const form = reactive({
  id: 0,
  parent_id: 0,
  name: '',
  slug: '',
  icon: '',
  description: '',
  sort_order: 0,
  is_show: true,
  seo_title: '',
  seo_keywords: '',
  seo_description: ''
})

// 表格列定义
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 100,
    align: 'center',
    sorter: (a, b) => a.id - b.id
  },
  {
    title: t('aiTool.category.icon'),
    dataIndex: 'icon',
    key: 'icon',
    width: 80,
    align: 'center'
  },
  {
    title: t('aiTool.category.name'),
    dataIndex: 'name',
    key: 'name',
    width: 200
  },
  {
    title: t('aiTool.category.slug'),
    dataIndex: 'slug',
    key: 'slug',
    width: 150
  },
  {
    title: t('aiTool.category.description'),
    dataIndex: 'description',
    key: 'description',
    ellipsis: true
  },
  {
    title: t('aiTool.category.sortOrder'),
    dataIndex: 'sort_order',
    key: 'sort_order',
    width: 100,
    sorter: (a, b) => a.sort_order - b.sort_order
  },
  {
    title: t('aiTool.category.isShow'),
    dataIndex: 'is_show',
    key: 'is_show',
    width: 100
  },
  {
    title: t('aiTool.category.createTime'),
    dataIndex: 'create_time',
    key: 'create_time',
    width: 180,
    customRender: ({ text }) => {
      return text ? formatDate(text) : '-'
    },
    sorter: (a, b) => a.create_time - b.create_time
  },
  {
    title: t('aiTool.category.operations'),
    key: 'action',
    width: 150,
    fixed: 'right'
  }
]

// 表单验证规则
const rules = {
  name: [
    { required: true, message: t('aiTool.category.nameRequired'), trigger: 'blur' },
    { max: 50, message: t('aiTool.category.nameLength'), trigger: 'blur' }
  ],
  slug: [
    { required: true, message: t('aiTool.category.slugRequired'), trigger: 'blur' },
    { max: 50, message: t('aiTool.category.slugLength'), trigger: 'blur' },
    { pattern: /^[a-z0-9-]+$/, message: t('aiTool.category.slugFormat'), trigger: 'blur' }
  ],
  icon: [
    { max: 255, message: t('aiTool.category.iconLength'), trigger: 'blur' }
  ],
  description: [
    { max: 255, message: t('aiTool.category.descriptionLength'), trigger: 'blur' }
  ],
  seo_title: [
    { max: 100, message: t('aiTool.category.seoTitleLength'), trigger: 'blur' }
  ],
  seo_keywords: [
    { max: 255, message: t('aiTool.category.seoKeywordsLength'), trigger: 'blur' }
  ],
  seo_description: [
    { max: 255, message: t('aiTool.category.seoDescriptionLength'), trigger: 'blur' }
  ]
}

// 获取分类列表
const fetchCategories = async () => {
  loading.value = true
  try {
    const res = await getToolCategoryList()
    if (res.code === 200) {
      // 存储原始分类数据
      categories.value = res.data || []

      // 构建分类树
      categoryTree.value = buildCategoryTree(categories.value)
      console.log('分类树数据:', categoryTree.value)

      // 构建分类选项
      const rootOption = { id: 0, name: t('aiTool.category.rootCategory'), children: [] }
      categoryOptions.value = [rootOption]

      if (categories.value.length > 0) {
        rootOption.children = JSON.parse(JSON.stringify(categoryTree.value))
      }
    } else {
      message.error(res.msg || t('aiTool.category.fetchFailed'))
    }
  } catch (error) {
    console.error(t('aiTool.category.fetchFailed'), error)
    message.error(t('aiTool.category.networkError'))
  } finally {
    loading.value = false
  }
}

// 刷新分类列表
const handleRefresh = () => {
  fetchCategories()
}

// 获取所有节点的ID
const getAllKeys = (data) => {
  let keys = []
  data.forEach(item => {
    keys.push(item.id)
    if (item.children && item.children.length > 0) {
      keys = keys.concat(getAllKeys(item.children))
    }
  })
  return keys
}

// 展开所有节点
const expandAllNodes = () => {
  // 获取所有节点的ID
  const allKeys = getAllKeys(categoryTree.value)
  console.log('所有节点ID:', allKeys)

  // 设置展开的节点
  expandedKeys.value = [...allKeys]
  console.log('展开的节点:', expandedKeys.value)

  message.success(t('aiTool.category.expandAllSuccess'))
}

// 折叠所有节点
const collapseAll = () => {
  // 清空展开的节点
  expandedKeys.value = []
  console.log('折叠后的节点:', expandedKeys.value)

  message.success(t('aiTool.category.collapseAllSuccess'))
}

// 注意：由于使用了 v-model:expandedRowKeys，不再需要 onExpandedRowsChange 函数

// 添加分类
const handleAdd = () => {
  isEdit.value = false
  resetForm()
  dialogVisible.value = true
}

// 添加子分类
const handleAddSub = (row) => {
  isEdit.value = false
  resetForm()
  form.parent_id = row.id
  dialogVisible.value = true
}

// 编辑分类
const handleEdit = (row) => {
  isEdit.value = true
  resetForm()

  // 填充表单数据
  Object.keys(form).forEach(key => {
    if (key in row) {
      form[key] = row[key]
    }
  })

  dialogVisible.value = true
}

// 删除分类
const handleDelete = (row) => {
  currentCategory.value = row

  // 检查是否有子分类
  if (row.children && row.children.length > 0) {
    deleteDialogContent.value = t('aiTool.category.deleteWithChildrenConfirm')
  } else {
    deleteDialogContent.value = t('aiTool.category.deleteConfirm')
  }

  deleteDialogVisible.value = true
}

// 确认删除
const confirmDelete = async () => {
  try {
    const res = await deleteToolCategory({ id: currentCategory.value.id })

    if (res.code === 200) {
      message.success(t('aiTool.category.deleteSuccess'))
      deleteDialogVisible.value = false
      fetchCategories()
    } else {
      message.error(res.msg || t('aiTool.category.deleteError'))
    }
  } catch (error) {
    console.error(t('aiTool.category.deleteError'), error)
    message.error(t('aiTool.category.networkError'))
  }
}

// 更新分类显示状态
const handleStatusChange = async (row, checked) => {
  try {
    const res = await updateToolCategoryStatus({
      id: row.id,
      is_show: checked ? 1 : 0
    })

    if (res.code === 200) {
      message.success(t('aiTool.category.statusSuccess'))
      row.is_show = checked ? 1 : 0  // 保持与后端数据一致，使用数字
    } else {
      message.error(res.msg || t('aiTool.category.statusError'))
    }
  } catch (error) {
    console.error(t('aiTool.category.statusError'), error)
    message.error(t('aiTool.category.networkError'))
  }
}

// 重置表单
const resetForm = () => {
  form.id = 0
  form.parent_id = 0
  form.name = ''
  form.slug = ''
  form.icon = ''
  form.description = ''
  form.sort_order = 0
  form.is_show = true
  form.seo_title = ''
  form.seo_keywords = ''
  form.seo_description = ''

  if (formRef.value) {
    formRef.value.resetFields()
  }
}

// 处理名称失去焦点时自动生成slug
const handleNameBlur = () => {
  if (form.name && !form.slug) {
    // 将名称转换为slug格式（小写，空格替换为短横线）
    form.slug = form.name
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '')
  }
}

// 提交表单
const submitForm = () => {
  formRef.value.validate().then(async () => {
    try {
      submitLoading.value = true
      const params = { ...form }
      params.is_show = params.is_show ? 1 : 0

      const apiMethod = isEdit.value ? updateToolCategory : createToolCategory
      const res = await apiMethod(params)

      if (res.code === 200) {
        message.success(isEdit.value ? t('aiTool.category.updateSuccess') : t('aiTool.category.createSuccess'))
        dialogVisible.value = false
        fetchCategories()
      } else {
        message.error(res.msg || (isEdit.value ? t('aiTool.category.updateError') : t('aiTool.category.createError')))
      }
    } catch (error) {
      console.error(isEdit.value ? t('aiTool.category.updateError') : t('aiTool.category.createError'), error)
      message.error(t('aiTool.category.networkError'))
    } finally {
      submitLoading.value = false
    }
  }).catch(error => {
    console.log('Validation failed:', error)
  })
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

// 验证图标名称
const validateIcon = (icon) => {
  // 如果图标名称为空，返回一个默认图标
  if (!icon) return FolderOutlined

  // 尝试从 @ant-design/icons-vue 中获取图标组件
  try {
    return AntIcons[icon] || FolderOutlined
  } catch (error) {
    console.warn(`Icon ${icon} not found`)
    return FolderOutlined
  }
}

// 显示图标选择器
const showIconSelector = () => {
  selectedIcon.value = form.icon
  iconSelectorVisible.value = true
}

// 确认图标选择
const confirmIconSelection = () => {
  form.icon = selectedIcon.value
  iconSelectorVisible.value = false
}

// 构建分类树
const buildCategoryTree = (categories, parentId = 0) => {
  const result = []

  if (!categories || categories.length === 0) {
    return result
  }

  for (const category of categories) {
    if (category.parent_id === parentId) {
      const children = buildCategoryTree(categories, category.id)
      if (children.length > 0) {
        category.children = children
      }
      result.push(category)
    }
  }

  return result
}

// 获取分类的嵌套层级
const getNestedLevel = (record) => {
  // 如果没有parent_id或者parent_id为0，则是一级分类
  if (!record.parent_id || record.parent_id === 0) {
    return 0;
  }

  // 在分类数组中查找父分类
  const parent = categories.value.find(item => item.id === record.parent_id);
  if (!parent) {
    return 1; // 如果找不到父分类，则默认为二级分类
  }

  // 递归计算父分类的层级，并加1
  return getNestedLevel(parent) + 1;
};

// 页面加载时执行
onMounted(() => {
  // 加载分类数据
  fetchCategories()
  // 默认不展开任何节点
  expandedKeys.value = []
})
</script>

<style scoped>
.ai-tool-category-container {
  padding: 16px;
}

.category-card {
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 18px;
  font-weight: bold;
}

/* 操作按钮样式 */
.action-buttons {
  margin-bottom: 16px;
}

/* 分类名称样式 */
.category-name {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* 图标单元格样式 */
.category-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

/* 图标选择器容器 */
.icon-selector-container {
  display: flex;
  align-items: center;
}

.icon-placeholder {
  width: 16px;
  height: 16px;
  display: inline-block;
}

/* 描述单元格样式 */
.description-cell {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 图标预览样式 */
.icon-preview {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
}

/* 操作按钮样式 */
.action-buttons {
  margin-top: 8px;
  margin-bottom: 16px;
}

/* 展开图标样式 */
.expand-icon {
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.expand-icon:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

/* 响应式调整 */
@media (max-width: 768px) {
  .action-buttons {
    justify-content: flex-start !important;
  }
}

/* ID列样式 */
.id-column {
  display: inline-block;
  min-width: 30px;
  vertical-align: middle;
  text-align: center;
  width: 100%;
  line-height: 32px; /* 与行高一致，实现垂直居中 */
}

/* 三级及以下分类的ID列样式 */
.id-column-child {
  display: block;
  text-align: right;
  padding-right: 8px;
  width: 100%;
  line-height: 32px; /* 与行高一致，实现垂直居中 */
}

/* 调整表格展开/折叠图标的位置 */
:deep(.ant-table-row-expand-icon) {
  margin-right: 8px;
}

/* 调整表格单元格的内边距 */
:deep(.ant-table-cell) {
  padding: 8px 8px;
}

/* 调整ID列的单元格 */
:deep(.ant-table-cell:first-child) {
  padding-left: 8px;
}

/* 暗黑模式下的表格样式修复 */
.feature-button,
.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 表格在暗黑模式下的样式 - 只调整表格颜色，不改变图标和按钮 */
:global(.dark-theme) .ant-table,
:global(.system-dark-theme) .ant-table {
  background-color: transparent;
}

:global(.dark-theme) .ant-table-thead > tr > th,
:global(.system-dark-theme) .ant-table-thead > tr > th {
  background-color: rgba(255, 255, 255, 0.04);
  color: rgba(255, 255, 255, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.09);
}

:global(.dark-theme) .ant-table-tbody > tr > td,
:global(.system-dark-theme) .ant-table-tbody > tr > td {
  background-color: transparent;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-table-tbody > tr:hover > td,
:global(.system-dark-theme) .ant-table-tbody > tr:hover > td {
  background-color: rgba(255, 255, 255, 0.04);
}

/* 修复暗黑模式下分页控件的样式 */
:global(.dark-theme) .ant-pagination,
:global(.system-dark-theme) .ant-pagination {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-pagination-item,
:global(.system-dark-theme) .ant-pagination-item {
  background-color: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
}

:global(.dark-theme) .ant-pagination-item a,
:global(.system-dark-theme) .ant-pagination-item a {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-pagination-item-active,
:global(.system-dark-theme) .ant-pagination-item-active {
  background-color: #1890ff;
  border-color: #1890ff;
}

:global(.dark-theme) .ant-pagination-item-active a,
:global(.system-dark-theme) .ant-pagination-item-active a {
  color: white;
}
</style>
