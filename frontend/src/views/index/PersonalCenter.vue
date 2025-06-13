<!-- 个人中心页面 -->
<template>
  <div class="personal-center-container">
    <Header />
    <main class="main-content">
      <div class="container">
        <!-- 面包屑导航 -->
        <div class="breadcrumb-container">
          <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/' }">{{ t('personalCenter.breadcrumb.home') }}</el-breadcrumb-item>
            <el-breadcrumb-item>{{ t('personalCenter.breadcrumb.personalCenter') }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>

        <el-row :gutter="20" class="profile-row">
          <!-- 左侧个人信息卡片 -->
          <el-col :xs="24" :sm="8" :md="7" :lg="6" class="profile-col">
            <el-card class="profile-info-card" shadow="hover">
              <div class="avatar-container">
                <div class="avatar-wrapper">
                  <el-avatar
                    :size="120"
                    :src="userInfo.avatar || defaultAvatar"
                    class="avatar"
                  />
                  <div class="avatar-hover-mask">
                    <el-upload
                      class="avatar-uploader"
                      :show-file-list="false"
                      :before-upload="beforeAvatarUpload"
                      :http-request="uploadAvatar"
                      accept="image/jpeg,image/png,image/gif"
                    >
                      <div>
                        <el-button type="primary" circle>
                          <el-icon><Upload /></el-icon>
                        </el-button>
                      </div>
                    </el-upload>
                  </div>
                </div>
                <h3 class="username">{{ userInfo.nickname || userInfo.username }}</h3>
                <p class="user-id">{{ t('personalCenter.userInfo.id') }}: {{ userInfo.id }}</p>
              </div>

              <div class="info-section">
                <div class="section-header">
                  <el-icon><User /></el-icon>
                  <span>{{ t('personalCenter.userInfo.basicInfo') }}</span>
                </div>
                <div class="info-list">
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.username') }}：</label>
                    <span>{{ userInfo.username || t('personalCenter.userInfo.notSet') }}</span>
                  </div>
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.email') }}：</label>
                    <span>{{ userInfo.email || t('personalCenter.userInfo.notSet') }}</span>
                  </div>
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.mobile') }}：</label>
                    <span>{{ formatMobile(userInfo.mobile) }}</span>
                  </div>
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.gender') }}：</label>
                    <span>{{ formatGender(userInfo.gender) }}</span>
                  </div>
                </div>
              </div>

              <!-- 新增资产信息区块 -->
              <div class="info-section">
                <div class="section-header">
                  <el-icon><Money /></el-icon>
                  <span>我的资产</span>
                </div>
                <div class="info-list">
                  <div class="info-item">
                    <label>账户余额：</label>
                    <span class="balance-value">{{ formatBalance(userInfo.balance) }}</span>
                  </div>
                  <div class="info-item">
                    <label>积分：</label>
                    <span class="points-value">{{ userInfo.points || 0 }}</span>
                  </div>
                </div>
              </div>

              <div class="info-section">
                <div class="section-header">
                  <el-icon><Calendar /></el-icon>
                  <span>{{ t('personalCenter.userInfo.accountInfo') }}</span>
                </div>
                <div class="info-list">
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.registerTime') }}：</label>
                    <span>{{ formatDate(userInfo.register_time || userInfo.create_time) }}</span>
                  </div>
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.lastLogin') }}：</label>
                    <span>{{ formatDate(userInfo.last_login_time) }}</span>
                  </div>
                  <div class="info-item">
                    <label>{{ t('personalCenter.userInfo.accountStatus') }}：</label>
                    <el-tag :type="userInfo.status === 1 ? 'success' : 'danger'" size="small">
                      {{ userInfo.status === 1 ? t('personalCenter.userInfo.normal') : t('personalCenter.userInfo.disabled') }}
                    </el-tag>
                  </div>
                </div>
              </div>

              <div class="security-score-section">
                <div class="section-header">
                  <el-icon><Lock /></el-icon>
                  <span>安全评分</span>
                </div>
                <div class="security-score">
                  <el-progress
                    :percentage="calculateSecurityScore()"
                    :color="getScoreColor()"
                    :stroke-width="14"
                  />
                  <div class="security-tips">
                    {{ getSecurityTips() }}
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>

          <!-- 右侧编辑表单 -->
          <el-col :xs="24" :sm="16" :md="17" :lg="18" class="profile-col">
            <el-card class="profile-form-card" shadow="hover">
              <template #header>
                <div class="card-header">
                  <el-tabs v-model="activeTab" class="profile-tabs" type="border-card">
                    <el-tab-pane name="basic">
                      <template #label>
                        <div class="custom-tab-label">
                          <el-icon><InfoFilled /></el-icon>
                          <span>{{ t('personalCenter.tabs.basicInfo') }}</span>
                        </div>
                      </template>
                    </el-tab-pane>
                    <el-tab-pane name="security">
                      <template #label>
                        <div class="custom-tab-label">
                          <el-icon><Lock /></el-icon>
                          <span>{{ t('personalCenter.tabs.accountSecurity') }}</span>
                        </div>
                      </template>
                    </el-tab-pane>
                  </el-tabs>
                </div>
              </template>

              <!-- 基本信息表单 -->
              <div v-show="activeTab === 'basic'">
                <el-form
                  ref="profileFormRef"
                  :model="profileForm"
                  :rules="profileRules"
                  label-width="100px"
                  class="profile-form"
                  :status-icon="true"
                >
                  <div class="form-section">
                    <h3 class="form-section-title">
                      <el-icon><User /></el-icon>
                      <span>{{ t('personalCenter.basicForm.basicInfo') }}</span>
                      <el-tooltip :content="t('personalCenter.basicForm.bioTip')" placement="top">
                        <div>
                          <el-icon class="info-icon"><InfoFilled /></el-icon>
                        </div>
                      </el-tooltip>
                    </h3>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="用户名" prop="username">
                          <el-input
                            v-model="profileForm.username"
                            placeholder="请输入用户名"
                            :prefix-icon="User"
                            maxlength="20"
                            show-word-limit
                            @input="checkUsernameAvailable"
                          >
                            <template #append>
                              <el-tooltip :content="usernameStatus.message" placement="top">
                                <div>
                                  <el-icon v-if="usernameStatus.checking">
                                    <Loading class="is-loading" />
                                  </el-icon>
                                  <el-icon v-else-if="usernameStatus.available === true" style="color: #67c23a">
                                    <CircleCheck />
                                  </el-icon>
                                  <el-icon v-else-if="usernameStatus.available === false" style="color: #f56c6c">
                                    <CircleClose />
                                  </el-icon>
                                </div>
                              </el-tooltip>
                            </template>
                          </el-input>
                          <div class="form-item-tip">用户名一旦设置较难修改，请谨慎填写</div>
                        </el-form-item>
                      </el-col>
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="昵称" prop="nickname">
                          <el-input
                            v-model="profileForm.nickname"
                            placeholder="请输入昵称"
                            :prefix-icon="User"
                            maxlength="20"
                            show-word-limit
                          />
                          <div class="form-item-tip">昵称将作为您的显示名称</div>
                        </el-form-item>
                      </el-col>
                    </el-row>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="邮箱" prop="email">
                          <el-input
                            v-model="profileForm.email"
                            placeholder="请输入邮箱"
                            :prefix-icon="Message"
                          />
                          <div class="form-item-tip">用于接收通知和密码重置</div>
                        </el-form-item>
                      </el-col>
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="手机号码" prop="mobile" required>
                          <el-input
                            v-model="profileForm.mobile"
                            placeholder="请输入手机号码"
                            :prefix-icon="Iphone"
                            maxlength="11"
                          />
                          <div class="form-item-tip">绑定手机可用于登录和找回密码</div>
                        </el-form-item>
                      </el-col>
                    </el-row>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="性别" prop="gender">
                          <el-radio-group v-model="profileForm.gender" class="gender-radio-group">
                            <el-radio :value="1">男</el-radio>
                            <el-radio :value="2">女</el-radio>
                            <el-radio :value="0">保密</el-radio>
                          </el-radio-group>
                        </el-form-item>
                      </el-col>
                    </el-row>
                  </div>

                  <div class="form-section">
                    <h3 class="form-section-title">
                      <el-icon><Memo /></el-icon>
                      <span>详细资料</span>
                      <el-tooltip content="这些信息仅您自己可见" placement="top">
                        <div>
                          <el-icon class="info-icon"><InfoFilled /></el-icon>
                        </div>
                      </el-tooltip>
                    </h3>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="真实姓名" prop="real_name">
                          <el-input
                            v-model="profileForm.real_name"
                            placeholder="请输入真实姓名"
                            :prefix-icon="User"
                          />
                          <div class="form-item-tip">仅用于身份验证，不会公开显示</div>
                        </el-form-item>
                      </el-col>
                      <el-col :xs="24" :sm="12">
                        <el-form-item label="生日" prop="birthday">
                          <el-date-picker
                            v-model="profileForm.birthday"
                            type="date"
                            placeholder="选择生日"
                            class="w-100"
                            :prefix-icon="Calendar"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            :disabled-date="disabledDate"
                          />
                          <div class="form-item-tip">用于生日提醒和年龄计算</div>
                        </el-form-item>
                      </el-col>
                    </el-row>

                    <el-form-item label="个人简介" prop="introduction">
                      <el-input
                        v-model="profileForm.introduction"
                        type="textarea"
                        :rows="4"
                        placeholder="介绍一下自己吧..."
                        maxlength="500"
                        show-word-limit
                        @input="syncToIntroduction"
                      />
                      <div class="form-item-tip">个人简介将显示在您的个人页面</div>
                    </el-form-item>

                    <el-form-item label="备注" prop="remark">
                      <el-input
                        v-model="profileForm.remark"
                        type="textarea"
                        :rows="2"
                        placeholder="添加一些个人备注..."
                        maxlength="200"
                        show-word-limit
                      />
                      <div class="form-item-tip">备注信息仅自己可见，可用于记录一些个人信息</div>
                    </el-form-item>
                  </div>

                  <div class="form-actions">
                    <el-button type="primary" @click="saveProfile" :loading="saving" :disabled="!formChanged">保存修改</el-button>
                    <el-button @click="resetForm" :disabled="!formChanged">重置</el-button>
                    <span v-if="formChanged" class="unsaved-tip">* 有未保存的修改</span>
                  </div>
                </el-form>
              </div>

              <!-- 账户安全表单 -->
              <div v-show="activeTab === 'security'">
                <div class="security-items">
                  <div class="security-item">
                    <div class="security-info">
                      <h3>修改密码</h3>
                      <p>定期修改密码可以保障您的账户安全</p>
                    </div>
                    <el-button type="primary" @click="showChangePasswordDialog">修改密码</el-button>
                  </div>


                  <div class="security-item">
                    <div class="security-info">
                      <h3>登录记录</h3>
                      <p>查看您的账号登录历史记录</p>
                    </div>
                    <el-button type="info" @click="showLoginHistoryDialog">查看记录</el-button>
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>
    </main>
    <Footer />
    <BackToTop />

    <!-- 修改密码对话框 -->
    <el-dialog
      v-model="changePasswordVisible"
      title="修改密码"
      width="500px"
      destroy-on-close
      class="change-password-dialog"
    >
      <el-form
        ref="passwordFormRef"
        :model="passwordForm"
        :rules="passwordRules"
        label-position="left"
        label-width="100px"
        :status-icon="true"
      >
        <el-form-item label="当前密码" prop="currentPassword">
          <el-input
            v-model="passwordForm.currentPassword"
            type="password"
            placeholder="请输入当前密码"
            show-password
            :prefix-icon="Lock"
          />
        </el-form-item>
        <el-form-item label="新密码" prop="newPassword">
          <el-input
            v-model="passwordForm.newPassword"
            type="password"
            placeholder="请输入新密码"
            show-password
            :prefix-icon="Lock"
          />
        </el-form-item>
        <el-form-item label="确认新密码" prop="confirmPassword">
          <el-input
            v-model="passwordForm.confirmPassword"
            type="password"
            placeholder="请再次输入新密码"
            show-password
            :prefix-icon="Lock"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="changePasswordVisible = false">取消</el-button>
          <el-button type="primary" @click="changePassword" :loading="changingPassword">
            确认修改
          </el-button>
        </span>
      </template>
    </el-dialog>


    <!-- 登录历史对话框 -->
    <el-dialog
      v-model="loginHistoryVisible"
      title="登录历史"
      width="800px"
    >
      <div class="login-history-wrapper">
        <div class="login-stats-summary">
          <div class="stat-item">
            <div class="stat-label">总登录次数</div>
            <div class="stat-value">{{ loginStats.total_logins || 0 }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">成功登录</div>
            <div class="stat-value success">{{ loginStats.success_logins || 0 }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">失败登录</div>
            <div class="stat-value danger">{{ loginStats.failed_logins || 0 }}</div>
          </div>
        </div>

        <el-divider content-position="center">登录记录</el-divider>

        <el-empty v-if="!loginLogs.length" description="暂无登录记录" v-loading="loadingLogs" />
        <el-timeline v-else>
          <el-timeline-item
            v-for="(item, index) in loginLogs"
            :key="index"
            :timestamp="String(item.create_time || '')"
            :type="index === 0 ? 'primary' : ''"
          >
            <el-card class="login-history-card">
              <div class="login-history-item">
                <div class="login-info">
                  <div class="login-info-item">
                    <div class="login-info-label">登录IP：</div>
                    <div class="login-info-value">{{ item.ip_address || '未知' }}</div>
                  </div>
                  <div class="login-info-item">
                    <div class="login-info-label">登录设备：</div>
                    <div class="login-info-value">{{ item.device || '未知' }}</div>
                  </div>
                  <div class="login-info-item">
                    <div class="login-info-label">登录地点：</div>
                    <div class="login-info-value">{{ item.location || '未知' }}</div>
                  </div>
                  <div class="login-info-item" v-if="item.remark">
                    <div class="login-info-label">备注：</div>
                    <div class="login-info-value">{{ item.remark }}</div>
                  </div>
                </div>
                <div class="login-status">
                  <el-tag :type="item.status === 1 ? 'success' : 'danger'">
                    {{ item.status === 1 ? '成功' : '失败' }}
                  </el-tag>
                </div>
              </div>
            </el-card>
          </el-timeline-item>
        </el-timeline>

        <div class="pagination-container" v-if="loginLogs.length">
          <el-pagination
            v-model:current-page="loginLogPage"
            v-model:page-size="loginLogLimit"
            :page-sizes="[5, 10, 20, 50]"
            layout="total, sizes, prev, pager, next"
            :total="loginLogTotal"
            @size-change="handleLoginLogSizeChange"
            @current-change="handleLoginLogCurrentChange"
          />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import BackToTop from './components/BackToTop.vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useUserStore } from '@/stores/user';
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { defaultAvatarList, defaultAvatar } from '@/assets/images/default-avatars.js';
import {
  User,
  Message,
  Lock,
  Edit,
  Calendar,
  InfoFilled,
  Iphone,
  Upload,
  Picture,
  Close,
  CircleCheck,
  CircleClose,
  Loading,
  Memo,
  Money
} from '@element-plus/icons-vue';

export default {
  name: 'PersonalCenter',
  components: {
    Header,
    Footer,
    BackToTop,
    User,
    Message,
    Lock,
    Edit,
    Calendar,
    InfoFilled,
    Iphone,
    Upload,
    Picture,
    Close,
    CircleCheck,
    CircleClose,
    Loading,
    Memo,
    Money
  },
  setup() {
    const userStore = useUserStore();
    const { t } = useI18n();
    const profileForm = ref({
      username: '',
      nickname: '',
      email: '',
      real_name: '',
      gender: '',
      birthday: '',
      mobile: '',
      introduction: '',
      remark: ''
    });

    // 存储初始表单值用于比较是否有更改
    const initialFormValues = ref({});

    // 初始化userInfo为空对象而不是undefined
    const userInfo = ref({
      avatar: '',
      username: '',
      nickname: '',
      id: '',
      created_at: '',
      email: '',
      gender: '',
      last_login_time: '',
      status: 1,
      mobile: '',
      balance: 0,
      points: 0
    });

    const saving = ref(false);
    const changingPassword = ref(false);
    const profileFormRef = ref(null);
    const passwordFormRef = ref(null);
    const activeTab = ref('basic');
    const twoFactorEnabled = ref(false);

    // 头像上传预览相关变量
    const previewImage = ref('');

    // 用户名可用性检查
    const usernameStatus = reactive({
      checking: false,
      available: null,
      message: ''
    });

    // 登录历史
    const loginHistoryVisible = ref(false);
    const loginLogs = ref([]);
    const loginStats = ref({});
    const loadingLogs = ref(false);
    const loginLogPage = ref(1);
    const loginLogLimit = ref(10);
    const loginLogTotal = ref(0);

    // 检查表单是否有更改
    const formChanged = computed(() => {
      if (!initialFormValues.value || Object.keys(initialFormValues.value).length === 0) {
        return false;
      }

      return Object.keys(profileForm.value).some(key => {
        return profileForm.value[key] !== initialFormValues.value[key];
      });
    });

    // 修改密码表单
    const passwordForm = ref({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });

    // 修改密码对话框可见性
    const changePasswordVisible = ref(false);

    // 表单验证规则
    const profileRules = {
      username: [
        { required: true, message: '请输入用户名', trigger: 'blur' },
        { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' },
        { pattern: /^[a-zA-Z0-9_-]+$/, message: '用户名只能包含字母、数字、下划线和连字符', trigger: 'blur' }
      ],
      nickname: [
        { required: true, message: '请输入昵称', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ],
      mobile: [
        { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号码', trigger: 'blur' }
      ],
      real_name: [
        { max: 20, message: '真实姓名不能超过20个字符', trigger: 'blur' }
      ],
      introduction: [
        { max: 500, message: '个人简介不能超过500个字符', trigger: 'blur' }
      ],
      remark: [
        { max: 200, message: '备注不能超过200个字符', trigger: 'blur' }
      ]
    };

    // 密码表单验证规则
    const passwordRules = {
      currentPassword: [
        { required: true, message: '请输入当前密码', trigger: 'blur' }
      ],
      newPassword: [
        { required: true, message: '请输入新密码', trigger: 'blur' },
        { min: 6, message: '密码长度不能少于6个字符', trigger: 'blur' }
      ],
      confirmPassword: [
        { required: true, message: '请再次输入新密码', trigger: 'blur' },
        {
          validator: (rule, value, callback) => {
            if (value !== passwordForm.value.newPassword) {
              callback(new Error('两次输入密码不一致'));
            } else {
              callback();
            }
          },
          trigger: 'blur'
        }
      ]
    };

    // 日期禁用函数 - 禁用未来日期
    const disabledDate = (time) => {
      return time.getTime() > Date.now();
    };

    // 格式化性别显示
    const formatGender = (gender) => {
      if (gender === undefined || gender === null) return '保密';

      // 处理数字类型的性别
      if (typeof gender === 'number' || !isNaN(Number(gender))) {
        const genderNum = Number(gender);
        if (genderNum === 1) return '男';
        if (genderNum === 2) return '女';
        return '保密';
      }

      // 处理字符串类型的性别
      const genderMap = {
        'male': '男',
        'female': '女',
        'secret': '保密',
        '1': '男',
        '2': '女',
        '0': '保密'
      };
      return genderMap[gender] || '保密';
    };

    // 将字符串性别转换为数字
    const convertGenderToNumber = (gender) => {
      if (gender === undefined || gender === null) return 0;

      // 如果已经是数字，直接返回
      if (typeof gender === 'number') return gender;
      if (!isNaN(Number(gender))) return Number(gender);

      // 字符串转换为数字
      const genderMap = {
        'male': 1,
        'female': 2,
        'secret': 0
      };
      return genderMap[gender] || 0;
    };

    // 上传默认头像
    const uploadDefaultAvatar = async (avatarUrl) => {
      try {
        saving.value = true;

        // 使用Vite兼容的方式获取头像路径
        const fullAvatarPath = getAvatarUrl(avatarUrl);

        // 创建FormData对象
        const formData = new FormData();

        // 从默认头像路径获取文件
        try {
          const response = await fetch(fullAvatarPath);
          const blob = await response.blob();
          const file = new File([blob], avatarUrl.split('/').pop(), { type: blob.type });
          formData.append('avatar', file);

          // 调用头像上传API
          const result =await userStore.updateUserAvatar(formData);
          if (result.code===200 && result) {
            ElMessage.success('头像更新成功');
          }else{
            ElMessage.error(result.msg);
          }

          // 更新用户信息
          if (apiResponse && apiResponse.avatar) {
            userInfo.value.avatar = apiResponse.avatar;
          } else {
            userInfo.value.avatar = fullAvatarPath;
          }

          previewImage.value = '';
        } catch (fetchError) {
          console.error('获取默认头像文件失败:', fetchError);
          ElMessage.error('获取默认头像文件失败，请重试');
        }
      } catch (error) {
        console.error('设置默认头像失败:', error);
        ElMessage.error('设置默认头像失败，请重试' + (error.message ? ': ' + error.message : ''));
      } finally {
        saving.value = false;
      }
    };

    // 取消预览
    const cancelPreview = () => {
      previewImage.value = '';
    };

    // 检查用户名是否可用
    const checkUsernameAvailable = debounce(async () => {
      const username = profileForm.value.username;

      // 如果用户名没变或为空，不做检查
      if (!username || username === initialFormValues.value.username) {
        usernameStatus.checking = false;
        usernameStatus.available = null;
        usernameStatus.message = '';
        return;
      }

      // 长度和格式检查
      if (username.length < 3) {
        usernameStatus.checking = false;
        usernameStatus.available = false;
        usernameStatus.message = '用户名太短';
        return;
      }

      if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        usernameStatus.checking = false;
        usernameStatus.available = false;
        usernameStatus.message = '用户名格式不正确';
        return;
      }

      // 开始检查
      usernameStatus.checking = true;
      usernameStatus.available = null;
      usernameStatus.message = '检查中...';

      try {
        // 模拟API调用，实际项目中应替换为真实API
        await new Promise(resolve => setTimeout(resolve, 500));

        // 随机模拟结果，实际项目应使用真实API响应
        const isAvailable = Math.random() > 0.3;

        usernameStatus.available = isAvailable;
        usernameStatus.message = isAvailable ? '用户名可用' : '用户名已被使用';
      } catch (error) {
        console.error('检查用户名失败:', error);
        usernameStatus.available = null;
        usernameStatus.message = '检查失败';
      } finally {
        usernameStatus.checking = false;
      }
    }, 500);

    // 函数防抖
    function debounce(fn, delay) {
      let timer = null;
      return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timer);
        timer = setTimeout(() => {
          fn.apply(context, args);
        }, delay);
      };
    }

    // 获取用户信息
    const fetchUserInfo = async () => {
      try {
        // 请求最新的用户信息
        const response = await userStore.getUserInfo();

        // 如果有数据，处理用户信息
        if (response.code===200 && response.data) {
          // 合并用户信息，确保所有必要字段都存在
          userInfo.value = {
            ...userInfo.value, // 保留默认值
            ...response.data, // 使用服务器返回的数据覆盖
          };

          // 如果有消息，可以显示
          if (response.msg) {
            console.log('服务器消息:', response.msg);
          }
        } else if (response) {
          // 兼容直接返回数据的情况
          userInfo.value = {
            ...userInfo.value,
            ...response,
          };
        }

        // 初始化表单
        initProfileForm();
      } catch (error) {
        console.error('获取用户信息失败:', error);
        ElMessage.error('获取用户信息失败，请重试');
      }
    };

    // 初始化个人资料表单
    const initProfileForm = () => {
      // 处理个人简介字段
      const introduction = userInfo.value.introduction || '';

      // 处理gender字段，确保是数字类型
      let genderValue = 0; // 默认为0（保密）

      if (userInfo.value.gender !== undefined && userInfo.value.gender !== null) {
        // 如果是字符串类型，转换为数字
        genderValue = convertGenderToNumber(userInfo.value.gender);
      }

      profileForm.value = {
        username: userInfo.value.username || '',
        nickname: userInfo.value.nickname || '',
        email: userInfo.value.email || '',
        real_name: userInfo.value.real_name || '',
        gender: genderValue,
        birthday: userInfo.value.birthday || '',
        introduction: introduction,
        mobile: userInfo.value.mobile || '',
        remark: userInfo.value.remark || '',
      };

      // 存储初始值用于比较
      initialFormValues.value = { ...profileForm.value };
    };

    // 保存个人资料
    const saveProfile = async () => {
      try {
        await profileFormRef.value.validate();
        saving.value = true;

        // 检查用户名是否可用
        if (profileForm.value.username !== initialFormValues.value.username &&
            usernameStatus.available === false) {
          ElMessage.error('用户名已被使用，请更换');
          saving.value = false;
          return;
        }

        // 创建要提交的数据对象
        const formData = { ...profileForm.value };

        // 确保 gender 是数字类型
        if (formData.gender !== undefined && formData.gender !== null) {
          formData.gender = Number(formData.gender);
        }

        // 调用API并获取返回结果
        const updateResult = await userStore.updateUser(formData);

        // 读取返回的数据
        if (updateResult.code === 200 && updateResult) {
          // 显示成功消息，使用API返回的消息
          ElMessage.success(updateResult.msg );
          // 如果API返回了用户数据，可以直接处理
          if (updateResult.data) {
            // 将返回的数据合并到当前用户信息中
            Object.assign(userInfo.value, updateResult.data);
          }
        } else {
          ElMessage.error(updateResult.msg );
        }

        // 更新用户信息
        await fetchUserInfo();
      } catch (error) {
        ElMessage.error('保存个人资料失败，请重试');
      } finally {
        saving.value = false;
      }
    };

    // 重置表单
    const resetForm = () => {
      initProfileForm();
    };

    // 上传头像前的验证
    const beforeAvatarUpload = (file) => {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/gif';
      const isLt2M = file.size / 1024 / 1024 < 2;

      if (!isJPG) {
        ElMessage.error('头像只能是JPG/PNG/GIF格式!');
        return false;
      }
      if (!isLt2M) {
        ElMessage.error('头像大小不能超过2MB!');
        return false;
      }

      // 读取文件并预览
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImage.value = e.target.result;
      };
      reader.readAsDataURL(file);

      return true;
    };

    // 自定义上传头像
    const uploadAvatar = async (options) => {
      try {
        saving.value = true;
        const formData = new FormData();
        formData.append('avatar', options.file);

        // 调用头像上传API
        const response = await userStore.updateUserAvatar(formData);
        console.log('头像上传响应:', response);

        // 读取返回的数据
        if (response) {
          // 显示成功消息，使用API返回的消息
          ElMessage.success(response.msg || '头像上传成功');

          // 如果API返回了头像数据
          if (response.data && response.data.avatar) {
            userInfo.value.avatar = response.data.avatar;
          } else if (response.avatar) {
            // 兼容直接返回头像字段的情况
            userInfo.value.avatar = response.avatar;
          } else {
            // 如果没有返回头像URL，可以使用本地URL
            userInfo.value.avatar = URL.createObjectURL(options.file);
            console.log('使用本地URL:', userInfo.value.avatar);
          }
        } else {
          ElMessage.success('头像上传成功');
          userInfo.value.avatar = URL.createObjectURL(options.file);
        }

        previewImage.value = ''; // 清除预览
      } catch (error) {
        console.error('上传头像失败:', error);
        ElMessage.error('上传头像失败，请重试' + (error.message ? ': ' + error.message : ''));
      } finally {
        saving.value = false;
      }
    };

    // 显示修改密码对话框
    const showChangePasswordDialog = () => {
      passwordForm.value = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      };
      changePasswordVisible.value = true;
    };

    // 修改密码
    const changePassword = async () => {
      try {
        await passwordFormRef.value.validate();
        changingPassword.value = true;

        // 调用修改密码API
     const re=  await userStore.updateUserPassword({
          old_password: passwordForm.value.currentPassword,
          new_password: passwordForm.value.newPassword,
          confirm_password: passwordForm.value.confirmPassword
        });
        if(re && re.code===200){
          ElMessage.success(re.msg);
          changePasswordVisible.value = false;
        }else{
          ElMessage.error(re.msg);
        }
      } catch (error) {
        ElMessage.error('修改密码失败，请重试');
      } finally {
        changingPassword.value = false;
      }
    };

    // 格式化日期
    const formatDate = (dateValue) => {
      if (!dateValue) return '未知';

      // 处理Unix时间戳（秒）
      let date;
      if (typeof dateValue === 'number' || (typeof dateValue === 'string' && !isNaN(dateValue) && dateValue.length === 10)) {
        date = new Date(Number(dateValue) * 1000);
      } else {
        date = new Date(dateValue);
      }

      // 检查日期是否有效
      if (isNaN(date.getTime())) return '未知';

      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };

    // 格式化日期时间
    const formatDateTime = (dateValue) => {
      if (!dateValue) return '未知';

      // 处理Unix时间戳（秒）
      let date;
      if (typeof dateValue === 'number' || (typeof dateValue === 'string' && !isNaN(dateValue) && dateValue.length === 10)) {
        date = new Date(Number(dateValue) * 1000);
      } else {
        date = new Date(dateValue);
      }

      // 检查日期是否有效
      if (isNaN(date.getTime())) return '未知';

      return `${formatDate(dateValue)} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
    };


    // 获取登录日志
    const fetchLoginLogs = async () => {
      try {
        loadingLogs.value = true;
        const response = await userStore.getLoginLogs(loginLogPage.value, loginLogLimit.value);

        if (response && response.code === 200) {
          loginLogs.value = response.data.list || [];
          loginLogTotal.value = response.data.total || 0;
          ElMessage.success(response.msg || '获取登录日志成功');
        } else {
          ElMessage.error(response.msg || '获取登录日志失败');
          loginLogs.value = [];
          loginLogTotal.value = 0;
        }
      } catch (error) {
        console.error('获取登录日志失败:', error);
        ElMessage.error('获取登录日志失败，请重试');
        loginLogs.value = [];
        loginLogTotal.value = 0;
      } finally {
        loadingLogs.value = false;
      }
    };

    // 获取登录统计信息
    const fetchLoginStats = async () => {
      try {
        const response = await userStore.getLoginStats();

        if (response && response.code === 200) {
          loginStats.value = response.data || {};
        } else {
          loginStats.value = {};
        }
      } catch (error) {
        console.error('获取登录统计信息失败:', error);
        loginStats.value = {};
      }
    };

    // 处理登录日志分页大小变化
    const handleLoginLogSizeChange = (size) => {
      loginLogLimit.value = size;
      fetchLoginLogs();
    };

    // 处理登录日志分页切换
    const handleLoginLogCurrentChange = (page) => {
      loginLogPage.value = page;
      fetchLoginLogs();
    };

    // 显示登录历史对话框
    const showLoginHistoryDialog = () => {
      loginLogPage.value = 1;
      loginLogLimit.value = 10;
      fetchLoginStats();
      fetchLoginLogs();
      loginHistoryVisible.value = true;
    };

    // 重新添加计算安全评分的函数
    const calculateSecurityScore = () => {
      let score = 60; // 基础分

      // 有头像加10分
      if (userInfo.value.avatar) score += 10;

      // 有个人简介加10分
      if (userInfo.value.introduction) score += 10;

      // 邮箱已设置加10分
      if (userInfo.value.email) score += 10;

      // 二次验证已开启加10分
      if (twoFactorEnabled.value) score += 10;

      return Math.min(score, 100);
    };

    // 获取评分颜色
    const getScoreColor = () => {
      const score = calculateSecurityScore();
      if (score >= 80) return '#67c23a';
      if (score >= 60) return '#e6a23c';
      return '#f56c6c';
    };

    // 获取安全提示
    const getSecurityTips = () => {
      const score = calculateSecurityScore();
      if (score >= 80) return '您的账户安全级别较高';
      if (score >= 60) return '建议完善个人资料提高安全性';
      return '请尽快完善个人资料和安全设置';
    };

    // 添加格式化余额的方法
    const formatBalance = (balance) => {
      if (balance === undefined || balance === null || balance === '') return '¥0.00';

      // 处理可能的非数字值
      const numBalance = parseFloat(balance);
      if (isNaN(numBalance)) return '¥0.00';

      return `¥${numBalance.toFixed(2)}`;
    };

    // 格式化手机号，隐藏中间部分
    const formatMobile = (mobile) => {
      if (!mobile) return '未设置';
      if (mobile.length < 7) return mobile; // 太短的号码不处理

      // 显示前3位和后4位，中间用星号代替
      return mobile.substring(0, 3) + '****' + mobile.substring(mobile.length - 4);
    };

    // 同步introduction字段
    const syncToIntroduction = (value) => {
      profileForm.value.introduction = value;
    };

    // 获取头像URL的辅助函数
    const getAvatarUrl = (avatarName) => {
      if (!avatarName) return '';

      // 如果是完整URL，直接返回
      if (avatarName.startsWith('http://') || avatarName.startsWith('https://')) {
        return avatarName;
      }

      // 如果是相对路径，添加基础URL
      if (avatarName.startsWith('/')) {
        return avatarName; // 已经是从根路径开始的路径
      }

      // 默认头像在public/images目录下
      return `/images/${avatarName}`;
    };

    onMounted(() => {
      fetchUserInfo();
    });

    return {
      t,
      userInfo,
      profileForm,
      profileRules,
      defaultAvatar,
      saving,
      changingPassword,
      profileFormRef,
      passwordFormRef,
      passwordForm,
      passwordRules,
      changePasswordVisible,
      saveProfile,
      resetForm,
      beforeAvatarUpload,
      uploadAvatar,
      showChangePasswordDialog,
      changePassword,
      formatDate,
      formatDateTime,
      formatGender,
      convertGenderToNumber,
      activeTab,
      twoFactorEnabled,
      calculateSecurityScore,
      getScoreColor,
      getSecurityTips,
      loginHistoryVisible,
      loginLogs,
      loginStats,
      loadingLogs,
      loginLogPage,
      loginLogLimit,
      loginLogTotal,
      fetchLoginLogs,
      fetchLoginStats,
      handleLoginLogSizeChange,
      handleLoginLogCurrentChange,
      showLoginHistoryDialog,
      disabledDate,
      previewImage,
      cancelPreview,
      usernameStatus,
      checkUsernameAvailable,
      formChanged,
      User,
      Message,
      Lock,
      Edit,
      Calendar,
      InfoFilled,
      Iphone,
      Upload,
      Picture,
      Close,
      CircleCheck,
      CircleClose,
      Loading,
      Memo,
      Money,
      formatBalance,
      formatMobile,
      syncToIntroduction,
      getAvatarUrl
    };
  }
};
</script>

<style scoped>
.personal-center-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: 'Microsoft YaHei', Arial, sans-serif;
}

.main-content {
  flex: 1;
  padding: 30px 0;
  background-color: var(--el-bg-color-page, #f5f5f5);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.breadcrumb-container {
  margin-bottom: 15px;
  padding: 10px 0;
}

.profile-row {
  margin: 0 !important;
}

.profile-col {
  margin-bottom: 20px;
}

.profile-info-card,
.profile-form-card {
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  height: 100%;
}

.profile-form-card :deep(.el-card__header) {
  padding: 0;
}

.avatar-container {
  position: relative;
  text-align: center;
  padding: 20px 0;
  background: linear-gradient(to bottom, var(--el-color-primary-light-8, #e6f1fc), transparent);
  border-radius: 8px 8px 0 0;
}

.avatar-wrapper {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
}

.avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s;
}

.avatar-hover-mask {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.3s;
  border-radius: 50%;
}

.avatar-wrapper:hover .avatar-hover-mask {
  opacity: 1;
}

.avatar-wrapper:hover .avatar {
  transform: scale(1.05);
}

.upload-btn {
  background-color: transparent;
  border: 2px solid white;
}

.username {
  margin: 15px 0 5px;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-text-color-primary, #303133);
}

.user-id {
  margin: 5px 0;
  font-size: 14px;
  color: var(--el-text-color-secondary, #909399);
}

.info-section {
  padding: 15px;
  border-bottom: 1px solid var(--el-border-color-lighter, #EBEEF5);
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  padding-left: 5px;
  color: var(--el-text-color-primary, #303133);
  font-weight: 500;
}

.section-header .el-icon {
  margin-right: 8px;
  font-size: 16px;
  color: var(--el-color-primary, #409EFF);
}

.info-list {
  padding: 0 5px;
}

.info-item {
  display: flex;
  align-items: center;
  margin: 10px 0;
}

.info-item label {
  color: var(--el-text-color-secondary, #909399);
  width: 70px;
  flex-shrink: 0;
}

.info-item span {
  flex: 1;
  color: var(--el-text-color-primary, #303133);
}

.security-score-section {
  padding: 15px;
}

.security-score {
  padding: 10px 5px;
}

.security-tips {
  margin-top: 10px;
  font-size: 13px;
  color: var(--el-text-color-secondary, #909399);
  text-align: center;
}

.profile-tabs {
  width: 100%;
}

.card-header {
  padding: 0;
  border-bottom: none;
}

.profile-tabs {
  width: 100%;
}

:deep(.el-tabs--border-card) {
  background: transparent;
  border: none;
  box-shadow: none;
}

:deep(.el-tabs__header) {
  background-color: #f5f7fa;
  border-radius: 8px 8px 0 0;
  margin: 0;
  border-bottom: none;
}

:deep(.el-tabs__nav) {
  border: none;
  height: 60px;
  display: flex;
  width: 100%;
}

:deep(.el-tabs__item) {
  flex: 1;
  text-align: center;
  height: 60px;
  line-height: 60px;
  font-size: 16px;
  font-weight: 500;
  padding: 0;
  transition: all 0.3s;
}

:deep(.el-tabs__item.is-active) {
  background-color: #fff;
  color: var(--el-color-primary);
  border-bottom: 3px solid var(--el-color-primary);
}

:deep(.el-tabs__item:hover) {
  color: var(--el-color-primary);
}

.custom-tab-label {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.custom-tab-label .el-icon {
  margin-right: 8px;
  font-size: 18px;
}

.custom-tab-label span {
  font-size: 16px;
}

.profile-form {
  padding: 20px;
}

.form-section {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px dashed var(--el-border-color-light);
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 10px;
}

.form-section-title {
  display: flex;
  align-items: center;
  margin: 0 0 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--el-border-color-light);
  font-size: 16px;
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.form-section-title .el-icon {
  margin-right: 8px;
  color: var(--el-color-primary);
}

.info-icon {
  margin-left: 5px;
  color: var(--el-color-info);
  cursor: help;
}

.form-item-tip {
  font-size: 12px;
  color: var(--el-text-color-secondary);
  margin-top: 4px;
  line-height: 1.4;
}

.form-actions {
  display: flex;
  align-items: center;
  margin-top: 30px;
}

.unsaved-tip {
  margin-left: 10px;
  color: var(--el-color-warning);
  font-size: 13px;
}

.w-100 {
  width: 100%;
}

.avatar-upload-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background-color: var(--el-fill-color-light);
  border-radius: 8px;
}

.upload-preview-container {
  position: relative;
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.preview-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  border: 4px solid white;
}

.avatar-operations {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.avatar-tips {
  text-align: center;
  margin-top: 10px;
}

.avatar-tips p {
  margin: 5px 0;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.default-avatars-container {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: center;
}

.default-avatar-item {
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  border: 2px solid transparent;
  transition: all 0.3s;
}

.default-avatar-item:hover {
  background-color: var(--el-fill-color-light);
}

.default-avatar-item.active {
  border-color: var(--el-color-primary);
  background-color: var(--el-color-primary-light-9);
}

.gender-radio-group {
  display: flex;
  gap: 20px;
}

.security-items {
  padding: 20px;
}

.security-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px;
  margin-bottom: 15px;
  border: 1px solid var(--el-border-color-lighter, #EBEEF5);
  border-radius: 4px;
  transition: all 0.3s;
}

.security-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.security-info h3 {
  text-align: left;
  font-size: 16px;
  margin: 0 0 5px;
  color: var(--el-text-color-primary, #303133);
}

.security-info p {
  font-size: 14px;
  margin: 0;
  color: var(--el-text-color-secondary, #909399);
}

.login-history-wrapper {
  max-height: 500px;
  overflow-y: auto;
}

.login-stats-summary {
  display: flex;
  justify-content: space-around;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.login-stats-summary .stat-item {
  text-align: center;
  padding: 10px 15px;
  border-radius: 4px;
  background-color: var(--el-fill-color-light);
  min-width: 120px;
  margin: 5px;
}

.login-stats-summary .stat-label {
  font-size: 14px;
  color: var(--el-text-color-secondary);
  margin-bottom: 5px;
}

.login-stats-summary .stat-value {
  font-size: 24px;
  font-weight: bold;
  color: var(--el-text-color-primary);
}

.login-stats-summary .stat-value.success {
  color: var(--el-color-success);
}

.login-stats-summary .stat-value.danger {
  color: var(--el-color-danger);
}

.login-history-card {
  margin-bottom: 10px;
}

.login-history-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.login-info {
  flex: 1;
}

.login-info-item {
  display: flex;
  margin-bottom: 8px;
}

.login-info-label {
  width: 100px;
  text-align: left;
  font-weight: 500;
  color: var(--el-text-color-regular);
  flex-shrink: 0;
}

.login-info-value {
  flex: 1;
  text-align: left;
  word-break: break-all;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.is-loading {
  animation: loading-rotate 2s linear infinite;
}

@keyframes loading-rotate {
  100% {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 15px 0;
  }

  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .avatar-container {
    margin-right: 0;
    margin-bottom: 20px;
  }

  .security-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .security-info {
    margin-bottom: 15px;
    width: 100%;
    text-align: left;
  }

  .info-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .info-item label {
    width: 100%;
    margin-bottom: 5px;
  }

  .profile-form {
    padding: 15px 10px;
  }

  .gender-radio-group {
    flex-direction: column;
    gap: 10px;
  }

  .avatar-operations {
    flex-wrap: wrap;
    justify-content: center;
  }

  .form-actions {
    flex-wrap: wrap;
  }

  .unsaved-tip {
    width: 100%;
    margin-top: 10px;
    margin-left: 0;
    text-align: center;
  }
}

/* 添加右上角头像选择器样式 */
.avatar-container {
  position: relative;
}

.avatar-default-selector {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 10;
}

.avatar-default-selector .el-button {
  background-color: rgba(64, 158, 255, 0.8);
  color: #fff;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.avatar-default-selector .el-button:hover {
  background-color: rgba(64, 158, 255, 1);
}

.balance-value, .points-value {
  font-weight: 500;
  color: var(--el-color-primary);
}

.balance-value {
  font-size: 15px;
}

.required-field {
  color: #f56c6c;
  margin-right: 4px;
}

/* 确保表单中的必填标记正确显示 */
:deep(.el-form-item.is-required:not(.is-no-asterisk) > .el-form-item__label:before) {
  content: '*';
  color: #f56c6c;
  margin-right: 4px;
}

/* 修改密码对话框样式 */
.change-password-dialog {
  :deep(.el-dialog__header) {
    text-align: left;
    padding-left: 20px;
  }

  :deep(.el-dialog__title) {
    font-weight: 600;
    font-size: 18px;
  }

  :deep(.el-form-item__label) {
    text-align: left;
    font-weight: 500;
    padding-right: 12px;
    color: var(--el-text-color-primary);
  }

  :deep(.el-input__wrapper) {
    width: 100%;
  }

  :deep(.el-form-item) {
    margin-bottom: 22px;
  }

  :deep(.el-dialog__body) {
    padding: 20px 20px 10px;
  }
}
</style>