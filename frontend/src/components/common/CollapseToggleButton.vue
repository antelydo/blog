<!-- 折叠切换按钮 -->
<template>
  <el-button 
    :class="['toggle-icon', 'collapse-toggle', { 'is-collapsed': isCollapsed }]" 
    :type="type" 
    :text="true"
    @click="toggleCollapse"
    v-bind="$attrs"
  >
    <el-icon :class="{ 'rotate-icon': !isCollapsed }">
      <component :is="icon" />
    </el-icon>
    <slot></slot>
  </el-button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'default'
  },
  icon: {
    type: String,
    default: 'ArrowDown'
  }
});

const emit = defineEmits(['update:modelValue']);

const isCollapsed = computed(() => props.modelValue);

const toggleCollapse = () => {
  emit('update:modelValue', !props.modelValue);
};
</script>

<style scoped>
.collapse-toggle {
  transition: all 0.3s;
}

.rotate-icon {
  transform: rotate(180deg);
  transition: transform 0.3s;
}

/* 确保图标大小一致 */
.el-icon {
  font-size: 16px;
  transition: transform 0.3s;
}

/* 图标和文本之间的间距 */
.collapse-toggle :deep(.el-icon) + span {
  margin-left: 4px;
}
</style> 