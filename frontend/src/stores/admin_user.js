// 管理员用户 Store
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getProfile as getProfileApi, updateProfile as updateProfileApi } from '@/api/admin/user'

export const useAdminUserStore = defineStore('admin_user', () => {
  const userInfo = ref(null)
  const token = ref(localStorage.getItem('admin_token') || '')

  // 获取用户信息
  const getProfile = async () => {
    try {
      const response = await getProfileApi()
      userInfo.value = response.data
      return response.data
    } catch (error) {
      console.error('获取用户信息失败:', error)
      throw error
    }
  }

  // 更新用户信息
  const updateProfile = async (data) => {
    try {
      const response = await updateProfileApi(data)
      userInfo.value = { ...userInfo.value, ...response.data }
      return response.data
    } catch (error) {
      console.error('更新用户信息失败:', error)
      throw error
    }
  }

  // 设置token
  const setToken = (newToken) => {
    token.value = newToken
    localStorage.setItem('admin_token', newToken)
  }

  // 清除token
  const clearToken = () => {
    token.value = ''
    localStorage.removeItem('admin_token')
  }

  // 登出
  const logout = () => {
    clearToken()
    userInfo.value = null
  }

  return {
    userInfo,
    token,
    getProfile,
    updateProfile,
    setToken,
    clearToken,
    logout
  }
}) 