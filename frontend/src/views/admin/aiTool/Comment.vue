<template>
  <div class="ai-tool-comment-container theme-transition">
    <a-card :bordered="false" :title="$t('aiTool.comment.management')" :headStyle="{textAlign: 'left'}">
      <!-- 统计卡片 -->
      <a-row :gutter="24" class="mb-4">
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><CommentOutlined /> {{ $t('aiTool.comment.totalComments') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ total }}</div>
              <div class="stat-label">{{ $t('aiTool.comment.commentRecords') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><ClockCircleOutlined /> {{ $t('aiTool.comment.pending') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ pendingCount }}</div>
              <div class="stat-label">{{ $t('aiTool.comment.pendingRecords') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><CheckCircleOutlined /> {{ $t('aiTool.comment.approved') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ approvedCount }}</div>
              <div class="stat-label">{{ $t('aiTool.comment.approvedRecords') }}</div>
            </div>
          </a-card>
        </a-col>
        <a-col :span="6">
          <a-card class="stat-card">
            <template #title>
              <span><CloseCircleOutlined /> {{ $t('aiTool.comment.rejected') }}</span>
            </template>
            <div class="stat-card-content">
              <div class="stat-number">{{ rejectedCount }}</div>
              <div class="stat-label">{{ $t('aiTool.comment.rejectedRecords') }}</div>
            </div>
          </a-card>
        </a-col>
      </a-row>

      <!-- 搜索表单 -->
      <a-form layout="inline" :model="queryParams" class="search-form mb-4">
        <a-row :gutter="24" style="width: 100%">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.comment.toolName')" name="toolName" style="margin-bottom: 16px; width: 100%">
              <a-input v-model:value="queryParams.toolName" :placeholder="$t('aiTool.comment.toolNamePlaceholder')" allow-clear style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.comment.status')" name="status" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.status" :placeholder="$t('aiTool.comment.statusPlaceholder')" style="width: 100%" allow-clear>
                <a-select-option value="pending">{{ $t('aiTool.comment.pending') }}</a-select-option>
                <a-select-option value="approved">{{ $t('aiTool.comment.approved') }}</a-select-option>
                <a-select-option value="rejected">{{ $t('aiTool.comment.rejected') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8" class="search-buttons" style="margin-bottom: 16px">
            <a-form-item style="margin-bottom: 0; width: 100%; display: flex; justify-content: flex-end;">
              <a-space>
                <a-button type="primary" @click="handleSearch">
                  <template #icon><SearchOutlined /></template>
                  {{ $t('aiTool.comment.search') }}
                </a-button>
                <a-button @click="handleReset">
                  <template #icon><ReloadOutlined /></template>
                  {{ $t('aiTool.comment.reset') }}
                </a-button>
                <a-button type="link" @click="toggleAdvanced" style="padding: 0 8px">
                  <template #icon><DownOutlined v-if="!advanced" /><UpOutlined v-else /></template>
                  {{ advanced ? $t('aiTool.comment.collapse') : $t('aiTool.comment.expand') }}
                </a-button>
              </a-space>
            </a-form-item>
          </a-col>
        </a-row>

        <!-- 高级搜索选项 -->
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.comment.commentType')" name="parentId" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.parentId" :placeholder="$t('aiTool.comment.commentTypePlaceholder')" style="width: 100%" allow-clear>
                <a-select-option :value="0">{{ $t('aiTool.comment.mainComment') }}</a-select-option>
                <a-select-option :value="-2">{{ $t('aiTool.comment.replyComment') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.comment.userType')" name="userType" style="margin-bottom: 16px; width: 100%">
              <a-select v-model:value="queryParams.userType" :placeholder="$t('aiTool.comment.userTypePlaceholder')" style="width: 100%" allow-clear>
                <a-select-option value="user">{{ $t('aiTool.comment.normalUser') }}</a-select-option>
                <a-select-option value="admin">{{ $t('aiTool.comment.admin') }}</a-select-option>
                <a-select-option value="guest">{{ $t('aiTool.comment.guest') }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item :label="$t('aiTool.comment.keyword')" name="keyword" style="margin-bottom: 16px; width: 100%">
              <a-input v-model:value="queryParams.keyword" :placeholder="$t('aiTool.comment.keywordPlaceholder')" allow-clear style="width: 100%" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="24" style="width: 100%" v-if="advanced">
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="评论时间" name="dateRange" style="margin-bottom: 16px; width: 100%">
              <a-range-picker v-model:value="dateRange" format="YYYY-MM-DD" style="width: 100%" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="8" :md="8" :lg="8">
            <a-form-item label="评分范围" style="margin-bottom: 16px; width: 100%">
              <a-form-item-rest>
                <div style="display: flex; align-items: center; gap: 8px; width: 100%">
                  <a-input-number v-model:value="queryParams.minRating" placeholder="最小值" style="flex: 1" :min="0" :max="5" />
                  <span>-</span>
                  <a-input-number v-model:value="queryParams.maxRating" placeholder="最大值" style="flex: 1" :min="0" :max="5" />
                </div>
              </a-form-item-rest>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <div class="action-buttons" style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <a-space>
          <a-button type="primary" @click="handleBatchApprove" :disabled="selectedRowKeys.length === 0">
            <template #icon><CheckOutlined /></template>
            批量通过
          </a-button>
          <a-button @click="handleBatchReject" :disabled="selectedRowKeys.length === 0">
            <template #icon><StopOutlined /></template>
            批量拒绝
          </a-button>
          <a-button type="primary" danger @click="handleBatchDelete" :disabled="selectedRowKeys.length === 0">
            <template #icon><DeleteOutlined /></template>
            批量删除
          </a-button>
          <a-button @click="handleRefresh">
            <template #icon><ReloadOutlined /></template>
            刷新
          </a-button>
          <a-button type="primary" @click="exportData">
            <template #icon><DownloadOutlined /></template>
            导出数据
          </a-button>
          <a-button type="primary" @click="showStatistics">
            <template #icon><BarChartOutlined /></template>
            评论统计
          </a-button>
        </a-space>
      </div>

      <a-table
        :columns="columns"
        :data-source="commentList"
        :rowKey="record => record.id"
        :pagination="false"
        :loading="tableLoading"
        :rowSelection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
        @change="handleTableChange"
        bordered
        size="middle"
        :scroll="{ x: 1300 }"
        class="tool-table"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'tool_name'">
            <div class="tool-info">
              <a-avatar :src="record.tool_logo || '/images/default-logo.png'" :size="40" shape="square" />
              <span class="ml-2">{{ record.tool_name }}</span>
            </div>
          </template>
          <template v-if="column.key === 'user_nickname'">
            <span>{{ record.user_nickname || '未知用户' }}</span>
          </template>
          <template v-if="column.key === 'user_type'">
            <a-tag v-if="record.user_type === 'admin'" color="blue">管理员</a-tag>
            <a-tag v-else-if="record.user_type === 'user'" color="green">普通用户</a-tag>
            <a-tag v-else color="green">访客</a-tag>
          </template>
          <template v-if="column.key === 'comment_type'">
            <a-tag v-if="record.parent_id > 0" color="purple">回复评论</a-tag>
            <a-tag v-else color="blue">主评论</a-tag>
          </template>
          <template v-if="column.key === 'content'">
            <div class="comment-content">
              <div class="comment-text">
                <a-tooltip :title="record.content">
                  {{ record.content.length > 30 ? record.content.substring(0, 30) + '...' : record.content }}
                </a-tooltip>
              </div>
              <div class="comment-tags">
                <a-tag v-if="record.reply_count > 0" color="cyan" @click="handleViewDetail(record)" class="clickable-tag">
                  <template #icon><MessageOutlined /></template>
                  {{ record.reply_count }}条回复
                </a-tag>
              </div>
            </div>
          </template>
          <template v-if="column.key === 'rating'">
            <a-rate v-if="record.rating > 0" :value="record.rating" disabled allow-half />
            <span v-else>-</span>
          </template>
          <template v-if="column.key === 'status'">
            <a-tag v-if="record.status === 'pending'" color="orange">
              <template #icon><ClockCircleOutlined /></template>
              待审核
            </a-tag>
            <a-tag v-else-if="record.status === 'approved'" color="green">
              <template #icon><CheckCircleOutlined /></template>
              已通过
            </a-tag>
            <a-tag v-else-if="record.status === 'rejected'" color="red">
              <template #icon><CloseCircleOutlined /></template>
              已拒绝
            </a-tag>
            <a-tag v-else color="default">未知</a-tag>
          </template>
          <template v-if="column.key === 'create_time'">
            {{ formatDateTime(record.create_time) }}
          </template>
          <template v-if="column.key === 'action'">
            <a-space>
              <a-tooltip title="查看详情">
                <a-button type="primary" shape="circle" size="small" @click="handleViewDetail(record)">
                  <template #icon><EyeOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="回复评论">
                <a-button shape="circle" size="small" @click="handleReply(record)">
                  <template #icon><CommentOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="查看工具">
                <a-button shape="circle" size="small" @click="viewTool(record)">
                  <template #icon><LinkOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip v-if="record.status !== 'approved'" title="通过">
                <a-button type="primary" shape="circle" size="small" @click="handleApprove(record)">
                  <template #icon><CheckOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip v-if="record.status !== 'rejected'" title="拒绝">
                <a-button danger shape="circle" size="small" @click="handleReject(record)">
                  <template #icon><StopOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="删除">
                <a-button type="primary" danger shape="circle" size="small" @click="handleDelete(record)">
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </template>
        </template>
        <!-- 自定义表格底部 -->
        <template #footer>
          <div class="table-footer">
            <span>已选择 <a>{{ selectedRowKeys.length }}</a> 项</span>
            <a-button type="link" @click="clearSelection" v-if="selectedRowKeys.length > 0">清空</a-button>
          </div>
        </template>
      </a-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <a-pagination
          v-model:current="queryParams.page"
          v-model:pageSize="queryParams.limit"
          :total="total"
          :showSizeChanger="true"
          :pageSizeOptions="['10', '20', '30', '50']"
          :showTotal="total => `共 ${total} 条记录`"
          @change="handlePageChange"
          @showSizeChange="handleSizeChange"
        />
      </div>

      <!-- 详情抽屉 -->
      <a-drawer
        title="评论详情"
        :open="detailDrawerVisible"
        :width="700"
        @close="detailDrawerVisible = false"
      >
        <template v-if="currentRecord">
          <a-descriptions :column="1" bordered>
            <a-descriptions-item label="ID">{{ currentRecord.id }}</a-descriptions-item>
            <a-descriptions-item label="工具名称">
              <div class="tool-info">
                <a-avatar :src="currentRecord.tool_logo || '/images/default-logo.png'" :size="40" shape="square" />
                <span class="ml-2">{{ currentRecord.tool_name }}</span>
              </div>
            </a-descriptions-item>
            <a-descriptions-item label="用户">
              {{ currentRecord.user_nickname || '未知用户' }}
              <a-tag v-if="currentRecord.user_type === 'admin'" color="blue">管理员</a-tag>
              <a-tag v-else color="green">普通用户</a-tag>
            </a-descriptions-item>
            <a-descriptions-item label="用户ID">{{ currentRecord.user_id || '无' }}</a-descriptions-item>
            <a-descriptions-item label="评分" v-if="currentRecord.parent_id === 0">
              <a-rate v-if="currentRecord.rating > 0" :value="currentRecord.rating" disabled allow-half />
              <span v-else>-</span>
            </a-descriptions-item>
            <a-descriptions-item label="状态">
              <a-tag v-if="currentRecord.status === 'pending'" color="orange">
                <template #icon><ClockCircleOutlined /></template>
                待审核
              </a-tag>
              <a-tag v-else-if="currentRecord.status === 'approved'" color="green">
                <template #icon><CheckCircleOutlined /></template>
                已通过
              </a-tag>
              <a-tag v-else-if="currentRecord.status === 'rejected'" color="red">
                <template #icon><CloseCircleOutlined /></template>
                已拒绝
              </a-tag>
              <a-tag v-else color="default">未知</a-tag>
            </a-descriptions-item>
            <a-descriptions-item label="IP地址">
              {{ currentRecord.ip || '-' }}
              <a-tag v-if="currentRecord.ip" color="cyan">
                {{ getIPLocation(currentRecord.ip) }}
              </a-tag>
            </a-descriptions-item>
            <a-descriptions-item label="评论时间">
              {{ formatDateTime(currentRecord.create_time) }}
            </a-descriptions-item>
            <a-descriptions-item label="评论内容">
              <div class="comment-full-content">{{ currentRecord.content }}</div>
            </a-descriptions-item>
            <a-descriptions-item label="用户代理">
              {{ currentRecord.user_agent || '-' }}
            </a-descriptions-item>
          </a-descriptions>

          <!-- 父评论信息 -->
          <div v-if="currentRecord.parent && currentRecord.parent_id > 0" class="parent-comment mt-4">
            <a-divider orientation="left">
              <template #orientationPrefix>
                <MessageOutlined />
              </template>
              回复的评论
            </a-divider>
            <a-card class="parent-comment-card">
              <div class="parent-comment-user">
                <a-avatar :size="24" icon="<UserOutlined />" />
                <span class="ml-2">{{ currentRecord.parent.user_nickname || '未知用户' }}</span>
              </div>
              <div class="parent-comment-content">
                {{ currentRecord.parent.content }}
              </div>
            </a-card>
          </div>

          <!-- 回复列表 -->
          <div v-if="currentRecord.replies && currentRecord.replies.length > 0" class="replies mt-4">
            <a-divider orientation="left">
              <template #orientationPrefix>
                <CommentOutlined />
              </template>
              回复列表 ({{ currentRecord.replies.length }})
            </a-divider>
            <a-list
              :data-source="currentRecord.replies"
              item-layout="horizontal"
              class="reply-list"
            >
              <template #renderItem="{ item }">
                <a-list-item>
                  <a-list-item-meta>
                    <template #avatar>
                      <a-avatar :size="32" icon="<UserOutlined />" />
                    </template>
                    <template #title>
                      <div class="reply-header">
                        <span class="reply-user">{{ item.user_nickname || '未知用户' }}</span>
                        <a-tag v-if="item.user_type === 'admin'" color="blue">管理员</a-tag>
                        <a-tag v-else color="green">普通用户</a-tag>
                        <span class="reply-time">{{ formatDateTime(item.create_time) }}</span>
                      </div>
                    </template>
                    <template #description>
                      <div class="reply-content">{{ item.content }}</div>
                      <div class="reply-status">
                        <a-tag v-if="item.status === 'pending'" color="orange">
                          <template #icon><ClockCircleOutlined /></template>
                          待审核
                        </a-tag>
                        <a-tag v-else-if="item.status === 'approved'" color="green">
                          <template #icon><CheckCircleOutlined /></template>
                          已通过
                        </a-tag>
                        <a-tag v-else-if="item.status === 'rejected'" color="red">
                          <template #icon><CloseCircleOutlined /></template>
                          已拒绝
                        </a-tag>
                      </div>
                      <div class="reply-actions">
                        <a-button type="link" size="small" @click="handleReply(item)">回复</a-button>
                        <a-button type="link" size="small" @click="handleApprove(item)" v-if="item.status !== 'approved'">通过</a-button>
                        <a-button type="link" size="small" @click="handleReject(item)" v-if="item.status !== 'rejected'">拒绝</a-button>
                        <a-button type="link" size="small" danger @click="handleDelete(item)">删除</a-button>
                      </div>
                    </template>
                  </a-list-item-meta>
                </a-list-item>
              </template>
            </a-list>
          </div>

          <!-- 快速回复表单 -->
          <div class="quick-reply mt-4">
            <a-divider orientation="left">
              <template #orientationPrefix>
                <EditOutlined />
              </template>
              快速回复
            </a-divider>
            <a-form layout="vertical">
              <a-form-item>
                <a-textarea v-model:value="quickReplyContent" :rows="3" placeholder="输入回复内容..." />
              </a-form-item>
              <a-form-item>
                <a-button type="primary" @click="submitQuickReply" :loading="submitting">提交回复</a-button>
              </a-form-item>
            </a-form>
          </div>
        </template>

        <template #footer>
          <div style="text-align: right;">
            <a-space>
              <a-button @click="detailDrawerVisible = false">关闭</a-button>
              <a-button type="primary" @click="viewTool(currentRecord)">查看工具</a-button>
              <a-button v-if="currentRecord && currentRecord.status !== 'approved'" type="primary" @click="handleApprove(currentRecord)">通过评论</a-button>
              <a-button v-if="currentRecord && currentRecord.status !== 'rejected'" danger @click="handleReject(currentRecord)">拒绝评论</a-button>
              <a-button type="primary" danger @click="handleDelete(currentRecord)">删除评论</a-button>
            </a-space>
          </div>
        </template>
      </a-drawer>

      <!-- 回复抽屉 -->
      <a-drawer
        title="回复评论"
        :open="replyDrawerVisible"
        :width="500"
        @close="replyDrawerVisible = false"
      >
        <template v-if="currentRecord">
          <div class="original-comment mb-4">
            <a-divider orientation="left">
              <template #orientationPrefix>
                <MessageOutlined />
              </template>
              原评论
            </a-divider>
            <a-card class="original-comment-card">
              <div class="original-comment-header">
                <div class="original-comment-user">
                  <a-avatar :size="32" icon="<UserOutlined />" />
                  <span class="ml-2">{{ currentRecord.user_nickname || '未知用户' }}</span>
                  <a-tag v-if="currentRecord.user_type === 'admin'" color="blue">管理员</a-tag>
                  <a-tag v-else color="green">普通用户</a-tag>
                </div>
                <div class="comment-time">{{ formatDateTime(currentRecord.create_time) }}</div>
              </div>
              <div class="original-comment-content">
                {{ currentRecord.content }}
              </div>
              <div class="original-comment-footer" v-if="currentRecord.rating > 0">
                <a-rate :value="currentRecord.rating" disabled allow-half />
              </div>
            </a-card>
          </div>

          <a-divider orientation="left">
            <template #orientationPrefix>
              <EditOutlined />
            </template>
            编写回复
          </a-divider>

          <a-form :model="replyForm" layout="vertical">
            <a-form-item label="回复内容" name="content" :rules="[{ required: true, message: '请输入回复内容' }]">
              <a-textarea v-model:value="replyForm.content" :rows="4" placeholder="请输入回复内容" />
            </a-form-item>
            <a-form-item>
              <a-space>
                <a-button type="primary" @click="submitReply" :loading="submitting">
                  <template #icon><SendOutlined /></template>
                  提交回复
                </a-button>
                <a-button @click="replyDrawerVisible = false">取消</a-button>
              </a-space>
            </a-form-item>
          </a-form>

          <!-- 快捷回复模板 -->
          <div class="quick-reply-templates mt-4">
            <a-divider orientation="left">
              <template #orientationPrefix>
                <ThunderboltOutlined />
              </template>
              快捷回复模板
            </a-divider>
            <div class="template-list">
              <a-button class="template-item" @click="useReplyTemplate('感谢您的评论，我们将不断改进产品和服务。')">感谢评论</a-button>
              <a-button class="template-item" @click="useReplyTemplate('您提到的问题我们已经收到，我们会尽快解决。')">问题反馈</a-button>
              <a-button class="template-item" @click="useReplyTemplate('非常感谢您的建议，我们会认真考虑。')">感谢建议</a-button>
              <a-button class="template-item" @click="useReplyTemplate('您的评论已违反社区规则，请遵守社区规范。')">违规提醒</a-button>
            </div>
          </div>
        </template>
      </a-drawer>
    </a-card>

    <!-- 统计数据对话框 -->
    <a-modal
      :open="statisticsVisible"
      title="评论统计"
      :width="800"
      @cancel="statisticsVisible = false"
      :footer="null"
    >
      <div ref="statisticsChartRef" style="width: 100%; height: 400px;"></div>

      <a-divider>评论状态统计</a-divider>

      <a-row :gutter="24" class="mt-4">
        <a-col :span="8">
          <a-statistic
            title="待审核"
            :value="pendingCount"
            :value-style="{ color: '#faad14' }"
          >
            <template #prefix>
              <ClockCircleOutlined />
            </template>
          </a-statistic>
        </a-col>
        <a-col :span="8">
          <a-statistic
            title="已通过"
            :value="approvedCount"
            :value-style="{ color: '#52c41a' }"
          >
            <template #prefix>
              <CheckCircleOutlined />
            </template>
          </a-statistic>
        </a-col>
        <a-col :span="8">
          <a-statistic
            title="已拒绝"
            :value="rejectedCount"
            :value-style="{ color: '#f5222d' }"
          >
            <template #prefix>
              <CloseCircleOutlined />
            </template>
          </a-statistic>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, nextTick } from 'vue';
import { message, Modal, Card, Row, Col, Form, Input, Select, Button, Table, Space, Drawer, Descriptions, Divider, List, Avatar, Rate, Tag, Tooltip, DatePicker, InputNumber, Statistic } from 'ant-design-vue';
import {
  SearchOutlined,
  ReloadOutlined,
  DeleteOutlined,
  EyeOutlined,
  CheckOutlined,
  StopOutlined,
  CommentOutlined,
  DownloadOutlined,
  BarChartOutlined,
  MessageOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  UserOutlined,
  LinkOutlined,
  DownOutlined,
  UpOutlined,
  EditOutlined,
  SendOutlined,
  ThunderboltOutlined
} from '@ant-design/icons-vue';
import { useRouter } from 'vue-router';
// import * as XLSX from 'xlsx'; // 需要先安装: npm install xlsx --save
import * as echarts from 'echarts/core';
import { BarChart, PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import {
  getToolCommentList,
  getTooCommentInfo,
  replyToolComment,
  updateToolCommentStatus,
  batchUpdateToolCommentStatus,
  deleteToolComment,
  batchDeleteToolComment,
} from '@/api/admin/aiToolComment';
import { formatDateTime } from '@/utils/timeUtils';

// 注册必要的ECharts组件
echarts.use([
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  BarChart,
  PieChart,
  CanvasRenderer
]);

const router = useRouter();

// 高级搜索开关
const advanced = ref(false);
const toggleAdvanced = () => {
  advanced.value = !advanced.value;
};

// 查询参数
const queryParams = reactive({
  page: 1,
  limit: 10,
  toolName: '',
  userType: undefined,
  parentId: undefined,
  status: undefined,
  keyword: '',
  startTime: undefined,
  endTime: undefined,
  minRating: undefined,
  maxRating: undefined,
  order_field: 'create_time',
  order_type: 'desc'
});

// 日期范围
const dateRange = ref([]);

// 监听日期范围变化
watch(dateRange, (newVal) => {
  if (newVal && newVal.length === 2) {
    // 将日期对象转换为字符串格式 YYYY-MM-DD
    queryParams.start_time = newVal[0] ? newVal[0].format('YYYY-MM-DD') : undefined;
    queryParams.end_time = newVal[1] ? newVal[1].format('YYYY-MM-DD') : undefined;
    // 删除旧的参数
    delete queryParams.startTime;
    delete queryParams.endTime;
  } else {
    queryParams.start_time = undefined;
    queryParams.end_time = undefined;
    // 删除旧的参数
    delete queryParams.startTime;
    delete queryParams.endTime;
  }
});

// 表格数据
const commentList = ref([]);
const loading = ref(false); // 保留原来的loading变量以兼容其他功能
const tableLoading = ref(false); // 新增表格加载状态变量
const total = ref(0);
const selectedRowKeys = ref([]);
const currentRecord = ref(null);
const detailDrawerVisible = ref(false);
const replyDrawerVisible = ref(false);
const submitting = ref(false);

// 更新统计数据
const updateStatistics = () => {
  // 这里不需要额外的操作，因为统计数据是通过计算属性自动更新的
  // 如果需要额外的统计数据，可以在这里添加
};

// 统计数据
const pendingCount = computed(() => {
  return commentList.value.filter(item => item.status === 'pending').length;
});

const approvedCount = computed(() => {
  return commentList.value.filter(item => item.status === 'approved').length;
});

const rejectedCount = computed(() => {
  return commentList.value.filter(item => item.status === 'rejected').length;
});

// 回复表单
const replyForm = reactive({
  parent_id: 0,
  content: ''
});

// 快速回复内容
const quickReplyContent = ref('');

// 分页配置
// 使用单独的分页组件，不需要计算属性

// 表格列定义
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    sorter: true,
    sortField: 'id', // 指定排序字段名
    sortDirections: ['ascend', 'descend', null], // 排序方向循环：升序 -> 降序 -> 不排序
    align: 'left'
  },
  {
    title: '工具名称',
    dataIndex: 'tool_name',
    key: 'tool_name',
    width: 180,
    ellipsis: true,
    align: 'left'
  },
  {
    title: '用户',
    dataIndex: 'user_nickname',
    key: 'user_nickname',
    width: 120,
    align: 'left'
  },
  {
    title: '用户类型',
    dataIndex: 'user_type',
    key: 'user_type',
    width: 100,
    filters: [
      { text: '管理员', value: 'admin' },
      { text: '普通用户', value: 'user' },
       { text: '访客', value: 'guest' }
    ],
    filterMultiple: false,
    align: 'left'
  },
  {
    title: '评论类型',
    dataIndex: 'parent_id',
    key: 'comment_type',
    width: 100,
    filters: [
      { text: '主评论', value: '0' },
      { text: '回复评论', value: '-1' }
    ],
    filterMultiple: false,
    align: 'left'
  },
  {
    title: '评论内容',
    dataIndex: 'content',
    key: 'content',
    ellipsis: true,
    align: 'left'
  },
  {
    title: '评分',
    dataIndex: 'rating',
    key: 'rating',
    width: 120,
    sorter: true,
    sortField: 'rating', // 指定排序字段名
    sortDirections: ['ascend', 'descend', null], // 排序方向循环：升序 -> 降序 -> 不排序
    align: 'left'
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    width: 100,
    filters: [
      { text: '待审核', value: 'pending' },
      { text: '已通过', value: 'approved' },
      { text: '已拒绝', value: 'rejected' }
    ],
    filterMultiple: false,
    align: 'left'
  },
  {
    title: '评论时间',
    dataIndex: 'create_time',
    key: 'create_time',
    width: 180,
    sorter: true,
    sortField: 'create_time', // 指定排序字段名
    sortDirections: ['ascend', 'descend', null], // 排序方向循环：升序 -> 降序 -> 不排序
    align: 'left'
  },
  {
    title: '操作',
    key: 'action',
    width: 220,
    fixed: 'right',
    align: 'left'
  }
];

// 使用公共的date.js中的formatDateTime函数

// 获取IP地理位置
const getIPLocation = (ip) => {
  if (!ip) return '未知位置';

  // 这里可以集成真实IP地理位置查询API
  // 这里使用模拟数据
  const locations = {
    '192.168.1.1': '北京市',
    '192.168.1.2': '上海市',
    '192.168.1.3': '广州市',
    '192.168.1.4': '深圳市',
    '192.168.1.5': '杭州市',
    '192.168.1.6': '成都市',
    '192.168.1.7': '重庆市',
    '192.168.1.8': '南京市',
    '192.168.1.9': '武汉市',
    '192.168.1.10': '西安市',
  };

  return locations[ip] || '其他地区';
};

// 加载评论列表
const loadCommentList = async () => {
  tableLoading.value = true; // 只将表格设置为加载状态
  try {
    // 创建查询参数的副本
    const params = { ...queryParams };

    // 处理字段名称映射
    if (params.toolName) {
      params.tool_name = params.toolName;
      delete params.toolName;
    }

    // 处理评论类型
    if (params.parentId === -2) {
      params.parent_id = -1; // -1表示查询所有回复评论
      delete params.parentId;
    } else if (params.parentId !== undefined) {
      params.parent_id = params.parentId;
      delete params.parentId;
    }

    // 处理用户类型
    if (params.userType) {
      params.user_type = params.userType;
      delete params.userType;
    }

    // 处理时间范围
    if (params.start_time) {
      params.startTime = params.start_time;
      delete params.start_time;
    }
    if (params.end_time) {
      params.endTime = params.end_time;
      delete params.end_time;
    }

    const res = await getToolCommentList(params);
    if (res.code === 200) {
      commentList.value = res.data.list || [];
      total.value = res.data.total || 0;

      // 清除选择
      selectedRowKeys.value = [];

      // 更新统计数据
      updateStatistics();
    } else {
      message.error(res.msg || '获取评论列表失败');
    }
  } catch (error) {
    console.error('获取评论列表出错:', error);
    message.error('获取评论列表失败');
  } finally {
    tableLoading.value = false; // 只将表格设置为非加载状态
  }
};

// 查询
const handleSearch = () => {
  queryParams.page = 1;
  loadCommentList();
};

// 重置
const handleReset = () => {
  queryParams.page = 1;
  queryParams.toolName = '';
  queryParams.userType = undefined;
  queryParams.parentId = undefined;
  queryParams.status = undefined;
  queryParams.keyword = '';
  queryParams.start_time = undefined;
  queryParams.end_time = undefined;
  queryParams.minRating = undefined;
  queryParams.maxRating = undefined;
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';
  // 删除旧的参数
  delete queryParams.startTime;
  delete queryParams.endTime;
  dateRange.value = [];
  advanced.value = false;
  loadCommentList();
};

// 刷新
const handleRefresh = () => {
  loadCommentList();
};

// 处理页码变化
const handlePageChange = (page, pageSize) => {
  queryParams.page = page;
  queryParams.limit = pageSize;
  loadCommentList();
};

// 处理每页数量变化
const handleSizeChange = (current, size) => {
  queryParams.limit = size;
  queryParams.page = 1;
  loadCommentList();
};

// 表格变化
const handleTableChange = (pagination, filters, sorter) => {
  // 处理排序
  if (sorter && sorter.field) {
    // 使用 sortField 属性（如果存在），否则使用 field
    const fieldName = sorter.column?.sortField || sorter.field;
    queryParams.order_field = fieldName;
    queryParams.order_type = sorter.order === 'ascend' ? 'asc' : 'desc';
  }

  // 处理过滤
  if (filters) {
    // 处理用户类型过滤
    if (filters.user_type && filters.user_type.length > 0) {
      queryParams.userType = filters.user_type[0];
    } else {
      queryParams.userType = undefined;
    }

    // 处理评论类型过滤
    if (filters.comment_type && filters.comment_type.length > 0) {
      queryParams.parentId = filters.comment_type[0];
    } else {
      queryParams.parentId = undefined;
    }

    // 处理状态过滤
    if (filters.status && filters.status.length > 0) {
      queryParams.status = filters.status[0];
    } else {
      queryParams.status = undefined;
    }
  }

  loadCommentList();
};

// 选择行变化
const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

// 清空选择
const clearSelection = () => {
  selectedRowKeys.value = [];
};

// 查看工具
const viewTool = (record) => {
  if (!record || !record.tool_id) {
    message.warning('工具信息不完整');
    return;
  }

  // 跳转到工具编辑页面
  router.push(`/admin/aiTool-edit/${record.tool_id}`);
};

// 使用回复模板
const useReplyTemplate = (template) => {
  replyForm.content = template;
};

// 提交快速回复
const submitQuickReply = async () => {
  if (!quickReplyContent.value.trim()) {
    message.warning('请输入回复内容');
    return;
  }

  if (!currentRecord.value) {
    message.warning('评论信息不完整');
    return;
  }

  submitting.value = true;
  try {
    const res = await replyToolComment({
      parent_id: currentRecord.value.id,
      content: quickReplyContent.value
    });

    if (res.code === 200) {
      message.success('回复成功');
      quickReplyContent.value = '';

      // 重新加载评论详情
      const detailRes = await getTooCommentInfo({ id: currentRecord.value.id });
      if (detailRes.code === 200) {
        currentRecord.value = detailRes.data;
      }

      // 刷新列表
      loadCommentList();
    } else {
      message.error(res.msg || '回复失败');
    }
  } catch (error) {
    console.error('回复评论出错:', error);
    message.error('回复失败');
  } finally {
    submitting.value = false;
  }
};

// 查看详情
const handleViewDetail = async (record) => {
  try {
    const res = await getTooCommentInfo({ id: record.id });
    if (res.code === 200) {
      currentRecord.value = res.data;
      detailDrawerVisible.value = true;
    } else {
      message.error(res.msg || '获取评论详情失败');
    }
  } catch (error) {
    console.error('获取评论详情出错:', error);
    message.error('获取评论详情失败');
  }
};

// 回复评论
const handleReply = (record) => {
  currentRecord.value = record;
  replyForm.parent_id = record.id;
  replyForm.content = '';
  replyDrawerVisible.value = true;
};

// 提交回复
const submitReply = async () => {
  if (!replyForm.content.trim()) {
    message.warning('请输入回复内容');
    return;
  }

  submitting.value = true;
  try {
    const res = await replyToolComment({
      parent_id: replyForm.parent_id,
      content: replyForm.content
    });

    if (res.code === 200) {
      message.success('回复成功');
      replyDrawerVisible.value = false;
      loadCommentList();
    } else {
      message.error(res.msg || '回复失败');
    }
  } catch (error) {
    console.error('回复评论出错:', error);
    message.error('回复失败');
  } finally {
    submitting.value = false;
  }
};

// 通过评论
const handleApprove = (record) => {
  Modal.confirm({
    title: '确认通过',
    content: `确定要通过ID为 ${record.id} 的评论吗？`,
    okText: '确认',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await updateToolCommentStatus({ id: record.id, status: 'approved' });
        if (res.code === 200) {
          message.success('操作成功');
          loadCommentList();
        } else {
          message.error(res.msg || '操作失败');
        }
      } catch (error) {
        console.error('通过评论出错:', error);
        message.error('操作失败');
      }
    }
  });
};

// 拒绝评论
const handleReject = (record) => {
  Modal.confirm({
    title: '确认拒绝',
    content: `确定要拒绝ID为 ${record.id} 的评论吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await updateToolCommentStatus({ id: record.id, status: 'rejected' });
        if (res.code === 200) {
          message.success('操作成功');
          loadCommentList();
        } else {
          message.error(res.msg || '操作失败');
        }
      } catch (error) {
        console.error('拒绝评论出错:', error);
        message.error('操作失败');
      }
    }
  });
};

// 删除评论
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除ID为 ${record.id} 的评论吗？${record.parent_id === 0 ? '这将同时删除所有回复！' : ''}`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await deleteToolComment({ id: record.id });
        if (res.code === 200) {
          message.success('删除成功');
          loadCommentList();
        } else {
          message.error(res.msg || '删除失败');
        }
      } catch (error) {
        console.error('删除评论出错:', error);
        message.error('删除失败');
      }
    }
  });
};

// 批量通过
const handleBatchApprove = () => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('请至少选择一条记录');
    return;
  }

  Modal.confirm({
    title: '确认批量通过',
    content: `确定要通过选中的 ${selectedRowKeys.value.length} 条评论吗？`,
    okText: '确认',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchUpdateToolCommentStatus({ ids: selectedRowKeys.value, status: 'approved' });
        if (res.code === 200) {
          message.success('批量通过成功');
          selectedRowKeys.value = [];
          loadCommentList();
        } else {
          message.error(res.msg || '批量通过失败');
        }
      } catch (error) {
        console.error('批量通过评论出错:', error);
        message.error('批量通过失败');
      }
    }
  });
};

