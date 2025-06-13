<!-- 首页 -->
<template>
  <MainLayout>
    <!-- Hero section -->
    <div class="container main-container">
      <div class="row">
        <div class="col-md-8">
          <FeaturedSlider />
          <LatestPosts />
        </div>
        <div class="col-md-4">
          <Sidebar />
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from './components/MainLayout.vue';
import FeaturedSlider from './components/FeaturedSlider.vue';
import LatestPosts from './components/LatestPosts.vue';
import Sidebar from './components/Sidebar.vue';
import './assets/blog.css';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { useHead } from '@vueuse/head';
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { useBaseSEO } from '@/utils/seo';
import viewTracker from '@/utils/viewTracker';

export default {
  name: 'HomePage',
  components: {
    MainLayout,
    FeaturedSlider,
    LatestPosts,
    Sidebar
  },
  setup() {
    const blogConfigStore = useBlogConfigStore();

    // 计算SEO相关的值
    const siteName = computed(() => blogConfigStore.config.site_name || '');
    const siteDescription = computed(() => blogConfigStore.config.site_description || '');
    const siteKeywords = computed(() => blogConfigStore.config.site_keywords || '');
    const siteLogo = computed(() => blogConfigStore.config.site_logo || '');

    // Title for the homepage
    const title = computed(() => `${siteName.value} - 首页`);

    // Use our SEO utility for consistent meta tags
    useHead(useBaseSEO({
      title,
      description: siteDescription,
      keywords: siteKeywords,
      image: siteLogo,
      type: 'website',
      twitterCard: 'summary_large_image'
    }));

    // Add homepage specific structured data
    useHead({
      script: [
        {
          type: 'application/ld+json',
          children: computed(() => {
            // Create WebSite structured data
            const structuredData = {
              "@context": "https://schema.org",
              "@type": "WebSite",
              "name": siteName.value,
              "description": siteDescription.value,
              "url": window.location.origin,
              "potentialAction": {
                "@type": "SearchAction",
                "target": {
                  "@type": "EntryPoint",
                  "urlTemplate": `${window.location.origin}/search?q={search_term_string}`
                },
                "query-input": "required name=search_term_string"
              }
            };

            return JSON.stringify(structuredData);
          })
        }
      ]
    });

    // 在页面加载时开始跟踪浏览时长
    onMounted(() => {
      viewTracker.start('site');
    });

    // 在页面卸载前停止跟踪
    onBeforeUnmount(() => {
      viewTracker.stop();
    });

    return {
      siteName,
      siteDescription
    };
  }
}
</script>

<style scoped>
/* Hero section styles */
.hero-section {
  background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
  color: white;
  padding: 80px 0;
  margin-bottom: 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTI4MCAxNDAiIHByZXNlcnZlQXNwZWN0UmF0aW89Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSI+PHBhdGggZD0iTTEyODAgMy40QzEwNTAuNTkgMTggMTAxOS40IDg0Ljg5IDczNC40MiA4NC44OWMtMzIwIDAtMzIwLTg0LjMtNjQwLTg0LjNDNTkuNC41OSAyOC4yIDEuNiAwIDMuNFYxNDBoMTI4MHoiIGZpbGwtb3BhY2l0eT0iLjMiLz48cGF0aCBkPSJNMCAyNC4zMWM0My40Ni01LjY5IDk0LjU2LTkuMjUgMTU4LjQyLTkuMjUgMzIwIDAgMzIwIDg5LjI0IDY0MCA4OS4yNCAyNTYuMTMgMCAzMDcuMjgtNTcuMTYgNDgxLjU4LTgwVjE0MEgweiIgZmlsbC1vcGFjaXR5PSIuNSIvPjxwYXRoIGQ9Ik0xMjgwIDUxLjc2Yy0yMDEgMTIuNDktMjQyLjQzIDUzLjQtNTEzLjU4IDUzLjQtMzIwIDAtMzIwLTU3LTY0MC01Ny00OC44NS4wMS05MC4yMSAxLjM1LTEyNi40MiAzLjZWMTQwaDEyODB6Ii8+PC9nPjwvc3ZnPg==');
  background-size: 100% 100px;
  background-position: bottom;
  background-repeat: no-repeat;
  z-index: 1;
  opacity: 0.8;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  opacity: 0.9;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.hero-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.btn {
  display: inline-block;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  background-color: #3498db;
  color: white;
}

.btn-primary:hover {
  background-color: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  backdrop-filter: blur(5px);
}

.btn-secondary:hover {
  background-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.main-container {
  margin-top: -20px;
}

/* Responsive styles */
@media (max-width: 768px) {
  .hero-section {
    padding: 60px 0;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .hero-buttons {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 80%;
    margin-bottom: 10px;
    text-align: center;
  }
}
</style>