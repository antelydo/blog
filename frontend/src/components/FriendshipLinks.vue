<!-- 友情链接组件 -->
<template>
  <div class="friendship-links">
    <h2 class="title">{{ $t('friendshipLinks.title') }}</h2>
    <div v-loading="loading" class="links-container">
      <a
        v-for="link in links"
        :key="link.id"
        :href="link.url"
        target="_blank"
        rel="noopener noreferrer"
        class="link-item"
        :title="link.title + ' - ' + (link.description || '')"
      >
        <img :src="link.logo" :alt="link.title" class="link-logo">
        <span class="link-title">{{ link.title }}</span>
      </a>
      <div v-if="!loading && links.length === 0" class="no-links">
        {{ $t('friendshipLinks.noLinks') }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { getLinkList } from '../api/link'

const { t } = useI18n()
const loading = ref(false)
const links = ref([])

const fetchLinks = async () => {
  loading.value = true
  try {
    const response = await getLinkList()
    if (response.code === 0) {
      links.value = response.data
    } else {
      console.error('Failed to fetch links:', response.msg)
    }
  } catch (error) {
    console.error('Error fetching links:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLinks()
})
</script>

<style scoped>
.friendship-links {
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.title {
  font-size: 20px;
  color: #303133;
  margin-bottom: 20px;
  text-align: center;
}

.links-container {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  min-height: 200px;
}

.link-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-decoration: none;
  color: #606266;
  transition: all 0.3s;
  padding: 10px;
  border-radius: 8px;
}

.link-item:hover {
  transform: translateY(-5px);
  background-color: #f5f7fa;
}

.link-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  margin-bottom: 8px;
  border-radius: 4px;
}

.link-title {
  font-size: 14px;
  text-align: center;
}

.no-links {
  color: #909399;
  font-size: 14px;
  text-align: center;
  width: 100%;
  padding: 20px;
}
</style>