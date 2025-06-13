export default {
  adminCat: {
    // Category management
    title: 'Category Management',
    add: 'Add Category',
    edit: 'Edit Category',
    delete: 'Delete',
    
    // Filters
    filters: {
      title: 'Filters',
      name: 'Name',
      slug: 'Slug',
      status: 'Status',
      search: 'Search',
      reset: 'Reset',
      keyword: 'Search category name',
      statusFilter: 'Status filter',
      all: 'All',
      active: 'Active',
      inactive: 'Inactive'
    },
    
    // Table
    table: {
      id: 'ID',
      name: 'Name',
      slug: 'Slug',
      description: 'Description',
      icon: 'Icon',
      path: 'Path',
      level: 'Level',
      sort: 'Sort',
      status: 'Status',
      createdAt: 'Created At',
      updatedAt: 'Updated At',
      operation: 'Operation',
      createTime: 'Create Time',
      updateTime: 'Update Time'
    },
    
    // Status
    status: {
      active: 'Active',
      inactive: 'Inactive',
      enabled: 'Enabled',
      disabled: 'Disabled'
    },
    
    // Dialog
    dialog: {
      addCategory: 'Add Category',
      editCategory: 'Edit Category',
      deleteCategory: 'Delete Category',
      confirmDelete: 'Are you sure to delete this category?',
      confirmDeleteWithChildren: 'Are you sure you want to delete the category "{name}"? {children}'
    },
    
    confirmDeleteChildren: 'This operation will delete all subcategories!',
    basicInfo: 'Basic Information',
    settings: 'Settings',
    parentCategory: 'Parent Category',
    
    // Placeholders
    placeholder: {
      name: 'Please enter category name',
      slug: 'Please enter category slug',
      description: 'Please enter category description',
      icon: 'Please select an icon',
      status: 'Please select status',
      parentCategory: 'Select parent category, leave empty for top level',
      sort: 'Enter sort order'
    },
    
    // Validation
    validation: {
      name: 'Category name cannot be empty',
      nameLength: 'Length must be between 2 and 50 characters',
      slug: 'Category slug cannot be empty',
      slugFormat: 'Slug can only contain letters, numbers, and hyphens',
      slugUnique: 'This slug is already in use',
      status: 'Status cannot be empty',
      description: 'Description cannot exceed 200 characters',
      sort: 'Sort order cannot be empty'
    },
    
    // Buttons
    button: {
      add: 'Add',
      edit: 'Edit',
      delete: 'Delete',
      save: 'Save',
      cancel: 'Cancel',
      confirm: 'Confirm',
      search: 'Search',
      reset: 'Reset'
    },
    
    // Messages
    message: {
      addSuccess: 'Category added successfully',
      addError: 'Failed to add category',
      editSuccess: 'Category updated successfully',
      editError: 'Failed to update category',
      deleteSuccess: 'Category deleted successfully',
      deleteError: 'Failed to delete category',
      statusEnabled: 'Category enabled',
      statusDisabled: 'Category disabled',
      statusUpdateError: 'Failed to update category status',
      fetchError: 'Failed to fetch category list',
      networkError: 'Failed to fetch category list, please check your network connection'
    },
    
    warning: 'Warning',
    refreshList: 'Refresh List',
    exportCsv: 'Export CSV',
    batchDelete: 'Batch Delete',
    selectedCount: '{count} items selected',
    noData: 'No Data',
    loading: 'Loading...'
  }
};
