<template>
  <el-dialog
    v-model="visible"
    :title="title"
    :width="width"
    :close-on-click-modal="false"
    :close-on-press-escape="true"
    :show-close="true"
    :center="true"
    class="custom-confirm-dialog"
    @close="handleClose"
  >
    <div class="confirm-content">
      <div class="confirm-icon" :class="type">
        <el-icon v-if="type === 'success'"><CircleCheckFilled /></el-icon>
        <el-icon v-else-if="type === 'warning'"><WarningFilled /></el-icon>
        <el-icon v-else-if="type === 'error'"><CircleCloseFilled /></el-icon>
        <el-icon v-else-if="type === 'info'"><InfoFilled /></el-icon>
      </div>
      <div class="confirm-message">{{ message }}</div>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleCancel" :disabled="loading">
          {{ cancelButtonText }}
        </el-button>
        <el-button 
          :type="type === 'error' ? 'danger' : type" 
          @click="handleConfirm"
          :loading="loading"
        >
          {{ confirmButtonText }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script>
import { ref, defineComponent, watch } from 'vue'
import { CircleCheckFilled, WarningFilled, CircleCloseFilled, InfoFilled } from '@element-plus/icons-vue'

export default defineComponent({
  name: 'CustomConfirm',
  components: {
    CircleCheckFilled,
    WarningFilled,
    CircleCloseFilled,
    InfoFilled
  },
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: '确认'
    },
    message: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'warning',
      validator: (value) => ['success', 'warning', 'error', 'info'].includes(value)
    },
    width: {
      type: String,
      default: '400px'
    },
    confirmButtonText: {
      type: String,
      default: '确定'
    },
    cancelButtonText: {
      type: String,
      default: '取消'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'confirm', 'cancel', 'close'],
  setup(props, { emit }) {
    const visible = ref(props.modelValue)

    watch(() => props.modelValue, (val) => {
      visible.value = val
    })

    watch(() => visible.value, (val) => {
      emit('update:modelValue', val)
    })

    const handleConfirm = () => {
      emit('confirm')
    }

    const handleCancel = () => {
      emit('cancel')
    }

    const handleClose = () => {
      emit('close')
    }

    return {
      visible,
      handleConfirm,
      handleCancel,
      handleClose
    }
  }
})
</script>

<style lang="scss" scoped>
.custom-confirm-dialog {
  :deep(.el-dialog__header) {
    margin-right: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--el-border-color-lighter);
  }

  :deep(.el-dialog__body) {
    padding: 20px;
  }

  :deep(.el-dialog__footer) {
    padding: 10px 20px 20px;
    border-top: 1px solid var(--el-border-color-lighter);
  }
}

.confirm-content {
  display: flex;
  align-items: flex-start;
  padding: 10px 0;
}

.confirm-icon {
  margin-right: 15px;
  font-size: 24px;
  
  &.success {
    color: var(--el-color-success);
  }
  
  &.warning {
    color: var(--el-color-warning);
  }
  
  &.error {
    color: var(--el-color-danger);
  }
  
  &.info {
    color: var(--el-color-info);
  }
}

.confirm-message {
  flex: 1;
  font-size: 16px;
  line-height: 1.5;
  color: var(--el-text-color-primary);
}

.dialog-footer {
  display: flex;
  justify-content: center;
  gap: 12px;
}
</style> 