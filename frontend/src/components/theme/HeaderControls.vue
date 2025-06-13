<!-- 头部控制组件 - 只包含语言切换 -->
<template>
  <div class="header-controls">
    <!-- 语言切换 -->
    <div class="control-item language-switch">
      <el-dropdown trigger="click" @command="handleLanguageChange">
        <el-button class="control-button language-button" circle>
          {{ currentLanguage }}
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="zh">
              <span>中文</span>
            </el-dropdown-item>
            <el-dropdown-item command="en">
              <span>English</span>
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLanguageStore } from '../../stores/language';

const { t } = useI18n();
const languageStore = useLanguageStore();

// 当前语言
const currentLanguage = computed(() => {
  return languageStore.language === 'zh' ? '中' : 'EN';
});

// 处理语言切换
const handleLanguageChange = (lang) => {
  languageStore.setLanguage(lang);
};
</script>

<style scoped>
.header-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.control-item {
  display: flex;
  align-items: center;
}

.control-button {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s;
}

.language-button {
  font-size: 12px;
}

.theme-button .el-icon {
  font-size: 16px;
}

@media (max-width: 768px) {
  .control-button {
    width: 28px;
    height: 28px;
  }

  .header-controls {
    gap: 4px;
  }
}
</style>
