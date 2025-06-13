import { createI18n } from 'vue-i18n'
// 移除对i18nValidator的导入，避免循环引用

// 获取保存的语言设置，默认为中文
const savedLanguage = localStorage.getItem('language') || 'zh'

// Import language files
import enLink from '../locales/en/link'
import zhLink from '../locales/zh/link'
import enDashboard from '../locales/en/dashboard'
import zhDashboard from '../locales/zh/dashboard'
import enProfile from '../locales/en/profile'
import zhProfile from '../locales/zh/profile'
import enAuth from '../locales/en/auth'
import zhAuth from '../locales/zh/auth'
import enActivityLog from '../locales/en/activitylog'
import zhActivityLog from '../locales/zh/activitylog'
import enBlog from '../locales/en/blog'
import zhBlog from '../locales/zh/blog'
import enArticle from '../locales/en/article'
import zhArticle from '../locales/zh/article'
import enCategory from '../locales/en/category'
import zhCategory from '../locales/zh/category'
import enTag from '../locales/en/tag'
import zhTag from '../locales/zh/tag'
import enComment from '../locales/en/comment'
import zhComment from '../locales/zh/comment'
import enAdminComment from '../locales/en/adminComment'
import zhAdminComment from '../locales/zh/adminComment'
import enAdminCat from '../locales/en/adminCat'
import zhAdminCat from '../locales/zh/adminCat'
import enLike from '../locales/en/like'
import zhLike from '../locales/zh/like'
import enFavorite from '../locales/en/favorite'
import zhFavorite from '../locales/zh/favorite'
import enAiTool from '../locales/en/aiTool'
import zhAiTool from '../locales/zh/aiTool'
import enLayout from '../locales/en/layout'
import zhLayout from '../locales/zh/layout'
import enSettings from '../locales/en/settings'
import zhSettings from '../locales/zh/settings'
import enBreadcrumb from '../locales/en/breadcrumb'
import zhBreadcrumb from '../locales/zh/breadcrumb'
import enCommon from '../locales/en/common'
import zhCommon from '../locales/zh/common'
import enError from '../locales/en/error'
import zhError from '../locales/zh/error'
import enRoute from '../locales/en/route'
import zhRoute from '../locales/zh/route'
import enWebUser from '../locales/en/webUser'
import zhWebUser from '../locales/zh/webUser'
import zhWebLink from '../locales/zh/webLink'
import enWebLink from '../locales/en/webLink'
import enWebAbout from '../locales/en/webAbout'
import zhWebAbout from '../locales/zh/webAbout'
import enWebContact from '../locales/en/webContact'
import zhWebContact from '../locales/zh/webContact'
import enContact from '../locales/en/contact'
import enViewStats from '../locales/en/viewStats'
import zhViewStats from '../locales/zh/viewStats'
import zhContact from '../locales/zh/contact'
import enWebSidebar from '../locales/en/webSidebar'
import zhWebSidebar from '../locales/zh/webSidebar'
import enWebBanner from '../locales/en/webBanner'
import zhWebBanner from '../locales/zh/webBanner'
import enWebPosts from '../locales/en/webPosts'
import zhWebPosts from '../locales/zh/webPosts'
import enWebCategory from '@/locales/en/webCategory'
import zhWebCategory from '@/locales/zh/webCategory'
import enWebTag from '@/locales/en/webTag'
import zhWebTag from '@/locales/zh/webTag'
import enWebPost from '@/locales/en/webPost'
import zhWebPost from '@/locales/zh/webPost'
import enSearch from '../locales/en/search'
import zhSearch from '../locales/zh/search'
import enWebTagCloud from '@/locales/en/webTagCloud'
import zhWebTagCloud from '@/locales/zh/webTagCloud'
import enCategoryCloud from '@/locales/en/categoryCloud'
import enPersonalCenter from '@/locales/en/personalCenter'
import zhPersonalCenter from '@/locales/zh/personalCenter'
import enHeader from '@/locales/en/header'
import zhHeader from '@/locales/zh/header'
import enFooter from '@/locales/en/footer'
import zhFooter from '@/locales/zh/footer'
import zhCategoryCloud from '@/locales/zh/categoryCloud'
import enWebConfig from '@/locales/en/webConfig'
import zhWebConfig from '@/locales/zh/webConfig'
import enHomepage from '@/locales/en/homepage'
import zhHomepage from '@/locales/zh/homepage'
import enAdmin from '@/locales/en/admin'
import zhAdmin from '@/locales/zh/admin'
import enMobileNav from '@/locales/en/mobileNav'
import zhMobileNav from '@/locales/zh/mobileNav'



