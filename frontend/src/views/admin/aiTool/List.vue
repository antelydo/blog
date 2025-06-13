<template>
  <div class="ai-tool-list-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.tool.management')" class="list-card" :headStyle="{textAlign: 'left'}">

      <!-- 统计卡片 -->
      <a-row :gutter="16" class="stat-row">
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><AppstoreOutlined /> {{ $t('aiTool.tool.totalTools') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total }}</div>
              <div class="stat-label">{{ $t('aiTool.tool.toolsCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><CheckCircleOutlined /> {{ $t('aiTool.tool.publishedTools') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ publishedCount }}</div>
              <div class="stat-label">{{ $t('aiTool.tool.publishedCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><LikeOutlined /> {{ $t('aiTool.tool.recommendedTools') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ recommendedCount }}</div>
              <div class="stat-label">{{ $t('aiTool.tool.recommendCount') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><StarOutlined /> {{ $t('aiTool.tool.topTools') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ topCount }}</div>
              <div class="stat-label">{{ $t('aiTool.tool.topCount') }}</div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <!-- 搜索和筛选 -->
      <a-form layout="inline" :model="listQuery" class="search-form">
        <a-row :gutter="24" style="width: 100%">
          <a-col :xs="24" :sm="12" :md="8" :lg="6">
            <a-form-item :label="$t('aiTool.tool.keyword')" style="margin-bottom: 16px; width: 100%">
              <a-input
                v-model:value="listQuery.keyword"
                :placeholder="$t('aiTool.tool.searchPlaceholder')"
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
          <a-col :xs="24" :sm="12" :md="10" :lg="8">
            <a-form-item :label="$t('aiTool.tool.category')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.category_ids"
                :placeholder="$t('aiTool.tool.categoryFilter')"
                mode="multiple"
                :max-tag-count="2"
                :options="flatCategoryOptions"
                :field-names="{ label: 'name', value: 'id' }"
                allow-clear
                show-search
                :filter-option="filterCategoryOption"
                @change="handleCategoryChange"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tool.status')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.status"
                :placeholder="$t('aiTool.tool.statusFilter')"
                style="width: 100%"
                allow-clear
              >
                <a-select-option v-for="item in statusOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4" class="search-buttons" style="margin-bottom: 16px">
            <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
              <a-space>
                <a-button type="primary" @click="handleFilter">
                  <template #icon><SearchOutlined /></template>
                  {{ $t('aiTool.tool.search') }}
                </a-button>
                <a-button @click="resetFilter">
                  <template #icon><ReloadOutlined /></template>
                  {{ $t('aiTool.tool.reset') }}
                </a-button>
                <a-button type="link" @click="toggleAdvanced" style="padding: 0 8px">
                  <template #icon><DownOutlined v-if="!advanced" /><UpOutlined v-else /></template>
                  {{ advanced ? $t('aiTool.tool.collapse') : $t('aiTool.tool.expand') }}
                </a-button>
              </a-space>
            </a-form-item>
          </a-col>
        </a-row>

        <!-- 高级搜索选项 -->
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tool.pricing')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.pricing_type"
                :placeholder="$t('aiTool.tool.pricingFilter')"
                style="width: 100%"
                allow-clear
              >
                <a-select-option v-for="item in pricingOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tool.tag')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.tag_id"
                :placeholder="$t('aiTool.tool.tagFilter')"
                style="width: 100%"
                allow-clear
              >
                <a-select-option v-for="item in tagOptions" :key="item.id" :value="item.id">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tool.recommend')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.is_recommended"
                :placeholder="$t('aiTool.tool.recommendFilter')"
                style="width: 100%"
                allow-clear
              >
                <a-select-option :value="1">{{ $t('aiTool.tool.recommended') }}</a-select-option>
                <a-select-option :value="0">{{ $t('aiTool.tool.notRecommended') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12" :md="7" :lg="4">
            <a-form-item :label="$t('aiTool.tool.top')" style="margin-bottom: 16px; width: 100%">
              <a-select
                v-model:value="listQuery.is_top"
                :placeholder="$t('aiTool.tool.topFilter')"
                style="width: 100%"
                allow-clear
              >
                <a-select-option :value="1">{{ $t('aiTool.tool.topped') }}</a-select-option>
                <a-select-option :value="0">{{ $t('aiTool.tool.notTopped') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <!-- 操作按钮 -->
      <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <a-space>
          <a-button type="primary" @click="handleAdd">
            <template #icon><PlusOutlined /></template>
            {{ $t('aiTool.tool.add') }}
          </a-button>
          <a-dropdown v-if="selectedTools.length > 0">
            <a-button type="primary">
              <template #icon><SettingOutlined /></template>
              {{ $t('aiTool.tool.batchActions') }} ({{ selectedTools.length }})
              <DownOutlined />
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item key="1" @click="handleBatchAction('publish')">
                  <CheckCircleOutlined /> {{ $t('aiTool.tool.batchPublish') }}
                </a-menu-item>
                <a-menu-item key="2" @click="handleBatchAction('draft')">
                  <FileOutlined /> {{ $t('aiTool.tool.batchDraft') }}
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="3" @click="handleBatchAction('recommend')">
                  <LikeOutlined /> {{ $t('aiTool.tool.batchRecommend') }}
                </a-menu-item>
                <a-menu-item key="4" @click="handleBatchAction('unrecommend')">
                  <DislikeOutlined /> {{ $t('aiTool.tool.batchUnrecommend') }}
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="5" @click="handleBatchAction('delete')">
                  <DeleteOutlined /> {{ $t('aiTool.tool.batchDelete') }}
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="6" @click="clearSelection">
                  <ClearOutlined /> {{ $t('aiTool.tool.clearSelection') }}
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
          <a-button @click="handleRefresh">
            <template #icon><ReloadOutlined /></template>
            {{ $t('aiTool.tool.refresh') }}
          </a-button>
          <a-button type="primary" @click="exportData">
            <template #icon><DownloadOutlined /></template>
            {{ $t('aiTool.tool.export') }}
          </a-button>
        </a-space>
      </div>

      <!-- 工具列表 -->
      <a-table
        :dataSource="toolList"
        :columns="columns"
        :rowKey="record => record.id"
        :pagination="false"
        :loading="loading"
        :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
        @change="handleTableChange"
        bordered
        size="middle"
        :scroll="{ x: 1300 }"
        class="tool-table"
      >
        <template #bodyCell="{ column, record }">
          <!-- 工具图标 -->
          <template v-if="column.key === 'logo'">
            <a-image
              :width="40"
              :height="40"
              :src="record.logo || '/images/default-logo.png'"
              :fallback="'/images/default-logo.png'"
              :preview="{
                src: record.logo || '/images/default-logo.png',
              }"
            />
          </template>

          <!-- 工具名称 -->
          <template v-if="column.key === 'name'">
            <div class="tool-name-container">
              <span class="tool-name">{{ record.name }}</span>
              <div class="tool-tags">
                <a-tag v-if="record.is_verified" color="green">
                  <template #icon><CheckCircleOutlined /></template>
                  {{ $t('aiTool.tool.verified') }}
                </a-tag>
              </div>
            </div>
          </template>

          <!-- 分类 -->
          <template v-if="column.key === 'category'">
            <div class="category-list">
              <a-tag color="blue" v-if="record.category">
                {{ record.category.name }}
              </a-tag>
              <a-tag
                v-for="category in record.categories"
                :key="category.id"
                color="blue"
                class="category-item"
              >
                {{ category.name }}
              </a-tag>
              <span v-if="!record.category && (!record.categories || record.categories.length === 0)" class="no-categories">
                {{ $t('aiTool.tool.noCategory') }}
              </span>
            </div>
          </template>

          <!-- 标签 -->
          <template v-if="column.key === 'tags'">
            <div class="tag-list">
              <a-tag
                v-for="tag in record.tags"
                :key="tag.id"
                color="cyan"
                class="tag-item"
              >
                {{ tag.name }}
              </a-tag>
              <span v-if="!record.tags || record.tags.length === 0" class="no-tags">
                {{ $t('aiTool.tool.noTags') }}
              </span>
            </div>
          </template>

          <!-- 价格类型 -->
          <template v-if="column.key === 'pricing'">
            <a-tag :color="getPricingTagColor(record.pricing_type)">
              {{ getPricingTypeText(record.pricing_type) }}
            </a-tag>
          </template>

          <!-- 评分 -->
          <template v-if="column.key === 'rating'">
            <div class="rating">
              <a-rate :value="record.rating" disabled allow-half />
              <span class="rating-count">({{ record.rating_count || 0 }})</span>
            </div>
          </template>

          <!-- 状态 -->
          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusTagColor(record.status)">
              {{ getStatusText(record.status) }}
            </a-tag>
          </template>

          <!-- 特色功能 -->
          <template v-if="column.key === 'featured'">
            <div class="featured-actions">
              <a-tooltip :title="record.is_recommended ? $t('aiTool.tool.cancelRecommend') : $t('aiTool.tool.recommend')">
                <a-button
                  :type="record.is_recommended ? 'primary' : 'default'"
                  shape="circle"
                  size="small"
                  class="feature-button"
                  @click="handleRecommend(record)"
                >
                  <template #icon>
                    <LikeOutlined />
                  </template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="record.is_top ? $t('aiTool.tool.cancelTop') : $t('aiTool.tool.top')">
                <a-button
                  :type="record.is_top ? 'primary' : 'default'"
                  shape="circle"
                  size="small"
                  class="feature-button"
                  @click="handleTop(record)"
                >
                  <template #icon>
                    <StarOutlined />
                  </template>
                </a-button>
              </a-tooltip>
            </div>
          </template>

          <!-- 创建时间 -->
          <template v-if="column.key === 'createTime'">
            {{record.create_time }}
          </template>

          <!-- 操作按钮 -->
          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip :title="$t('aiTool.tool.edit')">
                <a-button type="primary" shape="circle" size="small" class="action-button" @click="handleEdit(record)">
                  <template #icon><EditOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.tool.view')">
                <a-button shape="circle" size="small" class="action-button" @click="handleView(record)">
                  <template #icon><EyeOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip :title="$t('aiTool.tool.delete')">
                <a-button type="primary" danger shape="circle" size="small" class="action-button" @click="handleDelete(record)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
      </a-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <a-pagination
          v-model:current="listQuery.page"
          v-model:pageSize="listQuery.limit"
          :total="total"
          :showSizeChanger="true"
          :pageSizeOptions="['10', '20', '30', '50']"
          :showTotal="total => `${$t('aiTool.tool.total')} ${total} ${$t('aiTool.tool.items')}`"
          @change="handlePageChange"
          @showSizeChange="handleSizeChange"
        />
      </div>

      <!-- 工具详情抽屉 -->
      <a-drawer
        :title="$t('aiTool.tool.detailTitle')"
        :width="600"
        :visible="drawerVisible"
        @close="closeDrawer"
        :bodyStyle="{ paddingBottom: '80px' }"
        :headerStyle="{ borderBottom: '1px solid #f0f0f0' }"
      >
        <a-spin :spinning="detailLoading">
          <div v-if="toolDetail" class="tool-detail">
            <!-- Banner 区域 -->
            <div v-if="toolDetail.banner" class="detail-banner-container">
              <a-image
                :src="toolDetail.banner"
                alt="banner"
                :width="'100%'"
                :height="200"
                class="banner-image"
                :preview="{
                  src: toolDetail.banner,
                  mask: $t('aiTool.tool.clickToPreview'),
                  maskClassName: 'custom-mask'
                }"
              />
            </div>

            <!-- 基本信息 -->
            <div class="detail-section">
              <h3 class="section-title">{{ $t('aiTool.tool.basicInfo') }}</h3>
              <div class="detail-header">
                <div class="detail-logo">
                  <a-image
                    :src="toolDetail.logo || '/placeholder.png'"
                    alt="logo"
                    :width="80"
                    :height="80"
                    :preview="{
                      src: toolDetail.logo || '/placeholder.png',
                      mask: $t('aiTool.tool.clickToPreview'),
                      maskClassName: 'custom-mask'
                    }"
                  />
                </div>
                <div class="detail-basic">
                  <h2 class="detail-name">{{ toolDetail.name }}</h2>
                  <div class="detail-meta">
                    <a-tag v-if="toolDetail.status === 'published'" color="success">{{ $t('aiTool.tool.published') }}</a-tag>
                    <a-tag v-else-if="toolDetail.status === 'draft'" color="warning">{{ $t('aiTool.tool.draft') }}</a-tag>
                    <a-tag v-else-if="toolDetail.status === 'pending'" color="processing">{{ $t('aiTool.tool.pending') }}</a-tag>
                    <a-tag v-if="toolDetail.is_recommended" color="blue">{{ $t('aiTool.tool.recommended') }}</a-tag>
                    <a-tag v-if="toolDetail.is_top" color="purple">{{ $t('aiTool.tool.top') }}</a-tag>
                  </div>
                  <div class="detail-slug">{{ $t('aiTool.tool.slug') }}: {{ toolDetail.slug }}</div>
                </div>
              </div>

              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.shortDescription') }}:</span>
                <span class="detail-value">{{ toolDetail.short_description }}</span>
              </div>

              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.websiteUrl') }}:</span>
                <a :href="toolDetail.website_url" target="_blank" class="detail-link">{{ toolDetail.website_url }}</a>
              </div>

              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.pricingType') }}:</span>
                <span class="detail-value">
                  <a-tag v-if="toolDetail.pricing_type === 'free'" color="green">{{ $t('aiTool.tool.free') }}</a-tag>
                  <a-tag v-else-if="toolDetail.pricing_type === 'freemium'" color="blue">{{ $t('aiTool.tool.freemium') }}</a-tag>
                  <a-tag v-else-if="toolDetail.pricing_type === 'paid'" color="orange">{{ $t('aiTool.tool.paid') }}</a-tag>
                </span>
              </div>
            </div>

            <!-- 分类和标签 -->
            <div class="detail-section">
              <h3 class="section-title">{{ $t('aiTool.tool.categoriesAndTags') }}</h3>
              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.categories') }}:</span>
                <span class="detail-value">
                  <a-tag v-for="category in toolDetail.categories" :key="category.id" color="blue">
                    {{ category.name }}
                  </a-tag>
                </span>
              </div>

              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.tags') }}:</span>
                <span class="detail-value">
                  <a-tag v-for="tag in toolDetail.tags" :key="tag.id">
                    {{ tag.name }}
                  </a-tag>
                </span>
              </div>
            </div>

            <!-- 详细描述 -->
            <div class="detail-section">
              <h3 class="section-title">{{ $t('aiTool.tool.description') }}</h3>
              <div class="detail-description" v-html="toolDetail.description"></div>
            </div>

            <!-- 特点 -->
            <div class="detail-section" v-if="toolDetail.features">
              <h3 class="section-title">{{ $t('aiTool.tool.features') }}</h3>
              <div class="feature-paragraph">
                <div class="feature-item" v-for="(feature, index) in parsedFeatures" :key="index">
                  <CheckCircleFilled class="feature-icon" />
                  <span class="feature-text">{{ feature }}</span>
                </div>
                <div v-if="parsedFeatures.length === 0" class="no-features">
                  {{ $t('aiTool.tool.noFeatures') }}
                </div>
              </div>
            </div>

            <!-- 截图 -->
            <div class="detail-section" v-if="parsedScreenshots.length > 0">
              <h3 class="section-title">{{ $t('aiTool.tool.screenshots') }}</h3>
              <div class="detail-screenshots">
                <a-image
                  v-for="(screenshot, index) in parsedScreenshots"
                  :key="index"
                  :src="screenshot"
                  :width="150"
                  :height="100"
                  style="margin-right: 10px; margin-bottom: 10px;"
                  :preview="{
                    src: screenshot,
                    mask: $t('aiTool.tool.clickToPreview'),
                    maskClassName: 'custom-mask',
                    previewGroup: true
                  }"
                />
              </div>
            </div>

            <!-- 系统信息 -->
            <div class="detail-section">
              <h3 class="section-title">{{ $t('aiTool.tool.systemInfo') }}</h3>
              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.createTime') }}:</span>
                <span class="detail-value">{{ formatDate(toolDetail.create_time) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">{{ $t('aiTool.tool.updateTime') }}:</span>
                <span class="detail-value">{{ formatDate(toolDetail.update_time) }}</span>
              </div>
              <div class="detail-item" v-if="toolDetail.publish_time">
                <span class="detail-label">{{ $t('aiTool.tool.publishTime') }}:</span>
                <span class="detail-value">{{ formatDate(toolDetail.publish_time) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">ID:</span>
                <span class="detail-value">{{ toolDetail.id }}</span>
              </div>
            </div>
          </div>
          <div v-else-if="!detailLoading" class="no-data">
            {{ $t('aiTool.tool.noDetailData') }}
          </div>
        </a-spin>

        <template #footer>
          <div style="text-align: right;">
            <a-space>
              <a-button @click="closeDrawer">{{ $t('aiTool.tool.close') }}</a-button>
              <a-button type="primary" @click="handleEdit(toolDetail)">{{ $t('aiTool.tool.edit') }}</a-button>
              <a-button type="primary" @click="openInNewTab">{{ $t('aiTool.tool.viewInFrontend') }}</a-button>
            </a-space>
          </div>
        </template>
      </a-drawer>
    </a-card>

    <!-- 删除确认对话框 -->
    <a-modal
      :open="deleteDialogVisible"
      :title="$t('aiTool.tool.deleteConfirmTitle')"
      @cancel="deleteDialogVisible = false"
      @ok="confirmDelete"
      :okButtonProps="{ danger: true }"
      :okText="$t('aiTool.tool.confirm')"
      :cancelText="$t('aiTool.tool.cancel')"
    >
      <p>{{ $t('aiTool.tool.deleteConfirmContent') }}</p>
    </a-modal>

    <!-- 批量操作确认对话框 -->
    <a-modal
      :open="batchDialogVisible"
      :title="batchDialogTitle"
      @cancel="batchDialogVisible = false"
      @ok="confirmBatchAction"
      :okButtonProps="{ danger: batchAction === 'delete' }"
      :okText="$t('aiTool.tool.confirm')"
      :cancelText="$t('aiTool.tool.cancel')"
    >
      <p>{{ batchDialogContent }}</p>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, h } from 'vue'
import { useRouter } from 'vue-router'
import { message, Modal } from 'ant-design-vue'
import { useI18n } from 'vue-i18n'
import { formatDate } from '@/utils/date'
import {
  getToolList,
  getToolInfo,
  deleteTool,
  recommendTool,
  topTool,
  batchTool
} from '@/api/admin/aiTool'
import { getToolCategoryList } from '@/api/admin/aiToolCategory'
import { getAllToolTags } from '@/api/admin/aiToolTag'
import * as XLSX from 'xlsx'

// 导入Ant Design Vue图标
import {
  PlusOutlined,
  SearchOutlined,
  ReloadOutlined,
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  SettingOutlined,
  DownOutlined,
  UpOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  StarOutlined,
  LikeOutlined,
  DislikeOutlined,
  FileOutlined,
  DownloadOutlined,
  ClearOutlined,
  AppstoreOutlined,
  CheckCircleFilled
} from '@ant-design/icons-vue'

const { t } = useI18n()
const router = useRouter()

// 高级搜索开关
const advanced = ref(false)
const toggleAdvanced = () => {
  advanced.value = !advanced.value
}

// 工具列表数据
const loading = ref(false)
const toolList = ref([])
const total = ref(0)
const selectedTools = ref([])
const selectedRowKeys = ref([])

// 工具详情抽屉相关
const drawerVisible = ref(false)
const detailLoading = ref(false)
const toolDetail = ref(null)

// 解析功能特点
const parsedFeatures = computed(() => {
  if (!toolDetail.value || !toolDetail.value.features) return [];

  try {
    let featuresArray = [];

    // 如果是字符串，尝试解析JSON
    if (typeof toolDetail.value.features === 'string') {
      try {
        const parsed = JSON.parse(toolDetail.value.features);
        if (Array.isArray(parsed)) {
          featuresArray = parsed;
        } else {
          console.warn('Features is not an array after parsing:', parsed);
          return [];
        }
      } catch (parseError) {
        // 如果不是JSON，将其作为单个字符串处理
        return [toolDetail.value.features];
      }
    } else if (Array.isArray(toolDetail.value.features)) {
      // 如果已经是数组，直接使用
      featuresArray = toolDetail.value.features;
    } else {
      console.warn('Features is neither a string nor an array:', toolDetail.value.features);
      return [];
    }

    // 处理数组中的每一项
    return featuresArray
      .filter(item => item !== null && item !== undefined)
      .map(item => {
        // 如果是字符串，直接返回
        if (typeof item === 'string') {
          return item.trim() === '' ? null : item.trim();
        }

        // 如果是对象，尝试提取有用的信息
        if (typeof item === 'object') {
          // 如果对象有name或title属性，使用它
          if (item.name) return item.name;
          if (item.title) return item.title;
          if (item.text) return item.text;
          if (item.content) return item.content;
          if (item.description) return item.description;

          // 如果有其他属性，尝试使用第一个字符串属性
          for (const key in item) {
            if (typeof item[key] === 'string' && item[key].trim() !== '') {
              return item[key];
            }
          }

          // 如果没有可用的字符串属性，返回null（将在后面被过滤掉）
          return null;
        }

        // 其他类型（数字、布尔值等）转换为字符串
        return String(item);
      })
      .filter(item => item !== null); // 过滤掉null值
  } catch (error) {
    console.error('Error parsing features:', error);
    return [];
  }
});

// 解析截图
const parsedScreenshots = computed(() => {
  if (!toolDetail.value || !toolDetail.value.screenshots) return [];

  try {
    // 如果是字符串，尝试解析JSON
    if (typeof toolDetail.value.screenshots === 'string') {
      try {
        const parsed = JSON.parse(toolDetail.value.screenshots);
        if (Array.isArray(parsed)) {
          return parsed.filter(url => url && typeof url === 'string' && url.trim() !== '');
        } else {
          console.warn('Screenshots is not an array after parsing:', parsed);
          return [];
        }
      } catch (parseError) {
        console.error('Error parsing screenshots JSON:', parseError);
        // 如果不是JSON，尝试将其作为单个字符串处理
        if (typeof toolDetail.value.screenshots === 'string' && toolDetail.value.screenshots.trim() !== '') {
          return [toolDetail.value.screenshots];
        }
        return [];
      }
    } else if (Array.isArray(toolDetail.value.screenshots)) {
      // 如果已经是数组，直接过滤并使用
      return toolDetail.value.screenshots.filter(url => url && typeof url === 'string' && url.trim() !== '');
    } else {
      console.warn('Screenshots is neither a string nor an array:', toolDetail.value.screenshots);
      return [];
    }
  } catch (error) {
    console.error('Error processing screenshots:', error);
    return [];
  }
})

// 统计数据
const publishedCount = computed(() => {
  return toolList.value.filter(item => item.status == 'published').length
})

const recommendedCount = computed(() => {
  return toolList.value.filter(item => item.is_recommended == 1).length
})

const topCount = computed(() => {
  return toolList.value.filter(item => item.is_top == 1).length
})

// 查询参数
const listQuery = reactive({
  page: 1,
  limit: 10,
  keyword: '',
  category_id: '',  // 兼容旧版本
  category_ids: [], // 新增多选分类
  status: '',
  pricing_type: '',
  tag_id: '',
  order_field: 'create_time',
  order_type: 'desc'
})

// 处理分类变更
const handleCategoryChange = (values) => {
  console.log('Category selection changed:', values)

  // 如果只选了一个分类，同时设置category_id以兼容旧版本
  if (values && values.length === 1) {
    listQuery.category_id = values[0]
  } else {
    listQuery.category_id = ''
  }

  // 重置到第一页
  listQuery.page = 1

  // 获取列表
  fetchToolList()
}

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
    title: t('aiTool.tool.logo'),
    dataIndex: 'logo',
    key: 'logo',
    width: 80,
    align: 'left'
  },
  {
    title: t('aiTool.tool.name'),
    dataIndex: 'name',
    key: 'name',
    ellipsis: true,
    width: 180,
    align: 'left'
  },
  {
    title: t('aiTool.tool.category'),
    dataIndex: ['category', 'name'],
    key: 'category',
    width: 120,
    align: 'left'
  },
  {
    title: t('aiTool.tool.tags'),
    dataIndex: 'tags',
    key: 'tags',
    width: 150,
    align: 'left'
  },
  {
    title: t('aiTool.tool.pricing'),
    dataIndex: 'pricing_type',
    key: 'pricing',
    width: 100,
    align: 'left'
  },
  {
    title: t('aiTool.tool.views'),
    dataIndex: 'views',
    key: 'views',
    width: 100,
    sorter: true,
    align: 'left'
  },
  {
    title: t('aiTool.tool.rating'),
    dataIndex: 'rating',
    key: 'rating',
    width: 150,
    sorter: true,
    align: 'left'
  },
  {
    title: t('aiTool.tool.status'),
    dataIndex: 'status',
    key: 'status',
    width: 100,
    filters: [
      { text: t('aiTool.tool.enabled'), value: 1 },
      { text: t('aiTool.tool.disabled'), value: 0 }
    ],
    align: 'left'
  },
  {
    title: t('aiTool.tool.featured'),
    key: 'featured',
    width: 120,
    align: 'left'
  },
  {
    title: t('aiTool.tool.createTime'),
    dataIndex: 'create_time',
    key: 'createTime',
    width: 180,
    sorter: true,
    align: 'left',
    customRender: ({ text }) => {
      return text ? formatDate(text) : ''
    }
  },
  {
    title: t('aiTool.tool.operations'),
    key: 'action',
    width: 150,
    fixed: 'right',
    align: 'left'
  }
]

// 分类选项
const categoryOptions = ref([])

// 扁平化的分类选项
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

// 过滤分类选项
const filterCategoryOption = (input, option) => {
  return option.name.toLowerCase().indexOf(input.toLowerCase()) >= 0
}

// 标签选项
const tagOptions = ref([])

// 状态选项
const statusOptions = [
  { value: 'published', label: t('aiTool.tool.statusPublished') },
  { value: 'draft', label: t('aiTool.tool.statusDraft') },
  { value: 'pending', label: t('aiTool.tool.statusPending') }
]

// 价格类型选项
const pricingOptions = [
  { value: 'free', label: t('aiTool.tool.pricingFree') },
  { value: 'freemium', label: t('aiTool.tool.pricingFreemium') },
  { value: 'paid', label: t('aiTool.tool.pricingPaid') }
]

// 分页配置
const pagination = computed(() => ({
  current: listQuery.page,
  pageSize: listQuery.limit,
  total: total.value,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `${t('aiTool.tool.total')} ${total} ${t('aiTool.tool.items')}`
}))

// 删除对话框
const deleteDialogVisible = ref(false)
const currentTool = ref(null)

// 批量操作对话框
const batchDialogVisible = ref(false)
const batchAction = ref('')
const batchDialogTitle = ref('')
const batchDialogContent = ref('')

// 获取工具列表
const fetchToolList = async () => {
  loading.value = true
  try {
    // 创建查询参数的副本
    const queryParams = { ...listQuery }

    // 处理多选分类
    if (queryParams.category_ids && queryParams.category_ids.length > 0) {
      // 如果有多选分类，将其转换为逗号分隔的字符串
      queryParams.category_ids_str = queryParams.category_ids.join(',')
      console.log('Sending category_ids_str:', queryParams.category_ids_str)
    }

    const res = await getToolList(queryParams)

    if (res.code === 200 && res.data) {
      toolList.value = res.data.list || []
      total.value = res.data.total || 0

      // 清除选择
      selectedRowKeys.value = []
      selectedTools.value = []
    } else {
      message.error(res.msg || t('aiTool.tool.fetchFailed'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.fetchFailed'), error)
    message.error(t('aiTool.tool.networkError'))
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
    console.error(t('aiTool.tool.fetchCategoriesFailed'), error)
    message.error(t('aiTool.tool.fetchCategoriesFailed'))
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
    console.error(t('aiTool.tool.fetchTagsFailed'), error)
    message.error(t('aiTool.tool.fetchTagsFailed'))
  }
}

// 处理筛选
const handleFilter = () => {
  listQuery.page = 1
  fetchToolList()
}

// 重置筛选
const resetFilter = () => {
  listQuery.keyword = ''
  listQuery.category_id = ''
  listQuery.category_ids = []
  listQuery.status = ''
  listQuery.pricing_type = ''
  listQuery.tag_id = ''
  advanced.value = false
  handleFilter()
}

// 刷新数据
const handleRefresh = () => {
  fetchToolList()
}

// 处理页码变化
const handlePageChange = (page, pageSize) => {
  listQuery.page = page
  listQuery.limit = pageSize
  fetchToolList()
}

// 处理每页数量变化
const handleSizeChange = (current, size) => {
  listQuery.limit = size
  listQuery.page = 1
  fetchToolList()
}

// 处理表格变化
const handleTableChange = (pagination, filters, sorter) => {
  // 处理排序
  if (sorter && sorter.field) {
    listQuery.order_field = sorter.field
    listQuery.order_type = sorter.order === 'ascend' ? 'asc' : 'desc'
  }

  // 处理筛选
  if (filters && filters.status && filters.status.length > 0) {
    listQuery.status = filters.status[0]
  }

  fetchToolList()
}

// 添加工具
const handleAdd = () => {
  router.push('/admin/aiTool-create')
}

// 编辑工具
const handleEdit = (row) => {
  router.push(`/admin/aiTool-edit/${row.id}`)
}

// 使用导入的formatDate函数

// 查看工具
const handleView = async (row) => {
  toolDetail.value = null
  drawerVisible.value = true
  detailLoading.value = true

  try {
    const res = await getToolInfo({ id: row.id })
    if (res.code === 200) {
      toolDetail.value = res.data
    } else {
      message.error(res.msg || t('aiTool.tool.fetchDetailError'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.fetchDetailError'), error)
    message.error(t('aiTool.tool.networkError'))
  } finally {
    detailLoading.value = false
  }
}

// 关闭抽屉
const closeDrawer = () => {
  drawerVisible.value = false
}

// 在新标签页中打开前台页面
const openInNewTab = () => {
  if (toolDetail.value && toolDetail.value.id) {
    window.open(`/tool/${toolDetail.value.id}`, '_blank')
  }
}

// 删除工具
const handleDelete = (row) => {
  currentTool.value = row
  deleteDialogVisible.value = true
}

// 确认删除
const confirmDelete = async () => {
  try {
    const res = await deleteTool({ id: currentTool.value.id })

    if (res.code === 200) {
      message.success(t('aiTool.tool.deleteSuccess'))
      deleteDialogVisible.value = false
      fetchToolList()
    } else {
      message.error(res.msg || t('aiTool.tool.deleteError'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.deleteError'), error)
    message.error(t('aiTool.tool.networkError'))
  }
}

// 推荐工具
const handleRecommend = async (row) => {
  try {
    const res = await recommendTool({
      id: row.id,
      is_recommended: !row.is_recommended
    })

    if (res.code === 200) {
      message.success(row.is_recommended ? t('aiTool.tool.cancelRecommendSuccess') : t('aiTool.tool.recommendSuccess'))
      row.is_recommended = !row.is_recommended
    } else {
      message.error(res.msg || t('aiTool.tool.recommendError'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.recommendError'), error)
    message.error(t('aiTool.tool.networkError'))
  }
}

// 置顶工具
const handleTop = async (row) => {
  try {
    const res = await topTool({
      id: row.id,
      is_top: !row.is_top
    })

    if (res.code === 200) {
      message.success(row.is_top ? t('aiTool.tool.cancelTopSuccess') : t('aiTool.tool.topSuccess'))
      row.is_top = !row.is_top
    } else {
      message.error(res.msg || t('aiTool.tool.topError'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.topError'), error)
    message.error(t('aiTool.tool.networkError'))
  }
}

// 处理选择变化
const onSelectChange = (keys, rows) => {
  selectedRowKeys.value = keys
  selectedTools.value = rows
}

// 清除选择
const clearSelection = () => {
  selectedRowKeys.value = []
  selectedTools.value = []
}

// 批量操作
const handleBatchAction = (action) => {
  if (selectedTools.value.length === 0) {
    message.warning(t('aiTool.tool.noSelection'))
    return
  }

  // 设置批量操作对话框标题和内容
  batchAction.value = action

  const actionTitleMap = {
    'publish': t('aiTool.tool.batchPublishTitle'),
    'draft': t('aiTool.tool.batchDraftTitle'),
    'recommend': t('aiTool.tool.batchRecommendTitle'),
    'unrecommend': t('aiTool.tool.batchUnrecommendTitle'),
    'delete': t('aiTool.tool.batchDeleteTitle')
  }

  const actionContentMap = {
    'publish': t('aiTool.tool.batchPublishConfirm', { count: selectedTools.value.length }),
    'draft': t('aiTool.tool.batchDraftConfirm', { count: selectedTools.value.length }),
    'recommend': t('aiTool.tool.batchRecommendConfirm', { count: selectedTools.value.length }),
    'unrecommend': t('aiTool.tool.batchUnrecommendConfirm', { count: selectedTools.value.length }),
    'delete': t('aiTool.tool.batchDeleteConfirm', { count: selectedTools.value.length })
  }

  batchDialogTitle.value = actionTitleMap[action] || t('aiTool.tool.batchActionTitle')
  batchDialogContent.value = actionContentMap[action] || t('aiTool.tool.batchActionConfirm')
  batchDialogVisible.value = true
}

// 确认批量操作
const confirmBatchAction = async () => {
  try {
    const ids = selectedTools.value.map(item => item.id)
    const res = await batchTool({ ids, action: batchAction.value })

    if (res.code === 200) {
      message.success(t(`aiTool.tool.batch${batchAction.value.charAt(0).toUpperCase() + batchAction.value.slice(1)}Success`))
      batchDialogVisible.value = false
      clearSelection()
      fetchToolList()
    } else {
      message.error(res.msg || t('aiTool.tool.batchError'))
    }
  } catch (error) {
    console.error(t('aiTool.tool.batchError'), error)
    message.error(t('aiTool.tool.networkError'))
  }
}

// 获取状态文本
const getStatusText = (status) => {
  const statusMap = {
    'published': t('aiTool.tool.statusPublished'),
    'draft': t('aiTool.tool.statusDraft'),
    'pending': t('aiTool.tool.statusPending'),
    1: t('aiTool.tool.statusPublished'),
    0: t('aiTool.tool.statusDraft')
  }
  return statusMap[status] || status
}

// 获取状态标签颜色
const getStatusTagColor = (status) => {
  const colorMap = {
    'published': 'green',
    'draft': 'blue',
    'pending': 'orange',
    1: 'green',
    0: 'blue'
  }
  return colorMap[status] || 'default'
}

// 获取价格类型文本
const getPricingTypeText = (type) => {
  const typeMap = {
    'free': t('aiTool.tool.pricingFree'),
    'freemium': t('aiTool.tool.pricingFreemium'),
    'paid': t('aiTool.tool.pricingPaid')
  }
  return typeMap[type] || type
}

// 获取价格类型标签颜色
const getPricingTagColor = (type) => {
  const colorMap = {
    'free': 'green',
    'freemium': 'blue',
    'paid': 'red'
  }
  return colorMap[type] || 'default'
}

// 导出数据
const exportData = () => {
  if (toolList.value.length === 0) {
    message.warning(t('aiTool.tool.noDataToExport'))
    return
  }

  message.info(t('aiTool.tool.exportNotImplemented'))

  // 如果已安装xlsx库，可以取消下面的注释实现导出功能

  // 准备导出数据
  const exportData = toolList.value.map(item => ({
    'ID': item.id,
    '工具名称': item.name,
    '分类': item.category ? item.category.name : '',
    '价格类型': getPricingTypeText(item.pricing_type),
    '状态': getStatusText(item.status),
    '推荐': item.is_recommended ? '是' : '否',
    '置顶': item.is_top ? '是' : '否',
    '浏览量': item.views,
    '评分': item.rating,
    '创建时间': formatDate(item.create_time * 1000)
  }))

  // 创建Excel工作表
  const worksheet = XLSX.utils.json_to_sheet(exportData)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'AI工具数据')

  // 生成文件名
  const fileName = `AI工具数据_${dayjs().format('YYYYMMDD_HHmmss')}.xlsx`

  // 导出文件
  XLSX.writeFile(workbook, fileName)
  message.success(`数据导出成功，文件名：${fileName}`)

}

// 页面加载时执行
onMounted(() => {
  fetchToolList()
  fetchCategories()
  fetchTags()
})
</script>

<style scoped>
.ai-tool-list-container {
  padding: 16px;
}

.list-card {
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* 筛选框样式 */
.ant-form-item {
  margin-bottom: 16px;
}

/* 操作按钮样式 */
.action-buttons {
  margin-top: 8px;
  margin-bottom: 16px;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .action-buttons {
    justify-content: flex-start !important;
  }
}

.card-title {
  font-size: 18px;
  font-weight: bold;
}

/* 统计卡片样式 */
.stat-row {
  margin-bottom: 24px;
}

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

.search-buttons {
  display: flex;
  align-items: flex-end;
}

/* 操作按钮样式 */
.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media screen and (max-width: 768px) {
  .search-buttons {
    margin-top: 16px;
  }

  .stat-card {
    margin-bottom: 16px;
  }
}

/* 工具列表样式 */
.tool-table {
  margin-bottom: 16px;
}

.tool-name-container {
  display: flex;
  flex-direction: column;
}

.tool-name {
  font-weight: bold;
  margin-bottom: 4px;
}

.tool-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.tag-list,
.category-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.tag-item,
.category-item {
  margin-bottom: 4px;
}

.no-tags,
.no-categories {
  color: #999;
  font-style: italic;
}

.featured-actions {
  display: flex;
  gap: 8px;
}

.feature-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 分页样式 */
.pagination-container {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
}

/* 详情抽屉样式 */
.tool-detail {
  padding: 0;
}

.detail-section {
  margin-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
  padding: 0 16px 16px;
}

.detail-section:last-child {
  border-bottom: none;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 16px;
  color: #333;
}

.detail-header {
  display: flex;
  margin-bottom: 16px;
}

.detail-logo {
  width: 80px;
  height: 80px;
  margin-right: 16px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.detail-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-banner-container {
  width: 100%;
  margin-bottom: 24px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.banner-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
  transition: transform 0.3s ease;
}

.detail-banner-container:hover .banner-image {
  transform: scale(1.02);
}

/* 自定义预览遮罩样式 */
.custom-mask {
  font-size: 14px;
  border-radius: 4px;
  background-color: rgba(0, 0, 0, 0.5);
  transition: all 0.3s;
}

.custom-mask:hover {
  background-color: rgba(0, 0, 0, 0.7);
}

.detail-basic {
  flex: 1;
}

.detail-name {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
}

.detail-meta {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.detail-slug {
  color: #666;
  font-size: 14px;
}

.detail-item {
  margin-bottom: 12px;
  display: flex;
  flex-wrap: wrap;
}

.detail-label {
  font-weight: 500;
  color: #666;
  width: 120px;
  flex-shrink: 0;
}

.detail-value {
  flex: 1;
}

.detail-link {
  color: #1890ff;
}

.detail-description {
  line-height: 1.6;
}

/* 功能特点样式 - 段落形式 */
.feature-paragraph {
  margin-top: 16px;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 12px;
  padding: 0 0 12px 0;
  border-bottom: 1px dashed #f0f0f0;
}

.feature-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.feature-icon {
  margin-right: 10px;
  color: #52c41a;
  font-size: 16px;
  line-height: 24px;
  flex-shrink: 0;
}

.feature-text {
  flex: 1;
  font-size: 14px;
  line-height: 1.6;
  color: #333;
}

.no-features {
  color: #999;
  font-style: italic;
  padding: 10px 0;
  text-align: center;
}

/* 兼容旧版样式 */
.detail-features {
  padding-left: 20px;
  margin: 0;
}

.detail-features li {
  margin-bottom: 8px;
}

.detail-screenshots {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.no-data {
  text-align: center;
  color: #999;
  padding: 40px 0;
}

/* 操作按钮样式 */
.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 操作按钮中的图标 */
.action-button .anticon {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 确保图标和按钮外观与亮色模式保持一致 */
.feature-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

.rating {
  display: flex;
  align-items: center;
}

.rating-count {
  margin-left: 5px;
  color: #909399;
  font-size: 12px;
}

/* 表格在暗黑模式下的样式 - 只调整表格颜色，不改变图标和按钮 */
:global(.dark-theme) .tool-table,
:global(.system-dark-theme) .tool-table {
  background-color: transparent;
}

:global(.dark-theme) .tool-table .ant-table-thead > tr > th,
:global(.system-dark-theme) .tool-table .ant-table-thead > tr > th {
  background-color: rgba(255, 255, 255, 0.04);
  color: rgba(255, 255, 255, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.09);
}

:global(.dark-theme) .tool-table .ant-table-tbody > tr > td,
:global(.system-dark-theme) .tool-table .ant-table-tbody > tr > td {
  background-color: transparent;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .tool-table .ant-table-tbody > tr:hover > td,
:global(.system-dark-theme) .tool-table .ant-table-tbody > tr:hover > td {
  background-color: rgba(255, 255, 255, 0.04);
}

/* 修复暗黑模式下分页控件的样式 */
:global(.dark-theme) .pagination-container .ant-pagination,
:global(.system-dark-theme) .pagination-container .ant-pagination {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .pagination-container .ant-pagination-item,
:global(.system-dark-theme) .pagination-container .ant-pagination-item {
  background-color: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
}

:global(.dark-theme) .pagination-container .ant-pagination-item a,
:global(.system-dark-theme) .pagination-container .ant-pagination-item a {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .pagination-container .ant-pagination-item-active,
:global(.system-dark-theme) .pagination-container .ant-pagination-item-active {
  background-color: #1890ff;
  border-color: #1890ff;
}

:global(.dark-theme) .pagination-container .ant-pagination-item-active a,
:global(.system-dark-theme) .pagination-container .ant-pagination-item-active a {
  color: white;
}
</style>
