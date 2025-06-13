export default {
  adminComment: {
    // Comment management
    title: 'Comment Management',
    listTitle: 'Comment List',
    detailTitle: 'Comment Details',

    // List related
    list: {
      id: 'ID',
      articleTitle: 'Article Title',
      content: 'Comment Content',
      user: 'Commenter',
      type: 'Comment Type',
      ip: 'IP Address',
      userAgent: 'User Agent',
      status: 'Status',
      createTime: 'Comment Time',
      updateTime: 'Update Time',
      operations: 'Operations',
      likes: 'Likes',
      parentComment: 'Parent Comment',
      total: 'Total {total} Comments'
    },

    // Filter related
    filter: {
      keyword: 'Search comment content/user',
      status: 'Status',
      allStatus: 'All Status',
      post: 'Article',
      userType: 'User Type',
      dateRange: 'Date Range',
      search: 'Search',
      reset: 'Reset'
    },

    // Comment types
    type: {
      normal: 'Normal',
      admin: 'Admin',
      author: 'Author',
      guest: 'Guest',
      user: 'User',
      unknown: 'Unknown'
    },

    // Comment status
    status: {
      approved: 'Approved',
      pending: 'Pending',
      rejected: 'Rejected',
      unknown: 'Unknown',
      1: 'Approved',
      0: 'Pending',
      '-1': 'Rejected'
    },

    // Reply related
    reply: {
      title: 'Reply to Comment',
      originalComment: 'Original Comment',
      content: 'Reply Content',
      submit: 'Post Reply',
      cancel: 'Cancel',
      contentRequired: 'Reply content is required',
      contentLength: 'Length must be between 2 and 500 characters',
      success: 'Reply posted successfully',
      error: 'Failed to post reply',
      badge: 'Reply'
    },

    // Approval related
    approve: {
      title: 'Approve Comment',
      confirm: 'Are you sure you want to approve this comment?',
      success: 'Comment approved successfully',
      error: 'Failed to update comment status',
      button: 'Approve'
    },

    // Status change related
    changeStatus: {
      title: 'Change Comment Status',
      confirm: 'Are you sure you want to change the comment status to {status}?',
      success: 'Comment status changed successfully',
      error: 'Failed to change comment status',
      button: 'Change Status',
      select: 'Select Status'
    },

    // Delete related
    delete: {
      confirm: 'Are you sure you want to delete this comment? This action cannot be undone.',
      success: 'Comment deleted successfully',
      error: 'Failed to delete comment',
      warning: 'Warning',
      confirmButton: 'Confirm',
      cancelButton: 'Cancel',
      button: 'Delete'
    },

    // Editor related
    editor: {
      toolbar: {
        bold: 'Bold',
        italic: 'Italic',
        underline: 'Underline',
        emoji: {
          smile: 'Smile',
          thumbsUp: 'Thumbs Up',
          heart: 'Heart',
          thanks: 'Thanks'
        },
        clear: 'Clear Formatting'
      },
      charCount: '{count}/500'
    },

    // Error messages
    error: {
      fetchFailed: 'Failed to fetch comments',
      networkError: 'Network error, please check your connection',
      unexpectedFormat: 'Unexpected comment data format'
    },

    // Others
    noTitle: 'No Title',
    noContent: 'No Content',
    anonymousUser: 'Anonymous User',
    userAgent: {
      unknown: 'Unknown',
      other: 'Other Browser'
    },
    viewArticle: 'View Article',
    replyButton: 'Reply',
    approveButton: 'Approve',
    deleteButton: 'Delete',
    likeCount: 'Likes: {count}',
    exportCsv: 'Export CSV',
    exportExcel: 'Export Excel',
    exportSuccess: 'Export successful',
    batchApprove: 'Batch Approve',
    batchDelete: 'Batch Delete',
    batchApproveSuccess: 'Batch approval successful',
    batchApproveError: 'Batch approval failed',
    batchDeleteSuccess: 'Batch delete successful',
    batchDeleteError: 'Batch delete failed',
    batchDeleteConfirm: 'Are you sure you want to delete the selected comments? This action cannot be undone.',
    batchChangeStatusSuccess: 'Batch status change successful',
    batchChangeStatusError: 'Batch status change failed',
    noUnapprovedComments: 'No unapproved comments',
    selectAll: 'Select All',
    selectedCount: '{count} items selected',
    commentDetail: 'Comment Details',
    userInfo: 'User Information',
    deviceInfo: 'Device Information',
    browser: 'Browser',
    os: 'Operating System',
    refreshList: 'Refresh List',
    noData: 'No Data',
    loading: 'Loading...',
    expand: 'Expand',
    collapse: 'Collapse',
    showParent: 'Show Parent Comment',
    hideParent: 'Hide Parent Comment'
  }
};
