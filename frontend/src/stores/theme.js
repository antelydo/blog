/**
 * 主题存储
 * 注意：此文件已被简化，仅保留基本功能以避免导入错误
 * 根据项目需求，已移除主题切换和暗黑模式功能
 */

import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    theme: 'light', // 默认使用亮色主题
    themeColors: {
      primary: '#409eff',
      success: '#67c23a',
      warning: '#e6a23c',
      danger: '#f56c6c',
      info: '#909399'
    }
  }),
  
  getters: {
    // 获取当前主题
    currentTheme: (state) => state.theme,
    
    // 获取主题颜色
    primaryColor: (state) => state.themeColors.primary,
    successColor: (state) => state.themeColors.success,
    warningColor: (state) => state.themeColors.warning,
    dangerColor: (state) => state.themeColors.danger,
    infoColor: (state) => state.themeColors.info
  },
  
  actions: {
    // 设置主题（已禁用，始终返回light主题）
    setTheme(theme) {
      console.warn('主题切换功能已禁用，始终使用亮色主题');
      this.theme = 'light';
      return 'light';
    },
    
    // 初始化主题（已简化，始终设置为light主题）
    initTheme() {
      this.theme = 'light';
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.classList.add('light-theme');
      document.documentElement.classList.remove('dark-theme');
      document.documentElement.classList.remove('dark');
    }
  }
});
