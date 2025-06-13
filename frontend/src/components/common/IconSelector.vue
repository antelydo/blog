<template>
  <div class="icon-selector" @scroll="handleScroll" ref="selectorRef">
    <div class="icon-search-container" :class="{ 'search-scrolled': isScrolled, 'search-hidden': searchHidden }">
      <div class="search-toggle" v-if="isScrolled" @click="toggleSearchVisibility">
        <up-outlined v-if="searchHidden" />
        <down-outlined v-else />
      </div>
      <div class="search-content" :class="{ 'search-content-hidden': searchHidden }">
        <div class="icon-help-text" v-if="!searchText">
          {{ $t('common.iconSelectHelp') }}
        </div>
        <a-input-search
          v-model:value="searchText"
          :placeholder="$t('common.searchIcon')"
          style="margin-bottom: 8px"
          @change="handleSearch"
          @pressEnter="handleSearch"
          :allowClear="true"
          size="large"
        >
          <template #enterButton>
            <search-outlined />
          </template>
        </a-input-search>
      </div>
    </div>

    <a-tabs v-model:activeKey="activeCategory">
      <a-tab-pane v-for="category in filteredCategories" :key="category.key" :tab="$t(`common.iconCategories.${category.key}`) || category.name">
        <div class="category-description" v-if="!searchText && category.description">
          {{ $t(`common.iconCategoryDescriptions.${category.key}`) || category.description }}
        </div>

        <div v-if="category.filteredIcons.length === 0" class="empty-result">
          <inbox-outlined />
          <p>{{ $t('common.noIconsFound') }}</p>
        </div>

        <template v-else>
          <div class="icon-grid">
            <div
              v-for="iconName in paginatedIcons(category.filteredIcons)"
              :key="iconName"
              class="icon-item"
              :class="{ 'icon-selected': iconName === modelValue }"
              @click="handleSelect(iconName)"
              tabindex="0"
              @keydown.enter="handleSelect(iconName)"
              @keydown.space.prevent="handleSelect(iconName)"
            >
              <div class="icon-wrapper">
                <component :is="getIconComponent(iconName)" />
              </div>
              <div class="icon-name">{{ iconName }}</div>
            </div>
          </div>

          <div class="pagination-container" v-if="category.filteredIcons.length > pageSize">
            <a-pagination
              v-model:current="currentPage"
              :total="category.filteredIcons.length"
              :pageSize="pageSize"
              :showSizeChanger="false"
              :showQuickJumper="true"
              size="default"
              :showTotal="total => $t('common.iconPagination.total', { total })"
            >
              <template #buildOptionText="props">
                <span>{{ props.value }}</span>
              </template>
            </a-pagination>
            <div class="pagination-info">
              {{ $t('common.iconPagination.page', { current: currentPage, total: Math.ceil(category.filteredIcons.length / pageSize) }) }}
            </div>
          </div>
        </template>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { iconCategories } from '@/assets/icon/iconData';
import * as AntIcons from '@ant-design/icons-vue';
import { SearchOutlined, InboxOutlined, UpOutlined, DownOutlined } from '@ant-design/icons-vue';

// 初始化国际化
const { t } = useI18n();

// 处理滚动事件
const handleScroll = () => {
  if (scrollTimer.value) {
    clearTimeout(scrollTimer.value);
  }

  // 检查滚动位置
  if (selectorRef.value) {
    isScrolled.value = selectorRef.value.scrollTop > 20;
  }

  // 如果向上滚动，显示搜索框
  if (selectorRef.value && selectorRef.value.scrollTop < 10) {
    searchHidden.value = false;
  }
};

