<template>
  <div class="ip-location-page theme-transition">
    <a-card title="IP地理位置查询工具" :bordered="false">
      <template #extra>
        <a-button type="primary" @click="showBatchQueryModal">
          <upload-outlined />
          批量查询
        </a-button>
      </template>

      <a-form layout="inline" :model="queryForm" @finish="handleQuery">
        <a-form-item label="IP地址" name="ip">
          <a-input
            v-model:value="queryForm.ip"
            placeholder="输入IP地址，留空则查询当前IP"
            allow-clear
            style="width: 250px"
          />
        </a-form-item>
        <a-form-item>
          <a-button type="primary" html-type="submit" :loading="loading">
            <search-outlined />
            查询
          </a-button>
        </a-form-item>
      </a-form>

      <a-divider />

      <div v-if="loading" class="loading-container">
        <a-spin tip="正在查询IP地理位置..." />
      </div>

      <div v-else-if="error" class="error-container">
        <a-alert
          type="error"
          :message="error"
          show-icon
        />
      </div>

      <div v-else-if="locationData" class="result-container">
        <a-descriptions title="IP地理位置信息" bordered>
          <a-descriptions-item label="IP地址">{{ locationData.query }}</a-descriptions-item>
          <a-descriptions-item label="国家/地区">{{ locationData.country }}</a-descriptions-item>
          <a-descriptions-item label="国家代码">{{ locationData.countryCode }}</a-descriptions-item>
          <a-descriptions-item label="省/州">{{ locationData.regionName }}</a-descriptions-item>
          <a-descriptions-item label="省/州代码">{{ locationData.region }}</a-descriptions-item>
          <a-descriptions-item label="城市">{{ locationData.city }}</a-descriptions-item>
          <a-descriptions-item label="邮政编码">{{ locationData.zip || '未知' }}</a-descriptions-item>
          <a-descriptions-item label="经度">{{ locationData.lon }}</a-descriptions-item>
          <a-descriptions-item label="纬度">{{ locationData.lat }}</a-descriptions-item>
          <a-descriptions-item label="时区">{{ locationData.timezone }}</a-descriptions-item>
          <a-descriptions-item label="ISP">{{ locationData.isp }}</a-descriptions-item>
          <a-descriptions-item label="组织">{{ locationData.org || '未知' }}</a-descriptions-item>
          <a-descriptions-item label="AS">{{ locationData.as || '未知' }}</a-descriptions-item>
        </a-descriptions>

        <div class="map-container" v-if="locationData.lat && locationData.lon">
          <a-divider>地图位置</a-divider>
          <iframe
            width="100%"
            height="400"
            frameborder="0"
            scrolling="no"
            marginheight="0"
            marginwidth="0"
            :src="`https://www.openstreetmap.org/export/embed.html?bbox=${locationData.lon-0.01},${locationData.lat-0.01},${locationData.lon+0.01},${locationData.lat+0.01}&layer=mapnik&marker=${locationData.lat},${locationData.lon}`"
          ></iframe>
        </div>
      </div>

      <div v-else class="empty-container">
        <a-empty description="请输入IP地址进行查询" />
      </div>
    </a-card>

    <!-- 批量查询模态框 -->
    <a-modal
      v-model:open="batchQueryModalVisible"
      title="批量查询IP地理位置"
      width="800px"
      @ok="handleBatchQuery"
      :confirm-loading="batchLoading"
    >
      <a-form layout="vertical">
        <a-form-item label="IP地址列表（每行一个IP）">
          <a-textarea
            v-model:value="batchIps"
            placeholder="请输入IP地址，每行一个"
            :rows="10"
            allow-clear
          />
        </a-form-item>
      </a-form>

      <div v-if="batchResults.length > 0" class="batch-results">
        <a-divider>查询结果</a-divider>
        <a-table
          :dataSource="batchResults"
          :columns="batchResultColumns"
          :pagination="{ pageSize: 10 }"
          :rowKey="record => record.ip"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'success'">
              <a-tag v-if="record.success" color="success">成功</a-tag>
              <a-tag v-else color="error">失败</a-tag>
            </template>
            <template v-if="column.key === 'location'">
              <a-tag v-if="record.success" color="blue">
                {{ formatLocation(record.data) }}
              </a-tag>
              <a-tag v-else color="red">
                {{ record.message || '查询失败' }}
              </a-tag>
            </template>
          </template>
        </a-table>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { message } from 'ant-design-vue';
