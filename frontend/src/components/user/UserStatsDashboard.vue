<!-- 用户统计仪表盘组件 -->
<template>
  <div class="user-stats-dashboard">
    <el-row :gutter="20">
      <!-- Total Users Card -->
      <el-col :xs="24" :sm="12" :md="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-header">
            <h3>{{ $t('common.statistics.totalUsers') }}</h3>
            <el-tooltip :content="$t('common.statistics.totalUsersTooltip')" placement="top">
              <i class="el-icon-question"></i>
            </el-tooltip>
          </div>
          <div class="stat-value">{{ stats.totalUsers || 0 }}</div>
          <div class="stat-comparison" :class="{ 'positive': stats.totalUsersChange > 0, 'negative': stats.totalUsersChange < 0 }">
            {{ stats.totalUsersChange > 0 ? '+' : '' }}{{ stats.totalUsersChange || 0 }}% {{ $t('common.statistics.comparedToLastMonth') }}
          </div>
        </el-card>
      </el-col>

      <!-- Active Users Card -->
      <el-col :xs="24" :sm="12" :md="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-header">
            <h3>{{ $t('common.statistics.activeUsers') }}</h3>
            <el-tooltip :content="$t('common.statistics.activeUsersTooltip')" placement="top">
              <i class="el-icon-question"></i>
            </el-tooltip>
          </div>
          <div class="stat-value">{{ stats.activeUsers || 0 }}</div>
          <div class="stat-comparison" :class="{ 'positive': stats.activityRate > 50, 'neutral': stats.activityRate >= 30 && stats.activityRate <= 50, 'negative': stats.activityRate < 30 }">
            {{ stats.activityRate || 0 }}% {{ $t('common.statistics.activityRate') }}
          </div>
        </el-card>
      </el-col>

      <!-- New Today Card -->
      <el-col :xs="24" :sm="12" :md="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-header">
            <h3>{{ $t('common.statistics.newToday') }}</h3>
            <el-tooltip :content="$t('common.statistics.newTodayTooltip')" placement="top">
              <i class="el-icon-question"></i>
            </el-tooltip>
          </div>
          <div class="stat-value">{{ stats.newToday || 0 }}</div>
          <div class="stat-comparison" :class="{ 'positive': stats.newTodayChange > 0, 'negative': stats.newTodayChange < 0 }">
            {{ stats.newTodayChange > 0 ? '+' : '' }}{{ stats.newTodayChange || 0 }}% {{ $t('common.statistics.comparedToYesterday') }}
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="chart-row">
      <!-- Registration Trend Chart -->
      <el-col :xs="24" :md="12">
        <el-card shadow="hover" class="chart-card">
          <template #header>
            <div class="chart-header">
              <span>{{ $t('common.charts.registrationTrend') }}</span>
              <el-radio-group v-model="registrationPeriod" size="small">
                <el-radio-button :value="'day'">{{ $t('common.time.day') }}</el-radio-button>
                <el-radio-button :value="'week'">{{ $t('common.time.week') }}</el-radio-button>
                <el-radio-button :value="'month'">{{ $t('common.time.month') }}</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div v-if="!registrationData.length" class="no-data">{{ $t('common.charts.noData') }}</div>
          <div v-else ref="registrationChart" class="chart"></div>
        </el-card>
      </el-col>

      <!-- Activity Trend Chart -->
      <el-col :xs="24" :md="12">
        <el-card shadow="hover" class="chart-card">
          <template #header>
            <div class="chart-header">
              <span>{{ $t('common.charts.activityTrend') }}</span>
              <el-radio-group v-model="activityPeriod" size="small">
                <el-radio-button :value="'day'">{{ $t('common.time.day') }}</el-radio-button>
                <el-radio-button :value="'week'">{{ $t('common.time.week') }}</el-radio-button>
                <el-radio-button :value="'month'">{{ $t('common.time.month') }}</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div v-if="!activityData.length" class="no-data">{{ $t('user.charts.noData') }}</div>
          <div v-else ref="activityChart" class="chart"></div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch, onBeforeUnmount } from 'vue'
