<!-- Header component -->
<template>
  <!-- 预渲染骨架屏 - 在内容加载前显示 -->
  <div v-if="isLoading" class="header-skeleton">
    <div class="skeleton-top-bar"></div>
    <div class="skeleton-main-header">
      <div class="container">
        <div class="skeleton-branding">
          <div class="skeleton-logo"></div>
          <div class="skeleton-text">
            <div class="skeleton-title"></div>
            <div class="skeleton-description"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="skeleton-navigation"></div>
  </div>

  <!-- 实际内容 -->
  <header class="site-header" :class="{ 'sticky': isSticky }" v-show="!isLoading">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <!-- Top bar -->
    <div class="top-bar">
      <div class="container">
        <div class="top-bar-content">
          <ul class="top-nav">
            <template v-if="isLoggedIn && userInfo">
              <li>
                <router-link to="/personal-center" class="user-link" :title="t('header.topNav.greeting') + (userInfo.nickname || userInfo.username)">
                  {{ t('header.topNav.greeting') }}{{ userInfo.nickname || userInfo.username }}
                </router-link>
              </li>
              <li>
                <router-link to="/personal-center" class="dashboard-link" :title="t('header.topNav.personalCenter')">{{ t('header.topNav.personalCenter') }}</router-link>
              </li>
              <li>
                <a href="javascript:void(0)" class="logout-link" @click="handleLogout" :title="t('header.topNav.logout')">{{ t('header.topNav.logout') }}</a>
              </li>
            </template>
            <template v-else>
              <li><router-link to="/login" class="login-link" :title="t('header.topNav.pleaseLogin')">{{ t('header.topNav.pleaseLogin') }}</router-link></li>
              <li><router-link to="/register" class="register-link" :title="t('header.topNav.register')">{{ t('header.topNav.register') }}</router-link></li>
              <li><router-link to="/forgot-password" class="forgot-link" :title="t('header.topNav.forgotPassword')">{{ t('header.topNav.forgotPassword') }}</router-link></li>
            </template>
          </ul>
          <div class="user-panel">
            <router-link to="/category_cloud" class="category-cloud-link" :title="t('header.userPanel.categoryArchive')">{{ t('header.userPanel.categoryArchive') }}</router-link>
            <router-link to="/tag_cloud" class="tag-cloud-link" :title="t('header.userPanel.tagCloud')">{{ t('header.userPanel.tagCloud') }}</router-link>
            <router-link to="/links" :title="t('header.userPanel.friendlyLinks')">{{ t('header.userPanel.friendlyLinks') }}</router-link>
            <router-link to="/about" :title="t('header.userPanel.aboutSite')">{{ t('header.userPanel.aboutSite') }}</router-link>
            <router-link to="/contact">{{ t('header.userPanel.contactUs') }}</router-link>
            <!-- 主题控制组件 -->
            <div class="header-controls-wrapper">
              <header-controls />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Logo and main navigation area -->
    <div class="main-header">
      <div class="container main-header-container">
        <div class="header-flex">
          <!-- Logo and site description -->
          <div class="site-branding">
            <div class="branding-flex">
              <router-link to="/" class="site-logo">
                <img :src="siteLogo || '/assets/images/logo.png'" :alt="siteName" class="logo-img" />
              </router-link>
              <div class="branding-text">
                <h1 class="site-title">
                  <router-link to="/">{{ siteName }}</router-link>
                </h1>
                <p class="site-description">{{ siteDescription }}</p>
              </div>
            </div>
          </div>

          <!-- Mobile nav toggle - 不再显示 -->
          <!-- 移除移动导航按钮 -->
        </div>
      </div>
    </div>

    <!-- Main navigation -->
    <nav class="main-navigation" :class="{ 'mobile-active': mobileMenuActive, 'nav-sticky': navSticky }">
      <!-- 明确的关闭按钮 -->
      <button class="mobile-menu-close"
        @click.stop.prevent="closeMobileMenu"
        aria-label="关闭菜单"></button>
      <div class="container">
        <div class="site-nav-wrap" :class="{ 'open': isMobileMenuOpen }">
          <ul class="nav-menu">
            <li v-for="(item, index) in navItems" :key="index"
                :class="{
                  'menu-item-has-children': item.children && item.children.length > 0,
                  'active': activeMenuItems.includes(item.path)
                }">
              <a :href="item.path" :title="item.label">{{ item.label }}</a>
              <!-- First level dropdown toggle for mobile -->
              <button type="button" class="dropdown-toggle"
                v-if="item.children && item.children.length > 0"
                @click.stop.prevent="toggleSubMenu(item.path)"
                :class="{'active': activeSubMenus.includes(item.path)}"
                aria-label="展开子菜单">
                <i class="fa fa-angle-down"></i>
              </button>
              <ul class="sub-menu" v-if="item.children && item.children.length > 0" :class="{'open': activeSubMenus.includes(item.path), 'active': activeSubMenus.includes(item.path)}">
                <li v-for="(child, childIndex) in item.children" :key="childIndex"
                    :class="{
                      'menu-item-has-children': child.children && child.children.length > 0,
                      'active': activeMenuItems.includes(child.path)
                    }">
                  <a :href="child.path">{{ child.label }}</a>
                  <!-- Second level dropdown toggle for mobile -->
                  <button type="button" class="dropdown-toggle"
                    v-if="child.children && child.children.length > 0"
                    @click.stop.prevent="toggleSubMenu(child.path)"
                    :class="{'active': activeSubMenus.includes(child.path)}"
                    aria-label="展开子菜单">
                    <i class="fa fa-angle-down"></i>
                  </button>
                  <!-- Third level menus -->
                  <ul class="sub-menu" v-if="child.children && child.children.length > 0" :class="{'open': activeSubMenus.includes(child.path), 'active': activeSubMenus.includes(child.path)}">
                    <li v-for="(grandChild, grandChildIndex) in child.children" :key="grandChildIndex"
                        :class="{ 'active': activeMenuItems.includes(grandChild.path) }">
                      <a :href="grandChild.path">{{ grandChild.label }}</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        <div class="nav-search desktop-only">
          <form @submit.prevent="searchSite" class="search-form-nav">
            <input type="text" v-model="searchQueryNav" :placeholder="t('header.search.placeholder')">
            <button type="submit" class="search-button">
              <el-icon class="search-icon"><Search /></el-icon>
            </button>
          </form>
        </div>
        <!-- 移动端搜索按钮 -->
        <div class="mobile-search-button mobile-only">
          <button type="button" @click.stop.prevent="toggleMobileSearch" aria-label="搜索">
            <i class="fa fa-search"></i>
          </button>
        </div>
      </div>
    </nav>

    <!-- Mobile search panel -->
    <div class="mobile-search-panel" :class="{ 'active': mobileSearchActive }">
      <div class="container">
        <form @submit.prevent="searchSite" class="search-form-mobile">
          <input type="text" v-model="searchQueryMobile" :placeholder="t('header.search.mobileSearchPlaceholder')">
          <button type="submit">
            <el-icon class="search-icon-mobile"><Search /></el-icon>
          </button>
          <button type="button" class="close-search" @click="toggleMobileSearch">
            <el-icon><Close /></el-icon>
          </button>
        </form>
      </div>
    </div>

    <!-- Mobile menu mask -->
    <div class="mobile-mask"
      :class="{ 'active': mobileMenuActive }"
      @click.stop.prevent="closeMobileMenu"
      role="button"
      tabindex="0"
      aria-label="关闭菜单"></div>

  </header>
