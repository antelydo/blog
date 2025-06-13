export default {
  like: {
    title: 'Like Management',
    list: {
      id: 'ID',
      articleTitle: 'Article Title',
      user: 'User',
      userType: 'User Type',
      ip: 'IP Address',
      createTime: 'Time',
      operations: 'Operations',
      deleteButton: 'Delete',
      anonymousUser: 'Anonymous User'
    },
    filter: {
      keyword: 'Search article/user',
      userType: 'User Type',
      allTypes: 'All Types',
      registeredUser: 'Registered User',
      guest: 'Guest',
      admin: 'Admin',
      search: 'Search',
      reset: 'Reset',
      placeholder: 'Search article title/user'
    },
    userType: {
      user: 'Registered User',
      guest: 'Guest',
      admin: 'Admin',
      unknown: 'Unknown'
    },
    statistics: {
      totalLikes: 'Total Likes',
      todayLikes: 'Today New',
      likesPerUser: 'Likes Per User',
      activeUsers: 'Active Users',
      coverageRate: 'Coverage Rate',
      maxLikes: 'Most Popular Article Likes'
    },
    popularArticles: {
      title: 'Most Popular Articles',
      rank: 'Rank',
      articleTitle: 'Article Title',
      likes: 'Likes',
      views: 'Views',
      author: 'Author',
      publishTime: 'Publish Time'
    },
    delete: {
      confirm: 'Are you sure you want to delete this like record?',
      success: 'Like record deleted successfully',
      error: 'Failed to delete like record',
      warning: 'Warning',
      confirmButton: 'Confirm',
      cancelButton: 'Cancel'
    },
    error: {
      fetchFailed: 'Failed to fetch like list',
      networkError: 'Failed to fetch like list, please check your network connection'
    }
  }
} 