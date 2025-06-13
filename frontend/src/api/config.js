/**
 * API接口地址配置文件
 * 集中管理所有接口地址，便于维护
 */

// 环境配置
const ENV = {
  DEV: 'development',
  TEST: 'test',
  PROD: 'production'
};

// 当前环境
const CURRENT_ENV = import.meta.env.VITE_APP_ENV || ENV.DEV;

// 直接从环境变量获取网站地址
const HOST_URL = import.meta.env.VITE_APP_API_URL;

// 从环境变量获取API基础URL
const BASE_URL = `${HOST_URL}/api`;


// 添加CORS配置
const CORS_CONFIG = {
  credentials: true
};

// 仅在开发环境输出日志
if (CURRENT_ENV === ENV.DEV) {
}



// 网站配置
const CONFIG_API = {
  GET_CONFIG: `${BASE_URL}/admin/getConfig`, // 获取网站配置
  UPDATE_CONFIG: `${BASE_URL}/admin/updateConfig`, // 更新网站配置
};

//ip查询相关接口
const IP_API = {
  QUERY: `${BASE_URL}/ipLocation/query`, // 查询单个IP地理位置
  BATCH_QUERY: `${BASE_URL}/ipLocation/batchQuery`, // 批量查询IP地理位置
  ADMIN_QUERY: `${BASE_URL}/adminIpLocation/query`, // 管理员查询单个IP地理位置
  ADMIN_BATCH_QUERY: `${BASE_URL}/adminIpLocation/batchQuery`, // 管理员批量查询IP地理位置
};


// 管理后台管理员相关接口
const ADMIN_API = {
  LOGIN: `${BASE_URL}/admin/login`,          // 管理员登录
  LOGOUT: `${BASE_URL}/admin/logout`,        // 管理员退出
  INFO: `${BASE_URL}/admin/info`,            // 获取管理员信息
  UPDATE: `${BASE_URL}/admin/update`,        // 修改管理员信息
  CHANGE_PASSWORD: `${BASE_URL}/admin/changePassword`, // 修改密码
  UPLOAD_AVATAR: `${BASE_URL}/admin/uploadAvatar`,  // 上传头像
  ADMIN_LIST: `${BASE_URL}/admin/list`,      // 管理员列表
  ADD_ADMIN: `${BASE_URL}/admin/add`,        // 添加管理员
  EDIT_ADMIN: `${BASE_URL}/admin/edit`,      // 编辑管理员
  DELETE_ADMIN: `${BASE_URL}/admin/delete`,  // 删除管理员
  USER_LIST: `${BASE_URL}/admin/userList`,  // 获取用户列表
  UPDATE_USER_STATUS: `${BASE_URL}/admin/updateUserStatus`,  // 更新用户状态
  DELETE_USER: `${BASE_URL}/admin/deleteUser`,  // 删除用户
  USER_DETAIL: `${BASE_URL}/admin/userDetail`,  // 获取用户详情
  UPDATE_USER: `${BASE_URL}/admin/editUser`,  // 更新用户信息
  EDIT_USER: `${BASE_URL}/admin/editUser`,  // 编辑用户
  CONFIG: `${BASE_URL}/admin/getConfig`,        // 获取网站配置
  UPDATE_CONFIG: `${BASE_URL}/admin/updateConfig`, // 更新网站配置
  ACTIVITY_LOG: `${BASE_URL}/admin/activityLog`, // 获取用户活动日志
  STATISTICS: `${BASE_URL}/admin/statistics`, // 获取系统统计数据
  CLEAR_CACHE: `${BASE_URL}/admin/clearCache`, // 清除缓存
  SYSTEM_LOG: `${BASE_URL}/admin/systemLog`, // 获取系统日志
  USER_GROWTH: `${BASE_URL}/admin/userGrowth`, // 用户增长趋势数据
  ACCESS_STATS: `${BASE_URL}/admin/accessStats`, // 系统访问统计数据
  PROFILE: `${BASE_URL}/admin/profile`, // 获取管理员个人信息
  UPDATE_PROFILE: `${BASE_URL}/admin/updateProfile` // 更新管理员个人信息
};

