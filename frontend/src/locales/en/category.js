export default {
  category: {
    title: 'Category Management',
    add: 'Add Category',
    edit: 'Edit Category',
    delete: 'Delete',
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
    status: {
      active: 'Active',
      inactive: 'Inactive',
      enabled: 'Enabled',
      disabled: 'Disabled'
    },
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
    placeholder: {
      name: 'Please enter category name',
      slug: 'Please enter category slug',
      description: 'Please enter category description',
      icon: 'Please select an icon',
      status: 'Please select status',
      parentCategory: 'Select parent category, leave empty for top level',
      sort: 'Enter sort order'
    },
    validation: {
      name: 'Category name is required',
      nameLength: 'Length must be between 2 and 50 characters',
      slug: 'Category slug is required',
      slugFormat: 'Slug can only contain letters, numbers and hyphens',
      slugUnique: 'This slug is already in use',
      status: 'Status is required',
      description: 'Description cannot exceed 200 characters',
      sort: 'Sort order is required'
    },
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
    warning: 'Warning'
  }
} 