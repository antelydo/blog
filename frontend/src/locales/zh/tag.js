export default {
  tag: {
    title: '标签管理',
    add: '添加标签',
    addDialog: '添加新标签',
    editDialog: '编辑标签',
    name: '标签名称',
    namePlaceholder: '请输入标签名称',
    description: '描述',
    descriptionPlaceholder: '请输入标签描述',
    sort: '排序',
    basicInfo: '基本信息',
    settings: '设置',
    enabled: '启用',
    disabled: '禁用',
    searchPlaceholder: '搜索标签名称',
    createTime: '创建时间',
    updateTime: '更新时间',
    addSuccess: '标签添加成功',
    editSuccess: '标签更新成功',
    deleteSuccess: '标签删除成功',
    deleteConfirm: '确定要删除该标签吗？',
    statusUpdateSuccess: '状态更新成功',
    nameRequired: '标签名称不能为空',
    nameExists: '标签名称已存在',
    createSuccess: '标签创建成功',
    updateSuccess: '标签更新成功',
    deleteFailed: '删除标签失败',
    createFailed: '创建标签失败',
    updateFailed: '更新标签失败',
    statusUpdateFailed: '更新标签状态失败',
    enableSuccess: '标签启用成功',
    disableSuccess: '标签禁用成功',
    filters: {
      title: '筛选条件',
      name: '名称',
      slug: '别名',
      status: '状态',
      search: '搜索',
      reset: '重置'
    },
    table: {
      id: 'ID',
      name: '名称',
      slug: '别名',
      description: '描述',
      color: '颜色',
      status: '状态',
      createdAt: '创建时间',
      updatedAt: '更新时间',
      operation: '操作'
    },
    status: {
      active: '启用',
      inactive: '禁用'
    },
    dialog: {
      addTag: '添加标签',
      editTag: '编辑标签',
      deleteTag: '删除标签',
      confirmDelete: '确定要删除该标签吗？'
    },
    placeholder: {
      name: '请输入标签名称',
      slug: '请输入标签别名',
      description: '请输入标签描述',
      color: '请选择颜色',
      status: '请选择状态'
    },
    validation: {
      nameRequired: '标签名称不能为空',
      nameLength: '标签名称长度必须在1到30个字符之间',
      descriptionLength: '描述不能超过100个字符',
      sortRequired: '排序不能为空'
    },
    button: {
      add: '添加',
      edit: '编辑',
      delete: '删除',
      save: '保存',
      cancel: '取消',
      confirm: '确定'
    },
    message: {
      addSuccess: '添加标签成功',
      addError: '添加标签失败',
      editSuccess: '更新标签成功',
      editError: '更新标签失败',
      deleteSuccess: '删除标签成功',
      deleteError: '删除标签失败'
    },
    errors: {
      fetchFailed: '获取标签列表失败',
      networkError: '网络错误，请重试',
      unexpectedFormat: '数据格式异常'
    }
  },
  article: {
    table: {
      id: 'ID',
      status: '状态',
      category: '分类',
      tags: '标签',
      author: '作者',
      createdAt: '创建时间',
      actions: '操作'
    },
    actions: {
      edit: '编辑',
      delete: '删除'
    }
  }
} 