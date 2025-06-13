<!-- 浏览时长统计 -->
<template>
  <div class="view-duration-stats">
    <el-card class="stats-header">
      <template #header>
        <div class="card-header">
          <h2>{{ $t('viewStats.title') }}</h2>
          <div class="header-actions">
            <el-select v-model="timeRange" @change="fetchData" style="width: 150px;">
              <el-option
                v-for="option in timeRangeOptions"
                :key="option.value"
                :label="$t(`viewStats.${option.labelKey}`)"
                :value="option.value"
              />
            </el-select>
            <el-button type="primary" @click="fetchData">{{ $t('viewStats.refresh') }}</el-button>
          </div>
        </div>
      </template>

      <el-row :gutter="20">
        <el-col :span="8">
          <el-card class="stat-card">
            <div class="stat-value">{{ formatDuration(stats.avgPostViewDuration) }}</div>
            <div class="stat-label">{{ $t('viewStats.avgPostDuration') }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card class="stat-card">
            <div class="stat-value">{{ formatDuration(stats.avgSiteViewDuration) }}</div>
            <div class="stat-label">{{ $t('viewStats.avgSiteDuration') }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card class="stat-card">
            <div class="stat-value">{{ stats.totalViewTime }} {{ $t('viewStats.hours') }}</div>
            <div class="stat-label">{{ $t('viewStats.totalViewTime') }}</div>
          </el-card>
        </el-col>
      </el-row>
    </el-card>

    <el-row :gutter="20" class="chart-row">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <h3>{{ $t('viewStats.viewTrend') }}</h3>
            </div>
          </template>
          <div class="chart-container" ref="viewTrendChart"></div>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <h3>{{ $t('viewStats.durationDistribution') }}</h3>
            </div>
          </template>
          <div class="chart-container" ref="durationDistChart"></div>
        </el-card>
      </el-col>
    </el-row>

    <el-card class="popular-posts">
      <template #header>
        <div class="card-header">
          <h3>{{ $t('viewStats.popularPosts') }}</h3>
        </div>
      </template>

      <el-table :data="stats.popularPosts" style="width: 100%">
        <el-table-column prop="title" :label="$t('viewStats.postTitle')" min-width="200">
          <template #default="scope">
            <el-link type="primary" :href="`/post/${scope.row.id}`" target="_blank">{{ scope.row.title }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="views" :label="$t('viewStats.views')" width="120" sortable />
        <el-table-column prop="avg_duration" :label="$t('viewStats.avgDuration')" width="150" sortable>
          <template #default="scope">
            {{ formatDuration(scope.row.avg_duration) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('viewStats.engagementRate')" width="150">
          <template #default="scope">
            {{ calculateEngagementRate(scope.row.avg_duration) }}%
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import * as echarts from 'echarts/core';
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import api from '@/api';
import { apiUrls } from '@/api';

// 注册必要的ECharts组件
echarts.use([
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  BarChart,
  LineChart,
  PieChart,
  CanvasRenderer
]);

export default {
  name: 'ViewDurationStats',
  setup() {
    const { t } = useI18n();

    // 图表引用
    const viewTrendChart = ref(null);
    const durationDistChart = ref(null);

    // 图表实例
    let viewTrendChartInstance = null;
    let durationDistChartInstance = null;

    // 时间范围选项
    const timeRange = ref('30days');

    // 使用固定的标签键名，而不是动态生成的标签
    const timeRangeOptions = [
      { value: '7days', labelKey: 'last7Days' },
      { value: '30days', labelKey: 'last30Days' },
      { value: '90days', labelKey: 'last90Days' },
      { value: 'year', labelKey: 'lastYear' }
    ];

    // 确保初始值存在于选项中
    if (!timeRangeOptions.some(option => option.value === timeRange.value)) {
      timeRange.value = timeRangeOptions[1].value; // 默认选中30天
    }

    // 统计数据
    const stats = ref({
      avgPostViewDuration: 0,
      avgSiteViewDuration: 0,
      totalViewTime: 0,
      viewTrend: [],
      popularPosts: []
    });

    // 获取统计数据
    const fetchData = async () => {
      try {
        const response = await api.get(apiUrls.BLOG_API.WEB_STATISTICS, {
          params: { period: timeRange.value }
        });

        if (response && response.code === 200 && response.data) {
          stats.value = {
            avgPostViewDuration: response.data.avg_post_view_duration || 0,
            avgSiteViewDuration: response.data.avg_site_view_duration || 0,
            totalViewTime: response.data.total_view_time || 0,
            viewTrend: response.data.view_trend || [],
            popularPosts: response.data.popular_posts || []
          };

          // 更新图表
          renderViewTrendChart();
          renderDurationDistChart();
        }
      } catch (error) {
        console.error('获取浏览时长统计数据失败:', error);
      }
    };

    // 格式化时长显示
    const formatDuration = (seconds) => {
      if (!seconds) return '0秒';

      seconds = Math.round(parseFloat(seconds));

      if (seconds < 60) {
        return `${seconds}秒`;
      } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}分${remainingSeconds > 0 ? remainingSeconds + '秒' : ''}`;
      } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}小时${minutes > 0 ? minutes + '分' : ''}`;
      }
    };

    // 计算参与度（浏览时长与内容长度的比率）
    const calculateEngagementRate = (avgDuration) => {
      // 假设平均阅读速度是每分钟200字，一篇文章平均1000字
      // 那么完整阅读需要5分钟（300秒）
      const estimatedReadTime = 300; // 秒
      const rate = Math.min(100, Math.round((avgDuration / estimatedReadTime) * 100));
      return rate;
    };

    // 渲染浏览量趋势图表
    const renderViewTrendChart = () => {
      if (!viewTrendChart.value) return;

      if (!viewTrendChartInstance) {
        viewTrendChartInstance = echarts.init(viewTrendChart.value);
      }

      const dates = stats.value.viewTrend.map(item => item.date);
      const postViews = stats.value.viewTrend.map(item => item.post_views);
      const siteViews = stats.value.viewTrend.map(item => item.site_views);

      const option = {
        title: {
          text: t('viewStats.viewTrend'),
          left: 'center'
        },
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: [t('viewStats.postViews'), t('viewStats.siteViews')],
          bottom: 0
        },
        xAxis: {
          type: 'category',
          data: dates
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            name: t('viewStats.postViews'),
            type: 'line',
            data: postViews,
            smooth: true,
            lineStyle: {
              width: 3
            },
            itemStyle: {
              color: '#409EFF'
            }
          },
          {
            name: t('viewStats.siteViews'),
            type: 'line',
            data: siteViews,
            smooth: true,
            lineStyle: {
              width: 3
            },
            itemStyle: {
              color: '#67C23A'
            }
          }
        ]
      };

      viewTrendChartInstance.setOption(option);
    };

    // 渲染浏览时长分布图表
    const renderDurationDistChart = () => {
      if (!durationDistChart.value) return;

      if (!durationDistChartInstance) {
        durationDistChartInstance = echarts.init(durationDistChart.value);
      }

      // 根据浏览时长分布统计数据
      // 这里使用模拟数据，实际应该从API获取
      const durationRanges = [
        { name: '< 10秒', value: 30 },
        { name: '10-30秒', value: 25 },
        { name: '30秒-1分钟', value: 20 },
        { name: '1-3分钟', value: 15 },
        { name: '3-10分钟', value: 8 },
        { name: '> 10分钟', value: 2 }
      ];

      const option = {
        title: {
          text: t('viewStats.durationDistribution'),
          left: 'center'
        },
        tooltip: {
          trigger: 'item',
          formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
          orient: 'vertical',
          left: 'left',
          data: durationRanges.map(item => item.name)
        },
        series: [
          {
            name: t('viewStats.viewDuration'),
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
            data: durationRanges
          }
        ]
      };

      durationDistChartInstance.setOption(option);
    };

    // 窗口大小变化时重新调整图表大小
    const handleResize = () => {
      if (viewTrendChartInstance) {
        viewTrendChartInstance.resize();
      }
      if (durationDistChartInstance) {
        durationDistChartInstance.resize();
      }
    };

    onMounted(() => {
      // 获取数据
      fetchData();

      // 添加窗口调整事件监听
      window.addEventListener('resize', handleResize);
    });

    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize);
      if (viewTrendChartInstance) {
        viewTrendChartInstance.dispose();
      }
      if (durationDistChartInstance) {
        durationDistChartInstance.dispose();
      }
    });

    return {
      timeRange,
      timeRangeOptions,
      stats,
      viewTrendChart,
      durationDistChart,
      fetchData,
      formatDuration,
      calculateEngagementRate
    };
  }
};
</script>

<style scoped>
.view-duration-stats {
  padding: 20px;
}

.stats-header {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h2, .card-header h3 {
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* 确保下拉框文本可见 */
:deep(.el-select) {
  width: 150px;
}

:deep(.el-select .el-input__inner) {
  color: var(--el-text-color-primary);
  background-color: var(--el-fill-color-blank);
}

:deep(.el-select-dropdown__item) {
  color: var(--el-text-color-primary);
}

.stat-card {
  text-align: center;
  padding: 20px;
  height: 100%;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #409EFF;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.chart-row {
  margin-bottom: 20px;
}

.chart-card {
  height: 400px;
}

.chart-container {
  height: 320px;
}

.popular-posts {
  margin-bottom: 20px;
}
</style>