// 认证相关接口
const AUTH_API = {
  ADMIN_LOGIN: `${BASE_URL}/auth/adminLogin`, // 管理员登录
  USER_LOGIN: `${BASE_URL}/auth/userLogin`,   // 用户登录
  LOGOUT: `${BASE_URL}/auth/logout`,           // 退出登录
  REFRESH_TOKEN: `${BASE_URL}/auth/refresh`,   // 刷新Token
  SET_LANGUAGE: `${BASE_URL}/auth/language`,   // 设置语言
};

// 管理后台管理员特权接口
const ADMIN_PRIVILEGE_API = {
  FORCE_LOGOUT: `${BASE_URL}/admin/forceLogout`, // 强制用户下线
  ACTIVITY_LOGS: `${BASE_URL}/admin/activityLogs`, // 获取用户活动日志
  CLEAR_ALL_TOKENS: `${BASE_URL}/admin/clearAllTokens` // 清除所有Token
};

// 管理后台博客相关接口
const ADMIN_BLOG_API = {
  // 文章管理
  POST_LIST: `${BASE_URL}/adminPost/list`,         // 获取文章列表
  POST_DETAIL: `${BASE_URL}/adminPost/info`,     // 获取文章详情
  POST_CREATE: `${BASE_URL}/adminPost/create`,     // 创建文章
  POST_UPDATE: `${BASE_URL}/adminPost/update`,     // 更新文章
  POST_DELETE: `${BASE_URL}/adminPost/delete`,     // 删除文章
  POST_TOP: `${BASE_URL}/adminPost/top`,           // 置顶/取消置顶文章
  POST_RECOMMEND: `${BASE_URL}/adminPost/recommend`, // 推荐/取消推荐文章
  POST_BANNER_TOP: `${BASE_URL}/adminPost/setBannerTop`, // 首页banner展示/取消首页banner展示

  // 分类管理
  CATEGORY_LIST: `${BASE_URL}/adminCat/list`,    // 获取分类列表
  CATEGORY_CREATE: `${BASE_URL}/adminCat/create`, // 创建分类
  CATEGORY_UPDATE: `${BASE_URL}/adminCat/update`, // 更新分类
  CATEGORY_DELETE: `${BASE_URL}/adminCat/delete`, // 删除分类
  UPDATE_CAT_STATUS: `${BASE_URL}/adminCat/updateStatus`, // 修改状态




  // 标签管理
  TAG_LIST: `${BASE_URL}/adminTag/list`,           // 获取标签列表
  TAG_CREATE: `${BASE_URL}/adminTag/create`,       // 创建标签
  TAG_UPDATE: `${BASE_URL}/adminTag/update`,       // 更新标签
  TAG_DELETE: `${BASE_URL}/adminTag/delete`,       // 删除标签
  UPDATE_TAG_STATUS: `${BASE_URL}/adminTag/updateStatus`, // 修改状态

  // 评论管理
  COMMENT_LIST: `${BASE_URL}/adminComment/list`,     // 获取评论列表
  COMMENT_CREATE: `${BASE_URL}/adminComment/create`, // 创建评论
  COMMENT_REPLY: `${BASE_URL}/adminComment/reply`,   // 回复评论
  COMMENT_UPDATE: `${BASE_URL}/adminComment/update`, // 更新评论状态
  COMMENT_DELETE: `${BASE_URL}/adminComment/delete`, // 删除评论
  UPDATE_COMMENT_STATUS: `${BASE_URL}/adminComment/updateStatus`, // 修改评论状态

  // 评论点赞管理
  COMMENT_LIKE_LIST: `${BASE_URL}/adminCommentLike/list`,          // 获取评论点赞列表
  COMMENT_LIKE_DELETE: `${BASE_URL}/adminCommentLike/delete`,      // 删除评论点赞
  COMMENT_LIKE_STATS: `${BASE_URL}/adminCommentLike/stats`,        // 获取评论点赞统计

  // 点赞管理
  LIKE_LIST: `${BASE_URL}/adminLike/list`,          // 获取点赞列表
  LIKE_CREATE: `${BASE_URL}/adminLike/create`,      // 创建点赞
  LIKE_DELETE: `${BASE_URL}/adminLike/delete`,      // 删除点赞
  LIKE_STATS: `${BASE_URL}/adminLike/stats`,        // 获取点赞统计


  // 友情链接
  LINK_LIST: `${BASE_URL}/adminLink/list`,          // 获取友情链接列表
  LINK_CREATE: `${BASE_URL}/adminLink/create`,      // 创建友情链接
  LINK_UPDATE: `${BASE_URL}/adminLink/update`,      // 更新友情链接
  LINK_DELETE: `${BASE_URL}/adminLink/delete`,      // 删除友情链接
  LINK_UPDATE_SORT: `${BASE_URL}/adminLink/updateSort`,      // 更新友情链接排序

  // 文件上传
  UPLOAD_IMAGE: `${BASE_URL}/admin/upload/image`,    // 上传图片
  UPLOAD_FILE: `${BASE_URL}/admin/upload/file`,      // 上传文件
  UPLOAD_LINK_LOGO: `${BASE_URL}/admin/upload/link_logo`,      // 上传友情链接LOGO
  LINK_GET_LINK_TYPE_LIST: `${BASE_URL}/adminLink/getLinkTypeList`,      // 获取友情链接类型列表

  // 联系表单
  GET_MESSAGES: `${BASE_URL}/adminContact/getMessages`,      // 获取联系表单列表
  GET_MESSAGE: `${BASE_URL}/adminContact/getMessage`,      // 获取联系表单详情
  UPDATE_STATUS: `${BASE_URL}/adminContact/updateStatus`,      // 更新联系表单状态
  REPLY_MESSAGE: `${BASE_URL}/adminContact/replyMessage`,      // 回复联系表单
  DELETE_MESSAGE: `${BASE_URL}/adminContact/deleteMessage`,      // 删除联系表单

  // 统计数据
  BLOG_STATS: `${BASE_URL}/adminPost/stats`,             // 博客统计数据
};


 // 前端用户登录
 const USER_API={
  LOGIN: `${BASE_URL}/user/login`,     // 登录
  REGISTER: `${BASE_URL}/user/register`,     // 用户注册
  LOGOUT: `${BASE_URL}/user/logout`,         // 用户退出
  INFO: `${BASE_URL}/user/info`,             // 获取用户信息
  UPDATE: `${BASE_URL}/user/update`,         // 修改用户信息
  CHANGE_PASSWORD: `${BASE_URL}/user/changePassword`, // 修改密码
  UPLOAD_AVATAR: `${BASE_URL}/user/uploadAvatar`,    // 上传头像
  MESSAGES: `${BASE_URL}/user/messages`,     // 获取用户消息
  READ_MESSAGE: `${BASE_URL}/user/readMessage`, // 标记消息为已读
  VIP_INFO: `${BASE_URL}/user/vip_info`,     // 获取用户VIP信息
  ORDERS: `${BASE_URL}/user/orders`,         // 获取用户订单
  CREATE_ORDER: `${BASE_URL}/user/createOrder`, // 创建订单
  CANCEL_ORDER: `${BASE_URL}/user/cancelOrder`, // 取消订单
  LOGIN_LOGS: `${BASE_URL}/user/loginLogs`,   // 获取登录日志
  LOGIN_STATS: `${BASE_URL}/user/loginStats`, // 获取登录统计信息
};


