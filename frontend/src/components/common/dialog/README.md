# 自定义弹窗组件

本项目提供了一套自定义的弹窗组件，包括 Alert、Confirm 和 Modal，以提供更好的用户体验和视觉效果。

## 组件列表

- `Alert.vue`: 提示弹窗，只有一个确认按钮
- `Confirm.vue`: 确认弹窗，有确认和取消两个按钮
- `Modal.vue`: 模态弹窗，可以自定义内容

## 使用方法

### 方式一：直接使用组件

```vue
<template>
  <div>
    <custom-alert
      v-model="alertVisible"
      title="提示"
      message="这是一个提示信息"
      type="success"
      @confirm="handleAlertConfirm"
    />
    
    <custom-confirm
      v-model="confirmVisible"
      title="确认"
      message="确定要执行此操作吗？"
      type="warning"
      @confirm="handleConfirmConfirm"
      @cancel="handleConfirmCancel"
    />
    
    <custom-modal
      v-model="modalVisible"
      title="模态框"
      width="600px"
      @confirm="handleModalConfirm"
      @cancel="handleModalCancel"
    >
      <div>这里是模态框的内容</div>
    </custom-modal>
    
    <el-button @click="showAlert">显示提示</el-button>
    <el-button @click="showConfirm">显示确认</el-button>
    <el-button @click="showModal">显示模态框</el-button>
  </div>
</template>

<script>
import { ref } from 'vue'
import CustomAlert from './Alert.vue'
import CustomConfirm from './Confirm.vue'
import CustomModal from './Modal.vue'

export default {
  components: {
    CustomAlert,
    CustomConfirm,
    CustomModal
  },
  setup() {
    const alertVisible = ref(false)
    const confirmVisible = ref(false)
    const modalVisible = ref(false)
    
    const showAlert = () => {
      alertVisible.value = true
    }
    
    const showConfirm = () => {
      confirmVisible.value = true
    }
    
    const showModal = () => {
      modalVisible.value = true
    }
    
    const handleAlertConfirm = () => {
      console.log('Alert confirmed')
    }
    
    const handleConfirmConfirm = () => {
      console.log('Confirm confirmed')
    }
    
    const handleConfirmCancel = () => {
      console.log('Confirm cancelled')
    }
    
    const handleModalConfirm = () => {
      console.log('Modal confirmed')
    }
    
    const handleModalCancel = () => {
      console.log('Modal cancelled')
    }
    
    return {
      alertVisible,
      confirmVisible,
      modalVisible,
      showAlert,
      showConfirm,
      showModal,
      handleAlertConfirm,
      handleConfirmConfirm,
      handleConfirmCancel,
      handleModalConfirm,
      handleModalCancel
    }
  }
}
</script>
```

### 方式二：使用工具函数（推荐）

```vue
<template>
  <div>
    <el-button @click="showSuccessAlert">成功提示</el-button>
    <el-button @click="showErrorAlert">错误提示</el-button>
    <el-button @click="showWarningAlert">警告提示</el-button>
    <el-button @click="showInfoAlert">信息提示</el-button>
    <el-button @click="showSuccessConfirm">成功确认</el-button>
    <el-button @click="showErrorConfirm">错误确认</el-button>
    <el-button @click="showWarningConfirm">警告确认</el-button>
    <el-button @click="showInfoConfirm">信息确认</el-button>
    <el-button @click="showModal">显示模态框</el-button>
  </div>
</template>

<script>
import { 
  successAlert, 
  errorAlert, 
  warningAlert, 
  infoAlert,
  successConfirm,
  errorConfirm,
  warningConfirm,
  infoConfirm,
  modal
} from '@/utils/dialog'

export default {
  setup() {
    const showSuccessAlert = async () => {
      await successAlert('操作成功完成')
    }
    
    const showErrorAlert = async () => {
      await errorAlert('操作失败，请重试')
    }
    
    const showWarningAlert = async () => {
      await warningAlert('请注意此操作的风险')
    }
    
    const showInfoAlert = async () => {
      await infoAlert('这是一条提示信息')
    }
    
    const showSuccessConfirm = async () => {
      const confirmed = await successConfirm('确定要执行此操作吗？')
      if (confirmed) {
        console.log('用户确认了操作')
      } else {
        console.log('用户取消了操作')
      }
    }
    
    const showErrorConfirm = async () => {
      const confirmed = await errorConfirm('此操作不可恢复，确定要继续吗？')
      if (confirmed) {
        console.log('用户确认了危险操作')
      } else {
        console.log('用户取消了危险操作')
      }
    }
    
    const showWarningConfirm = async () => {
      const confirmed = await warningConfirm('确定要删除此项目吗？')
      if (confirmed) {
        console.log('用户确认了删除操作')
      } else {
        console.log('用户取消了删除操作')
      }
    }
    
    const showInfoConfirm = async () => {
      const confirmed = await infoConfirm('确定要查看详细信息吗？')
      if (confirmed) {
        console.log('用户确认了查看操作')
      } else {
        console.log('用户取消了查看操作')
      }
    }
    
    const showModal = async () => {
      const confirmed = await modal({
        title: '自定义模态框',
        width: '600px',
        content: '<div>这里是模态框的内容</div>',
        confirmButtonText: '提交',
        cancelButtonText: '关闭'
      })
      
      if (confirmed) {
        console.log('用户点击了提交按钮')
      } else {
        console.log('用户点击了关闭按钮或关闭了模态框')
      }
    }
    
    return {
      showSuccessAlert,
      showErrorAlert,
      showWarningAlert,
      showInfoAlert,
      showSuccessConfirm,
      showErrorConfirm,
      showWarningConfirm,
      showInfoConfirm,
      showModal
    }
  }
}
</script>
```