</template>

<script>
import { Search, Close } from '@element-plus/icons-vue'
import api, { apiUrls } from '../../../api'
import { useBlogConfigStore } from '../../../stores/blogConfig'
import { useUserStore } from '../../../stores/user'
import { useI18n } from 'vue-i18n'
import HeaderControls from '../../../components/theme/HeaderControls.vue'
// 导入现代化导航栏样式
import '../assets/navbar-modern.css'
import '../assets/navbar-modern-mobile.css'
// Temporarily remove HeaderSkeleton import to avoid the error

export default {
  name: 'SiteHeader',
  components: {
    HeaderControls,
    // HeaderSkeleton temporarily removed
    Search,
    Close
  },
  setup() {
    const { t } = useI18n();
    return { t };
  },
  data() {
    return {
      mobileMenuActive: false,
      mobileSearchActive: false,
      searchQueryNav: '',
      searchQueryMobile: '',
      activeSubMenus: [],
      isSticky: false,
      lastScrollPosition: 0,
      navSticky: false,
      activeMenuItems: [],
      isMobileMenuOpen: false,
      navItems: [
        { path: '/', label: this.t('header.navMenu.home') }
      ],
      isLoading: true, // 默认显示骨架屏，避免内容闪烁
      isLoggedIn: false,
      userInfo: null,
      // 预加载的配置数据
      preloadedConfig: null,
      // 预加载的分类数据
      preloadedCategories: null,
      // 资源加载状态
      resourcesLoaded: false
    }
  },
  computed: {
    // Get site info from blog config store
    blogConfig() {
      return useBlogConfigStore()
    },
    siteName() {
      return this.blogConfig.siteName
    },
    siteDescription() {
      return this.blogConfig.siteDescription || '/assets/images/favicon.svg';
    },
    siteFavicon() {
      return this.blogConfig.siteFavicon || null
    },
    siteLogo() {
      return this.blogConfig.siteLogo || null
    },
    // 移除暗黑模式判断
  },
  created() {
    // 立即尝试从本地存储加载预缓存数据
    this.loadPreloadedData();

    // 立即预加载LOGO图片
    this.preloadLogo();

    // 异步加载完整数据
    this.loadFullData();
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll)
    window.addEventListener('resize', this.handleResize)

    // Set active menu items based on current route
    this.updateActiveMenuItem()
    this.handleResize()

    // 设置网站图标，防止显示Vue默认图标
    if (this.siteFavicon) {
      // 移除所有现有的图标链接
      const existingIcons = document.querySelectorAll("link[rel*='icon']")
      existingIcons.forEach(icon => {
        icon.parentNode.removeChild(icon)
      })

      // 生成随机字符串，防止缓存
      const randomString = Math.random().toString(36).substring(2, 15)

      // 创建新的图标链接
      const link = document.createElement('link')
      link.type = 'image/x-icon'
      link.rel = 'shortcut icon'
      link.href = `${this.siteFavicon}?v=${randomString}`
      document.head.appendChild(link)
    }

    // Add FontAwesome
    this.loadFontAwesome()

    // Listen for route changes to update active menu items
    this.$router.afterEach(() => {
      // 只更新活动菜单项，不重新加载整个头部
      this.updateActiveMenuItem();

      // 确保导航栏在路由切换时保持可见
      this.isLoading = false;

      // 在路由切换后，预加载下一个页面可能需要的资源
      this.preloadNextPageResources();
    })

    // 添加导航结构化数据
    this.addStructuredData()

    // 检查资源是否已加载完成
    this.checkResourcesLoaded();
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll)
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    $route(to) {
      // Update active items when route changes
      this.updateActiveMenuItem()

      // Handle mobile menu if needed
      if (window.innerWidth <= 768) {
        this.closeMobileMenu()
      }
    },
    // 监听图标变化
    siteFavicon: {
      handler(newFavicon) {
        if (newFavicon) {
          // 移除所有现有的图标链接
          const existingIcons = document.querySelectorAll("link[rel*='icon']")
          existingIcons.forEach(icon => {
            icon.parentNode.removeChild(icon)
          })

          // 生成随机字符串，防止缓存
          const randomString = Math.random().toString(36).substring(2, 15)

          // 创建新的图标链接
          const link = document.createElement('link')
          link.type = 'image/x-icon'
          link.rel = 'shortcut icon'
          link.href = `${newFavicon}?v=${randomString}`
          document.head.appendChild(link)
        }
      },
      immediate: true
    },
    activeMenuItems: {
      handler(newActiveItems) {
        // Open corresponding submenus when active items change
        if (newActiveItems.length > 0) {
          // Extract parent paths from active items that have slashes
          newActiveItems.forEach(item => {
            if (item.includes('/')) {
              const segments = item.split('/')
              let currentPath = ''

              // Build up path segments and add them to activeSubMenus
              for (let i = 1; i < segments.length; i++) {
                if (segments[i]) {
                  currentPath += '/' + segments[i]
                  if (!this.activeSubMenus.includes(currentPath) &&
                      this.hasSubmenu(currentPath)) {
                    this.activeSubMenus.push(currentPath)
                  }
                }
              }
            }
          })
        }
      },
      deep: true
    }
  },
  methods: {
    // 从本地存储加载预缓存数据
    loadPreloadedData() {
      // 尝试从localStorage获取缓存的配置
      try {
        const cachedConfig = localStorage.getItem('website_config');
        if (cachedConfig) {
          this.preloadedConfig = JSON.parse(cachedConfig);

          // 立即应用预加载的配置
          if (this.preloadedConfig.site_name) {
            document.title = this.preloadedConfig.site_title || this.preloadedConfig.site_name;
          }
        }
      } catch (e) {
        console.warn('解析缓存的网站配置失败:', e);
      }

      // 尝试从sessionStorage获取缓存的分类数据
      try {
        const cachedCategories = sessionStorage.getItem('nav_categories');
        if (cachedCategories) {
          this.preloadedCategories = JSON.parse(cachedCategories);

          // 立即应用预加载的分类数据
          if (this.preloadedCategories && this.preloadedCategories.length > 0) {
            // 保留首页导航项
            const homeNavItem = { path: '/', label: this.t('header.navMenu.home') };

            // 转换缓存的分类数据为导航菜单格式
            const categoryNavItems = this.transformCategoryToNavItems(this.preloadedCategories);

            // 设置导航菜单项
            this.navItems = [homeNavItem, ...categoryNavItems];

            // 更新活动菜单项
            this.updateActiveMenuItem();
          }
        }
      } catch (e) {
        console.warn('解析缓存的分类数据失败:', e);
      }
    },

    // 预加载LOGO图片
    preloadLogo() {
      if (this.preloadedConfig && (this.preloadedConfig.site_logo || this.preloadedConfig.Favicon)) {
        const logoUrl = this.preloadedConfig.site_logo || '/assets/images/logo.png';
        const img = new Image();
        img.onload = () => {
          this.resourcesLoaded = true;
          this.checkResourcesLoaded();
        };
        img.onerror = () => {
          this.resourcesLoaded = true;
          this.checkResourcesLoaded();
        };
        img.src = logoUrl;
      } else {
        // 如果没有预加载的配置，标记资源已加载
        this.resourcesLoaded = true;
        this.checkResourcesLoaded();
      }
    },

    // 异步加载完整数据
    async loadFullData() {
      try {
        // 并行加载博客配置和分类导航
        await Promise.all([
          this.initBlogConfig(),
          this.initCategoryNav()
        ]);

        // 检查用户登录状态
        this.checkLoginStatus();

        // 标记加载完成
        this.isLoading = false;
      } catch (error) {
        console.error('加载完整数据时出错:', error);
        // 确保即使出错也不会阻止显示
        this.isLoading = false;
      }
    },

    // 检查资源是否已加载完成
    checkResourcesLoaded() {
      // 如果资源已加载且有预加载数据，可以提前显示内容
      if (this.resourcesLoaded && (this.preloadedConfig || this.preloadedCategories)) {
        // 设置短暂延迟，确保DOM已更新
        setTimeout(() => {
          this.isLoading = false;
        }, 50);
      }
    },

    // 初始化博客配置
    async initBlogConfig() {
      try {
        // 检查博客配置是否已初始化
        if (this.blogConfig.initialized) {
          // 配置已加载，无需重新加载
          return;
        }

        // 加载最新的博客配置
        await this.blogConfig.fetchConfig();
      } catch (error) {
        console.error('获取博客配置失败:', error);
      }
    },

    async initCategoryNav() {
      try {
        // 尝试从sessionStorage获取缓存的分类数据
        const cachedCategories = sessionStorage.getItem('nav_categories');

        if (cachedCategories) {
          // 使用缓存数据
          const categories = JSON.parse(cachedCategories);

          // 保留首页导航项
          const homeNavItem = { path: '/', label: this.t('header.navMenu.home') };

          // 转换缓存的分类数据为导航菜单格式
          const categoryNavItems = this.transformCategoryToNavItems(categories);

          // 设置导航菜单项
          this.navItems = [homeNavItem, ...categoryNavItems];

          // 更新活动菜单项
          this.updateActiveMenuItem();

          // 在后台刷新分类数据，不阻塞UI显示
          this.refreshCategoriesInBackground();
        } else {
          // 没有缓存，直接从API获取
          await this.fetchCategoriesFromAPI();
        }
      } catch (error) {
        console.error('加载分类导航失败:', error);
        this.addFallbackCategories();
        this.isLoading = false;
      }
    },

    // 后台刷新分类数据
    async refreshCategoriesInBackground() {
      try {
        const response = await api.get(apiUrls.BLOG_API.CATEGORY_LIST);

        if (response && response.code === 200) {
          // 缓存新的分类数据
          sessionStorage.setItem('nav_categories', JSON.stringify(response.data));

          // 如果新数据与当前显示的不同，更新导航
          if (JSON.stringify(this.navItems.slice(1)) !== JSON.stringify(this.transformCategoryToNavItems(response.data))) {
            // 保留首页导航项
            const homeNavItem = { path: '/', label: this.t('header.navMenu.home') };

            // 转换API返回的分类数据为导航菜单格式
            const categoryNavItems = this.transformCategoryToNavItems(response.data);

            // 设置导航菜单项
            this.navItems = [homeNavItem, ...categoryNavItems];

            // 更新活动菜单项
            this.updateActiveMenuItem();
          }
        }
      } catch (error) {
        console.warn('后台刷新分类数据失败:', error);
      }
    },

    // 从API获取分类数据
    async fetchCategoriesFromAPI() {
      try {
        const response = await api.get(apiUrls.BLOG_API.CATEGORY_LIST);

        if (response && response.code === 200) {
          // 缓存分类数据
          sessionStorage.setItem('nav_categories', JSON.stringify(response.data));

          // 保留首页导航项
          const homeNavItem = { path: '/', label: this.t('header.navMenu.home') };

          // 转换API返回的分类数据为导航菜单格式
          const categoryNavItems = this.transformCategoryToNavItems(response.data);

          // 设置导航菜单项
          this.navItems = [homeNavItem, ...categoryNavItems];

          // 更新活动菜单项
          this.updateActiveMenuItem();
        } else {
          this.addFallbackCategories();
        }
      } catch (error) {
        console.error('从API获取分类数据失败:', error);
        this.addFallbackCategories();
      } finally {
        this.isLoading = false;
      }
    },

    hasSubmenu(path) {
      // Check if the given path has a submenu
      let found = false
      const checkItems = (items, currentPath) => {
        for (const item of items) {
          if (item.path === path && item.children && item.children.length > 0) {
            found = true
            return
          }
          if (item.children && item.children.length > 0) {
            checkItems(item.children, path)
          }
        }
      }

      checkItems(this.navItems, path)
      return found
    },

    addFallbackCategories() {
      // 添加备用分类以防API调用失败
      this.navItems = [
        { path: '/', label: this.t('header.navMenu.home') },
        { path: '/category/1', label: '新闻' },
        { path: '/category/2', label: '科技' },
        { path: '/category/3', label: '文化', children: [
          { path: '/category/31', label: '艺术' },
          { path: '/category/32', label: '历史' }
        ]},
        { path: '/category/4', label: '生活' }
      ]
    },

    transformCategoryToNavItems(categories) {
      // 将分类数据转换为导航菜单格式
      return categories.map(category => {
        const navItem = {
          path: `/category/${category.id}`,
          label: category.name
        }

        // 如果有子分类，递归处理
        if (category.children && category.children.length > 0) {
          navItem.children = this.transformCategoryToNavItems(category.children)
        }

        return navItem
      })
    },

    loadFontAwesome() {
      // Font Awesome已通过CDN加载，无需再次加载
      console.log('Font Awesome已通过CDN加载');
    },

    // 预加载下一个页面可能需要的资源
    preloadNextPageResources() {
      // 获取当前路由
      const currentPath = this.$route.path;

      // 预加载可能的下一个页面资源
      // 例如，如果在首页，预加载分类页面资源
      if (currentPath === '/') {
        // 预加载分类页面资源并立即使用它，避免预加载警告
        this.preloadAndUseImage('/assets/images/category-banner.jpg');
      }
      // 如果在分类页面，预加载文章详情页资源
      else if (currentPath.startsWith('/category/')) {
        // 预加载文章详情页资源并立即使用它，避免预加载警告
        this.preloadAndUseImage('/assets/images/post-banner.jpg');
      }

      // Font Awesome 已通过 CDN 加载，无需预加载
    },

    // 预加载资源
    preloadResource(url, type) {
      if (!url) return;

      // 检查资源是否已经预加载
      const existingPreload = document.querySelector(`link[rel="preload"][href="${url}"]`);
      if (existingPreload) return;

      // 创建预加载链接
      const link = document.createElement('link');
      link.rel = 'preload';
      link.href = url;

      // 设置资源类型
      if (type === 'image') {
        link.as = 'image';
      } else if (type === 'style') {
        link.as = 'style';
      } else if (type === 'script') {
        link.as = 'script';
      }

      // 添加到文档头部
      document.head.appendChild(link);
    },

    // 预加载并立即使用图片，避免预加载警告
    preloadAndUseImage(url) {
      if (!url) return;

      // 先预加载图片
      this.preloadResource(url, 'image');

      // 然后立即创建一个隐藏的img元素来使用这个图片
      // 这样可以避免"预加载但未使用"的警告
      const img = new Image();
      img.src = url;
      img.style.position = 'absolute';
      img.style.width = '1px';
      img.style.height = '1px';
      img.style.opacity = '0.01';
      img.style.pointerEvents = 'none';
      img.style.left = '-9999px';
      img.style.top = '-9999px';

      // 图片加载完成后移除它
      img.onload = () => {
        console.log(`预加载图片已使用: ${url}`);
        // 延迟移除，确保浏览器有足够时间识别图片已被使用
        setTimeout(() => {
          if (img.parentNode) {
            img.parentNode.removeChild(img);
          }
        }, 5000);
      };

      // 添加到DOM中
      document.body.appendChild(img);
    },
    toggleMobileMenu() {
      this.mobileMenuActive = !this.mobileMenuActive;
      this.isMobileMenuOpen = this.mobileMenuActive; // 同步两个状态

      if (this.mobileMenuActive) {
        document.body.classList.add('menu-open');

        // 添加事件监听器，点击ESC键关闭菜单
        document.addEventListener('keydown', this.handleEscKey);

        // 防止页面滚动
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';

        // 确保导航栏在视口内
        const navElement = document.querySelector('.main-navigation');
        if (navElement) {
          // 重置任何可能的transform，确保使用right属性定位
          navElement.style.transform = 'none';
          // 确保导航栏在视口内
          navElement.style.right = '0';

          // 添加触摸滑动关闭功能
          this.setupTouchEvents(navElement);
        }

        // 显示遮罩层
        const maskElement = document.querySelector('.mobile-mask');
        if (maskElement) {
          maskElement.style.display = 'block';
          setTimeout(() => {
            maskElement.style.opacity = '1';
          }, 10);
        }
      } else {
        document.body.classList.remove('menu-open');
        // 关闭所有子菜单
        this.activeSubMenus = [];

        // 移除ESC键事件监听器
        document.removeEventListener('keydown', this.handleEscKey);

        // 恢复页面滚动
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';

        // 将导航栏移出视口
        const navElement = document.querySelector('.main-navigation');
        if (navElement) {
          navElement.style.right = '-100%';

          // 移除触摸事件
          this.removeTouchEvents(navElement);
        }

        // 隐藏遮罩层
        const maskElement = document.querySelector('.mobile-mask');
        if (maskElement) {
          maskElement.style.opacity = '0';
          setTimeout(() => {
            maskElement.style.display = 'none';
          }, 300);
        }
      }

      // 强制重新计算布局，解决某些浏览器中的渲染问题
      window.setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 50);
    },

    // 设置触摸滑动事件
    setupTouchEvents(element) {
      if (!element) return;

      let startX, startY, distX, distY;
      const threshold = 100; // 滑动阈值

      const touchStart = (e) => {
        const touch = e.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
      };

      const touchMove = (e) => {
        if (!startX || !startY) return;

        const touch = e.touches[0];
        distX = touch.clientX - startX;
        distY = touch.clientY - startY;

        // 如果是向右滑动且水平滑动距离大于垂直滑动距离
        if (distX > 0 && Math.abs(distX) > Math.abs(distY)) {
          e.preventDefault(); // 阻止默认滚动
        }
      };

      const touchEnd = (e) => {
        if (distX > threshold) {
          // 向右滑动超过阈值，关闭菜单
          this.closeMobileMenu();
        }

        // 重置
        startX = startY = distX = distY = null;
      };

      element.addEventListener('touchstart', touchStart, { passive: false });
      element.addEventListener('touchmove', touchMove, { passive: false });
      element.addEventListener('touchend', touchEnd);

      // 保存引用以便后续移除
      this._touchHandlers = {
        element,
        start: touchStart,
        move: touchMove,
        end: touchEnd
      };
    },

    // 移除触摸事件
    removeTouchEvents(element) {
      if (!this._touchHandlers || !element) return;

      const { start, move, end } = this._touchHandlers;

      element.removeEventListener('touchstart', start);
      element.removeEventListener('touchmove', move);
      element.removeEventListener('touchend', end);

      this._touchHandlers = null;
    },

    // 处理ESC键关闭菜单
    handleEscKey(event) {
      if (event.key === 'Escape' && this.mobileMenuActive) {
        this.closeMobileMenu();
      }
    },

    // 处理导航栏点击事件
    handleNavigationClick(event) {
      // 检查是否点击了关闭按钮（伪元素）
      const rect = event.currentTarget.getBoundingClientRect();
      const isCloseButton = (
        event.clientX >= rect.left + rect.width - 50 &&
        event.clientX <= rect.left + rect.width - 10 &&
        event.clientY >= rect.top + 10 &&
        event.clientY <= rect.top + 40
      );

      if (isCloseButton && this.mobileMenuActive) {
        this.closeMobileMenu();
        event.stopPropagation();
      }
    },
    closeMobileMenu() {
      this.mobileMenuActive = false;
      this.isMobileMenuOpen = false; // 同步两个状态

      document.body.classList.remove('menu-open');
      this.activeSubMenus = [];

      // 移除ESC键事件监听器
      document.removeEventListener('keydown', this.handleEscKey);

      // 恢复页面滚动
      document.documentElement.style.overflow = '';
      document.body.style.overflow = '';

      // 将导航栏移出视口
      const navElement = document.querySelector('.main-navigation');
      if (navElement) {
        navElement.style.right = '-100%';

        // 移除触摸事件
        this.removeTouchEvents(navElement);
      }

      // 隐藏遮罩层
      const maskElement = document.querySelector('.mobile-mask');
      if (maskElement) {
        maskElement.style.opacity = '0';
        setTimeout(() => {
          maskElement.style.display = 'none';
        }, 300);
      }
    },
    toggleSubMenu(menuPath) {
      // 阻止事件冒泡，防止点击下拉按钮时触发链接点击
      event && event.stopPropagation();

      if (this.activeSubMenus.includes(menuPath)) {
        // 如果子菜单已经激活，移除它和所有子菜单
        this.activeSubMenus = this.activeSubMenus.filter(path => path !== menuPath && !path.startsWith(menuPath + '/'));
      } else {
        // 添加子菜单到激活列表
        this.activeSubMenus.push(menuPath);

        // 确保父菜单也处于激活状态
        if (menuPath.includes('/')) {
          const parentPath = menuPath.substring(0, menuPath.lastIndexOf('/'));
          if (parentPath && !this.activeSubMenus.includes(parentPath)) {
            this.activeSubMenus.push(parentPath);
          }
        }
      }

      // 在移动端视图中，为菜单项添加active类
      this.$nextTick(() => {
        try {
          // 找到所有带有子菜单的菜单项
          const menuItems = document.querySelectorAll('.menu-item-has-children');

          // 移除所有active类
          menuItems.forEach(item => {
            item.classList.remove('active');
          });

          // 移除所有下拉按钮的active类
          const dropdownButtons = document.querySelectorAll('.dropdown-toggle');
          dropdownButtons.forEach(btn => {
            btn.classList.remove('active');
          });

          // 为活动子菜单的父菜单项添加active类
          this.activeSubMenus.forEach(path => {
            // 使用更精确的选择器
            const menuItems = document.querySelectorAll('.menu-item-has-children');
            menuItems.forEach(item => {
              const link = item.querySelector(`a[href="${path}"]`);
              if (link) {
                item.classList.add('active');

                // 同时为对应的下拉按钮添加active类
                const dropdownBtn = link.nextElementSibling;
                if (dropdownBtn && dropdownBtn.classList.contains('dropdown-toggle')) {
                  dropdownBtn.classList.add('active');
                }
              }
            });
          });

          // 如果是移动端，滚动到展开的子菜单位置
          if (window.innerWidth <= 768 && this.activeSubMenus.includes(menuPath)) {
            // 找到当前展开的子菜单
            const targetMenuItem = document.querySelector(`.menu-item-has-children a[href="${menuPath}"]`);
            if (targetMenuItem) {
              // 获取父级菜单项
              const parentItem = targetMenuItem.closest('li');
              if (parentItem) {
                // 延迟滚动，等待子菜单展开动画完成
                setTimeout(() => {
                  // 计算滚动位置，确保菜单项在可视区域内
                  const navContainer = document.querySelector('.main-navigation');
                  const itemTop = parentItem.offsetTop;
                  const containerScrollTop = navContainer.scrollTop;
                  const containerHeight = navContainer.clientHeight;

                  // 如果菜单项不在可视区域内，滚动到合适位置
                  if (itemTop < containerScrollTop || itemTop > containerScrollTop + containerHeight - 100) {
                    navContainer.scrollTo({
                      top: Math.max(0, itemTop - 80),
                      behavior: 'smooth'
                    });
                  }
                }, 100);
              }
            }
          }

          // 强制重新计算布局
          window.dispatchEvent(new Event('resize'));
        } catch (error) {
          console.error('Error updating submenu classes:', error);
        }
      });
    },
    toggleMobileSearch() {
      this.mobileSearchActive = !this.mobileSearchActive;
      if (this.mobileSearchActive) {
        // Focus the search input when opened
        this.$nextTick(() => {
          document.querySelector('.search-form-mobile input').focus();
        });
      }
    },
    searchSite() {
      // Use any of the search queries
      const query = this.searchQueryNav || this.searchQueryMobile;
      if (query) {
        console.log('Searching for:', query);
        // Navigate to search page with query parameter
        this.$router.push({
          path: '/search',
          query: { q: query }
        });
        // Reset search queries
        this.searchQueryNav = '';
        this.searchQueryMobile = '';
        // Close mobile search if open
        this.mobileSearchActive = false;
      }
    },
    handleScroll() {
      // Detect scroll direction
      const currentScrollPosition = window.scrollY;
      const scrollingDown = currentScrollPosition > this.lastScrollPosition;

      // Apply sticky header after scrolling down 100px
      if (currentScrollPosition > 100) {
        this.isSticky = true;
      } else {
        this.isSticky = false;
      }

      // Apply sticky navigation when scrolling down and past 200px
      if (currentScrollPosition > 200) {
        if (scrollingDown) {
          // When scrolling down, make navigation sticky
          this.navSticky = true;
        } else {
          // When scrolling up, keep navigation sticky until we reach the top section
          if (currentScrollPosition < 300) {
            this.navSticky = false;
          }
        }
      } else {
        // When near the top, navigation is not sticky
        this.navSticky = false;
      }

      this.lastScrollPosition = currentScrollPosition;
    },
    handleResize() {
      // Close mobile menu when switching to desktop
      if (window.innerWidth > 768 && this.mobileMenuActive) {
        this.mobileMenuActive = false;
        document.body.classList.remove('menu-open');
        this.activeSubMenus = [];
      }
    },
    updateActiveMenuItem() {
      const currentPath = this.$route.path;
      const newActiveItems = [];

      // Special case for home page
      if (currentPath === '/') {
        newActiveItems.push('/');
      }

      // Find matching menu items
      const findActiveItems = (items, parentActive = false) => {
        items.forEach(item => {
          // Exact path match
          if (currentPath === item.path) {
            newActiveItems.push(item.path);
          }
          // Path starts with this item's path (for nested routes)
          else if (currentPath.startsWith(item.path + '/')) {
            newActiveItems.push(item.path);
          }
          // Check submenus
          if (item.children && item.children.length > 0) {
            findActiveItems(item.children, newActiveItems.includes(item.path));
          }
        });
      };

      findActiveItems(this.navItems);

      // Update the activeMenuItems array
      this.activeMenuItems = newActiveItems;
    },
    addStructuredData() {
      // Remove existing structured data
      const existingScript = document.getElementById('header-structured-data');
      if (existingScript) {
        existingScript.remove();
      }

      // Create header structured data
      const structuredData = {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": this.siteName,
        "description": this.siteDescription,
        "url": window.location.origin,
        "logo": this.siteLogo || `${window.location.origin}/images/logo.png`
      };

      // Create and add script tag
      const script = document.createElement('script');
      script.id = 'header-structured-data';
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify(structuredData);
      document.head.appendChild(script);
    },

    // 检查用户登录状态
    checkLoginStatus() {
      // 检查本地存储中是否有token
      const token = localStorage.getItem('token')

      if (token) {
        this.isLoggedIn = true

        // 尝试从本地存储中获取用户信息
        const userInfoStr = localStorage.getItem('user_info')
        if (userInfoStr) {
          try {
            this.userInfo = JSON.parse(userInfoStr)
          } catch (e) {
            console.error('解析用户信息失败:', e)
            this.getUserInfo(token)
          }
        } else {
          // 如果本地没有用户信息，从服务器获取
          this.getUserInfo()
        }
      } else {
        this.isLoggedIn = false
        this.userInfo = null
      }
    },

    // 从服务器获取用户信息
    async getUserInfo(token) {
      try {
        // 调用用户信息API
        const response = await api.get(apiUrls.USER_API.INFO)

        if (response.code === 200 && response.data) {
          console.log(response.data,'fffff44444444444444')
          this.userInfo = response.data
          // 将用户信息保存到本地存储
          localStorage.setItem('user_info', JSON.stringify(response.data))
        } else {
          // 如果API返回错误，可能是token已过期
          this.handleLogout()
        }
      } catch (error) {
        console.error('获取用户信息失败:', error)
        // 如果获取用户信息失败，可能是token已过期
        if (error.response && error.response.status === 401) {
          this.handleLogout()
        }
      }
    },

    // 处理用户登出
    handleLogout() {
      // 清除本地存储中的token和用户信息
      localStorage.removeItem('token')
      localStorage.removeItem('user_info')

      // 重置状态
      this.isLoggedIn = false
      this.userInfo = null

      // 可选：跳转到登录页面或首页
      if (this.$route.path !== '/') {
        this.$router.push('/')
      }
    }
  }
}
</script>

