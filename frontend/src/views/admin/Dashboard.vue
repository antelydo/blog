<!-- 仪表盘 -->

<template>
  <div class="dashboard-container">
    <el-row :gutter="20">
      <!-- 数据卡片 -->
      <el-col :xs="24" :sm="12" :md="6" v-for="(item, index) in statCards" :key="index">
        <el-card class="stat-card" :body-style="{ padding: '20px' }">
          <div class="card-content">
            <div class="card-icon" :style="{ backgroundColor: item.color }">
              <el-icon><component :is="item.icon" /></el-icon>
            </div>
            <div class="card-info">
              <div class="card-title">{{ item.title }}</div>
              <div class="card-value">{{ item.value }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="chart-row">
      <!-- 图表区域 -->
      <el-col :xs="24" :sm="24" :md="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('dashboard.charts.userGrowthTrend') }}</span>
            </div>
          </template>
          <div class="chart-container" ref="userGrowthChart"></div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('dashboard.charts.visitStatistics') }}</span>
            </div>
          </template>
          <div class="chart-container" ref="visitStatChart"></div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="table-row">
      <!-- 最近活动 -->
      <el-col :xs="24" :sm="24" :md="24">
        <el-card class="table-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('dashboard.recentActivity.title') }}</span>
              <el-button type="primary" size="small" text @click="router.push('/admin/activity-log')">{{ $t('dashboard.recentActivity.viewMore') }}</el-button>
            </div>
          </template>
          <el-table :data="activityData" style="width: 100%" v-loading="loading">
            <el-table-column prop="id" :label="$t('dashboard.recentActivity.id')" width="80" />
            <el-table-column prop="user" :label="$t('dashboard.recentActivity.user')" width="120" />
            <el-table-column prop="action" :label="$t('dashboard.recentActivity.action')" />
            <el-table-column prop="ip" :label="$t('dashboard.recentActivity.ipAddress')" width="120" />
            <el-table-column prop="time" :label="$t('dashboard.recentActivity.time')" width="180" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { User, Money, ShoppingCart, View } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'
import apiConfig from '@/api/config'
import * as echarts from 'echarts'
import { getUserGrowthTrend, getVisitStatistics } from '@/api/admin/statistics'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const i18n = useI18n()

// 统计卡片数据
const statCards = ref([
  {
    title: i18n.t('dashboard.statistics.userTotal'),
    value: '0',
    icon: 'User',
    color: '#40c9c6'
  },
  {
    title: i18n.t('dashboard.statistics.todayVisits'),
    value: '0',
    icon: 'View',
    color: '#36a3f7'
  },
  {
    title: i18n.t('dashboard.statistics.orderTotal'),
    value: '0',
    icon: 'ShoppingCart',
    color: '#f4516c'
  },
  {
    title: i18n.t('dashboard.statistics.totalIncome'),
    value: '¥0',
    icon: 'Money',
    color: '#34bfa3'
  }
])

// 活动数据
const activityData = ref([])
const loading = ref(true)