// 批量拒绝
const handleBatchReject = () => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('请至少选择一条记录');
    return;
  }

  Modal.confirm({
    title: '确认批量拒绝',
    content: `确定要拒绝选中的 ${selectedRowKeys.value.length} 条评论吗？`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchUpdateToolCommentStatus({ ids: selectedRowKeys.value, status: 'rejected' });
        if (res.code === 200) {
          message.success('批量拒绝成功');
          selectedRowKeys.value = [];
          loadCommentList();
        } else {
          message.error(res.msg || '批量拒绝失败');
        }
      } catch (error) {
        console.error('批量拒绝评论出错:', error);
        message.error('批量拒绝失败');
      }
    }
  });
};

// 批量删除
const handleBatchDelete = () => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('请至少选择一条记录');
    return;
  }

  Modal.confirm({
    title: '确认批量删除',
    content: `确定要删除选中的 ${selectedRowKeys.value.length} 条评论吗？这可能会同时删除相关的回复评论！`,
    okText: '确认',
    okType: 'danger',
    cancelText: '取消',
    async onOk() {
      try {
        const res = await batchDeleteToolComment({ ids: selectedRowKeys.value });
        if (res.data.code === 200) {
          message.success('批量删除成功');
          selectedRowKeys.value = [];
          loadCommentList();
        } else {
          message.error(res.data.msg || '批量删除失败');
        }
      } catch (error) {
        console.error('批量删除评论出错:', error);
        message.error('批量删除失败');
      }
    }
  });
};

