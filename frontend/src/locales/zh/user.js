export default {
  user: {
    title: '用户',
    management: '用户管理',
    list: {
      title: '用户列表',
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
      exportSuccess: '导出成功: {fileName}'
    },
    detail: '用户详情',
    add: '添加用户',
    edit: '编辑用户',
    delete: '删除用户',
    deleteConfirm: '确定要删除此用户吗？',
    deleteSuccess: '用户删除成功',
    deleteError: '用户删除失败',
    
    // User form fields
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
    
    username: '用户名',
    nickname: '昵称',
    email: '邮箱',
    mobile: '手机号',
    password: '密码',
    confirmPassword: '确认密码',
    role: '角色',
    status: '状态',
    createdAt: '创建时间',
    updatedAt: '更新时间',
    lastLogin: '最后登录',
    loginIp: '登录IP',
    
    // Status options
    status: {
      all: '全部',
      active: '活跃',
      inactive: '非活跃',
      suspended: '已暂停',
      pending: '待审核',
      banned: '已封禁',
      normal: '正常',
      disabled: '禁用',
      1: '正常',
      0: '禁用'
    },
    
    // Role options
    role: {
      admin: '管理员',
      moderator: '版主',
      user: '普通用户',
      editor: '编辑'
    },
    
    // Messages
    createSuccess: '用户创建成功',
    updateSuccess: '用户更新成功',
    deleteSuccess: '用户删除成功',
    deleteError: '用户删除失败',
    statusChangeSuccess: '用户状态修改成功',
    
    // Validation
    usernameRequired: '用户名为必填项',
    emailRequired: '邮箱为必填项',
    emailInvalid: '请输入有效的邮箱地址',
    passwordRequired: '密码为必填项',
    passwordLength: '密码长度至少为8个字符',
    passwordMismatch: '两次输入的密码不一致',
    
    // User statistics
    statistics: {
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
      totalAdmin: '管理员总数',
      totalEditor: '编辑总数',
      totalRegular: '普通用户总数',
      registrationTrend: '注册趋势',
      activityTrend: '活跃趋势',
      loginFrequency: '登录频率',
      daily: '每日',
      weekly: '每周',
      monthly: '每月',
      yearly: '每年'
    },
    
    // Charts section
    charts: {
      registrationTrend: '用户注册趋势',
      activityTrend: '用户活跃趋势',
      noData: '所选时期没有可用数据',
      week: '周',
      month: '月'
    },
    
    fields: {
      id: 'ID',
      actions: '操作'
    },
    
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
    
    // User management actions
    activate: '激活',
    deactivate: '停用',
    suspend: '暂停',
    resetPassword: '重置密码',
    resetPasswordConfirm: '确定要重置此用户的密码吗？',
    resetPasswordSuccess: '密码重置成功',
    resetPasswordError: '密码重置失败',
    saveSuccess: '用户保存成功',
    saveError: '用户保存失败',
    
    // Filter options
    filter: {
      all: '全部',
      active: '已激活',
      inactive: '未激活',
      pending: '待审核',
      suspended: '已暂停',
      admin: '管理员',
      editor: '编辑',
      user: '普通用户',
      search: '搜索',
      searchPlaceholder: '按用户名、邮箱或昵称搜索',
      dateRange: '日期范围',
      startDate: '开始日期',
      endDate: '结束日期',
      apply: '应用',
      reset: '重置',
      sort: '排序',
      sortBy: '排序依据',
      order: '排序方式',
      ascending: '升序',
      descending: '降序'
    },
    
    roles: {
      admin: '管理员',
      editor: '编辑',
      user: '普通用户'
    },
    
    // User Management
    userList: '用户列表',
    addUser: '添加用户',
    editUser: '编辑用户',
    viewUser: '查看用户',
    
    // User Status
    active: '活跃',
    inactive: '不活跃',
    banned: '已禁用',
    pending: '待审核',
    
    // User Information
    id: 'ID',
    username: '用户名',
    email: '邮箱',
    password: '密码',
    newPassword: '新密码',
    confirmPassword: '确认密码',
    avatar: '头像',
    displayName: '显示名称',
    firstName: '名',
    lastName: '姓',
    fullName: '全名',
    phone: '电话',
    website: '网站',
    bio: '个人简介',
    status: '状态',
    role: '角色',
    roles: '角色',
    permissions: '权限',
    createdAt: '创建时间',
    updatedAt: '更新时间',
    lastLogin: '最后登录',
    registrationDate: '注册日期',
    uploadAvatar: '上传头像',
    removeAvatar: '移除头像',
    
    // User Roles
    admin: '管理员',
    editor: '编辑',
    author: '作者',
    contributor: '贡献者',
    subscriber: '订阅者',
    
    // Authentication
    login: '登录',
    logout: '退出登录',
    register: '注册',
    forgotPassword: '忘记密码',
    resetPassword: '重置密码',
    changePassword: '修改密码',
    currentPassword: '当前密码',
    sendResetLink: '发送重置链接',
    rememberMe: '记住我',
    loginWith: '使用 {provider} 登录',
    passwordReset: '密码重置',
    passwordResetSent: '密码重置指南已发送至您的邮箱',
    
    // Two-Factor Authentication
    twoFactor: '两步验证',
    enableTwoFactor: '启用两步验证',
    disableTwoFactor: '禁用两步验证',
    twoFactorEnabled: '两步验证已启用',
    twoFactorDisabled: '两步验证已禁用',
    enterCode: '输入验证码',
    verificationCode: '验证码',
    scanQrCode: '使用验证器应用扫描二维码',
    backupCodes: '备用码',
    saveBackupCodes: '请将这些备用码保存在安全的地方',
    
    // Profile
    profile: '个人资料',
    myProfile: '我的资料',
    editProfile: '编辑资料',
    accountSettings: '账户设置',
    personalInformation: '个人信息',
    securitySettings: '安全设置',
    
    // Activity
    activity: '活动',
    recentActivity: '最近活动',
    loginHistory: '登录历史',
    ipAddress: 'IP地址',
    device: '设备',
    browser: '浏览器',
    location: '位置',
    date: '日期',
    
    // Notifications
    notifications: '通知',
    notificationSettings: '通知设置',
    emailNotifications: '邮件通知',
    pushNotifications: '推送通知',
    
    // Subscriptions
    subscriptions: '订阅',
    mySubscriptions: '我的订阅',
    subscribeTo: '订阅 {item}',
    unsubscribeFrom: '取消订阅 {item}',
    
    // Statistics
    statistics: '统计',
    postsCount: '文章',
    commentsCount: '评论',
    likesCount: '点赞',
    
    // Buttons
    save: '保存',
    update: '更新',
    confirm: '确认',
    submit: '提交',
    
    // Messages
    userSaved: '用户保存成功',
    userUpdated: '用户更新成功',
    userDeleted: '用户删除成功',
    passwordChanged: '密码修改成功',
    profileUpdated: '个人资料更新成功',
    avatarUpdated: '头像更新成功',
    settingsSaved: '设置保存成功',
    invalidCredentials: '邮箱或密码无效',
    accountCreated: '账户创建成功',
    accountBanned: '该账户已被禁用',
    accountInactive: '该账户处于不活跃状态',
    accountPending: '该账户正在等待审核',
    confirmDelete: '确定要删除此用户吗？',
    deleteWarning: '此操作无法撤销',
    
    // Placeholders
    emailPlaceholder: '请输入邮箱',
    usernamePlaceholder: '请输入用户名',
    passwordPlaceholder: '请输入密码',
    searchUsers: '搜索用户...',
    
    // Validation
    validation: {
      required: '{field}为必填项',
      email: '请输入有效的邮箱地址',
      passwordMismatch: '两次输入的密码不一致',
      passwordLength: '密码长度必须至少为{length}个字符',
      passwordStrength: '密码必须包含至少一个大写字母、一个小写字母、一个数字和一个特殊字符',
      usernameFormat: '用户名只能包含字母、数字和下划线',
      emailExists: '该邮箱已被注册',
      usernameExists: '该用户名已被占用',
      invalidCode: '无效的验证码'
    },
    
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
    
    confirmDelete: {
      title: '警告',
      message: '确定要删除用户 "{username}" 吗？此操作不可恢复！',
      confirmButton: '确定',
      cancelButton: '取消',
      success: '删除成功',
      failed: '删除失败'
    },
    
    // Error messages
    error: {
      getUserList: '获取用户列表失败',
      getUserDetail: '获取用户详情失败',
      updateUser: '更新失败',
      exportFailed: '导出失败，请稍后重试',
      fetchStatistics: '获取统计数据失败，请稍后重试',
      fetchChartData: '获取图表数据失败，请稍后重试',
      renderChart: '渲染图表失败，请刷新页面重试',
      noData: '所选时期没有可用数据',
      statusChange: '更改用户状态失败',
      deleteUser: '删除用户失败'
    },
    
    // Button texts in charts
    retry: '重试',
    
    // Also add cancel at root level if it doesn't exist
    cancel: '取消'
  }
}   