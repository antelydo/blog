export default {
  blog: {
    title: '博客管理',
    filters: {
      title: '筛选条件',
      title: '标题',
      category: '分类',
      status: '状态',
      author: '作者',
      dateRange: '日期范围',
      clear: '清除筛选',
      apply: '应用筛选'
    },
    table: {
      title: '标题',
      category: '分类',
      author: '作者',
      status: '状态',
      publishDate: '发布日期',
      views: '浏览量',
      comments: '评论数',
      actions: '操作',
      noRecords: '暂无博客文章'
    },
    status: {
      draft: '草稿',
      published: '已发布',
      archived: '已归档'
    },
    actions: {
      add: '添加文章',
      edit: '编辑',
      delete: '删除',
      preview: '预览',
      publish: '发布',
      unpublish: '取消发布'
    },
    form: {
      addTitle: '添加新文章',
      editTitle: '编辑文章',
      title: '标题',
      content: '内容',
      category: '分类',
      tags: '标签',
      status: '状态',
      publishDate: '发布日期',
      featuredImage: '特色图片',
      metaTitle: 'Meta标题',
      metaDescription: 'Meta描述',
      submit: '提交',
      cancel: '取消',
      saveDraft: '保存草稿'
    },
    messages: {
      addSuccess: '博客文章添加成功',
      addError: '博客文章添加失败',
      editSuccess: '博客文章更新成功',
      editError: '博客文章更新失败',
      deleteSuccess: '博客文章删除成功',
      deleteError: '博客文章删除失败',
      deleteConfirm: '确定要删除此博客文章吗？',
      publishSuccess: '博客文章发布成功',
      publishError: '博客文章发布失败',
      unpublishSuccess: '博客文章取消发布成功',
      unpublishError: '博客文章取消发布失败'
    },
    validation: {
      titleRequired: '请输入标题',
      contentRequired: '请输入内容',
      categoryRequired: '请选择分类',
      statusRequired: '请选择状态',
      publishDateRequired: '请选择发布日期',
      metaTitleLength: 'Meta标题不能超过60个字符',
      metaDescriptionLength: 'Meta描述不能超过160个字符'
    }
  }
} 