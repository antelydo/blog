<!-- 系统设置 -->
<template>
  <div class="settings-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ $t('settings.title') }}</span>
        </div>
      </template>

      <el-form
        ref="settingsFormRef"
        :model="settingsForm"
        label-width="120px"
        v-loading="loading"
      >
        <el-tabs v-model="activeTab">
          <!-- 基本设置 -->
          <el-tab-pane :label="$t('settings.basicSettings')" name="basic">
            <el-form-item :label="$t('settings.siteName')" prop="site_name">
              <el-input v-model="settingsForm.site_name" />
            </el-form-item>
            <el-form-item :label="$t('settings.siteTitle')" prop="site_title">
              <el-input v-model="settingsForm.site_title" />
            </el-form-item>
            <el-form-item :label="$t('settings.siteDescription')" prop="site_description">
              <el-input v-model="settingsForm.site_description" type="textarea" :rows="3" />
            </el-form-item>
            <el-form-item :label="$t('settings.siteKeywords')" prop="site_keywords">
              <el-input v-model="settingsForm.site_keywords" :placeholder="$t('settings.keywordsPlaceholder')" />
            </el-form-item>
            <el-form-item :label="$t('settings.copyright')" prop="site_copyright">
              <el-input v-model="settingsForm.site_copyright" :placeholder="$t('settings.copyrightPlaceholder')" />
            </el-form-item>
            <el-form-item :label="$t('settings.icp')" prop="site_icp">
              <el-input v-model="settingsForm.site_icp" :placeholder="$t('settings.icpPlaceholder')" />
            </el-form-item>
            <el-form-item :label="$t('settings.publicSecurityRecord')" prop="site_public_security_record">
              <el-input v-model="settingsForm.site_public_security_record" />
            </el-form-item>
            <el-form-item :label="$t('settings.siteLogo')" prop="site_logo">
              <el-upload
                class="avatar-uploader"
                action="/api/admin/upload"
                :show-file-list="false"
                :on-success="handleLogoSuccess" >
                <img v-if="settingsForm.site_logo" :src="settingsForm.site_logo" class="avatar" />
                <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
              </el-upload>
            </el-form-item>
            <el-form-item :label="$t('settings.siteFavicon')" prop="Favicon">
              <el-upload
                class="avatar-uploader"
                action="/api/admin/upload"
                :show-file-list="false"
                :on-success="handleFaviconSuccess" >
                <img v-if="settingsForm.Favicon" :src="settingsForm.Favicon" class="avatar" />
                <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
              </el-upload>
            </el-form-item>
          </el-tab-pane>

          <!-- 联系方式 -->
          <el-tab-pane :label="$t('settings.contactInfo')" name="contact">
            <el-form-item :label="$t('settings.contactPhone')" prop="contact_phone">
              <el-input v-model="settingsForm.contact_phone" />
            </el-form-item>
            <el-form-item :label="$t('settings.contactEmail')" prop="contact_email">
              <el-input v-model="settingsForm.contact_email" />
            </el-form-item>
            <el-form-item :label="$t('settings.contactAddress')" prop="contact_address">
              <el-input v-model="settingsForm.contact_address" />
            </el-form-item>

            <!-- 联系表单设置 -->
            <el-divider content-position="left">{{ $t('settings.contactFormSettings') }}</el-divider>

            <el-form-item :label="$t('settings.contactFormEnabled')" prop="contact_form_enabled">
              <el-switch v-model="settingsForm.contact_form_enabled" />
            </el-form-item>

            <el-form-item :label="$t('settings.contactEmailNotification')" prop="contact_email_notification">
              <el-switch v-model="settingsForm.contact_email_notification" />
            </el-form-item>

            <el-form-item :label="$t('settings.contactNotificationEmail')" prop="contact_notification_email" v-if="settingsForm.contact_email_notification">
              <el-input v-model="settingsForm.contact_notification_email" :placeholder="$t('settings.contactNotificationEmailPlaceholder')" />
            </el-form-item>
          </el-tab-pane>

          <!-- 公司信息 -->
          <el-tab-pane :label="$t('settings.about_us_introduction')" name="about_us_introduction">
            <el-form-item :label="$t('settings.about_us_introduction')" prop="about_us_introduction">
              <el-input v-model="settingsForm.about_us_introduction" type="textarea" :rows="4" />
            </el-form-item>
            <el-form-item :label="$t('settings.about_us_history')" prop="about_us_history">
              <el-input v-model="settingsForm.about_us_history" type="textarea" :rows="4" />
            </el-form-item>
            <el-form-item :label="$t('settings.about_us_mission')" prop="about_us_mission">
              <el-input v-model="settingsForm.about_us_mission" type="textarea" :rows="3" />
            </el-form-item>
            <el-form-item :label="$t('settings.about_us_vision')" prop="about_us_vision">
              <el-input v-model="settingsForm.about_us_vision" type="textarea" :rows="3" />
            </el-form-item>
          </el-tab-pane>

          <!-- 其他设置 -->
          <el-tab-pane :label="$t('settings.otherSettings')" name="other">
            <el-form-item :label="$t('settings.registerEnabled')" prop="register_enabled">
              <el-switch v-model="settingsForm.register_enabled" />
            </el-form-item>
            <el-form-item :label="$t('settings.commentEnabled')" prop="comment_enabled">
              <el-switch v-model="settingsForm.comment_enabled" />
            </el-form-item>
            <el-form-item :label="$t('settings.commentNeedAudit')" prop="comment_need_audit">
              <el-switch v-model="settingsForm.comment_need_audit" />
            </el-form-item>
            <el-form-item :label="$t('settings.maintenanceMode')" prop="maintenance_mode">
              <el-switch v-model="settingsForm.maintenance_mode" />
            </el-form-item>
            <el-form-item :label="$t('settings.maintenanceMessage')" prop="maintenance_message" v-if="settingsForm.maintenance_mode">
              <el-input v-model="settingsForm.maintenance_message" type="textarea" :rows="3" :placeholder="$t('settings.maintenanceMessagePlaceholder')" />
            </el-form-item>
          </el-tab-pane>
        </el-tabs>

        <el-form-item>
          <el-button type="primary" @click="saveSettings" :loading="submitting">{{ $t('settings.save') }}</el-button>
          <el-button @click="resetForm">{{$t('settings.reset') }}</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { useI18n } from 'vue-i18n'
