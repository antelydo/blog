<!-- 用户统计组件 -->
<template>
  <div class="user-statistics-container">
    <h2 class="statistics-title">{{ $t('dashboard.title') }}</h2>
    
    <div class="statistics-cards">
      <el-card class="statistic-card">
        <div class="statistic-content">
          <div class="statistic-icon users-icon">
            <i class="el-icon-user"></i>
          </div>
          <div class="statistic-info">
            <h3 class="statistic-value">{{ userTotal }}</h3>
            <div class="statistic-label">
              <el-tooltip :content="$t('user.statistics.totalUsersTooltip')" placement="top">
                <span>{{ $t('user.statistics.totalUsers') }}</span>
              </el-tooltip>
            </div>
            <div class="comparison" :class="userGrowthClass">
              <i :class="userGrowthIcon"></i>
              <span>{{ userGrowthRate }}% {{ $t('user.statistics.comparedToLastMonth') }}</span>
            </div>
          </div>
        </div>
      </el-card>

      <el-card class="statistic-card">
        <div class="statistic-content">
          <div class="statistic-icon active-icon">
            <i class="el-icon-star-on"></i>
          </div>
          <div class="statistic-info">
            <h3 class="statistic-value">{{ activeUsers }}</h3>
            <div class="statistic-label">
              <el-tooltip :content="$t('user.statistics.activeUsersTooltip')" placement="top">
                <span>{{ $t('user.statistics.activeUsers') }}</span>
              </el-tooltip>
            </div>
            <div class="comparison" :class="activeGrowthClass">
              <i :class="activeGrowthIcon"></i>
              <span>{{ activePercentage }}% {{ $t('user.statistics.activityRate') }}</span>
            </div>
          </div>
        </div>
      </el-card>
      
      <el-card class="statistic-card">
        <div class="statistic-content">
          <div class="statistic-icon new-icon">
            <i class="el-icon-plus"></i>
          </div>
          <div class="statistic-info">
            <h3 class="statistic-value">{{ newUsers }}</h3>
            <div class="statistic-label">
              <el-tooltip :content="$t('user.statistics.newTodayTooltip')" placement="top">
                <span>{{ $t('user.statistics.newToday') }}</span>
              </el-tooltip>
            </div>
            <div class="comparison" :class="newGrowthClass">
              <i :class="newGrowthIcon"></i>
              <span>{{ newGrowthRate }}% {{ $t('user.statistics.comparedToYesterday') }}</span>
            </div>
          </div>
        </div>
      </el-card>
    </div>

    <div class="chart-container">
      <div class="chart-header">
        <h3>{{ $t('user.charts.registrationTrend') }}</h3>
        <el-radio-group v-model="timeRange" size="small">
          <el-radio-button value="week">{{ $t('dashboard.performance.week') }}</el-radio-button>
          <el-radio-button value="month">{{ $t('dashboard.performance.month') }}</el-radio-button>
        </el-radio-group>
      </div>
      <div class="chart-content" v-loading="isLoading">
        <div v-if="!isLoading && chartData.labels.length > 0" class="chart-wrapper">
          <canvas ref="userChart"></canvas>
        </div>
        <div v-else-if="!isLoading" class="no-data-message">
          {{ $t('user.charts.noData') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import Chart from 'chart.js/auto';
import { getUserStatistics } from '@/api/user';

export default {
  name: 'UserStatistics',
  setup() {
    const userTotal = ref(0);
    const activeUsers = ref(0);
    const newUsers = ref(0);
    const userGrowthRate = ref(0);
    const newGrowthRate = ref(0);
    const chartInstance = ref(null);
    const userChart = ref(null);
    const isLoading = ref(true);
    const timeRange = ref('week');
    const chartData = ref({
      labels: [],
      datasets: []
    });

    const activePercentage = computed(() => {
      if (userTotal.value === 0) return 0;
      return Math.round((activeUsers.value / userTotal.value) * 100);
    });

    const userGrowthClass = computed(() => 
      userGrowthRate.value >= 0 ? 'positive' : 'negative'
    );

    const activeGrowthClass = computed(() => 
      activePercentage.value >= 50 ? 'positive' : 'negative'
    );

    const newGrowthClass = computed(() => 
      newGrowthRate.value >= 0 ? 'positive' : 'negative'
    );

    const userGrowthIcon = computed(() => 
      userGrowthRate.value >= 0 ? 'el-icon-top' : 'el-icon-bottom'
    );

    const activeGrowthIcon = computed(() => 
      activePercentage.value >= 50 ? 'el-icon-top' : 'el-icon-bottom'
    );

    const newGrowthIcon = computed(() => 
      newGrowthRate.value >= 0 ? 'el-icon-top' : 'el-icon-bottom'
    );

    const fetchData = async () => {
      isLoading.value = true;
      try {
        const response = await getUserStatistics(timeRange.value);
        userTotal.value = response.data.totalUsers;
        activeUsers.value = response.data.activeUsers;
        newUsers.value = response.data.newUsers;
        userGrowthRate.value = response.data.userGrowthRate;
        newGrowthRate.value = response.data.newGrowthRate;
        
        chartData.value = {
          labels: response.data.chart.labels,
          datasets: [
            {
              label: 'Users',
              data: response.data.chart.data,
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 1
            }
          ]
        };
        
        renderChart();
      } catch (error) {
        console.error('Error fetching user statistics:', error);
      } finally {
        isLoading.value = false;
      }
    };

    const renderChart = () => {
      if (chartInstance.value) {
        chartInstance.value.destroy();
      }

      if (userChart.value && chartData.value.labels.length > 0) {
        const ctx = userChart.value.getContext('2d');
        chartInstance.value = new Chart(ctx, {
          type: 'line',
          data: chartData.value,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });
      }
    };

    onMounted(() => {
      fetchData();
    });

    return {
      userTotal,
      activeUsers,
      newUsers,
      userGrowthRate,
      newGrowthRate,
      userChart,
      isLoading,
      timeRange,
      chartData,
      activePercentage,
      userGrowthClass,
      activeGrowthClass,
      newGrowthClass,
      userGrowthIcon,
      activeGrowthIcon,
      newGrowthIcon,
      fetchData
    };
  },
  watch: {
    timeRange() {
      this.fetchData();
    }
  }
};
</script>

<style scoped>
.user-statistics-container {
  padding: 20px;
  background-color: #f5f7fa;
  border-radius: 8px;
}

.statistics-title {
  margin-bottom: 20px;
  font-size: 1.5rem;
  color: #303133;
}

.statistics-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.statistic-card {
  border-radius: 8px;
  transition: all 0.3s ease;
}

.statistic-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.statistic-content {
  display: flex;
  align-items: center;
}

.statistic-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  margin-right: 15px;
  font-size: 24px;
  color: white;
}

.users-icon {
  background-color: #409EFF;
}

.active-icon {
  background-color: #67C23A;
}

.new-icon {
  background-color: #E6A23C;
}

.statistic-info {
  flex: 1;
}

.statistic-value {
  font-size: 1.8rem;
  font-weight: bold;
  margin: 0;
  color: #303133;
}

.statistic-label {
  font-size: 0.9rem;
  color: #606266;
  margin: 5px 0;
}

.comparison {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.positive {
  color: #67C23A;
}

.negative {
  color: #F56C6C;
}

.chart-container {
  background-color: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #303133;
}

.chart-content {
  position: relative;
  height: 300px;
}

.chart-wrapper {
  height: 100%;
  width: 100%;
}

.no-data-message {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #909399;
  font-size: 1.1rem;
}
</style> 