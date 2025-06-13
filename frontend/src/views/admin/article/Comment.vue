<!-- 评论管理 -->
<template>
  <div class="comment-management-container">
    <el-card shadow="hover" class="comment-table-card">
      <template #header>
        <div class="card-header">
          <span>{{ $t('adminComment.title') }}</span>
          <div class="header-actions">
            <div class="search-filter-container">
              <el-input
                v-model="listQuery.keyword"
                :placeholder="$t('adminComment.filter.keyword')"
                clearable
                class="search-input"
                @keyup.enter="handleFilter"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
              <el-select v-model="listQuery.status" :placeholder="$t('adminComment.filter.status')" clearable class="filter-select">
                <el-option :label="$t('adminComment.filter.allStatus')" value="" />
                <el-option :label="$t('adminComment.status.approved')" value="1" />
                <el-option :label="$t('adminComment.status.pending')" value="0" />
                <el-option :label="$t('adminComment.status.rejected')" value="-1" />
              </el-select>
              <el-button type="primary" @click="handleFilter">{{ $t('adminComment.filter.search') }}</el-button>
              <el-button @click="resetQuery">{{ $t('adminComment.filter.reset') }}</el-button>
              <el-button type="info" @click="fetchCommentList">
                <el-icon><Refresh /></el-icon>
                {{ $t('adminComment.refreshList') }}
              </el-button>
              <el-button type="success" @click="exportToCsv">
                <el-icon><Download /></el-icon>
                {{ $t('adminComment.exportCsv') }}
              </el-button>
            </div>
          </div>
        </div>
      </template>

      <div class="batch-operations" v-if="selectedComments.length > 0">
        <span class="selected-count">{{ $t('adminComment.selectedCount', { count: selectedComments.length }) }}</span>
        <div class="batch-buttons">
          <el-button type="success" size="small" @click="batchApprove" :disabled="!hasUnapprovedComments">
            {{ $t('adminComment.batchApprove') }}
          </el-button>
          <el-dropdown @command="batchChangeStatus" trigger="click">
            <el-button size="small" type="warning">
              {{ $t('adminComment.changeStatus.button') }}
              <el-icon class="el-icon--right"><arrow-down /></el-icon>
            </el-button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item :command="1">{{ $t('adminComment.status.approved') }}</el-dropdown-item>
                <el-dropdown-item :command="0">{{ $t('adminComment.status.pending') }}</el-dropdown-item>
                <el-dropdown-item :command="-1">{{ $t('adminComment.status.rejected') }}</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
          <el-button type="danger" size="small" @click="batchDelete">
            {{ $t('adminComment.batchDelete') }}
          </el-button>
        </div>
      </div>

      <el-table
        v-loading="loading"
        :data="commentList"
        border
        style="width: 100%"
        row-key="id"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="post_title" :label="$t('adminComment.list.articleTitle')" min-width="150" show-overflow-tooltip>
          <template #default="scope">
            <el-link
              v-if="scope.row.post_id && scope.row.post_title"
              type="primary"
              @click="viewArticle(scope.row.post_id)"
            >
              {{ scope.row.post_title}}
            </el-link>
            <span v-else class="no-data">{{ $t('adminComment.noTitle') }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="content" :label="$t('adminComment.list.content')" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <div class="comment-content">
              <span v-if="scope.row.parent_id" class="reply-badge">{{ $t('adminComment.reply.badge') }}</span>
              {{ scope.row.content || $t('adminComment.noContent') }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="username" :label="$t('adminComment.list.user')" width="120">
          <template #default="scope">
            <div class="user-info" v-if="scope.row.username || scope.row.user_name">
              <el-avatar :size="24" :src="scope.row.avatar || defaultAvatar"></el-avatar>
              <span>{{ scope.row.username || scope.row.user_name}}</span>
            </div>
            <div class="user-info" v-else>
              <el-avatar :size="24" :src="defaultAvatar"></el-avatar>
              <span class="no-data">{{ $t('adminComment.anonymousUser') }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="type" :label="$t('adminComment.list.type')" width="100">
          <template #default="scope">
            <el-tag :type="getCommentTypeClass(scope.row.type)" effect="plain">
              {{ getCommentTypeText(scope.row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="ip" :label="$t('adminComment.list.ip')" width="150" show-overflow-tooltip>
          <template #default="scope">
            <div class="ip-info">
              <div>{{ scope.row.ip }}</div>
              <el-tooltip :content="scope.row.user_agent" effect="dark" placement="top">
                <div class="user-agent-text">{{ getUserAgentShort(scope.row.user_agent) }}</div>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="likes" :label="$t('adminComment.list.likes')" width="90">
          <template #default="scope">
            <div class="like-count">
              <el-icon><Star /></el-icon>
              <span>{{ scope.row.likes || 0 }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="status" :label="$t('adminComment.list.status')" width="90">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)" effect="plain">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" :label="$t('adminComment.list.createTime')" width="160">
          <template #default="scope">
            {{ formatDate(scope.row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column :label="$t('adminComment.list.operations')" width="350" fixed="right">
          <template #default="scope">
            <div class="operation-buttons">
              <el-button
                size="small"
                type="primary"
                @click="handleReply(scope.row)"
              >
                {{ $t('adminComment.replyButton') }}
              </el-button>
              <el-button
                size="small"
                type="success"
                v-if="scope.row.status === 'pending' || scope.row.status === 0"
                @click="handleApprove(scope.row)"
              >
                {{ $t('adminComment.approveButton') }}
              </el-button>
              <el-dropdown @command="(command) => handleChangeStatus(scope.row, command)" trigger="click">
                <el-button size="small" type="warning">
                  {{ $t('adminComment.changeStatus.button') }}
                  <el-icon class="el-icon--right"><arrow-down /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item :command="1">{{ $t('adminComment.status.approved') }}</el-dropdown-item>
                    <el-dropdown-item :command="0">{{ $t('adminComment.status.pending') }}</el-dropdown-item>
                    <el-dropdown-item :command="-1">{{ $t('adminComment.status.rejected') }}</el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
              <el-button
                size="small"
                type="danger"
                @click="handleDelete(scope.row)"
              >
                {{ $t('adminComment.deleteButton') }}
              </el-button>
            </div>
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

    <!-- 回复评论对话框 -->
    <el-dialog
      v-model="replyDialogVisible"
      :title="$t('adminComment.reply.title')"
      width="800px"
      top="10vh"
      class="reply-dialog"
    >
      <div class="original-comment" v-if="currentComment">
        <h4>{{ $t('adminComment.reply.originalComment') }}</h4>
        <div class="comment-card">
          <div class="comment-header">
            <div class="comment-user">
              <el-avatar :size="30" :src="currentComment.avatar || defaultAvatar"></el-avatar>
              <span class="user-name">{{ currentComment.username }}</span>
            </div>
            <div class="comment-time">{{ formatDate(currentComment.create_time) }}</div>
          </div>
          <div class="comment-body">{{ currentComment.content }}</div>
        </div>
      </div>

      <el-form
        ref="replyFormRef"
        :model="replyForm"
        :rules="replyRules"
        label-width="100px"
        class="reply-form"
      >
        <el-form-item :label="$t('adminComment.reply.content')" prop="content">
          <div class="editor-container">
            <div class="editor-toolbar">
              <div class="toolbar-group">
                <el-button size="small" @click="applyFormat('bold')" :title="$t('adminComment.editor.toolbar.bold')">
                  <strong>B</strong>
                </el-button>
                <el-button size="small" @click="applyFormat('italic')" :title="$t('adminComment.editor.toolbar.italic')">
                  <i>I</i>
                </el-button>
                <el-button size="small" @click="applyFormat('underline')" :title="$t('adminComment.editor.toolbar.underline')">
                  <u>U</u>
                </el-button>
              </div>
              <div class="toolbar-group">
                <el-button size="small" @click="insertEmoji('😊')" :title="$t('adminComment.editor.toolbar.emoji.smile')">😊</el-button>
                <el-button size="small" @click="insertEmoji('👍')" :title="$t('adminComment.editor.toolbar.emoji.thumbsUp')">👍</el-button>
                <el-button size="small" @click="insertEmoji('❤️')" :title="$t('adminComment.editor.toolbar.emoji.heart')">❤️</el-button>
                <el-button size="small" @click="insertEmoji('🙏')" :title="$t('adminComment.editor.toolbar.emoji.thanks')">🙏</el-button>
              </div>
              <div class="toolbar-group">
                <el-button size="small" @click="clearFormat()" :title="$t('adminComment.editor.toolbar.clear')">
                  <el-icon><Delete /></el-icon>
                </el-button>
              </div>
            </div>
            <div
              ref="editorRef"
              class="editor-content"
              contenteditable="true"
              @input="handleEditorInput"
              @paste="handlePaste"
              @keydown="handleKeyDown"
            ></div>
            <div class="editor-footer">
              <span class="char-count">{{ $t('adminComment.editor.charCount', { count: getCharCount() }) }}</span>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="replyDialogVisible = false">{{ $t('adminComment.reply.cancel') }}</el-button>
          <el-button type="primary" @click="submitReply" :loading="submitting">
            {{ $t('adminComment.reply.submit') }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted, nextTick, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Edit, Delete, Document, InfoFilled, Star, Refresh, Download, ArrowDown } from '@element-plus/icons-vue'
import { formatDate } from '@/utils/date'
import {
  getComments,
  createComment,
  replyComment,
  updateComment,
  deleteComment,
  updateCommentStatus
} from '@/api/admin/article'
import { useI18n } from 'vue-i18n'

export default {
  name: 'CommentManagement',
  components: {
    Search,
    Edit,
    Delete,
    Document,
    InfoFilled,
    Star,
    Refresh,
    Download,
    ArrowDown
  },
  setup() {
    const router = useRouter()
    const { t } = useI18n()

    // Define default avatar as base64 data URL instead of importing a file
    const defaultAvatar = ref('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QQdCQowj1/2IgAABLdJREFUeNrtnV2IVVUUx39XZ9Jx0gcTjJlRzBKllLSPB4/NEzHMw9Cklg+BZUF9PClSSlFQEFgvBQZBkxWUQUFEkWZRZKClZuGDH2lNfpSOOk6O3rVPZ2a4M9Nde59z9pnZ9/+ynLn77LX3+p/1sdde51wQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQhFoYVOQDPLBpyLUVHZj3aBpzTyV6U+HwzJH1rrZiOvH8bsYN/WPrJ0+fyJSQLhXeCJx0Nt/T3jGZCSF+99ZJ3NjjGJw3iMH0HlNx594tV10vQqKi1ZVm3CDmDmYiIVmdQwaMG1rQEY+1joiQFgHkHwlp4UndjLmnJCRN/KEhjdLU1iVCUsLlbpwv7pKQFjUkH/l3p9QhJWUBcIfT+TiZQ1JAZ1dvRIh0WfXA0mXVQYiIiIC8VloiJCNoBh0RIVmxaxGRXKaOrvmDRBESgQZX5zpdzicUuYe0dxzoLGuXtfrjmDdspZAuS4QUgSZXO9ZazwG2Mh+ICEmgdajnR4XGvWP9e4JsZV65Qxbf+/Jc4N1aOzbmCvsmhS0Rc3Iw/H5h7vXWPOOsG6GQQubccuMUQHcD/qD+vdYxNPZ9DWU4m4fm36feuPG3CEmBy+2e4QmZA9g6PHPH4c/Zm4sgDXPTldteHX9k76uduXhSmE/uuBt9vB54LMYhfjw5Y8XEVTvXvZuLIIWZ/Z6RA07W/3HdlGVdK6dufm7jXWsvy0VzmpHm4PxfF/W0rzwAfJBgmL99PHPp5Fe2vLB2Wy62JKUZFTjgO0+7YXSuoSFrcWxcr9+36tmHx2/+bfBfOdmSVCZUoC+kLedqKrRiH3yPe8b5pvxsfCj6adfLT16+/eBruRmTmhxC+Sh9HVrXkEbL1PfuuWdeu+H5A69vz9Wo5GdZ/9MBrLn/jUfb+q96zZP7XSKmEgTw7fwn7xiz8q2tP6zcn7txqS1u+R8dcGbe0gd85+4VF2frBrLw6bH7bVpjyvvO7LrtzdtfH1n8wuuFGJjqAvXFbuvM7MVDvfMvW17FDptW9WQwnDGmEvwSgPl0xtpVhRqZ9jP1/g3JnP8uAVY2cjQ8pQLHgfKPm7pWj1q37ZfCDU37KeqZX5fsa++8C3ikkWPxEoAzhzbGBa/c+cLowg3NwlPcM/OeGOac3wgs8Z7ZicDnBlgDnIvrmFOm4nz3xtF3jvv4u001NStPmv8JUrFrJrZdADyuXg8SJu8iHN0MnIx/jM5W4OtmEDIhS0+jByfmGrBUnV4e4Jhfq8+J6Ja+BW5vFiETs/bE/ACn5ypwF/AfcFb9OzHmcXwJ/N1MQuZl7en4AOdl7YIdbT3hF9LuBM6k8LtvgEPNJmRCHp5GDoI+DM1HgDdTOO6vCCsLTSdkch6eeL/yDnA/0B1zPT1EWOYfbEYh8/PwZH2AnSvVZwmwM8ahjwOfNauQ8Xl46kxxXhkPfFbjUH8FVhCW+Acdg2DeSW1y70L9tS4HThCW848A2wgZ1U+AB7ZWL0KaV8jxYSDCdXdJiAgRISJEhIgQESJCRIgIESEiRIQERIQIgiAIgiAIgiAIgiAIQpnwHwr4A/j3A3KDAAAAAElFTkSuQmCC')

    // 评论列表数据
    const commentList = ref([])
    const total = ref(0)
    const loading = ref(false)
    const listQuery = reactive({
      page: 1,
      limit: 10,
      keyword: '',
      status: ''
    })

    // 批量操作相关
    const selectedComments = ref([])
    const hasUnapprovedComments = computed(() => {
      return selectedComments.value.some(comment =>
        comment.status === 0 || comment.status === '0' || comment.status === 'pending'
      )
    })

    // 回复对话框
    const replyDialogVisible = ref(false)
    const replyFormRef = ref(null)
    const replyForm = reactive({
      post_id: '',
      parent_id: '',
      content: ''
    })
    const replyRules = reactive({
      content: [
        { required: true, message: t('adminComment.reply.contentRequired'), trigger: 'blur' },
        { min: 2, max: 500, message: t('adminComment.reply.contentLength'), trigger: 'blur' }
      ]
    })
    const currentComment = ref(null)
    const submitting = ref(false)

    // 编辑器相关
    const editorRef = ref(null)

    // 获取评论列表
    const fetchCommentList = async () => {
      loading.value = true

      try {
        const res = await getComments(listQuery)

        if (res.code === 200) {
          commentList.value = res.data.list
          total.value = res.data.total
        } else {
          ElMessage.error(t('adminComment.error.fetchFailed'))
        }
      } catch (error) {
        console.error('获取评论列表失败:', error)
        ElMessage.error(t('adminComment.error.networkError'))
      } finally {
        loading.value = false
      }
    }

    // 筛选
    const handleFilter = () => {
      listQuery.page = 1
      fetchCommentList()
    }

    // 重置筛选
    const resetQuery = () => {
      listQuery.keyword = ''
      listQuery.status = ''
      listQuery.page = 1
      fetchCommentList()
    }

    // 分页
    const handleCurrentChange = (page) => {
      listQuery.page = page
      fetchCommentList()
    }

    const handleSizeChange = (size) => {
      listQuery.limit = size
      listQuery.page = 1
      fetchCommentList()
    }

    // 查看文章
    const viewArticle = (id) => {
      // 这里可以根据实际需求跳转到文章页面
      window.open(`/admin/article/edit/${id}`, '_blank')
    }

    // 编辑器字符数
    const getCharCount = () => {
      if (!editorRef.value) return 0
      return editorRef.value.textContent.length
    }

    // 处理编辑器输入事件
    const handleEditorInput = () => {
      if (editorRef.value) {
        replyForm.content = editorRef.value.innerHTML
      }
    }

    // 应用格式
    const applyFormat = (format) => {
      document.execCommand(format, false, null)
      editorRef.value.focus()
      handleEditorInput()
    }

    // 插入表情
    const insertEmoji = (emoji) => {
      document.execCommand('insertText', false, emoji)
      editorRef.value.focus()
      handleEditorInput()
    }

    // 清除格式
    const clearFormat = () => {
      document.execCommand('removeFormat', false, null)
      editorRef.value.focus()
      handleEditorInput()
    }

    // 处理粘贴，只保留纯文本
    const handlePaste = (e) => {
      e.preventDefault()
      const text = (e.clipboardData || window.clipboardData).getData('text/plain')
      document.execCommand('insertText', false, text)
    }

    // 处理回车按键
    const handleKeyDown = (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        document.execCommand('insertText', false, '\n')
      }
    }

    // 回复评论
    const handleReply = (row) => {
      currentComment.value = row
      replyForm.post_id = row.post_id
      replyForm.parent_id = row.id
      replyForm.content = ''
      replyDialogVisible.value = true

      // 清空编辑器内容
      nextTick(() => {
        if (editorRef.value) {
          editorRef.value.innerHTML = ''
          editorRef.value.focus()
        }
      })
    }

    // 提交回复
    const submitReply = async () => {
      if (!replyFormRef.value) return

      await replyFormRef.value.validate(async (valid) => {
        if (!valid) return

        submitting.value = true

        try {
          const res = await replyComment(replyForm)

          if (res.code === 200) {
            ElMessage.success(t('adminComment.reply.success'))
            replyDialogVisible.value = false
            fetchCommentList()
          } else {
            ElMessage.error(res.msg || t('adminComment.reply.error'))
          }
        } catch (error) {
          console.error('回复评论失败:', error)
          ElMessage.error(t('adminComment.reply.error'))
        } finally {
          submitting.value = false
        }
      })
    }

    // 审核通过评论
    const handleApprove = (row) => {
      ElMessageBox.confirm(
        t('adminComment.approve.confirm'),
        t('adminComment.approve.title'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'info'
        }
      ).then(async () => {
        try {
          const res = await updateCommentStatus({
            id: row.id,
            status: 1  // Use numeric value 1 (approved)
          })

          if (res.code === 200) {
            ElMessage.success(t('adminComment.approve.success'))
            fetchCommentList()
          } else {
            ElMessage.error(res.msg || t('adminComment.approve.error'))
          }
        } catch (error) {
          console.error('更新评论状态失败:', error)
          ElMessage.error(t('adminComment.approve.error'))
        }
      }).catch(() => {})
    }

    // 修改评论状态
    const handleChangeStatus = (row, status) => {
      // 如果状态相同，不做任何操作
      if (row.status === status || row.status === Number(status)) {
        return
      }

      // 获取状态文本
      const statusText = t(`adminComment.status.${status}`)

      ElMessageBox.confirm(
        t('adminComment.changeStatus.confirm', { status: statusText }),
        t('adminComment.changeStatus.title'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await updateCommentStatus({
            id: row.id,
            status: status
          })

          if (res.code === 200) {
            ElMessage.success(t('adminComment.changeStatus.success'))
            fetchCommentList()
          } else {
            ElMessage.error(res.msg || t('adminComment.changeStatus.error'))
          }
        } catch (error) {
          console.error('修改评论状态失败:', error)
          ElMessage.error(t('adminComment.changeStatus.error'))
        }
      }).catch(() => {})
    }

    // 删除评论
    const handleDelete = (row) => {
      ElMessageBox.confirm(
        t('adminComment.delete.confirm'),
        t('adminComment.delete.warning'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        try {
          const res = await deleteComment({ id: row.id })

          if (res.code === 200) {
            ElMessage.success(t('adminComment.delete.success'))
            fetchCommentList()
          } else {
            ElMessage.error(res.msg || t('adminComment.delete.error'))
          }
        } catch (error) {
          console.error('删除评论失败:', error)
          ElMessage.error(t('adminComment.delete.error'))
        }
      }).catch(() => {})
    }

    // 获取评论类型文本
    const getCommentTypeText = (type) => {
      return t(`adminComment.type.${type}`) || t('adminComment.type.unknown')
    }

    // 获取评论类型样式
    const getCommentTypeClass = (type) => {
      const typeMap = {
        'normal': 'info',
        'admin': 'danger',
        'author': 'warning',
        'guest': 'info'
      }
      return typeMap[type] || 'info'
    }

    // 获取状态类型
    const getStatusType = (status) => {
      // Handle numeric status values
      if (typeof status === 'number' || !isNaN(parseInt(status))) {
        const numStatus = parseInt(status);
        switch (numStatus) {
          case 1: return 'success';  // 通过
          case 0: return 'warning';  // 待审核
          case -1: return 'danger';  // 拒绝
          default: return 'info';
        }
      }

      // Handle string status values
      const statusMap = {
        'approved': 'success',
        'pending': 'warning',
        'rejected': 'danger',
        '1': 'success',
        '0': 'warning',
        '-1': 'danger'
      }
      return statusMap[status] || 'info'
    }

    // 获取状态文本
    const getStatusText = (status) => {
      return t(`adminComment.status.${status}`) || t('adminComment.status.unknown')
    }

    // 获取用户代理简短描述
    const getUserAgentShort = (userAgent) => {
      if (!userAgent) return t('adminComment.userAgent.unknown')

      // 简化处理User-Agent
      if (userAgent.includes('Chrome')) {
        return 'Chrome'
      } else if (userAgent.includes('Firefox')) {
        return 'Firefox'
      } else if (userAgent.includes('Safari')) {
        return 'Safari'
      } else if (userAgent.includes('Edge')) {
        return 'Edge'
      } else if (userAgent.includes('MSIE') || userAgent.includes('Trident')) {
        return 'IE'
      } else {
        return t('adminComment.userAgent.other')
      }
    }

    // 处理表格选择变化
    const handleSelectionChange = (selection) => {
      selectedComments.value = selection
    }

    // 批量审核通过评论
    const batchApprove = () => {
      if (selectedComments.value.length === 0) return

      ElMessageBox.confirm(
        t('adminComment.approve.confirm'),
        t('adminComment.approve.title'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'info'
        }
      ).then(async () => {
        const unapprovedComments = selectedComments.value.filter(comment =>
          comment.status === 0 || comment.status === '0' || comment.status === 'pending'
        )

        if (unapprovedComments.length === 0) {
          ElMessage.info(t('adminComment.noUnapprovedComments'))
          return
        }

        loading.value = true

        try {
          // 使用Promise.all并行处理所有请求
          await Promise.all(unapprovedComments.map(comment =>
            updateCommentStatus({
              id: comment.id,
              status: 1
            })
          ))

          ElMessage.success(t('adminComment.batchApproveSuccess'))
          fetchCommentList()
        } catch (error) {
          console.error('批量审核评论失败:', error)
          ElMessage.error(t('adminComment.batchApproveError'))
        } finally {
          loading.value = false
        }
      }).catch(() => {})
    }

    // 批量删除评论
    const batchDelete = () => {
      if (selectedComments.value.length === 0) return

      ElMessageBox.confirm(
        t('adminComment.batchDeleteConfirm'),
        t('adminComment.delete.warning'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        loading.value = true

        try {
          // 使用Promise.all并行处理所有请求
          await Promise.all(selectedComments.value.map(comment =>
            deleteComment({ id: comment.id })
          ))

          ElMessage.success(t('adminComment.batchDeleteSuccess'))
          fetchCommentList()
        } catch (error) {
          console.error('批量删除评论失败:', error)
          ElMessage.error(t('adminComment.batchDeleteError'))
        } finally {
          loading.value = false
        }
      }).catch(() => {})
    }

    // 批量修改评论状态
    const batchChangeStatus = (status) => {
      if (selectedComments.value.length === 0) return

      // 获取状态文本
      const statusText = t(`adminComment.status.${status}`)

      ElMessageBox.confirm(
        t('adminComment.changeStatus.confirm', { status: statusText }),
        t('adminComment.changeStatus.title'),
        {
          confirmButtonText: t('adminComment.delete.confirmButton'),
          cancelButtonText: t('adminComment.delete.cancelButton'),
          type: 'warning'
        }
      ).then(async () => {
        loading.value = true

        try {
          // 使用Promise.all并行处理所有请求
          await Promise.all(selectedComments.value.map(comment =>
            updateCommentStatus({
              id: comment.id,
              status: status
            })
          ))

          ElMessage.success(t('adminComment.batchChangeStatusSuccess'))
          fetchCommentList()
        } catch (error) {
          console.error('批量修改评论状态失败:', error)
          ElMessage.error(t('adminComment.batchChangeStatusError'))
        } finally {
          loading.value = false
        }
      }).catch(() => {})
    }

    // 导出评论到CSV
    const exportToCsv = async () => {
      // 如果有选中的评论，则导出选中的，否则导出当前页
      const commentsToExport = selectedComments.value.length > 0 ? selectedComments.value : commentList.value

      if (commentsToExport.length === 0) {
        ElMessage.warning(t('adminComment.noData'))
        return
      }

      // 准备CSV内容
      const headers = [
        'ID',
        t('adminComment.list.articleTitle'),
        t('adminComment.list.content'),
        t('adminComment.list.user'),
        t('adminComment.list.type'),
        t('adminComment.list.ip'),
        t('adminComment.list.status'),
        t('adminComment.list.createTime'),
        t('adminComment.list.likes')
      ]

      let csvContent = headers.join(',') + '\n'

      commentsToExport.forEach(comment => {
        // 处理内容中的逗号和换行符
        const content = comment.content ? `"${comment.content.replace(/"/g, '""')}"` : ''
        const postTitle = comment.post_title ? `"${comment.post_title.replace(/"/g, '""')}"` : ''

        const row = [
          comment.id,
          postTitle,
          content,
          comment.username || t('adminComment.anonymousUser'),
          getCommentTypeText(comment.type),
          comment.ip || '',
          getStatusText(comment.status),
          formatDate(comment.create_time),
          comment.likes || 0
        ]

        csvContent += row.join(',') + '\n'
      })

      // 创建BOM头，确保中文正确显示
      const BOM = '\uFEFF'
      const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' })

      // 创建下载链接
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)

      link.setAttribute('href', url)
      link.setAttribute('download', `comments_${new Date().toISOString().slice(0, 10)}.csv`)
      link.style.visibility = 'hidden'

      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)

      ElMessage.success(t('adminComment.exportSuccess'))
    }

    // 初始化数据
    onMounted(() => {
      fetchCommentList()
    })

    return {
      commentList,
      total,
      loading,
      listQuery,
      replyDialogVisible,
      replyFormRef,
      replyForm,
      replyRules,
      currentComment,
      submitting,
      defaultAvatar,
      formatDate,
      fetchCommentList,
      handleFilter,
      resetQuery,
      handleCurrentChange,
      handleSizeChange,
      viewArticle,
      handleReply,
      submitReply,
      handleApprove,
      handleChangeStatus,
      handleDelete,
      getCommentTypeText,
      getCommentTypeClass,
      getStatusType,
      getStatusText,
      getUserAgentShort,
      editorRef,
      handleEditorInput,
      getCharCount,
      applyFormat,
      insertEmoji,
      clearFormat,
      handlePaste,
      handleKeyDown,
      // 批量操作相关
      selectedComments,
      hasUnapprovedComments,
      handleSelectionChange,
      batchApprove,
      batchChangeStatus,
      batchDelete,
      exportToCsv
    }
  }
}
</script>

<style scoped>
.comment-management-container {
  padding: 20px;
}

.comment-table-card {
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

.reply-badge {
  background-color: #ecf5ff;
  color: #409eff;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
  margin-right: 5px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ip-info {
  font-size: 12px;
  color: #606266;
}

.user-agent-text {
  color: #909399;
  margin-top: 3px;
  cursor: help;
}

.original-comment {
  margin-bottom: 20px;
}

.original-comment h4 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #606266;
}

.comment-card {
  background-color: #f5f7fa;
  border-radius: 4px;
  padding: 12px;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.comment-user {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-name {
  font-weight: 500;
}

.comment-time {
  font-size: 12px;
  color: #909399;
}

.comment-body {
  line-height: 1.5;
  color: #303133;
}

.comment-content {
  display: flex;
  align-items: center;
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
  .comment-management-container {
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
}

.no-data {
  color: #909399;
  font-style: italic;
}

/* 回复对话框样式 */
:deep(.reply-dialog .el-dialog__body) {
  padding: 20px 30px;
}

:deep(.reply-dialog .el-dialog__header) {
  padding: 20px 30px;
  margin-right: 0;
  border-bottom: 1px solid #ebeef5;
}

:deep(.reply-dialog .el-dialog__footer) {
  padding: 15px 30px 20px;
  border-top: 1px solid #ebeef5;
}

.reply-form {
  margin-top: 20px;
  width: 100%;
}

:deep(.reply-form .el-form-item__content) {
  width: calc(100% - 100px); /* Width minus label width */
  max-width: none;
}

.editor-container {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
  width: 100%;
}

.editor-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background-color: #f5f7fa;
  border-bottom: 1px solid #dcdfe6;
}

.toolbar-group {
  display: flex;
  gap: 5px;
  align-items: center;
}

.toolbar-group:not(:last-child) {
  margin-right: 10px;
  padding-right: 10px;
  border-right: 1px solid #dcdfe6;
}

.editor-content {
  min-height: 150px;
  max-height: 300px;
  padding: 12px;
  background-color: #fff;
  overflow-y: auto;
  line-height: 1.5;
}

.editor-content:focus {
  outline: none;
}

.editor-footer {
  padding: 5px 12px;
  background-color: #f5f7fa;
  border-top: 1px solid #dcdfe6;
  display: flex;
  justify-content: flex-end;
}

.char-count {
  font-size: 12px;
  color: #909399;
}

.like-count {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #e6a23c;
}

.like-count .el-icon {
  font-size: 16px;
}

.batch-operations {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  padding: 10px;
  background-color: #f0f9eb;
  border-radius: 4px;
}

.batch-buttons {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.selected-count {
  font-weight: bold;
  margin-right: 10px;
}

/* 操作按钮样式 */
.el-table .cell .el-button,
.el-table .cell .el-dropdown .el-button,
.batch-buttons .el-button,
.batch-buttons .el-dropdown .el-button {
  margin: 2px;
  padding: 6px 10px;
  height: 32px;
  line-height: 1;
  box-sizing: border-box;
  vertical-align: middle;
}

.el-table .cell .el-dropdown,
.batch-buttons .el-dropdown {
  margin: 2px;
  display: inline-block;
  vertical-align: middle;
}

.el-table .cell .el-button + .el-dropdown,
.el-table .cell .el-dropdown + .el-button,
.batch-buttons .el-button + .el-dropdown,
.batch-buttons .el-dropdown + .el-button {
  margin-left: 4px;
}

/* 确保操作列中的元素垂直居中对齐 */
.el-table .cell {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-start;
  gap: 4px;
}

/* 操作按钮容器 */
.operation-buttons {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
  width: 100%;
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  :deep(.reply-dialog) {
    width: 95% !important;
  }

  :deep(.reply-dialog .el-dialog__body),
  :deep(.reply-dialog .el-dialog__header),
  :deep(.reply-dialog .el-dialog__footer) {
    padding: 15px;
  }

  .toolbar-group {
    gap: 2px;
  }
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}
</style>