<template>
  <div class="ai-tool-like-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.like.management')" :headStyle="{textAlign: 'left'}">
      <!-- 统计卡片 -->
      <a-row :gutter="24" class="mb-4">
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><LikeOutlined /> {{ $t('aiTool.like.totalLikes') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total }}</div>
              <div class="stat-label">{{ $t('aiTool.like.likeRecords') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><UserOutlined /> {{ $t('aiTool.like.userLikes') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ registeredUserLikeCount }}</div>
              <div class="stat-label">{{ $t('aiTool.like.registeredUserLikes') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="8">
          <a-card class="stat-card">
            <template #title>
              <span><TeamOutlined /> {{ $t('aiTool.like.guestLikes') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ guestLikeCount }}</div>
              <div class="stat-label">{{ $t('aiTool.like.guestLikeRecords') }}</div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <!-- 搜索表单 -->
      <a-form layout="inline" :model="queryParams" class="search-form mb-4">
        <a-row :gutter="24" style="width: 100%">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.like.toolName')" name="toolName" style="margin-bottom: 16px; width: 100%">
              <a-input v-model:value="queryParams.toolName" :placeholder="$t('aiTool.like.toolNamePlaceholder')" allow-clear style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.like.userType')" name="userType" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.userType" :placeholder="$t('aiTool.like.userTypePlaceholder')" style="width: 100%" allow-clear>
                <a-select-option value="user">{{ $t('aiTool.like.normalUser') }}</a-select-option>
                <a-select-option value="admin">{{ $t('aiTool.like.admin') }}</a-select-option>
                <a-select-option value="guest">访客</a-select-option>
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
            <a-form-item label="点赞时间" name="dateRange" style="margin-bottom: 16px; width: 100%">
              <a-range-picker v-model:value="dateRange" format="YYYY-MM-DD" style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="用户ID" name="userId" style="margin-bottom: 16px; width: 100%">
              <a-input-number v-model:value="queryParams.userId" placeholder="请输入用户ID" style="width: 100%" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="排序字段" name="order_field" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.order_field" style="width: 100%" @change="handleOrderFieldChange">
                <a-select-option value="id">ID</a-select-option>
                <a-select-option value="user_id">用户ID</a-select-option>
                <a-select-option value="create_time">点赞时间</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="排序方式" name="order_type" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.order_type" style="width: 100%" @change="handleOrderTypeChange">
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
          <a-button type="primary" @click="showIPAnalysis">
            <template #icon><BarChartOutlined /></template>
            IP分析
          </a-button>
          <a-button type="primary" @click="refreshIPLocations">
            <template #icon><ReloadOutlined /></template>
            刷新IP位置
          </a-button>
        </a-space>
      </div>

      <!-- 数据表格 -->
      <a-table
        :columns="columns"
        :data-source="likeList"
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
            <a-tag v-else-if="record.user_id > 0" color="green">普通用户</a-tag>
            <a-tag v-else color="orange">游客</a-tag>
          </template>
          <template v-if="column.key === 'ip'">
            <a-tooltip :title="getIPLocationFromCache(record.ip)">
              <a @click="searchByIP(record.ip)">{{ record.ip || '-' }}</a>
            </a-tooltip>
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
        title="点赞详情"
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
            {{ currentRecord.user_nickname || '匿名用户' }}
            <a-tag v-if="currentRecord.user_type === 'admin'" color="blue">管理员</a-tag>
            <a-tag v-else-if="currentRecord.user_id > 0" color="green">普通用户</a-tag>
            <a-tag v-else color="orange">游客</a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="用户ID">{{ currentRecord.user_id || '无' }}</a-descriptions-item>
          <a-descriptions-item label="IP地址">
            {{ currentRecord.ip || '-' }}
            <a-tag v-if="currentRecord.ip" color="cyan">
              {{ getIPLocation(currentRecord.ip) }}
            </a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="点赞时间">
            {{ formatDateTime(currentRecord.create_time) }}
          </a-descriptions-item>
          <a-descriptions-item label="用户代理">
            {{ currentRecord.user_agent || '-' }}
          </a-descriptions-item>
        </a-descriptions>

        <template #footer>
          <div style="text-align: right;">
            <a-space>
              <a-button @click="drawerVisible = false">关闭</a-button>
              <a-button type="primary" @click="viewTool(currentRecord)">查看工具</a-button>
              <a-button type="primary" danger @click="handleDelete(currentRecord)">删除点赞</a-button>
            </a-space>
          </div>
        </template>
      </a-drawer>

      <!-- IP分析对话框 -->
      <a-modal
        :open="ipAnalysisVisible"
        title="IP分析统计"
        :width="800"
        @cancel="ipAnalysisVisible = false"
        :footer="null"
      >
        <div ref="ipChartRef" style="width: 100%; height: 400px;"></div>
        <a-table
          :columns="ipColumns"
          :data-source="ipAnalysisData"
          :pagination="false"
          size="small"
          bordered
          class="mt-4"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'percentage'">
              <a-progress :percent="record.percentage" size="small" :stroke-color="getRandomColor(record.ip)" />
            </template>
            <template v-if="column.key === 'action'">
              <a @click="searchByIP(record.ip)">查询</a>
            </template>
          </template>
        </a-table>
      </a-modal>
    </a-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, nextTick } from 'vue';
import { message, Modal, Card, Row, Col, Form, Input, Select, Button, Table, Space, Drawer, Descriptions, Divider, Avatar, Tag, Tooltip, DatePicker, InputNumber, Progress } from 'ant-design-vue';
import {
  SearchOutlined,
  ReloadOutlined,
  DeleteOutlined,
  EyeOutlined,
  DownloadOutlined,
  LikeOutlined,
  UserOutlined,
  TeamOutlined,
  LinkOutlined,
  DownOutlined,
  UpOutlined,
  BarChartOutlined
} from '@ant-design/icons-vue';
import { getLikeList, getLikeInfo, deleteLike, batchDeleteLike } from '@/api/admin/aiToolLike';
import { adminQueryIpLocation, formatLocation } from '@/api/admin/ipLocation';
import { formatDateTime } from '@/utils/timeUtils';
import { useRouter } from 'vue-router';
// import * as XLSX from 'xlsx'; // 需要先安装: npm install xlsx --save
import * as echarts from 'echarts/core';
import { PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import defaultAvatar from '@/assets/images/default-avatar.png';

// 注册必要的ECharts组件
echarts.use([
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  PieChart,
  CanvasRenderer
]);

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
  ip: '',
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
const likeList = ref([]);
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

// IP分析相关
const ipAnalysisVisible = ref(false);
const ipChartRef = ref(null);
const ipAnalysisData = ref([]);
let ipChart = null;

// 统计数据
const registeredUserLikeCount = computed(() => {
  // 添加防护措施，确保likeList.value存在
  return Array.isArray(likeList.value) ? likeList.value.filter(item => item.user_id > 0).length : 0;
});

const guestLikeCount = computed(() => {
  // 添加防护措施，确保likeList.value存在
  return Array.isArray(likeList.value) ? likeList.value.filter(item => !item.user_id || item.user_id === 0).length : 0;
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
    sortDirections: ['ascend', 'descend'],
    defaultSortOrder: queryParams.order_field === 'id' ? (queryParams.order_type === 'asc' ? 'ascend' : 'descend') : null,
    align: 'left'
  },
  {
    title: '工具名称',
    dataIndex: 'tool_name',
    key: 'tool_name',  // 修改key与dataIndex保持一致
    ellipsis: true,
    width: 200
  },
  {
    title: '用户',
    dataIndex: 'user_nickname',
    key: 'user_nickname',  // 修改key与dataIndex保持一致
    width: 150
  },
  {
    title: '用户ID',
    dataIndex: 'user_id',
    key: 'user_id',
    width: 100,
    sorter: true,
    sortDirections: ['ascend', 'descend'],
    defaultSortOrder: queryParams.order_field === 'user_id' ? (queryParams.order_type === 'asc' ? 'ascend' : 'descend') : null
  },
  {
    title: 'IP地址',
    dataIndex: 'ip',
    key: 'ip',
    width: 150
  },
  {
    title: '点赞时间',
    dataIndex: 'create_time',
    key: 'create_time',
    width: 180,
    sorter: true,
    sortDirections: ['ascend', 'descend'],
    defaultSortOrder: queryParams.order_field === 'create_time' ? (queryParams.order_type === 'asc' ? 'ascend' : 'descend') : null
  },
  {
    title: '操作',
    key: 'action',
    width: 150,
    fixed: 'right'
  }
];

// IP分析表格列定义
const ipColumns = [
  {
    title: 'IP地址',
    dataIndex: 'ip',
    key: 'ip'
  },
  {
    title: '点赞数量',
    dataIndex: 'count',
    key: 'count',
    sorter: (a, b) => a.count - b.count
  },
  {
    title: '占比',
    key: 'percentage',
    dataIndex: 'percentage'
  },
  {
    title: '位置',
    dataIndex: 'location',
    key: 'location'
  },
  {
    title: '操作',
    key: 'action',
    width: 80
  }
];

// 使用公共的date.js中的formatDateTime函数

// IP地理位置缓存
const ipLocationCache = ref({});
// 正在查询的IP集合，用于防止重复请求
const queryingIPs = ref(new Set());

// 确保缓存对象已初始化
if (!ipLocationCache.value) {
  ipLocationCache.value = {};
}

if (!queryingIPs.value) {
  queryingIPs.value = new Set();
}

// 批量查询IP地理位置
const batchQueryIPLocations = (ips) => {
  // 过滤出需要查询的IP（缓存中没有且不在查询中的IP）
  const ipsToQuery = ips.filter(ip => ip && !(ipLocationCache.value && ipLocationCache.value[ip]) && !(queryingIPs.value && queryingIPs.value.has(ip)));

  // 如果没有需要查询的IP，直接返回
  if (ipsToQuery.length === 0) return;

  // 将这些IP添加到正在查询的集合中
  ipsToQuery.forEach(ip => queryingIPs.value.add(ip));

  // 批量查询IP地理位置
  const promises = ipsToQuery.map(ip => {
    return adminQueryIpLocation(ip)
      .then(response => {
        // 调试日志，查看响应数据结构
        console.log(`IP ${ip} 查询结果:`, response);
        if (response.code === 200 && response.data) {
          // 直接使用返回的数据格式化地理位置信息
          const data = response.data;
          let locationText = '未知位置';

          if (data.status === 'success') {
            locationText = '';
            if (data.country) locationText += data.country;
            if (data.regionName && data.regionName !== data.country) locationText += ` ${data.regionName}`;
            if (data.city) locationText += ` ${data.city}`;
          }

          // 更新缓存
          ipLocationCache.value[ip] = locationText || '未知位置';
        } else {
          ipLocationCache.value[ip] = '查询失败';
        }
      })
      .catch(error => {
        console.error(`获取IP ${ip} 地理位置失败:`, error);
        ipLocationCache.value[ip] = '查询失败';
      })
      .finally(() => {
        // 从正在查询的集合中移除
        queryingIPs.value.delete(ip);
      });
  });

  // 使用Promise.allSettled确保所有请求都完成，无论成功或失败
  return Promise.allSettled(promises);
};

// 从缓存中安全地获取IP地理位置信息
const getIPLocationFromCache = (ip) => {
  if (!ip) return '未知位置';

  // 安全地访问缓存
  if (ipLocationCache && ipLocationCache.value && ipLocationCache.value[ip]) {
    return ipLocationCache.value[ip];
  }

  return '查询中...';
};

// 获取IP地理位置
const getIPLocation = (ip) => {
  if (!ip) return '未知位置';

  // 如果缓存中有该IP的位置信息，直接返回
  if (ipLocationCache.value && ipLocationCache.value[ip]) {
    return ipLocationCache.value[ip];
  }

  // 返回默认值
  return '查询中...';
};

// 生成随机颜色
const getRandomColor = (seed) => {
  if (!seed) return '#1890ff';

  // 根据IP生成固定颜色
  let hash = 0;
  for (let i = 0; i < seed.length; i++) {
    hash = seed.charCodeAt(i) + ((hash << 5) - hash);
  }

  const colors = [
    '#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1',
    '#13c2c2', '#fa8c16', '#eb2f96', '#a0d911', '#fadb14'
  ];

  const index = Math.abs(hash) % colors.length;
  return colors[index];
};

// 加载点赞列表
const loadLikeList = async () => {
  tableLoading.value = true; // 只将表格设置为加载状态
  try {
    const params = { ...queryParams };

    // 处理工具名称
    if (params.toolName) {
      params.tool_name = params.toolName;
      delete params.toolName;
    }

    // 如果没有指定排序字段，使用默认排序
    if (!params.order_field) {
      params.order_field = 'create_time';
    }

    // 如果没有指定排序方式，使用默认排序
    if (!params.order_type) {
      params.order_type = 'desc';
    }

    // 处理游客类型筛选
    if (params.guestType) {
      if (params.guestType === 'guest') {
        params.user_id = 0;
      } else if (params.guestType === 'registered') {
        params.user_id_gt = 0; // 假设后端支持大于查询
      }
      delete params.guestType;
    }

    const res = await getLikeList(params);

    if (res.code === 200) {
      // 检查响应结构，确保正确获取数据
      if (res.data && res.data.list) {
        // 如果数据在res.data中
        likeList.value = res.data.list || [];
        total.value = res.data.total || 0;
      } else if (res.list) {
        // 如果数据直接在res中
        likeList.value = res.list || [];
        total.value = res.total || 0;
      } else {
        // 如果找不到数据，初始化为空数组
        likeList.value = [];
        total.value = 0;
      }

      // 清除选择
      selectedRowKeys.value = [];

      // 更新统计数据
      updateStatistics();

      // 数据加载完成后，批量查询IP地理位置
      if (likeList.value && likeList.value.length > 0) {
        // 提取所有不同的IP地址
        const uniqueIPs = [...new Set(likeList.value.map(item => item.ip).filter(Boolean))];
        // 批量查询
        batchQueryIPLocations(uniqueIPs);
      }
    } else {
      likeList.value = [];
      total.value = 0;
      message.error(res.msg || res.data?.msg || '获取点赞列表失败');
    }
  } catch (error) {
    console.error('获取点赞列表出错:', error);
    message.error('获取点赞列表失败');
  } finally {
    tableLoading.value = false; // 只将表格设置为非加载状态
  }
};

// 查询
const handleSearch = () => {
  console.log('执行查询操作');
  queryParams.page = 1;
  loadLikeList();
};

// 重置
const handleReset = () => {
  console.log('执行重置操作');
  queryParams.page = 1;
  queryParams.toolName = '';
  queryParams.userType = undefined;
  queryParams.userId = undefined;
  queryParams.ip = '';
  queryParams.guestType = undefined;
  queryParams.start_time = undefined;
  queryParams.end_time = undefined;
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';
  // 删除旧的参数
  delete queryParams.startTime;
  delete queryParams.endTime;
  delete queryParams.orderField;
  delete queryParams.orderType;
  dateRange.value = [];
  advanced.value = false;
  loadLikeList();
};

// 刷新
const handleRefresh = () => {
  console.log('执行刷新操作');
  loadLikeList();
};

// 处理排序字段变化
const handleOrderFieldChange = (value) => {
  console.log(`排序字段变化: ${value}`);
  queryParams.order_field = value;
  // 删除旧的排序参数
  delete queryParams.orderField;
  loadLikeList();
};

// 处理排序方式变化
const handleOrderTypeChange = (value) => {
  console.log(`排序方式变化: ${value}`);
  queryParams.order_type = value;
  // 删除旧的排序参数
  delete queryParams.orderType;
  loadLikeList();
};

// 处理页码变化
const handlePageChange = (page, pageSize) => {
  queryParams.page = page;
  queryParams.limit = pageSize;
  loadLikeList();
};

// 处理每页数量变化
const handleSizeChange = (current, size) => {
  queryParams.limit = size;
  queryParams.page = 1;
  loadLikeList();
};

// 表格变化
const handleTableChange = (pagination, filters, sorter) => {
  // 处理排序
  if (sorter && sorter.field) {
    queryParams.order_field = sorter.field;
    queryParams.order_type = sorter.order === 'ascend' ? 'asc' : 'desc';

    // 删除旧的排序参数
    delete queryParams.orderField;
    delete queryParams.orderType;

    // 重新加载数据
    loadLikeList();
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

// 根据IP搜索
const searchByIP = (ip) => {
  if (!ip) return;
  queryParams.ip = ip;
  queryParams.page = 1;
  loadLikeList();
  message.success(`正在搜索IP: ${ip} 的点赞记录`);
};

// 查看详情
const handleViewDetail = async (record) => {
  try {
    const res = await getLikeInfo({ id: record.id });
    if (res.code === 200) {
      currentRecord.value = res.data;
      drawerVisible.value = true;
    } else {
      message.error(res.msg || '获取点赞详情失败');
    }
  } catch (error) {
    console.error('获取点赞详情出错:', error);
    message.error('获取点赞详情失败');
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

// 删除点赞
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除ID为 ${record.id} 的点赞记录吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await deleteLike({ id: record.id });
        if (res.code === 200) {
          message.success('删除成功');
          if (drawerVisible.value) {
            drawerVisible.value = false;
          }
          loadLikeList();
        } else {
          message.error(res.data.msg || '删除失败');
        }
      } catch (error) {
        console.error('删除点赞出错:', error);
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
    content: `确定要删除选中的 ${selectedRowKeys.value.length} 条点赞记录吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchDeleteLike({ ids: selectedRowKeys.value });
        if (res.code === 200) {
          message.success('批量删除成功');
          selectedRowKeys.value = [];
          loadLikeList();
        } else {
          message.error(res.msg || '批量删除失败');
        }
      } catch (error) {
        console.error('批量删除点赞出错:', error);
        message.error('批量删除失败');
      }
    }
  });
};

// 导出数据
const exportData = () => {
  if (likeList.value.length === 0) {
    message.warning('没有数据可导出');
    return;
  }

  message.info('请先安装xlsx库: npm install xlsx --save');

  // 以下代码需要安装xlsx库后才能使用

  // 准备导出数据
  const exportData = likeList.value.map(item => ({
    'ID': item.id,
    '工具名称': item.tool_name,
    '用户名称': item.user_nickname || '未知用户',
    '用户类型': item.user_type === 'admin' ? '管理员' : (item.user_id > 0 ? '普通用户' : '访客'),
    '用户ID': item.user_id || '无',
    'IP地址': item.ip || '无',
    'IP位置': getIPLocationFromCache(item.ip),
    '点赞时间': formatDateTime(item.create_time)
  }));

  // 创建Excel工作表
  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'AI工具点赞数据');

  // 生成文件名
  const fileName = `AI工具点赞数据_${dayjs().format('YYYYMMDD_HHmmss')}.xlsx`;

  // 导出文件
  XLSX.writeFile(workbook, fileName);
  message.success(`数据导出成功，文件名：${fileName}`);

};

// 显示IP分析
const showIPAnalysis = async () => {
  if (likeList.value.length === 0) {
    message.warning('没有数据可分析');
    return;
  }

  // 先确保所有IP的地理位置信息都已加载
  const uniqueIPs = [...new Set(likeList.value.map(item => item.ip).filter(Boolean))];
  const ipsToQuery = uniqueIPs.filter(ip => !(ipLocationCache.value && ipLocationCache.value[ip]) && !(queryingIPs.value && queryingIPs.value.has(ip)));

  // 如果有需要查询的IP，先查询再显示分析
  if (ipsToQuery.length > 0) {
    message.loading('正在加载IP地理位置信息...', 1);
    try {
      await batchQueryIPLocations(ipsToQuery);
    } catch (error) {
      console.error('加载IP地理位置信息失败:', error);
    }
  }

  // 统计IP地址分布
  const ipStats = {};
  likeList.value.forEach(item => {
    if (item.ip) {
      if (!ipStats[item.ip]) {
        ipStats[item.ip] = {
          ip: item.ip,
          count: 0,
          location: getIPLocationFromCache(item.ip)
        };
      }
      ipStats[item.ip].count++;
    }
  });

  // 转换为数组并排序
  const ipStatsArray = Object.values(ipStats).sort((a, b) => b.count - a.count);

  // 计算百分比
  const totalCount = ipStatsArray.reduce((sum, item) => sum + item.count, 0);
  ipStatsArray.forEach(item => {
    item.percentage = parseFloat(((item.count / totalCount) * 100).toFixed(2));
  });

  ipAnalysisData.value = ipStatsArray;
  ipAnalysisVisible.value = true;

  // 渲染图表
  nextTick(() => {
    renderIPChart(ipStatsArray);
  });
};

// 渲染IP分析图表
const renderIPChart = (data) => {
  if (!ipChartRef.value) return;

  if (!ipChart) {
    ipChart = echarts.init(ipChartRef.value);
  }

  // 准备图表数据
  const chartData = data.map(item => ({
    name: `${item.ip} (${item.location})`,
    value: item.count
  }));

  const option = {
    title: {
      text: 'IP地址分布',
      left: 'center'
    },
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left',
      data: chartData.map(item => item.name)
    },
    series: [
      {
        name: '点赞数量',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2
        },
        label: {
          show: false,
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: '18',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: false
        },
        data: chartData
      }
    ]
  };

  ipChart.setOption(option);
};

// 刷新IP地理位置信息
const refreshIPLocations = () => {
  if (!likeList.value || likeList.value.length === 0) {
    message.warning('没有数据可刷新');
    return;
  }

  // 清空缓存
  ipLocationCache.value = {};

  // 提取所有不同的IP地址
  const uniqueIPs = [...new Set(likeList.value.map(item => item.ip).filter(Boolean))];

  if (uniqueIPs.length === 0) {
    message.warning('没有可查询的IP地址');
    return;
  }

  message.loading(`正在刷新 ${uniqueIPs.length} 个IP地理位置信息...`, 2);

  // 批量查询
  batchQueryIPLocations(uniqueIPs)
    .then(() => {
      message.success('刷新IP地理位置信息成功');
    })
    .catch(error => {
      console.error('刷新IP地理位置信息失败:', error);
      message.error('刷新IP地理位置信息失败');
    });
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
  // 加载点赞列表
  loadLikeList();
};
</script>

<style scoped>
.ai-tool-like-container {
  padding: 16px;
}

.mb-4 {
  margin-bottom: 16px;
}

.mt-4 {
  margin-top: 16px;
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
