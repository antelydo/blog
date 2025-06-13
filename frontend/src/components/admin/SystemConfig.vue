<template>
  <div class="system-config-container">
    <el-card class="system-config-card">
      <template #header>
        <div class="card-header">
          <h3>{{ $t('admin.systemConfig.title') }}</h3>
        </div>
      </template>
      
      <el-form :model="configForm" label-width="180px">
        <el-divider content-position="left">{{ $t('admin.systemConfig.logSettings') }}</el-divider>
        
        <!-- 控制台日志开关 -->
        <el-form-item :label="$t('admin.systemConfig.enableConsoleLog')">
          <el-switch
            v-model="configForm.enableConsoleLog"
            @change="handleConsoleLogChange"
          />
        </el-form-item>
        
        <!-- 日志级别选择 -->
        <el-form-item :label="$t('admin.systemConfig.logLevel')">
          <el-select 
            v-model="configForm.logLevel"
            :disabled="!configForm.enableConsoleLog"
            @change="handleLogLevelChange"
          >
            <el-option
              v-for="level in logLevels"
              :key="level.value"
              :label="level.label"
              :value="level.value"
            />
          </el-select>
        </el-form-item>
        
        <!-- 显示时间戳开关 -->
        <el-form-item :label="$t('admin.systemConfig.showTimestamp')">
          <el-switch
            v-model="configForm.showTimestamp"
            :disabled="!configForm.enableConsoleLog"
            @change="handleConfigChange"
          />
        </el-form-item>
        
        <!-- 显示日志级别开关 -->
        <el-form-item :label="$t('admin.systemConfig.showLogLevel')">
          <el-switch
            v-model="configForm.showLogLevel"
            :disabled="!configForm.enableConsoleLog"
            @change="handleConfigChange"
          />
        </el-form-item>
        
        <!-- 测试日志按钮 -->
        <el-form-item>
          <el-button type="primary" @click="testLog">{{ $t('admin.systemConfig.testLog') }}</el-button>
          <el-button type="success" @click="saveConfig">{{ $t('admin.systemConfig.saveConfig') }}</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { ElMessage } from 'element-plus';
import { getLoggerConfig, updateLoggerConfig } from '@/utils/logger';

const { t } = useI18n();

// 日志级别选项
const logLevels = [
  { value: 'debug', label: t('admin.systemConfig.logLevels.debug') },
  { value: 'info', label: t('admin.systemConfig.logLevels.info') },
  { value: 'warn', label: t('admin.systemConfig.logLevels.warn') },
  { value: 'error', label: t('admin.systemConfig.logLevels.error') }
];

// 配置表单
const configForm = ref({
  enableConsoleLog: true,
  logLevel: 'debug',
  showTimestamp: true,
  showLogLevel: true
});

// 初始化配置
onMounted(() => {
  const currentConfig = getLoggerConfig();
  configForm.value = { ...currentConfig };
});

// 处理控制台日志开关变化
const handleConsoleLogChange = (value) => {
  if (value) {
    // 启用控制台日志
    window.$logger.restoreConsole();
    ElMessage.success(t('admin.systemConfig.consoleLogEnabled'));
  } else {
    // 禁用控制台日志
    window.$logger.overrideConsole();
    ElMessage.warning(t('admin.systemConfig.consoleLogDisabled'));
  }
  
  updateLoggerConfig({ enableConsoleLog: value });
  
  // 保存到本地存储
  saveConfigToStorage();
};

// 处理日志级别变化
const handleLogLevelChange = (value) => {
  updateLoggerConfig({ logLevel: value });
  ElMessage.success(t('admin.systemConfig.logLevelChanged', { level: value }));
  
  // 保存到本地存储
  saveConfigToStorage();
};

// 处理配置变化
const handleConfigChange = () => {
  updateLoggerConfig({
    showTimestamp: configForm.value.showTimestamp,
    showLogLevel: configForm.value.showLogLevel
  });
  
  // 保存到本地存储
  saveConfigToStorage();
};

// 测试日志
const testLog = () => {
  console.debug('这是一条 DEBUG 级别的日志消息');
  console.log('这是一条 LOG 级别的日志消息');
  console.info('这是一条 INFO 级别的日志消息');
  console.warn('这是一条 WARN 级别的日志消息');
  console.error('这是一条 ERROR 级别的日志消息');
  
  ElMessage.success(t('admin.systemConfig.logTestComplete'));
};

// 保存配置
const saveConfig = () => {
  updateLoggerConfig({
    enableConsoleLog: configForm.value.enableConsoleLog,
    logLevel: configForm.value.logLevel,
    showTimestamp: configForm.value.showTimestamp,
    showLogLevel: configForm.value.showLogLevel
  });
  
  // 保存到本地存储
  saveConfigToStorage();
  
  ElMessage.success(t('admin.systemConfig.configSaved'));
};

// 保存配置到本地存储
const saveConfigToStorage = () => {
  try {
    localStorage.setItem('logger_config', JSON.stringify(configForm.value));
  } catch (e) {
    console.error('保存日志配置到本地存储失败:', e);
  }
};
</script>

<style scoped>
.system-config-container {
  padding: 20px;
}

.system-config-card {
  max-width: 800px;
  margin: 0 auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
