<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use think\facade\Request;
use app\common\Token;
/**
 * 文章管理控制器
 * @route('admin/post')
 */
class AdminPost extends BaseController
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();
    }

    /**
     * 获取文章列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $status = input('status', '');
        $keyword = input('keyword', '');
        $title = input('title', '');
        $page = input('page', 1);
        $limit = input('limit', 10);
        $category_id = input('category_id', 0);
        $sortBy = input('sortBy', ''); // 获取排序字段
        $sortOrder = input('sortOrder', ''); // 获取排序方式
        $is_banner_top = input('is_banner_top', default: '');//是否首页banner展示

        // 记录排序参数
        \think\facade\Log::info('文章列表排序参数: ', [
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);

        // 初始化查询构造器
        $query = Db::name('blog_post');

        // 基本条件过滤
        $baseWhere = [];
        if ($status !== '') {
            // 处理状态值，支持字符串和数字
            $statusValue = $status;

            // 将字符串状态转换为对应的数值
            if ($status === 'published') {
                $statusValue = 1;
            } elseif ($status === 'draft') {
                $statusValue = 0;
            } elseif ($status === 'pending') {
                $statusValue = 2;
            } elseif ($status === 'rejected') {
                $statusValue = -1;
            }

            $baseWhere[] = ['status', '=', intval($statusValue)];
        }

        //是否首页banner展示
        if ($is_banner_top !== '') {
            $baseWhere[] = ['is_banner_top', '=', intval($is_banner_top)];
        }

        if ($title) {
            $baseWhere[] = ['title', 'like', "%{$title}%"];
        }

        // 如果有基本条件，应用到查询
        if (!empty($baseWhere)) {
            $query->where($baseWhere);
        }

        // 关键词搜索(支持标题、内容和作者)
        if ($keyword) {
            // 先查询匹配关键词的作者ID
            $adminIds = Db::name('admin')
                ->whereLike('username|nickname', "%{$keyword}%")
                ->column('id');

            $userIds = Db::name('user')
                ->whereLike('username|nickname', "%{$keyword}%")
                ->column('id');

            // 构建复杂搜索条件
            $query->where(function($q) use ($keyword, $adminIds, $userIds) {
                // 搜索标题或内容
                $q->whereLike('title|content', "%{$keyword}%");

                // 如果找到匹配的管理员作者
                if (!empty($adminIds)) {
                    $q->whereOr(function($subq) use ($adminIds) {
                        $subq->whereIn('author_id', $adminIds)
                             ->where('author_type', '=', 'admin');
                    });
                }

                // 如果找到匹配的普通用户作者
                if (!empty($userIds)) {
                    $q->whereOr(function($subq) use ($userIds) {
                        $subq->whereIn('author_id', $userIds)
                             ->where('author_type', '=', 'user');
                    });
                }
            });
        }

        // 如果有分类筛选
        if ($category_id) {
            // 确保分类ID是整数
            $categoryIdInt = intval($category_id);

            // 查询该分类下的所有文章ID
            $postIds = Db::name('blog_post_category')
                ->where('category_id', $categoryIdInt)
                ->column('post_id');

            if (!empty($postIds)) {
                // 如果找到文章，添加到查询条件
                $query->whereIn('id', $postIds);
            } else {
                // 没有该分类下的文章，直接返回空结果
                return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => [
                    'total' => 0,
                    'list' => [],
                    'page' => $page,
                    'limit' => $limit
                ]]);
            }
        }

        // 构建排序条件
        $orderParams = ['is_top desc', 'is_recommend desc', 'create_time desc', 'id desc']; // 默认排序

        // 如果有指定排序字段和排序方式
        if ($sortBy && $sortOrder) {
            // 有效的排序字段
            $validSortFields = ['id', 'title', 'views', 'likes', 'favorites', 'comments', 'create_time', 'author.username', 'is_top', 'is_recommend', 'is_banner_top'];

            // 转换前端字段名到数据库字段名
            $dbFieldMap = [
                'id' => 'id',
                'title' => 'title',
                'views' => 'views',
                'likes' => 'likes',
                'favorites' => 'favorites',
                'comments' => 'comments',
                'create_time' => 'create_time',
                'author.username' => 'author_id', // 作者名排序，实际按author_id排序
                'is_top' => 'is_top',
                'is_recommend' => 'is_recommend',
                'is_banner_top' => 'is_banner_top'
            ];

            // 检查是否是有效的排序字段
            if (in_array($sortBy, $validSortFields)) {
                $dbField = $dbFieldMap[$sortBy];
                $direction = strtolower($sortOrder) === 'ascending' ? 'asc' : 'desc';

                // 处理特殊字段
                if ($sortBy === 'author.username') {
                    // 对作者排序需要联表查询
                    $query->alias('p')
                          ->leftJoin('admin a', 'p.author_id = a.id AND p.author_type = "admin"')
                          ->leftJoin('user u', 'p.author_id = u.id AND p.author_type = "user"')
                          ->field('p.*, IFNULL(a.username, u.username) as author_username');

                    // 更新排序字段
                    $dbField = 'author_username';
                }

                // 将自定义排序放在第一位
                array_unshift($orderParams, $dbField . ' ' . $direction);
            }
        }

        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, [], $orderParams);

        // 获取文章分类
        if ($result['list']) {
            $postIds = array_column($result['list'], 'id');
            $postCategories = Db::name('blog_post_category')
                ->alias('pc')
                ->join('blog_category c', 'pc.category_id = c.id')
                ->where('pc.post_id', 'in', $postIds)
                ->field('pc.post_id, c.id as category_id, c.name as category_name')
                ->select()
                ->toArray();

            $postCategoryMap = [];
            foreach ($postCategories as $cat) {
                if (!isset($postCategoryMap[$cat['post_id']])) {
                    $postCategoryMap[$cat['post_id']] = [];
                }
                $postCategoryMap[$cat['post_id']][] = [
                    'id' => $cat['category_id'],
                    'name' => $cat['category_name']
                ];
            }

            // 获取文章标签
            $postTags = Db::name('blog_post_tag')
                ->alias('pt')
                ->join('blog_tag t', 'pt.tag_id = t.id')
                ->where('pt.post_id', 'in', $postIds)
                ->field('pt.post_id, t.id as tag_id, t.name as tag_name')
                ->select()
                ->toArray();

            $postTagMap = [];
            foreach ($postTags as $tag) {
                if (!isset($postTagMap[$tag['post_id']])) {
                    $postTagMap[$tag['post_id']] = [];
                }
                $postTagMap[$tag['post_id']][] = [
                    'id' => $tag['tag_id'],
                    'name' => $tag['tag_name']
                ];
            }

            // 添加作者信息、分类和标签到文章数据
            foreach ($result['list'] as &$post) {
                $post['categories'] = $postCategoryMap[$post['id']] ?? [];
                $post['tags'] = $postTagMap[$post['id']] ?? [];

                // 获取作者信息
                if ($post['author_type'] == 'admin') {
                    $author = Db::name('admin')->field('id, username, nickname, avatar')->find($post['author_id']);
                } else {
                    $author = Db::name('user')->field('id, username, nickname, avatar')->find($post['author_id']);
                }

                $post['author'] = $author ?? ['id' => 0, 'username' => Lang::get('blog.unknown'), 'nickname' => Lang::get('blog.unknown')];

            }

        }

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => $result]);
    }

    /**
     * 获取文章详情
     * @route('info', 'get')
     */
    public function info(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')->find($id);

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 获取分类
        $categories = Db::name('blog_post_category')
            ->alias('pc')
            ->join('blog_category c', 'pc.category_id = c.id')
            ->where('pc.post_id', $id)
            ->field('c.id, c.name')
            ->select()
            ->toArray();

        $post['categories'] = $categories;

        // 获取标签
        $tags = Db::name('blog_post_tag')
            ->alias('pt')
            ->join('blog_tag t', 'pt.tag_id = t.id')
            ->where('pt.post_id', $id)
            ->field('t.id, t.name')
            ->select()
            ->toArray();

        $post['tags'] = $tags;

        // 获取作者信息
        if ($post['author_type'] == 'admin') {
            $author = Db::name('admin')->field('id, username, nickname, avatar')->find($post['author_id']);
        } else {
            $author = Db::name('user')->field('id, username, nickname, avatar')->find($post['author_id']);
        }

        $post['author'] = $author ?? ['id' => 0, 'username' => Lang::get('blog.unknown'), 'nickname' => Lang::get('blog.unknown')];

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_successful'), 'data' => $post]);
    }

    /**
     * 创建文章
     * @route('create', 'post')
     */
    public function create(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $title = input('title', '');
        $content = input('content', '');
        $description = input('description', '');
        $thumbnail = input('thumbnail', '');
        $status = input('status', 1);
        $is_top = input('is_top', 0);
        $is_recommend = input('is_recommend', 0);
        $sort = input('sort', 0);
        $categoryIds = input('category_id', []);
        $tagIds = input('tags', []);
        $author_type  = input('author_type ','admin');
        $author_id   = input('author_id  ','');
        $is_banner_top = input('is_banner_top', 0);


        if (!$title) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_title_required')]);
        }

        if (!$content) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_content_required')]);
        }

        $now = time();
        $postData = [
            'title' => $title,
            'content' => $content,
            'description' => $description,
            'author_id' => $author_id,
            'author_type' => $author_type,
            'thumbnail' => $thumbnail,
            'status' => (int)$status,
            'is_top' => (int)$is_top,
            'is_recommend' => (int)$is_recommend,
            'is_banner_top' => (int)$is_banner_top,
            'sort' => (int)$sort,
            'publish_time' => $status == 1 ? $now : 0,
            'create_time' => $now,
            'update_time' => $now,
        ];
        Db::startTrans();
        try {
            // 插入文章
            $postId = Db::name('blog_post')->insertGetId($postData);

            // 插入分类关联
            if ($categoryIds) {
                $categoryData = [];
                foreach ($categoryIds as $categoryId) {
                    $categoryData[] = [
                        'post_id' => $postId,
                        'category_id' => $categoryId,
                        'create_time' => $now,
                    ];
                }
                Db::name('blog_post_category')->insertAll($categoryData);
            }

            // 插入标签关联
            if ($tagIds) {
                $tagData = [];
                foreach ($tagIds as $tagId) {
                    $tagData[] = [
                        'post_id' => $postId,
                        'tag_id' => $tagId,
                        'create_time' => $now,
                    ];
                }
                Db::name('blog_post_tag')->insertAll($tagData);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_post_create', Lang::get('blog.create_post_log') . ': ' . $title, [
                'post_id' => $postId,
                'title' => $title,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('blog.create_post_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新文章
     * @route('update', 'post')
     */
    public function update(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $title = input('title', '');
        $content = input('content', '');
        $description = input('description', '');
        $thumbnail = input('thumbnail', '');
        $status = input('status', 1);
        $is_top = input('is_top', 0);
        $is_recommend = input('is_recommend', 0);
        $sort = input('sort', 0);
        $categoryIds = input('category_id', []);
        $tagIds = input('tags', []);
        $is_banner_top = input('is_banner_top', 0);

        $author_id = input('author_id','');
        $author_type = input('author_type','admin');


        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')->find($id);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        if (!$title) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_title_required')]);
        }

        if (!$content) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_content_required')]);
        }

        $now = time();
        $postData = [
            'title' => $title,
            'content' => $content,
            'author_id'=>$author_id,
            'author_type'=>$author_type,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'status' => (int)$status,
            'is_top' => (int)$is_top,
            'is_recommend' => (int)$is_recommend,
            'is_banner_top' => (int)$is_banner_top,
            'sort' => (int)$sort,
            'update_time' => $now,
        ];



        // 如果状态从草稿变为发布，则更新发布时间
        if ($post['status'] != 1 && $status == 1) {
            $postData['publish_time'] = $now;
        }

        Db::startTrans();
        try {
            // 更新文章
            Db::name('blog_post')->where('id', $id)->update($postData);

            // 删除旧的分类关联
            Db::name('blog_post_category')->where('post_id', $id)->delete();

            // 插入新的分类关联
            if ($categoryIds) {
                $categoryData = [];
                foreach ($categoryIds as $categoryId) {
                    $categoryData[] = [
                        'post_id' => $id,
                        'category_id' => $categoryId,
                         'create_time' => $now
                    ];
                }
                Db::name('blog_post_category')->insertAll($categoryData);
            }

            // 删除旧的标签关联
            Db::name('blog_post_tag')->where('post_id', $id)->delete();

            // 插入新的标签关联
            if ($tagIds) {
                $tagData = [];
                foreach ($tagIds as $tagId) {
                    $tagData[] = [
                        'post_id' => $id,
                        'tag_id' => $tagId,
                        'create_time' => $now,
                    ];
                }
                Db::name('blog_post_tag')->insertAll($tagData);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_post_update', Lang::get('blog.update_post_log') . ': ' . $title, [
                'post_id' => $id,
                'title' => $title,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('blog.update_post_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 删除文章
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')->find($id);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        Db::startTrans();
        try {
            // 删除文章
            Db::name('blog_post')->where('id', $id)->delete();

            // 删除分类关联
            Db::name('blog_post_category')->where('post_id', $id)->delete();

            // 删除标签关联
            Db::name('blog_post_tag')->where('post_id', $id)->delete();

            // 删除点赞记录
            Db::name('blog_like')->where('post_id', $id)->delete();

            // 删除评论
            Db::name('blog_comment')->where('post_id', $id)->delete();

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_post_delete', Lang::get('blog.delete_post_log') . ': ' . $post['title'], [
                'post_id' => $id,
                'title' => $post['title'],
            ]);

            return json(['code' => 200, 'msg' => Lang::get('blog.delete_post_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 置顶/取消置顶文章
     * @route('top', 'post')
     */
    public function top(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $is_top = input('is_top', default: '');

        if($is_top ==''){
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }


        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')->find($id);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $now = time();
        $result = Db::name('blog_post')
            ->where('id', $id)
            ->update([
                'is_top' => $is_top,
                'update_time' => $now,
            ]);

        if ($result) {
            // 记录操作日志
            $actionType = $is_top ? Lang::get('blog.top_post_log') : Lang::get('blog.untop_post_log');
            $this->recordActivity($this->userId, 'admin', 'blog_post_top', $actionType . ': ' . $post['title'], [
                'post_id' => $id,
                'title' => $post['title'],
                'is_top' => $is_top,
            ]);

            return json(['code' => 200, 'msg' => $is_top ? Lang::get('blog.top_post_successful') : Lang::get('blog.untop_post_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.operation_failed')]);
        }
    }

    /**
     * 推荐/取消推荐文章
     * @route('recommend', 'post')
     */
    public function recommend(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $is_recommend = input('is_recommend', default: '');


        if($is_recommend ==''){
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }


        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')->find($id);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $now = time();
        $result = Db::name('blog_post')
            ->where('id', $id)
            ->update([
                'is_recommend' => (int)$is_recommend,
                'update_time' => $now,
            ]);

        if ($result) {
            // 记录操作日志
            $actionType = $is_recommend ? Lang::get('blog.recommend_post_log') : Lang::get('blog.unrecommend_post_log');
            $this->recordActivity($this->userId, 'admin', 'blog_post_recommend', $actionType . ': ' . $post['title'], [
                'post_id' => $id,
                'title' => $post['title'],
                'is_recommend' => $is_recommend,
            ]);

            return json(['code' => 200, 'msg' => $is_recommend ? Lang::get('blog.recommend_post_successful') : Lang::get('blog.unrecommend_post_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.operation_failed')]);
        }
    }

    /**
     * 博客统计数据
     * @route('stats', 'post')
     */
    public function stats(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $statsType = input('type', 'all');
        $period = input('period', 'all'); // all, today, week, month

        // 根据时间段设置时间条件
        $timeWhere = [];
        $now = time();
        $today = strtotime(date('Y-m-d'));

        if ($period === 'today') {
            $timeWhere[] = ['create_time', '>=', $today];
        } elseif ($period === 'week') {
            $timeWhere[] = ['create_time', '>=', strtotime('-7 days', $today)];
        } elseif ($period === 'month') {
            $timeWhere[] = ['create_time', '>=', strtotime('-30 days', $today)];
        }

        // 获取基础统计数据
        $result = [
            'totalPosts' => 0,
            'publishedPosts' => 0,
            'draftPosts' => 0,
            'pendingPosts' => 0,  // 待审核文章数
            'rejectedPosts' => 0, // 已拒绝文章数
            'totalViews' => 0,
            'totalLikes' => 0,
            'totalComments' => 0,
            'todayPosts' => 0,
            'todayViews' => 0,
            'todayLikes' => 0,
            'todayComments' => 0,
        ];

        // 获取文章总数和状态分布
        $postCountResult = Db::name('blog_post')
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select()
            ->toArray();

        $totalPosts = 0;
        foreach ($postCountResult as $row) {
            $totalPosts += $row['count'];
            if ($row['status'] == 1) {
                $result['publishedPosts'] = $row['count'];
            } else if ($row['status'] == 0) {
                $result['draftPosts'] = $row['count'];
            } else if ($row['status'] == 2) {
                $result['pendingPosts'] = $row['count'];
            } else if ($row['status'] == -1) {
                $result['rejectedPosts'] = $row['count'];
            }
        }
        $result['totalPosts'] = $totalPosts;

        // 获取今日发布的文章数
        $result['todayPosts'] = Db::name('blog_post')
            ->where('create_time', '>=', $today)
            ->count();

        // 获取总浏览量、点赞数和评论数
        $result['totalViews'] = Db::name('blog_post')->sum('views');
        $result['totalLikes'] = Db::name('blog_post')->sum('likes');
        $result['totalComments'] = Db::name('blog_comment')->count();

        // 获取今日新增浏览量、点赞数和评论数
        $result['todayViews'] = Db::name('blog_post_view_log')
            ->where('create_time', '>=', $today)
            ->count();

        $result['todayLikes'] = Db::name('blog_like')
            ->where('create_time', '>=', $today)
            ->count();

        $result['todayComments'] = Db::name('blog_comment')
            ->where('create_time', '>=', $today)
            ->count();

        // 针对不同类型的统计请求，返回特定数据
        switch ($statsType) {
            case 'likes':
                // 获取点赞相关统计
                $activeUsers = Db::name('blog_like')
                    ->field('user_id, user_type')
                    ->group('user_id, user_type')
                    ->count();

                $likesPerUser = $activeUsers > 0 ? round($result['totalLikes'] / $activeUsers, 1) : 0;

                // 获取最受欢迎的文章（点赞最多）
                $popularArticles = Db::name('blog_post')
                    ->alias('p')
                    ->field('p.id, p.title, p.likes, p.views, p.create_time')
                    ->leftJoin('blog_post_category pc', 'p.id = pc.post_id')
                    ->leftJoin('blog_category c', 'pc.category_id = c.id')
                    ->where('p.likes', '>', 0)
                    ->order('p.likes', 'desc')
                    ->limit(10)
                    ->select()
                    ->toArray();

                // 获取作者信息
                foreach ($popularArticles as &$article) {
                    // 获取作者信息
                    $authorQuery = Db::name('blog_post')
                        ->where('id', $article['id'])
                        ->field('author_id, author_type')
                        ->find();

                    if ($authorQuery) {
                        if ($authorQuery['author_type'] == 'admin') {
                            $author = Db::name('admin')->field('nickname')->find($authorQuery['author_id']);
                        } else {
                            $author = Db::name('user')->field('nickname')->find($authorQuery['author_id']);
                        }
                        $article['author'] = $author ? $author['nickname'] : Lang::get('blog.unknown');
                    } else {
                        $article['author'] = Lang::get('blog.unknown');
                    }
                }

                // 计算点赞覆盖率（有点赞的文章占总文章的比例）
                $articlesWithLikes = Db::name('blog_post')
                    ->where('likes', '>', 0)
                    ->count();
                $coverageRate = $result['totalPosts'] > 0
                    ? round(($articlesWithLikes / $result['totalPosts']) * 100, 1) . '%'
                    : '0%';

                // 获取点赞最多的文章的点赞数
                $maxLikes = empty($popularArticles) ? 0 : $popularArticles[0]['likes'];

                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => [
                        'totalLikes' => $result['totalLikes'],
                        'todayLikes' => $result['todayLikes'],
                        'likesPerUser' => $likesPerUser,
                        'activeUsers' => $activeUsers,
                        'coverageRate' => $coverageRate,
                        'maxLikes' => $maxLikes,
                        'popularArticles' => $popularArticles
                    ]
                ]);
                break;

            case 'views':
                // 获取浏览量相关统计
                $avgViews = $result['totalPosts'] > 0 ? round($result['totalViews'] / $result['totalPosts'], 1) : 0;

                // 获取阅读量最高的文章
                $mostViewedArticles = Db::name('blog_post')
                    ->field('id, title, views, likes, create_time')
                    ->where('views', '>', 0)
                    ->order('views', 'desc')
                    ->limit(10)
                    ->select()
                    ->toArray();

                // 获取作者信息
                foreach ($mostViewedArticles as &$article) {
                    // 获取作者信息
                    $authorQuery = Db::name('blog_post')
                        ->where('id', $article['id'])
                        ->field('author_id, author_type')
                        ->find();

                    if ($authorQuery) {
                        if ($authorQuery['author_type'] == 'admin') {
                            $author = Db::name('admin')->field('nickname')->find($authorQuery['author_id']);
                        } else {
                            $author = Db::name('user')->field('nickname')->find($authorQuery['author_id']);
                        }
                        $article['author'] = $author ? $author['nickname'] : Lang::get('blog.unknown');
                    } else {
                        $article['author'] = Lang::get('blog.unknown');
                    }
                }

                // 获取近七天的浏览趋势
                $viewTrends = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = strtotime(date('Y-m-d', strtotime("-{$i} day")));
                    $nextDay = strtotime(date('Y-m-d', strtotime("-" . ($i - 1) . " day")));

                    $count = Db::name('blog_post_view_log')
                        ->where('create_time', '>=', $date)
                        ->where('create_time', '<', $nextDay)
                        ->count();

                    $viewTrends[] = [
                        'date' => date('m-d', $date),
                        'count' => $count
                    ];
                }

                // 获取用户类型分布
                $userTypeDistribution = Db::name('blog_post_view_log')
                    ->field('user_type, COUNT(*) as count')
                    ->group('user_type')
                    ->select()
                    ->toArray();

                $userTypeData = [];
                $totalViewLogs = 0;
                foreach ($userTypeDistribution as $item) {
                    $totalViewLogs += $item['count'];
                    $userTypeData[$item['user_type']] = $item['count'];
                }

                // 计算百分比
                $userTypePercentage = [];
                foreach ($userTypeData as $type => $count) {
                    $percentage = $totalViewLogs > 0 ? round(($count / $totalViewLogs) * 100, 1) : 0;
                    $userTypePercentage[$type] = $percentage . '%';
                }

                // 获取来源页面统计
                $referrerStats = Db::name('blog_post_view_log')
                    ->field('referer, COUNT(*) as count')
                    ->group('referer')
                    ->order('count DESC')
                    ->limit(5)
                    ->select()
                    ->toArray();

                // 格式化来源页面数据
                $referrerData = [];
                foreach ($referrerStats as $item) {
                    $domain = $item['referer'] ? parse_url($item['referer'], PHP_URL_HOST) : '直接访问';
                    if (isset($referrerData[$domain])) {
                        $referrerData[$domain] += $item['count'];
                    } else {
                        $referrerData[$domain] = $item['count'];
                    }
                }

                // 转换为数组格式
                $formattedReferrers = [];
                foreach ($referrerData as $domain => $count) {
                    $formattedReferrers[] = [
                        'source' => $domain,
                        'count' => $count,
                        'percentage' => $totalViewLogs > 0 ? round(($count / $totalViewLogs) * 100, 1) . '%' : '0%'
                    ];
                }

                // 平均浏览时长
                $avgDuration = Db::name('blog_post_view_log')
                    ->avg('view_duration');
                $formattedDuration = round($avgDuration) . ' 秒';

                // 跳出率（仅访问一篇文章的比例）
                $uniqueVisitors = Db::name('blog_post_view_log')
                    ->field('ip_address')
                    ->group('ip_address')
                    ->count();

                $singleViewVisitors = Db::name('blog_post_view_log')
                    ->field('ip_address, COUNT(DISTINCT post_id) as post_count')
                    ->group('ip_address')
                    ->having('post_count = 1')
                    ->count();

                $bounceRate = $uniqueVisitors > 0 ? round(($singleViewVisitors / $uniqueVisitors) * 100, 1) . '%' : '0%';

                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => [
                        'totalViews' => $result['totalViews'],
                        'todayViews' => $result['todayViews'],
                        'avgViews' => $avgViews,
                        'viewTrends' => $viewTrends,
                        'mostViewedArticles' => $mostViewedArticles,
                        'userTypeDistribution' => [
                            'data' => $userTypeData,
                            'percentage' => $userTypePercentage
                        ],
                        'referrerSources' => $formattedReferrers,
                        'avgDuration' => $formattedDuration,
                        'bounceRate' => $bounceRate
                    ]
                ]);
                break;

            case 'comments':
                // 获取评论相关统计
                $avgComments = $result['totalPosts'] > 0 ? round($result['totalComments'] / $result['totalPosts'], 1) : 0;

                // 获取评论最多的文章
                $mostCommentedArticles = Db::name('blog_post')
                    ->field('id, title, comments, views, likes, create_time')
                    ->where('comments', '>', 0)
                    ->order('comments', 'desc')
                    ->limit(10)
                    ->select()
                    ->toArray();

                // 获取作者信息
                foreach ($mostCommentedArticles as &$article) {
                    // 获取作者信息
                    $authorQuery = Db::name('blog_post')
                        ->where('id', $article['id'])
                        ->field('author_id, author_type')
                        ->find();

                    if ($authorQuery) {
                        if ($authorQuery['author_type'] == 'admin') {
                            $author = Db::name('admin')->field('nickname')->find($authorQuery['author_id']);
                        } else {
                            $author = Db::name('user')->field('nickname')->find($authorQuery['author_id']);
                        }
                        $article['author'] = $author ? $author['nickname'] : Lang::get('blog.unknown');
                    } else {
                        $article['author'] = Lang::get('blog.unknown');
                    }
                }

                // 获取最近的评论
                $recentComments = Db::name('blog_comment')
                    ->alias('c')
                    ->join('blog_post p', 'c.post_id = p.id')
                    ->field('c.id, c.post_id, p.title as post_title, c.content, c.user_id, c.user_type, c.create_time')
                    ->order('c.create_time', 'desc')
                    ->limit(5)
                    ->select()
                    ->toArray();

                // 获取评论用户信息
                foreach ($recentComments as &$comment) {
                    if ($comment['user_type'] == 'admin') {
                        $user = Db::name('admin')->field('nickname, avatar')->find($comment['user_id']);
                    } else {
                        $user = Db::name('user')->field('nickname, avatar')->find($comment['user_id']);
                    }
                    $comment['user_name'] = $user ? $user['nickname'] : Lang::get('blog.unknown');
                    $comment['user_avatar'] = $user ? $user['avatar'] : '';
                }

                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => [
                        'totalComments' => $result['totalComments'],
                        'todayComments' => $result['todayComments'],
                        'avgComments' => $avgComments,
                        'mostCommentedArticles' => $mostCommentedArticles,
                        'recentComments' => $recentComments
                    ]
                ]);
                break;

            case 'categories':
                // 获取分类统计
                $categoryStats = Db::name('blog_post_category')
                    ->alias('pc')
                    ->join('blog_category c', 'pc.category_id = c.id')
                    ->field('c.id, c.name, COUNT(pc.post_id) as post_count')
                    ->group('pc.category_id')
                    ->order('post_count', 'desc')
                    ->select()
                    ->toArray();

                // 获取没有分配分类的文章数
                $postsWithCategory = Db::name('blog_post_category')
                    ->field('DISTINCT post_id')
                    ->count();
                $postsWithoutCategory = $result['totalPosts'] - $postsWithCategory;

                if ($postsWithoutCategory > 0) {
                    $categoryStats[] = [
                        'id' => 0,
                        'name' => Lang::get('blog.uncategorized'),
                        'post_count' => $postsWithoutCategory
                    ];
                }

                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => [
                        'categoryStats' => $categoryStats
                    ]
                ]);
                break;

            case 'tags':
                // 获取标签统计
                $tagStats = Db::name('blog_post_tag')
                    ->alias('pt')
                    ->join('blog_tag t', 'pt.tag_id = t.id')
                    ->field('t.id, t.name, COUNT(pt.post_id) as post_count')
                    ->group('pt.tag_id')
                    ->order('post_count', 'desc')
                    ->limit(20)
                    ->select()
                    ->toArray();

                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => [
                        'tagStats' => $tagStats
                    ]
                ]);
                break;

            default:
                // 返回所有基础统计数据
                return json([
                    'code' => 200,
                    'msg' => Lang::get('blog.get_stats_successful'),
                    'data' => $result
                ]);
        }
    }

    //设置首页banner展示
    public function setBannerTop(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        $is_banner_top = input('is_banner_top', 0);
        $post_id = input('id', 0);
        if (!$post_id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        $post = Db::name('blog_post')->find($post_id);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        $updateData = [
            'is_banner_top' => $is_banner_top,
        ];
        $updateResult = Db::name('blog_post')->where('id', $post_id)->update($updateData);
        if ($updateResult) {

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_post_set_banner', Lang::get('blog.set_banner_top_log') . ': ' . $post['title'], [
                'post_id' => $post_id,
                'title' => $post['title'],
            ]);

            return json(['code' => 200, 'msg' => Lang::get('blog.set_banner_top_successful')]);
        } else {
            return json(['code' => 400, 'msg' => Lang::get('blog.set_banner_top_failed')]);
        }
    }

}