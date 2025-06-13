<!-- 用户统计组件 -->
<template>
  <div class="user-statistics">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ $t('user.statistics.totalUsers') }}</span>
          <el-tooltip :content="$t('user.statistics.totalUsersTooltip')" placement="top">
            <el-icon><QuestionFilled /></el-icon>
          </el-tooltip>
        </div>
      </template>
      <div class="statistics-value">
        <span class="value">{{ statistics.totalUsers }}</span>
        <div class="trend" :class="statistics.totalUsersTrend > 0 ? 'positive' : statistics.totalUsersTrend < 0 ? 'negative' : ''">
          <el-icon v-if="statistics.totalUsersTrend > 0"><ArrowUp /></el-icon>
          <el-icon v-else-if="statistics.totalUsersTrend < 0"><ArrowDown /></el-icon>
          <span>{{ statistics.totalUsersTrend === 0 ? "0" : Math.abs(statistics.totalUsersTrend) }}% {{ $t('user.statistics.comparedToLastMonth') }}</span>
        </div>
      </div>
    </el-card>

    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ $t('user.statistics.activeUsers') }}</span>
          <el-tooltip :content="$t('user.statistics.activeUsersTooltip')" placement="top">
            <el-icon><QuestionFilled /></el-icon>
          </el-tooltip>
        </div>
      </template>
      <div class="statistics-value">
        <span class="value">{{ statistics.activeUsers }}</span>
        <div class="trend" :class="statistics.activeUsersTrend > 0 ? 'positive' : statistics.activeUsersTrend < 0 ? 'negative' : ''">
          <el-icon v-if="statistics.activeUsersTrend > 0"><ArrowUp /></el-icon>
          <el-icon v-else-if="statistics.activeUsersTrend < 0"><ArrowDown /></el-icon>
          <span>{{ statistics.activeUsersTrend === 0 ? "0" : Math.abs(statistics.activeUsersTrend) }}% {{ $t('user.statistics.comparedToLastMonth') }}</span>
        </div>
      </div>
    </el-card>

    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ $t('user.statistics.newToday') }}</span>
          <el-tooltip :content="$t('user.statistics.newTodayTooltip')" placement="top">
            <el-icon><QuestionFilled /></el-icon>
          </el-tooltip>
        </div>
      </template>
      <div class="statistics-value">
        <span class="value">{{ statistics.newToday }}</span>
        <div class="trend" :class="statistics.newTodayTrend > 0 ? 'positive' : statistics.newTodayTrend < 0 ? 'negative' : ''">
          <el-icon v-if="statistics.newTodayTrend > 0"><ArrowUp /></el-icon>
          <el-icon v-else-if="statistics.newTodayTrend < 0"><ArrowDown /></el-icon>
          <span>{{ statistics.newTodayTrend === 0 ? "0" : Math.abs(statistics.newTodayTrend) }}% {{ $t('user.statistics.comparedToYesterday') }}</span>
        </div>
      </div>
    </el-card>

    <el-card class="chart-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('user.charts.registrationTrend') }}</span>
          <el-radio-group v-model="timeRange" size="small" @change="handleTimeRangeChange">
            <el-radio-button value="week">{{ $t('common.week') }}</el-radio-button>
            <el-radio-button value="month">{{ $t('common.month') }}</el-radio-button>
          </el-radio-group>
        </div>
      </template>
      <div v-if="chartData.labels.length > 0" class="chart-container">
        <canvas ref="chart"></canvas>
      </div>
      <div v-else class="no-data">
        {{ $t('user.charts.noData') }}
      </div>
    </el-card>
  </div>
</template>

