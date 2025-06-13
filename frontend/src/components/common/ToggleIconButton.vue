
<!-- 切换图标按钮 -->
<template>
  <el-button 
    :class="['toggle-icon', { 'is-active': modelValue }]" 
    :type="type" 
    :text="true"
    @click="toggleValue"
    v-bind="$attrs"
  >
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
  }
});

const emit = defineEmits(['update:modelValue']);

const toggleValue = () => {
  emit('update:modelValue', !props.modelValue);
};
</script>

<style scoped>
.toggle-icon {
  transition: all 0.3s;
}

.toggle-icon.is-active {
  transform: scale(1.1);
}
</style> 