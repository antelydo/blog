<!-- 移动端导航组件 -->
<template>
  <div class="mobile-navigation-wrapper" v-if="isMobile">
    <!-- 底部固定导航栏 -->
    <nav class="mobile-nav-bar">
      <div class="mobile-nav-container">
        <!-- 主要导航项 -->
        <router-link to="/" class="mobile-nav-item" :class="{ 'active': isActive('/') }">
          <i class="fa fa-home"></i>
          <span>{{ t('mobileNav.home') }}</span>
        </router-link>

        <button class="mobile-nav-item mobile-nav-more" @click="toggleMoreMenu">
          <i class="fa fa-compass"></i>
          <span>{{ t('mobileNav.explore') }}</span>
        </button>

        <router-link to="/search" class="mobile-nav-item" :class="{ 'active': isActive('/search') }">
          <i class="fa fa-search"></i>
          <span>{{ t('mobileNav.search') }}</span>
        </router-link>

        <router-link to="/personal-center" class="mobile-nav-item" :class="{ 'active': isActive('/personal-center') }">
          <i class="fa fa-user"></i>
          <span>{{ t('mobileNav.me') }}</span>
        </router-link>
      </div>
    </nav>

    <!-- 更多菜单 -->
    <div class="mobile-more-menu" :class="{ 'active': moreMenuActive }">
      <div class="mobile-more-menu-header">
        <h3>{{ t('mobileNav.explore') }}</h3>
        <button class="mobile-more-close" @click="closeMoreMenu">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <div class="mobile-more-menu-content">
        <div class="mobile-more-section">
          <h4>{{ t('mobileNav.sections.explore') }}</h4>
          <div class="mobile-more-links">
            <router-link v-for="(item, index) in navItems"
                        :key="index"
                        :to="item.path"
                        class="mobile-more-link"
                        @click="closeMoreMenu">
              <i :class="getIconForPath(item.path)"></i>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <div class="mobile-more-section">
          <h4>{{ t('mobileNav.sections.about') }}</h4>
          <div class="mobile-more-links">
            <router-link to="/about" class="mobile-more-link" @click="closeMoreMenu">
              <i class="fa fa-info-circle"></i>
              <span>{{ t('mobileNav.about') }}</span>
            </router-link>
            <router-link to="/contact" class="mobile-more-link" @click="closeMoreMenu">
              <i class="fa fa-envelope"></i>
              <span>{{ t('mobileNav.contact') }}</span>
            </router-link>
            <router-link to="/links" class="mobile-more-link" @click="closeMoreMenu">
              <i class="fa fa-link"></i>
              <span>{{ t('mobileNav.links') }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- 遮罩层 -->
    <div class="mobile-nav-mask" :class="{ 'active': moreMenuActive }" @click="closeMoreMenu"></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useBlogConfigStore } from '@/stores/blogConfig'
import { getCatList } from '@/api/cat'
import '../assets/mobile-navigation.css'

export default {
  name: 'MobileNavigation',
  setup() {
    const { t } = useI18n()
    const route = useRoute()
    const router = useRouter()
    const blogConfigStore = useBlogConfigStore()

    // 状态
    const isMobile = ref(false)
    const moreMenuActive = ref(false)
    const navItems = ref([])

    // 检查是否为移动设备
    const checkMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    // 切换更多菜单
    const toggleMoreMenu = () => {
      moreMenuActive.value = !moreMenuActive.value
      if (moreMenuActive.value) {
        document.body.classList.add('mobile-menu-open')
      } else {
        document.body.classList.remove('mobile-menu-open')
      }
    }

    // 关闭更多菜单
    const closeMoreMenu = () => {
      moreMenuActive.value = false
      document.body.classList.remove('mobile-menu-open')
    }

    // 检查路由是否激活
    const isActive = (path) => {
      if (path === '/') {
        return route.path === '/'
      }
      return route.path.startsWith(path)
    }

    // 根据路径获取图标
    const getIconForPath = (path) => {
      const iconMap = {
        '/': 'fa fa-home',
        '/category': 'fa fa-folder',
        '/tag': 'fa fa-tag',
        '/search': 'fa fa-search',
        '/about': 'fa fa-info-circle',
        '/contact': 'fa fa-envelope',
        '/links': 'fa fa-link',
        '/personal-center': 'fa fa-user'
      }

      for (const key in iconMap) {
        if (path.startsWith(key)) {
          return iconMap[key]
        }
      }

      return 'fa fa-file'
    }

    // 初始化导航项
    const initNavItems = async () => {
      try {
        // 调用分类接口获取分类列表
        const response = await getCatList({
          status: 1, // 只获取状态为正常的分类
          page: 1,
          limit: 100 // 获取足够多的分类，以便筛选
        });

        if (response && response.code === 200 && response.data && response.data.list) {
          // 筛选出一级分类（parent_id为0或null的分类）
          const topLevelCategories = response.data.list.filter(category =>
            !category.parent_id || category.parent_id === 0
          );

          // 按照sort字段排序（如果有）
          if (topLevelCategories.length > 0 && topLevelCategories[0].sort !== undefined) {
            topLevelCategories.sort((a, b) => (a.sort || 0) - (b.sort || 0));
          }

          // 最多显示10个一级分类
          const limitedCategories = topLevelCategories.slice(0, 10);

          const categoryItems = limitedCategories.map(category => ({
            path: `/category/${category.id}`,
            label: category.name
          }));

          navItems.value = categoryItems;
          console.log('已加载分类数据:', categoryItems.length);
        } else {
          console.warn('获取分类数据失败或数据格式不正确:', response);
          // 使用默认导航项
          setDefaultNavItems();
        }
      } catch (error) {
        console.error('获取分类数据出错:', error);
        // 出错时使用默认导航项
        setDefaultNavItems();
      }
    }

    // 设置默认导航项
    const setDefaultNavItems = () => {
      navItems.value = [
        { path: '/category/1', label: t('mobileNav.news') },
        { path: '/category/2', label: t('mobileNav.tech') },
        { path: '/category/3', label: t('mobileNav.culture') },
        { path: '/category/4', label: t('mobileNav.life') }
      ];
    }

    // 监听路由变化
    watch(() => route.path, () => {
      closeMoreMenu()
    })

    onMounted(() => {
      checkMobile()
      window.addEventListener('resize', checkMobile)

      // 立即初始化导航项
      initNavItems()

      // 监听博客配置变化，当配置加载完成时重新获取分类
      if (!blogConfigStore.initialized) {
        window.addEventListener('blog-config-ready', () => {
          console.log('博客配置已加载，重新获取分类数据');
          initNavItems();
        }, { once: true })
      }
    })

    onBeforeUnmount(() => {
      window.removeEventListener('resize', checkMobile)
      document.body.classList.remove('mobile-menu-open')
    })

    return {
      t,
      isMobile,
      moreMenuActive,
      navItems,
      toggleMoreMenu,
      closeMoreMenu,
      isActive,
      getIconForPath
    }
  }
}
</script>
