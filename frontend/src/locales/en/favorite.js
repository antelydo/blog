export default {
  favorite: {
    title: 'Favorite Management',
    list: {
      id: 'ID',
      articleTitle: 'Article Title',
      user: 'User',
      userType: 'User Type',
      ip: 'IP Address',
      createTime: 'Time',
      operations: 'Operations',
      deleteButton: 'Delete',
      viewButton: 'Details',
      anonymousUser: 'Anonymous User'
    },
    filter: {
      keyword: 'Search article/user',
      userType: 'User Type',
      allTypes: 'All Types',
      registeredUser: 'Registered User',
      admin: 'Admin',
      search: 'Search',
      reset: 'Reset',
      placeholder: 'Search article title/user',
      startDate: 'Start Date',
      endDate: 'End Date'
    },
    userType: {
      user: 'Registered User',
      admin: 'Admin',
      unknown: 'Unknown'
    },
    statistics: {
      totalFavorites: 'Total Favorites',
      todayFavorites: 'Today New',
      yesterdayFavorites: 'Yesterday',
      weekFavorites: 'This Week',
      monthFavorites: 'This Month',
      activeUsers: 'Active Users',
      userDistribution: 'User Distribution'
    },
    popularArticles: {
      title: 'Most Favorited Articles',
      rank: 'Rank',
      articleTitle: 'Article Title',
      favorites: 'Favorites',
      author: 'Author',
      publishTime: 'Publish Time'
    },
    delete: {
      confirm: 'Are you sure you want to delete this favorite record?',
      success: 'Favorite record deleted successfully',
      error: 'Failed to delete favorite record',
      warning: 'Warning',
      confirmButton: 'Confirm',
      cancelButton: 'Cancel'
    },
    error: {
      fetchFailed: 'Failed to fetch favorite list',
      statsFailed: 'Failed to fetch statistics',
      networkError: 'Failed to fetch favorite list, please check your network connection'
    },
    batch: {
      selectedItems: '{count} items selected',
      delete: 'Batch Delete',
      export: 'Export to CSV',
      clear: 'Clear Selection',
      deleteConfirm: 'Are you sure you want to delete {count} favorite records?',
      deleteSuccess: 'Successfully deleted {count} favorite records',
      deleteFailed: 'Failed to delete {count} favorite records',
      exportSuccess: 'Successfully exported {count} favorite records',
      noSelection: 'Please select at least one record to export',
      error: 'An error occurred during batch operation'
    },
    detail: {
      title: 'Favorite Details',
      articleInfo: 'Article Information',
      articleTitle: 'Article Title',
      articleId: 'Article ID',
      favoriteTime: 'Favorite Time',
      userInfo: 'User Information',
      username: 'Username',
      userId: 'User ID',
      userType: 'User Type',
      userOtherFavorites: 'User Other Favorites',
      close: 'Close',
      delete: 'Delete'
    },
    autoRefresh: {
      enabled: 'Auto-refresh enabled',
      disabled: 'Auto-refresh disabled',
      started: 'Auto-refresh started, refreshing every {seconds} seconds',
      stopped: 'Auto-refresh stopped'
    }
  }
}
