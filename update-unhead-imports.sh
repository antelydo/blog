#!/bin/bash

# Files to update
FILES=(
  "frontend/src/views/index/TagPage.vue"
  "frontend/src/views/index/SearchPage.vue"
  "frontend/src/views/index/PostPage.vue"
  "frontend/src/views/index/LinksPage.vue"
  "frontend/src/views/index/ContactPage.vue"
  "frontend/src/views/index/CategoryPage.vue"
  "frontend/src/views/index/AboutPage.vue"
)

# For each file, replace the import
for file in "${FILES[@]}"; do
  echo "Updating $file..."
  sed -i 's/import { useHead } from '\''@unhead\/vue'\'';/import { useHead } from '\''unhead'\'';/g' "$file"
done

echo "All files updated." 