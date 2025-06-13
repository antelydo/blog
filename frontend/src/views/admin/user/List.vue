<!-- 用户管理 -->
<template>
  <div class="user-management-container">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="statistics-cards">
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('common.statistics.totalUsers') }}</span>
              <el-tooltip :content="$t('common.statistics.totalUsersTooltip')" placement="top">
                <el-icon><QuestionFilled /></el-icon>
              </el-tooltip>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalUsers || 0 }}</div>
          <div class="card-footer">
            <span>{{ $t('common.statistics.comparedToLastMonth') }}</span>
            <span :class="statistics.userGrowth >= 0 ? 'growth-up' : 'growth-down'">
              {{ statistics.userGrowth >= 0 ? '+' : '' }}{{ statistics.userGrowth || 0 }}%
            </span>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('common.statistics.activeUsers') }}</span>
              <el-tooltip :content="$t('common.statistics.activeUsersTooltip')" placement="top">
                <el-icon><QuestionFilled /></el-icon>
              </el-tooltip>
            </div>
          </template>
          <div class="card-value">{{ statistics.activeUsers || 0 }}</div>
          <div class="card-footer">
            <span>{{ $t('common.statistics.activityRate') }}</span>
            <span class="growth-up">{{ statistics.activityRate || 0 }}%</span>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('common.statistics.newToday') }}</span>
              <el-tooltip :content="$t('common.statistics.newTodayTooltip')" placement="top">
                <el-icon><QuestionFilled /></el-icon>
              </el-tooltip>
            </div>
          </template>
          <div class="card-value">{{ statistics.newToday || 0 }}</div>
          <div class="card-footer">
            <span>{{ $t('common.statistics.comparedToYesterday') }}</span>
            <span :class="statistics.newUserGrowth >= 0 ? 'growth-up' : 'growth-down'">
              {{ statistics.newUserGrowth >= 0 ? '+' : '' }}{{ statistics.newUserGrowth || 0 }}%
            </span>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 统计图表 -->
    <el-row :gutter="20" class="statistics-charts">
      <el-col :span="12">
        <el-card shadow="hover" class="chart-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('common.statistics.registrationTrend') }}</span>
              <div class="chart-controls">
                <el-radio-group v-model="registrationChartPeriod" size="small">
                  <el-radio-button value="week">{{ $t('common.time.week') }}</el-radio-button>
                  <el-radio-button value="month">{{ $t('common.time.month') }}</el-radio-button>
                </el-radio-group>
              </div>
            </div>
          </template>
          <div class="chart-container" ref="registrationChartRef" v-loading="registrationChartLoading">
            <div v-if="registrationChartError" class="chart-error">
              <el-icon><Warning /></el-icon>
              <p>{{ registrationChartError }}</p>
              <el-button type="primary" size="small" @click="fetchRegistrationChartData">重试</el-button>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card shadow="hover" class="chart-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('common.statistics.activityTrend') }}</span>
              <div class="chart-controls">
                <el-radio-group v-model="activityChartPeriod" size="small">
                  <el-radio-button value="week">{{ $t('common.time.week') }}</el-radio-button>
                  <el-radio-button value="month">{{ $t('common.time.month') }}</el-radio-button>
                </el-radio-group>
              </div>
            </div>
          </template>
          <div class="chart-container" ref="activityChartRef" v-loading="activityChartLoading">
            <div v-if="activityChartError" class="chart-error">
              <el-icon><Warning /></el-icon>
              <p>{{ activityChartError }}</p>
              <el-button type="primary" size="small" @click="fetchActivityChartData">重试</el-button>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 用户列表 -->
    <el-card shadow="hover" class="user-list-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('common.user.list_title') }}</span>
          <div class="header-actions">
            <el-input
              v-model="listQuery.keyword"
              :placeholder="$t('common.user.searchPlaceholder')"
              clearable
              class="search-input"
              @keyup.enter="handleFilter"
            >
              <template #prefix>
                <el-icon><Search /></el-icon>
              </template>
            </el-input>
            <el-select v-model="listQuery.status" :placeholder="$t('common.user.statusPlaceholder')" clearable class="status-select">
              <el-option :label="$t('common.user.statusAll')" value="" />
              <el-option :label="$t('common.user.statusNormal')" value="1" />
              <el-option :label="$t('common.user.statusDisabled')" value="0" />
            </el-select>
            <el-button type="primary" @click="handleFilter">{{ $t('common.user.searchButton') }}</el-button>
            <el-button @click="resetQuery" class="reset-btn">{{ $t('common.user.resetButton') }}</el-button>
            <el-dropdown @command="handleExport" trigger="click" class="export-dropdown">
              <el-button type="success">
                {{ $t('common.user.exportData') }}
                <el-icon class="el-icon--right"><ArrowDown /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="csv">{{ $t('common.user.exportAsCSV') }}</el-dropdown-item>
                  <el-dropdown-item command="excel">{{ $t('common.user.exportAsExcel') }}</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="userList"
        border
        style="width: 100%"
        highlight-current-row
        :header-cell-style="{
          backgroundColor: 'var(--el-table-header-bg-color)',
          color: 'var(--el-text-color-primary)'
        }"
        :cell-style="{
          backgroundColor: 'var(--el-table-tr-bg-color)',
          color: 'var(--el-text-color-regular)'
        }"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" :label="$t('common.form.username')" min-width="120" />
        <el-table-column prop="nickname" :label="$t('common.form.nickname')" min-width="120" />
        <el-table-column prop="email" :label="$t('common.form.email')" min-width="180" />
        <el-table-column prop="mobile" :label="$t('common.form.mobile')" min-width="120" />
        <el-table-column prop="status" :label="$t('common.form.status')" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.status === 1 ? 'success' : 'danger'">
              {{ scope.row.status === 1 ? $t('common.form.active') : $t('user.form.inactive') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="register_time" :label="$t('common.user.registerTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.register_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="last_login_time" :label="$t('common.user.lastLoginTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.last_login_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('common.user.operations')" width="200" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="primary"
              @click="handleEdit(scope.row)"
            >
              {{ $t('common.actions.edit') }}
            </el-button>
            <el-button
              size="small"
              :type="scope.row.status === 1 ? 'warning' : 'success'"
              @click="handleStatusChange(scope.row)"
            >
              {{ scope.row.status === 1 ? $t('common.user.disableUser') : $t('common.user.enableUser') }}
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(scope.row)"
            >
              {{ $t('common.user.deleteUser') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="listQuery.page"
          v-model:page-size="listQuery.limit"
          :page-sizes="[10, 20, 30, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 用户编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'edit' ? $t('common.form.editTitle') : $t('common.form.addTitle')"
      width="600px"
    >
      <el-form
        ref="userFormRef"
        :model="userForm"
        :rules="userRules"
        label-width="100px"
        :disabled="dialogType === 'view'"
      >
        <el-form-item :label="$t('common.form.username')" prop="username">
          <el-input v-model="userForm.username" disabled />
        </el-form-item>
        <el-form-item :label="$t('common.form.nickname')" prop="nickname">
          <el-input v-model="userForm.nickname" />
        </el-form-item>
        <el-form-item :label="$t('common.form.email')" prop="email">
          <el-input v-model="userForm.email" />
        </el-form-item>
        <el-form-item :label="$t('common.form.mobile')" prop="mobile">
          <el-input v-model="userForm.mobile" />
        </el-form-item>
        <el-form-item :label="$t('common.form.status')" prop="status">
          <el-switch
            v-model="userForm.status"
            :active-value="1"
            :inactive-value="0"
            :active-text="$t('common.form.active')"
            :inactive-text="$t('common.form.inactive')"
          />
        </el-form-item>
        <el-form-item :label="$t('common.form.registerTime')">
          <span>{{ formatDate(userForm.register_time) }}</span>
        </el-form-item>
        <el-form-item :label="$t('common.form.lastLoginTime')">
          <span>{{ formatDate(userForm.last_login_time) }}</span>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">{{ $t('common.form.cancel') }}</el-button>
          <el-button type="primary" @click="submitForm" v-if="dialogType !== 'view'">
            {{ $t('common.form.confirm') }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { useI18n } from 'vue-i18n';
import { ref, reactive, onMounted, watch, nextTick, onUnmounted, onBeforeUnmount } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { QuestionFilled, Search, Warning, ArrowDown } from '@element-plus/icons-vue';
import * as echarts from 'echarts';
import { formatDate } from '@/utils/date';
import {
  getUsers,
  updateUserStatus,
  deleteUser,
  getUserDetail,
  updateUser,
  getUserRegistrationStats,
  getUserActivityStats
} from '@/api/admin/user';

export default {
  name: 'UserManagement',
  components: {
    QuestionFilled,
    Search,
    Warning,
    ArrowDown
  },
  setup() {
    // Get i18n instance
    const { t } = useI18n();

    // 统计数据
    const statistics = ref({
      totalUsers: 0,
      activeUsers: 0,
      activityRate: 0,
      newToday: 0,
      userGrowth: 0,
      newUserGrowth: 0
    });

    // 图表相关
    const registrationChartRef = ref(null);
    const activityChartRef = ref(null);
    const registrationChart = ref(null);
    const activityChart = ref(null);
    const registrationChartPeriod = ref('week');
    const activityChartPeriod = ref('week');
    const registrationChartLoading = ref(false);
    const activityChartLoading = ref(false);
    const registrationChartError = ref('');
    const activityChartError = ref('');

    // 用户列表相关
    const userList = ref([]);
    const total = ref(0);
    const loading = ref(false);
    const listQuery = reactive({
      page: 1,
      limit: 10,
      keyword: '',
      status: ''
    });

    // 对话框相关
    const dialogVisible = ref(false);
    const dialogType = ref('edit'); // 'edit' or 'view'
    const userFormRef = ref(null);
    const userForm = reactive({
      id: '',
      username: '',
      nickname: '',
      email: '',
      mobile: '',
      status: 1,
      register_time: '',
      last_login_time: ''
    });

    // 表单验证规则
    const userRules = {
      nickname: [
        { required: true, message: t('common.form.nicknameRequired'), trigger: 'blur' },
        { min: 2, max: 20, message: t('common.form.nicknameLength'), trigger: 'blur' }
      ],
      email: [
        { required: true, message: t('common.form.emailRequired'), trigger: 'blur' },
        { type: 'email', message: t('common.form.emailInvalid'), trigger: 'blur' }
      ],
      mobile: [
        { pattern: /^1[3-9]\d{9}$/, message: t('common.form.mobileInvalid'), trigger: 'blur' }
      ]
    };

    // 获取用户列表
    const fetchUserList = async () => {
      loading.value = true;
      try {
        // 确保页码和每页条数是数字类型
        const params = {
          ...listQuery,
          page: Number(listQuery.page),
          limit: Number(listQuery.limit)
        };


        const res = await getUsers(params);

        if (res.code === 200) {
          userList.value = res.data.list || [];
          total.value = res.data.total || 0;

          // 检查当前页码是否超出总页数
          const totalPages = Math.ceil(total.value / listQuery.limit);
          if (listQuery.page > totalPages && totalPages > 0) {
            // 如果当前页码超出总页数，则重置为最后一页
            listQuery.page = totalPages;
            // 重新获取数据
            fetchUserList();
            return;
          }
        } else {
          ElMessage.error(res.msg || t('common.error.getUserList'));
        }
      } catch (error) {
        console.error('获取用户列表失败:', error);
        ElMessage.error(t('common.error.getUserList'));
      } finally {
        loading.value = false;
      }
    };

    // 获取统计数据
    const fetchStatistics = async () => {
      try {
        // 获取用户统计数据
        const res = await getUserRegistrationStats({
          type: 'statistics' // 添加type参数，用于后端区分是获取统计数据还是图表数据
        });


        if (res.code === 200 && res.data) {
          // 更新统计数据
          statistics.value = {
            totalUsers: res.data.totalUsers || 0,
            activeUsers: res.data.activeUsers || 0,
            activityRate: res.data.activityRate || 0,
            newToday: res.data.todayNewUsers || 0,
            userGrowth: res.data.userGrowth || 0,
            newUserGrowth: res.data.newUserGrowth || 0
          };
        } else {
          console.error('获取统计数据失败:', res.msg);
          ElMessage.error(t('common.error.fetchStatistics'));
        }
      } catch (error) {
        console.error('获取统计数据失败:', error);
        ElMessage.error(t('common.error.fetchStatistics'));
      }
    };

    // 获取注册趋势图表数据
    const fetchRegistrationChartData = async () => {
      registrationChartLoading.value = true;
      registrationChartError.value = '';
      try {
        const res = await getUserRegistrationStats({
          type: registrationChartPeriod.value
        });

        if (res.code === 200 && res.data) {
          // 转换数据格式
          const chartData = transformChartData(res.data);
          renderRegistrationChart(chartData);
        } else {
          registrationChartError.value = res.msg || t('common.error.fetchChartData');
        }
      } catch (error) {
        console.error('获取注册趋势数据失败:', error);
        registrationChartError.value = t('common.error.fetchChartData');
      } finally {
        registrationChartLoading.value = false;
      }
    };

    // 获取活跃度图表数据
    const fetchActivityChartData = async () => {
      activityChartLoading.value = true;
      activityChartError.value = '';
      try {
        const res = await getUserActivityStats({
          type: activityChartPeriod.value
        });

        if (res.code === 200 && res.data) {
          // 转换数据格式
          const chartData = transformChartData(res.data);
          renderActivityChart(chartData);
        } else {
          activityChartError.value = res.msg || t('common.error.fetchChartData');
        }
      } catch (error) {
        console.error('获取活跃度数据失败:', error);
        activityChartError.value = t('common.error.fetchChartData');
      } finally {
        activityChartLoading.value = false;
      }
    };

    // 转换图表数据格式
    const transformChartData = (data) => {
      // 如果数据为空或未定义，返回默认空数据结构
      if (!data) {
        return { dates: [], values: [] };
      }

      // 如果数据已经是正确的格式，直接返回
      if (data.dates && data.values) {
        return data;
      }

      // 如果是对象格式 {日期: 数量}，转换为 {dates: [], values: []}
      const dates = [];
      const values = [];

      try {
        // 按日期排序
        const sortedDates = Object.keys(data).sort();

        sortedDates.forEach(date => {
          dates.push(date);
          values.push(data[date]);
        });
      } catch (error) {
        console.error('转换图表数据失败:', error);
      }

      return {
        dates,
        values
      };
    };

    // 渲染注册趋势图表
    const renderRegistrationChart = (data) => {
      if (!registrationChartRef.value) {
        console.error('注册趋势图表DOM元素不存在');
        return;
      }

      try {
        // 确保清除之前的实例
        if (registrationChart.value) {
          registrationChart.value.dispose();
        }

        registrationChart.value = echarts.init(registrationChartRef.value, null, {
          renderer: 'canvas',
          useDirtyRect: true
        });

        // 确保数据有效
        let chartDates = Array.isArray(data.dates) ? data.dates : [];
        let chartValues = Array.isArray(data.values) ? data.values : [];

        // 防止空数据导致的错误
        if (chartDates.length === 0) {
          chartDates = [t('common.error.noData')];
          chartValues = [0];
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
            data: chartDates,
            axisTick: {
              alignWithLabel: true
            }
          },
          yAxis: {
            type: 'value'
          },
          series: [
            {
              name: t('common.charts.registrationTrend'),
              type: 'bar',
              barWidth: '60%',
              data: chartValues,
              itemStyle: {
                color: '#409EFF'
              }
            }
          ]
        };

        registrationChart.value.setOption(option, true);
      } catch (error) {
        console.error('渲染注册趋势图表失败:', error);
        registrationChartError.value = t('common.error.renderChart');
      }
    };

    // 渲染活跃度图表
    const renderActivityChart = (data) => {
      if (!activityChartRef.value) {
        console.error('活跃度图表DOM元素不存在');
        return;
      }

      try {
        // 确保清除之前的实例
        if (activityChart.value) {
          activityChart.value.dispose();
        }

        activityChart.value = echarts.init(activityChartRef.value, null, {
          renderer: 'canvas',
          useDirtyRect: true
        });

        // 确保数据有效
        let chartDates = Array.isArray(data.dates) ? data.dates : [];
        let chartValues = Array.isArray(data.values) ? data.values : [];

        // 防止空数据导致的错误
        if (chartDates.length === 0) {
          chartDates = [t('common.error.noData')];
          chartValues = [0];
        }

        const option = {
          tooltip: {
            trigger: 'axis'
          },
          legend: {
            data: [t('common.statistics.activityTrend')]
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: chartDates
          },
          yAxis: {
            type: 'value'
          },
          series: [
            {
              name: t('common.statistics.activityTrend'),
              type: 'line',
              data: chartValues,
              smooth: true,
              lineStyle: {
                width: 3,
                color: '#67C23A'
              },
              areaStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                  {
                    offset: 0,
                    color: 'rgba(103, 194, 58, 0.3)'
                  },
                  {
                    offset: 1,
                    color: 'rgba(103, 194, 58, 0.1)'
                  }
                ])
              }
            }
          ]
        };

        activityChart.value.setOption(option, true);
      } catch (error) {
        console.error('渲染活跃度图表失败:', error);
        activityChartError.value = t('common.error.renderChart');
      }
    };

    // 处理搜索
    const handleFilter = () => {
      listQuery.page = 1;
      fetchUserList();
    };

    // 重置搜索条件
    const resetQuery = () => {
      listQuery.keyword = '';
      listQuery.status = '';
      handleFilter();
    };

    // 处理每页条数变化
    const handleSizeChange = (val) => {
      listQuery.limit = val;
      listQuery.page = 1; // 重置为第一页
      fetchUserList();
    };

    // 处理页码变化
    const handleCurrentChange = (val) => {
      listQuery.page = val;
      fetchUserList();
    };

    // 导出用户数据
    const handleExport = (type) => {
      if (userList.value.length === 0) {
        ElMessage.warning(t('common.user.noDataToExport'));
        return;
      }

      try {
        // 准备CSV数据
        const headers = ['ID', t('common.form.username'), t('common.form.nickname'), t('common.form.email'), t('common.form.mobile'), t('common.form.status'), t('common.user.registerTime'), t('common.user.lastLoginTime')];
        const data = userList.value.map(user => [
          user.id,
          user.username,
          user.nickname,
          user.email,
          user.mobile,
          user.status === 1 ? t('common.form.active') : t('common.form.inactive'),
          formatDate(user.register_time),
          formatDate(user.last_login_time)
        ]);

        // 添加表头
        data.unshift(headers);

        // 转换为CSV格式
        const csvContent = data.map(row => row.map(cell => {
          // 将单元格值转换为字符串
          const cellStr = cell !== null && cell !== undefined ? String(cell) : '';

          // 处理包含逗号、引号或换行符的单元格
          if (cellStr && (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n'))) {
            return `"${cellStr.replace(/"/g, '""')}"`;
          }
          return cellStr;
        }).join(',')).join('\n');

        // 创建Blob对象
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });

        // 创建下载链接
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        // 设置文件名
        const fileName = `用户列表_${formatDate(new Date(), 'YYYY-MM-DD')}.${type === 'excel' ? 'xlsx' : 'csv'}`;

        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';

        // 添加到文档并触发点击
        document.body.appendChild(link);
        link.click();

        // 清理
        document.body.removeChild(link);

        ElMessage.success(t('common.user.exportSuccess', { fileName }));
      } catch (error) {
        console.error('导出失败:', error);
        ElMessage.error(t('common.error.exportFailed'));
      }
    };

    // 处理编辑用户
    const handleEdit = async (row) => {
      dialogType.value = 'edit';
      dialogVisible.value = true;

      try {
        const res = await getUserDetail({ id: row.id });
        if (res.code === 200 && res.data) {
          Object.assign(userForm, res.data);
        } else {
          ElMessage.error(res.msg || t('common.error.getUserDetail'));
        }
      } catch (error) {
        console.error('获取用户详情失败:', error);
        ElMessage.error(t('common.error.getUserDetail'));
      }
    };

    // 处理状态变更
    const handleStatusChange = (row) => {
      const newStatus = row.status === 1 ? 0 : 1;
      const statusMessage = newStatus === 1
        ? t('common.confirmStatus.enableMessage', { username: row.username })
        : t('common.confirmStatus.disableMessage', { username: row.username });
      const statusTitle = newStatus === 1
        ? t('common.confirmStatus.enableTitle')
        : t('common.confirmStatus.disableTitle');

      ElMessageBox.confirm(
        statusMessage,
        statusTitle,
        {
          confirmButtonText: t('common.confirmStatus.confirmButton'),
          cancelButtonText: t('common.confirmStatus.cancelButton'),
          type: newStatus === 1 ? 'success' : 'warning'
        }
      ).then(async () => {
        try {
          const res = await updateUserStatus({
            id: row.id,
            status: newStatus
          });

          if (res.code === 200) {
            ElMessage.success(newStatus === 1
              ? t('common.confirmStatus.enableSuccess')
              : t('common.confirmStatus.disableSuccess'));
            fetchUserList();
          } else {
            ElMessage.error(res.msg || t('common.confirmStatus.operationFailed'));
          }
        } catch (error) {
          console.error(`${newStatus === 1 ? '启用' : '禁用'}用户失败:`, error);
          ElMessage.error(t('common.confirmStatus.operationFailed'));
        }
      }).catch(() => {});
    };

    // 处理删除用户
    const handleDelete = (row) => {
      ElMessageBox.confirm(
        t('common.confirmDelete.message', { username: row.username }),
        t('common.confirmDelete.title'),
        {
          confirmButtonText: t('common.confirmDelete.confirmButton'),
          cancelButtonText: t('common.confirmDelete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteUser({ id: row.id });

          if (res.code === 200) {
            ElMessage.success(t('common.confirmDelete.success'));
            fetchUserList();
          } else {
            ElMessage.error(res.msg || t('common.confirmDelete.failed'));
          }
        } catch (error) {
          console.error('删除用户失败:', error);
          ElMessage.error(t('common.confirmDelete.failed'));
        }
      }).catch(() => {});
    };

    // 提交表单
    const submitForm = async () => {
      if (!userFormRef.value) return;

      await userFormRef.value.validate(async (valid) => {
        if (valid) {
          try {
            const res = await updateUser(userForm);

            if (res.code === 200) {
              ElMessage.success(t('common.user.userUpdated'));
              dialogVisible.value = false;
              fetchUserList();
            } else {
              ElMessage.error(res.msg || t('common.error.updateUser'));
            }
          } catch (error) {
            console.error('更新用户失败:', error);
            ElMessage.error(t('common.error.updateUser'));
          }
        }
      });
    };

    // 监听窗口大小变化，调整图表大小
    const handleResize = () => {
      try {
        if (registrationChart.value && typeof registrationChart.value.resize === 'function') {
          registrationChart.value.resize();
        }
        if (activityChart.value && typeof activityChart.value.resize === 'function') {
          activityChart.value.resize();
        }
      } catch (error) {
        console.warn('调整图表大小时出错:', error);
      }
    };

    onMounted(() => {
      fetchUserList();
      fetchStatistics();
      fetchRegistrationChartData();
      fetchActivityChartData();

      // 添加窗口大小变化监听
      window.addEventListener('resize', handleResize);
    });

    onBeforeUnmount(() => {
      // 移除窗口大小变化监听
      window.removeEventListener('resize', handleResize);

      // 销毁图表实例
      try {
        if (registrationChart.value) {
          registrationChart.value.dispose();
          registrationChart.value = null;
        }
        if (activityChart.value) {
          activityChart.value.dispose();
          activityChart.value = null;
        }
      } catch (error) {
        console.warn('销毁图表实例时出错:', error);
      }
    });

    // 监听图表类型变化
    watch(registrationChartPeriod, () => {
      fetchRegistrationChartData();
    });

    watch(activityChartPeriod, () => {
      fetchActivityChartData();
    });

    return {
      t,
      statistics,
      registrationChartRef,
      activityChartRef,
      registrationChartPeriod,
      activityChartPeriod,
      registrationChartLoading,
      activityChartLoading,
      registrationChartError,
      activityChartError,
      userList,
      total,
      loading,
      listQuery,
      dialogVisible,
      dialogType,
      userFormRef,
      userForm,
      userRules,
      formatDate,
      handleFilter,
      resetQuery,
      handleCurrentChange,
      handleSizeChange,
      handleEdit,
      handleStatusChange,
      handleDelete,
      submitForm,
      handleExport,
      fetchRegistrationChartData,
      fetchActivityChartData
    };
  }
};
</script>

<style scoped>
.user-management-container {
  padding: 20px;
  margin-top: 10px; /* 增加顶部间距 */
}

.statistics-cards {
  margin-bottom: 20px;
}

.statistics-card {
  height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  color: #909399;
  font-size: 14px;
}

.growth-up {
  color: #67C23A;
}

.growth-down {
  color: #F56C6C;
}

.statistics-charts {
  margin-bottom: 20px;
}

.chart-card {
  height: 400px;
}

.chart-container {
  height: 320px;
}

.user-list-card {
  margin-bottom: 20px;
}

.header-actions {
  display: flex;
  align-items: center;
}

.search-input {
  width: 200px;
  margin-right: 10px;
}

.status-select {
  width: 120px;
  margin-right: 10px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.chart-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #909399;
}

.chart-error .el-icon {
  font-size: 48px;
  margin-bottom: 10px;
  color: #E6A23C;
}

.chart-error p {
  margin: 10px 0;
  text-align: center;
}

/* 优化卡片内容区域样式 */
:deep(.el-card__body) {
  padding: 20px;
  padding-top: 0px; /* 修改顶部间距为0 */
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* 优化统计卡片内容区域 */
.statistics-card :deep(.el-card__body) {
  padding: 15px 20px;
  padding-top: 0px; /* 修改顶部间距为0 */
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: calc(100% - 55px); /* 减去header高度 */
}

/* 优化图表卡片内容区域 */
.chart-card :deep(.el-card__body) {
  padding: 15px 20px;
  padding-top: 0px; /* 修改顶部间距为0 */
  height: calc(100% - 55px); /* 减去header高度 */
}

/* 优化用户列表卡片内容区域 */
.user-list-card :deep(.el-card__body) {
  padding: 15px 20px;
  padding-top: 0px; /* 修改顶部间距为0 */
}

/* 优化表格样式 */
:deep(.el-table) {
  margin-bottom: 15px;
}

:deep(.el-table--border) {
  border-radius: 4px;
  overflow: hidden;
}

/* 优化分页器样式 */
:deep(.el-pagination) {
  padding: 10px 0;
  justify-content: flex-end;
}

/* 优化对话框样式 */
:deep(.el-dialog__body) {
  padding: 20px 30px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

/* 修复用户列表面板上下间距问题 */
.user-management-container {
  padding: 20px;
  margin-top: 10px; /* 增加顶部间距 */
}

/* 修复卡片头部样式 */
:deep(.el-card__header) {
  padding: 15px 20px;
  border-bottom: 1px solid #ebeef5;
  box-sizing: border-box;
}

/* 修复表格内容区域样式 */
:deep(.el-table__body-wrapper) {
  overflow-y: auto;
  max-height: calc(100vh - 500px); /* 限制表格高度，防止内容过多时页面过长 */
}

/* 修复表格头部固定 */
:deep(.el-table__header-wrapper) {
  position: sticky;
  top: 0;
  z-index: 1;
}

/* 修复卡片内容区域溢出问题 */
:deep(.el-card__body) {
  overflow: visible;
}

.reset-btn {
  margin-right: 10px;
}

.export-dropdown {
  margin-left: 10px;
}

/* 移除按钮焦点轮廓 */
:deep(.el-button:focus),
:deep(.el-button:focus-visible) {
  outline: none !important;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.3) !important;
}

/* 移除下拉菜单按钮焦点轮廓 */
:deep(.el-dropdown .el-button:focus),
:deep(.el-dropdown .el-button:focus-visible) {
  outline: none !important;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.3) !important;
}
</style>