// Create messages object
const messages = {
  en: {
    link: enLink.link,
    linkManagement: enLink.linkManagement,
    friendshipLinks: enLink.friendshipLinks,
    dashboard: enDashboard.dashboard,
    profile: enProfile.profile,
    auth: enAuth.auth,
    activityLog: enActivityLog.activityLog,
    blog: enBlog.blog,
    article: enArticle.article,
    category: enCategory.category,
    tag: enTag.tag,
    comment: enComment.comment,
    adminComment: enAdminComment.adminComment,
    adminCat: enAdminCat.adminCat,
    like: enLike.like,
    favorite: enFavorite.favorite,
    aiTool: enAiTool.aiTool,
    layout: enLayout.layout,
    settings: enSettings.settings,
    //webUser: enWebUser.webUser,
    ...enBreadcrumb,
    common: enCommon, // 直接使用整个对象作为common命名空间
    ...enError,
    ...enRoute,
    ...enWebUser,
    ...enWebLink,
    ...enWebAbout,
    ...enViewStats,
    ...enWebContact,
    ...enContact,
    ...enWebSidebar,
    ...enWebBanner,
    ...enWebPosts,
    ...enWebCategory,
    ...enWebTag,
    ...enWebPost,
    ...enSearch,
    ...enWebConfig,
    ...enHomepage,
    ...enPersonalCenter,
    ...enHeader,
    ...enFooter,
    ...enWebTagCloud,
    ...enCategoryCloud,
    ...enAdmin,
    ...enMobileNav,
    theme: {
      light: 'Light Mode',
      dark: 'Dark Mode',
      system: 'System Mode',
      settings: 'Theme Settings',
      names: {
        siteDefault: 'Site Default',
        default: 'Default Blue',
        deepBlue: 'Deep Space Blue',
        emerald: 'Emerald Green',
        violet: 'Violet',
        coral: 'Coral Red',
        amber: 'Amber',
        slate: 'Slate Gray',
        mint: 'Mint Green',
        burgundy: 'Burgundy Red',
        teal: 'Teal',
        indigo: 'Indigo',
        rose: 'Rose Pink',
        deepPurple: 'Deep Purple',
        lime: 'Lime Green',
        sky: 'Sky Blue',
        orange: 'Orange',
        cyan: 'Cyan',
        sunset: 'Sunset Orange',
        seaWave: 'Sea Wave Green',
        royalBlue: 'Royal Blue',
        pomegranate: 'Pomegranate Red',
        coolBlack: 'Cool Black',
        lava: 'Lava Red',
        royalIndigo: 'Royal Indigo',
        phantom: 'Phantom Gray',
        coffee: 'Coffee Brown',
        graphite: 'Graphite Black',
        neonPink: 'Neon Pink',
        starryBlue: 'Starry Blue',
        amethyst: 'Amethyst Purple'
      }
    },
    common: {
      cancel: 'Cancel',
      confirm: 'Confirm',
      warning: 'Warning',
      fetchError: 'Failed to fetch data',
      saveError: 'Failed to save data',
      deleteError: 'Failed to delete data',
      search: 'Search',
      reset: 'Reset',
      operations: 'Operations',
      add: 'Add',
      edit: 'Edit',
      delete: 'Delete',
      success: 'Success',
      error: 'Error',
      week: 'Week',
      month: 'Month',
       // Button texts in charts
       retry: 'Retry',
       backToHome: 'Back to Home',

       // Add direct cancel key at the user level, since code is looking for user.cancel
       cancel: 'Cancel',
       or:'Or',
      //会员
      user:{
        title: 'Users',
        list_title: 'User List',
        search: 'Search username/nickname/email',
        filter: 'Filter',
        reset: 'Reset',
        export: 'Export Data',
        exportCSV: 'Export as CSV',
        exportExcel: 'Export as Excel',
        exportAsCSV: 'Export as CSV',
        exportAsExcel: 'Export as Excel',
        operations: 'Operations',
        retry: 'Retry',
        searchPlaceholder: 'Search username/nickname/email',
        statusPlaceholder: 'Status',
        statusAll: 'All',
        statusNormal: 'Normal',
        statusDisabled: 'Disabled',
        searchButton: 'Search',
        resetButton: 'Reset',
        exportData: 'Export Data',
        registerTime: 'Registration Time',
        lastLoginTime: 'Last Login Time',
        enableUser: 'Enable',
        disableUser: 'Disable',
        deleteUser: 'Delete',
        noDataToExport: 'No data to export',
        exportSuccess: 'Export successful: {fileName}',
        backToHome: 'Back to Home'
      },
      //用户表单
      form: {
        createTitle: 'Create New User',
        editTitle: 'Edit User Information',
        submitButton: 'Save',
        cancelButton: 'Cancel',
        username: 'Username',
        usernamePlaceholder: 'Please enter username',
        usernameRule: 'Username must be 3-20 characters',
        nickname: 'Nickname',
        nicknamePlaceholder: 'Please enter nickname',
        email: 'Email',
        emailPlaceholder: 'Please enter email',
        emailRule: 'Please enter a valid email address',
        mobile: 'Phone Number',
        mobilePlaceholder: 'Please enter mobile number',
        mobileRule: 'Please enter a valid mobile number',
        password: 'Password',
        passwordPlaceholder: 'Please enter password',
        passwordRule: 'Password must be 6-20 characters',
        confirmPassword: 'Confirm Password',
        confirmPasswordPlaceholder: 'Please confirm password',
        confirmPasswordRule: 'Passwords do not match',
        role: 'Role',
        rolePlaceholder: 'Please select role',
        status: 'Status',
        statusPlaceholder: 'Please select status',
        avatar: 'Avatar',
        avatarUpload: 'Upload Avatar',
        bio: 'Bio',
        bioPlaceholder: 'Please enter user bio',
        remark: 'Remark',
        remarkPlaceholder: 'Please enter remark (admin only)',
        submit: 'Submit',
        cancel: 'Cancel',
        reset: 'Reset',
        addTitle: 'Create New User',
        nicknameRequired: 'Please enter nickname',
        nicknameLength: 'Length must be between 2 and 20 characters',
        emailRequired: 'Please enter email',
        emailInvalid: 'Please enter a valid email address',
        mobileInvalid: 'Please enter a valid phone number',
        active: 'Active',
        inactive: 'Inactive',
        registerTime: 'Registration Time',
        lastLoginTime: 'Last Login Time',
        confirm: 'Confirm'
      },
      //操作
      actions: {
        view: 'View',
        edit: 'Edit',
        delete: 'Delete',
        ban: 'Ban',
        unban: 'Unban',
        activate: 'Activate',
        deactivate: 'Deactivate',
        enable: 'Enable',
        disable: 'Disable'
      },
      //errors
      errors: {
        createFailed: 'Failed to create user',
        updateFailed: 'Failed to update user',
        deleteFailed: 'Failed to delete user',
        statusChangeFailed: 'Failed to change user status',
        notFound: 'User not found',
        fetchFailed: 'Failed to fetch user data',
        chartError: 'Failed to load chart data',
        getUserList: 'Failed to get user list',
        updateUser: 'Failed to update user',
        renderChart: 'Failed to render chart'
      },
      charts: {
        registrationTrend: 'User Registration Trend',
        activityTrend: 'User Activity Trend',
        noData: 'No data available for the selected period',
        week: 'Week',
        month: 'Month'
      },

      confirmStatus: {
        enableTitle: 'Enable User',
        disableTitle: 'Disable User',
        enableMessage: 'Are you sure you want to enable user {username}?',
        disableMessage: 'Are you sure you want to disable user {username}?',
        confirmButton: 'Confirm',
        cancelButton: 'Cancel',
        enableSuccess: 'User enabled successfully',
        disableSuccess: 'User disabled successfully',
        operationFailed: 'Operation failed'
      },
      //统计模板
      statistics:{
        totalUsers:'Total Users',
        title: 'User Statistics',
        description: 'View statistics about user registrations and activity',
        totalUsers: 'Total Users',
        totalUsersTooltip: 'Total number of registered users',
        activeUsers: 'Active Users',
        activeUsersTooltip: 'Users who logged in recently',
        activityRate: 'Activity Rate',
        newToday: 'New Today',
        newTodayTooltip: 'Users registered in the last 24 hours',
        comparedToLastMonth: 'compared to last month',
        comparedToYesterday: 'compared to yesterday',
        newUsers: 'New Users',
        inactiveUsers: 'Inactive Users',
        totalAdmin: 'Total Admins',
        totalEditor: 'Total Editors',
        totalRegular: 'Total Regular Users',
        registrationTrend: 'Registration Trend',
        activityTrend: 'Activity Trend',
        loginFrequency: 'Login Frequency',
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        yearly: 'Yearly',
        backToHome: 'Back to Home'
      },
      time: {
        week: 'Week',
        month: 'Month'
      },
      theme: {
        light: 'Light Mode',
        dark: 'Dark Mode',
        system: 'System Mode',
        settings: 'Theme Settings'
      }
    },
    webCategory: enWebCategory,
    webTag: enWebTag,
    webPost: enWebPost,
    webTagCloud: enWebTagCloud
  },
  zh: {
    link: zhLink.link,
    linkManagement: zhLink.linkManagement,
    friendshipLinks: zhLink.friendshipLinks,
    dashboard: zhDashboard.dashboard,
    profile: zhProfile.profile,
    auth: zhAuth.auth,
    activityLog: zhActivityLog.activityLog,
    blog: zhBlog.blog,
    article: zhArticle.article,
    category: zhCategory.category,
    tag: zhTag.tag,
    comment: zhComment.comment,
    adminComment: zhAdminComment.adminComment,
    adminCat: zhAdminCat.adminCat,
    like: zhLike.like,
    favorite: zhFavorite.favorite,
    aiTool: zhAiTool.aiTool,
    layout: zhLayout.layout,
    settings: zhSettings.settings,
    //webUser: zhWebUser.webUser,
    ...zhBreadcrumb,
    common: zhCommon, // 直接使用整个对象作为common命名空间
    ...zhError,
    ...zhRoute,
    ...zhWebUser,
    ...zhWebLink,
    ...zhWebAbout,
    ...zhViewStats,
    ...zhWebContact,
    ...zhContact,
    ...zhWebSidebar,
    ...zhWebBanner,
    ...zhWebPosts,
    ...zhWebCategory,
    ...zhWebTag,
    ...zhWebPost,
    ...zhSearch,
    ...zhWebConfig,
    ...zhPersonalCenter,
    ...zhHeader,
    ...zhFooter,
    ...zhWebTagCloud,
    ...zhAdmin,
    ...zhMobileNav,
    webTagCloud: zhWebTagCloud,
    theme: {
      light: '浅色模式',
      dark: '深色模式',
      system: '跟随系统',
      settings: '主题设置',
      names: {
        siteDefault: '网站默认',
        default: '默认蓝',
        deepBlue: '深空蓝',
        emerald: '翡翠绿',
        violet: '紫罗兰',
        coral: '珊瑚红',
        amber: '琥珀黄',
        slate: '石板灰',
        mint: '薄荷绿',
        burgundy: '勃艮第红',
        teal: '青蓝',
        indigo: '靛青',
        rose: '玫瑰粉',
        deepPurple: '深紫色',
        lime: '草绿色',
        sky: '天蓝色',
        orange: '橙色',
        cyan: '深青色',
        sunset: '日落橙',
        seaWave: '海浪绿',
        royalBlue: '宝石蓝',
        pomegranate: '石榴红',
        coolBlack: '酷黑',
        lava: '熔岩红',
        royalIndigo: '靛蓝',
        phantom: '幽灵灰',
        coffee: '茶褐色',
        graphite: '石墨黑',
        neonPink: '霓虹粉',
        starryBlue: '星空蓝',
        amethyst: '紫水晶'
      }
    },
    common: {
      cancel: '取消',
      confirm: '确认',
      warning: '警告',
      fetchError: '获取数据失败',
      saveError: '保存数据失败',
      deleteError: '删除数据失败',
      search: '搜索',
      reset: '重置',
      operations: '操作',
      add: '添加',
      edit: '编辑',
      delete: '删除',
      success: '成功',
      error: '错误',
      week: '周',
      month: '月',
       // Button texts in charts
    retry: '重试',
    backToHome: '返回首页',

    // Also add cancel at root level if it doesn't exist
    cancel: '取消',
    or:'或',
      //会员
      user:{
        title: '用户',
        list_title: '用户列表',
        search: '搜索用户名/昵称/邮箱',
        filter: '筛选',
        reset: '重置',
        export: '导出数据',
        exportCSV: '导出为CSV',
        exportExcel: '导出为Excel',
        exportAsCSV: '导出为CSV',
        exportAsExcel: '导出为Excel',
        operations: '操作',
        retry: '重试',
        searchPlaceholder: '搜索用户名/昵称/邮箱',
        statusPlaceholder: '状态',
        statusAll: '全部',
        statusNormal: '正常',
        statusDisabled: '禁用',
        searchButton: '搜索',
        resetButton: '重置',
        exportData: '导出数据',
        registerTime: '注册时间',
        lastLoginTime: '最后登录',
        enableUser: '启用',
        disableUser: '禁用',
        deleteUser: '删除',
        noDataToExport: '没有数据可导出',
        exportSuccess: '导出成功: {fileName}',
        backToHome: '返回首页'
      },
        // 用户表单
    form: {
      createTitle: '创建新用户',
      editTitle: '编辑用户信息',
      submitButton: '保存',
      cancelButton: '取消',
      username: '用户名',
      usernamePlaceholder: '请输入用户名',
      usernameRule: '用户名必须为3-20个字符',
      nickname: '昵称',
      nicknamePlaceholder: '请输入昵称',
      email: '邮箱',
      emailPlaceholder: '请输入邮箱',
      emailRule: '请输入有效的邮箱地址',
      mobile: '手机号',
      mobilePlaceholder: '请输入手机号',
      mobileRule: '请输入有效的手机号',
      password: '密码',
      passwordPlaceholder: '请输入密码',
      passwordRule: '密码必须为6-20个字符',
      confirmPassword: '确认密码',
      confirmPasswordPlaceholder: '请确认密码',
      confirmPasswordRule: '两次输入的密码不一致',
      role: '角色',
      rolePlaceholder: '请选择角色',
      status: '状态',
      statusPlaceholder: '请选择状态',
      avatar: '头像',
      avatarUpload: '上传头像',
      bio: '个人简介',
      bioPlaceholder: '请输入用户个人简介',
      remark: '备注',
      remarkPlaceholder: '请输入备注（仅管理员可见）',
      submit: '提交',
      cancel: '取消',
      reset: '重置',
      addTitle: '创建新用户',
      active: '正常',
      inactive: '禁用',
      registerTime: '注册时间',
      lastLoginTime: '最后登录',
      nicknameRequired: '请输入昵称',
      nicknameLength: '长度在 2 到 20 个字符',
      emailRequired: '请输入邮箱',
      emailInvalid: '请输入正确的邮箱地址',
      mobileInvalid: '请输入正确的手机号',
      confirm: '确认'
    },
    //操作
    actions: {
      view: '查看',
      edit: '编辑',
      delete: '删除',
      ban: '封禁',
      unban: '解封',
      activate: '激活',
      deactivate: '停用',
      enable: '启用',
      disable: '禁用'
    },
    //errors
    errors: {
      createFailed: '创建用户失败',
      updateFailed: '更新用户失败',
      deleteFailed: '删除用户失败',
      statusChangeFailed: '更改用户状态失败',
      notFound: '未找到用户',
      fetchFailed: '获取用户数据失败',
      chartError: '加载图表数据失败',
      getUserList: '获取用户列表失败',
      updateUser: '更新用户失败',
      renderChart: '渲染图表失败'
    },
    //charts
    charts: {
      registrationTrend: '用户注册趋势',
      activityTrend: '用户活跃趋势',
      noData: '所选时期没有可用数据',
      week: '周',
      month: '月'
    },

    //提示

    confirmStatus: {
      enableTitle: '提示',
      disableTitle: '提示',
      enableMessage: '确定要启用用户 "{username}" 吗？',
      disableMessage: '确定要禁用用户 "{username}" 吗？',
      confirmButton: '确定',
      cancelButton: '取消',
      enableSuccess: '启用成功',
      disableSuccess: '禁用成功',
      operationFailed: '操作失败'
    },
       //统计模板
       statistics:{
        totalUsers:'用户总数',
        title: '用户统计',
        description: '查看用户注册和活动的统计数据',
        totalUsers: '总用户数',
        totalUsersTooltip: '已注册用户总数',
        activeUsers: '活跃用户',
        activeUsersTooltip: '最近登录的用户',
        activityRate: '活跃率',
        newToday: '今日新增',
        newTodayTooltip: '过去24小时内注册的用户',
        comparedToLastMonth: '与上月相比',
        comparedToYesterday: '与昨日相比',
        newUsers: '新注册会员',
        inactiveUsers: '活跃会员',
        totalAdmin: '管理员总数',
        totalEditor: '编辑总数',
        totalRegular: '普通用户总数',
        registrationTrend: '注册趋势',
        activityTrend: '活跃趋势',
        loginFrequency: '登录频率',
        daily: '每日',
        weekly: '每周',
        monthly: '每月',
        yearly: '每年',
        backToHome: '返回首页'
       },

      time: {
        week: '周',
        month: '月'
      },
      theme: {
        light: '浅色模式',
        dark: '深色模式',
        system: '跟随系统',
        settings: '主题设置'
      }
    },
    webCategory: zhWebCategory,
    webTag: zhWebTag,
    webPost: zhWebPost,
    ...zhCategoryCloud,
    ...zhHomepage
  }
}

