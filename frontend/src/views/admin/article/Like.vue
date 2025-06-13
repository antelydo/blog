<!-- 点赞管理 -->
<template>
  <div class="like-management-container">
    <el-card shadow="hover" class="like-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('like.title') }}</span>
          <div class="header-actions">
            <div class="search-filter-container">
              <el-input
                v-model="listQuery.keyword"
                :placeholder="$t('like.filter.placeholder')"
                clearable
                class="search-input"
                @keyup.enter="handleFilter"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
              <el-select v-model="listQuery.user_type" :placeholder="$t('like.filter.userType')" clearable class="filter-select">
                <el-option :label="$t('like.filter.allTypes')" value="" />
                <el-option :label="$t('like.filter.registeredUser')" value="user" />
                <el-option :label="$t('like.filter.guest')" value="guest" />
                <el-option :label="$t('like.filter.admin')" value="admin" />
              </el-select>
              <el-button type="primary" @click="handleFilter">{{ $t('like.filter.search') }}</el-button>
              <el-button @click="resetQuery">{{ $t('like.filter.reset') }}</el-button>
            </div>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="likeList"
        border
        style="width: 100%"
      >
        <el-table-column prop="id" :label="$t('like.list.id')" width="80" />
        <el-table-column prop="article_title" :label="$t('like.list.articleTitle')" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <el-link type="primary" @click="viewArticle(scope.row.post_id)">{{ scope.row.post_title }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="user_name" :label="$t('like.list.user')" width="120">
          <template #default="scope">
            <div class="user-info">
              <el-avatar :size="24" :src="scope.row.user_avatar || defaultAvatar"></el-avatar>
              <span>{{ scope.row.nickname || scope.row.username || $t('like.list.anonymousUser') }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="user_type" :label="$t('like.list.userType')" width="100">
          <template #default="scope">
            <el-tag :type="getUserTypeClass(scope.row.user_type)" effect="plain">
              {{ getUserTypeText(scope.row.user_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="ip" :label="$t('like.list.ip')" width="130">
          <template #default="scope">
            {{ scope.row.ip || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="create_time" :label="$t('like.list.createTime')" min-width="160">
          <template #default="scope">
            {{ formatDate(scope.row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('like.list.operations')" width="120" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(scope.row)"
            >
              {{ $t('like.list.deleteButton') }}
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

    <!-- 点赞统计卡片 -->
    <el-row :gutter="20" class="statistics-cards">
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('like.statistics.totalLikes') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalLikes || 0 }}</div>
          <div class="card-footer">
            <span>{{ $t('like.statistics.todayLikes') }}</span>
            <span class="growth-up">+{{ statistics.todayLikes || 0 }}</span>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('like.statistics.likesPerUser') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.likesPerUser || 0 }}</div>
          <div class="card-footer">
            <span>{{ $t('like.statistics.activeUsers') }}</span>
            <span>{{ statistics.activeUsers || 0 }}</span>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('like.statistics.coverageRate') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.coverageRate || '0%' }}</div>
          <div class="card-footer">
            <span>{{ $t('like.statistics.maxLikes') }}</span>
            <span>{{ statistics.maxLikes || 0 }}</span>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 最受欢迎文章 -->
    <el-card shadow="hover" class="popular-articles-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('like.popularArticles.title') }}</span>
        </div>
      </template>

      <el-table
        :data="popularArticles"
        border
        style="width: 100%"
      >
        <el-table-column prop="rank" :label="$t('like.popularArticles.rank')" width="80">
          <template #default="scope">
            <div class="rank-cell" :class="'rank-' + scope.row.rank">
              {{ scope.row.rank }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="title" :label="$t('like.popularArticles.articleTitle')" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <el-link type="primary" @click="viewArticle(scope.row.id)">{{ scope.row.title }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="likes" :label="$t('like.popularArticles.likes')" width="100">
          <template #default="scope">
            <div class="likes-number">
              <el-icon><Star /></el-icon>
              {{ scope.row.likes }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="views" :label="$t('like.popularArticles.views')" width="100">
          <template #default="scope">
            <div class="views-number">
              <el-icon><View /></el-icon>
              {{ scope.row.views }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="author" :label="$t('like.popularArticles.author')" width="120" />
        <el-table-column prop="create_time" :label="$t('like.popularArticles.publishTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.create_time) }}
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Star, View } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import { useI18n } from 'vue-i18n'
import { 
  getLikes,
  deleteLike as deleteApi,
  getBlogStats
} from '@/api/admin/article'

export default {
  name: 'LikeManagement',
  components: {
    Search,
    Star,
    View
  },
  setup() {
    const router = useRouter()
    const { t } = useI18n()
    const defaultAvatar = 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png'
    
    // 点赞列表数据
    const likeList = ref([])
    const total = ref(0)
    const loading = ref(false)
    const listQuery = reactive({
      page: 1,
      limit: 10,
      keyword: '',
      user_type: ''
    })
    
    // 统计数据
    const statistics = ref({
      totalLikes: 0,
      todayLikes: 0,
      likesPerUser: 0,
      activeUsers: 0,
      coverageRate: '0%',
      maxLikes: 0,
      avgLikesPerArticle: 0
    })
    
    // 最受欢迎文章
    const popularArticles = ref([])
    
    // 获取点赞列表
    const fetchLikeList = async () => {
      loading.value = true
      try {
        const params = {
          ...listQuery,
          page: Number(listQuery.page),
          limit: Number(listQuery.limit)
        }
        
        const res = await getLikes(params)
        
        if (res.code === 200) {
          likeList.value = res.data.list || []
          total.value = res.data.total || 0
        } else {
          ElMessage.error(res.msg || t('like.list.error'))
        }
      } catch (error) {
        console.error(t('like.list.error'), error)
        ElMessage.error(t('like.list.error'))
      } finally {
        loading.value = false
      }
    }
    
    // 获取统计数据和最受欢迎文章
    const fetchStatistics = async () => {
      try {
        const res = await getBlogStats({ type: 'likes' })
        
        if (res.code === 200 && res.data) {
          // 更新统计数据
          statistics.value = {
            totalLikes: res.data.totalLikes || 0,
            todayLikes: res.data.todayLikes || 0,
            likesPerUser: res.data.likesPerUser || 0,
            activeUsers: res.data.activeUsers || 0,
            coverageRate: res.data.coverageRate || '0%',
            maxLikes: res.data.maxLikes || 0,
            avgLikesPerArticle: res.data.avgLikesPerArticle || 0
          }
          
          // 更新最受欢迎文章列表
          if (Array.isArray(res.data.popularArticles)) {
            popularArticles.value = res.data.popularArticles.map((article, index) => ({
              ...article,
              rank: index + 1
            }))
          }
        }
      } catch (error) {
        console.error(t('like.statistics.error'), error)
      }
    }
    
    // 处理筛选
    const handleFilter = () => {
      listQuery.page = 1
      fetchLikeList()
    }
    
    // 重置筛选条件
    const resetQuery = () => {
      listQuery.keyword = ''
      listQuery.user_type = ''
      handleFilter()
    }
    
    // 处理页码变化
    const handleCurrentChange = (val) => {
      listQuery.page = val
      fetchLikeList()
    }
    
    // 处理每页条数变化
    const handleSizeChange = (val) => {
      listQuery.limit = val
      listQuery.page = 1
      fetchLikeList()
    }
    
    // 查看文章
    const viewArticle = (articleId) => {
      router.push(`/admin/article/edit/${articleId}`)
    }
    
    // 删除点赞
    const handleDelete = (row) => {
      ElMessageBox.confirm(
        t('like.delete.confirm'),
        t('like.delete.warning'),
        {
          confirmButtonText: t('like.delete.confirmButton'),
          cancelButtonText: t('like.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteApi({ id:row.id,post_id:row.post_id,user_id:row.user_id,user_type:row.user_type})
          
          if (res.code === 200) {
            ElMessage.success(t('like.delete.success'))
            fetchLikeList()
            fetchStatistics()
          } else {
            ElMessage.error(res.msg || t('like.delete.error'))
          }
        } catch (error) {
          console.error(t('like.delete.error'), error)
          ElMessage.error(t('like.delete.error'))
        }
      }).catch(() => {})
    }
    
    // 获取用户类型文本
    const getUserTypeText = (type) => {
      return t(`like.userType.${type}`) || t('like.userType.unknown')
    }
    
    // 获取用户类型样式
    const getUserTypeClass = (type) => {
      const typeMap = {
        'user': 'success',
        'guest': 'info',
        'admin': 'danger'
      }
      return typeMap[type] || 'info'
    }
    
    // 初始化数据
    onMounted(() => {
      fetchLikeList()
      fetchStatistics()
    })
    
    return {
      likeList,
      total,
      loading,
      listQuery,
      statistics,
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
      getUserTypeClass
    }
  }
}
</script>

<style scoped>
.like-management-container {
  padding: 20px;
}

.like-table-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  align-items: center;
}

.search-filter-container {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  width: 200px;
}

.filter-select {
  width: 120px;
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

.statistics-cards {
  margin-bottom: 20px;
}

.statistics-card {
  height: 140px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  color: #909399;
  font-size: 14px;
}

.popular-articles-card {
  margin-bottom: 20px;
}

.rank-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  color: #fff;
  font-weight: bold;
  margin: 0 auto;
}

.rank-1 {
  background-color: #f56c6c;
}

.rank-2 {
  background-color: #e6a23c;
}

.rank-3 {
  background-color: #409eff;
}

.likes-number,
.views-number {
  display: flex;
  align-items: center;
  gap: 5px;
}

:deep(.el-table .cell) {
  padding: 8px;
}

:deep(.el-card__header) {
  padding: 15px 20px;
}

:deep(.el-card__body) {
  padding: 15px 20px;
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  .like-management-container {
    padding: 10px;
  }
  
  .search-filter-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input,
  .filter-select {
    width: 100%;
  }
  
  .statistics-cards {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 10px;
  }
}
</style> 