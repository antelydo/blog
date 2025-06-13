<!-- 活动日志 -->
<template>
  <div class="activity-log-container">
    <!-- 搜索和筛选区域 -->
    <el-card class="filter-card">
      <el-form :inline="true" :model="queryParams" class="filter-form">
        <el-form-item :label="$t('activityLog.filter.username')">
          <el-input
            v-model="queryParams.username"
            :placeholder="$t('activityLog.filter.usernamePlaceholder')"
            clearable
            @keyup.enter="handleQuery"
          />
        </el-form-item>
        <el-form-item :label="$t('activityLog.filter.userType')">
          <el-select
            v-model="queryParams.userType"
            :placeholder="$t('activityLog.filter.userTypePlaceholder')"
            clearable
            style="width: 160px;"
          >
            <el-option :label="$t('activityLog.table.admin')" value="admin" />
            <el-option :label="$t('activityLog.table.normalUser')" value="user" />
          </el-select>
        </el-form-item>
        <el-form-item :label="$t('activityLog.filter.ipAddress')">
          <el-input
            v-model="queryParams.ip"
            :placeholder="$t('activityLog.filter.ipAddressPlaceholder')"
            clearable
            @keyup.enter="handleQuery"
          />
        </el-form-item>
        <el-form-item :label="$t('activityLog.filter.timeRange')">
          <el-date-picker
            v-model="queryParams.timeRange"
            type="daterange"
            range-separator="至"
            :start-placeholder="$t('activityLog.filter.startDate')"
            :end-placeholder="$t('activityLog.filter.endDate')"
            value-format="YYYY-MM-DD"
            :shortcuts="dateShortcuts"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleQuery">
            <el-icon><Search /></el-icon>
            {{ $t('activityLog.filter.search') }}
          </el-button>
          <el-button @click="resetQuery">
            <el-icon><Refresh /></el-icon>
            {{ $t('activityLog.filter.reset') }}
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 数据列表 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('activityLog.table.logList') }}</span>
          <div class="header-operations">
            <el-button type="primary" :icon="Download" @click="handleExport">{{ $t('activityLog.table.export') }}</el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="logList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" :label="$t('activityLog.table.id')" width="80" />
        <el-table-column prop="username" :label="$t('activityLog.table.user')" width="120">
          <template #default="{ row }">
            <el-tag
              :type="row.user_type === 'admin' ? 'danger' : 'primary'"
              size="small"
              effect="plain"
            >
              {{ row.username || $t('activityLog.table.unknownUser') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="user_type" :label="$t('activityLog.table.userType')" width="100">
          <template #default="{ row }">
            {{ row.user_type === 'admin' ? $t('activityLog.table.admin') : $t('activityLog.table.normalUser') }}
          </template>
        </el-table-column>
        <el-table-column prop="ip" :label="$t('activityLog.table.ipAddress')" width="130" />
        <el-table-column prop="action" :label="$t('activityLog.table.action')" show-overflow-tooltip width="130" />
        <el-table-column prop="data" :label="$t('activityLog.table.content')" show-overflow-tooltip />
        <el-table-column prop="create_time" :label="$t('activityLog.table.time')" width="180" />
        <el-table-column :label="$t('activityLog.table.operations')" width="120" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              @click="showDetail(row)"
            >
              {{ $t('activityLog.table.details') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="queryParams.page"
          v-model:page-size="queryParams.limit"
          :page-sizes="[10, 20, 50, 100]"
          :total="total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      :title="$t('activityLog.detail.title')"
      width="600px"
      destroy-on-close
    >
      <el-descriptions :column="1" border>
        <el-descriptions-item :label="$t('activityLog.table.id')">{{ currentDetail.id }}</el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.table.user')">{{ currentDetail.username }}</el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.table.userType')">
          {{ currentDetail.user_type === 'admin' ? $t('activityLog.table.admin') : $t('activityLog.table.normalUser') }}
        </el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.table.ipAddress')">{{ currentDetail.ip }}</el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.table.action')">{{ currentDetail.action }}</el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.table.time')">
          {{ currentDetail.create_time }}
        </el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.detail.browser')" v-if="currentDetail.user_agent">
          {{ currentDetail.user_agent }}
        </el-descriptions-item>
        <el-descriptions-item :label="$t('activityLog.detail.extraData')" v-if="currentDetail.data">
          <pre>{{ formatJson(currentDetail.data) }}</pre>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="detailDialogVisible = false">{{ $t('activityLog.detail.close') }}</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Search, Refresh, Download } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useI18n } from 'vue-i18n'
import adminApi from '@/api/admin'
import apiConfig from '@/api/config'

const { t } = useI18n()

// 查询参数
const queryParams = reactive({
  page: 1,
  limit: 10,
  username: '',
  userType: '',
  ip: '',
  timeRange: []
})

// 日期快捷选项
const dateShortcuts = [
  {
    text: t('activityLog.dateShortcuts.lastWeek'),
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
      return [start, end]
    }
  },
  {
    text: t('activityLog.dateShortcuts.lastMonth'),
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
      return [start, end]
    }
  },
  {
    text: t('activityLog.dateShortcuts.lastThreeMonths'),
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 90)
      return [start, end]
    }
  }
]

const loading = ref(false)
const total = ref(0)
const logList = ref([])
const detailDialogVisible = ref(false)
const currentDetail = ref({})