import adminApi, { apiUrls } from '@/api/admin'

// 获取i18n实例
const { t } = useI18n()

// 组件级别的错误处理函数
const errorHandler = (event) => {
  // 检查错误是否与JSON-RPC相关
  if (event.message && (
    event.message.includes('JSON-RPC') ||
    event.message.includes('inpage.js') ||
    event.message.includes('MetaMask') ||
    event.message.includes('wallet') ||
    event.message.includes('ethereum') ||
    event.message.includes('provider') ||
    // 检查错误源是否来自Web3钱包扩展
    (event.filename && (
      event.filename.includes('chrome-extension') ||
      event.filename.includes('extension') ||
      event.filename.includes('inpage.js')
    ))
  )) {
    console.warn('捕获到Web3钱包扩展相关错误，已忽略:', event.message)
    // 阻止错误继续传播
    event.preventDefault()
    event.stopPropagation()
    return true
  }
  return false
}

// 未处理的Promise拒绝事件处理函数
const unhandledRejectionHandler = (event) => {
  // 检查错误是否与Web3钱包扩展相关
  if (event.reason && (
    (typeof event.reason.message === 'string' && (
      event.reason.message.includes('JSON-RPC') ||
      event.reason.message.includes('inpage.js') ||
      event.reason.message.includes('MetaMask') ||
      event.reason.message.includes('wallet') ||
      event.reason.message.includes('ethereum')
    )) ||
    (event.reason.stack && (
      event.reason.stack.includes('inpage.js') ||
      event.reason.stack.includes('MetaMask') ||
      event.reason.stack.includes('extension')
    ))
  )) {
    console.warn('捕获到未处理的Web3钱包扩展相关Promise拒绝:', event.reason)
    // 阻止错误继续传播
    event.preventDefault()
    event.stopPropagation()
    return true
  }
  return false
}