// Create i18n instance
const i18n = createI18n({
  legacy: false,
  locale: savedLanguage || 'zh',
  fallbackLocale: 'zh',
  messages,
  silentTranslationWarn: true,
  missingWarn: false,
  fallbackWarn: false,
  warnHtmlMessage: false
})

// 将i18n实例暴露到全局，方便直接访问
if (typeof window !== 'undefined') {
  window.i18n = i18n
}

// 添加主题相关翻译的快捷访问并修复图标选择器翻译
const injectThemeTranslations = () => {
  // 将common.js中的common对象提升到根级别
  if (i18n.global.messages.value.zh.common?.common) {
    // 将common.common中的内容复制到common中
    Object.assign(i18n.global.messages.value.zh.common, i18n.global.messages.value.zh.common.common)
    Object.assign(i18n.global.messages.value.en.common, i18n.global.messages.value.en.common.common)

    // 删除嵌套的common对象以避免混淆
    delete i18n.global.messages.value.zh.common.common
    delete i18n.global.messages.value.en.common.common

    console.log('[i18n] 已修复图标选择器翻译结构')
  }

  // 手动添加图标选择器翻译键
  const zhIconTranslations = {
    selectIcon: '选择图标',
    searchIcon: '搜索图标',
    noIconSelected: '未选择图标',
    noIconsFound: '未找到匹配的图标',
    iconSelectHelp: '点击选择图标，或输入关键词搜索',
    iconPagination: {
      total: '共 {total} 个图标',
      page: '第 {current} 页 / 共 {total} 页'
    },
    iconCategories: {
      direction: '方向性图标',
      suggestion: '提示建议性图标',
      edit: '编辑类图标',
      data: '数据类图标',
      logo: '品牌和标识',
      common: '网站通用图标',
      filled: '实心图标',
      twoTone: '双色图标',
      ui: '界面交互图标',
      weather: '天气气象图标',
      communication: '通信社交图标',
      business: '商业金融图标'
    },
    iconCategoryDescriptions: {
      direction: '表示方向、箭头、导航等用途的图标',
      suggestion: '表示提示、警告、成功、错误等状态的图标',
      edit: '用于文本编辑、格式化等操作的图标',
      data: '用于数据可视化、图表等场景的图标',
      logo: '各种品牌、产品和平台的标识图标',
      common: '常用于网站界面、功能和操作的通用图标',
      filled: '填充样式的图标，视觉效果更加突出',
      twoTone: '具有两种颜色的图标，可以更好地表达层次感',
      ui: '用于界面设计、菜单导航和用户交互的图标',
      weather: '用于天气预报、气象现象和环境监测的图标',
      communication: '用于即时通信、消息提醒和社交媒体的图标',
      business: '用于商业运营、金融交易和电子商务的图标'
    }
  };

  const enIconTranslations = {
    selectIcon: 'Select Icon',
    searchIcon: 'Search Icon',
    noIconSelected: 'No Icon Selected',
    noIconsFound: 'No matching icons found',
    iconSelectHelp: 'Click to select an icon or type to search',
    iconPagination: {
      total: 'Total {total} icons',
      page: 'Page {current} of {total}'
    },
    iconCategories: {
      direction: 'Direction Icons',
      suggestion: 'Suggestion Icons',
      edit: 'Edit Icons',
      data: 'Data Icons',
      logo: 'Brand & Logo Icons',
      common: 'Common Icons',
      filled: 'Filled Icons',
      twoTone: 'Two-Tone Icons',
      ui: 'UI Icons',
      weather: 'Weather Icons',
      communication: 'Communication Icons',
      business: 'Business Icons'
    },
    iconCategoryDescriptions: {
      direction: 'Icons for directions, arrows, navigation and related concepts',
      suggestion: 'Icons for prompts, warnings, success, error and other states',
      edit: 'Icons for text editing, formatting and related operations',
      data: 'Icons for data visualization, charts and related scenarios',
      logo: 'Icons for various brands, products and platforms',
      common: 'Common icons used for website interfaces, functions and operations',
      filled: 'Filled style icons with more prominent visual effects',
      twoTone: 'Two-color icons that better express hierarchy',
      ui: 'Icons for user interface design and interaction',
      weather: 'Icons representing weather conditions and meteorological information',
      communication: 'Icons for communication, messaging and social interaction',
      business: 'Icons for business, finance and e-commerce'
    }
  };

  // 将翻译键添加到common对象中
  Object.assign(i18n.global.messages.value.zh.common, zhIconTranslations);
  Object.assign(i18n.global.messages.value.en.common, enIconTranslations);

  console.log('[i18n] 已手动添加图标选择器翻译键');

  if (!i18n.global.messages.value.zh.theme) {
    // 如果theme命名空间不存在，从common.theme复制
    i18n.global.messages.value.zh.theme = i18n.global.messages.value.zh.common?.theme || {}
    i18n.global.messages.value.en.theme = i18n.global.messages.value.en.common?.theme || {}
  }

  // 确保root级别有primary、success等键
  const uiElements = ['primary', 'success', 'warning', 'danger', 'info', 'placeholder', 'toggle', 'slider']

  uiElements.forEach(key => {
    if (!i18n.global.messages.value.zh[key]) {
      i18n.global.messages.value.zh[key] = i18n.global.messages.value.zh.common?.[key] || key
      i18n.global.messages.value.en[key] = i18n.global.messages.value.en.common?.[key] || key
    }
  })
}

