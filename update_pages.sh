#!/bin/bash

# 需要修改的页面列表
PAGES=(
  "TopicsPage.vue"
  "TagPage.vue"
  "AboutPage.vue"
  "ContactPage.vue"
  "PostPage.vue"
  "TagCloudPage.vue"
  "CategoryCloud.vue"
  "PersonalCenter.vue"
)

# 遍历每个页面
for page in "${PAGES[@]}"; do
  echo "Processing $page..."
  
  # 修改页面模板开始部分
  sed -i 's/<div class="blog-container">\s*<Header \/>\s*<main class="main-content">/<MainLayout>/g' "frontend/src/views/index/$page"
  
  # 修改页面模板结束部分
  sed -i 's/<\/main>\s*<Footer \/>\s*<BackToTop \/>\s*<\/div>/<\/MainLayout>/g' "frontend/src/views/index/$page"
  
  # 修改组件导入
  sed -i 's/import Header from .\/components\/Header.vue.;\s*import Footer from .\/components\/Footer.vue.;\s*import.*BackToTop from .\/components\/BackToTop.vue.;/import MainLayout from .\/components\/MainLayout.vue;/g' "frontend/src/views/index/$page"
  
  # 修改组件注册
  sed -i 's/components: {\s*Header,\s*Footer,.*BackToTop\s*},/components: {\n    MainLayout,\n    Sidebar\n  },/g' "frontend/src/views/index/$page"
  
  echo "Completed processing $page"
done

echo "All pages updated successfully!"
