// C端用户 Store
import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  getUserInfo as getUserInfoApi,
  updateUser as updateUserApi,
  updateUserAvatar as updateUserAvatarApi,
  getLoginLogs as getLoginLogsApi,
  getLoginStats as getLoginStatsApi,
  updateUserPassword as updateUserPasswordApi
} from '@/api/user'

export const useUserStore = defineStore('user', () => {
  const userInfo = ref(localStorage.getItem('user_info') || '')
  const token = ref(localStorage.getItem('token') || '')


  // 获取用户信息
  const getUserInfo = async () => {
    try {
      const response = await getUserInfoApi()
      // 如果有数据，更新本地用户信息
      if (response.data) {
        userInfo.value = response.data
      }

      // 返回完整的响应，包含 code、msg 和 data
      return response
    } catch (error) {
      console.error('获取用户信息失败:', error)
      throw error
    }
  }

  // 更新用户信息
  const updateUser = async (data) => {
    try {
      const response = await updateUserApi(data)
      console.log('API 原始响应:', response)

      // 如果有数据，更新本地用户信息
      if (response.data) {
        userInfo.value = { ...userInfo.value, ...response.data }
      }

      // 返回完整的响应，包含 code、msg 和 data
      return response
    } catch (error) {
      console.error('更新用户信息失败:', error)
      throw error
    }
  }

  // 更新用户头像
  const updateUserAvatar = async (data) => {
    try {
      const response = await updateUserAvatarApi(data)

      // 如果有数据，更新本地用户信息
      if (response.data) {
        userInfo.value = { ...userInfo.value, ...response.data }
      }




      // 返回完整的响应，包含 code、msg 和 data
      return response
    } catch (error) {
      console.error('更新用户头像失败:', error)
      throw error
    }
  }

  // 修改用户密码
  const updateUserPassword = async (data) => {
    try {
      const response = await updateUserPasswordApi(data)
      return response
    } catch (error) {
      console.error('更新用户密码失败:', error)
      throw error
    }
  }

  // 获取登录日志
  const getLoginLogs = async (page = 1, limit = 10) => {
    try {
      const response = await getLoginLogsApi(page, limit)
      return response
    } catch (error) {
      console.error('获取登录日志失败:', error)
      throw error
    }
  }

  // 获取登录统计信息
  const getLoginStats = async () => {
    try {
      const response = await getLoginStatsApi()
      return response
    } catch (error) {
      console.error('获取登录统计信息失败:', error)
      throw error
    }
  }

  // 设置token
  const setToken = (newToken) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
  }

  // 清除token
  const clearToken = () => {
    token.value = ''
    localStorage.removeItem('token')
  }

  // 登出
  const logout = () => {
    clearToken()
    userInfo.value = null
  }

  const  getToken = () => {
    return token.value
  }

  const  getUserDetail = () => {
    return userInfo.value
  }

  return {
    userInfo,
    token,
    getToken,
    getUserDetail,
    getUserInfo,
    updateUserAvatar,
    updateUserPassword,
    updateUser,
    getLoginLogs,
    getLoginStats,
    setToken,
    clearToken,
    logout
  }
})