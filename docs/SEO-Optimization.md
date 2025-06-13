# SEO Optimization Documentation

## Overview

This document outlines the SEO optimization strategy implemented across the website to improve search engine visibility, social media sharing, and overall online presence.

## Implemented SEO Enhancements

### 1. Centralized SEO Utilities

We've created a centralized SEO utility (`/frontend/src/utils/seo.js`) that provides consistent SEO implementation across all pages. This ensures:

- Consistent meta tag structure
- Standard schema.org structured data
- Proper Open Graph and Twitter Card integration
- Dynamic canonical URLs

### 2. Page-Specific SEO Components

Different page types now use specialized SEO configurations:

- **Articles/Posts** - Rich article metadata with author info, published dates
- **Category/Tag Pages** - Collection-type schema and appropriate metadata
- **Home Page** - Website schema with search action

### 3. Meta Tags Implementation

All pages now include essential meta tags:

- Title tags with proper site name appending
- Meta descriptions tailored to page content
- Relevant keywords based on page content
- Robots directives for proper crawling

### 4. Structured Data

We've implemented schema.org structured data across different page types:

- **BlogPosting** schema for articles
- **CollectionPage** schema for category/tag pages
- **WebSite** schema for the homepage

### 5. Social Media Optimization

All pages now support:

- **Open Graph** tags for Facebook, LinkedIn, etc.
- **Twitter Card** tags for Twitter sharing
- Dynamic image selection based on page content

## Implementation Details

### SEO Utility Functions

The SEO utilities provide three main functions:

1. `useBaseSEO()` - Base SEO implementation for all page types
2. `useArticleSEO()` - Enhanced SEO for article/post pages
3. `useCollectionSEO()` - Special SEO for category, tag, and other collection pages

### Integration with Vue 3

The SEO implementation leverages Vue 3's Composition API and the `@unhead/vue` package:

- Uses computed properties for dynamic metadata
- Reactively updates when page data changes
- Works with Vue 3's template refs for data binding

## Usage Examples

### Basic Page SEO

```javascript
import { useBaseSEO } from '@/utils/seo';
import { useHead } from '@unhead/vue';

// Inside setup()
const title = computed(() => `Page Title - ${siteName.value}`);
const description = computed(() => 'Page description here');

useHead(useBaseSEO({
  title,
  description,
  keywords: 'keyword1,keyword2',
  image: pageImage
}));
```

### Article SEO

```javascript
import { useArticleSEO } from '@/utils/seo';

// Inside setup()
useHead(useArticleSEO({
  title: computed(() => `${post.value.title} - ${siteName.value}`),
  description: computed(() => post.value.excerpt),
  authorName: computed(() => post.value.author.name),
  publishDate: computed(() => formatISODate(post.value.publish_date)),
  modifiedDate: computed(() => formatISODate(post.value.update_date)),
  post // Ref to the post object
}));
```

## Best Practices

To maintain good SEO across the site:

1. Always use the appropriate SEO utility function based on page type
2. Ensure all page content loads with proper metadata
3. Keep titles under 60 characters
4. Keep descriptions between 120-160 characters
5. Use appropriate image sizes for social media sharing
6. Ensure canonical URLs are properly set for pagination

## Future Enhancements

Potential future SEO improvements:

1. Implement breadcrumb structured data
2. Add FAQ schema to appropriate pages
3. Implement AMP support for mobile
4. Add hreflang tags for multi-language support
5. Implement JSON-LD for local business info (if applicable) 