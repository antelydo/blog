/**
 * 后台管理系统路由配置
 */

import { h } from 'vue'
import AdminLayout from '../components/layout/AdminLayout.vue'

export default [
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('../views/admin/AdminLogin.vue'),
    meta: { title: 'adminLogin' }
  },
  {
    path: '/admin',
    component: AdminLayout,
    redirect: '/admin/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/admin/Dashboard.vue'),
        meta: { title: 'dashboard', requiresAuth: true }
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/admin/user/List.vue'),
        meta: { title: 'users', requiresAuth: true }
      },

      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/admin/Settings.vue'),
        meta: { title: 'settings', requiresAuth: true }
      },
      {
        path: 'system-config',
        name: 'SystemConfig',
        component: () => import('../components/admin/SystemConfig.vue'),
        meta: { title: 'systemConfig', requiresAuth: true }
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('../views/admin/Profile.vue'),
        meta: { title: 'profile', requiresAuth: true }
      },
      {
        path: 'activity-log',
        name: 'ActivityLog',
        component: () => import('../views/admin/ActivityLog.vue'),
        meta: { title: 'activityLog', requiresAuth: true }
      },
      // 文章管理路由
      {
        path: 'article',
        name: 'ArticleManagement',
        component: () => import('../views/admin/article/index.vue'),
        meta: { title: 'articleManagement', requiresAuth: true },
        redirect: '/admin/article/list',
        children: [
          {
            path: 'list',
            name: 'ArticleList',
            component: () => import('../views/admin/article/List.vue'),
            meta: { title: 'articleList', requiresAuth: true }
          },
          {
            path: 'create',
            name: 'ArticleCreate',
            component: () => import('../views/admin/article/Edit.vue'),
            meta: { title: 'articleCreate', requiresAuth: true }
          },
          {
            path: 'edit/:id',
            name: 'ArticleEdit',
            component: () => import('../views/admin/article/Edit.vue'),
            meta: { title: 'articleEdit', requiresAuth: true }
          }
        ]
      },
      // 分类管理路由
      {
        path: 'category',
        name: 'CategoryList',
        component: () => import('../views/admin/article/Category.vue'),
        meta: { title: 'categoryManagement', requiresAuth: true }
      },
      // 标签管理路由
      {
        path: 'tag',
        name: 'TagList',
        component: () => import('../views/admin/article/Tag.vue'),
        meta: { title: 'tagManagement', requiresAuth: true }
      },
      // 评论管理路由
      {
        path: 'comment',
        name: 'CommentList',
        component: () => import('../views/admin/article/Comment.vue'),
        meta: { title: 'commentManagement', requiresAuth: true }
      },
      // 点赞列表路由
      {
        path: 'like',
        name: 'LikeList',
        component: () => import('../views/admin/article/Like.vue'),
        meta: { title: 'likeManagement', requiresAuth: true }
      },
      // 收藏列表路由
      {
        path: 'favorite',
        name: 'FavoriteList',
        component: () => import('../views/admin/article/Favorite.vue'),
        meta: { title: 'favoriteManagement', requiresAuth: true }
      },

      // AI工具管理路由 - 平级路由
      {
        path: 'aiTool-list',
        name: 'AiToolList',
        component: () => import('../views/admin/aiTool/List.vue'),
        meta: { title: 'aiToolList', requiresAuth: true }
      },
      {
        path: 'aiTool-category',
        name: 'AiToolCategory',
        component: () => import('../views/admin/aiTool/Category.vue'),
        meta: { title: 'aiToolCategoryManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-create',
        name: 'AiToolCreate',
        component: () => import('../views/admin/aiTool/Edit.vue'),
        meta: { title: 'aiToolCreate', requiresAuth: true }
      },
      {
        path: 'aiTool-edit/:id',
        name: 'AiToolEdit',
        component: () => import('../views/admin/aiTool/Edit.vue'),
        meta: { title: 'aiToolEdit', requiresAuth: true }
      },
      {
        path: 'aiTool-tag',
        name: 'AiToolTag',
        component: () => import('../views/admin/aiTool/Tag.vue'),
        meta: { title: 'aiToolTagManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-favorite',
        name: 'AiToolFavorite',
        component: () => import('../views/admin/aiTool/Favorite.vue'),
        meta: { title: 'aiToolFavoriteManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-like',
        name: 'AiToolLike',
        component: () => import('../views/admin/aiTool/Like.vue'),
        meta: { title: 'aiToolLikeManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-comment',
        name: 'AiToolComment',
        component: () => import('../views/admin/aiTool/Comment.vue'),
        meta: { title: 'aiToolCommentManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-visit-log',
        name: 'AiToolVisitLog',
        component: () => import('../views/admin/aiTool/VisitLog.vue'),
        meta: { title: 'aiToolVisitLogManagement', requiresAuth: true }
      },
      {
        path: 'aiTool-comment-like',
        name: 'AiToolCommentLike',
        component: () => import('../views/admin/aiTool/CommentLike.vue'),
        meta: { title: 'aiToolCommentLikeManagement', requiresAuth: true }
      },
      // 友情链接管理路由
      {
        path: 'link',
        name: 'LinkManagement',
        component: () => import('../views/admin/link/LinkManagement.vue'),
        meta: { title: 'linkManagement', requiresAuth: true }
      },
      // 联系表单管理路由
      {
        path: 'contact/messages',
        name: 'ContactMessages',
        component: () => import('../views/admin/contact/messages.vue'),
        meta: { title: 'contactMessages', requiresAuth: true }
      },
      // 浏览时长统计路由
      {
        path: 'view-duration-stats',
        name: 'ViewDurationStats',
        component: () => import('../views/admin/ViewDurationStats.vue'),
        meta: { title: 'viewDurationStats', requiresAuth: true }
      },
      // IP地理位置查询工具路由
      {
        path: 'ipLocation',
        name: 'IpLocation',
        component: () => import('../views/admin/tools/IpLocation.vue'),
        meta: { title: 'ipLocation', requiresAuth: true }
      }
    ]
  }
]