/**
 * 颜色主题存储
 * 注意：此文件已被简化，仅保留基本功能以避免导入错误
 * 根据项目需求，已移除主题切换和暗黑模式功能
 */

import { defineStore } from 'pinia';

export const useColorThemesStore = defineStore('colorThemes', {
  state: () => ({
    // 默认颜色主题
    currentTheme: 'default',
    themes: {
      default: {
        primary: '#409eff',
        success: '#67c23a',
        warning: '#e6a23c',
        danger: '#f56c6c',
        info: '#909399'
      }
    }
  }),
  
  getters: {
    // 获取当前颜色主题
    getCurrentTheme: (state) => state.themes[state.currentTheme] || state.themes.default,
    
    // 获取所有主题
    getAllThemes: (state) => state.themes
  },
  
  actions: {
    // 设置颜色主题（已简化，始终使用默认主题）
    setColorTheme(themeName) {
      console.warn('颜色主题切换功能已禁用，始终使用默认主题');
      this.currentTheme = 'default';
    },
    
    // 初始化颜色主题
    initColorTheme() {
      this.currentTheme = 'default';
    }
  }
});
