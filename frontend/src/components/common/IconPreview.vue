<template>
  <div class="icon-preview">
    <div v-if="iconName" class="icon-preview-content">
      <component :is="iconComponent" class="preview-icon" />
      <div class="icon-name">{{ iconName }}</div>
    </div>
    <div v-else class="icon-preview-empty">
      <folder-outlined class="empty-icon" />
      <div class="empty-text">{{ $t('common.noIconSelected') }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import * as AntIcons from '@ant-design/icons-vue';
import { FolderOutlined } from '@ant-design/icons-vue';

const props = defineProps({
  iconName: {
    type: String,
    default: ''
  }
});

// 根据图标名称获取图标组件
const iconComponent = computed(() => {
  if (!props.iconName) return null;
  return AntIcons[props.iconName] || FolderOutlined;
});
</script>

<style scoped>
.icon-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100px;
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  margin-bottom: 16px;
  transition: all 0.3s;
}

.icon-preview:hover {
  border-color: #40a9ff;
}

.icon-preview-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.preview-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.icon-name {
  font-size: 14px;
  color: rgba(0, 0, 0, 0.65);
}

.icon-preview-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(0, 0, 0, 0.25);
}

.empty-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.empty-text {
  font-size: 14px;
}

/* 暗黑模式适配 */
:global(.dark-theme) .icon-preview,
:global(.system-dark-theme) .icon-preview {
  border-color: rgba(255, 255, 255, 0.15);
}

:global(.dark-theme) .icon-preview:hover,
:global(.system-dark-theme) .icon-preview:hover {
  border-color: #177ddc;
}

:global(.dark-theme) .icon-name,
:global(.system-dark-theme) .icon-name {
  color: rgba(255, 255, 255, 0.65);
}

:global(.dark-theme) .icon-preview-empty,
:global(.system-dark-theme) .icon-preview-empty {
  color: rgba(255, 255, 255, 0.25);
}
</style>
