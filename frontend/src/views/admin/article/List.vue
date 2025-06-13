<!-- 文章列表 -->
<template>
  <div class="article-list-container">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="statistics-cards">
      <el-col :span="6">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('article.statistics.totalArticles') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalArticles || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('article.statistics.publishedArticles') }}</span>
              <span class="stats-number">{{ statistics.publishedArticles || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('article.statistics.draftArticles') }}</span>
              <span class="stats-number">{{ statistics.draftArticles || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('article.statistics.pendingArticles') }}</span>
              <span class="stats-number">{{ statistics.pendingArticles || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('article.statistics.totalViews') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalViews || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('article.statistics.avgViews') }}</span>
              <span class="stats-number">{{ statistics.avgViews || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('article.statistics.todayViews') }}</span>
              <span class="stats-number growth-up">+{{ statistics.todayViews || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('article.statistics.totalLikes') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalLikes || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('article.statistics.likesPerArticle') }}</span>
              <span class="stats-number">{{ statistics.likesPerArticle || 0 }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('article.statistics.todayLikes') }}</span>
              <span class="stats-number growth-up">+{{ statistics.todayLikes || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="statistics-card">
          <template #header>
            <div class="card-header">
              <span>{{ $t('article.statistics.totalComments') }}</span>
            </div>
          </template>
          <div class="card-value">{{ statistics.totalComments || 0 }}</div>
          <div class="card-footer">
            <div class="stats-detail">
              <span>{{ $t('article.statistics.commentRate') }}</span>
              <span class="stats-number">{{ statistics.commentRate || '0%' }}</span>
            </div>
            <div class="stats-detail">
              <span>{{ $t('article.statistics.rejectedArticles') }}</span>
              <span class="stats-number">{{ statistics.rejectedArticles || 0 }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 文章列表 -->
    <el-card shadow="hover" class="article-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('article.list.title') }}</span>
          <div class="header-actions">
            <div class="search-filter-container">
              <el-input
                v-model="listQuery.keyword"
                :placeholder="$t('article.list.search')"
                clearable
                class="search-input"
                @keyup.enter="handleFilter"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
              <el-select
                v-model="listQuery.categoryId"
                :placeholder="$t('article.filters.category')"
                clearable
                filterable
                class="filter-select"
              >
                <el-option :label="$t('article.filters.allCategories')" value="" />
                <el-option
                  v-for="item in categoryOptions"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id"
                />
              </el-select>
              <el-select
                v-model="listQuery.status"
                :placeholder="$t('article.filters.status')"
                clearable
                filterable
                class="filter-select"
              >
                <el-option :label="$t('article.filters.allStatus')" value="" />
                <el-option :label="$t('article.status.published')" value="published" />
                <el-option :label="$t('article.status.pending')" value="pending" />
                <el-option :label="$t('article.status.draft')" value="draft" />
                <el-option :label="$t('article.status.rejected')" value="rejected" />
              </el-select>

              <el-select v-model="listQuery.is_banner_top" :placeholder="$t('article.filters.isBannerTop')" clearable
                filterable
                class="filter-select">
                <el-option :label="$t('article.filters.all')" value="" />
                <el-option :label="$t('article.filters.yes')" value="1" />
                <el-option :label="$t('article.filters.no')" value="0" />
              </el-select>


              <el-button type="primary" @click="handleFilter">{{ $t('article.list.filter') }}</el-button>
              <el-button @click="resetQuery">{{ $t('article.list.reset') }}</el-button>
            </div>
            <div class="action-buttons">
              <el-button type="success" @click="handleCreateArticle">
                <el-icon><Plus /></el-icon>{{ $t('article.list.create') }}
              </el-button>
            </div>
          </div>
        </div>
      </template>

      <template v-if="Array.isArray(articleList) && articleList !== null">
        <el-table
          v-loading="loading"
          :data="articleList"
          border
          style="width: 100%"
          row-key="id"
          :default-sort="{ prop: 'is_top', order: 'descending'}"
          :key="articleList.length"
          @sort-change="handleSortChange"
          :header-cell-style="{
            backgroundColor: 'var(--el-table-header-bg-color)',
            color: 'var(--el-text-color-primary)'
          }"
          :cell-style="{
            backgroundColor: 'var(--el-table-tr-bg-color)',
            color: 'var(--el-text-color-regular)'
          }"
        >
          <el-table-column prop="id" :label="$t('article.table.id')" width="80" sortable="custom" />
          <el-table-column prop="title" :label="$t('article.table.title')" min-width="220">
            <template #default="scope">
              <div class="article-title-cell">
                <span class="article-title">{{ scope.row.title || $t('article.table.noTitle') }}</span>
                <div class="article-tags" v-if="scope.row.tags && scope.row.tags.length">
                  <el-tag
                    v-for="tag in scope.row.tags"
                    :key="tag.id"
                    size="small"
                    effect="plain"
                    class="article-tag"
                  >
                    {{ tag.name }}
                  </el-tag>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="description" :label="$t('article.table.description')" min-width="200" show-overflow-tooltip />
          <el-table-column prop="author.username" :label="$t('article.table.author')" width="120" sortable="custom" />
          <el-table-column prop="category" :label="$t('article.table.category')" min-width="150">
            <template #default="scope">
              <template v-if="scope.row.categories && scope.row.categories.length">
                <div class="category-tags">
                  <el-tag
                    v-for="category in scope.row.categories"
                    :key="category.id"
                    size="small"
                    effect="plain"
                    class="category-tag"
                  >
                    {{ category.name }}
                  </el-tag>
                </div>
              </template>
              <span v-else>{{ $t('article.table.uncategorized') }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="views" :label="$t('article.table.views')" width="100" sortable="custom">
            <template #default="scope">
              <span class="views-count">
                <el-icon><View /></el-icon>
                {{ scope.row.views }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="likes" :label="$t('article.table.likes')" width="100" sortable="custom">
            <template #default="scope">
              <span class="like-count">
                <el-icon><Star /></el-icon>
                {{ scope.row.likes }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="favorites" :label="$t('article.table.favorites')" width="100" sortable="custom">
            <template #default="scope">
              <span class="favorite-count">
                <el-icon color="#ffc107"><StarFilled /></el-icon>
                {{ scope.row.favorites || 0 }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="comments" :label="$t('article.table.comments')" width="100" sortable="custom">
            <template #default="scope">
              <span class="comment-count">
                <el-icon><ChatDotRound /></el-icon>
                {{ scope.row.comments }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="status" :label="$t('article.table.status')" width="120">
            <template #default="scope">
              <el-tag
                :type="getStatusType(scope.row.status)"
                effect="light"
                size="small"
                class="status-tag"
              >
                {{ getStatusText(scope.row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="is_top" :label="$t('article.table.isTop')" width="80" sortable="custom">
            <template #default="scope">
              <el-switch
                v-model="scope.row.is_top"
                :active-value="1"
                :inactive-value="0"
                @change="handleTopChange(scope.row)"
              />
            </template>
          </el-table-column>
          <el-table-column prop="is_recommend" :label="$t('article.table.isRecommend')" width="80" sortable="custom">
            <template #default="scope">
              <el-switch
                v-model="scope.row.is_recommend"
                :active-value="1"
                :inactive-value="0"
                @change="handleRecommendChange(scope.row)"
              />
            </template>
          </el-table-column>

          <el-table-column prop="is_banner_top" :label="$t('article.table.isBannerTop')" width="80" sortable="custom">
            <template #default="scope">
             <el-switch
                v-model="scope.row.is_banner_top"
                :active-value="1"
                :inactive-value="0"
                @change="handleBannerTopChange(scope.row)"
              />
            </template>
          </el-table-column>

          <el-table-column prop="create_time" :label="$t('article.table.createdAt')" width="160" sortable="custom">
            <template #default="scope">
              {{ formatDate(scope.row.create_time) }}
            </template>
          </el-table-column>
          <el-table-column :label="$t('article.table.actions')" width="200" fixed="right">
            <template #default="scope">
              <el-button
                size="small"
                type="primary"
                @click="handleEdit(scope.row)"
              >
                {{ $t('article.actions.edit') }}
              </el-button>
              <el-button
                size="small"
                type="success"
                v-if="scope.row.status === 'pending' || scope.row.status === 2"
                @click="handleApprove(scope.row)"
              >
                {{ $t('article.actions.approve') }}
              </el-button>
              <el-button
                size="small"
                type="danger"
                @click="handleDelete(scope.row)"
              >
                {{ $t('article.actions.delete') }}
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </template>
      <template v-else>
        <div class="empty-data-placeholder">
          <el-empty :description="$t('article.table.noRecords')" />
        </div>
      </template>

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

    <!-- 文章审核对话框 -->
    <el-dialog
      v-model="approveDialogVisible"
      :title="$t('article.approval.title')"
      width="500px"
    >
      <el-form ref="approveFormRef" :model="approveForm" label-width="80px">
        <el-form-item :label="$t('article.approval.status')" prop="status">
          <el-radio-group v-model="approveForm.status">
            <el-radio value="published">{{ $t('article.approval.approve') }}</el-radio>
            <el-radio value="rejected">{{ $t('article.approval.reject') }}</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item :label="$t('article.approval.remarkLabel')" prop="remark" v-if="approveForm.status === 'rejected'">
          <el-input
            v-model="approveForm.remark"
            type="textarea"
            :rows="3"
            :placeholder="$t('article.approval.remarkPlaceholder')"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="approveDialogVisible = false">{{ $t('article.approval.cancel') }}</el-button>
          <el-button type="primary" @click="submitApprove">{{ $t('article.approval.confirm') }}</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, onErrorCaptured, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Edit, Delete, View, Star, StarFilled, ChatDotRound, Plus } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import {
  getArticles,
  deleteArticle,
  toggleTopArticle,
  toggleRecommendArticle,
  toggleBannerTopArticle,
  updateArticle,
  getBlogStats,
  getCategories
} from '@/api/admin/article'

// 引入i18n
const { t } = useI18n()

// 捕获特定错误并处理
onErrorCaptured((err, instance, info) => {
  // 检查是否是特定的 includes 错误
  if (err instanceof TypeError && err.message.includes('includes is not a function')) {
    console.warn('Caught includes error in table component, fixing...')
    // 确保 articleList 是数组
    if (!Array.isArray(articleList.value)) {
      articleList.value = []
      return false // 阻止错误继续传播
    }
  }
  // 其他错误正常传播
  return true
})

const router = useRouter()

// 文章统计数据
const statistics = ref({
  totalArticles: 0,
  publishedArticles: 0,
  draftArticles: 0,
  newArticles: 0,
  totalViews: 0,
  todayViews: 0,
  avgViews: 0,
  viewIncrease: 0,
  totalLikes: 0,
  todayLikes: 0,
  likesPerArticle: 0,
  totalComments: 0,
  todayComments: 0,
  commentRate: '0%',
  pendingArticles: 0,
  rejectedArticles: 0
})

// 文章列表数据 - 确保初始化为空数组
const articleList = ref([])

// 监听articleList确保它始终是一个数组
watch(articleList, (newValue) => {
  if (!Array.isArray(newValue)) {
    console.warn('articleList is not an array, resetting to empty array')
    articleList.value = []
  }
})

const total = ref(0)
const loading = ref(false)
const listQuery = reactive({
  page: 1,
  limit: 10,
  keyword: '',
  categoryId: '',
  status: '',
  sortBy: 'is_top',
  sortOrder: 'descending',
  is_banner_top: '',
  is_top: '',
  is_recommend: ''
})

// 分类选项
const categoryOptions = ref([])

// 审核对话框
const approveDialogVisible = ref(false)
const approveFormRef = ref(null)
const approveForm = reactive({
  id: '',
  status: 'published',
  remark: ''
})
const currentArticleId = ref(null)

// 获取文章列表
const fetchArticleList = async () => {
  loading.value = true
  // Make sure articleList is an array even during loading
  if (!Array.isArray(articleList.value)) {
    articleList.value = []
  }

  try {
    // 准备请求参数
    const params = {
      ...listQuery,
      page: Number(listQuery.page),
      limit: Number(listQuery.limit),
    }

    // 转换分类ID参数名和类型
    if (listQuery.categoryId) {
      params.category_id = Number(listQuery.categoryId)
      delete params.categoryId
    }

    const res = await getArticles(params)

    // 添加调试日志，检查API返回的数据结构

    if (res.code === 200) {
      // 确保列表数据是数组并处理每个文章对象
      const list = Array.isArray(res.data?.list) ? res.data.list.map(article => {
        // 确保status字段是字符串类型，用于正确翻译
        let status = article.status
        if (typeof status === 'number') {
          const statusMap = {
            0: 'draft',
            1: 'published',
            2: 'pending',
            3: 'rejected'
          }
          status = statusMap[status] || 'draft'
        }

        return {
          ...article,
          title: article.title || '无标题',
          status: status, // 使用转换后的状态
          tags: Array.isArray(article.tags) ? article.tags : [],
          categories: Array.isArray(article.categories) ? article.categories : [],
          views: Number(article.views) || 0,
          likes: Number(article.likes) || 0,
          favorites: Number(article.favorites) || 0,
          comments: Number(article.comments) || 0,
          is_top: Number(article.is_top) || 0,
          is_recommend: Number(article.is_recommend) || 0,
          is_banner_top: Number(article.is_banner_top) || 0
        }
      }) : []

      const totalCount = Number(res.data?.total || 0)

      // 使用nextTick确保DOM更新完成后再设置新的数组
      await nextTick(() => {
        articleList.value = list
        total.value = totalCount
      })
    } else {
      await nextTick(() => {
        articleList.value = []
        total.value = 0
      })
      ElMessage.error(res.msg || '获取文章列表失败')
    }
  } catch (error) {
    console.error(t('article.errors.fetchFailed'), error)
    await nextTick(() => {
      articleList.value = []
      total.value = 0
    })
    ElMessage.error('获取文章列表失败，请检查网络连接')
  } finally {
    loading.value = false
  }
}

// 获取统计数据
const fetchStatistics = async () => {
  try {
    // 获取博客全局统计数据
    const res = await getBlogStats()

    if (res.code === 200 && res.data) {
      // 计算平均每篇文章的点赞数和评论率
      const likesPerArticle = res.data.totalPosts > 0
        ? (res.data.totalLikes / res.data.totalPosts).toFixed(1)
        : 0;

      const commentRate = res.data.totalPosts > 0
        ? ((res.data.totalComments / res.data.totalPosts) * 100).toFixed(1) + '%'
        : '0%';

      // 更新统计数据
      statistics.value = {
        totalArticles: res.data.totalPosts || 0,
        publishedArticles: res.data.publishedPosts || 0,
        draftArticles: res.data.draftPosts || 0,
        newArticles: res.data.todayPosts || 0,
        totalViews: res.data.totalViews || 0,
        viewIncrease: 0, // 默认为0，可以从其他API获取
        totalLikes: res.data.totalLikes || 0,
        likesPerArticle: likesPerArticle,
        totalComments: res.data.totalComments || 0,
        commentRate: commentRate,
        todayViews: res.data.todayViews || 0,
        todayLikes: res.data.todayLikes || 0,
        todayComments: res.data.todayComments || 0,
        pendingArticles: res.data.pendingPosts || 0,
        rejectedArticles: res.data.rejectedPosts || 0
      }

      // 获取文章浏览量统计数据
      const viewsRes = await getBlogStats({ type: 'views' })
      if (viewsRes.code === 200 && viewsRes.data) {
        statistics.value.avgViews = viewsRes.data.avgViews || 0;
        statistics.value.viewIncrease = calculateGrowth(
          statistics.value.totalViews - statistics.value.todayViews,
          statistics.value.totalViews
        );
      }
    }
  } catch (error) {
    console.error(t('article.errors.statsFailed'), error)
  }
}

// 计算增长率
const calculateGrowth = (oldValue, newValue) => {
  if (oldValue <= 0) return 0;
  return ((newValue - oldValue) / oldValue * 100).toFixed(1);
}

// 获取分类列表
const fetchCategories = async () => {
  try {
    const res = await getCategories()

    if (res.code === 200 && res.data) {
      // 检查返回数据的结构
      if (res.data.list && Array.isArray(res.data.list)) {
        // 将树形结构转换为扁平结构
        categoryOptions.value = flattenCategoryTree(res.data.list)
      } else if (Array.isArray(res.data)) {
        categoryOptions.value = res.data
      } else {
        categoryOptions.value = []
      }
    } else {
      console.error(t('article.errors.categoriesFailed'), res.msg || '请求错误')
      categoryOptions.value = []
    }
  } catch (error) {
    console.error(t('article.errors.categoriesFailed'), error)
    categoryOptions.value = [] // 确保失败时也初始化为空数组
  }
}

// 将树形分类结构转换为扁平结构
const flattenCategoryTree = (categories, prefix = '') => {
  let result = []

  categories.forEach(category => {
    // 添加当前分类
    const currentCategory = { ...category }
    // 如果有前缀，添加到名称前
    if (prefix) {
      currentCategory.name = `${prefix}${currentCategory.name}`
    }

    // 删除children属性避免干扰
    if (currentCategory.children) {
      delete currentCategory.children
    }

    result.push(currentCategory)

    // 如果有子分类，递归处理并添加到结果中
    if (category.children && category.children.length > 0) {
      const childPrefix = prefix + '└─ '
      const childCategories = flattenCategoryTree(category.children, childPrefix)
      result = result.concat(childCategories)
    }
  })

  return result
}

// 处理筛选
const handleFilter = () => {
  listQuery.page = 1
  fetchArticleList()
}

// 重置筛选条件
const resetQuery = () => {
  listQuery.keyword = ''
  listQuery.categoryId = ''
  listQuery.status = ''
  listQuery.page = 1
  fetchArticleList()
}

// 处理页码变化
const handleCurrentChange = (val) => {
  listQuery.page = val
  fetchArticleList()
}

// 处理每页条数变化
const handleSizeChange = (val) => {
  listQuery.limit = val
  listQuery.page = 1
  fetchArticleList()
}

// 处理排序变化
const handleSortChange = (column) => {
  if (column.prop) {
    listQuery.sortBy = column.prop
    listQuery.sortOrder = column.order
    fetchArticleList()
  }
}

// 处理创建文章
const handleCreateArticle = () => {
  router.push('/admin/article/create')
}

// 处理编辑文章
const handleEdit = (row) => {
  router.push(`/admin/article/edit/${row.id}`)
}

// 处理删除文章
const handleDelete = (row) => {
  ElMessageBox.confirm(
    t('article.messages.deleteConfirm'),
    t('common.warning'),
    {
      confirmButtonText: t('common.confirm'),
      cancelButtonText: t('common.cancel'),
      type: 'warning'
    }
  ).then(async () => {
    try {
      const response = await deleteArticle({id: row.id})
      if (response.code === 200) {
        ElMessage.success(t('article.messages.deleteSuccess'))
        await fetchArticleList()
      } else {
        ElMessage.error(response.msg || t('article.messages.deleteError'))
      }
    } catch (error) {
      console.error(t('article.errors.deleteFailed'), error)
      ElMessage.error(t('article.messages.deleteError'))
    }
  }).catch(() => {
    // 用户取消删除，不做处理
  })
}

// 处理置顶状态变更
const handleTopChange = async (row) => {
  try {
    const response = await toggleTopArticle({id: row.id,is_top:row.is_top})
    if (response.code === 200) {
      ElMessage.success(t('article.messages.topSuccess'))
      await fetchArticleList()
    } else {
      ElMessage.error(response.msg || t('article.messages.topError'))
    }
  } catch (error) {
    console.error(t('article.errors.topChangeFailed'), error)
    ElMessage.error(t('article.messages.topError'))
  }
}

// 处理推荐状态变更
const handleRecommendChange = async (row) => {
  try {
    const response = await toggleRecommendArticle({id: row.id,is_recommend:row.is_recommend})
    if (response.code === 200) {
      ElMessage.success(t('article.messages.recommendSuccess'))
      await fetchArticleList()
    } else {
      ElMessage.error(response.msg || t('article.messages.recommendError'))
    }
  } catch (error) {
    console.error(t('article.errors.recommendChangeFailed'), error)
    ElMessage.error(t('article.messages.recommendError'))
  }
}

// 处理首页banner展示状态变更
const handleBannerTopChange = async (row) => {
  try {
    const response = await toggleBannerTopArticle({
      id: row.id,
      is_banner_top: row.is_banner_top
    })
    if (response.code === 200) {
      ElMessage.success(t('article.messages.bannerTopSuccess'))
      await fetchArticleList()
    } else {
      ElMessage.error(response.msg || t('article.messages.bannerTopError'))
    }
  } catch (error) {
    console.error(t('article.errors.bannerTopChangeFailed'), error)
    ElMessage.error(t('article.messages.bannerTopError'))
  }
}

// 处理审核
const handleApprove = (row) => {
  currentArticleId.value = row.id
  approveDialogVisible.value = true
  approveForm.status = 'published' // 默认通过
  approveForm.remark = ''
}

// 提交审核结果
const submitApprove = async () => {
  if (!currentArticleId.value) {
    ElMessage.error(t('article.messages.approveError'))
    return
  }

  try {
    const response = await updateArticle({
      id: currentArticleId.value,
      status: approveForm.status,
      remark: approveForm.status === 'rejected' ? approveForm.remark : ''
    })

    if (response.code === 200) {
      ElMessage.success(t('article.messages.approveSuccess'))
      approveDialogVisible.value = false

      // 刷新列表
      await fetchArticleList()
    } else {
      ElMessage.error(response.msg || t('article.messages.approveError'))
    }
  } catch (error) {
    console.error(t('article.errors.auditFailed'), error)
    ElMessage.error(t('article.messages.approveError'))
  }
}

// 获取状态文本
const getStatusText = (status) => {
  // 将数字状态转换为字符串状态
  if (typeof status === 'number') {
    const statusMap = {
      0: 'draft',
      1: 'published',
      2: 'pending',
      3: 'rejected'
    }
    status = statusMap[status] || 'draft'
  }
  return t(`article.status.${status}`)
}

// 获取状态类型
const getStatusType = (status) => {
  // 将数字状态转换为字符串状态
  if (typeof status === 'number') {
    const statusMap = {
      0: 'draft',
      1: 'published',
      2: 'pending',
      3: 'rejected'
    }
    status = statusMap[status] || 'draft'
  }

  const typeMap = {
    draft: 'info',
    published: 'success',
    pending: 'warning',
    rejected: 'danger'
  }
  return typeMap[status] || 'info'
}

// 初始化数据
onMounted(async () => {
  // 首先获取分类，确保在显示文章列表前已加载分类数据
  await fetchCategories()

  // 然后获取文章列表和统计数据
  fetchArticleList()
  fetchStatistics()
})
</script>

<style scoped>
.article-list-container {
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

.article-table-card {
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

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.article-title-cell {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.article-title {
  font-weight: 500;
}

.article-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.article-tag {
  font-size: 11px;
  padding: 0 5px;
  height: 20px;
  line-height: 20px;
}

.categories-more {
  margin-left: 5px;
  font-size: 12px;
  color: #909399;
}

.views-count,
.like-count,
.favorite-count,
.comment-count {
  display: flex;
  align-items: center;
  gap: 5px;
}

:deep(.el-table .cell) {
  padding: 8px;
}

:deep(.el-table th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: 600;
}

:deep(.el-card__header) {
  padding: 15px 20px;
}

:deep(.el-card__body) {
  padding: 15px 20px;
}

:deep(.el-pagination) {
  justify-content: flex-end;
}

:deep(.el-select-dropdown__wrap) {
  max-height: 280px;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  .statistics-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
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
  .filter-select {
    width: 100%;
  }
}

.category-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.category-tag {
  font-size: 11px;
  padding: 0 5px;
  height: 20px;
  line-height: 20px;
}

.categories-more {
  margin-left: 5px;
  font-size: 12px;
  color: #909399;
}

.status-tag {
  min-width: 60px;
  text-align: center;
  padding: 0 8px;
}
</style>