import { getUserStats, getRegistrationTrend, getActivityTrend } from '@/api/dashboard'
import { useI18n } from 'vue-i18n'
import * as echarts from 'echarts/core'
import { LineChart } from 'echarts/charts'
import { TooltipComponent, GridComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

echarts.use([LineChart, TooltipComponent, GridComponent, LegendComponent, CanvasRenderer])

export default {
  name: 'UserStatsDashboard',
  setup() {
    const { t } = useI18n()
    const stats = reactive({
      totalUsers: 0,
      totalUsersChange: 0,
      activeUsers: 0,
      activityRate: 0,
      newToday: 0,
      newTodayChange: 0
    })
    
    const registrationPeriod = ref('week')
    const activityPeriod = ref('week')
    const registrationData = ref([])
    const activityData = ref([])
    const registrationChart = ref(null)
    const activityChart = ref(null)
    let registrationChartInstance = null
    let activityChartInstance = null

    const fetchStats = async () => {
      try {
        const response = await getUserStats()
        Object.assign(stats, response.data)
      } catch (error) {
        console.error('Failed to fetch user statistics', error)
      }
    }

    const fetchRegistrationTrend = async () => {
      try {
        const response = await getRegistrationTrend({
          period: registrationPeriod.value,
          limit: 10
        })
        registrationData.value = response.data
        renderRegistrationChart()
      } catch (error) {
        console.error('Failed to fetch registration trend', error)
      }
    }

    const fetchActivityTrend = async () => {
      try {
        const response = await getActivityTrend({
          period: activityPeriod.value,
          limit: 10
        })
        activityData.value = response.data
        renderActivityChart()
      } catch (error) {
        console.error('Failed to fetch activity trend', error)
      }
    }

    const renderRegistrationChart = () => {
      if (!registrationData.value.length || !registrationChart.value) return
      
      try {
        if (!registrationChartInstance) {
          registrationChartInstance = echarts.init(registrationChart.value, null, {
            renderer: 'canvas',
            useDirtyRect: true
          })
        }
        
        // 准备数据
        let labels = registrationData.value.map(item => item.label)
        let values = registrationData.value.map(item => item.value)
        
        // 确保有数据，防止图表错误
        if (labels.length === 0) {
          labels = ['暂无数据']
          values = [0]
        }
        
        const option = {
          tooltip: {
            trigger: 'axis'
          },
          xAxis: {
            type: 'category',
            data: labels
          },
          yAxis: {
            type: 'value'
          },
          series: [
            {
              name: t('common.charts.registrationTrend'),
              type: 'line',
              data: values,
              smooth: true,
              areaStyle: {}
            }
          ]
        }
        
        registrationChartInstance.setOption(option, true)
      } catch (error) {
        console.error('渲染注册趋势图表失败:', error)
      }
    }

    const renderActivityChart = () => {
      if (!activityData.value.length || !activityChart.value) return
      
      try {
        if (!activityChartInstance) {
          activityChartInstance = echarts.init(activityChart.value, null, {
            renderer: 'canvas',
            useDirtyRect: true
          })
        }
        
        // 准备数据
        let labels = activityData.value.map(item => item.label)
        let values = activityData.value.map(item => item.value)
        
        // 确保有数据，防止图表错误
        if (labels.length === 0) {
          labels = ['暂无数据']
          values = [0]
        }
        
        const option = {
          tooltip: {
            trigger: 'axis'
          },
          xAxis: {
            type: 'category',
            data: labels
          },
          yAxis: {
            type: 'value'
          },
          series: [
            {
              name: t('common.charts.activityTrend'),
              type: 'line',
              data: values,
              smooth: true,
              areaStyle: {}
            }
          ]
        }
        
        activityChartInstance.setOption(option, true)
      } catch (error) {
        console.error('渲染活跃度图表失败:', error)
      }
    }

    watch(registrationPeriod, () => {
      fetchRegistrationTrend()
    })

    watch(activityPeriod, () => {
      fetchActivityTrend()
    })

    onMounted(() => {
      fetchStats()
      fetchRegistrationTrend()
      fetchActivityTrend()
      
      // 窗口大小变化事件处理
      const handleResize = () => {
        try {
          if (registrationChartInstance && typeof registrationChartInstance.resize === 'function') {
            registrationChartInstance.resize()
          }
          if (activityChartInstance && typeof activityChartInstance.resize === 'function') {
            activityChartInstance.resize()
          }
        } catch (error) {
          console.warn('调整图表大小时出错:', error)
        }
      }
      
      window.addEventListener('resize', handleResize)
      
      // 组件卸载时移除事件监听并销毁图表
      onBeforeUnmount(() => {
        window.removeEventListener('resize', handleResize)
        
        try {
          if (registrationChartInstance) {
            registrationChartInstance.dispose()
            registrationChartInstance = null
          }
          
          if (activityChartInstance) {
            activityChartInstance.dispose()
            activityChartInstance = null
          }
        } catch (error) {
          console.warn('销毁图表实例时出错:', error)
        }
      })
    })

    return {
      stats,
      registrationPeriod,
      activityPeriod,
      registrationData,
      activityData,
      registrationChart,
      activityChart
    }
  }
}
</script>

<style scoped>
.user-stats-dashboard {
  padding: 20px;
}

.stat-card {
  margin-bottom: 20px;
  height: 150px;
  display: flex;
  flex-direction: column;
}

.stat-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.stat-header h3 {
  margin: 0;
  margin-right: 5px;
  font-size: 16px;
  font-weight: 500;
  color: #606266;
}

.stat-value {
  font-size: 30px;
  font-weight: 600;
  margin-bottom: 10px;
}

.stat-comparison {
  font-size: 14px;
  color: #909399;
}

.stat-comparison.positive {
  color: #67C23A;
}

.stat-comparison.neutral {
  color: #E6A23C;
}

.stat-comparison.negative {
  color: #F56C6C;
}

.chart-row {
  margin-top: 20px;
}

.chart-card {
  margin-bottom: 20px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart {
  height: 300px;
}

.no-data {
  height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #909399;
  font-size: 14px;
}
</style> 