<template>
  <el-dialog
    v-model="visible"
    :title="title"
    :width="width"
    :close-on-click-modal="closeOnClickModal"
    :close-on-press-escape="closeOnPressEscape"
    :show-close="showClose"
    :center="center"
    :fullscreen="fullscreen"
    :draggable="draggable"
    :destroy-on-close="destroyOnClose"
    :lock-scroll="lockScroll"
    :append-to-body="appendToBody"
    :class="['custom-modal-dialog', customClass]"
    @close="handleClose"
    @open="handleOpen"
  >
    <template #header v-if="$slots.header">
      <slot name="header"></slot>
    </template>
    
    <slot></slot>
    
    <template #footer v-if="$slots.footer || showFooter">
      <slot name="footer">
        <div class="dialog-footer">
          <el-button @click="handleCancel" :disabled="loading">
            {{ cancelButtonText }}
          </el-button>
          <el-button 
            :type="confirmButtonType" 
            @click="handleConfirm"
            :loading="loading"
          >
            {{ confirmButtonText }}
          </el-button>
        </div>
      </slot>
    </template>
  </el-dialog>
</template>

<script>
import { ref, defineComponent, watch } from 'vue'

export default defineComponent({
  name: 'CustomModal',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    width: {
      type: String,
      default: '50%'
    },
    closeOnClickModal: {
      type: Boolean,
      default: false
    },
    closeOnPressEscape: {
      type: Boolean,
      default: true
    },
    showClose: {
      type: Boolean,
      default: true
    },
    center: {
      type: Boolean,
      default: true
    },
    fullscreen: {
      type: Boolean,
      default: false
    },
    draggable: {
      type: Boolean,
      default: true
    },
    destroyOnClose: {
      type: Boolean,
      default: false
    },
    lockScroll: {
      type: Boolean,
      default: true
    },
    appendToBody: {
      type: Boolean,
      default: true
    },
    customClass: {
      type: String,
      default: ''
    },
    showFooter: {
      type: Boolean,
      default: true
    },
    confirmButtonText: {
      type: String,
      default: '确定'
    },
    cancelButtonText: {
      type: String,
      default: '取消'
    },
    confirmButtonType: {
      type: String,
      default: 'primary'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'confirm', 'cancel', 'close', 'open'],
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

    const handleOpen = () => {
      emit('open')
    }

    return {
      visible,
      handleConfirm,
      handleCancel,
      handleClose,
      handleOpen
    }
  }
})
</script>

<style lang="scss" scoped>
.custom-modal-dialog {
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

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
</style> 