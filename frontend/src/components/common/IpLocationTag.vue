<template>
  <div class="ip-location-tag">
    <a-tooltip v-if="loading">
      <template #title>正在查询地理位置...</template>
      <a-spin size="small" />
    </a-tooltip>
    <template v-else-if="error">
      <a-tooltip>
        <template #title>查询失败: {{ error }}</template>
        <a-tag color="red">
          <environment-outlined />
          未知位置
        </a-tag>
      </a-tooltip>
    </template>
    <template v-else-if="locationData">
      <a-tooltip>
        <template #title>
          <div>
            <p><strong>IP:</strong> {{ ip || '未知' }}</p>
            <p><strong>国家/地区:</strong> {{ locationData.country || '未知' }}</p>
            <p><strong>省/州:</strong> {{ locationData.regionName || '未知' }}</p>
            <p><strong>城市:</strong> {{ locationData.city || '未知' }}</p>
            <p><strong>ISP:</strong> {{ locationData.isp || '未知' }}</p>
            <p><strong>经纬度:</strong> {{ locationData.lat }}, {{ locationData.lon }}</p>
            <p><strong>时区:</strong> {{ locationData.timezone || '未知' }}</p>
          </div>
        </template>
        <a-tag :color="tagColor">
          <environment-outlined />
          {{ formattedLocation }}
        </a-tag>
      </a-tooltip>
    </template>
    <template v-else>
      <a-tag color="default" @click="queryLocation">
        <environment-outlined />
        {{ showIp ? ip : '点击查询位置' }}
      </a-tag>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { EnvironmentOutlined } from '@ant-design/icons-vue';
import { queryIpLocation, adminQueryIpLocation } from '@/api/ipLocation';

const props = defineProps({
  ip: {
    type: String,
    default: ''
  },
  autoQuery: {
    type: Boolean,
    default: false
  },
  showIp: {
    type: Boolean,
    default: false
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  tagColor: {
    type: String,
    default: 'blue'
  }
});

const loading = ref(false);
const error = ref('');
const locationData = ref(null);

// 格式化地理位置信息
const formattedLocation = computed(() => {
  if (!locationData.value) return '未知位置';
  
  const { country, regionName, city } = locationData.value;
  let location = '';
  
  if (country) location += country;
  if (regionName && regionName !== country) location += ` ${regionName}`;
  if (city) location += ` ${city}`;
  
  return location || '未知位置';
});

// 查询IP地理位置
const queryLocation = async () => {
  if (!props.ip && !props.autoQuery) return;
  
  loading.value = true;
  error.value = '';
  
  try {
    // 根据是否是管理员选择不同的API
    const queryFunction = props.isAdmin ? adminQueryIpLocation : queryIpLocation;
    const response = await queryFunction(props.ip);
    
    if (response.data && response.data.code === 200) {
      locationData.value = response.data.data;
    } else {
      error.value = response.data?.msg || '查询失败';
    }
  } catch (err) {
    console.error('IP地理位置查询失败:', err);
    error.value = err.message || '查询失败';
  } finally {
    loading.value = false;
  }
};

// 组件挂载时，如果设置了自动查询，则自动查询IP地理位置
onMounted(() => {
  if (props.autoQuery && props.ip) {
    queryLocation();
  }
});

// 暴露方法给父组件
defineExpose({
  queryLocation,
  locationData
});
</script>

<style scoped>
.ip-location-tag {
  display: inline-block;
}
</style>