import { SearchOutlined, UploadOutlined } from '@ant-design/icons-vue';
import { adminQueryIpLocation, adminBatchQueryIpLocation } from '@/api/admin/ipLocation';

// 查询表单
const queryForm = reactive({
  ip: ''
});

// 状态变量
const loading = ref(false);
const error = ref('');
const locationData = ref(null);

// 批量查询相关
const batchQueryModalVisible = ref(false);
const batchIps = ref('');
const batchLoading = ref(false);
const batchResults = ref([]);

// 批量查询结果表格列
const batchResultColumns = [
  {
    title: 'IP地址',
    dataIndex: 'ip',
    key: 'ip',
    width: '150px'
  },
  {
    title: '查询状态',
    dataIndex: 'success',
    key: 'success',
    width: '100px'
  },
  {
    title: '地理位置',
    key: 'location',
    width: '250px'
  },
  {
    title: 'ISP',
    dataIndex: ['data', 'isp'],
    key: 'isp',
    width: '200px',
    customRender: ({ text, record }) => {
      return record.success && text ? text : '-';
    }
  }
];

// 处理查询
const handleQuery = async () => {
  loading.value = true;
  error.value = '';
  locationData.value = null;

  try {
    const response = await adminQueryIpLocation(queryForm.ip);

    if (response.data && response.code === 200) {
      locationData.value = response.data;
    } else {
      error.value = response.msg??(response.msg || '查询失败');
    }
  } catch (err) {
    console.error('IP地理位置查询失败:', err);
    error.value = err.message || '查询失败';
  } finally {
    loading.value = false;
  }
};

// 显示批量查询模态框
const showBatchQueryModal = () => {
  batchQueryModalVisible.value = true;
  batchIps.value = '';
  batchResults.value = [];
};

// 处理批量查询
const handleBatchQuery = async () => {
  if (!batchIps.value.trim()) {
    message.warning('请输入至少一个IP地址');
    return;
  }

  // 解析IP列表
  const ipList = batchIps.value
    .split('\n')
    .map(ip => ip.trim())
    .filter(ip => ip);

  if (ipList.length === 0) {
    message.warning('请输入至少一个有效的IP地址');
    return;
  }

  if (ipList.length > 20) {
    message.warning('一次最多查询20个IP地址');
    return;
  }

  batchLoading.value = true;

  try {
    const response = await adminBatchQueryIpLocation(ipList);

    if (response.data && response.data.code === 200) {
      const results = response.data.data;

      // 转换结果为表格数据
      batchResults.value = Object.keys(results).map(ip => ({
        ip,
        ...results[ip]
      }));

      message.success(`成功查询 ${batchResults.value.filter(r => r.success).length} 个IP地址的地理位置`);
    } else {
      message.error(response.data?.msg || '批量查询失败');
    }
  } catch (err) {
    console.error('批量查询IP地理位置失败:', err);
    message.error(err.message || '批量查询失败');
  } finally {
    batchLoading.value = false;
  }
};

// 格式化地理位置信息
const formatLocation = (data) => {
  if (!data) return '未知位置';

  const { country, regionName, city } = data;
  let location = '';

  if (country) location += country;
  if (regionName && regionName !== country) location += ` ${regionName}`;
  if (city) location += ` ${city}`;

  return location || '未知位置';
};
</script>

<style scoped>
.ip-location-page {
  padding: 24px;
}

.loading-container,
.error-container,
.empty-container {
  margin-top: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.result-container {
  margin-top: 24px;
}

.map-container {
  margin-top: 24px;
}

.batch-results {
  margin-top: 24px;
}
</style>