// 导出数据
const exportData = () => {
  if (commentList.value.length === 0) {
    message.warning('没有数据可导出');
    return;
  }

  message.info('请先安装xlsx库: npm install xlsx --save');

  // 以下代码需要安装xlsx库后才能使用
  /*
  // 准备导出数据
  const exportData = commentList.value.map(item => ({
    'ID': item.id,
    '工具名称': item.tool_name,
    '用户名称': item.user_nickname || '未知用户',
    '用户类型': item.user_type === 'admin' ? '管理员' : '普通用户',
    '评论类型': item.parent_id > 0 ? '回复评论' : '主评论',
    '评论内容': item.content,
    '评分': item.rating > 0 ? item.rating : '无',
    '状态': item.status === 'pending' ? '待审核' : (item.status === 'approved' ? '已通过' : '已拒绝'),
    '回复数': item.reply_count || 0,
    '评论时间': formatDateTime(item.create_time)
  }));

  // 创建Excel工作表
  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'AI工具评论数据');

  // 生成文件名
  const fileName = `AI工具评论数据_${dayjs().format('YYYYMMDD_HHmmss')}.xlsx`;

  // 导出文件
  XLSX.writeFile(workbook, fileName);
  message.success(`数据导出成功，文件名：${fileName}`);
  */
};

