<!-- 分类管理 -->
<template>
  <div class="category-management-container">
    <el-card shadow="hover" class="category-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('adminCat.title') }}</span>
          <div class="header-actions">
            <div class="search-container">
              <el-input
                v-model="searchQuery.keyword"
                :placeholder="$t('adminCat.filters.keyword')"
                clearable
                class="search-input"
                @keyup.enter="handleSearch"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
              <el-select
                v-model="searchQuery.status"
                :placeholder="$t('adminCat.filters.statusFilter')"
                clearable
                class="status-select"
              >
                <el-option :label="$t('adminCat.filters.all')" value="" />
                <el-option :label="$t('adminCat.filters.active')" :value="1" />
                <el-option :label="$t('adminCat.filters.inactive')" :value="0" />
              </el-select>
              <el-button type="primary" @click="handleSearch">{{ $t('adminCat.button.search') }}</el-button>
              <el-button @click="resetSearch">{{ $t('adminCat.button.reset') }}</el-button>
            </div>
            <el-button type="primary" @click="handleCreateCategory">
              <el-icon><Plus /></el-icon>{{ $t('adminCat.add') }}
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="categoryList"
        row-key="id"
        border
        default-expand-all
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column prop="id" :label="$t('adminCat.table.id')" width="80" />
        <el-table-column prop="name" :label="$t('adminCat.table.name')" min-width="150" />
        <el-table-column prop="description" :label="$t('adminCat.table.description')" min-width="200" show-overflow-tooltip />
        <el-table-column prop="path" :label="$t('adminCat.table.path')" min-width="150" show-overflow-tooltip />
        <el-table-column prop="level" :label="$t('adminCat.table.level')" width="80" />
        <el-table-column prop="sort" :label="$t('adminCat.table.sort')" width="80" />
        <el-table-column prop="status" :label="$t('adminCat.table.status')" width="100">
          <template #default="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" :label="$t('adminCat.table.createTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="update_time" :label="$t('adminCat.table.updateTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.update_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('adminCat.table.operation')" width="180" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="primary"
              @click="handleEditCategory(scope.row)"
            >
              {{ $t('adminCat.button.edit') }}
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDeleteCategory(scope.row)"
            >
              {{ $t('adminCat.button.delete') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 分类编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'create' ? $t('adminCat.dialog.addCategory') : $t('adminCat.dialog.editCategory')"
      width="800px"
      top="15vh"
      class="category-dialog"
    >
      <el-form
        ref="categoryFormRef"
        :model="categoryForm"
        :rules="categoryRules"
        label-width="120px"
        class="category-form"
      >
        <el-divider content-position="left">{{ $t('adminCat.basicInfo') }}</el-divider>
        <el-form-item :label="$t('adminCat.table.name')" prop="name">
          <el-input v-model="categoryForm.name" :placeholder="$t('adminCat.placeholder.name')" />
        </el-form-item>
        <el-form-item :label="$t('adminCat.parentCategory')" prop="parent_id">
          <el-cascader
            v-model="categoryForm.parent_id"
            :options="categoryOptions"
            :props="{
              checkStrictly: true,
              label: 'name',
              value: 'id',
              emitPath: false,
              expandTrigger: 'hover'
            }"
            clearable
            :placeholder="$t('adminCat.placeholder.parentCategory')"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item :label="$t('adminCat.table.description')" prop="description">
          <el-input
            v-model="categoryForm.description"
            type="textarea"
            :rows="4"
            :placeholder="$t('adminCat.placeholder.description')"
          />
        </el-form-item>

        <el-divider content-position="left">{{ $t('adminCat.settings') }}</el-divider>
        <el-form-item :label="$t('adminCat.table.sort')" prop="sort">
          <el-input-number v-model="categoryForm.sort" :min="0" :max="9999" style="width: 100%" />
        </el-form-item>
        <el-form-item :label="$t('adminCat.table.status')" prop="status">
          <el-switch
            v-model="categoryForm.status"
            :active-value="1"
            :inactive-value="0"
            :active-text="$t('adminCat.status.enabled')"
            :inactive-text="$t('adminCat.status.disabled')"
            class="status-switch"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">{{ $t('adminCat.button.cancel') }}</el-button>
          <el-button type="primary" @click="submitCategoryForm" :loading="submitting">{{ $t('adminCat.button.confirm') }}</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import { useI18n } from 'vue-i18n'
import {
  getCategories,
  createCategory,
  updateCategory,
  deleteCategory,
  updateCatStatus
} from '@/api/admin/article'

export default {
  name: 'CategoryManagement',
  components: {
    Plus,
    Search
  },
  setup() {
    const { t } = useI18n()

    // 分类数据
    const categoryList = ref([])
    const categoryOptions = ref([])
    const loading = ref(false)

    // 搜索相关
    const searchQuery = reactive({
      keyword: '',
      status: ''
    })

    // 对话框相关
    const dialogVisible = ref(false)
    const dialogType = ref('create') // 'create' 或 'edit'
    const categoryFormRef = ref(null)
    const categoryForm = reactive({
      id: undefined,
      name: '',
      parent_id: null,
      description: '',
      sort: 0,
      status: 1
    })
    const submitting = ref(false)

    // 表单验证规则
    const categoryRules = {
      name: [
        { required: true, message: t('adminCat.validation.name'), trigger: 'blur' },
        { min: 2, max: 50, message: t('adminCat.validation.nameLength'), trigger: 'blur' }
      ],
      description: [
        { max: 200, message: t('adminCat.validation.description'), trigger: 'blur' }
      ],
      sort: [
        { required: true, message: t('adminCat.validation.sort'), trigger: 'blur' }
      ]
    }

    // 获取分类列表
    const fetchCategoryList = async () => {
      loading.value = true
      try {
        // 准备搜索参数
        const params = {}
        if (searchQuery.keyword) {
          params.name = searchQuery.keyword
        }
        if (searchQuery.status !== '') {
          params.status = searchQuery.status
        }

        const res = await getCategories(params)

        if (res.code === 200) {
          // Check the response data format and extract the list
          if (res.data && res.data.list && Array.isArray(res.data.list)) {
            // API returned { list, total, page, limit } structure
            categoryList.value = res.data.list
          } else if (Array.isArray(res.data)) {
            // API directly returned an array
            categoryList.value = res.data
          } else {
            // Fallback to empty array if data structure is unexpected
            console.error('Unexpected category data format:', res.data)
            categoryList.value = []
          }

          // 处理分类选项，供级联选择器使用
          processCategoryOptions()
        } else {
          categoryList.value = []
          ElMessage.error(res.msg || t('adminCat.message.fetchError'))
        }
      } catch (error) {
        console.error('获取分类列表失败:', error)
        categoryList.value = []
        ElMessage.error(t('adminCat.message.networkError'))
      } finally {
        loading.value = false
      }
    }

    // 处理分类选项，供级联选择器使用
    const processCategoryOptions = () => {
      // 深拷贝以避免修改原始数据
      const options = JSON.parse(JSON.stringify(categoryList.value))

      // 过滤掉当前正在编辑的分类及其子分类，避免循环选择
      if (dialogType.value === 'edit' && categoryForm.id) {
        const filtered = filterCurrentCategory(options, categoryForm.id)
        categoryOptions.value = filtered
      } else {
        categoryOptions.value = options
      }
    }

    // 过滤当前分类及其子分类
    const filterCurrentCategory = (categories, currentId) => {
      return categories.filter(category => {
        if (category.id === currentId) {
          return false
        }

        if (category.children && category.children.length) {
          category.children = filterCurrentCategory(category.children, currentId)
        }

        return true
      })
    }

    // 添加分类
    const handleCreateCategory = () => {
      dialogType.value = 'create'
      resetCategoryForm()
      dialogVisible.value = true
      processCategoryOptions()
    }

    // 编辑分类
    const handleEditCategory = (row) => {
      dialogType.value = 'edit'
      Object.assign(categoryForm, {
        id: row.id,
        name: row.name,
        parent_id: row.parent_id,
        description: row.description,
        sort: row.sort,
        status: row.status
      })
      dialogVisible.value = true
      processCategoryOptions()
    }

    // 删除分类
    const handleDeleteCategory = (row) => {
      const hasChildren = row.children && row.children.length
      const warningMsg = hasChildren
        ? t('adminCat.dialog.confirmDeleteWithChildren', {
            name: row.name,
            children: t('adminCat.confirmDeleteChildren')
          })
        : t('adminCat.dialog.confirmDelete')

      ElMessageBox.confirm(
        warningMsg,
        t('adminCat.warning'),
        {
          confirmButtonText: t('adminCat.button.confirm'),
          cancelButtonText: t('adminCat.button.cancel'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteCategory({ id: row.id })

          if (res.code === 200) {
            ElMessage.success(t('adminCat.message.deleteSuccess'))
            fetchCategoryList()
          } else {
            ElMessage.error(res.msg || t('adminCat.message.deleteError'))
          }
        } catch (error) {
          console.error('删除分类失败:', error)
          ElMessage.error(t('adminCat.message.deleteError'))
        }
      }).catch(() => {})
    }

    // 更改分类状态
    const handleStatusChange = async (row) => {
      try {
        const res = await updateCatStatus({
          id: row.id,
          status: row.status
        })

        if (res.code === 200) {
          ElMessage.success(row.status === 1
            ? t('adminCat.message.statusEnabled')
            : t('adminCat.message.statusDisabled'))
        } else {
          // 操作失败，恢复状态
          row.status = row.status === 1 ? 0 : 1
          ElMessage.error(res.msg || t('adminCat.message.statusUpdateError'))
        }
      } catch (error) {
        // 操作失败，恢复状态
        row.status = row.status === 1 ? 0 : 1
        console.error('更新分类状态失败:', error)
        ElMessage.error(t('adminCat.message.statusUpdateError'))
      }
    }

    // 重置分类表单
    const resetCategoryForm = () => {
      categoryForm.id = undefined
      categoryForm.name = ''
      categoryForm.parent_id = null
      categoryForm.description = ''
      categoryForm.sort = 0
      categoryForm.status = 1

      // 如果表单ref已经存在，则调用重置方法
      if (categoryFormRef.value) {
        categoryFormRef.value.resetFields()
      }
    }

    // 提交分类表单
    const submitCategoryForm = async () => {
      if (!categoryFormRef.value) return

      await categoryFormRef.value.validate(async (valid) => {
        if (!valid) return

        submitting.value = true

        try {
          const params = { ...categoryForm }

          // 根据操作类型选择API
          const apiMethod = dialogType.value === 'create' ? createCategory : updateCategory
          const res = await apiMethod(params)

          if (res.code === 200) {
            ElMessage.success(dialogType.value === 'create'
              ? t('adminCat.message.addSuccess')
              : t('adminCat.message.editSuccess'))
            dialogVisible.value = false
            fetchCategoryList()
          } else {
            ElMessage.error(res.msg || (dialogType.value === 'create'
              ? t('adminCat.message.addError')
              : t('adminCat.message.editError')))
          }
        } catch (error) {
          console.error(dialogType.value === 'create' ? '添加分类失败:' : '更新分类失败:', error)
          ElMessage.error(dialogType.value === 'create'
            ? t('adminCat.message.addError')
            : t('adminCat.message.editError'))
        } finally {
          submitting.value = false
        }
      })
    }

    // 处理搜索
    const handleSearch = () => {
      fetchCategoryList()
    }

    // 重置搜索
    const resetSearch = () => {
      searchQuery.keyword = ''
      searchQuery.status = ''
      fetchCategoryList()
    }

    // 初始化数据
    onMounted(() => {
      fetchCategoryList()
    })

    return {
      categoryList,
      categoryOptions,
      loading,
      searchQuery,
      dialogVisible,
      dialogType,
      categoryFormRef,
      categoryForm,
      categoryRules,
      submitting,
      formatDate,
      handleCreateCategory,
      handleEditCategory,
      handleDeleteCategory,
      handleStatusChange,
      handleSearch,
      resetSearch,
      submitCategoryForm
    }
  }
}
</script>

<style scoped>
.category-management-container {
  padding: 20px;
}

.category-table-card {
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
  gap: 16px;
}

.search-container {
  display: flex;
  gap: 10px;
}

.search-input {
  width: 250px;
}

.status-select {
  width: 140px;
}

.category-dialog .el-form-item {
  margin-bottom: 22px;
}

.status-switch {
  margin-top: 8px;
}
</style>