// 组件挂载时添加错误监听
onMounted(() => {
  window.addEventListener('error', errorHandler, { once: false, capture: true })
  window.addEventListener('unhandledrejection', unhandledRejectionHandler, { capture: true })
})

// 组件卸载时移除错误监听
onUnmounted(() => {
  window.removeEventListener('error', errorHandler, { capture: true })
  window.removeEventListener('unhandledrejection', unhandledRejectionHandler, { capture: true })
})

// 表单数据
const settingsFormRef = ref(null)
const loading = ref(true)
const submitting = ref(false)
const activeTab = ref('basic')

// 设置表单
const settingsForm = reactive({
  site_name: '',
  site_title: '',
  site_description: '',
  site_keywords: '',
  site_logo: '',
  Favicon: '',
  site_copyright: '',
  site_icp: '',
  site_public_security_record: '',
  contact_phone: '',
  contact_email: '',
  contact_address: '',
  register_enabled: true,
  comment_enabled: true,
  comment_need_audit: true,
  maintenance_mode: false,
  maintenance_message: '网站正在维护中，请稍后再试...',
  about_us_introduction: '',
  about_us_history: '',
  about_us_mission: '',
  about_us_vision: '',
  contact_form_enabled: true,
  contact_email_notification: true,
  contact_notification_email: ''
})

// 原始设置，用于重置
let originalSettings = {}

