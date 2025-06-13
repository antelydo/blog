export default {
  comment: {
    title: 'Comment Management',
    list: {
      articleTitle: 'Article Title',
      content: 'Comment Content',
      user: 'Commenter',
      type: 'Comment Type',
      ip: 'IP Address',
      userAgent: 'User Agent',
      status: 'Status',
      createTime: 'Comment Time',
      operations: 'Operations',
      replyButton: 'Reply',
      approveButton: 'Approve',
      deleteButton: 'Delete',
      parentComment: 'Original Comment',
      total: 'Total {total} Comments'
    },
    filter: {
      keyword: 'Search comment content/user',
      status: 'Status',
      allStatus: 'All Status',
      approved: 'Approved',
      pending: 'Pending',
      rejected: 'Rejected',
      search: 'Search',
      reset: 'Reset'
    },
    type: {
      normal: 'Normal',
      admin: 'Admin',
      author: 'Author',
      guest: 'Guest',
      unknown: 'Unknown'
    },
    status: {
      approved: 'Approved',
      pending: 'Pending',
      rejected: 'Rejected',
      unknown: 'Unknown',
      1: 'Approved',
      0: 'Pending',
      '-1': 'Rejected'
    },
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
    approve: {
      title: 'Approve Comment',
      confirm: 'Are you sure you want to approve this comment?',
      success: 'Comment approved successfully',
      error: 'Failed to update comment status'
    },
    delete: {
      confirm: 'Are you sure you want to delete this comment? This action cannot be undone.',
      success: 'Comment deleted successfully',
      error: 'Failed to delete comment',
      warning: 'Warning',
      confirmButton: 'Confirm',
      cancelButton: 'Cancel'
    },
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
    error: {
      fetchFailed: 'Failed to fetch comments',
      networkError: 'Network error, please check your connection',
      unexpectedFormat: 'Unexpected comment data format'
    },
    noTitle: 'No Title',
    noContent: 'No Content',
    anonymousUser: 'Anonymous User',
    userAgent: {
      unknown: 'Unknown',
      other: 'Other Browser'
    }
  }
} 