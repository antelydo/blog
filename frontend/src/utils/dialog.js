import { createVNode, render } from 'vue'
import CustomAlert from '@/components/common/dialog/Alert.vue'
import CustomConfirm from '@/components/common/dialog/Confirm.vue'
import CustomModal from '@/components/common/dialog/Modal.vue'

// 创建弹窗容器
const createContainer = () => {
  const container = document.createElement('div')
  document.body.appendChild(container)
  return container
}

// 销毁弹窗
const destroyDialog = (container) => {
  render(null, container)
  container.remove()
}

// Alert弹窗
export const alert = (options) => {
  return new Promise((resolve) => {
    const container = createContainer()
    const close = () => {
      destroyDialog(container)
    }
    
    const vnode = createVNode(CustomAlert, {
      modelValue: true,
      title: options.title || '提示',
      message: options.message || '',
      type: options.type || 'info',
      width: options.width || '400px',
      confirmButtonText: options.confirmButtonText || '确定',
      loading: options.loading || false,
      'onUpdate:modelValue': (val) => {
        if (!val) close()
      },
      onConfirm: () => {
        close()
        resolve(true)
      },
      onClose: () => {
        close()
        resolve(false)
      }
    })
    
    render(vnode, container)
  })
}

// Confirm弹窗
export const confirm = (options) => {
  return new Promise((resolve) => {
    const container = createContainer()
    const close = () => {
      destroyDialog(container)
    }
    
    const vnode = createVNode(CustomConfirm, {
      modelValue: true,
      title: options.title || '确认',
      message: options.message || '',
      type: options.type || 'warning',
      width: options.width || '400px',
      confirmButtonText: options.confirmButtonText || '确定',
      cancelButtonText: options.cancelButtonText || '取消',
      loading: options.loading || false,
      'onUpdate:modelValue': (val) => {
        if (!val) close()
      },
      onConfirm: () => {
        close()
        resolve(true)
      },
      onCancel: () => {
        close()
        resolve(false)
      },
      onClose: () => {
        close()
        resolve(false)
      }
    })
    
    render(vnode, container)
  })
}

// Modal弹窗
export const modal = (options) => {
  return new Promise((resolve) => {
    const container = createContainer()
    const close = () => {
      destroyDialog(container)
    }
    
    const vnode = createVNode(CustomModal, {
      modelValue: true,
      title: options.title || '',
      width: options.width || '50%',
      closeOnClickModal: options.closeOnClickModal || false,
      closeOnPressEscape: options.closeOnPressEscape || true,
      showClose: options.showClose || true,
      center: options.center || true,
      fullscreen: options.fullscreen || false,
      draggable: options.draggable || true,
      destroyOnClose: options.destroyOnClose || false,
      lockScroll: options.lockScroll || true,
      appendToBody: options.appendToBody || true,
      customClass: options.customClass || '',
      showFooter: options.showFooter !== false,
      confirmButtonText: options.confirmButtonText || '确定',
      cancelButtonText: options.cancelButtonText || '取消',
      confirmButtonType: options.confirmButtonType || 'primary',
      loading: options.loading || false,
      'onUpdate:modelValue': (val) => {
        if (!val) close()
      },
      onConfirm: () => {
        close()
        resolve(true)
      },
      onCancel: () => {
        close()
        resolve(false)
      },
      onClose: () => {
        close()
        resolve(false)
      },
      onOpen: () => {
        if (options.onOpen) options.onOpen()
      }
    }, {
      default: () => options.content,
      header: () => options.header,
      footer: () => options.footer
    })
    
    render(vnode, container)
  })
}

// 快捷方法
export const successAlert = (message, title = '成功') => {
  return alert({
    title,
    message,
    type: 'success'
  })
}

export const errorAlert = (message, title = '错误') => {
  return alert({
    title,
    message,
    type: 'error'
  })
}

export const warningAlert = (message, title = '警告') => {
  return alert({
    title,
    message,
    type: 'warning'
  })
}

export const infoAlert = (message, title = '提示') => {
  return alert({
    title,
    message,
    type: 'info'
  })
}

export const successConfirm = (message, title = '确认') => {
  return confirm({
    title,
    message,
    type: 'success'
  })
}

export const errorConfirm = (message, title = '确认') => {
  return confirm({
    title,
    message,
    type: 'error'
  })
}

export const warningConfirm = (message, title = '确认') => {
  return confirm({
    title,
    message,
    type: 'warning'
  })
}

export const infoConfirm = (message, title = '确认') => {
  return confirm({
    title,
    message,
    type: 'info'
  })
} 