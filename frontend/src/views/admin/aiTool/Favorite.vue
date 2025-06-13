<template>
  <div class="ai-tool-favorite-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.favorite.management')" :headStyle="{textAlign: 'left'}">
      <!-- 统计卡片 -->
      <a-row :gutter="24" class="mb-4">
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><HeartOutlined /> {{ $t('aiTool.favorite.totalFavorites') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total }}</div>
              <div class="stat-label">{{ $t('aiTool.favorite.favoriteRecords') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><UserOutlined /> {{ $t('aiTool.favorite.userFavorites') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ userFavoriteCount }}</div>
              <div class="stat-label">{{ $t('aiTool.favorite.normalUserFavorites') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><CrownOutlined /> {{ $t('aiTool.favorite.adminFavorites') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ adminFavoriteCount }}</div>
              <div class="stat-label">{{ $t('aiTool.favorite.adminFavoriteRecords') }}</div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <!-- 搜索表单 -->
      <a-form layout="inline" :model="queryParams" class="search-form mb-4">
        <a-row :gutter="24" style="width: 100%">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.favorite.toolName')" name="toolName" style="margin-bottom: 16px; width: 100%">
              <a-input v-model:value="queryParams.toolName" :placeholder="$t('aiTool.favorite.toolNamePlaceholder')" allow-clear style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.favorite.userType')" name="userType" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.userType" placeholder="请选择用户类型" style="width: 100%" allow-clear>
                <a-select-option value="user">普通用户</a-select-option>
                <a-select-option value="admin">管理员</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8" class="search-buttons" style="margin-bottom: 16px">
            <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
              <a-space>
                <a-button type="primary" @click="handleSearch">
                  <template #icon><SearchOutlined /></template>
                  查询
                </a-button>
                <a-button @click="handleReset">
                  <template #icon><ReloadOutlined /></template>
                  重置
                </a-button>
                <a-button type="link" @click="toggleAdvanced" style="padding: 0 8px">
                  <template #icon><DownOutlined v-if="!advanced" /><UpOutlined v-else /></template>
                  {{ advanced ? '收起' : '展开' }}
                </a-button>
              </a-space>
            </a-form-item>
          </a-col>
        </a-row>

        <!-- 高级搜索选项 -->
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="收藏时间" name="dateRange" style="margin-bottom: 16px; width: 100%">
              <a-range-picker v-model:value="dateRange" format="YYYY-MM-DD" style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="用户ID" name="userId" style="margin-bottom: 16px; width: 100%">
              <a-input-number v-model:value="queryParams.userId" placeholder="请输入用户ID" style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="排序字段" name="order_field" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.order_field" style="width: 100%">
                <a-select-option value="create_time">收藏时间</a-select-option>
                <a-select-option value="id">ID</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="排序方式" name="order_type" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.order_type" style="width: 100%">
                <a-select-option value="desc">降序</a-select-option>
                <a-select-option value="asc">升序</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <!-- 操作按钮 -->
      <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <a-space>
          <a-button type="primary" danger @click="handleBatchDelete" :disabled="selectedRowKeys.length === 0">
            <template #icon><DeleteOutlined /></template>
            批量删除
          </a-button>
          <a-button @click="handleRefresh">
            <template #icon><ReloadOutlined /></template>
            刷新
          </a-button>
          <a-button type="primary" @click="exportData">
            <template #icon><DownloadOutlined /></template>
            导出数据
          </a-button>
        </a-space>
      </div>

      <!-- 数据表格 -->
      <a-table
        :columns="columns"
        :data-source="favoriteList"
        :pagination="false"
        :loading="tableLoading"
        :row-selection="{ selectedRowKeys, onChange: onSelectChange }"
        @change="handleTableChange"
        row-key="id"
        :scroll="{ x: 800 }"
        size="middle"
        bordered
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'tool_name'">
            <div class="tool-info">
              <a-avatar :src="record.tool_logo || '/images/default-logo.png'" :size="40" shape="square" />
              <span class="ml-2">{{ record.tool_name }}</span>
            </div>
          </template>
          <template v-if="column.key === 'user_nickname'">
            <span>{{ record.user_nickname || '未知用户' }}</span>
            <a-tag v-if="record.user_type === 'admin'" color="blue">管理员</a-tag>
            <a-tag v-else color="green">普通用户</a-tag>
          </template>
          <template v-if="column.key === 'create_time'">
            {{ formatDateTime(record.create_time) }}
          </template>
          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip title="查看详情">
                <a-button type="primary" shape="circle" size="small" @click="handleViewDetail(record)">
                  <template #icon><EyeOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="查看工具">
                <a-button shape="circle" size="small" @click="viewTool(record)">
                  <template #icon><LinkOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="删除">
                <a-button type="primary" danger shape="circle" size="small" @click="handleDelete(record)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
        <!-- 自定义表格底部 -->
        <template #footer>
          <div class="table-footer">
            <span>已选择 <a>{{ selectedRowKeys.length }}</a> 项</span>
            <a-button type="link" @click="clearSelection" v-if="selectedRowKeys.length > 0">清空</a-button>
          </div>
        </template>
      </a-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <a-pagination
          v-model:current="queryParams.page"
          v-model:pageSize="queryParams.limit"
          :total="total"
          :showSizeChanger="true"
          :pageSizeOptions="['10', '20', '30', '50']"
          :showTotal="total => `共 ${total} 条记录`"
          @change="handlePageChange"
          @showSizeChange="handleSizeChange"
        />
      </div>

      <!-- 详情抽屉 -->
      <a-drawer
        title="收藏详情"
        :open="drawerVisible"
        :width="500"
        @close="drawerVisible = false"
      >
        <a-descriptions :column="1" bordered v-if="currentRecord">
          <a-descriptions-item label="ID">{{ currentRecord.id }}</a-descriptions-item>
          <a-descriptions-item label="工具名称">
            <div class="tool-info">
              <a-avatar :src="currentRecord.tool_logo || '/images/default-logo.png'" :size="40" shape="square" />
              <span class="ml-2">{{ currentRecord.tool_name }}</span>
            </div>
          </a-descriptions-item>
          <a-descriptions-item label="用户">
            {{ currentRecord.user_nickname || '未知用户' }}
            <a-tag v-if="currentRecord.user_type === 'admin'" color="blue">管理员</a-tag>
            <a-tag v-else color="green">普通用户</a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="用户ID">{{ currentRecord.user_id }}</a-descriptions-item>
          <a-descriptions-item label="收藏时间">
            {{ formatDateTime(currentRecord.create_time) }}
          </a-descriptions-item>
        </a-descriptions>

        <template #footer>
          <div style="text-align: right;">
            <a-space>
              <a-button @click="drawerVisible = false">关闭</a-button>
              <a-button type="primary" @click="viewTool(currentRecord)">查看工具</a-button>
              <a-button type="primary" danger @click="handleDelete(currentRecord)">删除收藏</a-button>
            </a-space>
          </div>
        </template>
      </a-drawer>
    </a-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { message, Modal, Card, Row, Col, Form, Input, Select, Button, Table, Space, Drawer, Descriptions, Divider, Avatar, Tag, Tooltip, DatePicker, InputNumber } from 'ant-design-vue';
import {
  SearchOutlined,
  ReloadOutlined,
  DeleteOutlined,
  EyeOutlined,
  DownloadOutlined,
  HeartOutlined,
  UserOutlined,
  CrownOutlined,
  LinkOutlined,
  DownOutlined,
  UpOutlined
} from '@ant-design/icons-vue';
import { getFavoriteList, getFavoriteInfo, deleteFavorite, batchDeleteFavorite } from '@/api/admin/aiToolFavorite';
import { formatDateTime } from '@/utils/timeUtils';
import { useRouter } from 'vue-router';
// import * as XLSX from 'xlsx'; // 需要先安装: npm install xlsx --save

const router = useRouter();

// 高级搜索开关
const advanced = ref(false);
const toggleAdvanced = () => {
  advanced.value = !advanced.value;
};

// 查询参数
const queryParams = reactive({
  page: 1,
  limit: 10,
  toolName: '',
  userType: undefined,
  userId: undefined,
  startTime: undefined,
  endTime: undefined,
  order_field: 'create_time',
  order_type: 'desc'
});

// 日期范围
const dateRange = ref([]);

// 监听日期范围变化
watch(dateRange, (newVal) => {
  if (newVal && newVal.length === 2) {
    // 将日期对象转换为字符串格式 YYYY-MM-DD
    queryParams.start_time = newVal[0] ? newVal[0].format('YYYY-MM-DD') : undefined;
    queryParams.end_time = newVal[1] ? newVal[1].format('YYYY-MM-DD') : undefined;
    // 删除旧的参数
    delete queryParams.startTime;
    delete queryParams.endTime;
  } else {
    queryParams.start_time = undefined;
    queryParams.end_time = undefined;
    // 删除旧的参数
    delete queryParams.startTime;
    delete queryParams.endTime;
  }
});

// 表格数据
const favoriteList = ref([]);
const loading = ref(false); // 保留原来的loading变量以兼容其他功能
const tableLoading = ref(false); // 新增表格加载状态变量
const total = ref(0);
const selectedRowKeys = ref([]);
const currentRecord = ref(null);
const drawerVisible = ref(false);

// 更新统计数据
const updateStatistics = () => {
  // 这里不需要额外的操作，因为统计数据是通过计算属性自动更新的
  // 如果需要额外的统计数据，可以在这里添加
};

// 统计数据
const userFavoriteCount = computed(() => {
  return favoriteList.value.filter(item => item.user_type === 'user').length;
});

const adminFavoriteCount = computed(() => {
  return favoriteList.value.filter(item => item.user_type === 'admin').length;
});

// 分页配置
// 使用单独的分页组件，不需要计算属性

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
    title: '工具名称',
    dataIndex: 'tool_name',
    key: 'tool_name',
    ellipsis: true,
    width: 200,
    align: 'left'
  },
  {
    title: '用户',
    dataIndex: 'user_nickname',
    key: 'user_nickname',
    width: 150,
    align: 'left'
  },
  {
    title: '用户ID',
    dataIndex: 'user_id',
    key: 'user_id',
    width: 100,
    sorter: true,
    align: 'left'
  },
  {
    title: '收藏时间',
    dataIndex: 'create_time',
    key: 'create_time',
    width: 180,
    sorter: true,
    align: 'left'
  },
  {
    title: '操作',
    key: 'action',
    width: 150,
    fixed: 'right',
    align: 'left'
  }
];

// 使用公共的date.js中的formatDateTime函数

// 加载收藏列表
const loadFavoriteList = async () => {
  tableLoading.value = true; // 只将表格设置为加载状态
  try {
    const params = { ...queryParams };
    if (params.toolName) {
      params.tool_name = params.toolName;
      delete params.toolName;
    }
    const res = await getFavoriteList(params);
    if (res.code === 200) {
      favoriteList.value = res.data.list || [];
      total.value = res.data.total || 0;

      // 清除选择
      selectedRowKeys.value = [];

      // 更新统计数据
      updateStatistics();
    } else {
      message.error(res.data.msg || '获取收藏列表失败');
    }
  } catch (error) {
    console.error('获取收藏列表出错:', error);
    message.error('获取收藏列表失败');
  } finally {
    tableLoading.value = false; // 只将表格设置为非加载状态
  }
};

// 查询
const handleSearch = () => {
  queryParams.page = 1;
  loadFavoriteList();
};

// 重置
const handleReset = () => {
  queryParams.page = 1;
  queryParams.toolName = '';
  queryParams.userType = undefined;
  queryParams.userId = undefined;
  queryParams.start_time = undefined;
  queryParams.end_time = undefined;
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';
  // 删除旧的参数
  delete queryParams.startTime;
  delete queryParams.endTime;
  dateRange.value = [];
  advanced.value = false;
  loadFavoriteList();
};

// 刷新
const handleRefresh = () => {
  loadFavoriteList();
};

// 处理页码变化
const handlePageChange = (page, pageSize) => {
  queryParams.page = page;
  queryParams.limit = pageSize;
  loadFavoriteList();
};

// 处理每页数量变化
const handleSizeChange = (current, size) => {
  queryParams.limit = size;
  queryParams.page = 1;
  loadFavoriteList();
};

// 表格变化
const handleTableChange = (pagination, filters, sorter) => {
  // 处理排序
  if (sorter && sorter.field) {
    queryParams.order_field = sorter.field;
    queryParams.order_type = sorter.order === 'ascend' ? 'asc' : 'desc';
    loadFavoriteList();
  }
};

// 选择行变化
const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

// 清空选择
const clearSelection = () => {
  selectedRowKeys.value = [];
};

// 查看详情
const handleViewDetail = async (record) => {
  try {
    const res = await getFavoriteInfo({ id: record.id });
    if (res.code === 200) {
      currentRecord.value = res.data;
      drawerVisible.value = true;
    } else {
      message.error(res.msg || '获取收藏详情失败');
    }
  } catch (error) {
    console.error('获取收藏详情出错:', error);
    message.error('获取收藏详情失败');
  }
};

// 查看工具
const viewTool = (record) => {
  if (!record || !record.tool_id) {
    message.warning('工具信息不完整');
    return;
  }

  // 跳转到工具编辑页面
  router.push(`/admin/aiTool-edit/${record.tool_id}`);
};

// 删除收藏
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除ID为 ${record.id} 的收藏记录吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await deleteFavorite({ id: record.id });
        if (res.data.code === 200) {
          message.success('删除成功');
          if (drawerVisible.value) {
            drawerVisible.value = false;
          }
          loadFavoriteList();
        } else {
          message.error(res.data.msg || '删除失败');
        }
      } catch (error) {
        console.error('删除收藏出错:', error);
        message.error('删除失败');
      }
    }
  });
};

