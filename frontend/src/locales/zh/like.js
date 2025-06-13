export default {
  like: {
    title: '点赞管理',
    list: {
      id: 'ID',
      articleTitle: '文章标题',
      user: '点赞用户',
      userType: '用户类型',
      ip: 'IP地址',
      createTime: '点赞时间',
      operations: '操作',
      deleteButton: '删除',
      anonymousUser: '匿名用户'
    },
    filter: {
      keyword: '搜索文章标题/用户',
      userType: '用户类型',
      allTypes: '全部类型',
      registeredUser: '注册用户',
      guest: '访客',
      admin: '管理员',
      search: '搜索',
      reset: '重置',
      placeholder: '搜索文章标题/用户'
    },
    userType: {
      user: '注册用户',
      guest: '访客',
      admin: '管理员',
      unknown: '未知'
    },
    statistics: {
      totalLikes: '总点赞数',
      todayLikes: '今日新增',
      likesPerUser: '人均点赞',
      activeUsers: '参与用户数',
      coverageRate: '点赞覆盖率',
      maxLikes: '最受欢迎文章点赞数'
    },
    popularArticles: {
      title: '最受欢迎的文章',
      rank: '排名',
      articleTitle: '文章标题',
      likes: '点赞数',
      views: '阅读量',
      author: '作者',
      publishTime: '发布时间'
    },
    delete: {
      confirm: '确定要删除此点赞记录吗？',
      success: '删除点赞记录成功',
      error: '删除点赞记录失败',
      warning: '警告',
      confirmButton: '确定',
      cancelButton: '取消'
    },
    error: {
      fetchFailed: '获取点赞列表失败',
      networkError: '获取点赞列表失败，请检查网络连接'
    }
  }
} 