// 获取统计数据
const fetchStatistics = async () => {
  try {
    const admin_token = localStorage.getItem('admin_token');
    const response = await adminApi.post(apiConfig.ADMIN_API.STATISTICS, { token: admin_token })

    if (response.code === 200) {
      // 检查响应数据结构
      let data = {};

      // 处理不同的数据结构情况
      if (response.data && typeof response.data === 'object') {
        data = response.data;
      }


      // 处理嵌套的数据结构 - 后端返回的是嵌套对象
      // 用户总数 - 从user.total字段获取
      if (data.user && data.user.total !== undefined) {
        statCards.value[0].value = data.user.total.toString();
      } else {
        // 兼容旧版API或扁平化结构
        statCards.value[0].value = data.userCount || data.user_count || data.total_users || '0';
      }

      // 今日访问 - 这里需要根据实际API返回结构调整
      // 由于后端可能没有直接返回访问统计，这里使用用户今日新增数据代替
      if (data.user && data.user.today !== undefined) {
        statCards.value[1].value = data.user.today.toString();
      } else {
        statCards.value[1].value = data.todayVisits || data.today_visits || data.visits_today || '0';
      }

      // 订单总数 - 从order.total字段获取
      if (data.order && data.order.total !== undefined) {
        statCards.value[2].value = data.order.total.toString();
      } else {
        statCards.value[2].value = data.orderCount || data.order_count || data.total_orders || '0';
      }

      // 总收入 - 从income.total字段获取
      let incomeValue = '0';
      if (data.income && data.income.total !== undefined) {
        incomeValue = data.income.total.toString();
      } else {
        // 兼容旧版API或扁平化结构
        incomeValue = data.totalIncome || data.total_income || data.income || '0';

        // 检查收入值的类型并进行适当转换
        if (typeof incomeValue === 'object' && incomeValue !== null) {
          // 如果是对象，尝试提取有效的数值
          if (incomeValue.value !== undefined) {
            incomeValue = incomeValue.value.toString();
          } else if (incomeValue.amount !== undefined) {
            incomeValue = incomeValue.amount.toString();
          } else if (incomeValue.total !== undefined) {
            incomeValue = incomeValue.total.toString();
          } else {
            // 如果无法提取有效值，则使用默认值
            console.warn('收入数据格式不正确:', incomeValue);
            incomeValue = '0';
          }
        }
      }

      // 确保最终值是字符串并添加货币符号
      statCards.value[3].value = `¥${incomeValue}`;
      chartError.value = false;

    } else {
      // 使用模拟数据
      statCards.value[0].value = '1234'
      statCards.value[1].value = '567'
      statCards.value[2].value = '89'
      statCards.value[3].value = '¥1024'
      chartError.value = true
      console.warn('获取统计数据失败:', response.message || response.msg || '未知错误')
      ElMessage.warning(i18n.t('dashboard.errors.fetchStatsFailed'))
    }
  } catch (error) {
    // 使用模拟数据
    statCards.value[0].value = '1234'
    statCards.value[1].value = '567'
    statCards.value[2].value = '89'
    statCards.value[3].value = '¥1024'
    chartError.value = true
    console.error('获取统计数据失败:', error)
    ElMessage.error(i18n.t('dashboard.errors.networkError'))
  }
}

// 获取活动日志
const fetchActivityLogs = async () => {
  try {
    const admin_token = localStorage.getItem('admin_token');
    const response = await adminApi.post(apiConfig.ADMIN_API.ACTIVITY_LOG, {
      page: 1,
      limit: 10,
      token: admin_token
    })


    if (response.code === 200) {
      // 检查响应数据结构
      let originalData = [];

      // 处理不同的数据结构情况
      if (response.data && response.data.data && Array.isArray(response.data.data)) {
        // 如果是ThinkPHP分页数据结构
        originalData = response.data.data;
      } else if (response.data && response.data.data && response.data.data.list) {
        // 嵌套的分页数据结构
        originalData = response.data.data.list;
      } else if (response.data && Array.isArray(response.data)) {
        // 如果直接返回数组
        originalData = response.data;
      } else if (response.data && response.data.list && Array.isArray(response.data.list)) {
        // 如果是带list的结构
        originalData = response.data.list;
      } else if (response.data && typeof response.data === 'object') {
        // 其他情况，尝试直接使用data
        originalData = [response.data];
      } else {
        // 兜底方案
        originalData = [];
      }


      // 根据数据库表结构进行更精确的字段映射
      activityData.value = originalData.map(item => {
        // 处理时间格式 - 支持多种格式
        let formattedTime = '';
        if (item.create_time) {
          // 处理Unix时间戳（秒）
          if (typeof item.create_time === 'number') {
            formattedTime = new Date(item.create_time * 1000).toLocaleString();
          } else if (typeof item.create_time === 'string') {
            // 尝试解析日期字符串
            const timestamp = Date.parse(item.create_time);
            if (!isNaN(timestamp)) {
              formattedTime = new Date(timestamp).toLocaleString();
            } else {
              formattedTime = item.create_time;
            }
          }
        } else if (item.time) {
          formattedTime = item.time;
        } else if (item.created_at) {
          formattedTime = item.created_at;
        }

        // 处理用户信息
        let userInfo = '';
        if (item.username) {
          userInfo = item.username;
        } else if (item.user) {
          userInfo = item.user;
        } else if (item.user_id) {
          userInfo = (item.user_type === 'admin' ? '管理员' : '用户') + item.user_id;
        } else {
          userInfo = '未知用户';
        }

        return {
          id: item.id || item.log_id || '',
          user: userInfo,
          action: item.action || item.operation || item.description || item.content || '',
          ip: item.ip || item.ip_address || '',
          time: formattedTime
        };
      });

    } else {
      console.warn('获取活动日志失败:', response.message || response.msg || '未知错误')
      activityData.value = []
    }
  } catch (error) {
    console.error('获取活动日志失败:', error)
    activityData.value = []
  } finally {
    loading.value = false
  }
}

