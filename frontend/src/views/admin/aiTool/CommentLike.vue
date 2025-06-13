<template>
  <div class="comment-like-container">
    <a-card :bordered="false" class="card-container">
      <!-- 标题和统计按钮 -->
      <template #title>
        <div class="card-title">
          <span>{{ $t('layout.aiToolCommentLikeManagement') }}</span>
        </div>
      </template>

      <!-- 统计按钮，靠右对齐 -->
      <div class="statistics-button-container">
        <a-button type="primary" @click="showStatistics">
          <template #icon><BarChartOutlined /></template>
          {{ $t('aiTool.commentLike.statistics') }}
        </a-button>
      </div>

      <!-- 搜索表单 -->
      <a-form layout="inline" :model="queryParams" class="search-form">
        <a-form-item label="评价ID">
          <a-input-number
            v-model:value="queryParams.comment_id"
            placeholder="请输入评价ID"
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="用户ID">
          <a-input-number
            v-model:value="queryParams.user_id"
            placeholder="请输入用户ID"
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="用户类型">
          <a-select
            v-model:value="queryParams.user_type"
            placeholder="请选择用户类型"
            style="width: 100%"
            allowClear
          >
            <a-select-option value="admin">管理员</a-select-option>
            <a-select-option value="user">注册用户</a-select-option>
            <a-select-option value="guest">访客</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="点赞时间">
          <a-range-picker
            v-model:value="dateRange"
            format="YYYY-MM-DD"
            :placeholder="['开始日期', '结束日期']"
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="IP地址">
          <a-input
            v-model:value="queryParams.keyword"
            placeholder="请输入IP地址"
            allowClear
          />
        </a-form-item>
        <a-form-item>
          <a-space>
            <a-button type="primary" @click="handleSearch">
              <template #icon><SearchOutlined /></template>
              搜索
            </a-button>
            <a-button @click="handleReset">
              <template #icon><ReloadOutlined /></template>
              重置
            </a-button>
          </a-space>
        </a-form-item>
      </a-form>

      <!-- 操作按钮 -->
      <div class="table-operations">
        <a-space>
          <a-button
            danger
            :disabled="selectedRowKeys.length === 0"
            @click="handleBatchDelete"
          >
            <template #icon><DeleteOutlined /></template>
            批量删除
          </a-button>
          <a-button type="primary" @click="handleExport">
            <template #icon><DownloadOutlined /></template>
            导出数据
          </a-button>
        </a-space>
      </div>

      <!-- 数据表格 -->
      <a-table
        :columns="columns"
        :data-source="dataList"
        :loading="loading"
        :pagination="pagination"
        :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
        :row-key="record => record.id"
        @change="handleTableChange"
      >
        <!-- 用户类型 -->
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'user_type'">
            <a-tag :color="getUserTypeColor(record.user_type)">
              {{ getUserTypeText(record.user_type) }}
            </a-tag>
          </template>

          <!-- 创建时间 -->
          <template v-if="column.key === 'create_time'">
            {{ formatDateTime(record.create_time) }}
          </template>

          <!-- 操作 -->
          <template v-if="column.key === 'action'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">
                <template #icon><EyeOutlined /></template>
                查看
              </a-button>
              <a-button type="link" size="small" danger @click="handleDelete(record)">
                <template #icon><DeleteOutlined /></template>
                删除
              </a-button>
            </a-space>
          </template>
        </template>
      </a-table>

      <!-- 详情抽屉 -->
      <a-drawer
        title="点赞详情"
        :open="drawerVisible"
        :width="500"
        @close="drawerVisible = false"
      >
        <a-descriptions :column="1" bordered v-if="currentRecord">
          <a-descriptions-item label="ID">{{ currentRecord.id }}</a-descriptions-item>
          <a-descriptions-item label="评价ID">{{ currentRecord.comment_id }}</a-descriptions-item>
          <a-descriptions-item label="工具名称">{{ currentRecord.tool_name }}</a-descriptions-item>
          <a-descriptions-item label="评价内容">
            <div style="max-height: 100px; overflow-y: auto;">{{ currentRecord.comment_content }}</div>
          </a-descriptions-item>
          <a-descriptions-item label="用户ID">{{ currentRecord.user_id }}</a-descriptions-item>
          <a-descriptions-item label="用户名称">{{ currentRecord.user_nickname || '-' }}</a-descriptions-item>
          <a-descriptions-item label="用户类型">
            <a-tag :color="getUserTypeColor(currentRecord.user_type)">
              {{ getUserTypeText(currentRecord.user_type) }}
            </a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="访客标识" v-if="currentRecord.uuid">
            {{ currentRecord.uuid }}
          </a-descriptions-item>
          <a-descriptions-item label="IP地址">{{ currentRecord.ip }}</a-descriptions-item>
          <a-descriptions-item label="用户代理">
            <div style="max-height: 60px; overflow-y: auto;">{{ currentRecord.user_agent }}</div>
          </a-descriptions-item>
          <a-descriptions-item label="点赞时间">
            {{ formatDateTime(currentRecord.create_time) }}
          </a-descriptions-item>
        </a-descriptions>

        <template #extra>
          <a-space>
            <a-button @click="drawerVisible = false">关闭</a-button>
            <a-button danger @click="handleDeleteFromDrawer">删除</a-button>
          </a-space>
        </template>
      </a-drawer>

      <!-- 统计分析对话框 -->
      <a-modal
        title="点赞统计分析"
        :open="statisticsVisible"
        :width="800"
        @cancel="statisticsVisible = false"
        :footer="null"
      >
        <a-spin :spinning="statisticsLoading">
          <!-- 统计卡片 -->
          <a-row :gutter="16" class="statistics-cards">
            <a-col :span="8">
              <a-card>
                <template #title>
                  <span>总点赞数</span>
                </template>
                <div class="statistics-value">{{ statistics.total_likes || 0 }}</div>
              </a-card>
            </a-col>
            <a-col :span="8">
              <a-card>
                <template #title>
                  <span>今日点赞数</span>
                </template>
                <div class="statistics-value">{{ statistics.today_likes || 0 }}</div>
              </a-card>
            </a-col>
            <a-col :span="8">
              <a-card>
                <template #title>
                  <span>点赞用户数</span>
                </template>
                <div class="statistics-value">
                  {{ (statistics.like_user_count || 0) + (statistics.like_guest_count || 0) }}
                </div>
              </a-card>
            </a-col>
          </a-row>

          <!-- 用户类型分布图表 -->
          <a-card title="用户类型分布" class="chart-card">
            <div ref="userTypeChartRef" style="width: 100%; height: 300px;"></div>
          </a-card>

          <!-- 点赞最多的评价 -->
          <a-card title="点赞最多的评价" class="chart-card">
            <a-table
              :columns="mostLikedColumns"
              :data-source="statistics.most_liked_comments || []"
              :pagination="false"
              size="small"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'content'">
                  <div class="comment-content-preview">{{ record.content }}</div>
                </template>
              </template>
            </a-table>
          </a-card>
        </a-spin>
      </a-modal>
    </a-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue';