// 前端用户博客相关接口
const BLOG_API = {
  // 网站配置
  BLOG_CONFIG: `${BASE_URL}/admin/getConfig`,      // 获取博客配置

  // 搜索功能
  SEARCH: `${BASE_URL}/search`,                 // 站内搜索

  // 文章管理
  POST_LIST: `${BASE_URL}/post/list`,         // 获取文章列表
  POST_DETAIL: `${BASE_URL}/post/detail`,     // 获取文章详情
  POST_CREATE: `${BASE_URL}/post/create`,     // 创建文章
  POST_UPDATE: `${BASE_URL}/post/update`,     // 更新文章
  POST_DELETE: `${BASE_URL}/post/delete`,     // 删除文章
  POST_TOP: `${BASE_URL}/post/top`,           // 置顶/取消置顶文章
  POST_RECOMMEND: `${BASE_URL}/post/recommend`, // 推荐/取消推荐文章
  POST_BANNER_TOP: `${BASE_URL}/post/bannerTop`, // 首页banner展示/取消首页banner展示
  POST_HOT: `${BASE_URL}/post/hot`, // 获取热门文章
  POST_NEW: `${BASE_URL}/post/new`, // 获取最新文章
  //POST_RECOMMEND: `${BASE_URL}/post/recommend`, // 获取推荐文章

  // 分类管理
  CATEGORY_LIST: `${BASE_URL}/cat/list`,    // 获取分类列表
  CATEGORY_DETAIL: `${BASE_URL}/cat/detail`, // 获取分类详情
  CATEGORY_CREATE: `${BASE_URL}/cat/create`, // 创建分类
  CATEGORY_UPDATE: `${BASE_URL}/cat/update`, // 更新分类
  CATEGORY_DELETE: `${BASE_URL}/cat/delete`, // 删除分类
  GET_CAT_LIST: `${BASE_URL}/cat/getCatList`, // 获取分类列表

  // 标签管理
  TAG_LIST: `${BASE_URL}/tag/list`,           // 获取标签列表
  TAG_CREATE: `${BASE_URL}/tag/create`,       // 创建标签
  TAG_UPDATE: `${BASE_URL}/tag/update`,       // 更新标签
  TAG_DELETE: `${BASE_URL}/tag/delete`,       // 删除标签
  TAG_DETAIL: `${BASE_URL}/tag/detail`,       // 获取标签详情
  TAG_HOT: `${BASE_URL}/tag/hot`,       // 获取热门标签
  TAG_NEW: `${BASE_URL}/tag/new`,       // 获取最新标签


  // 评论管理
  COMMENT_LIST: `${BASE_URL}/comment/list`,     // 获取评论列表
  COMMENT_CREATE: `${BASE_URL}/comment/add`, // 创建评论
  COMMENT_REPLY: `${BASE_URL}/comment/add`,   // 回复评论
  COMMENT_UPDATE: `${BASE_URL}/comment/update`, // 更新评论状态
  COMMENT_DELETE: `${BASE_URL}/comment/delete`, // 删除评论
  COMMENT_NEW: `${BASE_URL}/comment/new`, // 获取最新评论

  // 评论点赞
  COMMENT_LIKE: `${BASE_URL}/commentLike/like`,      // 点赞评论
  COMMENT_UNLIKE: `${BASE_URL}/commentLike/unlike`,  // 取消点赞评论
  COMMENT_LIKE_STATUS: `${BASE_URL}/commentLike/status`, // 获取评论点赞状态

  // 文章收藏
  FAVORITE_ADD: `${BASE_URL}/favorite/add`,          // 添加收藏
  FAVORITE_CANCEL: `${BASE_URL}/favorite/cancel`,    // 取消收藏
  FAVORITE_STATUS: `${BASE_URL}/favorite/status`,    // 获取收藏状态
  FAVORITE_LIST: `${BASE_URL}/favorite/list`,        // 获取收藏列表

  // 点赞管理
  LIKE_LIST: `${BASE_URL}/like/list`,          // 获取点赞列表
  LIKE_CREATE: `${BASE_URL}/like/create`,      // 创建点赞
  LIKE_DELETE: `${BASE_URL}/like/delete`,      // 删除点赞
  LIKE_STATS: `${BASE_URL}/like/stats`,        // 获取点赞统计
  LIKE: `${BASE_URL}/like/like`,      // 点赞
  UNLIKE: `${BASE_URL}/like/unlike`,      // 取消点赞
  LIKE_STATUS: `${BASE_URL}/like/likeStatus`,      // 获取点赞状态
  LIKE_CHECK: `${BASE_URL}/like/check`,      // 检查是否点赞
  GUEST_LIKE_STATUS: `${BASE_URL}/like/guestLikeStatus`,      // 获取访客点赞状态
  POST_LIKE: `${BASE_URL}/like/likePost`,      // 点赞文章
  POST_UNLIKE: `${BASE_URL}/like/unlikePost`,      // 取消点赞文章



  // 文件上传
  UPLOAD_IMAGE: `${BASE_URL}/upload/image`,    // 上传图片
  UPLOAD_FILE: `${BASE_URL}/upload/file`,      // 上传文件
  UPLOAD_LINK_LOGO: `${BASE_URL}/upload/link_logo`,      // 上传友情链接LOGO

  // 统计数据
  BLOG_STATS: `${BASE_URL}/stats`,             // 博客统计数据

  // 访客信息
  GET_IP_INFO: `${BASE_URL}/visitor/ip_info`,   // 获取访客IP信息

    // 友情链接
    LINK_LIST: `${BASE_URL}/link/list`,          // 获取友情链接列表
    LINK_CREATE: `${BASE_URL}/link/create`,      // 创建友情链接
    LINK_UPDATE: `${BASE_URL}/link/update`,      // 更新友情链接
    LINK_DELETE: `${BASE_URL}/link/delete`,      // 删除友情链接
    LINK_UPDATE_SORT: `${BASE_URL}/link/updateSort`,      // 更新友情链接排序

    // 联系表单
    SUBMIT_CONTACT_FORM: `${BASE_URL}/contact/submitForm`,      // 提交联系表单

    // 网站统计
    WEB_STATISTICS: `${BASE_URL}/statistics/webStatistics`,      // 获取网站统计信息

    // 浏览时长统计
    UPDATE_VIEW_DURATION: `${BASE_URL}/post/updateViewDuration`,  // 更新文章浏览时长
    UPDATE_SITE_DURATION: `${BASE_URL}/statistics/updateSiteDuration`,  // 更新网站浏览时长

};

