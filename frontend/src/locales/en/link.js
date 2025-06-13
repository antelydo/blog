export default {
  link: {
    title: 'Link',
    listSuccess: 'Links retrieved successfully',
    listError: 'Failed to retrieve links',
    link_already_exists: 'Link with this title or URL already exists',
    link_id_required: 'Link ID is required',
    link_not_exist: 'Link does not exist',
    create_link_log: 'Created link',
    update_link_log: 'Updated link',
    delete_link_log: 'Deleted link',
    createSuccess: 'Link created successfully',
    createError: 'Failed to create link',
    updateSuccess: 'Link updated successfully',
    updateError: 'Failed to update link',
    deleteSuccess: 'Link deleted successfully',
    deleteError: 'Failed to delete link',
    addError: 'Failed to add link',
    invalidSortData: 'Invalid sort data',
    sortUpdateSuccess: 'Sort order updated successfully',
    sortUpdateError: 'Failed to update sort order',
    friendLinks: 'Friend Links',
    friendLinksManagement: 'Friend Links Management',
    linkList: 'Link List',
    addFriendLink: 'Add Friend Link',
    editFriendLink: 'Edit Friend Link',
    deleteFriendLink: 'Delete Friend Link',
    id: 'ID',
    name: 'Name',
    url: 'URL',
    description: 'Description',
    logo: 'Logo',
    category: 'Category',
    status: 'Status',
    sortOrder: 'Sort Order',
    createdAt: 'Created At',
    updatedAt: 'Updated At',
    active: 'Active',
    inactive: 'Inactive',
    pending: 'Pending Review',
    personal: 'Personal Blog',
    tech: 'Technology',
    design: 'Design',
    news: 'News',
    entertainment: 'Entertainment',
    education: 'Education',
    business: 'Business',
    other: 'Other',
    view: 'View',
    edit: 'Edit',
    delete: 'Delete',
    save: 'Save',
    cancel: 'Cancel',
    linkDetails: 'Link Details',
    linkName: 'Link Name',
    linkUrl: 'Link URL',
    linkLogo: 'Link Logo',
    linkDescription: 'Link Description',
    selectCategory: 'Select Category',
    selectStatus: 'Select Status',
    uploadLogo: 'Upload Logo',
    removeLogo: 'Remove Logo',
    namePlaceholder: 'Enter link name',
    urlPlaceholder: 'Enter website URL',
    descriptionPlaceholder: 'Enter a brief description',
    searchLinks: 'Search links...',
    confirmDelete: 'Are you sure you want to delete this link?',
    deleteWarning: 'This action cannot be undone.',
    linkSaved: 'Link saved successfully',
    linkUpdated: 'Link updated successfully',
    linkDeleted: 'Link deleted successfully',
    validation: {
      required: '{field} is required',
      url: 'Please enter a valid URL',
      maxLength: '{field} cannot exceed {length} characters',
      invalidImage: 'Please upload a valid image file',
      urlExists: 'This URL is already in the friend links list'
    }
  },
  linkManagement: {
    title: 'Link Management',
    addLink: 'Add Link',
    editLink: 'Edit Link',
    deleteConfirm: 'Are you sure you want to delete this link?',
    saveSuccess: 'Link saved successfully',
    deleteSuccess: 'Link deleted successfully',
    
    
    search: {
      title: 'Title',
      titlePlaceholder: 'Search by title',
      status: 'Status',
      statusPlaceholder: 'Select status',
      type: 'Type',
      typePlaceholder: 'Select type'
    },
    
    table: {
      id: 'ID',
      title: 'Title',
      url: 'URL',
      email: 'Email',
      description: 'Description',
      logo: 'Logo',
      sort: 'Sort Order',
      status: 'Status',
      type: 'Type'
    },
    
    status: {
      enabled: 'Enabled',
      disabled: 'Disabled'
    },
    
    form: {
      title: 'Title',
      titlePlaceholder: 'Please enter link title',
      url: 'URL',
      urlPlaceholder: 'Please enter link URL',
      logo: 'Logo',
      uploadLogo: 'Upload Logo',
      logoTip: 'Recommended size: 80x80px, file types: jpg, png, gif, max size: 2MB',
      sort: 'Sort Order',
      status: 'Status',
      type: 'Type',
      typePlaceholder: 'Please select type'
    },
    
    validation: {
      titleRequired: 'Title is required',
      titleLength: 'Title length must be between 2 and 50 characters',
      urlRequired: 'URL is required',
      urlFormat: 'Please enter a valid URL',
      logoRequired: 'Logo is required',
      sortRequired: 'Sort order is required',
      typeRequired: 'Type is required'
    },
    
    error: {
      add: 'Failed to add link',
      edit: 'Failed to update link',
      delete: 'Failed to delete link',
      fetch: 'Failed to fetch links'
    }
  },
  friendshipLinks: {
    title: 'Friendship Links',
    description: 'Exchange links with our partners',
    applyLink: 'Apply for Link Exchange',
    noLinks: 'No friendship links to display',
    applicationForm: {
      title: 'Link Exchange Application',
      siteName: 'Site Name',
      siteUrl: 'Site URL',
      siteLogo: 'Site Logo',
      siteDescription: 'Site Description',
      contactEmail: 'Contact Email',
      captcha: 'Verification Code',
      submit: 'Submit Application',
      reset: 'Reset'
    },
    validation: {
      siteNameRequired: 'Site name is required',
      siteUrlRequired: 'Site URL is required',
      siteUrlFormat: 'Please enter a valid URL',
      siteLogoRequired: 'Site logo is required',
      siteDescriptionRequired: 'Site description is required',
      contactEmailRequired: 'Contact email is required',
      contactEmailFormat: 'Please enter a valid email',
      captchaRequired: 'Verification code is required'
    },
    message: {
      applicationSuccess: 'Application submitted successfully. We will review it as soon as possible.',
      applicationError: 'Failed to submit application. Please try again later.',
      captchaError: 'Incorrect verification code'
    }
  }
} 