// 图表实例引用
const userGrowthChart = ref(null)
const visitStatChart = ref(null)
let userGrowthChartInstance = null
let visitStatChartInstance = null
const chartError = ref(false)

// 获取用户增长趋势数据
const fetchUserGrowthTrend = async () => {
  try {
    const response = await getUserGrowthTrend({
      days: 30
    })

    if (response.code === 200) {
      renderUserGrowthChart(response.data)
    } else {
      console.error('获取用户增长趋势数据失败:', response.msg)
      ElMessage.error('获取用户增长趋势数据失败')
    }
  } catch (error) {
    console.error('获取用户增长趋势数据失败:', error)
    ElMessage.error('获取用户增长趋势数据失败')
  }
}

// 获取系统访问统计数据
const fetchVisitStatistics = async () => {
  try {
    const response = await getVisitStatistics({
      days: 30
    })

    if (response.code === 200) {
      renderVisitStatChart(response.data)
    } else {
      console.error('获取系统访问统计数据失败:', response.msg)
      ElMessage.error('获取系统访问统计数据失败')
    }
  } catch (error) {
    console.error('获取系统访问统计数据失败:', error)
    ElMessage.error('获取系统访问统计数据失败')
  }
}

// 渲染用户增长趋势图表
const renderUserGrowthChart = (data) => {
  if (!userGrowthChartInstance) return

  // 处理数据格式
  const dates = []
  const counts = []

  // 如果是对象格式，转换为数组
  if (typeof data === 'object' && !Array.isArray(data)) {
    Object.entries(data).forEach(([date, count]) => {
      dates.push(date)
      counts.push(count)
    })
  } else if (Array.isArray(data)) {
    data.forEach(item => {
      dates.push(item.date)
      counts.push(item.count)
    })
  }

  // 确保数据存在，避免空图表引起的错误
  if (dates.length === 0) {
    dates.push('暂无数据')
    counts.push(0)
  }

  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow'
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: dates,
      axisLabel: {
        rotate: 45
      }
    },
    yAxis: {
      type: 'value',
      name: '新增用户数'
    },
    series: [
      {
        name: '新增用户',
        type: 'line',
        smooth: true,
        data: counts,
        itemStyle: {
          color: '#40c9c6'
        },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: 'rgba(64, 201, 198, 0.8)' },
            { offset: 1, color: 'rgba(64, 201, 198, 0.1)' }
          ])
        }
      }
    ]
  }

  try {
    userGrowthChartInstance.setOption(option, true)
  } catch (error) {
    console.warn('渲染用户增长趋势图表出错:', error)
  }
}