// 防止重复请求的映射表
const pendingRequests = new Map();

/**
 * 生成请求的唯一键
 * @param {string} url 请求地址
 * @param {string} method 请求方法
 * @param {object} params 请求参数
 * @returns {string} 请求的唯一键
 */
const generateRequestKey = (url, method, params) => {
  // 创建参数的副本，以便我们可以安全地修改它
  const processedParams = { ...params };

  // 移除时间戳参数，避免每次请求都被视为新请求
  if (processedParams && processedParams._t) {
    delete processedParams._t;
  }

  // 移除分页参数，不再区分不同页的请求
  if (processedParams) {
    delete processedParams.page;
    delete processedParams.limit;
  }

  // 返回不包含分页信息的键
  return `${method}:${url}:${JSON.stringify(processedParams || {})}`;
};

/**
 * 添加请求到映射表
 * @param {string} key 请求的唯一键
 * @param {AbortController} controller 请求的控制器
 */
const addPendingRequest = (key, controller) => {
  pendingRequests.set(key, controller);
};

/**
 * 移除请求从映射表
 * @param {string} key 请求的唯一键
 */
const removePendingRequest = (key) => {
  pendingRequests.delete(key);
};

/**
 * 取消重复的请求
 * @param {string} key 请求的唯一键
 */
