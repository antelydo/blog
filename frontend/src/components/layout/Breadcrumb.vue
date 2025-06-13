
<!--- 面包屑导航组件 -->
<template>
  <el-breadcrumb separator="/" class="breadcrumb">
    <el-breadcrumb-item v-for="(item, index) in breadcrumbs" :key="index" :to="item.path">
      <el-icon v-if="index === 0"><HomeFilled /></el-icon>
      <span>{{ getTranslatedTitle(item) }}</span>
    </el-breadcrumb-item>
  </el-breadcrumb>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { HomeFilled } from '@element-plus/icons-vue'

const route = useRoute()
const { t } = useI18n()
const breadcrumbs = ref([])

// 获取翻译后的标题
const getTranslatedTitle = (item) => {
  const metaTitle = item.meta?.title || ''
  // 首页特殊处理
  if (metaTitle === '首页' || metaTitle === 'Home') {
    return t('breadcrumb.home')
  }
  
  if (!item.name) return metaTitle
  
  // 将首字母转为小写
  const routeName = item.name.charAt(0).toLowerCase() + item.name.slice(1)
  
  // 尝试使用面包屑翻译键
  const key = `breadcrumb.${routeName}`
  const translation = t(key)
  
  // 如果找不到翻译，返回原始标题
  return translation !== key ? translation : metaTitle
}

// 将kebab-case转换为camelCase
const kebabToCamel = (str) => {
  if (!str) return ''
  return str.replace(/-([a-z])/g, (g) => g[1].toLowerCase())
}

// 生成面包屑导航
const getBreadcrumbs = () => {
  // 获取当前路由的完整匹配数组
  const matched = route.matched.filter(item => item.meta && item.meta.title)
  
  // 如果没有匹配到路由，则显示首页
  if (!matched.length) {
    breadcrumbs.value = [{
      path: '/admin/dashboard',
      meta: { title: t('breadcrumb.home') },
      name: 'dashboard'
    }]
    return
  }
  
  // 生成面包屑数组
  const result = []
  
  // 添加首页
  result.push({
    path: '/admin/dashboard',
    meta: { title: t('breadcrumb.home') },
    name: 'dashboard'
  })
  
  // 添加当前路由的父级路由
  if (matched.length > 1) {
    // 添加父级路由（如果有）
    result.push({
      path: matched[0].path,
      meta: matched[0].meta,
      name: matched[0].name
    })
  }
  
  // 添加当前路由
  if (matched.length > 0) {
    const currentRoute = matched[matched.length - 1]
    result.push({
      path: currentRoute.path,
      meta: currentRoute.meta,
      name: currentRoute.name
    })
  }
  
  breadcrumbs.value = result
}

// 监听路由变化，更新面包屑
watch(() => route.path, getBreadcrumbs, { immediate: true })

// 监听语言变化，更新面包屑
watch(() => t.locale, getBreadcrumbs)
</script>

<style scoped>
.breadcrumb {
  display: inline-block;
  line-height: 60px;
  font-size: 14px;
}

:deep(.el-breadcrumb__item) {
  display: flex;
  align-items: center;
}

:deep(.el-breadcrumb__inner) {
  display: flex;
  align-items: center;
}

:deep(.el-icon) {
  margin-right: 4px;
  font-size: 16px;
}

:deep(.el-breadcrumb__separator) {
  margin: 0 8px;
  color: #909399;
}
</style>