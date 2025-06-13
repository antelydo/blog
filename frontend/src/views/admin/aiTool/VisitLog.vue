<template>
  <div class="ai-tool-visit-log-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.visitLog.management')" :headStyle="{textAlign: 'left'}">
      <a-tabs v-model:activeKey="activeTabKey">
        <a-tab-pane key="list" :tab="$t('aiTool.visitLog.logList')">
          <a-row :gutter="24" class="mb-4">
            <a-col :span="24">
              <a-form layout="inline" :model="queryParams" class="search-form">
                <a-row :gutter="24" style="width: 100%">
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item :label="$t('aiTool.visitLog.toolName')" style="margin-bottom: 16px; width: 100%">
                      <a-input v-model:value="queryParams.toolName" :placeholder="$t('aiTool.visitLog.toolNamePlaceholder')" allow-clear style="width: 100%" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item :label="$t('aiTool.visitLog.userType')" style="margin-bottom: 16px; width: 100%">
                      <a-select v-model:value="queryParams.userType" :placeholder="$t('aiTool.visitLog.userTypePlaceholder')" style="width: 100%" allow-clear>
                        <a-select-option value="user">{{ $t('aiTool.visitLog.normalUser') }}</a-select-option>
                        <a-select-option value="admin">{{ $t('aiTool.visitLog.admin') }}</a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="8" :md="8" :lg="8" class="search-buttons" style="margin-bottom: 16px">
                    <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
                      <a-space>
                        <a-button type="primary" @click="handleSearch">
                          <template #icon><SearchOutlined /></template>
                          {{ $t('aiTool.visitLog.search') }}
                        </a-button>
                        <a-button @click="handleReset">
                          <template #icon><ReloadOutlined /></template>
                          {{ $t('aiTool.visitLog.reset') }}
                        </a-button>
                        <a-button type="link" @click="toggleAdvanced" style="padding: 0 8px">
                          <template #icon><DownOutlined v-if="!advanced" /><UpOutlined v-else /></template>
                          {{ advanced ? $t('aiTool.visitLog.collapse') : $t('aiTool.visitLog.expand') }}
                        </a-button>
                      </a-space>
                    </a-form-item>
                  </a-col>
                </a-row>

                <!-- 高级搜索选项 -->
                <a-row :gutter="24" style="width: 100%" v-if="advanced">
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item label="IP地址" style="margin-bottom: 16px; width: 100%">
                      <a-input v-model:value="queryParams.ip" placeholder="请输入IP地址" allow-clear style="width: 100%" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item label="来源页面" style="margin-bottom: 16px; width: 100%">
                      <a-input v-model:value="queryParams.referer" placeholder="请输入来源页面" allow-clear style="width: 100%" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item label="访问时间" style="margin-bottom: 16px; width: 100%">
                      <a-range-picker v-model:value="dateRange" format="YYYY-MM-DD" style="width: 100%" />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-form>
            </a-col>
          </a-row>

          <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
            <a-space>
              <a-button type="primary" danger @click="handleBatchDelete" :disabled="selectedRowKeys.length === 0">
                <template #icon><DeleteOutlined /></template>
                批量删除
              </a-button>
              <a-button danger @click="handleClear">
                <template #icon><ClearOutlined /></template>
                清空日志
              </a-button>
              <a-button @click="handleRefresh">
                <template #icon><ReloadOutlined /></template>
                刷新
              </a-button>
            </a-space>
          </div>

          <a-table
            :columns="columns"
            :data-source="visitLogList"
            :pagination="pagination"
            :loading="loading"
            :row-selection="{ selectedRowKeys, onChange: onSelectChange }"
            @change="handleTableChange"
            row-key="id"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'tool_name'">
                <div class="tool-info">
                  <a-avatar :src="record.tool_logo || '/images/default-logo.png'" :size="40" shape="square" />
                  <span class="ml-2">{{ record.tool_name }}</span>
                </div>
              </template>
              <template v-if="column.key === 'user_nickname'">
                <span v-if="record.user_id > 0">{{ record.user_nickname || '未知用户' }}</span>
                <span v-else>游客</span>
                <a-tag v-if="record.user_type === 'admin'" color="blue">管理员</a-tag>
                <a-tag v-else-if="record.user_id > 0" color="green">普通用户</a-tag>
              </template>
              <template v-if="column.key === 'referer'">
                <a-tooltip :title="record.referer">
                  {{ formatReferer(record.referer) }}
                </a-tooltip>
              </template>
              <template v-if="column.key === 'create_time'">
                {{ formatDateTime(record.create_time) }}
              </template>
              <template v-if="column.key === 'action'">
                <a-space>
                  <a-tooltip title="查看详情">
                    <a-button shape="circle" size="small" @click="handleViewDetail(record)">
                      <template #icon><EyeOutlined /></template>
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
          </a-table>

          <!-- 详情抽屉 -->
          <a-drawer
            title="访问日志详情"
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
                <span v-if="currentRecord.user_id > 0">{{ currentRecord.user_nickname || '未知用户' }}</span>
                <span v-else>游客</span>
                <a-tag v-if="currentRecord.user_type === 'admin'" color="blue">管理员</a-tag>
                <a-tag v-else-if="currentRecord.user_id > 0" color="green">普通用户</a-tag>
              </a-descriptions-item>
              <a-descriptions-item label="IP地址">
                {{ currentRecord.ip || '-' }}
              </a-descriptions-item>
              <a-descriptions-item label="用户代理">
                {{ currentRecord.user_agent || '-' }}
              </a-descriptions-item>
              <a-descriptions-item label="来源页面">
                {{ currentRecord.referer || '-' }}
              </a-descriptions-item>
              <a-descriptions-item label="访问时间">
                {{ formatDateTime(currentRecord.create_time) }}
              </a-descriptions-item>
            </a-descriptions>
          </a-drawer>

          <!-- 清空日志对话框 -->
          <a-modal
            v-model:open="clearModalVisible"
            title="清空访问日志"
            @ok="confirmClear"
            :confirmLoading="clearLoading"
          >
            <a-form :model="clearForm" layout="vertical">
              <a-form-item label="工具选择" name="toolId">
                <a-select
                  v-model:value="clearForm.toolId"
                  placeholder="请选择工具（不选则清空所有工具的日志）"
                  style="width: 100%"
                  allow-clear
                >
                  <a-select-option :value="0">所有工具</a-select-option>
                  <a-select-option v-for="tool in toolOptions" :key="tool.id" :value="tool.id">
                    {{ tool.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="清空时间范围" name="days">
                <a-select
                  v-model:value="clearForm.days"
                  placeholder="请选择要清空的时间范围"
                  style="width: 100%"
                >
                  <a-select-option :value="0">所有日志</a-select-option>
                  <a-select-option :value="7">7天前的日志</a-select-option>
                  <a-select-option :value="30">30天前的日志</a-select-option>
                  <a-select-option :value="90">90天前的日志</a-select-option>
                  <a-select-option :value="180">180天前的日志</a-select-option>
                  <a-select-option :value="365">365天前的日志</a-select-option>
                </a-select>
              </a-form-item>
              <a-alert
                type="warning"
                message="警告：此操作不可恢复，请谨慎操作！"
                show-icon
              />
            </a-form>
          </a-modal>
        </a-tab-pane>

        <a-tab-pane key="stats" :tab="$t('aiTool.visitLog.statistics')">
          <a-row :gutter="24" class="mb-4">
            <a-col :span="24">
              <a-form layout="inline" class="search-form">
                <a-row :gutter="24" style="width: 100%">
                  <a-col :xs="24" :sm="16" :md="16" :lg="16">
                    <a-form-item label="工具选择" style="margin-bottom: 16px; width: 100%">
                      <a-select
                        v-model:value="statsParams.toolId"
                        placeholder="请选择工具（不选则统计所有工具）"
                        style="width: 100%"
                        allow-clear
                        @change="loadVisitStats"
                      >
                        <a-select-option :value="0">所有工具</a-select-option>
                        <a-select-option v-for="tool in toolOptions" :key="tool.id" :value="tool.id">
                          {{ tool.name }}
                        </a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="8" :md="8" :lg="8" class="search-buttons" style="margin-bottom: 16px">
                    <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
                      <a-space>
                        <a-button type="primary" @click="loadVisitStats">
                          <template #icon><ReloadOutlined /></template>
                          刷新统计
                        </a-button>
                        <a-button type="link" @click="toggleStatsAdvanced" style="padding: 0 8px">
                          <template #icon><DownOutlined v-if="!statsAdvanced" /><UpOutlined v-else /></template>
                          {{ statsAdvanced ? '收起' : '展开' }}
                        </a-button>
                      </a-space>
                    </a-form-item>
                  </a-col>
                </a-row>

                <!-- 高级统计选项 -->
                <a-row :gutter="24" style="width: 100%" v-if="statsAdvanced">
                  <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item label="时间范围" style="margin-bottom: 16px; width: 100%">
                      <a-select
                        v-model:value="statsParams.days"
                        placeholder="请选择时间范围"
                        style="width: 100%"
                        @change="loadVisitStats"
                      >
                        <a-select-option :value="7">最近7天</a-select-option>
                        <a-select-option :value="30">最近30天</a-select-option>
                        <a-select-option :value="90">最近90天</a-select-option>
                        <a-select-option :value="180">最近180天</a-select-option>
                        <a-select-option :value="365">最近365天</a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>
                  <!-- 可以在这里添加更多的高级统计选项 -->
                </a-row>
              </a-form>
            </a-col>
          </a-row>

          <a-spin :spinning="statsLoading">
            <a-row :gutter="16" class="stat-row mb-4">
              <a-col :span="8">
                <a-card class="stat-card">
                  <template #title>
                    <span><EyeOutlined /> {{ $t('aiTool.visitLog.totalVisits') }}</span>
                  </template>
                  <div class="stat-card-content">
                    <div class="stat-number">{{ statsData.totalVisits || 0 }}</div>
                    <div class="stat-label">{{ $t('aiTool.visitLog.visits') }}</div>
                  </div>
                </a-card>
              </a-col>
              <a-col :span="8">
                <a-card class="stat-card">
                  <template #title>
                    <span><UserOutlined /> {{ $t('aiTool.visitLog.uniqueVisitors') }}</span>
                  </template>
                  <div class="stat-card-content">
                    <div class="stat-number">{{ statsData.uniqueVisitors || 0 }}</div>
                    <div class="stat-label">{{ $t('aiTool.visitLog.visitors') }}</div>
                  </div>
                </a-card>
              </a-col>
              <a-col :span="8">
                <a-card class="stat-card">
                  <template #title>
                    <span><FieldTimeOutlined /> {{ $t('aiTool.visitLog.dailyAverage') }}</span>
                  </template>
                  <div class="stat-card-content">
                    <div class="stat-number">{{ calculateDailyAverage() }}</div>
                    <div class="stat-label">{{ $t('aiTool.visitLog.perDay') }}</div>
                  </div>
                </a-card>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :span="16">
                <a-card :title="$t('aiTool.visitLog.visitTrend')" class="stat-card">
                  <div ref="chartRef" style="width: 100%; height: 400px;"></div>
                </a-card>
              </a-col>
              <a-col :span="8">
                <a-card :title="$t('aiTool.visitLog.sourceDistribution')" class="stat-card">
                  <a-table
                    :columns="refererColumns"
                    :data-source="statsData.refererStats || []"
                    :pagination="false"
                    size="small"
                  >
                    <template #bodyCell="{ column, record }">
                      <template v-if="column.key === 'referer'">
                        <a-tooltip :title="record.referer">
                          {{ formatReferer(record.referer) }}
                        </a-tooltip>
                      </template>
                      <template v-if="column.key === 'percentage'">
                        {{ calculatePercentage(record.count) }}%
                      </template>
                    </template>
                  </a-table>
                </a-card>
              </a-col>
            </a-row>
          </a-spin>
        </a-tab-pane>
      </a-tabs>
    </a-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, nextTick } from 'vue';

import { message, Modal } from 'ant-design-vue';
import {
  SearchOutlined,
  ReloadOutlined,
  DeleteOutlined,
  EyeOutlined,
  ClearOutlined,
  UserOutlined,
  FieldTimeOutlined,
  DownOutlined,
  UpOutlined
} from '@ant-design/icons-vue';
import {
  getVisitLogList,
  getVisitLogInfo,
  getVisitStats,
  deleteVisitLog,
  batchDeleteVisitLog,
  clearVisitLog
} from '@/api/admin/aiToolVisitLog';
import { formatDateTime } from '@/utils/timeUtils';
import * as echarts from 'echarts/core';
import { LineChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  ToolboxComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

// 注册ECharts组件
echarts.use([
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  ToolboxComponent,
  LineChart,
  CanvasRenderer
]);

// 高级搜索开关
const advanced = ref(false);
const toggleAdvanced = () => {
  advanced.value = !advanced.value;
};

// 高级统计开关
const statsAdvanced = ref(false);
const toggleStatsAdvanced = () => {
  statsAdvanced.value = !statsAdvanced.value;
};

// 当前激活的标签页
const activeTabKey = ref('list');

// 查询参数
const queryParams = reactive({
  page: 1,
  limit: 10,
  toolName: '',
  userType: undefined,
  ip: '',
  referer: '',
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
const visitLogList = ref([]);
const loading = ref(false);
const total = ref(0);
const selectedRowKeys = ref([]);
const currentRecord = ref(null);
const drawerVisible = ref(false);

// 清空日志相关
const clearModalVisible = ref(false);
const clearLoading = ref(false);
const clearForm = reactive({
  toolId: 0,
  days: 30
});

// 工具选项
const toolOptions = ref([]);

// 统计相关
const statsParams = reactive({
  toolId: 0,
  days: 30
});
const statsLoading = ref(false);
const statsData = reactive({
  daily: [],
  totalVisits: 0,
  uniqueVisitors: 0,
  refererStats: []
});
const chartRef = ref(null);
let chart = null;

// 分页配置
const pagination = computed(() => ({
  total: total.value,
  current: queryParams.page,
  pageSize: queryParams.limit,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `共 ${total} 条记录`
}));

// 表格列定义
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 80
  },
  {
    title: '工具名称',
    dataIndex: 'tool_name',
    key: 'tool_name',
    width: 180
  },
  {
    title: '用户',
    dataIndex: 'user_nickname',
    key: 'user_nickname',
    width: 150,
    filters: [
      { text: '管理员', value: 'admin' },
      { text: '普通用户', value: 'user' }
    ],
  },
  {
    title: 'IP地址',
    dataIndex: 'ip',
    key: 'ip',
    width: 150
  },
  {
    title: '来源页面',
    dataIndex: 'referer',
    key: 'referer'
  },
  {
    title: '访问时间',
    dataIndex: 'create_time',
    key: 'create_time',
    width: 180,
    sorter: true
  },
  {
    title: '操作',
    key: 'action',
    width: 150,
    fixed: 'right'
  }
];

// 来源分布表格列定义
const refererColumns = [
  {
    title: '来源',
    dataIndex: 'referer',
    key: 'referer'
  },
  {
    title: '访问次数',
    dataIndex: 'count',
    key: 'count'
  },
  {
    title: '占比',
    key: 'percentage'
  }
];

// 使用公共的date.js中的formatDateTime函数

// 格式化来源页面
const formatReferer = (referer) => {
  if (!referer) return '直接访问';
  if (referer.length > 30) {
    return referer.substring(0, 30) + '...';
  }
  return referer;
};

// 计算百分比
const calculatePercentage = (count) => {
  if (!statsData.totalVisits || !count) return 0;
  return ((count / statsData.totalVisits) * 100).toFixed(2);
};

// 计算日均访问量
const calculateDailyAverage = () => {
  if (!statsData.totalVisits || !statsData.daily || statsData.daily.length === 0) return 0;
  return (statsData.totalVisits / statsData.daily.length).toFixed(2);
};

// 加载访问日志列表
const loadVisitLogList = async () => {
  loading.value = true;
  try {
    const params = { ...queryParams };
    if (params.toolName) {
      params.tool_name = params.toolName;
      delete params.toolName;
    }
    const res = await getVisitLogList(params);
    if (res.code === 200) {
      visitLogList.value = res.data.list;
      total.value = res.data.total;

      // 提取工具选项
      const tools = new Map();
      visitLogList.value.forEach(item => {
        if (item.tool_id && item.tool_name) {
          tools.set(item.tool_id, { id: item.tool_id, name: item.tool_name });
        }
      });
      toolOptions.value = Array.from(tools.values());
    } else {
      message.error(res.data.msg || '获取访问日志列表失败');
    }
  } catch (error) {
    console.error('获取访问日志列表出错:', error);
    message.error('获取访问日志列表失败');
  } finally {
    loading.value = false;
  }
};

// 加载访问统计数据
const loadVisitStats = async () => {
  statsLoading.value = true;
  try {
    const res = await getVisitStats(statsParams);
    if (res.code === 200) {
      statsData.daily = res.data.daily || [];
      statsData.totalVisits = res.data.total_visits || 0;
      statsData.uniqueVisitors = res.data.unique_visitors || 0;
      statsData.refererStats = res.data.referer_stats || [];

      // 更新图表
      nextTick(() => {
        renderChart();
      });
    } else {
      message.error(res.data.msg || '获取访问统计数据失败');
    }
  } catch (error) {
    console.error('获取访问统计数据出错:', error);
    message.error('获取访问统计数据失败');
  } finally {
    statsLoading.value = false;
  }
};

// 渲染图表
const renderChart = () => {
  if (!chartRef.value) return;

  if (!chart) {
    chart = echarts.init(chartRef.value);
  }

  const dates = statsData.daily.map(item => item.date);
  const counts = statsData.daily.map(item => item.count);

  const option = {
    title: {
      text: '访问趋势图'
    },
    tooltip: {
      trigger: 'axis',
      formatter: '{b}<br />{a}: {c} 次'
    },
    toolbox: {
      feature: {
        saveAsImage: {}
      }
    },
    xAxis: {
      type: 'category',
      data: dates,
      axisLabel: {
        rotate: dates.length > 15 ? 45 : 0
      }
    },
    yAxis: {
      type: 'value',
      minInterval: 1
    },
    series: [
      {
        name: '访问量',
        type: 'line',
        data: counts,
        smooth: true,
        markPoint: {
          data: [
            { type: 'max', name: '最大值' },
            { type: 'min', name: '最小值' }
          ]
        },
        markLine: {
          data: [
            { type: 'average', name: '平均值' }
          ]
        }
      }
    ]
  };

  chart.setOption(option);

  // 监听窗口大小变化，调整图表大小
  window.addEventListener('resize', () => {
    chart && chart.resize();
  });
};

// 查询
const handleSearch = () => {
  queryParams.page = 1;
  loadVisitLogList();
};

// 重置
const handleReset = () => {
  queryParams.page = 1;
  queryParams.toolName = '';
  queryParams.userType = undefined;
  queryParams.ip = '';
  queryParams.referer = '';
  queryParams.start_time = undefined;
  queryParams.end_time = undefined;
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';
  // 删除旧的参数
  delete queryParams.startTime;
  delete queryParams.endTime;
  dateRange.value = [];
  loadVisitLogList();
};

// 刷新
const handleRefresh = () => {
  loadVisitLogList();
};

// 表格变化
const handleTableChange = (pag, filters, sorter) => {
  queryParams.page = pag.current;
  queryParams.limit = pag.pageSize;

  if (sorter && sorter.field) {
    queryParams.order_field = sorter.field;
    queryParams.order_type = sorter.order === 'ascend' ? 'asc' : 'desc';
  }

  loadVisitLogList();
};

// 选择行变化
const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

// 查看详情
const handleViewDetail = async (record) => {
  try {
    const res = await getVisitLogInfo({ id: record.id });
    if (res.code === 200) {
      currentRecord.value = res.data;
      drawerVisible.value = true;
    } else {
      message.error(res.data.msg || '获取访问日志详情失败');
    }
  } catch (error) {
    console.error('获取访问日志详情出错:', error);
    message.error('获取访问日志详情失败');
  }
};

// 删除访问日志
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除ID为 ${record.id} 的访问日志吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await deleteVisitLog({ id: record.id });
        if (res.data.code === 200) {
          message.success('删除成功');
          loadVisitLogList();
        } else {
          message.error(res.data.msg || '删除失败');
        }
      } catch (error) {
        console.error('删除访问日志出错:', error);
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
    content: `确定要删除选中的 ${selectedRowKeys.value.length} 条访问日志吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchDeleteVisitLog({ ids: selectedRowKeys.value });
        if (res.data.code === 200) {
          message.success('批量删除成功');
          selectedRowKeys.value = [];
          loadVisitLogList();
        } else {
          message.error(res.data.msg || '批量删除失败');
        }
      } catch (error) {
        console.error('批量删除访问日志出错:', error);
        message.error('批量删除失败');
      }
    }
  });
};

// 清空日志
const handleClear = () => {
  clearForm.toolId = 0;
  clearForm.days = 30;
  clearModalVisible.value = true;
};

// 确认清空
const confirmClear = async () => {
  clearLoading.value = true;
  try {
    const res = await clearVisitLog({
      tool_id: clearForm.toolId,
      days: clearForm.days
    });

    if (res.data.code === 200) {
      message.success('清空日志成功');
      clearModalVisible.value = false;
      loadVisitLogList();
    } else {
      message.error(res.data.msg || '清空日志失败');
    }
  } catch (error) {
    console.error('清空日志出错:', error);
    message.error('清空日志失败');
  } finally {
    clearLoading.value = false;
  }
};

// 监听标签页切换
watch(activeTabKey, (newVal) => {
  if (newVal === 'stats') {
    loadVisitStats();
  }
});

// 页面加载时获取数据
onMounted(() => {
  loadVisitLogList();
});
</script>

<style scoped>
.ai-tool-visit-log-container {
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

/* 保留旧样式以兼容其他部分 */
.stats-card-content {
  text-align: center;
  padding: 16px 0;
}

.stats-number {
  font-size: 28px;
  font-weight: bold;
  color: #1890ff;
}

.stats-label {
  font-size: 14px;
  color: #666;
  margin-top: 8px;
}

.mt-4 {
  margin-top: 16px;
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
