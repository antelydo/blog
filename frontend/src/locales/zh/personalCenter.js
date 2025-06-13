export default {
  personalCenter: {
    // 页面标题和导航
    title: '个人中心',
    breadcrumb: {
      home: '首页',
      personalCenter: '个人中心'
    },
    
    // 左侧个人信息卡片
    userInfo: {
      id: 'ID',
      basicInfo: '基本信息',
      username: '用户名',
      email: '邮箱',
      mobile: '手机',
      gender: '性别',
      accountInfo: '账户信息',
      registerTime: '注册时间',
      lastLogin: '上次登录',
      accountStatus: '账户状态',
      normal: '正常',
      disabled: '已禁用',
      notSet: '未设置',
      male: '男',
      female: '女',
      secret: '保密'
    },
    
    // 右侧选项卡
    tabs: {
      basicInfo: '基本信息',
      accountSecurity: '账户安全'
    },
    
    // 基本信息表单
    basicForm: {
      basicInfo: '基本信息',
      username: '用户名',
      usernamePlaceholder: '请输入用户名',
      usernameRule: '用户名只能包含字母、数字、下划线和连字符',
      usernameLength: '长度在 3 到 20 个字符',
      nickname: '昵称',
      nicknamePlaceholder: '请输入昵称',
      nicknameLength: '长度在 2 到 20 个字符',
      email: '邮箱',
      emailPlaceholder: '请输入邮箱',
      emailInvalid: '请输入正确的邮箱格式',
      mobile: '手机号',
      mobilePlaceholder: '请输入手机号',
      mobileInvalid: '请输入正确的手机号码',
      realName: '真实姓名',
      realNamePlaceholder: '请输入真实姓名',
      realNameTip: '仅用于身份验证，不会公开显示',
      birthday: '生日',
      birthdayPlaceholder: '选择生日',
      birthdayTip: '用于生日提醒和年龄计算',
      gender: '性别',
      contactInfo: '联系信息',
      personalProfile: '个人简介',
      bioPlaceholder: '介绍一下自己吧...',
      bioTip: '个人简介将显示在您的个人页面',
      remark: '备注',
      remarkPlaceholder: '添加一些个人备注...',
      remarkTip: '备注信息仅自己可见，可用于记录一些个人信息',
      saveChanges: '保存更改',
      reset: '重置',
      checkingUsername: '正在检查用户名可用性...',
      usernameAvailable: '用户名可用',
      usernameUnavailable: '用户名已被使用',
      formChanged: '表单已修改，确定要离开吗？'
    },
    
    // 账户安全
    security: {
      changePassword: '修改密码',
      passwordTip: '定期修改密码可以保障您的账户安全',
      loginRecords: '登录记录',
      loginRecordsTip: '查看您的账号登录历史记录',
      viewRecords: '查看记录',
      securityLevel: '安全等级',
      securityTips: '安全建议',
      tip1: '设置强密码并定期更换',
      tip2: '绑定手机号和邮箱',
      tip3: '定期检查登录记录',
      tip4: '不要在公共设备上保存登录状态'
    },
    
    // 修改密码对话框
    changePasswordDialog: {
      title: '修改密码',
      currentPassword: '当前密码',
      currentPasswordPlaceholder: '请输入当前密码',
      newPassword: '新密码',
      newPasswordPlaceholder: '请输入新密码',
      confirmPassword: '确认新密码',
      confirmPasswordPlaceholder: '请再次输入新密码',
      passwordLength: '密码长度不能少于6个字符',
      passwordMismatch: '两次输入密码不一致',
      cancel: '取消',
      confirm: '确认修改'
    },
    
    // 登录历史对话框
    loginHistoryDialog: {
      title: '登录历史',
      totalLogins: '总登录次数',
      successLogins: '成功登录',
      failedLogins: '失败登录',
      loginRecords: '登录记录',
      noRecords: '暂无登录记录',
      loginIP: '登录IP',
      loginDevice: '登录设备',
      loginLocation: '登录地点',
      loginStatus: '登录状态',
      loginTime: '登录时间',
      remark: '备注',
      unknown: '未知',
      success: '成功',
      failed: '失败'
    },
    
    // 头像上传
    avatar: {
      upload: '上传头像',
      preview: '预览',
      cancel: '取消',
      confirm: '确认',
      selectDefault: '选择默认头像',
      fileTypeError: '只能上传 JPG/PNG/GIF 格式的图片',
      fileSizeError: '图片大小不能超过 2MB'
    },
    
    // 消息提示
    messages: {
      profileUpdateSuccess: '个人资料保存成功',
      profileUpdateFailed: '个人资料保存失败，请重试',
      passwordChangeSuccess: '密码修改成功',
      passwordChangeFailed: '密码修改失败，请重试',
      avatarUploadSuccess: '头像上传成功',
      avatarUploadFailed: '头像上传失败，请重试',
      getLoginLogsSuccess: '获取登录日志成功',
      getLoginLogsFailed: '获取登录日志失败，请重试',
      getUserInfoFailed: '获取用户信息失败，请重试'
    }
  }
}