// 统计数据对话框
const statisticsVisible = ref(false);
const statisticsChartRef = ref(null);
let statisticsChart = null;

// 显示统计数据
const showStatistics = () => {
  statisticsVisible.value = true;

  // 使用nextTick确保DOM已经渲染
  nextTick(() => {
    renderStatisticsChart();
  });
};

// 渲染统计图表
const renderStatisticsChart = () => {
  if (!statisticsChartRef.value) return;

  if (!statisticsChart) {
    statisticsChart = echarts.init(statisticsChartRef.value);
  }

  // 准备状态统计数据
  const statusData = [
    { name: '待审核', value: pendingCount.value },
    { name: '已通过', value: approvedCount.value },
    { name: '已拒绝', value: rejectedCount.value }
  ];

  // 准备评分统计数据
  const ratingCounts = [0, 0, 0, 0, 0, 0]; // 对应0-5星的计数

  commentList.value.forEach(item => {
    if (item.rating >= 0 && item.rating <= 5) {
      // 处理半星评分
      const ratingIndex = Math.round(item.rating);
      ratingCounts[ratingIndex]++;
    }
  });

  const option = {
    title: {
      text: 'AI工具评论统计',
      left: 'center'
    },
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left',
      data: ['待审核', '已通过', '已拒绝']
    },
    series: [
      {
        name: '评论状态',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2
        },
        label: {
          show: false,
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: '18',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: false
        },
        data: [
          { value: pendingCount.value, name: '待审核', itemStyle: { color: '#faad14' } },
          { value: approvedCount.value, name: '已通过', itemStyle: { color: '#52c41a' } },
          { value: rejectedCount.value, name: '已拒绝', itemStyle: { color: '#f5222d' } }
        ]
      }
    ]
  };

  statisticsChart.setOption(option);

  // 监听窗口大小变化，调整图表大小
  window.addEventListener('resize', () => {
    statisticsChart && statisticsChart.resize();
  });
};