import { message, Modal } from 'ant-design-vue';
import {
  SearchOutlined,
  ReloadOutlined,
  DeleteOutlined,
  EyeOutlined,
  DownloadOutlined,
  LikeOutlined,
  UserOutlined,
  TeamOutlined,
  BarChartOutlined
} from '@ant-design/icons-vue';
import { getCommentLikeList, getCommentLikeInfo, deleteCommentLike, batchDeleteCommentLike, getCommentLikeStatistics } from '@/api/admin/aiToolCommentLike';
import { formatDateTime } from '@/utils/timeUtils';
import * as XLSX from 'xlsx';
import * as echarts from 'echarts/core';
import { PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

// 注册必要的ECharts组件
echarts.use([
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  PieChart,
  CanvasRenderer
]);

// 表格列定义
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'center'
  },
  {
    title: '评价ID',
    dataIndex: 'comment_id',
    key: 'comment_id',
    width: 100,
    align: 'center'
  },
  {
    title: '工具名称',
    dataIndex: 'tool_name',
    key: 'tool_name',
    width: 150,
    ellipsis: true
  },
  {
    title: '评价内容',
    dataIndex: 'comment_content',
    key: 'comment_content',
    width: 200,
    ellipsis: true
  },
  {
    title: '用户ID',
    dataIndex: 'user_id',
    key: 'user_id',
    width: 100,
    align: 'center'
  },
  {
    title: '用户名称',
    dataIndex: 'user_nickname',
    key: 'user_nickname',
    width: 120,
    ellipsis: true
  },
  {
    title: '用户类型',
    dataIndex: 'user_type',
    key: 'user_type',
    width: 120,
    align: 'center'
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
    width: 180
  },
  {
    title: '操作',
    key: 'action',
    width: 150,
    align: 'center',
    fixed: 'right'
  }
];

