export default {
  personalCenter: {
    // Page title and navigation
    title: 'Personal Center',
    breadcrumb: {
      home: 'Home',
      personalCenter: 'Personal Center'
    },
    
    // Left sidebar user info card
    userInfo: {
      id: 'ID',
      basicInfo: 'Basic Information',
      username: 'Username',
      email: 'Email',
      mobile: 'Mobile',
      gender: 'Gender',
      accountInfo: 'Account Information',
      registerTime: 'Registration Time',
      lastLogin: 'Last Login',
      accountStatus: 'Account Status',
      normal: 'Normal',
      disabled: 'Disabled',
      notSet: 'Not set',
      male: 'Male',
      female: 'Female',
      secret: 'Private'
    },
    
    // Right tabs
    tabs: {
      basicInfo: 'Basic Information',
      accountSecurity: 'Account Security'
    },
    
    // Basic information form
    basicForm: {
      basicInfo: 'Basic Information',
      username: 'Username',
      usernamePlaceholder: 'Enter username',
      usernameRule: 'Username can only contain letters, numbers, underscores and hyphens',
      usernameLength: 'Length between 3 and 20 characters',
      nickname: 'Nickname',
      nicknamePlaceholder: 'Enter nickname',
      nicknameLength: 'Length between 2 and 20 characters',
      email: 'Email',
      emailPlaceholder: 'Enter email',
      emailInvalid: 'Please enter a valid email address',
      mobile: 'Mobile',
      mobilePlaceholder: 'Enter mobile number',
      mobileInvalid: 'Please enter a valid mobile number',
      realName: 'Real Name',
      realNamePlaceholder: 'Enter real name',
      realNameTip: 'Only used for identity verification, will not be publicly displayed',
      birthday: 'Birthday',
      birthdayPlaceholder: 'Select birthday',
      birthdayTip: 'Used for birthday reminders and age calculation',
      gender: 'Gender',
      contactInfo: 'Contact Information',
      personalProfile: 'Personal Profile',
      bioPlaceholder: 'Introduce yourself...',
      bioTip: 'Your personal profile will be displayed on your personal page',
      remark: 'Remarks',
      remarkPlaceholder: 'Add some personal notes...',
      remarkTip: 'Remarks are only visible to yourself, can be used to record personal information',
      saveChanges: 'Save Changes',
      reset: 'Reset',
      checkingUsername: 'Checking username availability...',
      usernameAvailable: 'Username is available',
      usernameUnavailable: 'Username is already taken',
      formChanged: 'Form has been modified, are you sure you want to leave?'
    },
    
    // Account security
    security: {
      changePassword: 'Change Password',
      passwordTip: 'Regularly changing your password helps secure your account',
      loginRecords: 'Login Records',
      loginRecordsTip: 'View your account login history',
      viewRecords: 'View Records',
      securityLevel: 'Security Level',
      securityTips: 'Security Tips',
      tip1: 'Set a strong password and change it regularly',
      tip2: 'Bind your mobile number and email',
      tip3: 'Check login records regularly',
      tip4: 'Do not save login status on public devices'
    },
    
    // Change password dialog
    changePasswordDialog: {
      title: 'Change Password',
      currentPassword: 'Current Password',
      currentPasswordPlaceholder: 'Enter current password',
      newPassword: 'New Password',
      newPasswordPlaceholder: 'Enter new password',
      confirmPassword: 'Confirm Password',
      confirmPasswordPlaceholder: 'Confirm new password',
      passwordLength: 'Password must be at least 6 characters',
      passwordMismatch: 'Passwords do not match',
      cancel: 'Cancel',
      confirm: 'Confirm'
    },
    
    // Login history dialog
    loginHistoryDialog: {
      title: 'Login History',
      totalLogins: 'Total Logins',
      successLogins: 'Successful Logins',
      failedLogins: 'Failed Logins',
      loginRecords: 'Login Records',
      noRecords: 'No login records',
      loginIP: 'Login IP',
      loginDevice: 'Login Device',
      loginLocation: 'Login Location',
      loginStatus: 'Login Status',
      loginTime: 'Login Time',
      remark: 'Remark',
      unknown: 'Unknown',
      success: 'Success',
      failed: 'Failed'
    },
    
    // Avatar upload
    avatar: {
      upload: 'Upload Avatar',
      preview: 'Preview',
      cancel: 'Cancel',
      confirm: 'Confirm',
      selectDefault: 'Select Default Avatar',
      fileTypeError: 'You can only upload JPG/PNG/GIF files',
      fileSizeError: 'Image size cannot exceed 2MB'
    },
    
    // Messages
    messages: {
      profileUpdateSuccess: 'Profile updated successfully',
      profileUpdateFailed: 'Failed to update profile, please try again',
      passwordChangeSuccess: 'Password changed successfully',
      passwordChangeFailed: 'Failed to change password, please try again',
      avatarUploadSuccess: 'Avatar uploaded successfully',
      avatarUploadFailed: 'Failed to upload avatar, please try again',
      getLoginLogsSuccess: 'Login logs retrieved successfully',
      getLoginLogsFailed: 'Failed to retrieve login logs, please try again',
      getUserInfoFailed: 'Failed to get user information, please try again'
    }
  }
}