<script>
import { ref, onMounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { QuestionFilled, ArrowUp, ArrowDown } from '@element-plus/icons-vue';
import { getUserStatistics, getUserGrowthTrend } from '@/api/user';
import Chart from 'chart.js/auto';

export default {
  name: 'UserStatistics',
  components: {
    QuestionFilled,
    ArrowUp,
    ArrowDown
  },
  setup() {
    const { t } = useI18n();
    const chart = ref(null);
    const chartInstance = ref(null);
    const timeRange = ref('week');
    const statistics = ref({
      totalUsers: 0,
      totalUsersTrend: 0,
      activeUsers: 0,
      activeUsersTrend: 0,
      newToday: 0,
      newTodayTrend: 0
    });
    const chartData = ref({
      labels: [],
      datasets: [{
        label: '',
        data: [],
        backgroundColor: '#409EFF',
        borderColor: '#409EFF',
        tension: 0.4
      }]
    });
    
    // 格式化百分比函数
    const formatPercentage = (value) => {
      // 处理undefined或null
      if (value === undefined || value === null) return 0;
      
      // 如果是字符串格式
      if (typeof value === 'string') {
        // 处理带百分号的字符串
        if (value.endsWith('%')) {
          return parseInt(value, 10) || 0;
        }
        // 处理纯数字字符串
        return parseInt(value, 10) || 0;
      }
      
      // 如果是数字，确保是整数
      if (typeof value === 'number') {
        // 处理小数(例如0.05表示5%)，转为整数百分比
        if (value < 1 && value > -1) {
          return Math.round(value * 100);
        }
        return Math.round(value);
      }
      
      // 其他情况返回0
      return 0;
    };
    
    // 获取用户统计数据
    const fetchStatistics = async () => {
      try {
        // 先获取基本统计数据
        const response = await getUserStatistics(timeRange.value);
        
        if (response && response.code === 200 && response.data) {
          // 处理用户统计数据
          const userData = response.data.user || {};
          
          // 处理各种可能的数据字段和格式
          const total = userData.total || userData.totalUsers || 0;
          const active = userData.active || userData.activeUsers || 0;
          const today = userData.today || userData.newToday || userData.todayUsers || 0;
          
          // 暂存基本统计数据
          statistics.value = {
            totalUsers: parseInt(total, 10) || 0,
            totalUsersTrend: 0,
            activeUsers: parseInt(active, 10) || 0,
            activeUsersTrend: 0,
            newToday: parseInt(today, 10) || 0,
            newTodayTrend: 0
          };
        }
        
        // 获取专门的统计百分比数据
        await fetchStatisticsPercentage();
        
        // 获取用户增长趋势数据用于图表
        await fetchGrowthTrend();
      } catch (error) {
        console.error('获取用户统计数据失败:', error);
      }
    };
    
    // 获取统计百分比数据
    const fetchStatisticsPercentage = async () => {
      try {
        // 调用专门的统计接口获取百分比数据
        const response = await getUserGrowthTrend({ 
          days: 30, 
          type: "statistics" 
        });
        
        
        if (response && response.code === 200 && response.data) {
          const statsData = response.data;
          
          // 更新统计对象中的百分比数据
          statistics.value = {
            ...statistics.value,
            totalUsersTrend: formatPercentage(statsData.userGrowth || statsData.totalUsersTrend || 0),
            activeUsersTrend: formatPercentage(statsData.activeRate || statsData.activeUsersTrend || 0),
            newTodayTrend: formatPercentage(statsData.newUserGrowth || statsData.newTodayTrend || 0)
          };
          
        }
      } catch (error) {
        console.error('获取用户增长百分比数据失败:', error);
      }
    };
    
    // 获取用户增长趋势数据
    const fetchGrowthTrend = async () => {
      try {
        // 根据时间范围设置获取的天数
        const days = timeRange.value === 'week' ? 7 : 30;
        
        const response = await getUserGrowthTrend({ days });
        
        if (response && response.code === 200 && response.data) {
          const labels = [];
          const data = [];
          
          // 处理返回的数据
          if (typeof response.data === 'object' && !Array.isArray(response.data)) {
            // 如果是对象格式（键值对形式的日期和数量）
            Object.entries(response.data).forEach(([date, count]) => {
              labels.push(date);
              data.push(count);
            });
          } else if (Array.isArray(response.data)) {
            // 如果是数组格式
            response.data.forEach(item => {
              if (item.date && item.count !== undefined) {
                labels.push(item.date);
                data.push(item.count);
              }
            });
          }
          
          // 更新图表数据
          chartData.value.labels = labels;
          chartData.value.datasets[0].data = data;
          chartData.value.datasets[0].label = t('user.charts.registrationTrend');
          
          // 更新图表
          nextTick(() => {
            updateChart();
          });
        } else {
          // 清空图表数据
          chartData.value.labels = [];
          chartData.value.datasets[0].data = [];
        }
      } catch (error) {
        console.error('获取用户增长趋势数据失败:', error);
        
        // 错误时清空图表数据
        chartData.value.labels = [];
        chartData.value.datasets[0].data = [];
      }
    };
    
    const updateChart = () => {
      if (chartInstance.value) {
        chartInstance.value.destroy();
      }
      
      if (chartData.value.labels.length > 0 && chart.value) {
        chartInstance.value = new Chart(chart.value, {
          type: 'line',
          data: chartData.value,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                mode: 'index',
                intersect: false
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                }
              },
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });
      }
    };
    
    // 时间范围变化时刷新数据
    const handleTimeRangeChange = () => {
      // 重置统计图表
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }
      // 获取完整的统计数据
      fetchStatistics();
    };
    
    onMounted(() => {
      fetchStatistics();
    });
    
    return {
      chart,
      timeRange,
      statistics,
      chartData,
      fetchStatistics,
      handleTimeRangeChange
    };
  }
};
</script>

<style scoped>
.user-statistics {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.chart-card {
  grid-column: 1 / -1;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.statistics-value {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.value {
  font-size: 36px;
  font-weight: bold;
  margin-bottom: 10px;
}

.trend {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #909399;
}

.trend.positive {
  color: #67C23A;
}

.trend.negative {
  color: #F56C6C;
}

.chart-container {
  height: 300px;
  width: 100%;
}

.no-data {
  height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #909399;
  font-style: italic;
}
</style> 