// 点赞最多的评价表格列
const mostLikedColumns = [
  {
    title: '评价ID',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'center'
  },
  {
    title: '评价内容',
    dataIndex: 'content',
    key: 'content'
  },
  {
    title: '点赞数',
    dataIndex: 'likes',
    key: 'likes',
    width: 100,
    align: 'center'
  }
];

// 数据列表
const dataList = ref([]);
// 加载状态
const loading = ref(false);
// 选中的行
const selectedRowKeys = ref([]);
// 当前记录
const currentRecord = ref(null);
// 抽屉可见性
const drawerVisible = ref(false);
// 统计对话框可见性
const statisticsVisible = ref(false);
// 统计数据加载状态
const statisticsLoading = ref(false);
// 统计数据
const statistics = ref({});
// 用户类型图表引用
const userTypeChartRef = ref(null);
// 日期范围
const dateRange = ref([]);

// 分页配置
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `共 ${total} 条记录`
});

// 查询参数
const queryParams = reactive({
  page: 1,
  limit: 10,
  comment_id: undefined,
  user_id: undefined,
  user_type: undefined,
  start_time: undefined,
  end_time: undefined,
  keyword: undefined
});

// 组件挂载时加载数据
onMounted(() => {
  loadData();
});

// 加载数据列表
const loadData = async () => {
  loading.value = true;
  try {
    // 处理日期范围
    if (dateRange.value && dateRange.value.length === 2) {
      queryParams.start_time = dateRange.value[0] ? Math.floor(dateRange.value[0].valueOf() / 1000) : undefined;
      queryParams.end_time = dateRange.value[1] ? Math.floor(dateRange.value[1].valueOf() / 1000) + 86399 : undefined;
    } else {
      queryParams.start_time = undefined;
      queryParams.end_time = undefined;
    }

    const res = await getCommentLikeList({
      ...queryParams,
      page: pagination.current,
      limit: pagination.pageSize
    });

    if (res.code === 200) {
      dataList.value = res.data.list;
      pagination.total = res.data.total;
    } else {
      message.error(res.msg || '获取点赞列表失败');
    }
  } catch (error) {
    console.error('获取点赞列表出错:', error);
    message.error('获取点赞列表失败');
  } finally {
    loading.value = false;
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  loadData();
};

// 重置搜索条件
const handleReset = () => {
  Object.keys(queryParams).forEach(key => {
    if (key !== 'page' && key !== 'limit') {
      queryParams[key] = undefined;
    }
  });
  dateRange.value = [];
  pagination.current = 1;
  loadData();
};

// 表格变化事件
const handleTableChange = (pag) => {
  pagination.current = pag.current;
  pagination.pageSize = pag.pageSize;
  loadData();
};

// 选择行变化
const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

// 查看详情
const handleView = (record) => {
  // 直接使用表格中的记录数据，避免额外的API请求
  currentRecord.value = record;
  drawerVisible.value = true;
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
        const res = await deleteCommentLike({ id: record.id });
        if (res.code === 200) {
          message.success('删除成功');
          loadData();
        } else {
          message.error(res.msg || '删除失败');
        }
      } catch (error) {
        console.error('删除点赞出错:', error);
        message.error('删除失败');
      }
    }
  });
};

