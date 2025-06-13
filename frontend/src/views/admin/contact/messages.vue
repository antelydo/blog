<!-- 留言管理 -->
<template>
  <div class="contact-messages-container">
    <el-card class="page-header">
      <div class="header-content">
        <h2 class="page-title">{{ t('contact.contact_management') }}</h2>
        <div class="page-tools">
          <el-select v-model="listQuery.status" :placeholder="t('contact.all')" clearable @change="handleFilter">
            <el-option v-for="item in statusOptions" :key="item.value" :label="t(item.label)" :value="item.value" />
          </el-select>
          <el-input
            v-model="listQuery.search"
            :placeholder="t('contact.search_placeholder')"
            clearable
            @keyup.enter="handleFilter"
            @clear="handleFilter"
            class="search-input"
          >
            <template #append>
              <el-button @click="handleFilter">
                <el-icon><Search /></el-icon>
              </el-button>
            </template>
          </el-input>
        </div>
      </div>
    </el-card>

    <el-card class="messages-list">
      <div class="table-operations">
        <el-button type="danger" :disabled="selectedMessages.length === 0" @click="handleBatchDelete">
          {{ t('contact.batchDelete') }}
        </el-button>
        <el-button type="primary" @click="handleRefresh">
          <el-icon><Refresh /></el-icon>
          {{ t('contact.refresh') }}
        </el-button>
      </div>

      <el-table
        v-loading="listLoading"
        :data="messagesList"
        element-loading-text="Loading..."
        border
        fit
        highlight-current-row
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column type="index" width="50" label="#" />
        <el-table-column :label="t('contact.sender_name')" prop="name" min-width="120" />
        <el-table-column :label="t('contact.sender_email')" prop="email" min-width="180" show-overflow-tooltip />
        <el-table-column :label="t('contact.subject')" prop="subject" min-width="200" show-overflow-tooltip />
        <el-table-column :label="t('contact.status')" prop="status" width="120">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="t('contact.sent_time')" prop="create_time" width="180">
          <template #default="{ row }">
            {{ formatDate(row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="t('contact.actions')" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleView(row)">
              <el-icon><View /></el-icon>
              {{ t('contact.view') }}
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              <el-icon><Delete /></el-icon>
              {{ t('contact.delete') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="listQuery.page"
          v-model:page-size="listQuery.limit"
          :page-sizes="[10, 20, 30, 50]"
          background
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 消息详情弹窗 -->
    <el-dialog
      v-model="messageDetailVisible"
      :title="t('contact.message_details')"
      width="60%"
      destroy-on-close
    >
      <div v-if="currentMessage" class="message-detail">
        <div class="message-info">
          <div class="info-item">
            <span class="label">{{ t('contact.sender_name') }}:</span>
            <span class="value">{{ currentMessage.name }}</span>
          </div>
          <div class="info-item">
            <span class="label">{{ t('contact.sender_email') }}:</span>
            <span class="value">{{ currentMessage.email }}</span>
          </div>
          <div class="info-item">
            <span class="label">{{ t('contact.subject') }}:</span>
            <span class="value">{{ currentMessage.subject }}</span>
          </div>
          <div class="info-item">
            <span class="label">{{ t('contact.sent_time') }}:</span>
            <span class="value">{{ formatDate(currentMessage.create_time) }}</span>
          </div>
          <div class="info-item">
            <span class="label">{{ t('contact.ip_address') }}:</span>
            <span class="value">{{ currentMessage.ip_address }}</span>
          </div>
        </div>

        <div class="message-content">
          <h3>{{ t('contact.content') }}</h3>
          <div class="content-box">{{ currentMessage.message }}</div>
        </div>

        <div v-if="currentMessage.status === 2 && currentMessage.reply" class="reply-content">
          <h3>{{ t('contact.reply_content') }}</h3>
          <div class="content-box">{{ currentMessage.reply }}</div>
          <div class="reply-info">
            <span>{{ t('contact.replied_by') }}: {{ currentMessage.reply_admin || 'Admin' }}</span>
            <span>{{ t('contact.replied_time') }}: {{ formatDate(currentMessage.replied_time) }}</span>
          </div>
        </div>

        <div class="action-section">
          <el-divider />
          
          <div class="status-actions">
            <el-button 
              v-if="currentMessage.status === 0" 
              type="primary" 
              @click="handleMarkAsProcessed(currentMessage)"
            >
              {{ t('contact.mark_as_processed') }}
            </el-button>
            
            <el-button 
              v-if="currentMessage.status !== 2" 
              type="success" 
              @click="showReplyForm = true"
            >
              {{ t('contact.reply') }}
            </el-button>
          </div>
          
          <div v-if="showReplyForm" class="reply-form">
            <h3>{{ t('contact.reply_message') }}</h3>
            <el-form>
              <el-form-item>
                <el-input
                  v-model="replyContent"
                  type="textarea"
                  :rows="5"
                  :placeholder="t('contact.reply_content')"
                />
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="handleSendReply">{{ t('contact.send') }}</el-button>
                <el-button @click="showReplyForm = false">{{ t('contact.cancel') }}</el-button>
              </el-form-item>
            </el-form>
          </div>
        </div>
      </div>
    </el-dialog>

    <!-- 确认删除弹窗 -->
    <el-dialog
      v-model="deleteConfirmVisible"
      :title="t('contact.confirmTitle')"
      width="30%"
    >
      <div class="confirm-content">
        <p>{{ t('contact.confirm_delete') }}</p>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="deleteConfirmVisible = false">{{ t('contact.cancel') }}</el-button>
          <el-button type="danger" @click="confirmDelete">{{ t('contact.confirm') }}</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, View, Delete, Refresh } from '@element-plus/icons-vue'
import contactApi from '@/api/admin/contact'

const { t } = useI18n()

// 分页和查询参数
const listQuery = reactive({
  page: 1,
  limit: 10,
  status: '',
  search: ''
})

// 数据列表
const messagesList = ref([])
const total = ref(0)
const listLoading = ref(false)
const selectedMessages = ref([])

// 消息详情和回复表单
const messageDetailVisible = ref(false)
const currentMessage = ref(null)
const showReplyForm = ref(false)
const replyContent = ref('')

// 删除确认
const deleteConfirmVisible = ref(false)
const messageToDelete = ref(null)

// 状态选项
const statusOptions = [
  { value: '', label: 'contact.all' },
  { value: '0', label: 'contact.status_0' },
  { value: '1', label: 'contact.status_1' },
  { value: '2', label: 'contact.status_2' }
]

// 获取消息列表
const getMessages = async () => {
  listLoading.value = true
  try {
    const response = await contactApi.getContactList({
      page: listQuery.page,
      limit: listQuery.limit,
      status: listQuery.status,
      keyword: listQuery.search
    })
    
    if (response && response.code === 200 && response.data) {
      messagesList.value = response.data.list || []
      total.value = response.data.total || 0
    } else {
      ElMessage.error(t('contact.fetchFailed'))
    }
  } catch (error) {
    console.error('获取留言列表失败:', error)
    ElMessage.error(t('contact.fetchFailed'))
  } finally {
    listLoading.value = false
  }
}

// 获取状态类型
const getStatusType = (status) => {
  const statusMap = {
    0: 'danger',  // 未处理
    1: 'warning', // 已处理
    2: 'success'  // 已回复
  }
  return statusMap[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  return t(`contact.status_${status}`)
}

// 格式化日期
const formatDate = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString()
}

// 查看消息详情
const handleView = async (row) => {
  try {
    const response = await contactApi.getMessage({ id: row.id })
    if (response && response.code === 200 && response.data) {
      currentMessage.value = response.data
      messageDetailVisible.value = true
      showReplyForm.value = false
      replyContent.value = ''
    } else {
      ElMessage.error(t('contact.message_not_found'))
    }
  } catch (error) {
    console.error('获取消息详情失败:', error)
    ElMessage.error(t('contact.fetchFailed'))
  }
}

// 标记为已处理
const handleMarkAsProcessed = async (message) => {
  try {
    const response = await contactApi.updateStatus({
      id: message.id,
      status: 1
    })
    
    if (response && response.code === 200) {
      ElMessage.success(t('contact.update_success'))
      currentMessage.value.status = 1
      getMessages() // 刷新列表
    } else {
      ElMessage.error(t('contact.update_failed'))
    }
  } catch (error) {
    console.error('更新消息状态失败:', error)
    ElMessage.error(t('contact.update_failed'))
  }
}

// 发送回复
const handleSendReply = async () => {
  if (!replyContent.value.trim()) {
    ElMessage.warning(t('contact.reply_required'))
    return
  }
  
  try {
    const response = await contactApi.replyMessage({
      id: currentMessage.value.id,
      reply: replyContent.value
    })
    
    if (response && response.code === 200) {
      ElMessage.success(t('contact.reply_success'))
      showReplyForm.value = false
      getMessages() // 刷新列表
      
      // 更新当前查看的消息
      const messageResponse = await contactApi.getMessage({ id: currentMessage.value.id })
      if (messageResponse && messageResponse.code === 200 && messageResponse.data) {
        currentMessage.value = messageResponse.data
      }
    } else {
      ElMessage.error(t('contact.reply_failed'))
    }
  } catch (error) {
    console.error('回复消息失败:', error)
    ElMessage.error(t('contact.reply_failed'))
  }
}

// 处理删除消息
const handleDelete = (row) => {
  messageToDelete.value = row
  deleteConfirmVisible.value = true
}

// 确认删除
const confirmDelete = async () => {
  if (!messageToDelete.value) return
  
  try {
    const response = await contactApi.deleteMessage({ id: messageToDelete.value.id })
    
    if (response && response.code === 200) {
      ElMessage.success(t('contact.delete_success'))
      deleteConfirmVisible.value = false
      
      // 如果正在查看的消息被删除，关闭详情窗口
      if (currentMessage.value && currentMessage.value.id === messageToDelete.value.id) {
        messageDetailVisible.value = false
      }
      
      getMessages() // 刷新列表
    } else {
      ElMessage.error(t('contact.delete_failed'))
    }
  } catch (error) {
    console.error('删除消息失败:', error)
    ElMessage.error(t('contact.delete_failed'))
  } finally {
    deleteConfirmVisible.value = false
  }
}

// 批量删除
const handleBatchDelete = () => {
  if (selectedMessages.value.length === 0) return
  
  ElMessageBox.confirm(
    t('contact.batchDeleteConfirm', { count: selectedMessages.value.length }),
    t('contact.confirmTitle'),
    {
      confirmButtonText: t('contact.confirm'),
      cancelButtonText: t('contact.cancel'),
      type: 'warning'
    }
  ).then(async () => {
    const deletePromises = selectedMessages.value.map(item => 
      contactApi.deleteMessage({ id: item.id })
    )
    
    try {
      await Promise.all(deletePromises)
      ElMessage.success(t('contact.batchDeleteSuccess'))
      getMessages() // 刷新列表
    } catch (error) {
      console.error('批量删除失败:', error)
      ElMessage.error(t('contact.batchDeleteFailed'))
    }
  }).catch(() => {
    // 用户取消
  })
}

// 表格选择变化
const handleSelectionChange = (selection) => {
  selectedMessages.value = selection
}

// 筛选
const handleFilter = () => {
  listQuery.page = 1
  getMessages()
}

// 刷新
const handleRefresh = () => {
  getMessages()
}

// 改变每页数量
const handleSizeChange = (val) => {
  listQuery.limit = val
  getMessages()
}

// 改变页码
const handleCurrentChange = (val) => {
  listQuery.page = val
  getMessages()
}

// 页面加载时获取数据
onMounted(() => {
  getMessages()
})
</script>

<style scoped>
.contact-messages-container {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  margin: 0;
  font-size: 22px;
  color: var(--el-text-color-primary);
}

.page-tools {
  display: flex;
  gap: 15px;
}

.search-input {
  width: 500px;
}

.table-operations {
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

/* 消息详情样式 */
.message-detail {
  padding: 10px;
}

.message-info {
  margin-bottom: 20px;
  padding: 15px;
  background-color: var(--el-bg-color-page);
  border-radius: 4px;
}

.info-item {
  margin-bottom: 10px;
  display: flex;
}

.info-item .label {
  width: 120px;
  font-weight: bold;
  color: var(--el-text-color-regular);
}

.message-content, .reply-content {
  margin-bottom: 20px;
}

.content-box {
  padding: 15px;
  background-color: var(--el-fill-color-light);
  border-radius: 4px;
  white-space: pre-wrap;
  min-height: 100px;
}

.reply-info {
  margin-top: 10px;
  font-size: 14px;
  color: var(--el-text-color-secondary);
  display: flex;
  justify-content: space-between;
}

.status-actions {
  margin-bottom: 20px;
  display: flex;
  gap: 15px;
}

.reply-form {
  margin-top: 20px;
}

.confirm-content {
  text-align: center;
  padding: 20px 0;
}
</style> 