export default {
  link: {
    title: '链接',
    listSuccess: '链接获取成功',
    listError: '获取链接失败',
    link_already_exists: '该标题或URL的链接已存在',
    link_id_required: '链接ID是必需的',
    link_not_exist: '链接不存在',
    create_link_log: '创建链接',
    update_link_log: '更新链接',
    delete_link_log: '删除链接',
    createSuccess: '链接创建成功',
    createError: '创建链接失败',
    updateSuccess: '链接更新成功',
    updateError: '更新链接失败',
    deleteSuccess: '链接删除成功',
    deleteError: '删除链接失败',
    addError: '添加链接失败',
    invalidSortData: '无效的排序数据',
    sortUpdateSuccess: '排序更新成功',
    sortUpdateError: '更新排序失败',
    friendLinks: '友情链接',
    friendLinksManagement: '友情链接管理',
    linkList: '链接列表',
    addFriendLink: '添加友情链接',
    editFriendLink: '编辑友情链接',
    deleteFriendLink: '删除友情链接',
    id: 'ID',
    name: '名称',
    url: '网址',
    description: '描述',
    logo: '图标',
    category: '分类',
    status: '状态',
    sortOrder: '排序',
    createdAt: '创建时间',
    updatedAt: '更新时间',
    active: '活跃',
    inactive: '不活跃',
    pending: '待审核',
    personal: '个人博客',
    tech: '技术',
    design: '设计',
    news: '新闻',
    entertainment: '娱乐',
    education: '教育',
    business: '商业',
    other: '其他',
    view: '查看',
    edit: '编辑',
    delete: '删除',
    save: '保存',
    cancel: '取消',
    linkDetails: '链接详情',
    linkName: '链接名称',
    linkUrl: '链接地址',
    linkLogo: '链接图标',
    linkDescription: '链接描述',
    selectCategory: '选择分类',
    selectStatus: '选择状态',
    uploadLogo: '上传图标',
    removeLogo: '移除图标',
    namePlaceholder: '请输入链接名称',
    urlPlaceholder: '请输入网址',
    descriptionPlaceholder: '请输入简短描述',
    searchLinks: '搜索链接...',
    confirmDelete: '确定要删除此链接吗？',
    deleteWarning: '此操作无法撤销。',
    linkSaved: '链接保存成功',
    linkUpdated: '链接更新成功',
    linkDeleted: '链接删除成功',
    validation: {
      required: '{field}为必填项',
      url: '请输入有效的URL',
      maxLength: '{field}不能超过{length}个字符',
      invalidImage: '请上传有效的图片文件',
      urlExists: '此URL已存在于友情链接列表中'
    }
  },
  linkManagement: {
    title: '链接管理',
    addLink: '添加链接',
    editLink: '编辑链接',
    deleteConfirm: '确定要删除此链接吗？',
    saveSuccess: '链接保存成功',
    deleteSuccess: '链接删除成功',
    
    search: {
      title: '标题',
      titlePlaceholder: '搜索标题',
      status: '状态',
      statusPlaceholder: '选择状态',
      type: '类型',
      typePlaceholder: '选择类型'
    },
    
    table: {
      id: 'ID',
      title: '标题',
      url: '链接地址',
      email: '邮箱',
      description: '描述',
      logo: '图标',
      sort: '排序',
      status: '状态',
      type: '类型'
    },
    
    status: {
      enabled: '启用',
      disabled: '禁用'
    },
    
    form: {
      title: '标题',
      titlePlaceholder: '请输入链接标题',
      url: '链接地址',
      urlPlaceholder: '请输入链接URL',
      logo: '图标',
      uploadLogo: '上传图标',
      logoTip: '推荐尺寸：80x80像素，文件类型：jpg, png, gif，最大2MB',
      sort: '排序',
      status: '状态',
      type: '类型',
      typePlaceholder: '请选择类型'
    },
    
    validation: {
      titleRequired: '标题不能为空',
      titleLength: '标题长度必须在2到50个字符之间',
      urlRequired: 'URL不能为空',
      urlFormat: '请输入有效的URL',
      logoRequired: '图标不能为空',
      sortRequired: '排序不能为空',
      typeRequired: '类型不能为空'
    },
    
    error: {
      add: '添加链接失败',
      edit: '更新链接失败',
      delete: '删除链接失败',
      fetch: '获取链接列表失败'
    }
  },
  friendshipLinks: {
    title: '友情链接',
    description: '与合作伙伴交换链接',
    applyLink: '申请链接交换',
    noLinks: '暂无友情链接',
    applicationForm: {
      title: '链接交换申请',
      siteName: '网站名称',
      siteUrl: '网站地址',
      siteLogo: '网站图标',
      siteDescription: '网站描述',
      contactEmail: '联系邮箱',
      captcha: '验证码',
      submit: '提交申请',
      reset: '重置'
    },
    validation: {
      siteNameRequired: '网站名称不能为空',
      siteUrlRequired: '网站地址不能为空',
      siteUrlFormat: '请输入有效的URL',
      siteLogoRequired: '网站图标不能为空',
      siteDescriptionRequired: '网站描述不能为空',
      contactEmailRequired: '联系邮箱不能为空',
      contactEmailFormat: '请输入有效的邮箱地址',
      captchaRequired: '验证码不能为空'
    },
    message: {
      applicationSuccess: '申请提交成功，我们将尽快审核。',
      applicationError: '申请提交失败，请稍后再试。',
      captchaError: '验证码不正确'
    }
  }
} 