// 渲染系统访问统计图表
const renderVisitStatChart = (data) => {
  if (!visitStatChartInstance) return

  // 处理数据格式
  const dates = []
  const counts = []

  // 如果是对象格式，转换为数组
  if (typeof data === 'object' && !Array.isArray(data)) {
    Object.entries(data).forEach(([date, count]) => {
      dates.push(date)
      counts.push(count)
    })
  } else if (Array.isArray(data)) {
    data.forEach(item => {
      dates.push(item.date)
      counts.push(item.count)
    })
  }

  // 确保数据存在，避免空图表引起的错误
  if (dates.length === 0) {
    dates.push('暂无数据')
    counts.push(0)
  }

  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'line'
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: dates,
      axisLabel: {
        rotate: 45
      }
    },
    yAxis: {
      type: 'value',
      name: '访问次数'
    },
    series: [
      {
        name: '访问量',
        type: 'line',
        smooth: true,
        data: counts,
        itemStyle: {
          color: '#36a3f7'
        },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: 'rgba(54, 163, 247, 0.8)' },
            { offset: 1, color: 'rgba(54, 163, 247, 0.1)' }
          ])
        }
      }
    ]
  }

  try {
    visitStatChartInstance.setOption(option, true)
  } catch (error) {
    console.warn('渲染系统访问统计图表出错:', error)
  }
}

// 窗口大小变化时重新调整图表大小
const handleResize = () => {
  try {
    if (userGrowthChartInstance && typeof userGrowthChartInstance.resize === 'function') {
      userGrowthChartInstance.resize()
    }
    if (visitStatChartInstance && typeof visitStatChartInstance.resize === 'function') {
      visitStatChartInstance.resize()
    }
  } catch (error) {
    console.warn('调整图表大小时出错:', error)
  }
}

onMounted(async () => {
  // 首先获取基础统计数据
  await fetchStatistics()
  await fetchActivityLogs()

  // 等待DOM更新
  await nextTick()

  // 初始化图表
  if (userGrowthChart.value && visitStatChart.value) {
    userGrowthChartInstance = echarts.init(userGrowthChart.value, null, {
      renderer: 'canvas',
      useDirtyRect: true
    })
    visitStatChartInstance = echarts.init(visitStatChart.value, null, {
      renderer: 'canvas',
      useDirtyRect: true
    })

    // 获取并渲染图表数据
    await fetchUserGrowthTrend()
    await fetchVisitStatistics()

    // 监听窗口大小变化
    window.addEventListener('resize', handleResize)
  }
})

onBeforeUnmount(() => {
  // 移除事件监听
  window.removeEventListener('resize', handleResize)

  // 销毁图表实例
  userGrowthChartInstance && userGrowthChartInstance.dispose()
  visitStatChartInstance && visitStatChartInstance.dispose()
})
</script>

<style scoped>
.dashboard-container {
  padding: 20px 0;
}

.stat-card {
  margin-bottom: 20px;
  cursor: pointer;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-content {
  display: flex;
  align-items: center;
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.card-icon .el-icon {
  font-size: 24px;
  color: #fff;
}

.card-info {
  flex: 1;
}

.card-title {
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.card-value {
  font-size: 20px;
}

:deep(.dark-theme) .card-value,
:deep(.system-theme) .card-value {
  /* 使用全局样式 */
}

:deep(.dark-theme) .stats-number,
:deep(.system-theme) .stats-number {
  /* 使用全局样式 */
}

:deep(.dark-theme) .growth-up,
:deep(.system-theme) .growth-up {
  /* 使用全局样式 */
}

:deep(.dark-theme) .growth-down,
:deep(.system-theme) .growth-down {
  /* 使用全局样式 */
}

.stats-number {
  margin-top: 5px;
}

.growth-up {
  /* 使用全局样式 */
}

.growth-down {
  /* 使用全局样式 */
}

.chart-row, .table-row {
  margin-top: 20px;
}

.chart-card, .table-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-container {
  height: 400px;
  width: 100%;
}

.chart-card {
  margin-bottom: 20px;
}

.chart-card :deep(.el-card__body) {
  padding: 20px;
  height: calc(100% - 60px);
}

.chart-row {
  margin-bottom: 20px;
}

.chart-placeholder {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>