// 从抽屉中删除
const handleDeleteFromDrawer = () => {
  if (currentRecord.value) {
    Modal.confirm({
      title: '确认删除',
      content: `确定要删除ID为 ${currentRecord.value.id} 的点赞记录吗？`,
      okText: '确认',
      okType: 'danger',
      cancelText: '取消',
      async onOk() {
        try {
          const res = await deleteCommentLike({ id: currentRecord.value.id });
          if (res.code === 200) {
            message.success('删除成功');
            drawerVisible.value = false;
            loadData();
          } else {
            message.error(res.msg || '删除失败');
          }
        } catch (error) {
          console.error('删除点赞出错:', error);
          message.error('删除失败');
        }
      }
    });
  }
};

// 批量删除
const handleBatchDelete = () => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('请选择要删除的记录');
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
        const res = await batchDeleteCommentLike({ ids: selectedRowKeys.value });
        if (res.code === 200) {
          message.success('批量删除成功');
          selectedRowKeys.value = [];
          loadData();
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
const handleExport = () => {
  if (dataList.value.length === 0) {
    message.warning('没有可导出的数据');
    return;
  }

  // 准备导出数据
  const exportData = dataList.value.map(item => ({
    'ID': item.id,
    '评价ID': item.comment_id,
    '点赞类型': item.type === 'comment' ? '评价点赞' : '回复点赞',
    '用户ID': item.user_id,
    '用户类型': getUserTypeText(item.user_type),
    '访客标识': item.uuid || '',
    'IP地址': item.ip,
    '点赞时间': formatDateTime(item.create_time)
  }));

  // 创建工作簿
  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, '工具评价点赞数据');

  // 导出文件
  XLSX.writeFile(workbook, `工具评价点赞数据_${new Date().toISOString().split('T')[0]}.xlsx`);
};

// 显示统计数据
const showStatistics = async () => {
  statisticsVisible.value = true;
  statisticsLoading.value = true;

  try {
    const res = await getCommentLikeStatistics();
    if (res.code === 200) {
      statistics.value = res.data;

      // 在下一个DOM更新周期后初始化图表
      nextTick(() => {
        initUserTypeChart();
      });
    } else {
      message.error(res.msg || '获取统计数据失败');
    }
  } catch (error) {
    console.error('获取统计数据出错:', error);
    message.error('获取统计数据失败');
  } finally {
    statisticsLoading.value = false;
  }
};

// 初始化用户类型分布图表
const initUserTypeChart = () => {
  if (!userTypeChartRef.value) return;

  const chartInstance = echarts.init(userTypeChartRef.value);

  // 准备图表数据
  const userTypeData = statistics.value.user_type_distribution || [];
  const chartData = userTypeData.map(item => ({
    name: getUserTypeText(item.user_type),
    value: item.count
  }));

  // 图表配置
  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'horizontal',
      bottom: 10,
      data: chartData.map(item => item.name)
    },
    series: [
      {
        name: '用户类型分布',
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

  // 设置图表配置
  chartInstance.setOption(option);

  // 监听窗口大小变化，调整图表大小
  window.addEventListener('resize', () => {
    chartInstance.resize();
  });
};

// 获取用户类型文本
const getUserTypeText = (type) => {
  switch (type) {
    case 'admin':
      return '管理员';
    case 'user':
      return '注册用户';
    case 'guest':
      return '访客';
    default:
      return '未知';
  }
};

// 获取用户类型标签颜色
const getUserTypeColor = (type) => {
  switch (type) {
    case 'admin':
      return 'blue';
    case 'user':
      return 'green';
    case 'guest':
      return 'orange';
    default:
      return 'default';
  }
};
</script>

<style scoped>
.comment-like-container {
  padding: 20px;
}

.card-container {
  margin-bottom: 20px;
}

.card-title {
  display: flex;
  align-items: center;
}

.statistics-button-container {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 16px;
}

.search-form {
  margin-bottom: 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.table-operations {
  margin-bottom: 16px;
}

.statistics-cards {
  margin-bottom: 20px;
}

.statistics-value {
  font-size: 24px;
  font-weight: bold;
  text-align: center;
  color: #1890ff;
}

.chart-card {
  margin-bottom: 20px;
}

.comment-content-preview {
  max-width: 400px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
