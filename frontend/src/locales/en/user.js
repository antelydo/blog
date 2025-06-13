export default {
    user: {
      title: 'Users',
      management: 'User Management',
      list: {
        title: 'User List',
        search: 'Search username/nickname/email',
        filter: 'Filter',
        reset: 'Reset',
        export: 'Export Data',
        exportCSV: 'Export as CSV',
        exportExcel: 'Export as Excel',
        exportAsCSV: 'Export as CSV',
        exportAsExcel: 'Export as Excel',
        operations: 'Operations',
        retry: 'Retry',
        searchPlaceholder: 'Search username/nickname/email',
        statusPlaceholder: 'Status',
        statusAll: 'All',
        statusNormal: 'Normal',
        statusDisabled: 'Disabled',
        searchButton: 'Search',
        resetButton: 'Reset',
        exportData: 'Export Data',
        registerTime: 'Registration Time',
        lastLoginTime: 'Last Login Time',
        enableUser: 'Enable',
        disableUser: 'Disable',
        deleteUser: 'Delete',
        noDataToExport: 'No data to export',
        exportSuccess: 'Export successful: {fileName}'
      },
      detail: 'User Detail',
      add: 'Add User',
      edit: 'Edit User',
      delete: 'Delete User',
      deleteConfirm: 'Are you sure you want to delete this user?',
      deleteSuccess: 'User deleted successfully',
      deleteError: 'Failed to delete user',
      
      // User form fields
      form: {
        createTitle: 'Create New User',
        editTitle: 'Edit User Information',
        submitButton: 'Save',
        cancelButton: 'Cancel',
        username: 'Username',
        usernamePlaceholder: 'Please enter username',
        usernameRule: 'Username must be 3-20 characters',
        nickname: 'Nickname',
        nicknamePlaceholder: 'Please enter nickname',
        email: 'Email',
        emailPlaceholder: 'Please enter email',
        emailRule: 'Please enter a valid email address',
        mobile: 'Phone Number',
        mobilePlaceholder: 'Please enter mobile number',
        mobileRule: 'Please enter a valid mobile number',
        password: 'Password',
        passwordPlaceholder: 'Please enter password',
        passwordRule: 'Password must be 6-20 characters',
        confirmPassword: 'Confirm Password',
        confirmPasswordPlaceholder: 'Please confirm password',
        confirmPasswordRule: 'Passwords do not match',
        role: 'Role',
        rolePlaceholder: 'Please select role',
        status: 'Status',
        statusPlaceholder: 'Please select status',
        avatar: 'Avatar',
        avatarUpload: 'Upload Avatar',
        bio: 'Bio',
        bioPlaceholder: 'Please enter user bio',
        remark: 'Remark',
        remarkPlaceholder: 'Please enter remark (admin only)',
        submit: 'Submit',
        cancel: 'Cancel',
        reset: 'Reset',
        addTitle: 'Create New User',
        nicknameRequired: 'Please enter nickname',
        nicknameLength: 'Length must be between 2 and 20 characters',
        emailRequired: 'Please enter email',
        emailInvalid: 'Please enter a valid email address',
        mobileInvalid: 'Please enter a valid phone number',
        active: 'Active',
        inactive: 'Inactive',
        registerTime: 'Registration Time',
        lastLoginTime: 'Last Login Time',
        confirm: 'Confirm'
      },
      
      username: 'Username',
      nickname: 'Nickname',
      email: 'Email',
      mobile: 'Mobile',
      password: 'Password',
      confirmPassword: 'Confirm Password',
      role: 'Role',
      status: 'Status',
      createdAt: 'Created At',
      updatedAt: 'Updated At',
      lastLogin: 'Last Login',
      loginIp: 'Login IP',
      
      // Status options
      status: {
        all: 'All',
        active: 'Active',
        inactive: 'Inactive',
        suspended: 'Suspended',
        pending: 'Pending Approval',
        1: 'Normal',
        0: 'Disabled'
      },
      
      // Role options
      role: {
        admin: 'Admin',
        editor: 'Editor',
        user: 'User'
      },
      
      // Messages
      createSuccess: 'User created successfully',
      updateSuccess: 'User updated successfully',
      deleteSuccess: 'User deleted successfully',
      statusChangeSuccess: 'User status changed successfully',
      
      // Validation
      usernameRequired: 'Username is required',
      emailRequired: 'Email is required',
      emailInvalid: 'Please enter a valid email address',
      passwordRequired: 'Password is required',
      passwordLength: 'Password must be at least a minimum of 8 characters',
      passwordMismatch: 'Passwords do not match',
      
      // User statistics
      statistics: {
        title: 'User Statistics',
        description: 'View statistics about user registrations and activity',
        totalUsers: 'Total Users',
        totalUsersTooltip: 'Total number of registered users',
        activeUsers: 'Active Users',
        activeUsersTooltip: 'Users who logged in recently',
        activityRate: 'Activity Rate',
        newToday: 'New Today',
        newTodayTooltip: 'Users registered in the last 24 hours',
        comparedToLastMonth: 'compared to last month',
        comparedToYesterday: 'compared to yesterday',
        newUsers: 'New Users',
        inactiveUsers: 'Inactive Users',
        totalAdmin: 'Total Admins',
        totalEditor: 'Total Editors',
        totalRegular: 'Total Regular Users',
        registrationTrend: 'Registration Trend',
        activityTrend: 'Activity Trend',
        loginFrequency: 'Login Frequency',
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        yearly: 'Yearly'
      },
      
      // Charts section
      charts: {
        registrationTrend: 'User Registration Trend',
        activityTrend: 'User Activity Trend',
        noData: 'No data available for the selected period',
        week: 'Week',
        month: 'Month'
      },
      
      fields: {
        id: 'ID',
        actions: 'Actions'
      },
      
      actions: {
        view: 'View',
        edit: 'Edit',
        delete: 'Delete',
        ban: 'Ban',
        unban: 'Unban',
        activate: 'Activate',
        deactivate: 'Deactivate',
        enable: 'Enable',
        disable: 'Disable'
      },
      
      errors: {
        createFailed: 'Failed to create user',
        updateFailed: 'Failed to update user',
        deleteFailed: 'Failed to delete user',
        statusChangeFailed: 'Failed to change user status',
        notFound: 'User not found',
        fetchFailed: 'Failed to fetch user data',
        chartError: 'Failed to load chart data',
        getUserList: 'Failed to get user list',
        updateUser: 'Failed to update user',
        renderChart: 'Failed to render chart'
      },
      
      // Actions and messages
      activate: 'Activate',
      deactivate: 'Deactivate',
      suspend: 'Suspend',
      resetPassword: 'Reset Password',
      resetPasswordConfirm: 'Are you sure you want to reset this user\'s password?',
      resetPasswordSuccess: 'Password reset successfully',
      resetPasswordError: 'Failed to reset password',
      saveSuccess: 'User saved successfully',
      saveError: 'Failed to save user',
      
      // Filters
      filter: {
        all: 'All',
        active: 'Active',
        inactive: 'Inactive',
        pending: 'Pending',
        suspended: 'Suspended',
        admin: 'Admin',
        editor: 'Editor',
        user: 'User',
        search: 'Search',
        searchPlaceholder: 'Search by username, email or nickname',
        dateRange: 'Date Range',
        startDate: 'Start Date',
        endDate: 'End Date',
        apply: 'Apply',
        reset: 'Reset',
        sort: 'Sort',
        sortBy: 'Sort By',
        order: 'Order',
        ascending: 'Ascending',
        descending: 'Descending'
      },
      
      // User Management
      userList: 'User List',
      addUser: 'Add User',
      editUser: 'Edit User',
      viewUser: 'View User',
      
      // User Status
      active: 'Active',
      inactive: 'Inactive',
      banned: 'Banned',
      pending: 'Pending Approval',
      
      // User Information
      id: 'ID',
      username: 'Username',
      email: 'Email',
      password: 'Password',
      newPassword: 'New Password',
      confirmPassword: 'Confirm Password',
      avatar: 'Avatar',
      displayName: 'Display Name',
      firstName: 'First Name',
      lastName: 'Last Name',
      fullName: 'Full Name',
      phone: 'Phone',
      website: 'Website',
      bio: 'Bio',
      status: 'Status',
      role: 'Role',
      roles: 'Roles',
      permissions: 'Permissions',
      createdAt: 'Created',
      updatedAt: 'Updated',
      lastLogin: 'Last Login',
      registrationDate: 'Registration Date',
      uploadAvatar: 'Upload Avatar',
      removeAvatar: 'Remove Avatar',
      
      // User Roles
      admin: 'Administrator',
      editor: 'Editor',
      author: 'Author',
      contributor: 'Contributor',
      subscriber: 'Subscriber',
      
      // Authentication
      login: 'Login',
      logout: 'Logout',
      register: 'Register',
      forgotPassword: 'Forgot Password',
      resetPassword: 'Reset Password',
      changePassword: 'Change Password',
      currentPassword: 'Current Password',
      sendResetLink: 'Send Reset Link',
      rememberMe: 'Remember Me',
      loginWith: 'Login with {provider}',
      passwordReset: 'Password Reset',
      passwordResetSent: 'Password reset instructions sent to your email',
      
      // Two-Factor Authentication
      twoFactor: 'Two-Factor Authentication',
      enableTwoFactor: 'Enable Two-Factor Authentication',
      disableTwoFactor: 'Disable Two-Factor Authentication',
      twoFactorEnabled: 'Two-Factor Authentication Enabled',
      twoFactorDisabled: 'Two-Factor Authentication Disabled',
      enterCode: 'Enter Verification Code',
      verificationCode: 'Verification Code',
      scanQrCode: 'Scan QR Code with Authenticator App',
      backupCodes: 'Backup Codes',
      saveBackupCodes: 'Save these backup codes in a secure location',
      
      // Profile
      profile: 'Profile',
      myProfile: 'My Profile',
      editProfile: 'Edit Profile',
      accountSettings: 'Account Settings',
      personalInformation: 'Personal Information',
      securitySettings: 'Security Settings',
      
      // Activity
      activity: 'Activity',
      recentActivity: 'Recent Activity',
      loginHistory: 'Login History',
      ipAddress: 'IP Address',
      device: 'Device',
      browser: 'Browser',
      location: 'Location',
      date: 'Date',
      
      // Notifications
      notifications: 'Notifications',
      notificationSettings: 'Notification Settings',
      emailNotifications: 'Email Notifications',
      pushNotifications: 'Push Notifications',
      
      // Subscriptions
      subscriptions: 'Subscriptions',
      mySubscriptions: 'My Subscriptions',
      subscribeTo: 'Subscribe to {item}',
      unsubscribeFrom: 'Unsubscribe from {item}',
      
      // Statistics
      statistics: 'Statistics',
      postsCount: 'Posts',
      commentsCount: 'Comments',
      likesCount: 'Likes',
      
      // Buttons
      save: 'Save',
      update: 'Update',
      cancel: 'Cancel',
      confirm: 'Confirm',
      submit: 'Submit',
      
      // Messages
      userSaved: 'User saved successfully',
      userUpdated: 'User updated successfully',
      userDeleted: 'User deleted successfully',
      passwordChanged: 'Password changed successfully',
      profileUpdated: 'Profile updated successfully',
      avatarUpdated: 'Avatar updated successfully',
      settingsSaved: 'Settings saved successfully',
      invalidCredentials: 'Invalid email or password',
      accountCreated: 'Account created successfully',
      accountBanned: 'This account has been banned',
      accountInactive: 'This account is inactive',
      accountPending: 'This account is pending approval',
      confirmDelete: 'Are you sure you want to delete this user?',
      deleteWarning: 'This action cannot be undone',
      
      // Placeholders
      emailPlaceholder: 'Enter your email',
      usernamePlaceholder: 'Enter your username',
      passwordPlaceholder: 'Enter your password',
      searchUsers: 'Search users...',
      
      // Validation
      validation: {
        required: '{field} is required',
        email: 'Please enter a valid email address',
        passwordMismatch: 'Passwords do not match',
        passwordLength: 'Password must be at least {length} characters',
        passwordStrength: 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character',
        usernameFormat: 'Username can only contain letters, numbers, and underscores',
        emailExists: 'This email is already registered',
        usernameExists: 'This username is already taken',
        invalidCode: 'Invalid verification code'
      }, 
      
      confirmStatus: {
        enableTitle: 'Enable User',
        disableTitle: 'Disable User',
        enableMessage: 'Are you sure you want to enable user {username}?',
        disableMessage: 'Are you sure you want to disable user {username}?',
        confirmButton: 'Confirm',
        cancelButton: 'Cancel',
        enableSuccess: 'User enabled successfully',
        disableSuccess: 'User disabled successfully',
        operationFailed: 'Operation failed'
      },
      
      confirmDelete: {
        title: 'Delete User',
        message: 'Are you sure you want to delete user {username}?',
        confirmButton: 'Delete',
        cancelButton: 'Cancel',
        success: 'User deleted successfully',
        failed: 'Failed to delete user'
      },
      
      // Error messages
      error: {
        getUserList: 'Failed to get user list',
        getUserDetail: 'Failed to get user details',
        updateUser: 'Failed to update user',
        exportFailed: 'Failed to export data',
        fetchStatistics: 'Failed to fetch statistics',
        fetchChartData: 'Failed to fetch chart data',
        renderChart: 'Failed to render chart, please refresh the page',
        noData: 'No data available for the selected period',
        statusChange: 'Failed to change user status',
        deleteUser: 'Failed to delete user'
      },
      
      // Button texts in charts
      retry: 'Retry',
      
      // Add direct cancel key at the user level, since code is looking for user.cancel
      cancel: 'Cancel'
    }
  } 