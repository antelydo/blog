export default {
  adminComment: {
    // 评论管理
    title: '评论管理',
    listTitle: '评论列表',
    detailTitle: '评论详情',

    // 列表相关
    list: {
      id: 'ID',
      articleTitle: '文章标题',
      content: '评论内容',
      user: '评论用户',
      type: '评论类型',
      ip: 'IP地址',
      userAgent: '用户代理',
      status: '状态',
      createTime: '评论时间',
      updateTime: '更新时间',
      operations: '操作',
      likes: '点赞数',
      parentComment: '父评论',
      total: '共 {total} 条评论'
    },

    // 筛选相关
    filter: {
      keyword: '搜索评论内容/用户',
      status: '状态',
      allStatus: '全部状态',
      post: '文章',
      userType: '用户类型',
      dateRange: '时间范围',
      search: '搜索',
      reset: '重置'
    },

    // 评论类型
    type: {
      normal: '普通评论',
      admin: '管理员',
      author: '作者',
      guest: '访客',
      user: '用户',
      unknown: '未知'
    },

    // 评论状态
    status: {
      approved: '已通过',
      pending: '待审核',
      rejected: '已拒绝',
      unknown: '未知',
      1: '已通过',
      0: '待审核',
      '-1': '已拒绝'
    },

    // 回复相关
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

    // 审核相关
    approve: {
      title: '审核评论',
      confirm: '确定要通过此评论吗？',
      success: '评论已通过审核',
      error: '更新评论状态失败',
      button: '通过'
    },

    // 状态修改相关
    changeStatus: {
      title: '修改评论状态',
      confirm: '确定要将评论状态修改为 {status} 吗？',
      success: '评论状态修改成功',
      error: '评论状态修改失败',
      button: '修改状态',
      select: '选择状态'
    },

    // 删除相关
    delete: {
      confirm: '确定要删除此评论吗？此操作不可恢复。',
      success: '删除评论成功',
      error: '删除评论失败',
      warning: '警告',
      confirmButton: '确定',
      cancelButton: '取消',
      button: '删除'
    },

    // 编辑器相关
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

    // 错误信息
    error: {
      fetchFailed: '获取评论列表失败',
      networkError: '获取评论列表失败，请检查网络连接',
      unexpectedFormat: '评论数据格式异常'
    },

    // 其他
    noTitle: '无标题',
    noContent: '无内容',
    anonymousUser: '匿名用户',
    userAgent: {
      unknown: '未知',
      other: '其他浏览器'
    },
    viewArticle: '查看文章',
    replyButton: '回复',
    approveButton: '通过',
    deleteButton: '删除',
    likeCount: '点赞数: {count}',
    exportCsv: '导出CSV',
    exportExcel: '导出Excel',
    exportSuccess: '导出成功',
    batchApprove: '批量通过',
    batchDelete: '批量删除',
    batchApproveSuccess: '批量审核通过成功',
    batchApproveError: '批量审核通过失败',
    batchDeleteSuccess: '批量删除成功',
    batchDeleteError: '批量删除失败',
    batchDeleteConfirm: '确定要删除选中的评论吗？此操作不可恢复。',
    batchChangeStatusSuccess: '批量修改状态成功',
    batchChangeStatusError: '批量修改状态失败',
    noUnapprovedComments: '没有待审核的评论',
    selectAll: '全选',
    selectedCount: '已选择 {count} 项',
    commentDetail: '评论详情',
    userInfo: '用户信息',
    deviceInfo: '设备信息',
    browser: '浏览器',
    os: '操作系统',
    refreshList: '刷新列表',
    noData: '暂无数据',
    loading: '加载中...',
    expand: '展开',
    collapse: '收起',
    showParent: '显示父评论',
    hideParent: '隐藏父评论'
  }
};
