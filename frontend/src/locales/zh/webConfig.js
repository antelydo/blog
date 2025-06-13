export default {
  config: {
    // 配置组
    groups: {
      basic: '基础配置',
      seo: 'SEO配置',
      post: '文章配置',
      comment: '评论配置',
      upload: '上传配置',
      social: '社交媒体',
      security: '安全配置',
      cache: '缓存配置',
      user: '用户配置'
    },

    // 基础配置
    site_name: '网站名称',
    site_description: '网站描述',
    site_keywords: '网站关键词',
    site_favicon: '网站图标',
    site_logo: '网站Logo',
    site_icp: 'ICP备案号',
    site_copyright: '版权信息',
    site_analytics: '统计代码',

    // SEO配置
    seo_title_suffix: '标题后缀',
    seo_description_length: '描述长度',
    seo_keywords_max: '关键词数量',
    seo_auto_description: '自动生成描述',
    seo_auto_keywords: '自动生成关键词',

    // 文章配置
    post_list_size: '文章列表数量',
    post_excerpt_length: '文章摘要长度',
    post_thumbnail_width: '缩略图宽度',
    post_thumbnail_height: '缩略图高度',
    post_related_count: '相关文章数量',
    post_hot_threshold: '热门文章阈值',

    // 评论配置
    comment_enabled: '启用评论',
    comment_audit: '评论审核',
    comment_need_audit: '评论需要审核',
    comment_captcha: '评论验证码',
    comment_interval: '评论间隔',
    comment_list_size: '评论列表数量',
    comment_max_length: '评论最大长度',
    comment_notify_admin: '评论通知管理员',
    comment_notify_email: '通知邮箱',

    // 上传配置
    upload_max_size: '最大上传大小',
    upload_allowed_types: '允许上传类型',
    upload_image_resize: '图片自动缩放',
    upload_image_max_width: '图片最大宽度',
    upload_image_max_height: '图片最大高度',
    upload_image_quality: '图片质量',

    // 社交媒体配置
    social_weibo: '微博',
    social_wechat: '微信',
    social_qq: 'QQ',
    social_email: '邮箱',
    social_github: 'GitHub',
    social_twitter: 'Twitter',
    social_facebook: 'Facebook',
    social_instagram: 'Instagram',

    // 安全配置
    security_login_captcha: '登录验证码',
    security_login_retry: '登录重试次数',
    security_login_lock_time: '登录锁定时间',
    security_token_expire: 'Token过期时间',
    security_xss_filter: 'XSS过滤',
    security_csrf_token: 'CSRF防护',

    // 缓存配置
    cache_enabled: '启用缓存',
    cache_type: '缓存类型',
    cache_expire: '缓存过期时间',
    cache_prefix: '缓存前缀',

    // 用户配置
    register_enabled: '启用注册',

    // 表单类型
    types: {
      text: '文本',
      textarea: '多行文本',
      number: '数字',
      radio: '单选',
      checkbox: '多选',
      select: '下拉选择',
      image: '图片',
      file: '文件',
      color: '颜色',
      date: '日期',
      time: '时间',
      datetime: '日期时间',
      switch: '开关'
    },

    // 操作
    actions: {
      save: '保存配置',
      reset: '重置',
      refresh: '刷新',
      edit: '编辑',
      delete: '删除',
      add: '添加',
      search: '搜索',
      filter: '筛选',
      import: '导入',
      export: '导出',
      backup: '备份',
      restore: '恢复'
    },

    // 提示
    tips: {
      saveSuccess: '配置保存成功',
      saveError: '配置保存失败',
      resetConfirm: '确定要重置所有配置吗？',
      resetSuccess: '配置重置成功',
      deleteConfirm: '确定要删除此配置吗？',
      deleteSuccess: '配置删除成功',
      importSuccess: '配置导入成功',
      exportSuccess: '配置导出成功',
      backupSuccess: '配置备份成功',
      restoreSuccess: '配置恢复成功',
      restoreConfirm: '确定要恢复配置吗？这将覆盖当前配置。'
    }
  }
}
