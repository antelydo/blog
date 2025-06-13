export default {
  favorite: {
    title: '收藏管理',
    list: {
      id: 'ID',
      articleTitle: '文章标题',
      user: '收藏用户',
      userType: '用户类型',
      ip: 'IP地址',
      createTime: '收藏时间',
      operations: '操作',
      deleteButton: '删除',
      viewButton: '详情',
      anonymousUser: '匿名用户'
    },
    filter: {
      keyword: '搜索文章标题/用户',
      userType: '用户类型',
      allTypes: '全部类型',
      registeredUser: '注册用户',
      admin: '管理员',
      search: '搜索',
      reset: '重置',
      placeholder: '搜索文章标题/用户',
      startDate: '开始日期',
      endDate: '结束日期'
    },
    userType: {
      user: '注册用户',
      admin: '管理员',
      unknown: '未知'
    },
    statistics: {
      totalFavorites: '总收藏数',
      todayFavorites: '今日新增',
      yesterdayFavorites: '昨日收藏',
      weekFavorites: '本周收藏',
      monthFavorites: '本月收藏',
      activeUsers: '参与用户数',
      userDistribution: '用户类型分布'
    },
    popularArticles: {
      title: '最受欢迎的文章',
      rank: '排名',
      articleTitle: '文章标题',
      favorites: '收藏数',
      author: '作者',
      publishTime: '发布时间'
    },
    delete: {
      confirm: '确定要删除这条收藏记录吗？',
      success: '收藏记录删除成功',
      error: '删除收藏记录失败',
      warning: '警告',
      confirmButton: '确定',
      cancelButton: '取消'
    },
    error: {
      fetchFailed: '获取收藏列表失败',
      statsFailed: '获取统计数据失败',
      networkError: '获取收藏列表失败，请检查网络连接'
    },
    batch: {
      selectedItems: '已选择 {count} 项',
      delete: '批量删除',
      export: '导出为CSV',
      clear: '清除选择',
      deleteConfirm: '确定要删除选中的 {count} 条收藏记录吗？',
      deleteSuccess: '成功删除 {count} 条收藏记录',
      deleteFailed: '有 {count} 条收藏记录删除失败',
      exportSuccess: '成功导出 {count} 条收藏记录',
      noSelection: '请至少选择一条记录进行导出',
      error: '批量操作过程中发生错误'
    },
    detail: {
      title: '收藏详情',
      articleInfo: '文章信息',
      articleTitle: '文章标题',
      articleId: '文章ID',
      favoriteTime: '收藏时间',
      userInfo: '用户信息',
      username: '用户名',
      userId: '用户ID',
      userType: '用户类型',
      userOtherFavorites: '用户其他收藏',
      close: '关闭',
      delete: '删除'
    },
    autoRefresh: {
      enabled: '自动刷新已启用',
      disabled: '自动刷新已禁用',
      started: '自动刷新已启动，每 {seconds} 秒刷新一次',
      stopped: '自动刷新已停止'
    }
  }
}
