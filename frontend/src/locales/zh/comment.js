export default {
  comment: {
    title: '评论管理',
    list: {
      articleTitle: '文章标题',
      content: '评论内容',
      user: '评论用户',
      type: '评论类型',
      ip: 'IP地址',
      userAgent: '用户代理',
      status: '状态',
      createTime: '评论时间',
      operations: '操作',
      replyButton: '回复',
      approveButton: '通过',
      deleteButton: '删除',
      parentComment: '原评论',
      total: '共 {total} 条评论'
    },
    filter: {
      keyword: '搜索评论内容/用户',
      status: '状态',
      allStatus: '全部状态',
      approved: '已通过',
      pending: '待审核',
      rejected: '已拒绝',
      search: '搜索',
      reset: '重置'
    },
    type: {
      normal: '普通评论',
      admin: '管理员',
      author: '作者',
      guest: '访客',
      unknown: '未知'
    },
    status: {
      approved: '已通过',
      pending: '待审核',
      rejected: '已拒绝',
      unknown: '未知',
      1: '已通过',
      0: '待审核',
      '-1': '已拒绝'
    },
    reply: {
      title: '回复评论',
      originalComment: '原评论',
      content: '回复内容',
      submit: '发布回复',
      cancel: '取消',
      contentRequired: '请输入回复内容',
      contentLength: '长度在 2 到 500 个字符',
      success: '回复评论成功',
      error: '回复评论失败',
      badge: '回复'
    },
    approve: {
      title: '审核评论',
      confirm: '确定要通过此评论吗？',
      success: '评论已通过审核',
      error: '更新评论状态失败'
    },
    delete: {
      confirm: '确定要删除此评论吗？此操作不可恢复。',
      success: '删除评论成功',
      error: '删除评论失败',
      warning: '警告',
      confirmButton: '确定',
      cancelButton: '取消'
    },
    editor: {
      toolbar: {
        bold: '加粗',
        italic: '斜体',
        underline: '下划线',
        emoji: {
          smile: '笑脸',
          thumbsUp: '点赞',
          heart: '爱心',
          thanks: '谢谢'
        },
        clear: '清除格式'
      },
      charCount: '{count}/500'
    },
    error: {
      fetchFailed: '获取评论列表失败',
      networkError: '获取评论列表失败，请检查网络连接',
      unexpectedFormat: '评论数据格式异常'
    },
    noTitle: '无标题',
    noContent: '无内容',
    anonymousUser: '匿名用户',
    userAgent: {
      unknown: '未知',
      other: '其他浏览器'
    }
  }
} 