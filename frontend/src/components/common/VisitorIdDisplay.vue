<template>
  <div class="visitor-id-display" v-if="showVisitorId">
    <div class="visitor-id-content">
      <div class="visitor-id-title">{{ title }}</div>
      <div class="visitor-id-value" v-if="visitorId">{{ visitorId }}</div>
      <div class="visitor-id-loading" v-else-if="isLoading">
        <el-icon class="is-loading"><Loading /></el-icon>
        {{ $t('common.loading') }}
      </div>
      <div class="visitor-id-error" v-else-if="error">
        {{ $t('common.error') }}: {{ error.message }}
      </div>
      <div class="visitor-id-empty" v-else>
        {{ $t('common.noData') }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useVisitorId } from '@/composables/useVisitorId';
import { Loading } from '@element-plus/icons-vue';

export default {
  name: 'VisitorIdDisplay',
  components: {
    Loading
  },
  props: {
    title: {
      type: String,
      default: '访客ID'
    },
    showVisitorId: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const { visitorId, isLoading, error, initialize } = useVisitorId();

    onMounted(() => {
      if (props.showVisitorId) {
        initialize();
      }
    });

    return {
      visitorId,
      isLoading,
      error
    };
  }
}
</script>

<style scoped>
.visitor-id-display {
  margin: 10px 0;
  padding: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
  background-color: #f9f9f9;
}

.visitor-id-title {
  font-weight: bold;
  margin-bottom: 5px;
}

.visitor-id-value {
  font-family: monospace;
  word-break: break-all;
  background-color: #f0f0f0;
  padding: 5px;
  border-radius: 3px;
  font-size: 12px;
}

.visitor-id-loading,
.visitor-id-error,
.visitor-id-empty {
  color: #999;
  font-size: 14px;
}

.visitor-id-error {
  color: #f56c6c;
}

.is-loading {
  animation: rotating 2s linear infinite;
}

@keyframes rotating {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
