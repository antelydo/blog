<!-- 文章编辑 -->
<template>
  <div class="article-edit-container">
    <el-card shadow="hover" class="article-edit-card">
      <template #header>
        <div class="card-header">
          <span>{{ isEdit ? t('article.edit.editTitle') : t('article.edit.createTitle') }}</span>
          <div class="header-actions">
            <el-button @click="goBack">{{ t('common.backToList') }}</el-button>
          </div>
        </div>
      </template>

      <el-form 
        ref="articleFormRef" 
        :model="articleForm" 
        :rules="rules" 
        label-width="100px" 
        label-position="right"
        v-loading="loading"
      >
        <el-form-item :label="t('article.form.title')" prop="title">
          <el-input v-model="articleForm.title" :placeholder="t('article.form.titlePlaceholder')" maxlength="100" show-word-limit />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item :label="t('article.form.category')" prop="category_id">
              <el-cascader
                v-model="articleForm.category_id"
                :options="categoryOptions"
                :props="{ 
                  checkStrictly: true,
                  label: 'name',
                  value: 'id',
                  emitPath: false,
                  expandTrigger: 'hover',
                  multiple: true
                }"
                :placeholder="t('article.form.categoryPlaceholder')" 
                filterable
                style="width: 100%"
                clearable
                :show-all-levels="true"
                tag-type="success"
                collapse-tags-tooltip
                class="category-cascader"
                :key="categorySelectorKey"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item :label="t('article.form.tags')" prop="tags">
              <el-select 
                v-model="articleForm.tags" 
                multiple 
                filterable 
                allow-create 
                default-first-option
                :placeholder="t('article.form.tagsPlaceholder')" 
                style="width: 100%"
                clearable
                value-key="id"
                class="tag-container"
              >
                <el-option 
                  v-for="item in tagOptions" 
                  :key="item.id" 
                  :label="item.name" 
                  :value="item.id" 
                >
                  <span>{{ item.name }}</span>
                </el-option>
                <template #selected-item="{ item }">
                  <span>{{ typeof item === 'object' ? item.name : getTagText(item) }}</span>
                </template>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item :label="t('article.form.description')" prop="description">
          <el-input 
            v-model="articleForm.description" 
            type="textarea" 
            :rows="2" 
            :placeholder="t('article.form.descriptionPlaceholder')" 
            maxlength="200" 
            show-word-limit
          />
        </el-form-item>
        
        <el-form-item :label="t('article.form.thumbnail')" prop="thumbnail">
          <el-upload
            class="cover-uploader"
            action="#"
            :http-request="handleCoverUpload"
            :show-file-list="false"
            :before-upload="beforeCoverUpload"
          >
            <img v-if="articleForm.thumbnail" :src="articleForm.thumbnail" class="cover-image" />
            <el-icon v-else class="cover-uploader-icon"><Plus /></el-icon>
          </el-upload>
          <div class="cover-tip">{{ t('article.form.thumbnailTip') }}</div>
        </el-form-item>
        
        <el-form-item :label="t('article.form.content')" prop="content">
          <!-- 富文本编辑器 -->
          <div class="editor-container">
            <div style="border: 1px solid #ccc">
              <Toolbar
                style="border-bottom: 1px solid #ccc"
                :editor="editorRef"
                :defaultConfig="toolbarConfig"
                :mode="mode"
              />
              <Editor
                style="height: 400px; overflow-y: hidden;"
                v-model="articleForm.content"
                :defaultConfig="editorConfig"
                :mode="mode"
                @onCreated="handleEditorCreated"
              />
            </div>
          </div>
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item :label="t('article.form.status')" prop="status">
              <el-select v-model="articleForm.status" :placeholder="t('article.form.statusPlaceholder')" style="width: 100%" clearable>
                <el-option :label="t('article.status.published')" value="published" />
                <el-option :label="t('article.status.draft')" value="draft" />
                <el-option :label="t('article.status.pending')" value="pending" />
                <el-option :label="t('article.status.rejected')" value="rejected" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item :label="t('article.form.isTop')" prop="is_top">
              <el-switch v-model="articleForm.is_top" :active-value="1" :inactive-value="0" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item :label="t('article.form.isRecommend')" prop="is_recommend">
              <el-switch v-model="articleForm.is_recommend" :active-value="1" :inactive-value="0" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item :label="t('article.form.author')" prop="author">
              <el-select
                v-model="articleForm.author"
                :placeholder="t('article.form.authorPlaceholder')"
                filterable
                style="width: 100%"
                clearable
                allow-create
                default-first-option
                remote
                :remote-method="searchAuthors"
                :loading="authorLoading"
                @change="handleAuthorChange"
              >
                <el-option-group :label="t('article.authorTypes.admin')">
                  <el-option
                    v-for="item in adminAuthors"
                    :key="item.id"
                    :label="item.username || item.value"
                    :value="item.value"
                    :data-type="'admin'"
                    :data-id="item.id.replace('admin_', '')"
                  />
                </el-option-group>
                <el-option-group :label="t('article.authorTypes.user')">
                  <el-option
                    v-for="item in userAuthors"
                    :key="item.id"
                    :label="item.username || item.value"
                    :value="item.value"
                    :data-type="'user'"
                    :data-id="item.id.replace('user_', '')"
                  />
                </el-option-group>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item :label="t('article.form.publishTime')" prop="publish_time">
              <el-date-picker
                v-model="articleForm.publish_time"
                type="datetime"
                format="YYYY-MM-DD HH:mm:ss"
                value-format="YYYY-MM-DD HH:mm:ss"
                :placeholder="t('article.form.publishTimePlaceholder')"
                style="width: 100%"
                :default-value="new Date()"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item>
          <el-button type="primary" @click="submitForm" :loading="submitting">{{ isEdit ? t('article.edit.saveChanges') : t('article.edit.create') }}</el-button>
          <el-button @click="resetForm">{{ t('common.reset') }}</el-button>
          <el-button @click="goBack">{{ t('common.cancel') }}</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onBeforeUnmount, watch, nextTick, shallowRef } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getArticleDetail, createArticle, updateArticle, getCategories, getTags, uploadImage } from '@/api/admin/article'
