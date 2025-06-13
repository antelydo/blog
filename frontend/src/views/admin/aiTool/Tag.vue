<template>
  <div class="ai-tool-tag-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.tag.management')" class="tag-card" :headStyle="{textAlign: 'left'}">

      <!-- 统计卡片 -->
      <a-row :gutter="24" class="mb-4">
        <a-col :span="6" :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <template #title>
              <span><TagsOutlined /> {{ $t('aiTool.tag.totalTags') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total }}</div>
              <div class="stat-label">{{ $t('aiTool.tag.tagsCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6" :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <template #title>
              <span><FireOutlined /> {{ $t('aiTool.tag.popularTags') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ popularCount }}</div>
              <div class="stat-label">{{ $t('aiTool.tag.popularCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6" :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <template #title>
              <span><LinkOutlined /> {{ $t('aiTool.tag.relatedTools') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ totalToolsCount }}</div>
              <div class="stat-label">{{ $t('aiTool.tag.toolsCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6" :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <template #title>
              <span><AppstoreOutlined /> {{ $t('aiTool.tag.unusedTags') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total - totalToolsCount > 0 ? total - totalToolsCount : 0 }}</div>
              <div class="stat-label">{{ $t('aiTool.tag.unusedCount') }}</div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <!-- 搜索和筛选 -->
      <a-form layout="inline" :model="listQuery" class="search-form">
        <a-row :gutter="24" style="width: 100%">
          <a-col :xs="24" :sm="12" :md="10" :lg="8">
            <a-form-item :label="$t('aiTool.tag.keyword')" style="margin-bottom: 16px; width: 100%">
              <a-input
                v-model:value="listQuery.keyword"
                :placeholder="$t('aiTool.tag.searchPlaceholder')"
                allow-clear
                @pressEnter="handleFilter"
                style="width: 100%"
              >
                <template #prefix>
                  <SearchOutlined />
                </template>
              </a-input>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tag.orderField')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.order_field"
                :placeholder="$t('aiTool.tag.orderField')"
                style="width: 100%"
              >
                <a-select-option v-for="item in orderFieldOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tag.orderType')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.order_type"
                :placeholder="$t('aiTool.tag.orderType')"
                style="width: 100%"
              >
                <a-select-option v-for="item in orderTypeOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tag.isShow')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.is_show"
                :placeholder="$t('aiTool.tag.isShowPlaceholder')"
                style="width: 100%"
              >
                <a-select-option value="">{{ $t('aiTool.tag.all') }}</a-select-option>
                <a-select-option :value="1">{{ $t('aiTool.tag.show') }}</a-select-option>
                <a-select-option :value="0">{{ $t('aiTool.tag.hide') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4" class="search-buttons" style="margin-bottom: 16px">
            <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
              <a-space>
                <a-button type="primary" @click="handleFilter">
                  <template #icon><SearchOutlined /></template>
                  {{ $t('aiTool.tag.search') }}
                </a-button>
                <a-button @click="resetFilter">
                  <template #icon><ReloadOutlined /></template>
                  {{ $t('aiTool.tag.reset') }}
                </a-button>
              </a-space>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <!-- 操作按钮 -->
      <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <a-space>
          <a-button type="primary" @click="handleAdd">
            <template #icon><PlusOutlined /></template>
            {{ $t('aiTool.tag.add') }}
          </a-button>
          <a-button type="primary" @click="showMergeDialog">
            <template #icon><MergeCellsOutlined /></template>
            {{ $t('aiTool.tag.merge') }}
          </a-button>
          <a-button @click="handleRefresh">
            <template #icon><ReloadOutlined /></template>
            {{ $t('aiTool.tag.refresh') }}
          </a-button>
          <a-button type="primary" @click="exportData">
            <template #icon><DownloadOutlined /></template>
            {{ $t('aiTool.tag.export') }}
          </a-button>
        </a-space>
      </div>

      <!-- 批量操作 -->
      <a-alert v-if="selectedTags.length > 0" type="info" class="batch-alert" show-icon>
        <template #message>
          <div class="batch-message">
            <span>{{ $t('aiTool.tag.batchSelected', { count: selectedTags.length }) }}</span>
            <div class="batch-actions">
              <a-button danger size="small" @click="handleBatchDelete">
                <template #icon><DeleteOutlined /></template>
                {{ $t('aiTool.tag.batchDelete') }}
              </a-button>
              <a-button size="small" @click="clearSelection">
                <template #icon><ClearOutlined /></template>
                {{ $t('aiTool.tag.clearSelection') }}
              </a-button>
            </div>
          </div>
        </template>
      </a-alert>

      <!-- 标签列表 -->
      <a-table
        :dataSource="tagList"
        :columns="columns"
        :rowKey="record => record.id"
        :pagination="pagination"
        :loading="loading"
        :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
        @change="handleTableChange"
        bordered
        size="middle"
        class="tag-table"
      >
        <template #bodyCell="{ column, record }">
          <!-- 标签名称 -->
          <template v-if="column.key === 'name'">
            <div class="tag-name-container">
              <a-tag color="blue">{{ record.name }}</a-tag>
            </div>
          </template>

          <!-- 标签别名 -->
          <template v-if="column.key === 'slug'">
            <span class="tag-slug">{{ record.slug }}</span>
          </template>

          <!-- 关联数量 -->
          <template v-if="column.key === 'count'">
            <a-badge :count="record.count" :numberStyle="{ backgroundColor: getCountColor(record.count) }" />
          </template>

          <!-- 描述 -->
          <template v-if="column.key === 'description'">
            <a-tooltip :title="record.description">
              <div class="description-cell">{{ record.description || '-' }}</div>
            </a-tooltip>
          </template>

          <!-- 是否显示 -->
          <template v-if="column.key === 'isShow'">
            <a-switch
              :checked="Boolean(record.is_show)"
              @change="(checked) => handleStatusChange(record, checked)"
              :checkedChildren="t('aiTool.tag.show')"
              :unCheckedChildren="t('aiTool.tag.hide')"
            />
          </template>

          <!-- 创建时间 -->
          <template v-if="column.key === 'createTime'">
            {{ formatDate(record.create_time) }}
          </template>

          <!-- 操作按钮 -->
          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip :title="$t('aiTool.tag.edit')">
                <a-button type="primary" shape="circle" size="small" @click="handleEdit(record)">
                  <template #icon><EditOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.tag.view')">
                <a-button shape="circle" size="small" @click="handleView(record)">
                  <template #icon><EyeOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.tag.delete')">
                <a-button type="primary" danger shape="circle" size="small" @click="handleDelete(record)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>

      <!-- 注意：分页已移至表格内部，这里不再需要单独的分页组件 -->
    </a-card>

    <!-- 添加/编辑标签对话框 -->
    <a-modal
      :open="dialogVisible"
      :title="isEdit ? $t('aiTool.tag.editTitle') : $t('aiTool.tag.addTitle')"
      :width="600"
      @cancel="dialogVisible = false"
      @ok="submitForm"
      :confirmLoading="submitLoading"
      :okText="$t('aiTool.tag.ok')"
      :cancelText="$t('aiTool.tag.cancel')"
    >
      <a-form
        ref="formRef"
        :model="form"
        :rules="rules"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-item :label="$t('aiTool.tag.name')" name="name">
          <a-input v-model:value="form.name" :placeholder="$t('aiTool.tag.namePlaceholder')" @blur="handleNameBlur" />
        </a-form-item>

        <a-form-item :label="$t('aiTool.tag.slug')" name="slug">
          <a-input v-model:value="form.slug" :placeholder="$t('aiTool.tag.slugPlaceholder')" />
        </a-form-item>

        <a-form-item :label="$t('aiTool.tag.description')" name="description">
          <a-textarea
            v-model:value="form.description"
            :rows="3"
            :placeholder="$t('aiTool.tag.descriptionPlaceholder')"
          />
        </a-form-item>

        <a-form-item :label="t('aiTool.tag.isShow')" name="is_show">
          <a-switch
            v-model:checked="form.is_show"
            :checkedChildren="t('aiTool.tag.show')"
            :unCheckedChildren="t('aiTool.tag.hide')"
          />
        </a-form-item>

        <a-divider>{{ $t('aiTool.tag.seoSettings') }}</a-divider>

        <a-form-item :label="$t('aiTool.tag.seoTitle')" name="seo_title">
          <a-input v-model:value="form.seo_title" :placeholder="$t('aiTool.tag.seoTitlePlaceholder')" />
        </a-form-item>

        <a-form-item :label="$t('aiTool.tag.seoKeywords')" name="seo_keywords">
          <a-input v-model:value="form.seo_keywords" :placeholder="$t('aiTool.tag.seoKeywordsPlaceholder')" />
        </a-form-item>

        <a-form-item :label="$t('aiTool.tag.seoDescription')" name="seo_description">
          <a-textarea
            v-model:value="form.seo_description"
            :rows="3"
            :placeholder="$t('aiTool.tag.seoDescriptionPlaceholder')"
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 合并标签对话框 -->
    <a-modal
      :open="mergeDialogVisible"
      :title="$t('aiTool.tag.mergeTitle')"
      :width="600"
      @cancel="mergeDialogVisible = false"
      @ok="submitMergeForm"
      :confirmLoading="mergeLoading"
      :okText="$t('aiTool.tag.ok')"
      :cancelText="$t('aiTool.tag.cancel')"
    >
      <a-form
        ref="mergeFormRef"
        :model="mergeForm"
        :rules="mergeRules"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-item :label="$t('aiTool.tag.sourceTags')" name="source_ids">
          <a-select
            v-model:value="mergeForm.source_ids"
            mode="multiple"
            :placeholder="$t('aiTool.tag.sourceTagsPlaceholder')"
            style="width: 100%"
            :options="tagOptions.map(item => ({ value: item.id, label: `${item.name} (${item.count})` }))"
            :filterOption="filterOption"
            show-search
          />
        </a-form-item>

        <a-form-item :label="$t('aiTool.tag.targetTag')" name="target_id">
          <a-select
            v-model:value="mergeForm.target_id"
            :placeholder="$t('aiTool.tag.targetTagPlaceholder')"
            style="width: 100%"
            :options="tagOptions.map(item => ({ value: item.id, label: `${item.name} (${item.count})` }))"
            :filterOption="filterOption"
            show-search
          />
        </a-form-item>

        <a-alert type="warning" show-icon>
          <template #message>
            {{ $t('aiTool.tag.mergeWarning') }}
          </template>
          <template #description>
            {{ $t('aiTool.tag.mergeDescription') }}
          </template>
        </a-alert>
      </a-form>
    </a-modal>

    <!-- 删除确认对话框 -->
    <a-modal
      :open="deleteDialogVisible"
      :title="$t('aiTool.tag.deleteConfirmTitle')"
      @cancel="deleteDialogVisible = false"
      @ok="confirmDelete"
      :okButtonProps="{ danger: true }"
      :okText="$t('aiTool.tag.confirm')"
      :cancelText="$t('aiTool.tag.cancel')"
    >
      <p>{{ deleteDialogContent }}</p>
    </a-modal>

    <!-- 批量删除确认对话框 -->
    <a-modal
      :open="batchDeleteDialogVisible"
      :title="$t('aiTool.tag.batchDeleteTitle')"
      @cancel="batchDeleteDialogVisible = false"
      @ok="confirmBatchDelete"
      :okButtonProps="{ danger: true }"
      :okText="$t('aiTool.tag.confirm')"
      :cancelText="$t('aiTool.tag.cancel')"
    >
      <p>{{ $t('aiTool.tag.batchDeleteConfirm', { count: selectedTags.length }) }}</p>
    </a-modal>

    <!-- 标签详情抽屉 -->
    <a-drawer
      :open="detailDrawerVisible"
      :title="$t('aiTool.tag.detailTitle')"
      @close="detailDrawerVisible = false"
      :width="700"
      placement="right"
    >
      <a-spin :spinning="detailLoading">
        <div v-if="currentTagDetail.id" class="tag-detail">
          <a-descriptions bordered :column="1">
            <a-descriptions-item :label="$t('aiTool.tag.id')">{{ currentTagDetail.id }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.name')">{{ currentTagDetail.name }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.slug')">{{ currentTagDetail.slug }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.description')">{{ currentTagDetail.description || '-' }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.count')">{{ currentTagDetail.count || 0 }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.createTime')">{{ formatDate(currentTagDetail.create_time) }}</a-descriptions-item>
            <a-descriptions-item :label="$t('aiTool.tag.updateTime')">{{ formatDate(currentTagDetail.update_time) }}</a-descriptions-item>
          </a-descriptions>

          <!-- 相关工具列表 -->
          <div v-if="currentTagDetail.tools && currentTagDetail.tools.length > 0" class="related-tools">
            <h3>{{ $t('aiTool.tag.relatedTools') }} ({{ currentTagDetail.tools.length }})</h3>
            <a-table
              :dataSource="currentTagDetail.tools"
              :columns="relatedToolsColumns"
              :pagination="false"
              size="small"
              :scroll="{ y: 300 }"
            >
              <template #bodyCell="{ column, record }">
                <!-- 工具名称 -->
                <template v-if="column.key === 'name'">
                  <a-tooltip :title="record.name">
                    <a :href="'/admin/ai-tool/edit?id=' + record.id" target="_blank">{{ record.name }}</a>
                  </a-tooltip>
                </template>
              </template>
            </a-table>
          </div>
          <div v-else class="no-related-tools">
            <p>{{ $t('aiTool.tag.noRelatedTools') }}</p>
          </div>
        </div>
      </a-spin>

      <template #footer>
        <div style="text-align: right;">
          <a-space>
            <a-button @click="detailDrawerVisible = false">{{ $t('aiTool.tag.close') }}</a-button>
            <a-button type="primary" @click="handleEdit(currentTagDetail)" v-if="currentTagDetail.id">{{ $t('aiTool.tag.edit') }}</a-button>
          </a-space>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, h, watch } from 'vue'
import { message, Modal } from 'ant-design-vue'
import { useI18n } from 'vue-i18n'
import { formatDate } from '@/utils/date'
import {
  getToolTagList,
  getAllToolTags,
  getToolTagInfo,
  createToolTag,
  updateToolTag,
  deleteToolTag,
  batchDeleteToolTag,
  mergeToolTags,
  updateToolTagStatus,
} from '@/api/admin/aiToolTag'
import * as XLSX from 'xlsx'

// 导入Ant Design Vue图标
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  SearchOutlined,
  ReloadOutlined,
  DownloadOutlined,
  TagsOutlined,
  FireOutlined,
  LinkOutlined,
  MergeCellsOutlined,
  ClearOutlined,
  AppstoreOutlined
} from '@ant-design/icons-vue'

const { t } = useI18n()

// 标签列表数据
const loading = ref(false)
const tagList = ref([])
const total = ref(0)
const selectedTags = ref([])
const selectedRowKeys = ref([])
const deleteDialogVisible = ref(false)
const deleteDialogContent = ref('')
const currentTag = ref(null)
const batchDeleteDialogVisible = ref(false)
const submitLoading = ref(false)
const mergeLoading = ref(false)
const tagTableRef = ref(null)

// 统计数据
const popularCount = computed(() => {
  return tagList.value.filter(item => item.count > 5).length
})

const totalToolsCount = computed(() => {
  return tagList.value.reduce((sum, item) => sum + (item.count || 0), 0)
})

// 相关工具列表列定义
const relatedToolsColumns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'left'
  },
  {
    title: t('aiTool.tag.toolName'),
    dataIndex: 'name',
    key: 'name',
    ellipsis: true,
    align: 'left'
  },
  {
    title: t('aiTool.tag.toolCategory'),
    dataIndex: ['category', 'name'],
    key: 'category',
    width: 120,
    align: 'left'
  }
]

// 表格列定义
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    sorter: true,
    align: 'left'
  },
  {
    title: t('aiTool.tag.name'),
    dataIndex: 'name',
    key: 'name',
    width: 150,
    align: 'left'
  },
  {
    title: t('aiTool.tag.slug'),
    dataIndex: 'slug',
    key: 'slug',
    width: 150,
    align: 'left'
  },
  {
    title: t('aiTool.tag.count'),
    dataIndex: 'count',
    key: 'count',
    width: 100,
    sorter: true,
    align: 'left'
  },
  {
    title: t('aiTool.tag.description'),
    dataIndex: 'description',
    key: 'description',
    ellipsis: true,
    align: 'left'
  },
  {
    title: t('aiTool.tag.isShow'),
    dataIndex: 'is_show',
    key: 'isShow',
    width: 100,
    align: 'left'
  },
  {
    title: t('aiTool.tag.createTime'),
    dataIndex: 'create_time',
    key: 'createTime',
    width: 180,
    sorter: true,
    align: 'left'
  },
  {
    title: t('aiTool.tag.operations'),
    key: 'action',
    width: 150,
    fixed: 'right',
    align: 'left'
  }
]

// 查询参数
const listQuery = reactive({
  page: 1,
  limit: 10,
  keyword: '',
  order_field: 'count',
  order_type: 'desc',
  is_show: ''
})

// 排序字段选项
const orderFieldOptions = [
  { value: 'id', label: 'ID' },
  { value: 'name', label: t('aiTool.tag.name') },
  { value: 'count', label: t('aiTool.tag.count') },
  { value: 'create_time', label: t('aiTool.tag.createTime') }
]

// 排序方式选项
const orderTypeOptions = [
  { value: 'asc', label: t('aiTool.tag.orderAsc') },
  { value: 'desc', label: t('aiTool.tag.orderDesc') }
]

// 标签选项（用于合并标签）
const tagOptions = ref([])

// 添加/编辑对话框
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const form = reactive({
  id: 0,
  name: '',
  slug: '',
  description: '',
  is_show: true,
  seo_title: '',
  seo_keywords: '',
  seo_description: ''
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: t('aiTool.tag.nameRequired'), trigger: 'blur' },
    { max: 50, message: t('aiTool.tag.nameLength'), trigger: 'blur' }
  ],
  slug: [
    { required: true, message: t('aiTool.tag.slugRequired'), trigger: 'blur' },
    { max: 50, message: t('aiTool.tag.slugLength'), trigger: 'blur' },
    { pattern: /^[a-z0-9-]+$/, message: t('aiTool.tag.slugFormat'), trigger: 'blur' }
  ],
  description: [
    { max: 255, message: t('aiTool.tag.descriptionLength'), trigger: 'blur' }
  ],
  seo_title: [
    { max: 100, message: t('aiTool.tag.seoTitleLength'), trigger: 'blur' }
  ],
  seo_keywords: [
    { max: 255, message: t('aiTool.tag.seoKeywordsLength'), trigger: 'blur' }
  ],
  seo_description: [
    { max: 255, message: t('aiTool.tag.seoDescriptionLength'), trigger: 'blur' }
  ]
}

// 合并标签对话框
const mergeDialogVisible = ref(false)
const mergeFormRef = ref(null)
const mergeForm = reactive({
  source_ids: [],
  target_id: ''
})

// 合并表单验证规则
const mergeRules = {
  source_ids: [
    { required: true, message: t('aiTool.tag.sourceTagsRequired'), trigger: 'change' },
    { type: 'array', min: 1, message: t('aiTool.tag.sourceTagsRequired'), trigger: 'change' }
  ],
  target_id: [
    { required: true, message: t('aiTool.tag.targetTagRequired'), trigger: 'change' }
  ]
}

// 分页配置
const pagination = reactive({
  current: computed(() => listQuery.page),
  pageSize: computed(() => listQuery.limit),
  total: computed(() => total.value),
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '30', '50'],
  showTotal: (total) => `${t('aiTool.tag.total')} ${total} ${t('aiTool.tag.items')}`,
  position: ['bottomRight']
})

// 处理表格变化（排序、筛选、分页）
const handleTableChange = (pag, filters, sorter) => {
  listQuery.page = pag.current
  listQuery.limit = pag.pageSize

  if (sorter.field && sorter.order) {
    listQuery.order_field = sorter.field
    listQuery.order_type = sorter.order === 'ascend' ? 'asc' : 'desc'
  }

  fetchTagList()
}

// 处理页码变化
const handlePageChange = (page) => {
  listQuery.page = page
  fetchTagList()
}

// 处理每页条数变化
const handleSizeChange = (current, size) => {
  listQuery.limit = size
  listQuery.page = 1
  fetchTagList()
}

// 获取标签列表
const fetchTagList = async () => {
  loading.value = true
  try {
    const res = await getToolTagList(listQuery)

    if (res.code === 200 && res.data) {
      tagList.value = res.data.list || []
      total.value = res.data.total || 0

      // 清除选择
      selectedRowKeys.value = []
      selectedTags.value = []
    } else {
      message.error(res.msg || t('aiTool.tag.fetchFailed'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.fetchFailed'), error)
    message.error(t('aiTool.tag.networkError'))
  } finally {
    loading.value = false
  }
}

// 获取所有标签（用于合并标签）
const fetchAllTags = async () => {
  try {
    const res = await getAllToolTags()

    if (res.code === 200) {
      tagOptions.value = res.data || []
    }
  } catch (error) {
    console.error(t('aiTool.tag.fetchAllTagsFailed'), error)
  }
}

// 处理筛选
const handleFilter = () => {
  listQuery.page = 1
  fetchTagList()
}

// 重置筛选
const resetFilter = () => {
  listQuery.keyword = ''
  listQuery.order_field = 'count'
  listQuery.order_type = 'desc'
  listQuery.is_show = ''
  handleFilter()
}

// 处理标签行选择变化
const onSelectChange = (selectedKeys, selectedRows) => {
  selectedRowKeys.value = selectedKeys
  selectedTags.value = selectedRows
}

// 清除选择
const clearSelection = () => {
  selectedRowKeys.value = []
  selectedTags.value = []
}

// 批量删除标签
const handleBatchDelete = () => {
  if (selectedTags.value.length === 0) {
    message.warning(t('aiTool.tag.noSelection'))
    return
  }

  batchDeleteDialogVisible.value = true
}

// 确认批量删除
const confirmBatchDelete = async () => {
  try {
    const ids = selectedTags.value.map(item => item.id)
    const res = await batchDeleteToolTag({ ids })

    if (res.code === 200) {
      message.success(t('aiTool.tag.batchDeleteSuccess'))
      fetchTagList()
      fetchAllTags()
      batchDeleteDialogVisible.value = false
    } else {
      message.error(res.msg || t('aiTool.tag.batchDeleteError'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.batchDeleteError'), error)
    message.error(t('aiTool.tag.networkError'))
  }
}

// 处理单个删除
const handleDelete = (record) => {
  currentTag.value = record
  deleteDialogContent.value = t('aiTool.tag.deleteConfirm')
  deleteDialogVisible.value = true
}

// 确认删除
const confirmDelete = async () => {
  try {
    const res = await deleteToolTag({ id: currentTag.value.id })

    if (res.code === 200) {
      message.success(t('aiTool.tag.deleteSuccess'))
      fetchTagList()
      fetchAllTags()
      deleteDialogVisible.value = false
    } else {
      message.error(res.msg || t('aiTool.tag.deleteError'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.deleteError'), error)
    message.error(t('aiTool.tag.networkError'))
  }
}

// 标签详情抽屉
const detailDrawerVisible = ref(false)
const currentTagDetail = ref({})
const detailLoading = ref(false)

// 查看标签详情
const handleView = async (record) => {
  try {
    detailLoading.value = true
    detailDrawerVisible.value = true

    // 调用API获取标签详情
    const res = await getToolTagInfo({ id: record.id })

    if (res.code === 200) {
      currentTagDetail.value = res.data
    } else {
      message.error(res.msg || t('aiTool.tag.fetchDetailError'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.fetchDetailError'), error)
    message.error(t('aiTool.tag.networkError'))
  } finally {
    detailLoading.value = false
  }
}

// 切换显示状态
const handleStatusChange = async (record, checked) => {
  try {
    const res = await updateToolTagStatus({
      id: record.id,
      is_show: checked ? 1 : 0  // 后端需要数字类型
    })

    if (res.code === 200) {
      message.success(checked ? t('aiTool.tag.showSuccess') : t('aiTool.tag.hideSuccess'))
      // 直接更新当前行的状态，不需要重新加载列表
      record.is_show = checked ? 1 : 0  // 保持与后端数据一致，使用数字
    } else {
      message.error(res.msg || t('aiTool.tag.updateError'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.updateError'), error)
    message.error(t('aiTool.tag.networkError'))
  }
}

// 刷新数据
const handleRefresh = () => {
  fetchTagList()
}

// 根据数量返回颜色
const getCountColor = (count) => {
  if (count > 10) return '#f50'
  if (count > 5) return '#2db7f5'
  return '#87d068'
}

// 导出数据
const exportData = () => {
  const data = tagList.value.map(item => ({
    ID: item.id,
    [t('aiTool.tag.name')]: item.name,
    [t('aiTool.tag.slug')]: item.slug,
    [t('aiTool.tag.count')]: item.count,
    [t('aiTool.tag.description')]: item.description,
    [t('aiTool.tag.createTime')]: formatDate(item.create_time * 1000)
  }))

  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Tags')
  XLSX.writeFile(workbook, 'tags.xlsx')
}

// 处理名称失去焦点，自动生成slug
const handleNameBlur = () => {
  if (!isEdit.value && !form.slug && form.name) {
    form.slug = generateSlug(form.name)
  }
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

// 下拉选项过滤
const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0
}

// 添加标签
const handleAdd = () => {
  isEdit.value = false
  resetForm()
  dialogVisible.value = true
}

// 编辑标签
const handleEdit = async (row) => {
  isEdit.value = true
  resetForm()

  try {
    const res = await getToolTagInfo({ id: row.id })

    if (res.code === 200 && res.data) {
      // 填充表单数据
      Object.keys(form).forEach(key => {
        if (key in res.data) {
          // 特殊处理is_show字段，将数字转换为布尔值
          if (key === 'is_show') {
            form[key] = Boolean(res.data[key])
          } else {
            form[key] = res.data[key]
          }
        }
      })

      dialogVisible.value = true
    } else {
      message.error(res.msg || t('aiTool.tag.fetchDetailFailed'))
    }
  } catch (error) {
    console.error(t('aiTool.tag.fetchDetailFailed'), error)
    message.error(t('aiTool.tag.networkError'))
  }
}

// 重置表单
const resetForm = () => {
  form.id = 0
  form.name = ''
  form.slug = ''
  form.description = ''
  form.is_show = true
  form.seo_title = ''
  form.seo_keywords = ''
  form.seo_description = ''

  if (formRef.value) {
    formRef.value.resetFields()
  }
}

// 提交表单
const submitForm = async () => {
  try {
    await formRef.value.validate()
    // 验证通过
    const apiMethod = isEdit.value ? updateToolTag : createToolTag

    // 将布尔值转换为数字，以便后端处理
    const formData = { ...form, is_show: form.is_show ? 1 : 0 }
    const res = await apiMethod(formData)

    if (res.code === 200) {
      message.success(isEdit.value ? t('aiTool.tag.updateSuccess') : t('aiTool.tag.createSuccess'))
      dialogVisible.value = false
      fetchTagList()
      fetchAllTags()
    } else {
      message.error(res.msg || (isEdit.value ? t('aiTool.tag.updateError') : t('aiTool.tag.createError')))
    }
  } catch (error) {
    // 验证失败或请求错误
    console.error(isEdit.value ? t('aiTool.tag.updateError') : t('aiTool.tag.createError'), error)
    message.error(t('aiTool.tag.networkError'))
    return false
  }
}

// 显示合并标签对话框
const showMergeDialog = () => {
  mergeForm.source_ids = []
  mergeForm.target_id = ''
  mergeDialogVisible.value = true
}

// 提交合并表单
const submitMergeForm = async () => {
  try {
    await mergeFormRef.value.validate()
    // 验证通过

    // 检查源标签和目标标签是否相同
    if (mergeForm.source_ids.includes(mergeForm.target_id)) {
      message.error(t('aiTool.tag.mergeSourceTargetSame'))
      return
    }

    try {
      const res = await mergeToolTags(mergeForm)

      if (res.code === 200) {
        message.success(t('aiTool.tag.mergeSuccess'))
        mergeDialogVisible.value = false
        fetchTagList()
        fetchAllTags()
      } else {
        message.error(res.msg || t('aiTool.tag.mergeError'))
      }
    } catch (error) {
      console.error(t('aiTool.tag.mergeError'), error)
      message.error(t('aiTool.tag.networkError'))
    }
  } catch (error) {
    // 验证失败
    console.error('Form validation failed:', error)
    return false
  }
}

// 监听名称变化，自动生成别名
watch(() => form.name, (newVal, oldVal) => {
  if (!isEdit.value && !form.slug && newVal) {
    form.slug = generateSlug(newVal)
  }
})

onMounted(() => {
  fetchTagList()
  fetchAllTags()
})
</script>

<style scoped>
.ai-tool-tag-container {
  padding: 16px;
}

.mb-4 {
  margin-bottom: 16px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.filter-container {
  margin-bottom: 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.filter-item {
  margin-right: 10px;
}

.batch-container {
  margin-bottom: 20px;
}

.batch-actions {
  margin-top: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.merge-warning {
  margin-top: 20px;
}

/* 统计卡片样式 */
.stat-card {
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;
  height: 100%;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.stat-card-content {
  text-align: center;
  padding: 16px 0;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: #1890ff;
}

.stat-label {
  font-size: 14px;
  color: #666;
  margin-top: 8px;
}

/* 搜索表单样式 */
.search-form {
  background-color: #f5f5f5;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

/* 操作按钮样式 */
.action-buttons {
  margin-top: 8px;
  margin-bottom: 16px;
}

/* 标签详情样式 */
.tag-detail {
  padding: 16px 0;
}

.tag-detail .ant-descriptions {
  margin-bottom: 24px;
}

.related-tools {
  margin-top: 24px;
}

.related-tools h3 {
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: 500;
}

.no-related-tools {
  margin-top: 24px;
  text-align: center;
  color: #999;
  padding: 24px 0;
  background-color: #f5f5f5;
  border-radius: 4px;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .stat-card {
    margin-bottom: 16px;
  }

  .action-buttons {
    justify-content: flex-start !important;
  }
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
