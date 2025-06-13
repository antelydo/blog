/**
 * SEO utilities for enhancing website visibility and discoverability
 */
import { computed } from 'vue';
import { useBlogConfigStore } from '@/stores/blogConfig';

/**
 * Creates base SEO meta tags for all pages
 *
 * @param {Object} options Configuration options
 * @param {Function} options.title Computed function that returns the page title
 * @param {Function} options.description Computed function that returns the page description
 * @param {String|Function} options.type Content type (default: 'website')
 * @param {String|Function} options.image Optional image URL for social media
 * @param {String|Function} options.keywords Optional keywords for SEO
 * @param {String} options.twitterCard Type of Twitter card (default: 'summary')
 * @returns {Object} SEO metadata configuration for useHead
 */
export function useBaseSEO(options) {
  const blogConfigStore = useBlogConfigStore();
  const siteName = computed(() => blogConfigStore.config.site_name || '');
  const siteUrl = computed(() => blogConfigStore.config.site_url || window.location.origin);

  // Default type is website
  const contentType = options.type || 'website';
  // Default Twitter card is summary
  const twitterCardType = options.twitterCard || 'summary';

  return {
    title: options.title,
    meta: [
      {
        name: 'description',
        content: options.description
      },
      ...(options.keywords ? [
        {
          name: 'keywords',
          content: options.keywords
        }
      ] : []),
      // Open Graph tags
      {
        property: 'og:title',
        content: options.title
      },
      {
        property: 'og:description',
        content: options.description
      },
      {
        property: 'og:type',
        content: contentType
      },
      {
        property: 'og:url',
        content: computed(() => window.location.href)
      },
      ...(options.image ? [
        {
          property: 'og:image',
          content: options.image
        }
      ] : []),
      {
        property: 'og:site_name',
        content: siteName
      },
      // Twitter Card tags
      {
        name: 'twitter:card',
        content: twitterCardType
      },
      {
        name: 'twitter:title',
        content: options.title
      },
      {
        name: 'twitter:description',
        content: options.description
      },
      ...(options.image ? [
        {
          name: 'twitter:image',
          content: options.image
        }
      ] : []),
      // Additional SEO tags
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
  };
}

/**
 * Creates article-specific SEO meta tags
 *
 * @param {Object} options Configuration options
 * @param {Object} options.post Computed function that returns the post object
 * @param {String|Function} options.authorName Author name or function that returns it
 * @param {String|Function} options.publishDate Published date in ISO format
 * @param {String|Function} options.modifiedDate Modified date in ISO format
 * @returns {Object} Article SEO metadata for useHead
 */
export function useArticleSEO(options) {
  const baseSEO = useBaseSEO({
    title: options.title,
    description: options.description,
    type: 'article',
    image: options.image,
    keywords: options.keywords,
    twitterCard: 'summary_large_image'
  });

  // Add article-specific metadata
  baseSEO.meta.push(
    {
      name: 'author',
      content: options.authorName
    },
    {
      property: 'article:published_time',
      content: options.publishDate
    },
    {
      property: 'article:modified_time',
      content: options.modifiedDate
    }
  );

  // Add structured data for articles
  baseSEO.script = [
    {
      type: 'application/ld+json',
      children: computed(() => {
        if (!options.post.value) return '';

        try {
          // Create a safe, non-reactive object with only the properties we need
          const blogConfigStore = useBlogConfigStore();
          const siteName = blogConfigStore.config.site_name || '';

          // Extract post data safely
          const post = options.post.value;

          // Article structured data - using primitive values only
          const safeStructuredData = {
            "@context": "https://schema.org",
            "@type": "BlogPosting",
            "headline": String(post.title || ''),
            "description": String(post.description || post.excerpt || ''),
            "image": post.thumbnail ? String(post.thumbnail) : '',
            "datePublished": typeof options.publishDate === 'function' ? String(options.publishDate.value || '') : String(options.publishDate || ''),
            "dateModified": typeof options.modifiedDate === 'function' ? String(options.modifiedDate.value || '') : String(options.modifiedDate || ''),
            "author": {
              "@type": "Person",
              "name": typeof options.authorName === 'function' ? String(options.authorName.value || "Anonymous") : String(options.authorName || "Anonymous")
            },
            "publisher": {
              "@type": "Organization",
              "name": String(siteName),
              "logo": {
                "@type": "ImageObject",
                "url": document.querySelector('link[rel="icon"]')?.href || ''
              }
            },
            "mainEntityOfPage": {
              "@type": "WebPage",
              "@id": window.location.href
            }
          };

          // Add category info if available - safely extract primitive values
          if (post.category && post.category.name) {
            safeStructuredData.articleSection = String(post.category.name);
          }

          // Add tags if available - safely extract primitive values
          if (post.tags && Array.isArray(post.tags) && post.tags.length > 0) {
            // Extract only the name property from each tag
            const tagNames = [];
            for (let i = 0; i < post.tags.length; i++) {
              if (post.tags[i] && post.tags[i].name) {
                tagNames.push(String(post.tags[i].name));
              }
            }
            if (tagNames.length > 0) {
              safeStructuredData.keywords = tagNames.join(',');
            }
          }

          // Stringify the safe data structure
          return JSON.stringify(safeStructuredData);
        } catch (error) {
          console.error('Error creating article structured data:', error);
          return '{"@context":"https://schema.org","@type":"BlogPosting"}';
        }
      })
    }
  ];

  return baseSEO;
}

/**
 * Creates collection page (category, tag, archive) SEO meta tags
 *
 * @param {Object} options Configuration options
 * @param {String|Function} options.title Page title
 * @param {String|Function} options.description Page description
 * @param {String|Function} options.type Collection type (e.g., "CategoryPage", "TagPage")
 * @param {Object} options.collection Collection data object (category or tag)
 * @returns {Object} Collection page SEO metadata for useHead
 */
export function useCollectionSEO(options) {
  const baseSEO = useBaseSEO({
    title: options.title,
    description: options.description,
    keywords: options.keywords
  });

  // Add structured data for collection pages
  baseSEO.script = [
    {
      type: 'application/ld+json',
      children: computed(() => {
        if (!options.collection.value) return '';

        // Create a safe, non-reactive object with only the properties we need
        const safeStructuredData = {
          "@context": "https://schema.org",
          "@type": "CollectionPage",
          "headline": typeof options.title === 'function' ? String(options.title.value || '') : String(options.title || ''),
          "description": typeof options.description === 'function' ? String(options.description.value || '') : String(options.description || ''),
          "url": window.location.href
        };

        // If we need any properties from the collection, extract them safely
        try {
          const collection = options.collection.value;
          if (collection && typeof collection === 'object') {
            // Add any collection-specific properties we need
            if (collection.name) {
              safeStructuredData.name = String(collection.name);
            }

            if (collection.post_count) {
              safeStructuredData.itemCount = Number(collection.post_count);
            }
          }
        } catch (e) {
          console.warn('Could not extract collection properties:', e);
        }

        // Use a try-catch to handle any stringification errors
        try {
          return JSON.stringify(safeStructuredData);
        } catch (error) {
          console.error('Error stringifying collection structured data:', error);
          return '{"@context":"https://schema.org","@type":"CollectionPage"}';
        }
      })
    }
  ];

  return baseSEO;
}

/**
 * Helper to generate ISO date string from date string
 *
 * @param {String} dateString Date string
 * @returns {String} ISO date string or empty string if invalid
 */
export function formatISODate(dateString) {
  if (!dateString) return '';

  try {
    const date = new Date(dateString);
    return date.toISOString();
  } catch (e) {
    console.error('Error formatting ISO date:', e);
    return '';
  }
}

/**
 * Creates a safe object from potentially reactive Vue objects
 * This function is used to extract only the data we need without circular references
 *
 * @param {Object} obj - The object to extract data from
 * @param {Array} props - Array of property names to extract
 * @returns {Object} A safe object with only the specified properties
 */
function createSafeObject(obj, props) {
  if (!obj || typeof obj !== 'object') return {};

  const result = {};

  for (const prop of props) {
    try {
      // Handle nested properties with dot notation
      if (prop.includes('.')) {
        const parts = prop.split('.');
        let current = obj;
        let valid = true;

        // Navigate through the object hierarchy
        for (let i = 0; i < parts.length - 1; i++) {
          if (current && typeof current === 'object' && current[parts[i]]) {
            current = current[parts[i]];
          } else {
            valid = false;
            break;
          }
        }

        // If we successfully navigated, get the final property
        if (valid && current && typeof current === 'object') {
          const finalProp = parts[parts.length - 1];
          if (current[finalProp] !== undefined) {
            // Convert to primitive type to avoid circular references
            const value = current[finalProp];
            if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
              result[prop] = value;
            } else if (value === null) {
              result[prop] = null;
            } else if (Array.isArray(value)) {
              result[prop] = '[Array]';
            } else if (typeof value === 'object') {
              result[prop] = '[Object]';
            }
          }
        }
      } else {
        // Handle direct properties
        if (obj[prop] !== undefined) {
          // Convert to primitive type to avoid circular references
          const value = obj[prop];
          if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
            result[prop] = value;
          } else if (value === null) {
            result[prop] = null;
          } else if (Array.isArray(value)) {
            result[prop] = '[Array]';
          } else if (typeof value === 'object') {
            result[prop] = '[Object]';
          }
        }
      }
    } catch (e) {
      console.warn(`Error extracting property ${prop}:`, e);
    }
  }

  return result;
}