const cancelDuplicateRequest = (key) => {
  // 对于管理员个人资料请求，不取消重复的请求以避免取消问题
  if (key.includes('/admin/info') && key.includes('post:')) {
    return;
  }

  if (pendingRequests.has(key)) {
    const controller = pendingRequests.get(key);
    controller.abort();
    pendingRequests.delete(key);
  }
};

// 管理后台收藏管理接口
const ADMIN_FAVORITE_API = {
  FAVORITE_LIST: `${BASE_URL}/adminFavorite/list`,      // 获取收藏列表
  FAVORITE_DELETE: `${BASE_URL}/adminFavorite/delete`,  // 删除收藏
  FAVORITE_STATS: `${BASE_URL}/adminFavorite/stats`,    // 获取收藏统计
};

// AI工具分类管理接口
const ADMIN_AI_TOOL_CATEGORY_API = {
  LIST: `${BASE_URL}/adminAiToolCategory/list`,          // 获取分类列表
  INFO: `${BASE_URL}/adminAiToolCategory/info`,          // 获取分类详情
  CREATE: `${BASE_URL}/adminAiToolCategory/create`,      // 创建分类
  UPDATE: `${BASE_URL}/adminAiToolCategory/update`,      // 更新分类
  DELETE: `${BASE_URL}/adminAiToolCategory/delete`,      // 删除分类
  UPDATE_STATUS: `${BASE_URL}/adminAiToolCategory/updateStatus`,  // 更新分类状态
};

