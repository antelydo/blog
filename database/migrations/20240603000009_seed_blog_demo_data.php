<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SeedBlogDemoData extends Migrator
{
    public function up()
    {
        // 获取表前缀
        $prefix ='';
        
        // 生成管理员和用户IDs (假设已有admin和user表，ID分别为1和1)
        $adminId = 1;
        $userId = 1;
        $now = time();
        
        // 插入分类数据
        $categories = [
            [
                'id' => 1,
                'name' => 'Technology',
                'description' => 'All about technology, programming, and gadgets',
                'parent_id' => 0,
                'path' => '0',
                'level' => 1,
                'sort' => 1,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 2,
                'name' => 'Web Development',
                'description' => 'Web development tutorials and news',
                'parent_id' => 1,
                'path' => '0,1',
                'level' => 2,
                'sort' => 1,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 3,
                'name' => 'Mobile Development',
                'description' => 'Mobile app development for iOS and Android',
                'parent_id' => 1,
                'path' => '0,1',
                'level' => 2,
                'sort' => 2,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 4,
                'name' => 'Lifestyle',
                'description' => 'Articles about lifestyle, health and wellness',
                'parent_id' => 0,
                'path' => '0',
                'level' => 1,
                'sort' => 2,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 5,
                'name' => 'Travel',
                'description' => 'Travel guides and experiences',
                'parent_id' => 4,
                'path' => '0,4',
                'level' => 2,
                'sort' => 1,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ]
        ];
        
        $this->table($prefix.'blog_category')->insert($categories)->saveData();
        
        // 插入标签数据
        $tags = [
            [
                'id' => 1,
                'name' => 'PHP',
                'description' => 'PHP programming language',
                'sort' => 1,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 2,
                'name' => 'JavaScript',
                'description' => 'JavaScript programming language',
                'sort' => 2,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 3,
                'name' => 'ThinkPHP',
                'description' => 'ThinkPHP framework',
                'sort' => 3,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 4,
                'name' => 'Vue.js',
                'description' => 'Vue.js framework',
                'sort' => 4,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 5,
                'name' => 'Mobile Apps',
                'description' => 'Mobile application development',
                'sort' => 5,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ],
            [
                'id' => 6,
                'name' => 'Travel Tips',
                'description' => 'Tips for travelers',
                'sort' => 6,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now
            ]
        ];
        
        $this->table($prefix.'blog_tag')->insert($tags)->saveData();
        
        // 插入文章数据
        $posts = [
            [
                'id' => 1,
                'title' => 'Getting Started with ThinkPHP 6',
                'content' => '<p>ThinkPHP is a fast, compatible, simple, light and rational PHP development framework. This article will guide you through the process of setting up your first ThinkPHP application.</p><h2>Installation</h2><p>You can install ThinkPHP via Composer:</p><pre><code>composer create-project topthink/think tp</code></pre><h2>Basic Concepts</h2><p>ThinkPHP follows the MVC pattern to organize your code...</p>',
                'description' => 'A beginner-friendly guide to ThinkPHP 6 framework',
                'author_id' => $adminId,
                'author_type' => 'admin',
                'thumbnail' => '/static/uploads/thumbs/thinkphp.jpg',
                'views' => 521,
                'likes' => 48,
                'comments' => 12,
                'status' => 1,
                'is_top' => 1,
                'is_recommend' => 1,
                'sort' => 0,
                'publish_time' => $now - 86400 * 15, // 15 days ago
                'create_time' => $now - 86400 * 16,
                'update_time' => $now - 86400 * 14
            ],
            [
                'id' => 2,
                'title' => 'Building Modern SPAs with Vue.js',
                'content' => '<p>Vue.js is a progressive JavaScript framework for building user interfaces. This article explores how to build single-page applications with Vue.js.</p><h2>Introduction to Vue.js</h2><p>Vue.js is designed from the ground up to be incrementally adoptable...</p><h2>Setting Up a Vue Project</h2><p>To create a new Vue project, you can use the Vue CLI:</p><pre><code>npm install -g @vue/cli\nvue create my-project</code></pre>',
                'description' => 'Learn how to build single-page applications using Vue.js',
                'author_id' => $adminId,
                'author_type' => 'admin',
                'thumbnail' => '/static/uploads/thumbs/vuejs.jpg',
                'views' => 382,
                'likes' => 37,
                'comments' => 8,
                'status' => 1,
                'is_top' => 0,
                'is_recommend' => 1,
                'sort' => 0,
                'publish_time' => $now - 86400 * 10, // 10 days ago
                'create_time' => $now - 86400 * 11,
                'update_time' => $now - 86400 * 10
            ],
            [
                'id' => 3,
                'title' => 'Best Practices for Mobile App Development',
                'content' => '<p>Mobile app development comes with its own set of challenges and best practices. In this article, we\'ll explore key considerations for building high-quality mobile applications.</p><h2>User Experience Design</h2><p>The mobile user experience is fundamentally different from desktop...</p><h2>Performance Optimization</h2><p>Mobile devices have limited resources, making performance optimization critical...</p>',
                'description' => 'Essential best practices for developing mobile applications',
                'author_id' => $userId,
                'author_type' => 'user',
                'thumbnail' => '/static/uploads/thumbs/mobile-dev.jpg',
                'views' => 276,
                'likes' => 29,
                'comments' => 6,
                'status' => 1,
                'is_top' => 0,
                'is_recommend' => 1,
                'sort' => 0,
                'publish_time' => $now - 86400 * 7, // 7 days ago
                'create_time' => $now - 86400 * 8,
                'update_time' => $now - 86400 * 7
            ],
            [
                'id' => 4,
                'title' => 'Exploring Southeast Asia: A Traveler\'s Guide',
                'content' => '<p>Southeast Asia is a traveler\'s paradise with its rich culture, delicious food, and stunning landscapes. This guide will help you plan your Southeast Asian adventure.</p><h2>Thailand</h2><p>Known for its beautiful beaches, ornate temples, and vibrant street life...</p><h2>Vietnam</h2><p>A country of breathtaking natural beauty and complex history...</p><h2>Cambodia</h2><p>Home to the magnificent Angkor Wat and friendly locals...</p>',
                'description' => 'A comprehensive guide to traveling in Southeast Asia',
                'author_id' => $userId,
                'author_type' => 'user',
                'thumbnail' => '/static/uploads/thumbs/southeast-asia.jpg',
                'views' => 419,
                'likes' => 42,
                'comments' => 15,
                'status' => 1,
                'is_top' => 0,
                'is_recommend' => 1,
                'sort' => 0,
                'publish_time' => $now - 86400 * 5, // 5 days ago
                'create_time' => $now - 86400 * 6,
                'update_time' => $now - 86400 * 5
            ],
            [
                'id' => 5,
                'title' => 'JavaScript ES6 Features You Should Know',
                'content' => '<p>ECMAScript 6 (ES6) introduced many new features to JavaScript that have transformed how we write modern JavaScript code. This article covers the most important ES6 features you should be using.</p><h2>Arrow Functions</h2><p>Arrow functions provide a concise syntax for writing functions...</p><h2>Destructuring Assignment</h2><p>The destructuring assignment syntax makes it possible to unpack values from arrays or properties from objects...</p><h2>Template Literals</h2><p>Template literals provide an easy way to create multiline strings and perform string interpolation...</p>',
                'description' => 'Essential ES6 features for modern JavaScript development',
                'author_id' => $adminId,
                'author_type' => 'admin',
                'thumbnail' => '/static/uploads/thumbs/javascript-es6.jpg',
                'views' => 305,
                'likes' => 31,
                'comments' => 9,
                'status' => 1,
                'is_top' => 0,
                'is_recommend' => 0,
                'sort' => 0,
                'publish_time' => $now - 86400 * 3, // 3 days ago
                'create_time' => $now - 86400 * 4,
                'update_time' => $now - 86400 * 3
            ]
        ];
        
        $this->table($prefix.'blog_post')->insert($posts)->saveData();
        
        // 插入文章分类关联数据
        $postCategories = [
            ['post_id' => 1, 'category_id' => 2, 'create_time' => $now],
            ['post_id' => 2, 'category_id' => 2, 'create_time' => $now],
            ['post_id' => 3, 'category_id' => 3, 'create_time' => $now],
            ['post_id' => 4, 'category_id' => 5, 'create_time' => $now],
            ['post_id' => 5, 'category_id' => 2, 'create_time' => $now]
        ];
        
        $this->table($prefix.'blog_post_category')->insert($postCategories)->saveData();
        
        // 插入文章标签关联数据
        $postTags = [
            ['post_id' => 1, 'tag_id' => 1, 'create_time' => $now],
            ['post_id' => 1, 'tag_id' => 3, 'create_time' => $now],
            ['post_id' => 2, 'tag_id' => 2, 'create_time' => $now],
            ['post_id' => 2, 'tag_id' => 4, 'create_time' => $now],
            ['post_id' => 3, 'tag_id' => 5, 'create_time' => $now],
            ['post_id' => 4, 'tag_id' => 6, 'create_time' => $now],
            ['post_id' => 5, 'tag_id' => 2, 'create_time' => $now]
        ];
        
        $this->table($prefix.'blog_post_tag')->insert($postTags)->saveData();
        
        // 插入点赞数据
        $likes = [
            ['post_id' => 1, 'user_id' => $adminId, 'user_type' => 'admin', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 14],
            ['post_id' => 1, 'user_id' => $userId, 'user_type' => 'user', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 13],
            ['post_id' => 2, 'user_id' => $adminId, 'user_type' => 'admin', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 9],
            ['post_id' => 3, 'user_id' => $adminId, 'user_type' => 'admin', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 6],
            ['post_id' => 4, 'user_id' => $adminId, 'user_type' => 'admin', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 4],
            ['post_id' => 5, 'user_id' => $userId, 'user_type' => 'user', 'ip' => '127.0.0.1', 'create_time' => $now - 86400 * 2]
        ];
        
        $this->table($prefix.'blog_like')->insert($likes)->saveData();
        
        // 插入评论数据
        $comments = [
            [
                'id' => 1,
                'post_id' => 1,
                'parent_id' => 0,
                'content' => 'Great introduction to ThinkPHP 6! This helped me get started with my project. Looking forward to more articles in this series.',
                'user_id' => $userId,
                'user_type' => 'user',
                'likes' => 3,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 14,
                'update_time' => $now - 86400 * 14
            ],
            [
                'id' => 2,
                'post_id' => 1,
                'parent_id' => 1,
                'content' => 'Thank you for your feedback! I\'m planning to write more tutorials on ThinkPHP soon.',
                'user_id' => $adminId,
                'user_type' => 'admin',
                'likes' => 1,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 13,
                'update_time' => $now - 86400 * 13
            ],
            [
                'id' => 3,
                'post_id' => 2,
                'parent_id' => 0,
                'content' => 'Vue.js is definitely my favorite JavaScript framework. The learning curve is gentle and the documentation is excellent.',
                'user_id' => $userId,
                'user_type' => 'user',
                'likes' => 2,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 9,
                'update_time' => $now - 86400 * 9
            ],
            [
                'id' => 4,
                'post_id' => 3,
                'parent_id' => 0,
                'content' => 'As a mobile developer, I agree with all these practices. Performance optimization is especially crucial for delivering a good user experience.',
                'user_id' => $adminId,
                'user_type' => 'admin',
                'likes' => 4,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 6,
                'update_time' => $now - 86400 * 6
            ],
            [
                'id' => 5,
                'post_id' => 4,
                'parent_id' => 0,
                'content' => 'I visited Vietnam last year and it was amazing! The food is incredible and the landscapes are breathtaking. Definitely recommend Halong Bay.',
                'user_id' => $adminId,
                'user_type' => 'admin',
                'likes' => 5,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 4,
                'update_time' => $now - 86400 * 4
            ],
            [
                'id' => 6,
                'post_id' => 4,
                'parent_id' => 5,
                'content' => 'Halong Bay is definitely on my bucket list! Did you also visit Thailand during your trip?',
                'user_id' => $userId,
                'user_type' => 'user',
                'likes' => 2,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 3,
                'update_time' => $now - 86400 * 3
            ],
            [
                'id' => 7,
                'post_id' => 5,
                'parent_id' => 0,
                'content' => 'Arrow functions are such a game-changer! They make the code so much cleaner and more readable.',
                'user_id' => $userId,
                'user_type' => 'user',
                'likes' => 1,
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'status' => 1,
                'create_time' => $now - 86400 * 2,
                'update_time' => $now - 86400 * 2
            ]
        ];
        
        $this->table($prefix.'blog_comment')->insert($comments)->saveData();
    }

    public function down()
    {
        // 获取表前缀
        $prefix = '';
        
        // 清空表数据
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_comment');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_like');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_post_tag');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_post_category');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_post');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_tag');
        $this->execute('TRUNCATE TABLE ' . $prefix . 'blog_category');
    }
} 