// 注入主题翻译
injectThemeTranslations()

// 添加调试方法，帮助检查翻译是否存在
i18n.global.debugTranslation = (key) => {
  const locales = ['zh', 'en']
  const results = {}

  locales.forEach(locale => {
    try {
      const translation = i18n.global.t(key, locale)
      results[locale] = {
        translated: translation,
        exists: translation !== key
      }
    } catch (e) {
      results[locale] = { error: e.message }
    }
  })

  console.table(results)
  return results
}

export default i18n

// 调试代码：检查路由翻译是否正确加载
console.log('[DEBUG] 检查路由翻译载入情况:')
console.log('zh webUser 对象存在:', !!zhWebUser.webUser)
console.log('路由翻译示例 (zh):', i18n.global.t('webUser.login.userLogin'))
console.log('路由翻译示例 (en):', i18n.global.t('webUser.forgotPassword.backToLogin', 'en'))

// 调试图标选择器翻译
console.log('[DEBUG] 检查图标选择器翻译:')
console.log('common.selectIcon (zh):', i18n.global.t('common.selectIcon'))
console.log('common.iconCategories.direction (zh):', i18n.global.t('common.iconCategories.direction'))
console.log('common.iconCategoryDescriptions.direction (zh):', i18n.global.t('common.iconCategoryDescriptions.direction'))

// 输出完整的国际化对象结构
console.log('[DEBUG] 国际化对象结构:', {
  'common对象存在': !!i18n.global.messages.value.zh.common,
  'common.common对象存在': !!i18n.global.messages.value.zh.common?.common,
  'common.selectIcon值': i18n.global.messages.value.zh.common?.selectIcon,
  'common.iconCategories存在': !!i18n.global.messages.value.zh.common?.iconCategories
})