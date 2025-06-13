<?php
return [
    'adminComment' => [
        // Comment management
        'title' => 'Comment Management',
        'list_title' => 'Comment List',
        'detail_title' => 'Comment Details',
        
        // List related
        'id' => 'ID',
        'post_title' => 'Article Title',
        'content' => 'Comment Content',
        'user' => 'Commenter',
        'type' => 'Comment Type',
        'ip' => 'IP Address',
        'user_agent' => 'User Agent',
        'status' => 'Status',
        'create_time' => 'Comment Time',
        'update_time' => 'Update Time',
        'operations' => 'Operations',
        'likes' => 'Likes',
        'parent_comment' => 'Parent Comment',
        'total_comments' => 'Total {total} Comments',
        
        // Filter related
        'filter_keyword' => 'Search comment content/user',
        'filter_status' => 'Status',
        'filter_all_status' => 'All Status',
        'filter_post' => 'Article',
        'filter_user_type' => 'User Type',
        'filter_date_range' => 'Date Range',
        'filter_search' => 'Search',
        'filter_reset' => 'Reset',
        
        // Comment types
        'type_normal' => 'Normal',
        'type_admin' => 'Admin',
        'type_author' => 'Author',
        'type_guest' => 'Guest',
        'type_unknown' => 'Unknown',
        
        // Comment status
        'status_approved' => 'Approved',
        'status_pending' => 'Pending',
        'status_rejected' => 'Rejected',
        'status_unknown' => 'Unknown',
        'status_1' => 'Approved',
        'status_0' => 'Pending',
        'status_-1' => 'Rejected',
        
        // Reply related
        'reply_title' => 'Reply to Comment',
        'reply_original_comment' => 'Original Comment',
        'reply_content' => 'Reply Content',
        'reply_submit' => 'Post Reply',
        'reply_cancel' => 'Cancel',
        'reply_content_required' => 'Reply content is required',
        'reply_content_length' => 'Length must be between 2 and 500 characters',
        'reply_success' => 'Reply posted successfully',
        'reply_error' => 'Failed to post reply',
        'reply_badge' => 'Reply',
        
        // Approval related
        'approve_title' => 'Approve Comment',
        'approve_confirm' => 'Are you sure you want to approve this comment?',
        'approve_success' => 'Comment approved successfully',
        'approve_error' => 'Failed to update comment status',
        'approve_button' => 'Approve',
        
        // Delete related
        'delete_confirm' => 'Are you sure you want to delete this comment? This action cannot be undone.',
        'delete_success' => 'Comment deleted successfully',
        'delete_error' => 'Failed to delete comment',
        'delete_warning' => 'Warning',
        'delete_confirm_button' => 'Confirm',
        'delete_cancel_button' => 'Cancel',
        'delete_button' => 'Delete',
        
        // Editor related
        'editor_toolbar_bold' => 'Bold',
        'editor_toolbar_italic' => 'Italic',
        'editor_toolbar_underline' => 'Underline',
        'editor_toolbar_emoji_smile' => 'Smile',
        'editor_toolbar_emoji_thumbs_up' => 'Thumbs Up',
        'editor_toolbar_emoji_heart' => 'Heart',
        'editor_toolbar_emoji_thanks' => 'Thanks',
        'editor_toolbar_clear' => 'Clear Formatting',
        'editor_char_count' => '{count}/500',
        
        // Error messages
        'error_fetch_failed' => 'Failed to fetch comments',
        'error_network_error' => 'Network error, please check your connection',
        'error_unexpected_format' => 'Unexpected comment data format',
        
        // Others
        'no_title' => 'No Title',
        'no_content' => 'No Content',
        'anonymous_user' => 'Anonymous User',
        'user_agent_unknown' => 'Unknown',
        'user_agent_other' => 'Other Browser',
        'view_article' => 'View Article',
        'reply_button' => 'Reply',
        'like_count' => 'Likes: {count}',
        'export_csv' => 'Export CSV',
        'export_excel' => 'Export Excel',
        'batch_approve' => 'Batch Approve',
        'batch_delete' => 'Batch Delete',
        'select_all' => 'Select All',
        'selected_count' => '{count} items selected',
        'comment_detail' => 'Comment Details',
        'user_info' => 'User Information',
        'device_info' => 'Device Information',
        'browser' => 'Browser',
        'os' => 'Operating System',
        'refresh_list' => 'Refresh List',
        'no_data' => 'No Data',
        'loading' => 'Loading...',
        'expand' => 'Expand',
        'collapse' => 'Collapse',
        'show_parent' => 'Show Parent Comment',
        'hide_parent' => 'Hide Parent Comment',
    ]
];
