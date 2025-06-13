<!-- 侧边栏示例 -->
<template>
  <div class="sidebar-sample">
    <div class="sidebar-header">
      <h3>{{ $t('layout.sidebarExample') }}</h3>
      <!-- 使用class方式添加主题跟随 -->
      <el-button class="theme-icon-button sidebar-toggle" text @click="toggleSidebar">
        <el-icon :class="{ 'is-active': collapsed }">
          <Fold v-if="collapsed" />
          <Expand v-else />
        </el-icon>
      </el-button>
    </div>
    
    <div class="sidebar-content" :class="{ 'is-collapsed': collapsed }">
      <!-- 使用CollapseToggleButton组件 -->
      <div class="sidebar-section">
        <div class="section-header">
          <collapse-toggle-button v-model="sections.dashboard" icon="Menu">
            {{ $t('layout.dashboard') }}
          </collapse-toggle-button>
        </div>
        <div v-show="!sections.dashboard" class="section-content">
          <div class="menu-item">{{ $t('layout.overview') }}</div>
        </div>
      </div>
      
      <div class="sidebar-section">
        <div class="section-header">
          <collapse-toggle-button v-model="sections.user" icon="User">
            {{ $t('layout.userManagement') }}
          </collapse-toggle-button>
        </div>
        <div v-show="!sections.user" class="section-content">
          <div class="menu-item">{{ $t('layout.userList') }}</div>
          <div class="menu-item">{{ $t('layout.userStatistics') }}</div>
        </div>
      </div>
      
      <div class="sidebar-section">
        <div class="section-header">
          <collapse-toggle-button v-model="sections.article" icon="Document">
            {{ $t('layout.articleManagement') }}
          </collapse-toggle-button>
        </div>
        <div v-show="!sections.article" class="section-content">
          <div class="menu-item">{{ $t('layout.articleList') }}</div>
          <div class="menu-item">{{ $t('layout.categoryManagement') }}</div>
          <div class="menu-item">{{ $t('layout.tagManagement') }}</div>
        </div>
      </div>
    </div>
    
    <!-- 底部折叠按钮 -->
    <div class="sidebar-footer">
      <el-button class="menu-collapse-button toggle-icon" text @click="toggleSidebarCollapse">
        <el-icon :class="{ 'rotate-icon': !sidebarCollapsed }"><ArrowLeft /></el-icon>
        {{ sidebarCollapsed ? '' : $t('layout.collapseMenu') }}
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Menu, User, Document, Fold, Expand, ArrowLeft } from '@element-plus/icons-vue';

// 侧边栏是否折叠
const collapsed = ref(false);
// 侧边栏整体是否折叠
const sidebarCollapsed = ref(false);

// 各部分折叠状态
const sections = reactive({
  dashboard: false,
  user: true,
  article: true
});

// 切换侧边栏
const toggleSidebar = () => {
  collapsed.value = !collapsed.value;
};

// 切换侧边栏折叠状态
const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};
</script>

<style scoped>
.sidebar-sample {
  width: 250px;
  min-height: 400px;
  border-radius: 8px;
  border: 1px solid var(--el-border-color);
  background-color: var(--el-bg-color);
  margin: 20px 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: width 0.3s;
}

.sidebar-header {
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--el-border-color-light);
}

.sidebar-header h3 {
  margin: 0;
  font-size: 16px;
}

.sidebar-content {
  flex: 1;
  overflow: auto;
  transition: all 0.3s;
}

.sidebar-content.is-collapsed {
  height: 0;
  overflow: hidden;
}

.sidebar-section {
  margin: 8px 0;
}

.section-header {
  padding: 0 16px;
}

.section-content {
  padding: 8px 16px 8px 32px;
  animation: fade-in 0.3s;
}

.menu-item {
  padding: 8px 0;
  color: var(--el-text-color-primary);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s;
}

.menu-item:hover {
  color: var(--toggle-icon-color);
}

.sidebar-footer {
  padding: 16px;
  border-top: 1px solid var(--el-border-color-light);
  display: flex;
  justify-content: center;
}

.rotate-icon {
  transform: rotate(180deg);
  transition: transform 0.3s;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style> 