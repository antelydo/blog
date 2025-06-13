<!-- 专题页 -->
<template>
  <MainLayout>
    <div class="container">
        <div class="topics-header mb-4">
          <h1 class="topics-title">Topics</h1>
          <p class="topics-description">Browse all available topics on our blog</p>
        </div>
        <div class="row">
          <div class="col-md-8">
            <div class="topics-list">
              <div v-for="topic in topics" :key="topic.id" class="topic-item card mb-4">
                <div class="topic-content p-3">
                  <h2 class="topic-title"><a :href="topic.link">{{ topic.name }}</a></h2>
                  <div class="topic-meta">
                    <span class="topic-count"><i class="fa fa-file-text-o"></i> {{ topic.count }} posts</span>
                  </div>
                  <div class="topic-description">{{ topic.description }}</div>
                  <div class="topic-posts">
                    <h3 class="posts-heading">Recent Posts</h3>
                    <ul class="posts-list">
                      <li v-for="post in topic.recentPosts" :key="post.id" class="post-link">
                        <a :href="post.link">{{ post.title }}</a>
                        <span class="post-date">{{ post.date }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="pagination-wrapper text-center mb-4">
              <div class="pagination">
                <a href="#" @click.prevent="prevPage" class="prev-page" :class="{ disabled: currentPage === 1 }">上一页</a>
                <span class="current-page">{{ currentPage }}</span>
                <a href="#" @click.prevent="nextPage" class="next-page" :class="{ disabled: currentPage === totalPages }">下一页</a>
              </div>
            </div>
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
import Sidebar from './components/Sidebar.vue';
import './assets/blog.css';
import { useHead } from '@vueuse/head';
import { computed } from 'vue';
import { useBlogConfigStore } from '@/stores/blogConfig';
import { useI18n } from 'vue-i18n';

export default {
  name: 'TopicsPage',
  components: {
    MainLayout,
    Sidebar
  },
  data() {
    return {
      currentPage: 1,
      totalPages: 3,
      topics: [
        {
          id: 1,
          name: 'Technology',
          count: 24,
          description: 'Explore the latest technological innovations and trends shaping our digital world.',
          link: '/topic/technology',
          recentPosts: [
            { id: 1, title: 'The Future of AI in Everyday Life', link: '/post/1', date: '2023-06-15' },
            { id: 2, title: 'Understanding Blockchain Technology', link: '/post/2', date: '2023-06-12' },
            { id: 3, title: '5G Technology: What You Need to Know', link: '/post/3', date: '2023-06-10' }
          ]
        },
        {
          id: 2,
          name: 'Design',
          count: 18,
          description: 'Discover design principles, tools, and inspirations for creating beautiful user experiences.',
          link: '/topic/design',
          recentPosts: [
            { id: 4, title: 'Color Theory for Modern Web Design', link: '/post/4', date: '2023-06-08' },
            { id: 5, title: 'UX Design Principles Every Designer Should Know', link: '/post/5', date: '2023-06-05' },
            { id: 6, title: 'Typography Trends in 2023', link: '/post/6', date: '2023-06-03' }
          ]
        },
        {
          id: 3,
          name: 'Development',
          count: 32,
          description: 'Learn about software development best practices, tools, and frameworks.',
          link: '/topic/development',
          recentPosts: [
            { id: 7, title: 'Getting Started with Vue.js 3', link: '/post/7', date: '2023-06-01' },
            { id: 8, title: 'Modern JavaScript Features You Should Know', link: '/post/8', date: '2023-05-29' },
            { id: 9, title: 'Building RESTful APIs with Node.js', link: '/post/9', date: '2023-05-27' }
          ]
        },
        {
          id: 4,
          name: 'Business',
          count: 15,
          description: 'Insights on business strategies, entrepreneurship, and digital marketing.',
          link: '/topic/business',
          recentPosts: [
            { id: 10, title: 'Digital Marketing Strategies for 2023', link: '/post/10', date: '2023-05-25' },
            { id: 11, title: 'Starting a Tech Business: A Comprehensive Guide', link: '/post/11', date: '2023-05-22' },
            { id: 12, title: 'The Future of Remote Work', link: '/post/12', date: '2023-05-20' }
          ]
        }
      ]
    };
  },
  setup() {
    const { t } = useI18n();
    const blogConfigStore = useBlogConfigStore();

    // 计算SEO相关的值
    const siteName = computed(() => blogConfigStore.config.site_name || '');
    const siteDescription = computed(() => blogConfigStore.config.site_description || '');

    // 使用useHead设置页面的meta标签
    useHead({
      title: computed(() => `专题列表 - ${siteName.value}`),
      meta: [
        {
          name: 'description',
          content: computed(() => `浏览${siteName.value}的所有专题分类，包括科技、设计、开发和商业等多个领域的精选内容。`)
        },
        {
          name: 'keywords',
          content: '专题,专题列表,技术专题,设计专题,开发专题,商业专题'
        },
        // Open Graph标签
        {
          property: 'og:title',
          content: computed(() => `专题列表 - ${siteName.value}`)
        },
        {
          property: 'og:description',
          content: computed(() => `浏览${siteName.value}的所有专题分类，包括科技、设计、开发和商业等多个领域的精选内容。`)
        },
        {
          property: 'og:type',
          content: 'website'
        },
        {
          property: 'og:url',
          content: computed(() => window.location.href)
        },
        // Twitter Card标签
        {
          name: 'twitter:card',
          content: 'summary'
        },
        {
          name: 'twitter:title',
          content: computed(() => `专题列表 - ${siteName.value}`)
        },
        {
          name: 'twitter:description',
          content: computed(() => `浏览${siteName.value}的所有专题分类，包括科技、设计、开发和商业等多个领域的精选内容。`)
        },
        // 其他重要SEO标签
        {
          name: 'robots',
          content: 'index, follow'
        }
      ],
      link: [
        {
          rel: 'canonical',
          href: computed(() => window.location.href)
        }
      ]
    });

    return {
      siteName,
      siteDescription
    };
  },
  methods: {
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.loadTopics(this.currentPage);
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.loadTopics(this.currentPage);
      }
    },
    loadTopics(page) {
      console.log(`Loading topics page: ${page}`);
      // Here you would normally fetch topics from an API
      alert(`Loading page ${page} of topics - This is a demo`);

      // 更新结构化数据
      this.$nextTick(() => {
        this.addStructuredData();
      });
    },
    // 添加结构化数据
    addStructuredData() {
      // 移除已有的结构化数据
      const existingScript = document.getElementById('topics-structured-data');
      if (existingScript) {
        existingScript.remove();
      }

      // 创建主题页结构化数据
      const structuredData = {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "headline": "专题列表",
        "description": `浏览${this.siteName}的所有专题分类，包括科技、设计、开发和商业等多个领域的精选内容。`,
        "url": window.location.href,
        "mainEntity": {
          "@type": "ItemList",
          "itemListElement": this.topics.map((topic, index) => ({
            "@type": "ListItem",
            "position": index + 1,
            "url": `${window.location.origin}${topic.link}`,
            "name": topic.name,
            "description": topic.description
          }))
        }
      };

      // 创建并添加脚本标签
      const script = document.createElement('script');
      script.id = 'topics-structured-data';
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify(structuredData);
      document.head.appendChild(script);
    }
  },
  mounted() {
    // 添加结构化数据
    this.$nextTick(() => {
      this.addStructuredData();
    });
  }
};
</script>