// AI工具管理接口
const ADMIN_AI_TOOL_API = {
  LIST: `${BASE_URL}/adminAiTool/list`,                  // 获取工具列表
  INFO: `${BASE_URL}/adminAiTool/info`,                  // 获取工具详情
  CREATE: `${BASE_URL}/adminAiTool/create`,              // 创建工具
  UPDATE: `${BASE_URL}/adminAiTool/update`,              // 更新工具
  DELETE: `${BASE_URL}/adminAiTool/delete`,              // 删除工具
  RECOMMEND: `${BASE_URL}/adminAiTool/recommend`,        // 推荐工具
  TOP: `${BASE_URL}/adminAiTool/top`,                    // 置顶工具
  UPDATE_STATUS: `${BASE_URL}/adminAiTool/updateStatus`,  // 更新工具状态
  BATCH: `${BASE_URL}/adminAiTool/batch`,                // 批量操作
};

// AI工具标签管理接口
const ADMIN_AI_TOOL_TAG_API = {
  LIST: `${BASE_URL}/adminAiToolTag/list`,                // 获取标签列表
  ALL: `${BASE_URL}/adminAiToolTag/all`,                 // 获取所有标签
  INFO: `${BASE_URL}/adminAiToolTag/info`,               // 获取标签详情
  CREATE: `${BASE_URL}/adminAiToolTag/create`,           // 创建标签
  UPDATE: `${BASE_URL}/adminAiToolTag/update`,           // 更新标签
  DELETE: `${BASE_URL}/adminAiToolTag/delete`,           // 删除标签
  BATCH_DELETE: `${BASE_URL}/adminAiToolTag/batchDelete`, // 批量删除标签
  MERGE: `${BASE_URL}/adminAiToolTag/merge`,             // 合并标签
  UPDATE_STATUS: `${BASE_URL}/adminAiToolTag/updateStatus`,  // 更新标签显示状态
};

// AI工具收藏管理接口
const ADMIN_AI_TOOL_FAVORITE_API = {
  LIST: `${BASE_URL}/adminAiToolFavorite/list`,           // 获取收藏列表
  INFO: `${BASE_URL}/adminAiToolFavorite/info`,          // 获取收藏详情
  DELETE: `${BASE_URL}/adminAiToolFavorite/delete`,      // 删除收藏
  BATCH_DELETE: `${BASE_URL}/adminAiToolFavorite/batchDelete`, // 批量删除收藏
};

// AI工具点赞管理接口
const ADMIN_AI_TOOL_LIKE_API = {
  LIST: `${BASE_URL}/adminAiToolLike/list`,               // 获取点赞列表
  INFO: `${BASE_URL}/adminAiToolLike/info`,              // 获取点赞详情
  DELETE: `${BASE_URL}/adminAiToolLike/delete`,          // 删除点赞
  BATCH_DELETE: `${BASE_URL}/adminAiToolLike/batchDelete`, // 批量删除点赞
};