// 切换搜索框可见性
const toggleSearchVisibility = () => {
  searchHidden.value = !searchHidden.value;

  // 如果显示搜索框，滚动到顶部
  if (!searchHidden.value && selectorRef.value) {
    selectorRef.value.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// 调试国际化
console.log('[IconSelector] 国际化测试:', {
  'common.selectIcon': t('common.selectIcon'),
  'common.iconCategories.direction': t('common.iconCategories.direction'),
  'common.iconCategoryDescriptions.direction': t('common.iconCategoryDescriptions.direction'),
  'common.iconCategories.ui': t('common.iconCategories.ui'),
  'common.iconCategories.weather': t('common.iconCategories.weather'),
  'common.iconCategories.communication': t('common.iconCategories.communication'),
  'common.iconCategories.business': t('common.iconCategories.business')
});

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const searchText = ref('');
const activeCategory = ref('common'); // 默认显示通用图标
const currentPage = ref(1);
const pageSize = 60; // 每页显示的图标数量
const isScrolled = ref(false); // 是否已滚动
const searchHidden = ref(false); // 搜索框是否隐藏
const selectorRef = ref(null); // 选择器DOM引用
const scrollTimer = ref(null); // 滚动计时器

// 根据图标名称获取图标组件
const getIconComponent = (iconName) => {
  // 使用缓存机制提高性能
  if (!iconComponentCache[iconName]) {
    iconComponentCache[iconName] = AntIcons[iconName] || null;
  }
  return iconComponentCache[iconName];
};

// 图标组件缓存
const iconComponentCache = {};

// 过滤图标
const filteredCategories = computed(() => {
  // 重置分页到第一页
  if (searchText.value) {
    currentPage.value = 1;
  }

  return iconCategories.map(category => {
    const filteredIcons = category.icons.filter(iconName => {
      if (!searchText.value) return true;
      return iconName.toLowerCase().includes(searchText.value.toLowerCase());
    });

    return {
      ...category,
      filteredIcons
    };
  }).filter(category => category.filteredIcons.length > 0);
});

// 处理搜索
const handleSearch = () => {
  // 重置分页
  currentPage.value = 1;

  // 如果有搜索结果，自动切换到第一个有结果的分类
  if (filteredCategories.value.length > 0) {
    activeCategory.value = filteredCategories.value[0].key;
  }
};

// 处理选择图标
const handleSelect = (iconName) => {
  emit('update:modelValue', iconName);
};

// 分页处理
const paginatedIcons = (icons) => {
  const startIndex = (currentPage.value - 1) * pageSize;
  const endIndex = startIndex + pageSize;
  return icons.slice(startIndex, endIndex);
};

// 当选中的图标变化时，自动切换到对应的分类
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    for (const category of iconCategories) {
      if (category.icons.includes(newValue)) {
        activeCategory.value = category.key;

        // 计算图标在哪一页并跳转到该页
        const iconIndex = category.icons.indexOf(newValue);
        if (iconIndex !== -1) {
          currentPage.value = Math.floor(iconIndex / pageSize) + 1;
        }
        break;
      }
    }
  }
}, { immediate: true });

// 当切换分类时，重置分页
watch(() => activeCategory.value, () => {
  currentPage.value = 1;
});

// 组件挂载时，如果有默认值，自动切换到对应的分类
onMounted(() => {
  if (props.modelValue) {
    for (const category of iconCategories) {
      if (category.icons.includes(props.modelValue)) {
        activeCategory.value = category.key;

        // 计算图标在哪一页并跳转到该页
        const iconIndex = category.icons.indexOf(props.modelValue);
        if (iconIndex !== -1) {
          currentPage.value = Math.floor(iconIndex / pageSize) + 1;
        }
        break;
      }
    }
  }

  // 预加载常用图标以提高性能
  nextTick(() => {
    const commonIcons = ['SearchOutlined', 'SettingOutlined', 'UserOutlined', 'HomeOutlined', 'AppstoreOutlined'];
    commonIcons.forEach(iconName => {
      getIconComponent(iconName);
    });

    // 初始化滚动事件
    if (selectorRef.value) {
      // 添加滚动事件监听
      selectorRef.value.addEventListener('scroll', () => {
        // 使用节流函数减少滚动事件触发频率
        if (scrollTimer.value) {
          clearTimeout(scrollTimer.value);
        }
        scrollTimer.value = setTimeout(() => {
          handleScroll();
        }, 100);
      });

      // 初始检查滚动位置
      handleScroll();
    }
  });
});
</script>

