export default {
  adminCat: {
    // 分类管理
    title: '分类管理',
    add: '添加分类',
    edit: '编辑分类',
    delete: '删除',
    
    // 筛选相关
    filters: {
      title: '筛选条件',
      name: '名称',
      slug: '别名',
      status: '状态',
      search: '搜索',
      reset: '重置',
      keyword: '搜索分类名称',
      statusFilter: '状态筛选',
      all: '全部',
      active: '启用',
      inactive: '禁用'
    },
    
    // 表格相关
    table: {
      id: 'ID',
      name: '名称',
      slug: '别名',
      description: '描述',
      icon: '图标',
      path: '路径',
      level: '层级',
      sort: '排序',
      status: '状态',
      createdAt: '创建时间',
      updatedAt: '更新时间',
      operation: '操作',
      createTime: '创建时间',
      updateTime: '更新时间'
    },
    
    // 状态相关
    status: {
      active: '启用',
      inactive: '禁用',
      enabled: '启用',
      disabled: '禁用'
    },
    
    // 对话框相关
    dialog: {
      addCategory: '添加分类',
      editCategory: '编辑分类',
      deleteCategory: '删除分类',
      confirmDelete: '确定要删除该分类吗？',
      confirmDeleteWithChildren: '确定要删除分类 "{name}" 吗？{children}'
    },
    
    confirmDeleteChildren: '此操作将连带删除所有子分类！',
    basicInfo: '基本信息',
    settings: '设置',
    parentCategory: '父级分类',
    
    // 占位符
    placeholder: {
      name: '请输入分类名称',
      slug: '请输入分类别名',
      description: '请输入分类描述',
      icon: '请选择图标',
      status: '请选择状态',
      parentCategory: '请选择父级分类，不选则为顶级分类',
      sort: '请输入排序值'
    },
    
    // 验证相关
    validation: {
      name: '分类名称不能为空',
      nameLength: '长度在 2 到 50 个字符',
      slug: '分类别名不能为空',
      slugFormat: '别名只能包含字母、数字和连字符',
      slugUnique: '该别名已被使用',
      status: '状态不能为空',
      description: '描述最多 200 个字符',
      sort: '排序值不能为空'
    },
    
    // 按钮相关
    button: {
      add: '添加',
      edit: '编辑',
      delete: '删除',
      save: '保存',
      cancel: '取消',
      confirm: '确定',
      search: '搜索',
      reset: '重置'
    },
    
    // 消息相关
    message: {
      addSuccess: '添加分类成功',
      addError: '添加分类失败',
      editSuccess: '更新分类成功',
      editError: '更新分类失败',
      deleteSuccess: '删除分类成功',
      deleteError: '删除分类失败',
      statusEnabled: '已启用分类',
      statusDisabled: '已禁用分类',
      statusUpdateError: '更新分类状态失败',
      fetchError: '获取分类列表失败',
      networkError: '获取分类列表失败，请检查网络连接'
    },
    
    warning: '警告',
    refreshList: '刷新列表',
    exportCsv: '导出CSV',
    batchDelete: '批量删除',
    selectedCount: '已选择 {count} 项',
    noData: '暂无数据',
    loading: '加载中...'
  }
};
