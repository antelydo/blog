export default {
  blog: {
    title: 'Blog Management',
    filters: {
      title: 'Filters',
      title: 'Title',
      category: 'Category',
      status: 'Status',
      author: 'Author',
      dateRange: 'Date Range',
      clear: 'Clear Filters',
      apply: 'Apply Filters'
    },
    table: {
      title: 'Title',
      category: 'Category',
      author: 'Author',
      status: 'Status',
      publishDate: 'Publish Date',
      views: 'Views',
      comments: 'Comments',
      actions: 'Actions',
      noRecords: 'No blog posts found'
    },
    status: {
      draft: 'Draft',
      published: 'Published',
      archived: 'Archived'
    },
    actions: {
      add: 'Add Post',
      edit: 'Edit',
      delete: 'Delete',
      preview: 'Preview',
      publish: 'Publish',
      unpublish: 'Unpublish'
    },
    form: {
      addTitle: 'Add New Post',
      editTitle: 'Edit Post',
      title: 'Title',
      content: 'Content',
      category: 'Category',
      tags: 'Tags',
      status: 'Status',
      publishDate: 'Publish Date',
      featuredImage: 'Featured Image',
      metaTitle: 'Meta Title',
      metaDescription: 'Meta Description',
      submit: 'Submit',
      cancel: 'Cancel',
      saveDraft: 'Save as Draft'
    },
    messages: {
      addSuccess: 'Blog post added successfully',
      addError: 'Failed to add blog post',
      editSuccess: 'Blog post updated successfully',
      editError: 'Failed to update blog post',
      deleteSuccess: 'Blog post deleted successfully',
      deleteError: 'Failed to delete blog post',
      deleteConfirm: 'Are you sure you want to delete this blog post?',
      publishSuccess: 'Blog post published successfully',
      publishError: 'Failed to publish blog post',
      unpublishSuccess: 'Blog post unpublished successfully',
      unpublishError: 'Failed to unpublish blog post'
    },
    validation: {
      titleRequired: 'Title is required',
      contentRequired: 'Content is required',
      categoryRequired: 'Category is required',
      statusRequired: 'Status is required',
      publishDateRequired: 'Publish date is required',
      metaTitleLength: 'Meta title must be less than 60 characters',
      metaDescriptionLength: 'Meta description must be less than 160 characters'
    }
  }
} 