// 获取日志列表
const getLogList = async () => {
  loading.value = true
  try {
    // 构建请求参数
    const params = {
      ...queryParams,
      start_time: queryParams.timeRange?.[0],
      end_time: queryParams.timeRange?.[1],
      user_type: queryParams.userType // 修改参数名以匹配后端
    }
    delete params.timeRange
    delete params.userType

    // 添加token到请求参数
    const admin_token = localStorage.getItem('admin_token')
    if (admin_token) {
      params.token = admin_token
    }

    const response = await adminApi.post(apiConfig.ADMIN_API.ACTIVITY_LOG, params)


    if (response.code === 200) {
      // 处理不同的数据结构情况
      let logData = []
      let totalCount = 0

      if (response.data && response.data.list) {
        // 标准分页结构
        logData = response.data.list
        totalCount = response.data.total || response.data.count || 0
      } else if (response.data && Array.isArray(response.data)) {
        // 直接返回数组
        logData = response.data
        totalCount = response.data.length
      } else if (response.data && response.data.data) {
        // ThinkPHP分页结构
        logData = response.data.data
        totalCount = response.data.total || 0
      } else {
        // 其他情况
        logData = []
        totalCount = 0
      }

      // 处理日志数据
      logList.value = logData.map(item => {
        // 处理时间格式
        let formattedTime = ''
        if (item.create_time) {
          // 处理Unix时间戳（秒）
          if (typeof item.create_time === 'number') {
            formattedTime = new Date(item.create_time * 1000).toLocaleString()
          } else if (typeof item.create_time === 'string') {
            // 尝试解析日期字符串
            const timestamp = Date.parse(item.create_time)
            if (!isNaN(timestamp)) {
              formattedTime = new Date(timestamp).toLocaleString()
            } else {
              formattedTime = item.create_time
            }
          }
        } else if (item.time) {
          formattedTime = item.time
        } else if (item.created_at) {
          formattedTime = item.created_at
        }

        return {
          id: item.id || item.log_id || '',
          username: item.username || item.user || item.user_name || '未知用户', // 增加更多可能的用户名字段
          user_type: item.user_type || (item.username && item.username.includes('admin') ? 'admin' : 'user'),
          ip: item.ip || item.ip_address || '',
          action: item.action || item.operation || item.description || item.content || '',
          create_time: formattedTime,
          user_agent: item.user_agent || item.browser || '',
          data: item.data || item.extra || ''
        }
      })

      total.value = totalCount
    } else {
      ElMessage.error(response.msg || '获取日志列表失败')
      logList.value = []
      total.value = 0
    }
  } catch (error) {
    console.error('获取日志列表失败:', error)
    ElMessage.error('获取日志列表失败')
    logList.value = []
    total.value = 0
  } finally {
    loading.value = false
  }
}

// 查询操作
const handleQuery = () => {
  queryParams.page = 1
  getLogList()
}

// 重置查询
const resetQuery = () => {
  queryParams.username = ''
  queryParams.userType = ''
  queryParams.ip = ''
  queryParams.timeRange = []
  handleQuery()
}

// 分页大小改变
const handleSizeChange = (val) => {
  queryParams.limit = val
  getLogList()
}

// 页码改变
const handleCurrentChange = (val) => {
  queryParams.page = val
  getLogList()
}

// 显示详情
const showDetail = (row) => {
  currentDetail.value = row
  detailDialogVisible.value = true
}

// 导出日志
const handleExport = async () => {
  try {
    const params = {
      ...queryParams,
      start_time: queryParams.timeRange?.[0],
      end_time: queryParams.timeRange?.[1],
      export: true
    }
    delete params.timeRange

    // 添加token到请求参数
    const admin_token = localStorage.getItem('admin_token')
    if (admin_token) {
      params.token = admin_token
    }


    // 使用原生fetch API处理二进制响应
    const response = await fetch(`${apiUrls.ADMIN_API.ACTIVITY_LOG}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': admin_token ? (admin_token.startsWith('Bearer ') ? admin_token : `Bearer ${admin_token}`) : ''
      },
      body: JSON.stringify(params),
      credentials: 'include'
    })

    if (!response.ok) {
      throw new Error(`导出失败: ${response.status} ${response.statusText}`)
    }

    // 获取文件名
    const contentDisposition = response.headers.get('Content-Disposition')
    let filename = '活动日志.xlsx'
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1].replace(/['"]/g, '')
      }
    }

    // 获取二进制数据
    const blob = await response.blob()

    // 创建下载链接
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    ElMessage.success('导出成功')
  } catch (error) {
    console.error('导出失败:', error)
    ElMessage.error('导出失败: ' + (error.message || '未知错误'))
  }
}

// 格式化日期时间
const formatDateTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(typeof timestamp === 'string' ? timestamp : timestamp * 1000)
  return date.toLocaleString()
}

// 格式化JSON数据
const formatJson = (data) => {
  if (!data) return ''
  try {
    const jsonData = typeof data === 'string' ? JSON.parse(data) : data
    return JSON.stringify(jsonData, null, 2)
  } catch (e) {
    return data
  }
}

onMounted(() => {
  getLogList()
})
</script>

<style scoped>
.activity-log-container {
  padding: 20px;
}

.filter-card {
  margin-bottom: 20px;
}

.filter-form {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.table-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-operations {
  display: flex;
  gap: 10px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

:deep(.el-descriptions__body) {
  background-color: #fff;
}

:deep(.el-descriptions__label) {
  width: 120px;
}

pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  background-color: #f5f7fa;
  padding: 8px;
  border-radius: 4px;
  font-family: monospace;
}
</style>