import { getAdmins, getUsers } from '@/api/admin/user'
// 引入 wangEditor
import '@wangeditor/editor/dist/css/style.css'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'

export default {
  name: 'ArticleEdit',
  components: {
    Plus,
    Editor,
    Toolbar
  },
  setup() {
    // Initialize i18n
    const { t } = useI18n()
    
    const router = useRouter()
    const route = useRoute()
    
    // 判断是编辑还是创建
    const isEdit = route.name === 'ArticleEdit'
    const articleId = isEdit ? route.params.id : null
    
    // 表单ref
    const articleFormRef = ref(null)
    
    // wangEditor 编辑器实例，必须用 shallowRef
    const editorRef = shallowRef()
    
    // 编辑器配置
    const toolbarConfig = {}
    const editorConfig = {
      placeholder: '请输入文章内容...',
      MENU_CONF: {
        uploadImage: {
          customUpload: async (file, insertFn) => {
            try {
              const formData = new FormData()
              formData.append('image', file)
              
              const res = await uploadImage(formData)
              
              if (res.code === 200 && res.data) {
                insertFn(res.data.url, file.name, res.data.url)
              } else {
                ElMessage.error(res.msg || '图片上传失败')
              }
            } catch (error) {
              console.error('图片上传失败:', error)
              ElMessage.error('图片上传失败，请稍后重试')
            }
          }
        }
      }
    }
    const mode = 'default' // 或 'simple'
    
    // 加载状态
    const loading = ref(false)
    const submitting = ref(false)
    
    // 简洁日期格式化函数
    const formatDate = (date) => {
      if (!date) return ''
      
      try {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        const hours = String(date.getHours()).padStart(2, '0')
        const minutes = String(date.getMinutes()).padStart(2, '0')
        const seconds = String(date.getSeconds()).padStart(2, '0')
        
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
      } catch (e) {
        console.error('日期格式化失败:', e)
        return ''
      }
    }
    
    // 文章表单数据
    const articleForm = reactive({
      id: '',
      title: '',
      description: '',
      content: '',
      category_id: [],  // 改为数组，支持多选
      tags: [],
      thumbnail: '',
      author: '',       // 保留author字段作为显示名称
      author_id: '',    // 添加author_id字段，存储作者ID
      author_type: '',  // 添加author_type字段，存储作者类型：'admin'或'user'
      status: 'draft',
      is_top: 0,
      is_recommend: 0,
      publish_time: formatDate(new Date())
    })
    
    // 日期时间格式化函数
    const formatDateTime = (dateTime) => {
      if (!dateTime) return formatDate(new Date())
      
      try {
        // 如果是日期对象，转为字符串
        if (dateTime instanceof Date) {
          return formatDate(dateTime)
        }
        
        // 处理字符串格式
        if (typeof dateTime === 'string') {
          // 统一替换日期格式中的字符
          const normalizedDate = dateTime.replace(/-/g, '/').replace('T', ' ').split('.')[0]
          const date = new Date(normalizedDate)
          
          if (!isNaN(date.getTime())) {
            return formatDate(date)
          }
        }
        
        // 其他情况，返回当前时间
        return formatDate(new Date())
      } catch (error) {
        console.error('日期格式化错误:', error)
        return formatDate(new Date())
      }
    }
    
    // 表单验证规则
    const rules = {
      title: [
        { required: true, message: '请输入文章标题', trigger: 'blur' },
        { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' }
      ],
      category_id: [
        { required: true, message: '请选择文章分类', trigger: 'change' }
      ],
      description: [
        { required: true, message: '请输入文章描述', trigger: 'blur' },
        { max: 200, message: '长度不能超过 200 个字符', trigger: 'blur' }
      ],
      author: [
        { required: true, message: '请输入作者', trigger: 'blur' }
      ],
      status: [
        { required: true, message: '请选择状态', trigger: 'change' }
      ]
    }
    
    // 选项数据
    const categoryOptions = ref([])
    const tagOptions = ref([])
    const authorOptions = ref([]) // 所有作者选项列表
    const adminAuthors = ref([]) // 管理员作者列表 
    const userAuthors = ref([])  // 用户作者列表
    const authorLoading = ref(false) // 作者加载状态
    const categorySelectorKey = ref(0) // Key for forcing cascader re-render
    
    // 搜索作者
    const searchAuthors = (query) => {
      if (query) {
        const q = query.toLowerCase()
        adminAuthors.value = authorOptions.value
          .filter(item => item.type === 'admin' && 
            (item.label.toLowerCase().includes(q) || 
             item.value.toLowerCase().includes(q)))
        
        userAuthors.value = authorOptions.value
          .filter(item => item.type === 'user' && 
            (item.label.toLowerCase().includes(q) || 
             item.value.toLowerCase().includes(q)))
      } else {
        // 没有搜索词时显示所有
        updateAuthorLists()
      }
    }
    
    // 更新作者列表分组
    const updateAuthorLists = () => {
      adminAuthors.value = authorOptions.value.filter(item => item.type === 'admin')
      userAuthors.value = authorOptions.value.filter(item => item.type === 'user')
    }
    
    // 加载作者列表 (管理员和用户)
    const fetchAuthors = async () => {
      authorLoading.value = true
      try {
        // 并行获取管理员和用户列表
        const [adminRes, userRes] = await Promise.all([
          getAdmins({ limit: 1000 }), // 增加limit以获取更多管理员
          getUsers({ limit: 5000 })   // 增加limit以获取所有用户
        ])
        
        const authors = []
        
        // 处理管理员列表
        if (adminRes.code === 200) {
          let adminList = []
          
          if (adminRes.data && adminRes.data.data) {
            adminList = Array.isArray(adminRes.data.data) 
              ? adminRes.data.data 
              : adminRes.data.data.list || []
          } else if (adminRes.data) {
            adminList = Array.isArray(adminRes.data) 
              ? adminRes.data 
              : adminRes.data.list || []
          }
          
          
          adminList.forEach(admin => {
            if (admin.username) {
              authors.push({
                id: `admin_${admin.id}`,
                name: admin.nickname || admin.username,
                label: `${admin.nickname || admin.username} (${t('common.admin')})`,
                value: admin.username,
                type: 'admin',
                username: admin.username  // 确保包含原始username字段
              })
            }
          })
        }
        
        // 处理用户列表
        if (userRes.code === 200) {
          let userList = []
          
          if (userRes.data && userRes.data.data) {
            userList = Array.isArray(userRes.data.data) 
              ? userRes.data.data 
              : userRes.data.data.list || []
          } else if (userRes.data) {
            userList = Array.isArray(userRes.data) 
              ? userRes.data 
              : userRes.data.list || []
          }
          
          
          userList.forEach(user => {
            if (user.username) {
              authors.push({
                id: `user_${user.id}`,
                name: user.nickname || user.username,
                label: `${user.nickname || user.username} (${t('common.user')})`,
                value: user.username,
                type: 'user',
                username: user.username  // 确保包含原始username字段
              })
            }
          })
        }
        
        // 更新作者选项列表
        authorOptions.value = authors
        
        // 更新分组列表
        updateAuthorLists()
        
      } catch (error) {
        console.error(`${t('article.errors.fetchAuthors')}:`, error)
      } finally {
        authorLoading.value = false
      }
    }
    
    // 获取文章详情
    const fetchArticleDetail = async () => {
      if (!isEdit) return
      
      loading.value = true
      try {
        // 先重置分类ID
        articleForm.category_id = []
        
        const res = await getArticleDetail({ id: articleId })
        
        if (res.code === 200 && res.data) {
          const article = res.data
          
          // 处理分类ID逻辑
          let categoryIds = [];
          
          // 1. 检查category_id字段
          if (article.category_id) {
            // 检查是否已经是数组格式
            if (Array.isArray(article.category_id)) {
              categoryIds = [...article.category_id.map(id => 
                typeof id === 'string' ? parseInt(id, 10) : id)];
            } else {
              const id = typeof article.category_id === 'string' ? 
                parseInt(article.category_id, 10) : article.category_id;
              categoryIds.push(id);
            }
          }
          
          // 2. 检查category对象
          if (article.category && typeof article.category === 'object') {
            if (Array.isArray(article.category)) {
              // 处理分类对象数组
              article.category.forEach(cat => {
                if (cat && cat.id) {
                  const id = typeof cat.id === 'string' ? parseInt(cat.id, 10) : cat.id;
                  if (!categoryIds.includes(id)) {
                    categoryIds.push(id);
                  }
                }
              });
            } else if (article.category.id) {
              // 处理单个分类对象
              const id = typeof article.category.id === 'string' ? 
                parseInt(article.category.id, 10) : article.category.id;
              if (!categoryIds.includes(id)) {
                categoryIds.push(id);
              }
            }
          }
          
          // 3. 检查categories数组
          if (article.categories && Array.isArray(article.categories)) {
            article.categories.forEach(cat => {
              let id;
              if (typeof cat === 'object' && cat.id) {
                id = typeof cat.id === 'string' ? parseInt(cat.id, 10) : cat.id;
              } else if (typeof cat === 'number' || typeof cat === 'string') {
                id = typeof cat === 'string' ? parseInt(cat, 10) : cat;
              }
              
              if (id && !categoryIds.includes(id)) {
                categoryIds.push(id);
              }
            });
          }
          
          
          // 先更新其他所有字段
          for (const key in articleForm) {
            if (key in article && key !== 'category_id' && key !== 'tags' && key !== 'publish_time' && key !== 'content') {
              articleForm[key] = article[key]
            }
          }
          
          // 直接设置作者字段，确保正确回显
          if (article.author) {
            articleForm.author = article.author;
          }

          // 设置作者ID和类型
          if (article.author_type && article.author_id) {
            // 如果API直接返回了作者类型和ID，直接使用
            articleForm.author_type = article.author_type;
            articleForm.author_id = article.author_id;
            
            // 确保下拉列表选中正确的作者
            if (article.author_type === 'admin') {
              // 尝试在管理员列表中匹配作者ID
              const matchedAdmin = adminAuthors.value.find(admin => 
                admin.id === `admin_${article.author_id}` || 
                admin.id.replace('admin_', '') === article.author_id.toString()
              );
              
              if (matchedAdmin && matchedAdmin.value !== articleForm.author) {
                articleForm.author = matchedAdmin.value;
              }
            } else if (article.author_type === 'user') {
              // 尝试在用户列表中匹配作者ID
              const matchedUser = userAuthors.value.find(user => 
                user.id === `user_${article.author_id}` || 
                user.id.replace('user_', '') === article.author_id.toString()
              );
              
              if (matchedUser && matchedUser.value !== articleForm.author) {
                articleForm.author = matchedUser.value;
              }
            }
          } else {
            // 如果API没有返回作者类型和ID，尝试根据作者名称自动匹配
            if (articleForm.author) {
              // 尝试在管理员列表中查找匹配的作者
              const matchedAdmin = adminAuthors.value.find(admin => admin.value === articleForm.author);
              if (matchedAdmin) {
                articleForm.author_type = 'admin';
                articleForm.author_id = matchedAdmin.id.replace('admin_', '');
              } else {
                // 尝试在用户列表中查找匹配的作者
                const matchedUser = userAuthors.value.find(user => user.value === articleForm.author);
                if (matchedUser) {
                  articleForm.author_type = 'user';
                  articleForm.author_id = matchedUser.id.replace('user_', '');
                } else {
                  // 如果都找不到匹配的，默认设为用户类型
                  articleForm.author_type = 'user';
                  articleForm.author_id = '';
                }
              }
            }
          }

          // 处理标签数据
          if (article.tags && Array.isArray(article.tags)) {
            if (article.tags.length > 0) {
              
              if (article.tags[0] && typeof article.tags[0] === 'object' && article.tags[0].id) {
                // 如果标签是对象，提取ID
                articleForm.tags = article.tags.map(tag => {
                  const id = parseInt(tag.id, 10) || tag.id
                  return id
                })
              } else {
                // 如果标签是ID，直接使用
                articleForm.tags = article.tags.map(tag => {
                  const id = typeof tag === 'string' ? parseInt(tag, 10) : tag
                  return id
                })
              }
              
            }
          }
          
          // 处理日期时间
          if (article.publish_time) {
            articleForm.publish_time = formatDateTime(article.publish_time)
          }
          
          // 处理文章内容
          if (article.content) {
            articleForm.content = article.content
          }
          
          // 设置分类ID
          if (categoryIds.length > 0) {
            // 确保分类选项已加载
            if (categoryOptions.value.length > 0) {
              // 验证所有分类ID是否存在于选项中
              const validCategoryIds = categoryIds.map(id => 
                // Ensure all IDs being set are numbers
                typeof id === 'string' ? parseInt(id, 10) : id
              ).filter(id => {
                if (isNaN(id)) {
                  return false;
                }
                const exists = findCategoryById(categoryOptions.value, id);
                if (!exists) {
                }
                return exists;
              });
              
              if (validCategoryIds.length > 0) {
                
                // Use nextTick to ensure assignment happens after DOM updates
                nextTick(() => {
                  // Increment key to force re-render *before* setting the value
                  categorySelectorKey.value++; 
                  
                  articleForm.category_id = validCategoryIds;
                  
                  // Check value in another tick to see final state after re-render
                  nextTick(() => {
                  });
                });
              } else {
              }
            } else {
              // This case should be less likely now due to onMounted changes
            }
          } else {
          }
          
          // 确保状态有效
          if (!['published', 'draft', 'pending', 'rejected'].includes(articleForm.status)) {
            // 如果状态是数字，转换为对应的字符串
            if (typeof article.status === 'number') {
              const statusMap = {
                0: 'draft',     // 草稿
                1: 'published', // 发布
                2: 'pending',   // 待审核
                '-1': 'rejected' // 禁用/拒绝
              };
              
              articleForm.status = statusMap[article.status] || 'draft';
            } else {
              // 默认设为草稿
              articleForm.status = 'draft';
            }
          }

        } else {
          ElMessage.error(res.msg || '获取文章详情失败')
          goBack()
        }
      } catch (error) {
        console.error('获取文章详情失败:', error)
        ElMessage.error('获取文章详情失败')
        goBack()
      } finally {
        loading.value = false
      }
    }
    
    // 递归查找分类是否存在
    const findCategoryById = (categories, id) => {
      if (!categories || !Array.isArray(categories)) {
        return false;
      }
      
      
      for (const category of categories) {
        if (category.id === id) {
          return true;
        }
        
        // 递归检查子分类
        if (category.children && category.children.length) {
          if (findCategoryById(category.children, id)) {
            return true;
          }
        }
      }
      
      return false;
    }
    
    // 获取分类列表
    const fetchCategories = async () => {
      try {
        const res = await getCategories();

        if (res.code === 200) {
          let rawCategories = [];
          // 直接使用后端返回的树状结构
          if (Array.isArray(res.data)) {
            rawCategories = res.data;
          } else if (res.data && Array.isArray(res.data.list)) {
            // 处理可能的分页返回格式
            rawCategories = res.data.list;
          }

          // 递归函数，确保所有ID都是数字
          const ensureNumericIds = (categories) => {
            if (!categories || !Array.isArray(categories)) return [];
            return categories.map(cat => {
              const numericId = typeof cat.id === 'string' ? parseInt(cat.id, 10) : cat.id;
              if (isNaN(numericId)) {
                console.warn(`分类 "${cat.name || '未命名'}" 有无效的ID: ${cat.id}`);
              }
              const children = cat.children ? ensureNumericIds(cat.children) : [];
              return {
                ...cat,
                id: numericId, // Ensure ID is a number
                ...(children.length > 0 && { children })
              };
            }).filter(cat => !isNaN(cat.id)); // Filter out categories with NaN IDs
          };

          categoryOptions.value = ensureNumericIds(rawCategories);

          // 打印分类树结构，辅助调试
          const printCategoryTree = (categories, level = 0) => {
            if (!categories || !Array.isArray(categories)) return;

            categories.forEach(cat => {
              const prefix = '  '.repeat(level);

              if (cat.children && Array.isArray(cat.children) && cat.children.length > 0) {
                printCategoryTree(cat.children, level + 1);
              }
            });
          };

          printCategoryTree(categoryOptions.value);
        }
      } catch (error) {
        console.error('获取分类列表失败:', error);
        categoryOptions.value = []; // 确保在失败时也初始化为空数组
      }
    };
    
    // 获取标签列表
    const fetchTags = async () => {
      try {
        const res = await getTags({ limit: 1000 ,page: 1}) // 请求更多标签，确保加载全部
        
        if (res.code === 200) {
          let tags = []
          
          // 处理不同格式的响应数据
          if (Array.isArray(res.data)) {
            tags = res.data
          } else if (res.data && Array.isArray(res.data.list)) {
            tags = res.data.list
          } else if (res.data && typeof res.data === 'object') {
            // 如果是其他对象格式，尝试使用其数组属性
            const possibleLists = ['items', 'rows', 'records', 'data']
            for (const key of possibleLists) {
              if (Array.isArray(res.data[key])) {
                tags = res.data[key]
                break
              }
            }
          }
          
          if (tags.length === 0) {
            // 尝试递归查找数组
            const findArrayInObject = (obj, depth = 0) => {
              if (depth > 3) return null // 限制递归深度
              if (!obj || typeof obj !== 'object') return null
              
              for (const key in obj) {
                if (Array.isArray(obj[key]) && obj[key].length > 0) {
                  return obj[key]
                } else if (typeof obj[key] === 'object') {
                  const result = findArrayInObject(obj[key], depth + 1)
                  if (result) return result
                }
              }
              return null
            }
            
            const foundTags = findArrayInObject(res.data)
            if (foundTags) {
              tags = foundTags
            }
          }
          
          // 确保标签有id和name属性
          tagOptions.value = tags.map(tag => {
            const id = parseInt(tag.id, 10) || 0
            // 创建标签对象，确保有必要的属性
            return {
              id,
              name: tag.name || `标签${id}`,
              label: tag.name || `标签${id}`, // 添加label属性用于显示
              value: id, // 添加value属性用于绑定
              ...tag
            }
          })
          
        }
      } catch (error) {
        console.error('获取标签列表失败:', error)
        tagOptions.value = [] // 确保在失败时也初始化为空数组
      }
    }
    
    // 显示标签的文本内容
    const getTagText = (tagId) => {
      const tag = tagOptions.value.find(t => t.id === tagId)
      return tag ? tag.name : `标签${tagId}`
    }
    
    // 监听标签选项加载完成，处理标签回显问题
    watch(tagOptions, (newTags) => {
      if (isEdit && articleForm.id && newTags.length > 0 && articleForm.tags.length > 0) {
        // 从保存的ID中找到完整的标签对象，用于回显
        const tagObjects = articleForm.tags.map(tagId => {
          // 确保ID是数字类型
          const numId = typeof tagId === 'string' ? parseInt(tagId, 10) : tagId
          return numId
        })
        
        // 设置为处理后的ID数组
        articleForm.tags = tagObjects
      }
    })
    
    // 监听分类选项变化 - 简化
    watch(categoryOptions, (newOptions) => {
      // This watcher might still be useful if categories can be dynamically loaded/changed
      // after the initial mount, but we remove the complex fetch/set logic triggered on mount.
      // If needed, logic to re-validate/update articleForm.category_id based on new options 
      // *without* re-fetching article details could be added here.
    }, { immediate: true })
    
    // 处理作者选择变化
    const handleAuthorChange = (value) => {
      
      // 如果没有选择作者或者是手动输入的新作者，重置作者ID和类型
      if (!value) {
        articleForm.author_id = '';
        articleForm.author_type = '';
        return;
      }
      
      // 检查选择的是否是预定义的作者选项
      const adminAuthor = adminAuthors.value.find(author => author.value === value);
      if (adminAuthor) {
        // 设置为管理员作者
        articleForm.author_type = 'admin';
        articleForm.author_id = adminAuthor.id.replace('admin_', '');
        return;
      }
      
      const userAuthor = userAuthors.value.find(author => author.value === value);
      if (userAuthor) {
        // 设置为普通用户作者
        articleForm.author_type = 'user';
        articleForm.author_id = userAuthor.id.replace('user_', '');
        return;
      }
      
      // 如果是手动输入的新作者，默认设为用户类型
      articleForm.author_type = 'user';
      articleForm.author_id = '';
    }
    
    // 提交表单
    const submitForm = async () => {
      if (!articleFormRef.value) return
      
      // 验证内容
      if (!articleForm.content || articleForm.content.trim() === '<p><br></p>') {
        ElMessage.error('请输入文章内容')
        return
      }
      
      // 验证表单
      await articleFormRef.value.validate(async (valid) => {
        if (!valid) return
        
        submitting.value = true
        
        try {
          const params = { ...articleForm }
          
          // 处理标签数据
          if (Array.isArray(params.tags)) {
            params.tags = params.tags.map(tag => {
              // 如果是对象，保留id; 如果是数字，直接使用; 如果是字符串，作为新标签
              if (typeof tag === 'object') return tag.id
              return tag
            })
          }
          
          // 处理分类数据，确保category_id正确传递
          if (Array.isArray(params.category_id)) {
            // 保留数组格式，不做转换
            
            // 同时添加categories字段以支持可能的多分类API
            params.categories = [...params.category_id];
          }
          
          // 转换状态值为数字
          if (params.status) {
            // 状态转换：0草稿，1发布，2待审核，-1禁用
            const statusMap = {
              'draft': 0,
              'published': 1,
              'pending': 2,
              'rejected': -1
            };
            
            params.status = statusMap[params.status] !== undefined ? statusMap[params.status] : 0;
          }
          
          // 确保作者信息正确传递
          if (params.author && !params.author_type) {
            // 如果有作者名称但没有类型，默认设为user
            params.author_type = 'user';
          }
          
          // 如果没有作者ID但有类型和名称，可以保留空ID，由后端处理
          
          
          // 根据是否是编辑模式选择接口
          const apiMethod = isEdit ? updateArticle : createArticle
          const res = await apiMethod(params)
          
          if (res.code === 200) {
            ElMessage.success(isEdit ? '更新文章成功' : '创建文章成功')
            goBack()
          } else {
            ElMessage.error(res.msg || (isEdit ? '更新文章失败' : '创建文章失败'))
          }
        } catch (error) {
          console.error(isEdit ? '更新文章失败:' : '创建文章失败:', error)
          ElMessage.error(isEdit ? '更新文章失败' : '创建文章失败')
        } finally {
          submitting.value = false
        }
      })
    }
    
    // 重置表单
    const resetForm = () => {
      if (!articleFormRef.value) return
      
      if (isEdit) {
        // 编辑模式下重新获取文章数据
        fetchArticleDetail()
      } else {
        // 创建模式下重置表单
        articleFormRef.value.resetFields()
        
        // 重置编辑器内容
        articleForm.content = ''
      }
    }
    
    // 返回列表页
    const goBack = () => {
      router.push('/admin/article/list')
    }
    
    // 封面图上传前的校验
    const beforeCoverUpload = (file) => {
      const isImage = file.type === 'image/jpeg' || file.type === 'image/png'
      const isLt2M = file.size / 1024 / 1024 < 2
      
      if (!isImage) {
        ElMessage.error('封面图片只能是JPG或PNG格式')
        return false
      }
      
      if (!isLt2M) {
        ElMessage.error('封面图片大小不能超过2MB')
        return false
      }
      
      return true
    }
    
    // 处理封面图上传
    const handleCoverUpload = async (options) => {
      const { file } = options
      
      try {
        const formData = new FormData()
        formData.append('image', file)
        
        const res = await uploadImage(formData)
        
        if (res.code === 200 && res.data) {
          articleForm.thumbnail = res.data.url
          ElMessage.success('封面上传成功')
        } else {
          ElMessage.error(res.msg || '封面上传失败')
        }
      } catch (error) {
        console.error('封面上传失败:', error)
        ElMessage.error('封面上传失败，请稍后重试')
      }
    }
    
    // wangEditor 相关方法
    const handleEditorCreated = (editor) => {
      editorRef.value = editor // 记录 editor 实例
    }
    
    // 组件挂载时初始化
    onMounted(async () => {
      // 确保先获取分类、标签和作者列表
      loading.value = true; // Show loading indicator
      try {
        await Promise.all([fetchCategories(), fetchTags(), fetchAuthors()]);
        
        // 只有在分类等选项加载完成后，才获取文章详情（如果是编辑模式）
        if (isEdit) {
          await fetchArticleDetail();
        } else {
          // 创建模式：设置默认作者
          const adminInfo = localStorage.getItem('admin_info');
          if (adminInfo) {
            try {
              const parsedInfo = JSON.parse(adminInfo);
              if (parsedInfo && parsedInfo.username) {
                // 设置作者名称
                articleForm.author = parsedInfo.username;
                
                // 设置作者类型为管理员，并尝试获取管理员ID
                articleForm.author_type = 'admin';
                
                // 如果有ID，直接使用
                if (parsedInfo.id) {
                  articleForm.author_id = parsedInfo.id;
                } else {
                  // 如果没有ID但有username，尝试从adminAuthors中匹配
                  const matchedAdmin = adminAuthors.value.find(admin => admin.value === parsedInfo.username);
                  if (matchedAdmin) {
                    articleForm.author_id = matchedAdmin.id.replace('admin_', '');
                  }
                }
              }
            } catch (e) {
              console.error('解析管理员信息失败:', e);
            }
          }
        }
      } catch (error) {
        console.error('初始化加载失败:', error);
        ElMessage.error('页面初始化失败，请稍后重试');
      } finally {
        loading.value = false; // Hide loading indicator
      }
    });
    
    // 组件销毁前清理
    onBeforeUnmount(() => {
      const editor = editorRef.value
      if (editor == null) return
      
      editor.destroy()
    })
    
    return {
      t,
      isEdit,
      articleFormRef,
      editorRef,
      loading,
      submitting,
      articleForm,
      rules,
      categoryOptions,
      tagOptions,
      adminAuthors,
      userAuthors,
      authorLoading,
      searchAuthors,
      toolbarConfig,
      editorConfig,
      mode,
      submitForm,
      resetForm,
      goBack,
      beforeCoverUpload,
      handleCoverUpload,
      handleEditorCreated,
      getTagText,
      categorySelectorKey,
      handleAuthorChange
    }
  }
}
</script>

<style scoped>
.article-edit-container {
  padding: 20px;
}

.article-edit-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.editor-container {
  border-radius: 4px;
  margin-bottom: 10px;
}

.cover-uploader {
  width: 300px;
  height: 150px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}

.cover-uploader:hover {
  border-color: #409eff;
}

.cover-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 30px;
  height: 30px;
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

:deep(.el-card__header) {
  padding: 15px 20px;
}

:deep(.el-card__body) {
  padding: 20px;
}

/* 移动端响应式 */
@media screen and (max-width: 768px) {
  .article-edit-container {
    padding: 10px;
  }
  
  :deep(.el-form-item) {
    margin-bottom: 18px;
  }
  
  :deep(.el-col) {
    margin-bottom: 0;
  }
}

.category-cascader :deep(.el-cascader__tags) {
  flex-wrap: wrap;
  margin-right: 30px;
}

.tag-container :deep(.el-select__tags) {
  flex-wrap: wrap;
  margin-right: 30px;
}
</style> 