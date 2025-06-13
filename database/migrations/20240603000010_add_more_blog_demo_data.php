<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddMoreBlogDemoData extends Migrator
{
    public function up()
    {
        // 获取表前缀
        $prefix = '';
        
        // 生成管理员和用户IDs (假设已有admin和user表，ID分别为1和1)
        $adminId = 1;
        $userId = 1;
        $now = time();
        
        // ----- 1. 添加更多分类数据 (50条) -----
        $categories = [];
        $parentCategories = [0, 1, 4]; // 已有的顶级分类和二级分类的父ID
        
        for ($i = 6; $i <= 55; $i++) {
            $parentId = $parentCategories[array_rand($parentCategories)];
            $level = ($parentId == 0) ? 1 : 2;
            $path = ($parentId == 0) ? '0' : '0,'.$parentId;
            
            $categories[] = [
                'id' => $i,
                'name' => 'Category ' . $i,
                'description' => 'Description for category ' . $i,
                'parent_id' => $parentId,
                'path' => $path,
                'level' => $level,
                'sort' => $i,
                'status' => 1,
                'create_time' => $now - rand(0, 86400 * 30),
                'update_time' => $now - rand(0, 86400 * 15)
            ];
        }
        
        $this->table($prefix.'blog_category')->insert($categories)->saveData();
        
        // ----- 2. 添加更多标签数据 (50条) -----
        $tags = [];
        
        for ($i = 7; $i <= 56; $i++) {
            $tags[] = [
                'id' => $i,
                'name' => 'Tag ' . $i,
                'description' => 'Description for tag ' . $i,
                'sort' => $i,
                'status' => 1,
                'create_time' => $now - rand(0, 86400 * 30),
                'update_time' => $now - rand(0, 86400 * 15)
            ];
        }
        
        $this->table($prefix.'blog_tag')->insert($tags)->saveData();
        
        // ----- 3. 添加更多文章数据 (50条) -----
        $posts = [];
        $authorTypes = ['admin', 'user'];
        $statusOptions = [0, 1]; // 0=草稿, 1=已发布
        $isTopOptions = [0, 1]; // 0=普通, 1=置顶
        $isRecommendOptions = [0, 1]; // 0=普通, 1=推荐
        
        for ($i = 6; $i <= 55; $i++) {
            $authorType = $authorTypes[array_rand($authorTypes)];
            $authorId = ($authorType == 'admin') ? $adminId : $userId;
            $publishDate = $now - rand(86400 * 1, 86400 * 60); // 1-60天前
            $createDate = $publishDate - rand(3600, 86400); // 发布前1小时到1天
            
            $posts[] = [
                'id' => $i,
                'title' => 'Blog Post Title ' . $i,
                'content' => '<p>This is the content for blog post ' . $i . '.</p><h2>Section 1</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eget felis eget turpis variable nisi ornare.</p><h2>Section 2</h2><p>Phasellus sit amet vestibulum justo. Proin in nunc id diam variable ornare eu vel enim.</p>',
                'description' => 'This is the description for blog post ' . $i,
                'author_id' => $authorId,
                'author_type' => $authorType,
                'thumbnail' => '/static/uploads/thumbs/post-' . ($i % 10 + 1) . '.jpg',
                'views' => rand(50, 1000),
                'likes' => rand(5, 100),
                'comments' => rand(0, 30),
                'status' => $statusOptions[array_rand($statusOptions)],
                'is_top' => $isTopOptions[array_rand($isTopOptions)],
                'is_recommend' => $isRecommendOptions[array_rand($isRecommendOptions)],
                'sort' => 0,
                'publish_time' => $publishDate,
                'create_time' => $createDate,
                'update_time' => $publishDate - rand(0, 86400 * 5)
            ];
        }
        
        $this->table($prefix.'blog_post')->insert($posts)->saveData();
        
        // ----- 4. 文章分类关联 (至少50条) -----
        $postCategories = [];
        $allCategories = range(1, 55); // 所有分类ID
        
        // 为每篇新文章关联1-3个分类
        for ($i = 6; $i <= 55; $i++) {
            $numCategories = rand(1, 3);
            $postCategoriesIds = array_rand(array_flip($allCategories), $numCategories);
            
            if (!is_array($postCategoriesIds)) {
                $postCategoriesIds = [$postCategoriesIds];
            }
            
            foreach ($postCategoriesIds as $categoryId) {
                $postCategories[] = [
                    'post_id' => $i,
                    'category_id' => $categoryId,
                    'create_time' => $now
                ];
            }
        }
        
        $this->table($prefix.'blog_post_category')->insert($postCategories)->saveData();
        
        // ----- 5. 文章标签关联 (至少50条) -----
        $postTags = [];
        $allTags = range(1, 56); // 所有标签ID
        
        // 为每篇新文章关联1-5个标签
        for ($i = 6; $i <= 55; $i++) {
            $numTags = rand(1, 5);
            $postTagIds = array_rand(array_flip($allTags), $numTags);
            
            if (!is_array($postTagIds)) {
                $postTagIds = [$postTagIds];
            }
            
            foreach ($postTagIds as $tagId) {
                $postTags[] = [
                    'post_id' => $i,
                    'tag_id' => $tagId,
                    'create_time' => $now
                ];
            }
        }
        
        $this->table($prefix.'blog_post_tag')->insert($postTags)->saveData();
        
        // ----- 6. 添加更多点赞数据 (50条) -----
        $likes = [];
        $ipPool = ['127.0.0.1', '192.168.1.1', '10.0.0.1', '172.16.0.1', '8.8.8.8', '114.114.114.114'];
        
        // 创建一个额外的用户ID数组，避免与已有用户冲突
        $extraUserIds = range(2, 30); // 假设ID 2-30是新用户
        
        // 获取所有已存在的点赞组合
        $existingLikes = [];
        try {
            $existingLikesResult = $this->fetchAll("SELECT post_id, user_id, user_type FROM {$prefix}blog_like");
            foreach ($existingLikesResult as $row) {
                $key = $row['post_id'] . '-' . $row['user_id'] . '-' . $row['user_type'];
                $existingLikes[$key] = true;
            }
        } catch (\Exception $e) {
            // 如果表不存在或查询失败，就忽略
        }
        
        // 预先生成所有可能的组合，然后筛选未使用的
        $allPossibleCombinations = [];
        for ($postId = 1; $postId <= 55; $postId++) {
            foreach ($extraUserIds as $uid) {
                // 用户类型随机
                $userType = $authorTypes[array_rand($authorTypes)];
                $key = $postId . '-' . $uid . '-' . $userType;
                
                // 如果这个组合不存在于已有数据中
                if (!isset($existingLikes[$key])) {
                    $allPossibleCombinations[] = [
                        'post_id' => $postId,
                        'user_id' => $uid,
                        'user_type' => $userType,
                        'key' => $key
                    ];
                }
            }
        }
        
        // 如果有足够的组合，随机选择50个
        if (count($allPossibleCombinations) >= 50) {
            shuffle($allPossibleCombinations);
            $selectedCombinations = array_slice($allPossibleCombinations, 0, 50);
            
            foreach ($selectedCombinations as $combo) {
                $ip = $ipPool[array_rand($ipPool)];
                $createTime = $now - rand(0, 86400 * 30); // 最近30天内
                
                $likes[] = [
                    'post_id' => $combo['post_id'],
                    'user_id' => $combo['user_id'],
                    'user_type' => $combo['user_type'],
                    'ip' => $ip,
                    'create_time' => $createTime
                ];
            }
            
            if (!empty($likes)) {
                $this->table($prefix.'blog_like')->insert($likes)->saveData();
            }
        } else {
            echo "警告: 没有足够的唯一组合来生成50条点赞记录\n";
        }
        
        // ----- 7. 添加更多评论数据 (50条) -----
        $comments = [];
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 11; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36'
        ];
        
        $commentContents = [
            'Great article! Thanks for sharing.',
            'I found this very informative.',
            'I have a question about this topic. Can you elaborate more?',
            'This is exactly what I was looking for!',
            'I disagree with some points, but overall a good read.',
            'Looking forward to more content like this.',
            'Have you considered writing about related topic X?',
            'This helped me solve a problem I was having. Thank you!',
            'Interesting perspective on this subject.',
            'I shared this with my colleagues - very useful information.'
        ];
        
        // 开始ID从8，因为已有7条评论
        for ($i = 8; $i <= 57; $i++) {
            $postId = rand(1, 55); // 随机选择一篇文章
            $parentId = rand(0, 3) > 0 ? 0 : rand(1, $i - 1); // 70%是主评论，30%是回复其他评论
            $userType = $authorTypes[array_rand($authorTypes)];
            $userId = ($userType == 'admin') ? $adminId : $userId;
            $content = $commentContents[array_rand($commentContents)] . ' (Comment ' . $i . ')';
            $createTime = $now - rand(0, 86400 * 30); // 最近30天内
            
            $comments[] = [
                'id' => $i,
                'post_id' => $postId,
                'parent_id' => $parentId,
                'content' => $content,
                'user_id' => $userId,
                'user_type' => $userType,
                'likes' => rand(0, 10),
                'ip' => $ipPool[array_rand($ipPool)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'status' => rand(0, 10) > 1 ? 1 : rand(-1, 0), // 80%已审核，20%待审核或已拒绝
                'create_time' => $createTime,
                'update_time' => $createTime
            ];
        }
        
        $this->table($prefix.'blog_comment')->insert($comments)->saveData();
    }

    public function down()
    {
        // 获取表前缀
        $prefix = '';
        
        // 删除新增的数据
        $this->execute('DELETE FROM ' . $prefix . 'blog_comment WHERE id > 7');
        $this->execute('DELETE FROM ' . $prefix . 'blog_like WHERE create_time > ' . (time() - 86400 * 31));
        $this->execute('DELETE FROM ' . $prefix . 'blog_post_tag WHERE post_id > 5');
        $this->execute('DELETE FROM ' . $prefix . 'blog_post_category WHERE post_id > 5');
        $this->execute('DELETE FROM ' . $prefix . 'blog_post WHERE id > 5');
        $this->execute('DELETE FROM ' . $prefix . 'blog_tag WHERE id > 6');
        $this->execute('DELETE FROM ' . $prefix . 'blog_category WHERE id > 5');
    }
} 