// 获取网站配置
const fetchSettings = async () => {
  loading.value = true

  // 设置一个标志，表示组件是否已卸载
  let isMounted = true

  // 添加超时处理，增加超时时间到30秒
  const timeoutPromise = new Promise((_, reject) => {
    setTimeout(() => reject(new Error('请求超时')), 30000) // 30秒超时
  })

  // 创建取消令牌
  const controller = new AbortController()

  // 组件级别的错误处理，优先使用全局错误处理函数，如果不存在则使用本地实现
  const handleJsonRpcError = (error) => {
    // 优先使用全局错误处理函数
    if (window.handleJsonRpcError) {
      return window.handleJsonRpcError(error)
    }

    // 本地实现作为备份
    if (error && typeof error === 'object') {
      // 检查是否是JSON-RPC错误
      if ((error.code === -32603 && error.message && error.message.includes('JSON-RPC')) ||
          (error.message && (error.message.includes('inpage.js') ||
                           error.message.includes('MetaMask') ||
                           error.message.includes('wallet') ||
                           error.message.includes('ethereum') ||
                           error.message.includes('provider'))) ||
          (error.data && error.data.originalError &&
           (error.data.originalError.status === 'ERR_NETWORK' ||
            error.data.originalError.message &&
            (error.data.originalError.message.includes('MetaMask') ||
             error.data.originalError.message.includes('wallet') ||
             error.data.originalError.message.includes('ethereum')))) ||
          (error.stack && (error.stack.includes('inpage.js') ||
                         error.stack.includes('MetaMask') ||
                         error.stack.includes('wallet') ||
                         error.stack.includes('ethereum')))) {
        console.warn('捕获到Web3钱包扩展相关错误，已忽略:', error)
        return true
      }
    }
    return false
  }

  try {

    // 使用Promise.race实现超时控制，并添加取消信号
    const response = await Promise.race([
      // 修复：从POST请求改为GET请求，因为Admin.php的getConfig是GET方法
      adminApi.get(apiUrls.ADMIN_API.CONFIG, {
        signal: controller.signal,
        // 增加请求级别的超时设置
        timeout: 30000
      }).catch(error => {
        console.error('请求出错:', error) // 添加明确的错误日志
        // 检查是否是JSON-RPC错误
        if (handleJsonRpcError(error)) {
          // 如果是JSON-RPC错误，返回一个默认响应
          return { code: 500, msg: '请求被浏览器扩展中断，请禁用MetaMask等Web3钱包扩展后重试' }
        }
        throw error; // 如果不是JSON-RPC错误，继续抛出
      }),
      timeoutPromise
    ])

    // 添加响应数据日志

    // 如果组件已卸载，不继续处理
    if (!isMounted) return

    if (response && response.code === 200) {
      // 检查response.data是否存在
      if (!response.data) {
        ElMessage.warning('获取网站配置失败：返回数据为空')
        return
      }

      // 确保data是数组，如果是对象则尝试转换为数组
      let data = []
      if (Array.isArray(response.data)) {
        data = response.data
      } else if (typeof response.data === 'object') {
        // 如果是对象，尝试将其转换为数组格式
        data = Object.entries(response.data).map(([name, value]) => ({ name, value }))
      }

      if (data.length === 0) {
        ElMessage.warning('获取网站配置失败：返回数据为空')
        return
      }

      // 将后端返回的配置数组转换为对象格式
      let hasError = false
      for (const item of data) {
        try {
          // 检查item是否有效
          if (!item || typeof item !== 'object') {
            console.warn('配置项无效:', item)
            hasError = true
            continue // 跳过无效项，继续处理其他配置
          }

          // 确保item.name存在
          const name = item.name || Object.keys(item)[0]
          if (!name) {
            console.warn('配置项名称无效:', item)
            hasError = true
            continue // 跳过无效项，继续处理其他配置
          }

          // 获取value值
          const value = item.value !== undefined ? item.value : item[name]

          if (settingsForm.hasOwnProperty(name)) {
            // 根据类型处理值
            if (typeof settingsForm[name] === 'boolean') {
              // 布尔值转换
              settingsForm[name] = value === '1' || value === 'true' || value === true
            } else {
              settingsForm[name] = value || ''
            }
          }
        } catch (itemError) {
          console.error('处理配置项时出错:', itemError)
          hasError = true
          // 继续处理下一个配置项
        }
      }

      if (hasError) {
        ElMessage.warning('部分配置项加载失败，已跳过无效数据')
      }

      // 保存原始设置用于重置
      originalSettings = JSON.parse(JSON.stringify(settingsForm))
    } else {
      console.error('获取配置失败, 响应码:', response?.code, '错误信息:', response?.msg)
      ElMessage.error(response?.msg || '获取网站配置失败')
      // 使用默认值填充表单
      setDefaultValues()
    }
  } catch (error) {
    // 如果组件已卸载，不继续处理
    if (!isMounted) return

    console.error('获取配置出错:', error)
    // 检查是否是Web3钱包扩展相关错误
    if (handleJsonRpcError(error)) {
      console.warn('捕获到Web3钱包扩展相关错误:', error)
      ElMessage.warning('检测到浏览器扩展冲突，请尝试禁用MetaMask等Web3钱包扩展后重试')
      // 使用默认值填充表单
      setDefaultValues()
      // 确保表单可用
      loading.value = false
    } else if (error.message === '请求超时') {
      ElMessage.error('获取配置超时，请检查网络连接')
    } else if (error.name === 'AbortError') {
    } else if (error.code === 'ERR_NETWORK') {
      ElMessage.error('网络连接错误，请检查网络连接')
      // 使用默认值填充表单
      setDefaultValues()
    } else {
      ElMessage.error('服务器错误，请稍后再试')
      // 使用默认值填充表单
      setDefaultValues()
    }
  } finally {
    // 如果组件仍然挂载，则更新loading状态
    if (isMounted) {
      loading.value = false
    }

    // 清理函数
    return () => {
      isMounted = false
      controller.abort() // 取消请求
    }
  }
}