## 组件属性

### Alert 组件

| 属性名 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| modelValue | Boolean | false | 是否显示弹窗 |
| title | String | '提示' | 弹窗标题 |
| message | String | '' | 弹窗内容 |
| type | String | 'info' | 弹窗类型，可选值：'success', 'warning', 'error', 'info' |
| width | String | '400px' | 弹窗宽度 |
| confirmButtonText | String | '确定' | 确认按钮文本 |
| loading | Boolean | false | 确认按钮是否显示加载状态 |

### Confirm 组件

| 属性名 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| modelValue | Boolean | false | 是否显示弹窗 |
| title | String | '确认' | 弹窗标题 |
| message | String | '' | 弹窗内容 |
| type | String | 'warning' | 弹窗类型，可选值：'success', 'warning', 'error', 'info' |
| width | String | '400px' | 弹窗宽度 |
| confirmButtonText | String | '确定' | 确认按钮文本 |
| cancelButtonText | String | '取消' | 取消按钮文本 |
| loading | Boolean | false | 确认按钮是否显示加载状态 |

### Modal 组件

| 属性名 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| modelValue | Boolean | false | 是否显示弹窗 |
| title | String | '' | 弹窗标题 |
| width | String | '50%' | 弹窗宽度 |
| closeOnClickModal | Boolean | false | 是否可以通过点击遮罩层关闭弹窗 |
| closeOnPressEscape | Boolean | true | 是否可以通过按下 ESC 键关闭弹窗 |
| showClose | Boolean | true | 是否显示关闭按钮 |
| center | Boolean | true | 是否居中显示弹窗 |
| fullscreen | Boolean | false | 是否全屏显示弹窗 |
| draggable | Boolean | true | 是否可拖拽弹窗 |
| destroyOnClose | Boolean | false | 关闭弹窗时是否销毁内容 |
| lockScroll | Boolean | true | 是否锁定滚动 |
| appendToBody | Boolean | true | 是否将弹窗插入到 body 元素 |
| customClass | String | '' | 自定义类名 |
| showFooter | Boolean | true | 是否显示底部按钮 |
| confirmButtonText | String | '确定' | 确认按钮文本 |
| cancelButtonText | String | '取消' | 取消按钮文本 |
| confirmButtonType | String | 'primary' | 确认按钮类型 |
| loading | Boolean | false | 确认按钮是否显示加载状态 |

## 事件

### Alert 组件

| 事件名 | 说明 | 回调参数 |
| --- | --- | --- |
| update:modelValue | 弹窗显示状态变化时触发 | (value: boolean) |
| confirm | 点击确认按钮时触发 | - |
| close | 弹窗关闭时触发 | - |

### Confirm 组件

| 事件名 | 说明 | 回调参数 |
| --- | --- | --- |
| update:modelValue | 弹窗显示状态变化时触发 | (value: boolean) |
| confirm | 点击确认按钮时触发 | - |
| cancel | 点击取消按钮时触发 | - |
| close | 弹窗关闭时触发 | - |

### Modal 组件

| 事件名 | 说明 | 回调参数 |
| --- | --- | --- |
| update:modelValue | 弹窗显示状态变化时触发 | (value: boolean) |
| confirm | 点击确认按钮时触发 | - |
| cancel | 点击取消按钮时触发 | - |
| close | 弹窗关闭时触发 | - |
| open | 弹窗打开时触发 | - |

## 插槽

### Modal 组件

| 插槽名 | 说明 |
| --- | --- |
| default | 弹窗内容 |
| header | 弹窗头部 |
| footer | 弹窗底部 |
``` 