<style scoped>
.icon-selector {
  width: 100%;
  height: 500px;
  overflow-y: auto;
  position: relative;
  padding: 0 8px;
}

.icon-search-container {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: inherit;
  padding: 8px 0;
  transition: all 0.3s ease;
  border-radius: 0 0 8px 8px;
}

.search-scrolled {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  margin-bottom: 8px;
  padding: 4px 8px;
  border-radius: 8px;
}

.search-hidden {
  transform: translateY(-100%);
  box-shadow: none;
  padding-top: 0;
  padding-bottom: 0;
  margin-bottom: 0;
}

.search-hidden .search-toggle {
  transform: translateY(100%);
}

.search-toggle {
  position: absolute;
  bottom: -24px;
  left: 50%;
  transform: translateX(-50%);
  width: 36px;
  height: 24px;
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: 0 0 8px 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 5;
  transition: all 0.3s ease;
}

.search-toggle:hover {
  background-color: #e6f7ff;
}

.search-content {
  transition: all 0.3s ease;
  max-height: 120px;
  overflow: hidden;
}

.search-content-hidden {
  max-height: 0;
  opacity: 0;
  margin: 0;
  padding: 0;
}

.category-description {
  margin-bottom: 16px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 14px;
  line-height: 1.5;
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
  gap: 12px;
  padding: 16px 0;
}

.icon-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 12px 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid transparent;
  outline: none;
}

.icon-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.icon-item:focus {
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.icon-selected {
  background-color: #e6f7ff;
  border: 1px solid #1890ff;
}

.icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  height: 40px;
  width: 40px;
  margin-bottom: 8px;
}

.icon-name {
  font-size: 12px;
  text-align: center;
  word-break: break-all;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  height: 36px;
}

.pagination-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 16px;
  margin-bottom: 16px;
}

.pagination-info {
  margin-top: 8px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 13px;
}

.icon-help-text {
  margin-bottom: 12px;
  color: rgba(0, 0, 0, 0.65);
  font-size: 14px;
  text-align: center;
  padding: 8px;
  background-color: rgba(0, 0, 0, 0.02);
  border-radius: 4px;
}

.empty-result {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 0;
  color: rgba(0, 0, 0, 0.25);
}

.empty-result > span {
  font-size: 48px;
  margin-bottom: 16px;
}

/* 暗黑模式适配 */
:global(.dark-theme) .category-description,
:global(.system-dark-theme) .category-description {
  color: rgba(255, 255, 255, 0.45);
}

:global(.dark-theme) .icon-item:hover,
:global(.system-dark-theme) .icon-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

:global(.dark-theme) .icon-selected,
:global(.system-dark-theme) .icon-selected {
  background-color: rgba(24, 144, 255, 0.2);
  border: 1px solid #1890ff;
}

:global(.dark-theme) .empty-result,
:global(.system-dark-theme) .empty-result {
  color: rgba(255, 255, 255, 0.25);
}

:global(.dark-theme) .pagination-info,
:global(.system-dark-theme) .pagination-info {
  color: rgba(255, 255, 255, 0.45);
}

:global(.dark-theme) .icon-help-text,
:global(.system-dark-theme) .icon-help-text {
  color: rgba(255, 255, 255, 0.65);
  background-color: rgba(255, 255, 255, 0.04);
}

:global(.dark-theme) .search-scrolled,
:global(.system-dark-theme) .search-scrolled {
  background-color: rgba(0, 0, 0, 0.85);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

:global(.dark-theme) .search-toggle,
:global(.system-dark-theme) .search-toggle {
  background-color: rgba(0, 0, 0, 0.85);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

:global(.dark-theme) .search-toggle:hover,
:global(.system-dark-theme) .search-toggle:hover {
  background-color: rgba(24, 144, 255, 0.2);
}

/* 响应式调整 */
@media (max-width: 768px) {
  .icon-grid {
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 8px;
  }

  .icon-wrapper {
    font-size: 20px;
    height: 32px;
    width: 32px;
  }

  .icon-name {
    font-size: 11px;
  }
}
</style>