// 设置默认值，当无法从服务器获取配置时使用
const setDefaultValues = () => {
  // 只有在originalSettings为空对象时才设置默认值
  if (Object.keys(originalSettings).length === 0) {
    // 使用表单的初始值作为默认值
    originalSettings = JSON.parse(JSON.stringify(settingsForm))
  }
}

// 保存设置
const saveSettings = async () => {
  // 如果已经在提交中，不重复请求
  if (submitting.value) return

  submitting.value = true

  // 设置一个标志，表示组件是否已卸载
  let isMounted = true

  // 创建取消令牌
  const controller = new AbortController()

  // 添加超时处理
  const timeoutPromise = new Promise((_, reject) => {
    setTimeout(() => reject(new Error('保存超时')), 30000) // 30秒超时
  })

  try {
    const response = await adminApi.post(apiUrls.ADMIN_API.UPDATE_CONFIG, settingsForm, {
      signal: controller.signal
    })

    if (response.code === 200) {
      ElMessage.success(t('settings.saveSuccess'))

      // 更新原始设置
      Object.keys(settingsForm).forEach(key => {
        originalSettings[key] = settingsForm[key]
      })

      // 两秒后重新获取配置，确保获取到最新数据
      setTimeout(() => {
        if (isMounted) {
          fetchSettings()
        }
      }, 1000)
    } else {
      ElMessage.error(response?.msg || t('settings.saveError'))
    }
  } catch (error) {
    // 如果组件已卸载，不继续处理
    if (!isMounted) return

    console.error('保存配置出错:', error)
    // 检查是否是Web3钱包扩展相关错误
    if (handleJsonRpcError(error)) {
      console.warn('捕获到Web3钱包扩展相关错误:', error)
      ElMessage.warning(t('settings.walletExtensionError'))
      // 确保表单可用
      submitting.value = false
    } else if (error.message === '保存超时') {
      ElMessage.error(t('settings.saveTimeout'))
    } else if (error.name === 'AbortError') {
    } else if (error.code === 'ERR_NETWORK') {
      ElMessage.error(t('settings.networkError'))
    } else {
      ElMessage.error(t('settings.serverError'))
    }
  } finally {
    // 如果组件仍然挂载，则更新submitting状态
    if (isMounted) {
      submitting.value = false
    }

    // 清理函数
    return () => {
      isMounted = false
      controller.abort() // 取消请求
    }
  }
}

// 重置表单
const resetForm = () => {
  Object.keys(originalSettings).forEach(key => {
    settingsForm[key] = originalSettings[key]
  })
  ElMessage.info(t('settings.resetSuccess'))
}

// 上传Logo成功回调
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    settingsForm.site_logo = response.data.url
    ElMessage.success(response.msg || t('settings.uploadSuccess'))
  } else {
    ElMessage.error(response.msg || t('settings.uploadError'))
  }
}

// 上传Favicon成功回调
const handleFaviconSuccess = (response) => {
  if (response.code === 200) {
    settingsForm.Favicon = response.data.url
    ElMessage.success(response.msg || t('settings.uploadSuccess'))
  } else {
    ElMessage.error(response.msg || t('settings.uploadError'))
  }
}

// 组件挂载时获取配置
let cleanup = null

onMounted(() => {
  // 设置初始loading状态
  loading.value = true

  // 添加一个小延迟，避免在组件刚挂载时就发起请求
  // 这有助于避免与某些浏览器扩展的冲突
  setTimeout(() => {
    // 调用fetchSettings并保存清理函数
    cleanup = fetchSettings()
  }, 100)
})
</script>

<style scoped>
.settings-container {
  padding: 20px 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.avatar-uploader {
  width: 178px;
  height: 178px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.avatar-uploader:hover {
  border-color: #409EFF;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  line-height: 178px;
  text-align: center;
}

.avatar {
  width: 178px;
  height: 178px;
  display: block;
}
</style>