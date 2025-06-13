<!-- 标签管理 -->
<template>
  <div class="tag-management-container">
    <el-card shadow="hover" class="tag-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ t('article.tag.title') }}</span>
          <div class="header-actions">
            <el-input
              v-model="searchQuery"
              :placeholder="t('article.tag.searchPlaceholder')"
              clearable
              class="search-input"
              @keyup.enter="handleSearch"
            >
              <template #prefix>
                <el-icon><Search /></el-icon>
              </template>
            </el-input>
            <el-button type="primary" @click="handleCreateTag">
              <el-icon><Plus /></el-icon>{{ t('article.tag.add') }}
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="filteredTagList"
        border
        style="width: 100%"
      >
        <el-table-column prop="id" :label="t('article.table.id')" width="80" />
        <el-table-column prop="name" :label="t('article.tag.name')" min-width="150">
          <template #default="scope">
            <el-tag effect="plain">{{ scope.row.name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="description" :label="t('article.tag.description')" min-width="200" show-overflow-tooltip />
        <el-table-column prop="sort" :label="t('article.tag.sort')" width="100" sortable />
        <el-table-column prop="status" :label="t('article.table.status')" width="100">
          <template #default="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" :label="t('article.tag.createTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="update_time" :label="t('article.tag.updateTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.update_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="t('article.table.actions')" width="180" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="primary"
              @click="handleEditTag(scope.row)"
            >
              {{ t('article.actions.edit') }}
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDeleteTag(scope.row)"
            >
              {{ t('article.actions.delete') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 标签编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'create' ? t('article.tag.addDialog') : t('article.tag.editDialog')"
      width="700px"
      top="15vh"
      class="tag-dialog"
    >
      <el-form
        ref="tagFormRef"
        :model="tagForm"
        :rules="tagRules"
        label-width="120px"
        class="tag-form"
      >
        <el-divider content-position="left">{{ t('article.tag.basicInfo') }}</el-divider>
        <el-form-item :label="t('article.tag.name')" prop="name">
          <el-input v-model="tagForm.name" :placeholder="t('article.tag.namePlaceholder')" />
        </el-form-item>
        <el-form-item :label="t('article.tag.description')" prop="description">
          <el-input 
            v-model="tagForm.description" 
            type="textarea" 
            :rows="4" 
            :placeholder="t('article.tag.descriptionPlaceholder')" 
          />
        </el-form-item>
        
        <el-divider content-position="left">{{ t('article.tag.settings') }}</el-divider>
        <el-form-item :label="t('article.tag.sort')" prop="sort">
          <el-input-number v-model="tagForm.sort" :min="0" :max="9999" style="width: 100%" />
        </el-form-item>
        <el-form-item :label="t('article.table.status')" prop="status">
          <el-switch
            v-model="tagForm.status"
            :active-value="1"
            :inactive-value="0"
            :active-text="t('article.tag.enabled')"
            :inactive-text="t('article.tag.disabled')"
            class="status-switch"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">{{ t('common.cancel') }}</el-button>
          <el-button type="primary" @click="submitTagForm" :loading="submitting">{{ t('common.confirm') }}</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import { 
  getTags,
  createTag,
  updateTag,
  deleteTag,
  updateTagStatus
} from '@/api/admin/article'

export default {
  name: 'TagManagement',
  components: {
    Plus,
    Search
  },
  setup() {
    const { t } = useI18n()
    
    // 标签数据
    const tagList = ref([])
    const loading = ref(false)
    const searchQuery = ref('')

    // 过滤后的标签列表
    const filteredTagList = computed(() => {
      if (!searchQuery.value) return tagList.value
      
      return tagList.value.filter(tag => 
        tag.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (tag.description && tag.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
      )
    })

    // 对话框相关
    const dialogVisible = ref(false)
    const dialogType = ref('create') // 'create' 或 'edit'
    const tagFormRef = ref(null)
    const tagForm = reactive({
      id: undefined,
      name: '',
      description: '',
      sort: 0,
      status: 1
    })
    const submitting = ref(false)

    // 表单验证规则
    const tagRules = {
      name: [
        { required: true, message: t('article.tag.validation.nameRequired'), trigger: 'blur' },
        { min: 1, max: 30, message: t('article.tag.validation.nameLength'), trigger: 'blur' }
      ],
      description: [
        { max: 100, message: t('article.tag.validation.descriptionLength'), trigger: 'blur' }
      ],
      sort: [
        { required: true, message: t('article.tag.validation.sortRequired'), trigger: 'blur' }
      ]
    }

    // 获取标签列表
    const fetchTagList = async () => {
      loading.value = true
      try {
        const res = await getTags()
        
        if (res.code === 200) {
          // Check the response data format and extract the list
          if (res.data && res.data.list && Array.isArray(res.data.list)) {
            // API returned { list, total, page, limit } structure
            tagList.value = res.data.list
          } else if (Array.isArray(res.data)) {
            // API directly returned an array
            tagList.value = res.data
          } else {
            // Fallback to empty array if data structure is unexpected
            console.error(t('article.tag.errors.unexpectedFormat'), res.data)
            tagList.value = []
          }
        } else {
          tagList.value = []
          ElMessage.error(res.msg || t('article.tag.errors.fetchFailed'))
        }
      } catch (error) {
        console.error(t('article.tag.errors.fetchFailed'), error)
        tagList.value = []
        ElMessage.error(t('article.tag.errors.networkError'))
      } finally {
        loading.value = false
      }
    }

    // 搜索标签
    const handleSearch = () => {
      // 使用计算属性自动过滤，无需额外操作
    }

    // 添加标签
    const handleCreateTag = () => {
      dialogType.value = 'create'
      resetTagForm()
      dialogVisible.value = true
    }

    // 编辑标签
    const handleEditTag = (row) => {
      dialogType.value = 'edit'
      Object.assign(tagForm, {
        id: row.id,
        name: row.name,
        description: row.description,
        sort: row.sort,
        status: row.status
      })
      dialogVisible.value = true
    }

    // 删除标签
    const handleDeleteTag = (row) => {
      ElMessageBox.confirm(
        t('article.tag.deleteConfirm', { name: row.name }),
        t('common.warning'),
        {
          confirmButtonText: t('common.confirm'),
          cancelButtonText: t('common.cancel'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteTag({ id: row.id })
          
          if (res.code === 200) {
            ElMessage.success(t('article.tag.deleteSuccess'))
            fetchTagList()
          } else {
            ElMessage.error(res.msg || t('article.tag.deleteFailed'))
          }
        } catch (error) {
          console.error(t('article.tag.deleteFailed'), error)
          ElMessage.error(t('article.tag.deleteFailed'))
        }
      }).catch(() => {})
    }

    // 更改标签状态
    const handleStatusChange = async (row) => {
      try {
        const res = await updateTagStatus({
          id: row.id,
          status: row.status
        })
        
        if (res.code === 200) {
          ElMessage.success(row.status === 1 ? t('article.tag.enableSuccess') : t('article.tag.disableSuccess'))
        } else {
          // 操作失败，恢复状态
          row.status = row.status === 1 ? 0 : 1
          ElMessage.error(res.msg || t('article.tag.statusUpdateFailed'))
        }
      } catch (error) {
        // 操作失败，恢复状态
        row.status = row.status === 1 ? 0 : 1
        console.error(t('article.tag.statusUpdateFailed'), error)
        ElMessage.error(t('article.tag.statusUpdateFailed'))
      }
    }

    // 重置标签表单
    const resetTagForm = () => {
      tagForm.id = undefined
      tagForm.name = ''
      tagForm.description = ''
      tagForm.sort = 0
      tagForm.status = 1
      
      // 如果表单ref已经存在，则调用重置方法
      if (tagFormRef.value) {
        tagFormRef.value.resetFields()
      }
    }

    // 提交标签表单
    const submitTagForm = async () => {
      if (!tagFormRef.value) return
      
      await tagFormRef.value.validate(async (valid) => {
        if (!valid) return
        
        submitting.value = true
        
        try {
          const params = { ...tagForm }
          
          // 根据操作类型选择API
          const apiMethod = dialogType.value === 'create' ? createTag : updateTag
          const res = await apiMethod(params)
          
          if (res.code === 200) {
            ElMessage.success(dialogType.value === 'create' ? t('article.tag.createSuccess') : t('article.tag.updateSuccess'))
            dialogVisible.value = false
            fetchTagList()
          } else {
            ElMessage.error(res.msg || (dialogType.value === 'create' ? t('article.tag.createFailed') : t('article.tag.updateFailed')))
          }
        } catch (error) {
          console.error(dialogType.value === 'create' ? t('article.tag.createFailed') : t('article.tag.updateFailed'), error)
          ElMessage.error(dialogType.value === 'create' ? t('article.tag.createFailed') : t('article.tag.updateFailed'))
        } finally {
          submitting.value = false
        }
      })
    }

    // 初始化数据
    onMounted(() => {
      fetchTagList()
    })

    return {
      t,
      tagList,
      filteredTagList,
      loading,
      searchQuery,
      dialogVisible,
      dialogType,
      tagFormRef,
      tagForm,
      tagRules,
      submitting,
      formatDate,
      handleSearch,
      handleCreateTag,
      handleEditTag,
      handleDeleteTag,
      handleStatusChange,
      submitTagForm
    }
  }
}
</script>

<style scoped>
.tag-management-container {
  padding: 20px;
}

.tag-table-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  width: 200px;
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

/* 标签编辑对话框样式 */
:deep(.tag-dialog .el-dialog__body) {
  padding: 30px 30px 20px;
}

:deep(.tag-dialog .el-dialog__header) {
  padding: 20px 30px;
  margin-right: 0;
  border-bottom: 1px solid #ebeef5;
}

:deep(.tag-dialog .el-dialog__footer) {
  padding: 15px 30px 20px;
  border-top: 1px solid #ebeef5;
}

.tag-form .el-form-item {
  margin-bottom: 28px;
}

.tag-form .el-divider {
  margin: 5px 0 25px;
}

.tag-form .el-input__inner,
.tag-form .el-textarea__inner {
  padding: 8px 12px;
}

.status-switch {
  display: inline-flex;
  align-items: center;
}

/* 改进状态开关标签对齐 */
:deep(.el-switch) {
  margin-right: 10px;
}

:deep(.el-switch__label) {
  position: relative;
  top: 1px;
}

:deep(.el-switch__label--left) {
  margin-right: 10px;
}

:deep(.el-switch__label--right) {
  margin-left: 10px;
}

:deep(.el-switch__label span) {
  font-size: 14px;
}

:deep(.el-switch__label.is-active) {
  color: var(--el-color-primary);
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  .tag-management-container {
    padding: 10px;
  }
  
  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input {
    width: 100%;
    margin-bottom: 10px;
  }
  
  :deep(.tag-dialog) {
    width: 95% !important;
    margin: 0 auto;
  }
  
  :deep(.tag-dialog .el-dialog__body) {
    padding: 20px 15px;
  }
  
  :deep(.tag-dialog .el-dialog__header) {
    padding: 15px 15px;
  }
  
  :deep(.tag-dialog .el-dialog__footer) {
    padding: 10px 15px 15px;
  }
}
</style> 