// AI工具评论管理接口
const ADMIN_AI_TOOL_COMMENT_API = {
  LIST: `${BASE_URL}/adminAiToolComment/list`,            // 获取评论列表
  INFO: `${BASE_URL}/adminAiToolComment/info`,           // 获取评论详情
  REPLY: `${BASE_URL}/adminAiToolComment/reply`,         // 回复评论
  UPDATE_STATUS: `${BASE_URL}/adminAiToolComment/updateStatus`, // 更新评论状态
  BATCH_UPDATE_STATUS: `${BASE_URL}/adminAiToolComment/batchUpdateStatus`, // 批量更新评论状态
  DELETE: `${BASE_URL}/adminAiToolComment/delete`,      // 删除评论
  BATCH_DELETE: `${BASE_URL}/adminAiToolComment/batchDelete`, // 批量删除评论
};

// AI工具访问日志管理接口
const ADMIN_AI_TOOL_VISIT_LOG_API = {
  LIST: `${BASE_URL}/adminAiToolVisitLog/list`,           // 获取访问日志列表
  INFO: `${BASE_URL}/adminAiToolVisitLog/info`,          // 获取访问日志详情
  STATS: `${BASE_URL}/adminAiToolVisitLog/stats`,        // 获取访问统计数据
  DELETE: `${BASE_URL}/adminAiToolVisitLog/delete`,      // 删除访问日志
  BATCH_DELETE: `${BASE_URL}/adminAiToolVisitLog/batchDelete`, // 批量删除访问日志
  CLEAR: `${BASE_URL}/adminAiToolVisitLog/clear`,        // 清空访问日志
};

// AI工具评价点赞管理接口
const ADMIN_AI_TOOL_COMMENT_LIKE_API = {
  LIST: `${BASE_URL}/adminAiToolCommentLike/list`,           // 获取评价点赞列表
  INFO: `${BASE_URL}/adminAiToolCommentLike/info`,          // 获取评价点赞详情
  DELETE: `${BASE_URL}/adminAiToolCommentLike/delete`,      // 删除评价点赞
  BATCH_DELETE: `${BASE_URL}/adminAiToolCommentLike/batchDelete`, // 批量删除评价点赞
  STATISTICS: `${BASE_URL}/adminAiToolCommentLike/statistics`,    // 获取评价点赞统计数据
};

// 导出所有API配置
export default {
  ENV,
  CURRENT_ENV,
  HOST_URL,
  BASE_URL,
  CORS_CONFIG, // 导出CORS配置
  IP_API,// 导出IP地理位置查询接口
  CONFIG_API,
  USER_API,
  ADMIN_API,
  AUTH_API,
  BLOG_API, // 导出博客接口
  ADMIN_BLOG_API, // 导出管理员博客接口
  ADMIN_PRIVILEGE_API, // 导出管理员特权接口
  ADMIN_FAVORITE_API, // 导出管理员收藏接口
  ADMIN_AI_TOOL_CATEGORY_API, // 导出AI工具分类管理接口
  ADMIN_AI_TOOL_API, // 导出AI工具管理接口
  ADMIN_AI_TOOL_TAG_API, // 导出AI工具标签管理接口
  ADMIN_AI_TOOL_FAVORITE_API, // 导出AI工具收藏管理接口
  ADMIN_AI_TOOL_LIKE_API, // 导出AI工具点赞管理接口
  ADMIN_AI_TOOL_COMMENT_API, // 导出AI工具评论管理接口
  ADMIN_AI_TOOL_COMMENT_LIKE_API, // 导出AI工具评价点赞管理接口
  ADMIN_AI_TOOL_VISIT_LOG_API, // 导出AI工具访问日志管理接口
  // 导出防止重复请求的工具函数
  generateRequestKey,
  addPendingRequest,
  removePendingRequest,
  cancelDuplicateRequest
};