<style scoped>
/* Icon font definitions for search and navigation icons */
@font-face {
  font-family: 'dux-icons';
  src: url('data:application/x-font-woff2;charset=utf-8;base64,d09GMgABAAAAAAWcAAsAAAAACqQAAAVPAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHEIGVgCDSAqILIc1ATYCJAMYCw4ABCAFhG0HUBvMCFFUUE7Ifh7YMWNbOZqXUkTcFH7gn+jvzBQJEDFkYCMUGQojMUK8gAOAVuUTe/+AkBwgM7XPLQs4OBQOEcpFgO3Zs/+5nF5fC+Tz28xldmwab9KAowkUUEZtW3QB7oAcgL+hvqo7DAM8GgTwUERNpLWnZwB8DBYJILp27pgV/FQYzJY4gQ9vPGwqIlPBwzchcwCYon9e/KFFfMAwKtiJJjqnT6f+B/ejj5xdY1bjGsK2XwBwywEFqAnAANGv0dMKlVQ1UHzZGJY2AAofBvggo7IhH7mPnF3z0QoK8Zr/8DAoMAQOWGxEHgiFkkkB56tGDeCD1xkEfPQEPvjoC/hgdaUkYHcRd4DPAFQDkoVIlhZOHDcJvHi8QLrX7/SFLm9Ycd/h8Lud7pvXrHYXSHrJdYAg7jQYwMUdJDQWXVm/+VzfRYKEXGJJxMHkDjhsZ05nTLcbRfv27JLthOWdDPQC73UXbbz9l737trlEi1OxJ8NxUW+41+3a5bpr+JXrLuLEUd0/IcNh+g0bbtGe/Uf27juG8O7Z9fCFh4ePJdxHjl29cfnI0ZMFXWMtK/qltVtXrFNjrTaF64/prH50r/DII0tXp2VBX5rDGR5L5JG9+JFdOEfiwNGD+nh2/tE9uYsXLxnbBweSg7vZvNF98uDB48mdO8KmtJw4cTweXXR495Gjlx/eHRs5lpBhyIGjR05GDx87cuzKicPHl9+27szyK0tGzLNnlVe9vCDjmeZbvs1Jj8R21XH7FW6b+l4+4YhsYeW+Fq3j/v2JhIcf2TvEULdnNw+ydUvhxfP7qfxDhO7tFBQsGKR89sxJL08+flzi+fRIXU1QQG9I0lOTT1ZUQnq6TdXlv6O3w9JwdkODNSixft/aPXvWfRKIzGJrDEFTczRYomZsabG6yMqmZmuDDHKcVIgsa5w0VSo80rhh5BKx/cCBUOw3duzXrl09avv2UbZwSOi6KwwOHWpxuR+lDApB25FDWPipnDRLaZtkmzTDsqCX5aaUFLO1e+eSmnQjVqxdssRZKnU1cQ37Ym+73Y6ZZdIyzYSp0ZKR7u3QTlwicfFCx2lTWWUE3ggrZ20LCQHbLOALBW5LM8+E2EK2sGWkJX/hFtuoGQX/7b1TzLuftHztPvmv/5Rv2Y6O3/HqVYl9x85E/u0XHPtxw88/N+BjRbR4TS08VrCmbGpKR+GxkrIF3+xwi6kJ3+GYGi/6pnxHdLXWUMvQaR1eW7d+mKlYVH39+pxTpnLT5L1nSkrqGmPJc1G2bS1bd+7MtjQ1RLPXtQ1dh1pnFZ86Vdyx+V9R4y/v9XOWztvqePG0+4nT5V3/n21ubKs9dapWbGtuLxW1p4jF9pYyES0tDcZYW2Nj6wKx3m1dw8/PnFP1ySIAZIzEbwD6R2QHc0d8hIiOCx2PAAiv6NchMp+P3ItMJ14BEPXcKkbklzs7ZZWjRRkFuUFnw+Bnw7+uCSzj3SadtvFVMz5UszxW3nTn9zL9D/sxXt4qPkYFAAeYNzLPTj9iZSZDpCeAwHx5NzH0IQvNVRFXhYvQIhc+QUPKj99pTYk4WQJVAMDH3wDDpQRK0SWYMTUBBz9tAGd3oPCIU+dPCFC1SIPCZQCgMhYCIeYWGEL+BSXmOZgxX4HDAn+BMyZcKJLw/C1JLpyGJXIotMXoBPaVbtrCYrBHZDNlr2XZrfkWeiJ94DLtfHeSLp5hhQ3GnMfrohQWU4xwCC9Ct83Bbucwz6dTGscWO72t7aQXtgXJIdEKIzvANZXaaGtjCm/fj5CyUdKnFXMM/yuQF1JGDNXBqt46kZJZ5RZV18hxeHSpVBZqMcLfCxzACyKZB3MEbJKDkM+qjLMudqG7PFmLGnWN1OWyR9wP2/wnSIKJ0iTDmA7EbTrb0FJ5tbRxR9z7s7YwdNE8AAAAAA==');
  font-weight: normal;
  font-style: normal;
}

