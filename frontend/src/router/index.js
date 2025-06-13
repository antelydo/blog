import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue';
import Register from '@/views/Register.vue';
import ForgotPassword from '@/views/ForgotPassword.vue';
import NotFound from '@/views/NotFound.vue';
import Forbidden from '@/views/Forbidden.vue';
import ServerError from '@/views/ServerError.vue';
import TermsOfService from '@/views/TermsOfService.vue';
import PrivacyPolicy from '@/views/PrivacyPolicy.vue';
import adminRoutes from './admin';
import HomePage from '@/views/index/HomePage.vue';
import CategoryPage from '@/views/index/CategoryPage.vue';
import PostPage from '@/views/index/PostPage.vue';
import LinksPage from '@/views/index/LinksPage.vue';
import AboutPage from '@/views/index/AboutPage.vue';
import ContactPage from '@/views/index/ContactPage.vue';
import TagPage from '@/views/index/TagPage.vue';
import SearchPage from '@/views/index/SearchPage.vue';
import TagCloudPage from '@/views/index/TagCloudPage.vue';
import CategoryCloud from '@/views/index/CategoryCloud.vue';
import PersonalCenter from '@/views/index/PersonalCenter.vue';
import Sitemap from '@/views/index/Sitemap.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomePage,
    meta: {
      title: '首页',
      requireAuth: false
    }
  },
  {
    path: '/category/:category?',
    name: 'Category',
    component: CategoryPage,
    meta: {
      title: '分类',
      requireAuth: false
    }
  },
  {
    path: '/tag/:tag?',
    name: 'tag',
    component: TagPage,
    meta: {
      title: '标签',
      requireAuth: false
    }
  },
  {
    path: '/post/:id',
    name: 'Post',
    component: PostPage,
    meta: {
      title: '文章',
      requireAuth: false
    }
  },
  {
    path: '/search',
    name: 'Search',
    component: SearchPage,
    meta: {
      title: '搜索结果',
      requireAuth: false
    }
  },
  {
    path: '/tag_cloud',
    name: 'TagCloud',
    component: TagCloudPage,
    meta: {
      title: '标签云',
      requireAuth: false
    }
  },
  {
    path: '/category_cloud',
    name: 'CategoryCloud',
    component: CategoryCloud,
    meta: {
      title: 'categoryCloud',
      requireAuth: false
    }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      title: 'login'
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: {
      title: 'register'
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: {
      title: 'forgotPassword'
    }
  },
  {
    path: '/forbidden',
    name: 'Forbidden',
    component: Forbidden,
    meta: {
      title: 'forbidden'
    }
  },
  {
    path: '/403',
    name: 'forbidden',
    component: Forbidden,
    meta: {
      requireAuth: false,
      title: 'forbidden'
    }
  },
  {
    path: '/500',
    name: 'server-error',
    component: ServerError,
    meta: {
      requireAuth: false,
      title: 'serverError'
    }
  },
  {
    path: '/terms-of-service',
    name: 'TermsOfService',
    component: TermsOfService,
    meta: {
      title: 'termsOfService',
      requireAuth: false
    }
  },
  {
    path: '/privacy-policy',
    name: 'PrivacyPolicy',
    component: PrivacyPolicy,
    meta: {
      title: 'privacyPolicy',
      requireAuth: false
    }
  },
  {
    path: '/links',
    name: 'Links',
    component: LinksPage,
    meta: {
      title: 'friendlyLinks',
      requireAuth: false
    }
  },
  {
    path: '/about',
    name: 'About',
    component: AboutPage,
    meta: {
      title: 'aboutUs',
      requireAuth: false
    }
  },
  {
    path: '/contact',
    name: 'Contact',
    component: ContactPage,
    meta: {
      title: '联系我们',
      requireAuth: false
    }
  },
  {
    path: '/personal-center',
    name: 'PersonalCenter',
    component: PersonalCenter,
    meta: {
      title: '个人中心',
      requireAuth: true
    }
  },
  {
    path: '/sitemap',
    name: 'Sitemap',
    component: Sitemap,
    meta: {
      title: 'sitemap',
      requireAuth: false
    }
  },
  // 后台管理路由
  ...adminRoutes,
  // Catch-all 404 route
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: {
      requireAuth: false,
      title: 'notFound'
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// 设置全局标题
const setDocumentTitle = (title) => {
  document.title = title;
  console.log('[Debug] 设置页面标题:', title);
}

// 路由守卫
router.beforeEach((to, from, next) => {
  // 检查是否是管理员路由
  const isAdminRoute = to.path.startsWith('/admin') && to.path !== '/admin/login';

  // 统一处理登录页面直接放行
  if (to.path === '/admin/login' || to.path === '/login') {
    return next();
  }

  // 获取token并检查其有效性
  let token = null;
  let hasValidToken = false;

  if (isAdminRoute) {
    // 管理员路由使用admin_token
    token = localStorage.getItem('admin_token');
    if (token) {
      // 如果token格式不正确，尝试修复
      const tokenWasFixed = !token.startsWith('Bearer ');
      if (tokenWasFixed) {
        token = `Bearer ${token}`;
        // 更新localStorage中的token
        localStorage.setItem('admin_token', token);
      }
      hasValidToken = true;
    }
  } else {
    // 普通路由使用token
    token = localStorage.getItem('token');
    hasValidToken = !!token;
  }

  // 添加调试日志
  const tokenInfo = () => ({
    path: to.path,
    isAdminRoute,
    hasToken: hasValidToken,
    tokenPrefix: token ? token.substring(0, 30) + '...' : null,
    tokenLength: token ? token.length : 0,
    requiresAuth: to.matched.some(record => record.meta.requiresAuth),
    newTokenStart: token ? token.substring(0, 30) + '...' : null
  });

  // 权限检查
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!hasValidToken) {
      // 添加调试日志
      // 根据路由类型跳转到对应的登录页
      next(isAdminRoute ? '/admin/login' : '/login');
      return;
    }
  }

  // Handle errors from API responses or other issues
  if (to.query.error) {
    const errorCode = parseInt(to.query.error);
    if (errorCode === 403) {
      return next({ name: 'forbidden' });
    } else if (errorCode === 500) {
      return next({ name: 'server-error' });
    }
  }

  // 设置页面标题
  // 1. 首先从路由meta.title获取当前页面标题
  const pageTitle = to.meta.title;

  // 2. 尝试获取Pinia store中的配置，动态生成完整标题
  if (window.getPageTitle && pageTitle) {
    // 使用configStore的getPageTitle方法，它会处理国际化
    try {
      const title = window.getPageTitle(pageTitle);
      setDocumentTitle(title);

      // 添加一个事件监听器，在语言变更时更新标题
      window.addEventListener('language-changed', () => {
        const updatedTitle = window.getPageTitle(pageTitle);
        setDocumentTitle(updatedTitle);
      }, { once: true });
    } catch (error) {
      console.error('设置页面标题时出错:', error);
      setDocumentTitle(pageTitle || '后台管理系统');
    }
  } else {
    // 如果还没有初始化store，则仅使用页面标题
    setDocumentTitle(pageTitle || '后台管理系统');

    // 等待store初始化后再次尝试更新标题
    window.addEventListener('config-store-ready', () => {
      if (window.getPageTitle && pageTitle) {
        setDocumentTitle(window.getPageTitle(pageTitle));
      }
    }, { once: true });
  }

  // 没有特殊限制，允许访问
  next();
});

export default router;