<style scoped>
.topics-header {
  border-bottom: 1px solid var(--border-color);
}

.topics-title {
  font-size: 1.8rem;
  color: var(--heading-color);
  margin-bottom: 0.5rem;
}

.topics-description {
  color: var(--text-color-secondary);
  margin-bottom: 1rem;
}

.topic-item {
  transition: transform var(--transition-speed);
}

.topic-item:hover {
  transform: translateY(-3px);
}

.topic-title {
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
}

.topic-title a {
  color: var(--heading-color);
  text-decoration: none;
  transition: color var(--transition-speed);
}

.topic-title a:hover {
  color: var(--primary-color-hover);
}

.topic-meta {
  font-size: 0.85rem;
  color: var(--text-color-secondary);
  margin-bottom: 0.75rem;
}

.topic-meta i {
  margin-right: 0.25rem;
}

.topic-description {
  margin-bottom: 1rem;
  color: var(--text-color);
  line-height: 1.6;
}

.topic-posts {
  border-top: 1px solid var(--border-color-light);
  padding-top: 1rem;
  margin-top: 0.5rem;
}

.posts-heading {
  font-size: 1rem;
  color: var(--heading-color);
  margin-bottom: 0.75rem;
}

.posts-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.post-link {
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
}

.post-link a {
  color: var(--primary-color);
  text-decoration: none;
  transition: color var(--transition-speed);
  flex: 1;
  margin-right: 1rem;
}

.post-link a:hover {
  color: var(--primary-color-hover);
  text-decoration: underline;
}

.post-date {
  color: var(--text-color-secondary);
  white-space: nowrap;
}

@media (max-width: 576px) {
  .post-link {
    flex-direction: column;
  }

  .post-link a {
    margin-right: 0;
    margin-bottom: 0.25rem;
  }
}
</style>