export default {
  config: {
    // Configuration groups
    groups: {
      basic: 'Basic Settings',
      seo: 'SEO Settings',
      post: 'Article Settings',
      comment: 'Comment Settings',
      upload: 'Upload Settings',
      social: 'Social Media',
      security: 'Security Settings',
      cache: 'Cache Settings',
      user: 'User Settings'
    },

    // Basic settings
    site_name: 'Site Name',
    site_description: 'Site Description',
    site_keywords: 'Site Keywords',
    site_favicon: 'Site Favicon',
    site_logo: 'Site Logo',
    site_icp: 'ICP License',
    site_copyright: 'Copyright Info',
    site_analytics: 'Analytics Code',

    // SEO settings
    seo_title_suffix: 'Title Suffix',
    seo_description_length: 'Description Length',
    seo_keywords_max: 'Max Keywords',
    seo_auto_description: 'Auto Description',
    seo_auto_keywords: 'Auto Keywords',

    // Article settings
    post_list_size: 'Articles Per Page',
    post_excerpt_length: 'Excerpt Length',
    post_thumbnail_width: 'Thumbnail Width',
    post_thumbnail_height: 'Thumbnail Height',
    post_related_count: 'Related Articles Count',
    post_hot_threshold: 'Popular Article Threshold',

    // Comment settings
    comment_enabled: 'Enable Comments',
    comment_audit: 'Comment Moderation',
    comment_need_audit: 'Comments Need Approval',
    comment_captcha: 'Comment Captcha',
    comment_interval: 'Comment Interval',
    comment_list_size: 'Comments Per Page',
    comment_max_length: 'Max Comment Length',
    comment_notify_admin: 'Notify Admin',
    comment_notify_email: 'Notification Email',

    // Upload settings
    upload_max_size: 'Max Upload Size',
    upload_allowed_types: 'Allowed File Types',
    upload_image_resize: 'Auto Resize Images',
    upload_image_max_width: 'Max Image Width',
    upload_image_max_height: 'Max Image Height',
    upload_image_quality: 'Image Quality',

    // Social media settings
    social_weibo: 'Weibo',
    social_wechat: 'WeChat',
    social_qq: 'QQ',
    social_email: 'Email',
    social_github: 'GitHub',
    social_twitter: 'Twitter',
    social_facebook: 'Facebook',
    social_instagram: 'Instagram',

    // Security settings
    security_login_captcha: 'Login Captcha',
    security_login_retry: 'Login Retry Limit',
    security_login_lock_time: 'Login Lock Time',
    security_token_expire: 'Token Expiration',
    security_xss_filter: 'XSS Filter',
    security_csrf_token: 'CSRF Protection',

    // Cache settings
    cache_enabled: 'Enable Cache',
    cache_type: 'Cache Type',
    cache_expire: 'Cache Expiration',
    cache_prefix: 'Cache Prefix',

    // User settings
    register_enabled: 'Enable Registration',

    // Form types
    types: {
      text: 'Text',
      textarea: 'Textarea',
      number: 'Number',
      radio: 'Radio',
      checkbox: 'Checkbox',
      select: 'Select',
      image: 'Image',
      file: 'File',
      color: 'Color',
      date: 'Date',
      time: 'Time',
      datetime: 'Datetime',
      switch: 'Switch'
    },

    // Actions
    actions: {
      save: 'Save Settings',
      reset: 'Reset',
      refresh: 'Refresh',
      edit: 'Edit',
      delete: 'Delete',
      add: 'Add',
      search: 'Search',
      filter: 'Filter',
      import: 'Import',
      export: 'Export',
      backup: 'Backup',
      restore: 'Restore'
    },

    // Tips
    tips: {
      saveSuccess: 'Settings saved successfully',
      saveError: 'Failed to save settings',
      resetConfirm: 'Are you sure you want to reset all settings?',
      resetSuccess: 'Settings reset successfully',
      deleteConfirm: 'Are you sure you want to delete this setting?',
      deleteSuccess: 'Setting deleted successfully',
      importSuccess: 'Settings imported successfully',
      exportSuccess: 'Settings exported successfully',
      backupSuccess: 'Settings backed up successfully',
      restoreSuccess: 'Settings restored successfully',
      restoreConfirm: 'Are you sure you want to restore settings? This will overwrite current settings.'
    }
  }
}
