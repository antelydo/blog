<!-- 收藏管理 -->
<template>
  <div class="favorite-management-container">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="statistics-cards">
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('favorite.statistics.totalFavorites') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.total_favorites || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('favorite.statistics.todayFavorites') }}</span>
              <span class="stats-number growth-up">+{{ statistics.today_favorites || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('favorite.statistics.weekFavorites') }}</span>
              <span class="stats-number">{{ statistics.week_favorites || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('favorite.statistics.monthFavorites') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.month_favorites || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('favorite.statistics.yesterdayFavorites') }}</span>
              <span class="stats-number">{{ statistics.yesterday_favorites || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('favorite.statistics.activeUsers') }}</span>
              <span class="stats-number">{{ userTypeStats.total || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('favorite.statistics.userDistribution') }}</span>
            </div>
          </template>
          <div class="user-distribution">
            <div class="distribution-item" v-for="(item, index) in userTypeStats.items" :key="index">
              <span class="user-type">{{ getUserTypeText(item.user_type) }}</span>
              <el-progress :percentage="item.percentage" :color="getUserTypeColor(item.user_type)" />
              <span class="user-count">{{ item.count }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 收藏列表 -->
    <el-card shadow="hover" class="favorite-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('favorite.title') }}</span>
          <div class="header-actions">
            <div class="search-filter-container">
              <el-input
                v-model="listQuery.keyword"
                :placeholder="$t('favorite.filter.placeholder')"
                clearable
                class="search-input"
                @keyup.enter="handleFilter"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
              <el-select v-model="listQuery.user_type" :placeholder="$t('favorite.filter.userType')" clearable class="filter-select">
                <el-option :label="$t('favorite.filter.allTypes')" value="" />
                <el-option :label="$t('favorite.filter.registeredUser')" value="user" />
                <el-option :label="$t('favorite.filter.admin')" value="admin" />
              </el-select>
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                :start-placeholder="$t('favorite.filter.startDate')"
                :end-placeholder="$t('favorite.filter.endDate')"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
                class="date-range-picker"
              />
              <el-button type="primary" @click="handleFilter">{{ $t('favorite.filter.search') }}</el-button>
              <el-button @click="resetQuery">{{ $t('favorite.filter.reset') }}</el-button>
            </div>
          </div>
        </div>
      </template>

      <div class="batch-actions" v-if="selectedFavorites.length > 0">
        <el-alert
          :title="$t('favorite.batch.selectedItems', { count: selectedFavorites.length })"
          type="info"
          show-icon
          :closable="false"
        >
          <template #default>
            <div class="batch-buttons">
              <el-button type="danger" size="small" @click="batchDelete">
                <el-icon><Delete /></el-icon>
                {{ $t('favorite.batch.delete') }}
              </el-button>
              <el-button type="info" size="small" @click="exportToCsv">
                <el-icon><Download /></el-icon>
                {{ $t('favorite.batch.export') }}
              </el-button>
              <el-button type="warning" size="small" @click="clearSelection">
                <el-icon><Close /></el-icon>
                {{ $t('favorite.batch.clear') }}
              </el-button>
            </div>
          </template>
        </el-alert>
      </div>

      <el-table
        v-loading="loading"
        :data="favoriteList"
        border
        style="width: 100%"
        @selection-change="handleSelectionChange"
        ref="favoriteTableRef"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" :label="$t('favorite.list.id')" width="80" />
        <el-table-column prop="post_title" :label="$t('favorite.list.articleTitle')" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <el-link type="primary" @click="viewArticle(scope.row.post_id)">{{ scope.row.post_title }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="username" :label="$t('favorite.list.user')" width="120">
          <template #default="scope">
            <div class="user-info">
              <el-avatar :size="24" :src="scope.row.avatar || defaultAvatar"></el-avatar>
              <span>{{ scope.row.nickname || scope.row.username || $t('favorite.list.anonymousUser') }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="user_type" :label="$t('favorite.list.userType')" width="120">
          <template #default="scope">
            <el-tag :type="getUserTypeClass(scope.row.user_type)" size="small">
              {{ getUserTypeText(scope.row.user_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" :label="$t('favorite.list.createTime')" width="180">
          <template #default="scope">
            {{ formatDate(scope.row.create_time * 1000) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('favorite.list.operations')" width="180" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="primary"
              @click="viewDetails(scope.row)"
            >
              <el-icon><InfoFilled /></el-icon>
              {{ $t('favorite.list.viewButton') }}
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(scope.row)"
            >
              <el-icon><Delete /></el-icon>
              {{ $t('favorite.list.deleteButton') }}
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
          background
        />
      </div>
    </el-card>

    <!-- 最受欢迎文章 -->
    <el-card shadow="hover" class="popular-articles-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('favorite.popularArticles.title') }}</span>
        </div>
      </template>

      <el-table
        :data="popularArticles"
        border
        style="width: 100%"
      >
        <el-table-column prop="rank" :label="$t('favorite.popularArticles.rank')" width="80">
          <template #default="scope">
            <div class="rank-cell" :class="'rank-' + scope.row.rank">
              {{ scope.row.rank }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="title" :label="$t('favorite.popularArticles.articleTitle')" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <el-link type="primary" @click="viewArticle(scope.row.id)">{{ scope.row.title }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="favorites" :label="$t('favorite.popularArticles.favorites')" width="100">
          <template #default="scope">
            <div class="favorites-number">
              <el-icon><StarFilled /></el-icon>
              {{ scope.row.favorites }}
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      :title="$t('favorite.detail.title')"
      width="600px"
    >
      <div v-loading="detailLoading" class="favorite-detail">
        <template v-if="currentDetail">
          <div class="detail-section">
            <h3>{{ $t('favorite.detail.articleInfo') }}</h3>
            <el-descriptions :column="1" border>
              <el-descriptions-item :label="$t('favorite.detail.articleTitle')">
                <el-link type="primary" @click="viewArticle(currentDetail.post_id)">
                  {{ currentDetail.post_title }}
                </el-link>
              </el-descriptions-item>
              <el-descriptions-item :label="$t('favorite.detail.articleId')">
                {{ currentDetail.post_id }}
              </el-descriptions-item>
              <el-descriptions-item :label="$t('favorite.detail.favoriteTime')">
                {{ formatDate(currentDetail.create_time * 1000) }}
              </el-descriptions-item>
            </el-descriptions>
          </div>

          <div class="detail-section">
            <h3>{{ $t('favorite.detail.userInfo') }}</h3>
            <el-descriptions :column="1" border>
              <el-descriptions-item :label="$t('favorite.detail.username')">
                <div class="user-info-detail">
                  <el-avatar :size="32" :src="currentDetail.avatar || defaultAvatar"></el-avatar>
                  <span>{{ currentDetail.nickname || currentDetail.username }}</span>
                </div>
              </el-descriptions-item>
              <el-descriptions-item :label="$t('favorite.detail.userId')">
                {{ currentDetail.user_id }}
              </el-descriptions-item>
              <el-descriptions-item :label="$t('favorite.detail.userType')">
                <el-tag :type="getUserTypeClass(currentDetail.user_type)">
                  {{ getUserTypeText(currentDetail.user_type) }}
                </el-tag>
              </el-descriptions-item>
            </el-descriptions>
          </div>

          <div class="detail-section" v-if="userFavorites.length > 0">
            <h3>{{ $t('favorite.detail.userOtherFavorites') }}</h3>
            <el-table :data="userFavorites" style="width: 100%" size="small">
              <el-table-column prop="post_title" :label="$t('favorite.detail.articleTitle')" min-width="200" show-overflow-tooltip>
                <template #default="scope">
                  <el-link type="primary" @click="viewArticle(scope.row.post_id)">
                    {{ scope.row.post_title }}
                  </el-link>
                </template>
              </el-table-column>
              <el-table-column prop="create_time" :label="$t('favorite.detail.favoriteTime')" width="150">
                <template #default="scope">
                  {{ formatDate(scope.row.create_time * 1000) }}
                </template>
              </el-table-column>
            </el-table>
          </div>
        </template>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">{{ $t('favorite.detail.close') }}</el-button>
          <el-button type="danger" @click="handleDeleteFromDetail">
            {{ $t('favorite.detail.delete') }}
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 自动刷新提示 -->
    <el-backtop :right="100" :bottom="100">
      <div class="backtop-content">
        <el-tooltip :content="autoRefreshEnabled ? $t('favorite.autoRefresh.enabled') : $t('favorite.autoRefresh.disabled')">
          <el-button
            :type="autoRefreshEnabled ? 'success' : 'info'"
            circle
            @click="toggleAutoRefresh"
          >
            <el-icon><RefreshRight /></el-icon>
          </el-button>
        </el-tooltip>
      </div>
    </el-backtop>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, StarFilled, Delete, InfoFilled, Download, Close, RefreshRight } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import { useI18n } from 'vue-i18n'
import {
  getFavorites,
  deleteFavorite,
  getFavoriteStats
} from '@/api/admin/favorite'

export default {
  name: 'FavoriteManagement',
  components: {
    Search,
    StarFilled,
    Delete,
    InfoFilled,
    Download,
    Close,
    RefreshRight
  },
  setup() {
    const router = useRouter()
    const { t } = useI18n()
    const defaultAvatar = 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png'

    // 收藏列表数据
    const favoriteList = ref([])
    const total = ref(0)
    const loading = ref(false)
    const listQuery = reactive({
      page: 1,
      limit: 10,
      keyword: '',
      user_type: '',
      start_time: '',
      end_time: ''
    })

    // 日期范围选择器
    const dateRange = ref([])

    // 表格引用
    const favoriteTableRef = ref(null)

    // 选中的收藏记录
    const selectedFavorites = ref([])

    // 详情对话框
    const detailDialogVisible = ref(false)
    const detailLoading = ref(false)
    const currentDetail = ref(null)
    const userFavorites = ref([])

    // 自动刷新
    const autoRefreshEnabled = ref(false)
    const autoRefreshInterval = ref(null)
    const autoRefreshTime = 30000 // 30秒

    // 统计数据
    const statistics = ref({
      total_favorites: 0,
      today_favorites: 0,
      yesterday_favorites: 0,
      week_favorites: 0,
      month_favorites: 0,
      user_type_stats: []
    })

    // 用户类型统计
    const userTypeStats = computed(() => {
      const stats = statistics.value.user_type_stats || []
      const total = stats.reduce((sum, item) => sum + item.count, 0)

      const items = stats.map(item => ({
        user_type: item.user_type,
        count: item.count,
        percentage: total > 0 ? Math.round((item.count / total) * 100) : 0
      }))

      return {
        total,
        items
      }
    })

    // 最受欢迎文章
    const popularArticles = ref([])

    // 获取收藏列表
    const fetchFavoriteList = async () => {
      loading.value = true
      try {
        // 处理日期范围
        if (dateRange.value && dateRange.value.length === 2) {
          listQuery.start_time = dateRange.value[0]
          listQuery.end_time = dateRange.value[1]
        } else {
          listQuery.start_time = ''
          listQuery.end_time = ''
        }

        const res = await getFavorites(listQuery)

        if (res.code === 200) {
          favoriteList.value = res.data.list || []
          total.value = res.data.total || 0

          // 清除选中状态
          if (favoriteTableRef.value) {
            favoriteTableRef.value.clearSelection()
          }
          selectedFavorites.value = []
        } else {
          ElMessage.error(res.msg || t('favorite.error.fetchFailed'))
        }
      } catch (error) {
        console.error(t('favorite.error.fetchFailed'), error)
        ElMessage.error(t('favorite.error.networkError'))
      } finally {
        loading.value = false
      }
    }

    // 获取统计数据和最受欢迎文章
    const fetchStatistics = async () => {
      try {
        const res = await getFavoriteStats()

        if (res.code === 200 && res.data) {
          // 更新统计数据
          statistics.value = res.data

          // 更新最受欢迎文章列表
          if (Array.isArray(res.data.popular_posts)) {
            popularArticles.value = res.data.popular_posts.map((article, index) => ({
              ...article,
              rank: index + 1
            }))
          }
        }
      } catch (error) {
        console.error(t('favorite.error.statsFailed'), error)
      }
    }

    // 处理筛选
    const handleFilter = () => {
      listQuery.page = 1
      fetchFavoriteList()
    }

    // 重置筛选条件
    const resetQuery = () => {
      listQuery.keyword = ''
      listQuery.user_type = ''
      dateRange.value = []
      listQuery.start_time = ''
      listQuery.end_time = ''
      handleFilter()
    }

    // 处理页码变化
    const handleCurrentChange = (val) => {
      listQuery.page = val
      fetchFavoriteList()
    }

    // 处理每页条数变化
    const handleSizeChange = (val) => {
      listQuery.limit = val
      listQuery.page = 1
      fetchFavoriteList()
    }

    // 查看文章
    const viewArticle = (articleId) => {
      router.push(`/admin/article/edit/${articleId}`)
    }

    // 删除收藏
    const handleDelete = (row) => {
      ElMessageBox.confirm(
        t('favorite.delete.confirm'),
        t('favorite.delete.warning'),
        {
          confirmButtonText: t('favorite.delete.confirmButton'),
          cancelButtonText: t('favorite.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteFavorite({ id: row.id })

          if (res.code === 200) {
            ElMessage.success(t('favorite.delete.success'))
            fetchFavoriteList()
            fetchStatistics()

            // 如果详情对话框打开且当前记录是该条，则关闭对话框
            if (detailDialogVisible.value && currentDetail.value && currentDetail.value.id === row.id) {
              detailDialogVisible.value = false
            }
          } else {
            ElMessage.error(res.msg || t('favorite.delete.error'))
          }
        } catch (error) {
          console.error(t('favorite.delete.error'), error)
          ElMessage.error(t('favorite.delete.error'))
        }
      }).catch(() => {})
    }

    // 从详情对话框删除
    const handleDeleteFromDetail = () => {
      if (!currentDetail.value) return

      ElMessageBox.confirm(
        t('favorite.delete.confirm'),
        t('favorite.delete.warning'),
        {
          confirmButtonText: t('favorite.delete.confirmButton'),
          cancelButtonText: t('favorite.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteFavorite({ id: currentDetail.value.id })

          if (res.code === 200) {
            ElMessage.success(t('favorite.delete.success'))
            detailDialogVisible.value = false
            fetchFavoriteList()
            fetchStatistics()
          } else {
            ElMessage.error(res.msg || t('favorite.delete.error'))
          }
        } catch (error) {
          console.error(t('favorite.delete.error'), error)
          ElMessage.error(t('favorite.delete.error'))
        }
      }).catch(() => {})
    }

    // 批量删除
    const batchDelete = () => {
      if (selectedFavorites.value.length === 0) return

      ElMessageBox.confirm(
        t('favorite.batch.deleteConfirm', { count: selectedFavorites.value.length }),
        t('favorite.delete.warning'),
        {
          confirmButtonText: t('favorite.delete.confirmButton'),
          cancelButtonText: t('favorite.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        loading.value = true
        const deletePromises = selectedFavorites.value.map(item => {
          return deleteFavorite({ id: item.id })
        })

        try {
          const results = await Promise.allSettled(deletePromises)
          const successCount = results.filter(r => r.status === 'fulfilled' && r.value.code === 200).length
          const failCount = selectedFavorites.value.length - successCount

          if (successCount > 0) {
            ElMessage.success(t('favorite.batch.deleteSuccess', { count: successCount }))
            fetchFavoriteList()
            fetchStatistics()
          }

          if (failCount > 0) {
            ElMessage.warning(t('favorite.batch.deleteFailed', { count: failCount }))
          }
        } catch (error) {
          console.error(t('favorite.batch.error'), error)
          ElMessage.error(t('favorite.batch.error'))
        } finally {
          loading.value = false
        }
      }).catch(() => {})
    }

    // 获取用户类型文本
    const getUserTypeText = (type) => {
      return t(`favorite.userType.${type}`) || t('favorite.userType.unknown')
    }

    // 获取用户类型样式
    const getUserTypeClass = (type) => {
      const typeMap = {
        'user': 'success',
        'admin': 'danger'
      }
      return typeMap[type] || 'info'
    }

    // 获取用户类型颜色
    const getUserTypeColor = (type) => {
      const colorMap = {
        'user': '#67C23A',
        'admin': '#F56C6C'
      }
      return colorMap[type] || '#909399'
    }

    // 查看详情
    const viewDetails = async (row) => {
      detailDialogVisible.value = true
      detailLoading.value = true
      currentDetail.value = row
      userFavorites.value = []

      try {
        // 获取该用户的其他收藏
        const res = await getFavorites({
          user_id: row.user_id,
          user_type: row.user_type,
          limit: 5
        })

        if (res.code === 200 && res.data && res.data.list) {
          // 过滤掉当前记录，只显示其他收藏
          userFavorites.value = res.data.list.filter(item => item.id !== row.id)
        }
      } catch (error) {
        console.error('Failed to fetch user favorites', error)
      } finally {
        detailLoading.value = false
      }
    }

    // 处理选择变化
    const handleSelectionChange = (selection) => {
      selectedFavorites.value = selection
    }

    // 清除选择
    const clearSelection = () => {
      if (favoriteTableRef.value) {
        favoriteTableRef.value.clearSelection()
      }
    }

    // 导出为CSV
    const exportToCsv = () => {
      if (selectedFavorites.value.length === 0) {
        ElMessage.warning(t('favorite.batch.noSelection'))
        return
      }

      // 准备CSV内容
      const headers = [
        t('favorite.list.id'),
        t('favorite.list.articleTitle'),
        t('favorite.list.user'),
        t('favorite.list.userType'),
        t('favorite.list.createTime')
      ]

      const rows = selectedFavorites.value.map(item => [
        item.id,
        item.post_title,
        item.nickname || item.username,
        getUserTypeText(item.user_type),
        formatDate(item.create_time * 1000)
      ])

      // 生成CSV内容
      let csvContent = headers.join(',') + '\n'
      rows.forEach(row => {
        // 处理可能包含逗号的字段
        const processedRow = row.map(field => {
          const fieldStr = String(field)
          return fieldStr.includes(',') ? `"${fieldStr}"` : fieldStr
        })
        csvContent += processedRow.join(',') + '\n'
      })

      // 创建Blob并下载
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.setAttribute('href', url)
      link.setAttribute('download', `favorites_export_${new Date().toISOString().slice(0, 10)}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)

      ElMessage.success(t('favorite.batch.exportSuccess', { count: selectedFavorites.value.length }))
    }

    // 切换自动刷新
    const toggleAutoRefresh = () => {
      autoRefreshEnabled.value = !autoRefreshEnabled.value

      if (autoRefreshEnabled.value) {
        // 启动自动刷新
        autoRefreshInterval.value = setInterval(() => {
          fetchFavoriteList()
          fetchStatistics()
        }, autoRefreshTime)

        ElMessage.success(t('favorite.autoRefresh.started', { seconds: autoRefreshTime / 1000 }))
      } else {
        // 停止自动刷新
        if (autoRefreshInterval.value) {
          clearInterval(autoRefreshInterval.value)
          autoRefreshInterval.value = null
        }

        ElMessage.info(t('favorite.autoRefresh.stopped'))
      }
    }

    // 初始化数据
    onMounted(() => {
      fetchFavoriteList()
      fetchStatistics()
    })

    // 组件卸载前清除定时器
    onBeforeUnmount(() => {
      if (autoRefreshInterval.value) {
        clearInterval(autoRefreshInterval.value)
      }
    })

    return {
      favoriteList,
      total,
      loading,
      listQuery,
      dateRange,
      statistics,
      userTypeStats,
      popularArticles,
      defaultAvatar,
      formatDate,
      handleFilter,
      resetQuery,
      handleCurrentChange,
      handleSizeChange,
      viewArticle,
      handleDelete,
      getUserTypeText,
      getUserTypeClass,
      getUserTypeColor,
      // 新增的属性和方法
      favoriteTableRef,
      selectedFavorites,
      handleSelectionChange,
      clearSelection,
      batchDelete,
      exportToCsv,
      detailDialogVisible,
      detailLoading,
      currentDetail,
      userFavorites,
      viewDetails,
      handleDeleteFromDetail,
      autoRefreshEnabled,
      toggleAutoRefresh
    }
  }
}
</script>

<style scoped>
.favorite-management-container {
  padding: 20px;
}

.statistics-cards {
  margin-bottom: 20px;
}

.statistics-card {
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-value {
  font-size: 36px;
  font-weight: bold;
  color: #303133;
  margin: 20px 0;
  text-align: center;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  color: #909399;
  font-size: 14px;
}

.stats-detail {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stats-number {
  font-weight: bold;
  color: #606266;
  margin-top: 5px;
}

.growth-up {
  color: #67C23A;
}

.growth-down {
  color: #F56C6C;
}

.favorite-table-card {
  margin-bottom: 20px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.search-filter-container {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.search-input {
  width: 300px;
}

.filter-select {
  width: 200px;
}

.date-range-picker {
  width: 350px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-distribution {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.distribution-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-type {
  width: 80px;
  text-align: right;
}

.user-count {
  width: 40px;
  text-align: right;
  font-weight: bold;
}

:deep(.el-progress) {
  flex: 1;
  margin: 0;
}

.popular-articles-card {
  margin-bottom: 20px;
}

.rank-cell {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  margin: 0 auto;
  font-weight: bold;
}

.rank-1 {
  background-color: #f56c6c;
  color: white;
}

.rank-2 {
  background-color: #e6a23c;
  color: white;
}

.rank-3 {
  background-color: #409eff;
  color: white;
}

.favorites-number {
  display: flex;
  align-items: center;
  gap: 5px;
}

/* 批量操作样式 */
.batch-actions {
  margin-bottom: 15px;
}

.batch-buttons {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}

/* 详情对话框样式 */
.favorite-detail {
  padding: 10px;
}

.detail-section {
  margin-bottom: 20px;
}

.detail-section h3 {
  margin-bottom: 10px;
  font-size: 16px;
  color: #606266;
  border-left: 3px solid #409eff;
  padding-left: 10px;
}

.user-info-detail {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* 自动刷新按钮 */
.backtop-content {
  height: 100%;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  .statistics-cards {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 10px;
  }

  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .search-filter-container {
    flex-direction: column;
    align-items: stretch;
  }

  .search-input,
  .filter-select,
  .date-range-picker {
    width: 100%;
  }
}
</style>