[class^="icon-"], [class*=" icon-"] {
  font-family: 'dux-icons' !important;
  /* 修复speak属性警告 */
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.icon-search:before {
  content: "\e900";
}
.icon-angle-down:before {
  content: "\e901";
}
.icon-close:before {
  content: "\e902";
}

.site-header {
  background-color: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 1000;
}

/* Sticky header */
.site-header.sticky {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  animation: slideDown 0.3s ease;
  z-index: 1000;
}

.site-header.sticky .top-bar {
  display: none;
}

.site-header.sticky .main-header {
  padding: 10px 0;
}

/* Sticky navigation */
.main-navigation.nav-sticky {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  animation: slideDown 0.3s ease;
  background-color: var(--secondary-color, #1c2033);
}

@keyframes slideDown {
  0% {
    transform: translateY(-100%);
  }
  100% {
    transform: translateY(0);
  }
}

.container {
  max-width: var(--container-width, 1200px);
  margin: 0 auto;
  padding: 0 15px;
  width: 100%;
}

/* Top bar */
.top-bar {
  background-color: var(--bg-color, #fff);
  border-bottom: 1px solid var(--border-color, #eee);
  font-size: 14px; /* 增加字体大小 */
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  color: #555555;
}

.top-bar .container {
  padding: 10px 15px; /* 增加垂直内边距 */
}

.top-bar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  width: 100%;
}

.top-nav {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
}

.top-nav li {
  position: relative;
  margin-right: 5px;
}

.top-nav li a {
  color: #828080 !important; /* 使用更深的颜色并强制应用 */
  text-decoration: none;
  transition: color var(--transition-speed, 0.3s);
  font-weight: 500; /* 增加字体粗细 */
}

.top-nav li a:hover {
  color: var(--primary-color, #09c) !important;
  text-decoration: underline;
}

.user-link, .dashboard-link, .logout-link {
  display: inline-block;
  padding: 0 10px;
  transition: color 0.3s ease;
}

.user-link {
  font-weight: 700; /* 更粗的字体 */
  color: var(--primary-color, #09c) !important;
}

.logout-link {
  cursor: pointer;
}

.logout-link:hover {
  color: #f56c6c;
}

.top-nav .menu-item-has-children > a:after {
  content: '\f107';
  font-family: 'FontAwesome';
  margin-left: 4px;
}

.top-nav .sub-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  z-index: 100;
  min-width: 150px;
  display: none;
  border-radius: 3px;
  overflow: hidden;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.top-nav .menu-item-has-children:hover > .sub-menu {
  display: block;
}

.top-nav .sub-menu li {
  margin: 0;
}

.top-nav .sub-menu a {
  padding: 8px 15px;
  display: block;
  border-bottom: 1px solid var(--border-color, #eee);
}

.top-nav .sub-menu a:hover {
  background-color: var(--bg-color-hover, #f8f8f8);
  transition: background-color 0.3s ease;
}

.user-panel {
  display: flex;
  flex-grow: 1;
  justify-content: flex-end;
  align-items: center;
  gap: 1px;
}

.user-panel a {
  color: #828080 !important; /* 使用更深的颜色并强制应用 */
  text-decoration: none;
  font-size: 13px;
  padding: 0 8px;
  transition: color var(--transition-speed, 0.3s);
  white-space: nowrap;
  font-weight: 500; /* 增加字体粗细 */
}

.user-panel a:hover {
  color: var(--primary-color, #09c) !important;
  text-decoration: underline;
}

/* Main header area with logo and search */
.main-header {
  padding: 20px 0;
  transition: all 0.3s ease;
  background-color: var(--card-bg-color, #fff);
  border-bottom: 1px solid var(--border-color, #eee);
  will-change: background-color, border-color;
}

.main-header-container {
  position: relative;
}

.header-flex {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* Logo area */
.site-branding {
  text-align: left;
  flex: 1;
}

.branding-flex {
  display: flex;
  align-items: center;
  gap: 15px;
}

.site-logo {
  display: block;
}

.logo-img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.logo-img:hover {
  transform: scale(1.05);
}

.branding-text {
  flex: 1;
}

.site-title {
  margin: 0;
  font-size: 28px;
  font-weight: bold;
}

.site-title a {
  color: var(--heading-color, #333);
  text-decoration: none;
  transition: color var(--transition-speed, 0.3s);
}

.site-title a:hover {
  color: var(--primary-color, #09c);
}

.site-description {
  margin: 5px 0 0;
  font-size: 14px;
  color: var(--text-color-secondary, #999);
  line-height: 1.5;
}

/* Mobile nav toggle */
.mobile-nav-toggle {
  display: none;
  background: transparent;
  border: none;
  padding: 0;
  width: 26px;
  height: 26px;
  cursor: pointer;
  position: relative;
  z-index: 1050;
}

.mobile-nav-toggle span {
  display: block;
  width: 26px;
  height: 2px;
  background-color: var(--heading-color, #333);
  margin: 5px 0;
  transition: all 0.3s;
  transform-origin: center;
}

.mobile-nav-toggle.active span:nth-child(1) {
  transform: rotate(45deg) translate(5px, 5px);
}

.mobile-nav-toggle.active span:nth-child(2) {
  opacity: 0;
}

.mobile-nav-toggle.active span:nth-child(3) {
  transform: rotate(-45deg) translate(7px, -7px);
}

/* Main navigation */
.main-navigation {
  background-color: var(--secondary-color, #1c2033);
  position: relative;
  transition: all 0.3s ease-out;
  z-index: 999;
  border-bottom: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
}

.main-navigation .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.site-nav-wrap {
  display: flex;
  flex-wrap: wrap;
}

.nav-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  flex-wrap: wrap;
}

.nav-menu > li {
  position: relative;
}

.nav-menu > li > a {
  display: block;
  padding: 0 20px;
  line-height: 50px;
  color: var(--text-color-inverse, #fff);
  text-decoration: none;
  font-size: 15px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.nav-menu > li > a::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 0;
  background-color: rgba(255, 255, 255, 0.1);
  transition: height 0.3s ease;
  z-index: -1;
}

.nav-menu > li:hover > a {
  color: #fff;
}

.nav-menu > li:hover > a::before {
  height: 100%;
}

.nav-menu > li > a.highlight {
  background-color: var(--primary-color, #09c);
}

.dropdown-toggle {
  display: none;
  color: var(--text-color-inverse-light, rgba(255, 255, 255, 0.7));
  cursor: pointer;
  padding: 0 10px;
  position: absolute;
  right: 0;
  top: 0;
  height: 46px;
  text-align: center;
  line-height: 46px;
  z-index: 2;
  transition: color var(--transition-speed, 0.3s);
}

.dropdown-toggle i {
  transition: transform 0.3s ease;
}

.menu-item-has-children.active > .dropdown-toggle i {
  transform: rotate(180deg);
}

.nav-menu .menu-item-has-children > a:after {
  content: '\f107';
  font-family: 'FontAwesome';
  margin-left: 6px;
}

.nav-menu .sub-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background-color: var(--card-bg-color, #fff);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
  min-width: 220px;
  display: none;
  z-index: 100;
  list-style: none;
  padding: 8px 0;
  margin: 0;
  border-radius: 4px;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  border-top: 3px solid var(--primary-color, #09c);
  overflow: hidden;
}

.nav-menu .menu-item-has-children:hover > .sub-menu {
  display: block;
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.nav-menu .sub-menu .menu-item-has-children > a:after {
  content: '\f105';
  font-family: 'FontAwesome';
  float: right;
  margin-left: 6px;
  transition: transform 0.3s ease;
}

.nav-menu .sub-menu .menu-item-has-children:hover > a:after {
  transform: translateX(3px);
}

.nav-menu .sub-menu li {
  position: relative;
}

.nav-menu .sub-menu a {
  display: block;
  padding: 10px 18px;
  color: var(--text-color, #333);
  text-decoration: none;
  border-bottom: 1px solid var(--border-color, #eee);
  font-size: 14px;
  transition: all 0.25s ease;
  white-space: nowrap;
  position: relative;
}

.nav-menu .sub-menu li:last-child > a {
  border-bottom: none;
}

.nav-menu .sub-menu a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 3px;
  height: 0;
  background-color: var(--primary-color, #09c);
  transition: height 0.25s ease;
}

.nav-menu .sub-menu a:hover {
  background-color: rgba(52, 152, 219, 0.05);
  color: var(--primary-color, #09c);
  padding-left: 24px;
}

.nav-menu .sub-menu a:hover::before {
  height: 100%;
}

.nav-menu .sub-menu .sub-menu {
  top: 0;
  left: 100%;
  margin-top: -8px;
  margin-left: 0;
  border-radius: 4px;
  border-top: none;
  border-left: 3px solid var(--primary-color, #09c);
}

/* Fix hover display for third-level menu */
.nav-menu .sub-menu .menu-item-has-children:hover > .sub-menu {
  display: block;
}

/* Active state styling for multi-level menus */
.nav-menu .menu-item-has-children.active > a,
.nav-menu .sub-menu .menu-item-has-children.active > a {
  color: var(--primary-color, #09c);
  background-color: rgba(0, 123, 255, 0.05);
}

/* Mobile menu adjustments for multi-level */
@media (max-width: 768px) {
  .mobile-nav-toggle {
    display: none; /* 不显示移动导航按钮 */
  }

  .nav-menu {
    display: none;
  }

  .main-navigation.mobile-active .nav-menu {
    display: block;
    position: fixed;
    top: 0;
    right: 0;
    width: 80%;
    max-width: 300px;
    height: 100vh;
    background-color: #fff;
    z-index: 1000;
    overflow-y: auto;
    padding: 60px 0 100px;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    animation: slideInRight 0.3s ease;
  }

  @keyframes slideInRight {
    0% {
      transform: translateX(100%);
    }
    100% {
      transform: translateX(0);
    }
  }

  .nav-menu > li {
    float: none;
    display: block;
    margin: 0;
  }

  .nav-menu > li > a {
    color: var(--heading-color, #333);
    padding: 12px 20px;
    line-height: 22px;
    border-bottom: 1px solid var(--border-color, #eee);
  }

  .nav-menu > li:hover > a {
    background-color: transparent;
    color: var(--primary-color, #09c);
  }

  .dropdown-toggle {
    display: block;
  }

  /* Mobile adjustments for sub-menus */
  .nav-menu .sub-menu {
    position: static;
    display: none;
    width: 100%;
    box-shadow: none;
    background-color: #f5f5f5;
  }

  .nav-menu .menu-item-has-children > a:after {
    display: none;
  }

  .nav-menu .menu-item-has-children.active > .sub-menu {
    display: block;
  }

  .nav-menu .sub-menu a {
    padding-left: 30px;
  }

  .nav-menu .sub-menu .sub-menu a {
    padding-left: 45px;
  }
}

/* Search form in navigation */
.nav-search {
  margin-left: 15px;
}

.search-form-nav {
  display: flex;
  position: relative;
  height: 34px;
}

.search-form-nav input {
  width: 200px;
  height: 34px;
  padding: 0 40px 0 15px;
  border: none;
  border-radius: 20px;
  font-size: 13px;
  outline: none;
  transition: width var(--transition-speed, 0.3s);
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.search-form-nav input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.search-form-nav input:focus {
  width: 240px;
  background-color: rgba(255, 255, 255, 0.15);
}

.search-button {
  position: absolute;
  right: 0;
  top: 0;
  height: 34px;
  width: 34px;
  background-color: transparent;
  border: none;
  cursor: pointer;
  transition: color var(--transition-speed, 0.3s);
  display: flex;
  align-items: center;
  justify-content: center;
}

.search-button:hover {
  color: #fff;
}

.search-icon {
  font-size: 18px;
  color: #fff;
}

/* Mobile mask */
.mobile-mask {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.mobile-mask.active {
  display: block;
  opacity: 1;
}

/* Mobile search panel */
.mobile-search-panel {
  background-color: #f5f5f5;
  padding: 15px 0;
  display: none;
}

.mobile-search-panel.active {
  display: block;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

.search-form-mobile {
  position: relative;
  display: flex;
}

.search-form-mobile input {
  width: 100%;
  height: 42px;
  padding: 0 80px 0 15px;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 4px;
  font-size: 14px;
  outline: none;
}

.search-form-mobile button[type="submit"] {
  position: absolute;
  right: 42px;
  top: 0;
  height: 42px;
  width: 42px;
  background-color: var(--primary-color, #09c);
  color: #fff;
  border: none;
  cursor: pointer;
}

.search-form-mobile .close-search {
  position: absolute;
  right: 0;
  top: 0;
  height: 42px;
  width: 42px;
  background-color: #ccc;
  color: #333;
  border: none;
  cursor: pointer;
  border-radius: 0 4px 4px 0;
}

.search-icon-mobile {
  font-size: 18px;
  color: #fff;
}

.desktop-only {
  display: block;
}

/* 骨架屏样式 - 优先级提高，确保立即应用 */
.header-skeleton {
  width: 100% !important;
  background-color: #fff !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  position: relative !important;
  z-index: 1000 !important;
}

.skeleton-top-bar {
  height: 40px !important;
  background-color: #f8f9fa !important;
  border-bottom: 1px solid #eaeaea !important;
}

.skeleton-main-header {
  padding: 20px 0 !important;
  background-color: #fff !important;
  border-bottom: 1px solid #eaeaea !important;
}

.skeleton-branding {
  display: flex !important;
  align-items: center !important;
  gap: 15px !important;
  max-width: 1200px !important;
  margin: 0 auto !important;
  padding: 0 15px !important;
}

.skeleton-logo {
  width: 50px !important;
  height: 50px !important;
  border-radius: 50% !important;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%) !important;
  background-size: 200% 100% !important;
  animation: shimmer 1.5s infinite !important;
}

.skeleton-text {
  flex: 1 !important;
}

.skeleton-title {
  height: 28px !important;
  width: 200px !important;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%) !important;
  background-size: 200% 100% !important;
  animation: shimmer 1.5s infinite !important;
  margin-bottom: 8px !important;
  border-radius: 4px !important;
}

.skeleton-description {
  height: 14px !important;
  width: 300px !important;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%) !important;
  background-size: 200% 100% !important;
  animation: shimmer 1.5s infinite !important;
  border-radius: 4px !important;
}

.skeleton-navigation {
  height: 50px !important;
  background-color: #2c3e50 !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Responsive styles */
@media (max-width: 992px) {
  .site-branding {
    text-align: center;
  }

  .site-title {
    font-size: 24px;
  }

  .nav-search {
    display: none;
  }
}

/* 移动端样式通过外部CSS文件引入 */
@media (max-width: 768px) {
  /* 基本样式保留，详细样式在navbar-modern-mobile.css中定义 */
  body.menu-open {
    overflow: hidden;
  }

  .site-branding {
    text-align: left;
    margin-bottom: 0;
    flex: 1;
  }

  .site-title {
    font-size: 22px;
  }

  .site-description {
    display: none;
  }

  .mobile-nav-toggle {
    display: none; /* 不显示移动导航按钮 */
  }

  .desktop-only {
    display: none;
  }

  .main-header {
    padding: 15px 0;
  }

  .top-bar {
    display: none;
  }
}

@media (max-width: 576px) {
  .site-title {
    font-size: 20px;
  }

  .main-navigation {
    width: 250px;
    right: -250px;
  }

  .main-navigation.mobile-active {
    transform: translateX(-250px);
  }
}

/* Active menu styles */
.nav-menu > li.active > a {
  color: #fff;
  font-weight: 600;
}

.nav-menu > li.active > a::before {
  height: 100%;
  background-color: rgba(255, 255, 255, 0.15);
}

.nav-menu > li.active:after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background-color: #fff;
}

/* Active submenu styles */
.sub-menu li.active > a {
  color: var(--primary-color, #09c);
  background-color: rgba(52, 152, 219, 0.05);
  font-weight: 500;
  padding-left: 24px;
}

.sub-menu li.active > a::before {
  height: 100%;
}

/* 暗黑模式和系统模式下的站点标题颜色 */
html.dark-theme .site-title a,
html.dark .site-title a,
html.system-dark-theme .site-title a,
html.dark.system-theme .site-title a {
  color: #ffffff !important;
}

html.dark-theme .site-description,
html.dark .site-description,
html.system-dark-theme .site-description,
html.dark.system-theme .site-description {
  color: rgba(255, 255, 255, 0.8) !important;
}

/* 暗黑模式和系统模式下的主标题区域背景颜色 */
html.dark-theme .main-header,
html.dark .main-header,
html.system-dark-theme .main-header,
html.dark.system-theme .main-header {
  background-color: var(--card-bg-color, #232324) !important;
  border-color: rgba(255, 255, 255, 0.05) !important;
}

html.dark-theme .mobile-nav-toggle span,
html.dark .mobile-nav-toggle span,
html.system-dark-theme .mobile-nav-toggle span,
html.dark.system-theme .mobile-nav-toggle span {
  background-color: #ffffff !important;
}

/* Mobile active menu styles */
@media (max-width: 991px) {
  .nav-menu > li.active:after {
    display: none;
  }

  .nav-menu > li.active > a {
    border-left: 3px solid var(--primary-color);
    background-color: rgba(0, 123, 255, 0.05);
  }
}

/* Header controls styles */
.top-bar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-controls-wrapper {
  display: flex;
  align-items: center;
  margin-left: 10px;
  padding-left: 10px;
  border-left: 1px solid var(--border-color, #eee);
}

@media (max-width: 768px) {
  .header-controls-wrapper {
    padding-left: 5px;
  }

  .top-bar-content {
    justify-content: center;
  }

  .user-panel {
    order: 2;
    margin-top: 5px;
    justify-content: center;
    width: 100%;
    gap: 10px;
    flex-wrap: wrap;
  }

  .user-panel a {
    font-size: 12px;
    padding: 4px 6px;
  }

  .header-controls-wrapper {
    order: 1;
    margin-left: 10px;
    border-left: none;
    padding-left: 0;
  }

  .top-nav {
    order: 0;
  }
}
</style>