// 批量删除
const handleBatchDelete = () => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('请至少选择一条记录');
    return;
  }

  Modal.confirm({
    title: '确认批量删除',
    content: `确定要删除选中的 ${selectedRowKeys.value.length} 条收藏记录吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchDeleteFavorite({ ids: selectedRowKeys.value });
        if (res.data.code === 200) {
          message.success('批量删除成功');
          selectedRowKeys.value = [];
          loadFavoriteList();
        } else {
          message.error(res.data.msg || '批量删除失败');
        }
      } catch (error) {
        console.error('批量删除收藏出错:', error);
        message.error('批量删除失败');
      }
    }
  });
};

// 导出数据
const exportData = () => {
  if (favoriteList.value.length === 0) {
    message.warning('没有数据可导出');
    return;
  }

  message.info('请先安装xlsx库: npm install xlsx --save');

  // 以下代码需要安装xlsx库后才能使用
  /*
  // 准备导出数据
  const exportData = favoriteList.value.map(item => ({
    'ID': item.id,
    '工具名称': item.tool_name,
    '用户名称': item.user_nickname || '未知用户',
    '用户类型': item.user_type === 'admin' ? '管理员' : '普通用户',
    '用户ID': item.user_id,
    '收藏时间': formatDateTime(item.create_time)
  }));

  // 创建Excel工作表
  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'AI工具收藏数据');

  // 生成文件名
  const fileName = `AI工具收藏数据_${dayjs().format('YYYYMMDD_HHmmss')}.xlsx`;

  // 导出文件
  XLSX.writeFile(workbook, fileName);
  message.success(`数据导出成功，文件名：${fileName}`);
  */
};

// 页面加载时获取数据
onMounted(() => {
  // 设置默认排序
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';

  // 初始化页面
  initPage();
});

// 初始化页面
const initPage = async () => {
  // 加载收藏列表
  loadFavoriteList();
};
</script>

<style scoped>
.ai-tool-favorite-container {
  padding: 16px;
}

.mb-4 {
  margin-bottom: 16px;
}

.ml-2 {
  margin-left: 8px;
}

.tool-info {
  display: flex;
  align-items: center;
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

.search-buttons {
  display: flex;
  align-items: flex-end;
}

/* 表格底部样式 */
.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
}

.table-footer a {
  color: #1890ff;
  font-weight: bold;
}

/* 操作按钮样式 */
.action-buttons {
  margin-top: 8px;
  margin-bottom: 16px;
}

@media (max-width: 768px) {
  .search-buttons {
    margin-top: 16px;
  }

  .stat-card {
    margin-bottom: 16px;
  }

  .action-buttons {
    justify-content: flex-start !important;
  }
}

/* 分页容器样式 */
.pagination-container {
  margin-top: 16px;
  text-align: right;
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