// 页面加载时获取数据
onMounted(() => {
  // 设置默认排序
  queryParams.order_field = 'create_time';
  queryParams.order_type = 'desc';

  // 初始化页面
  initPage();
});

// 初始化页面
const initPage = async () => {
  // 加载评论列表
  loadCommentList();
};
</script>

<style scoped>
.ai-tool-comment-container {
  padding: 16px;
}

.mb-4 {
  margin-bottom: 16px;
}

.mt-4 {
  margin-top: 16px;
}

.ml-2 {
  margin-left: 8px;
}

.tool-info {
  display: flex;
  align-items: center;
}

/* 统计卡片样式 */
.stat-card {
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;
  height: 100%;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.stat-card-content {
  text-align: center;
  padding: 16px 0;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: #1890ff;
}

.stat-label {
  font-size: 14px;
  color: #666;
  margin-top: 8px;
}

/* 搜索表单样式 */
.search-form {
  background-color: #f5f5f5;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.search-buttons {
  display: flex;
  align-items: flex-end;
}

/* 评论内容样式 */
.comment-content {
  display: flex;
  flex-direction: column;
}

.comment-text {
  margin-bottom: 4px;
}

.comment-tags {
  display: flex;
  gap: 8px;
}

.clickable-tag {
  cursor: pointer;
}

.comment-full-content {
  white-space: pre-wrap;
  word-break: break-word;
  background-color: #f9f9f9;
  padding: 12px;
  border-radius: 4px;
  border-left: 4px solid #1890ff;
}

/* 表格底部样式 */
.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
}

.table-footer a {
  color: #1890ff;
  font-weight: bold;
}

/* 详情抽屉样式 */
.parent-comment-card, .original-comment-card {
  border-radius: 8px;
  background-color: #f9f9f9;
}

.original-comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.original-comment-user, .parent-comment-user {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.original-comment-content, .parent-comment-content {
  white-space: pre-wrap;
  word-break: break-word;
  padding: 8px 0;
}

.original-comment-footer {
  margin-top: 8px;
  border-top: 1px dashed #e8e8e8;
  padding-top: 8px;
}

/* 回复列表样式 */
.reply-list {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 8px;
}

.reply-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.reply-user {
  font-weight: bold;
}

.reply-time {
  margin-left: auto;
  color: #999;
  font-size: 12px;
}

.reply-content {
  margin: 8px 0;
}

.reply-status {
  margin-top: 4px;
  margin-bottom: 8px;
}

.reply-actions {
  display: flex;
  gap: 8px;
}

/* 快捷回复模板 */
.template-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.template-item {
  margin-bottom: 8px;
}

/* 操作按钮样式 */
.action-buttons {
  margin-top: 8px;
  margin-bottom: 16px;
}

@media (max-width: 768px) {
  .search-buttons {
    margin-top: 16px;
  }

  .stat-card {
    margin-bottom: 16px;
  }

  .template-list {
    flex-direction: column;
  }

  .action-buttons {
    justify-content: flex-start !important;
  }
}

/* 分页容器样式 */
.pagination-container {
  margin-top: 16px;
  text-align: right;
}

/* 暗黑模式下的表格样式修复 */
.feature-button,
.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 表格在暗黑模式下的样式 - 只调整表格颜色，不改变图标和按钮 */
:global(.dark-theme) .ant-table,
:global(.system-dark-theme) .ant-table {
  background-color: transparent;
}

:global(.dark-theme) .ant-table-thead > tr > th,
:global(.system-dark-theme) .ant-table-thead > tr > th {
  background-color: rgba(255, 255, 255, 0.04);
  color: rgba(255, 255, 255, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.09);
}

:global(.dark-theme) .ant-table-tbody > tr > td,
:global(.system-dark-theme) .ant-table-tbody > tr > td {
  background-color: transparent;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-table-tbody > tr:hover > td,
:global(.system-dark-theme) .ant-table-tbody > tr:hover > td {
  background-color: rgba(255, 255, 255, 0.04);
}

/* 修复暗黑模式下分页控件的样式 */
:global(.dark-theme) .ant-pagination,
:global(.system-dark-theme) .ant-pagination {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-pagination-item,
:global(.system-dark-theme) .ant-pagination-item {
  background-color: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
}

:global(.dark-theme) .ant-pagination-item a,
:global(.system-dark-theme) .ant-pagination-item a {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .ant-pagination-item-active,
:global(.system-dark-theme) .ant-pagination-item-active {
  background-color: #1890ff;
  border-color: #1890ff;
}

:global(.dark-theme) .ant-pagination-item-active a,
:global(.system-dark-theme) .ant-pagination-item-active a {
  color: white;
}
</style>
