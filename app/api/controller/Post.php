<?php

declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;

/**
 * 前端文章控制器
 * @route('post')
 */
class Post extends BaseController
{

    /**
     * 用户类型
     * @var string
     */
    protected string $userType;

    /**
     * 用户ID
     * @var string
     */
    private string $uuid;

    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        $this->userId = $this->getUserId();
        $this->uuid = $this->getUuid();
         parent::initialize();
    }

    /**
     * 获取文章列表
     * @route('list', 'get')
     */
    public function list(): Json
    {

        $status = input('status', '');
        $keyword = input('keyword', '');
        $title = input('title', '');
        $page = intval(input('page', 1));
        $limit = intval(input('limit', 10));
        $category_id = intval(input('category_id', 0));
        $sortBy = input('sortBy', ''); // 获取排序字段
        $sortOrder = input('sortOrder', ''); // 获取排序方式
        $is_banner_top = input('is_banner_top', default: ''); // 是否首页banner展示
        $is_top = input('is_top', default: ''); // 是否置顶
        $is_recommend = input('is_recommend', default: ''); // 是否推荐
        $tag_id = intval(input('tag_id', ''));
        $id = intval(input('id', ''));
        $is_check_like = input('is_check_like', default: 0); // 是否检查点赞状态
        $is_check_favorite = input('is_check_favorite', default: 0); // 是否检查收藏状态


        // 记录排序参数
        \think\facade\Log::info('文章列表排序参数: ', [
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);


        // 初始化查询构造器
        $query = Db::name('blog_post');


        // 获取相关文章 如有id则获取相关文章 根据当前的文章分类和标签获取相关文章
        if ($id) {
            $post_category = Db::name('blog_post_category')->where('post_id', $id)->column('category_id');
            $post_tag = Db::name('blog_post_tag')->where('post_id', $id)->column('tag_id');
            if ($post_category || $post_tag) {
                if($post_category){
                    $categoryIds = array_column($post_category, 'id');
                }
                if($post_tag){
                    $tagIds = array_column($post_tag, 'id');
                }
                $query->where('id', '<>', $id);
                $query->where('category_id', 'in', $categoryIds);
                $query->where('tag_id', 'in', $tagIds);
            }
        }

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
            $query->where(function ($q) use ($keyword, $adminIds, $userIds) {
                // 搜索标题或内容
                $q->whereLike('title|content', "%{$keyword}%");

                // 如果找到匹配的管理员作者
                if (!empty($adminIds)) {
                    $q->whereOr(function ($subq) use ($adminIds) {
                        $subq->whereIn('author_id', $adminIds)
                            ->where('author_type', '=', 'admin');
                    });
                }

                // 如果找到匹配的普通用户作者
                if (!empty($userIds)) {
                    $q->whereOr(function ($subq) use ($userIds) {
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
            $postIds = Db::name('blog_post_category')->alias('pc')
                ->join('blog_category c', 'pc.category_id = c.id')
                ->where('c.status', 1)
                ->where('category_id', $categoryIdInt)
                ->column('post_id');

            if (!empty($postIds)) {
                // 如果找到文章，添加到查询条件
                $query->whereIn('id', $postIds);
            } else {
                // 没有该分类下的文章，直接返回空结果
                $query->where('id', 0);
            }
        }

        // 如果有标签筛选
        if ($tag_id) {
            // 确保标签ID是整数
            $tagIdInt = intval($tag_id);

            // 查询该标签下的所有文章ID
            $postIds = Db::name('blog_post_tag')->alias('pt')
                ->join('blog_tag t', 'pt.tag_id = t.id')
                ->where('t.status', 1)
                ->where('tag_id', $tagIdInt)
                ->column('post_id');

            if (!empty($postIds)) {
                $query->whereIn('id', $postIds);
            } else {
                // 没有该标签下的文章，直接返回空结果
                $query->whereIn('id', 0);
            }
        }


        // 首页banner展示
        if ($is_banner_top !== '') {
            $query->where('is_banner_top', '=', intval($is_banner_top));
        }

        // 置顶
        if ($is_top !== '') {
            $query->where('is_top', '=', intval($is_top));
        }

        //推荐
        if ($is_recommend !== '') {
            $query->where('is_recommend', '=', intval($is_recommend));
        }

        // 构建排序条件
        $orderParams = []; // 默认排序

        // 如果有指定排序字段和排序方式
        if ($sortBy && $sortOrder) {

            // 有效的排序字段
            $validSortFields = ['id', 'title', 'views', 'likes', 'comments', 'create_time', 'author.username', 'is_top', 'is_recommend', 'is_banner_top'];

            // 转换前端字段名到数据库字段名
            $dbFieldMap = [
                'id' => 'id',
                'title' => 'title',
                'views' => 'views',
                'likes' => 'likes',
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

        // 获取文章列表
        if ($result['list']) {
            $postIds = array_column($result['list'], 'id');
            $postCategories = Db::name('blog_post_category')
                ->alias('pc')
                ->join('blog_category c', 'pc.category_id = c.id')
                ->where('pc.post_id', 'in', $postIds)
                ->where('c.status', 1)
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
                ->where('t.status', 1)
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

                //检测用户或者访客是否已经点赞
                if($is_check_like>0){
                    $post['is_liked'] = false;
                    if ($this->isLogin()) {
                        $liked = Db::name('blog_like')
                            ->where('post_id', $post['id'])
                            ->where('user_id', $this->userId)
                            ->where('user_type', $this->userType)
                            ->value('id');
                        $post['is_liked'] = (bool)$liked;
                    }else{
                        //检查访客是否点赞
                        $liked = Db::name('blog_like')
                            ->where('post_id', $post['id'])
                            ->where('user_id', 0)
                            ->where('user_type', 'guest')
                            ->where('uuid', $this->uuid)
                            ->value('id');
                        $post['is_liked'] = (bool)$liked;
                    }
                }

                //检测用户收藏
                if($is_check_favorite>0){
                    $post['is_favorited'] = false;
                    if ($this->isLogin()) {
                        $favorited = Db::name('blog_favorite')
                            ->where('post_id', $post['id'])
                            ->where('user_id', $this->userId)
                            ->where('user_type', $this->userType)
                            ->value('id');
                        $post['is_favorited'] = (bool)$favorited;
                    }
                }

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
     * @route('detail', 'get')
     */
    public function detail(): Json
    {
        $id = intval(input('id', 0));

        $is_get_prev_next = input('is_get_prev_next', 0);
        $is_get_comment = input('is_get_comment', 0);
        $is_check_like = input('is_check_like', 0);
        $is_check_favorite = input('is_check_favorite', default: 0); // 是否检查收藏状态

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $post = Db::name('blog_post')
            ->where('id', $id)
            //->where('status', 1) // 只显示已发布的文章
            ->find();

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 更新浏览量
        Db::name('blog_post')->where('id', $id)->inc('views')->update();

        // 记录浏览日志
        $this->recordView($id);

        // 获取分类
        $categories = Db::name('blog_post_category')
            ->alias('pc')
            ->join('blog_category c', 'pc.category_id = c.id')
            ->where('pc.post_id', $id)
            ->where('c.status', 1)
            ->field('c.id, c.name')
            ->select()
            ->toArray();

        $post['categories'] = $categories;

        // 获取标签
        $tags = Db::name('blog_post_tag')
            ->alias('pt')
            ->join('blog_tag t', 'pt.tag_id = t.id')
            ->where('pt.post_id', $id)
            ->where('t.status', 1)
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

        // 检查当前用户或者访客是否已点赞
        $post['is_liked'] =false;
        if($is_check_like>0){
            if ($this->isLogin()) {
                $liked = Db::name('blog_like')
                    ->where('post_id', $id)
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $post['is_liked'] = (bool)$liked;
            }else{
                //检查访客是否点赞
                $liked = Db::name('blog_like')
                    ->where('post_id', $id)
                    ->where('user_id', 0)
                    ->where('user_type', 'guest')
                    ->where('uuid', $this->uuid)
                    ->value('id');
                $post['is_liked'] = (bool)$liked;
            }
        }

        //检测登录用户是否收藏
        $post['is_favorited'] = false;
        if($is_check_favorite>0){
            if ($this->isLogin()) {
                $favorited = Db::name('blog_favorite')
                    ->where('post_id', $id)
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $post['is_favorited'] = (bool)$favorited;
            }
        }


        // 获取相关文章
        $relatedPosts = [];
        if ($categories) {
            $categoryIds = array_column($categories, 'id');

            // 获取同分类的其他文章
            $postIds = Db::name('blog_post_category')
                ->where('category_id', 'in', $categoryIds)
                ->where('post_id', '<>', $id)
                ->group('post_id')
                ->column('post_id');

            if ($postIds) {
                $relatedPosts = Db::name('blog_post')
                    ->where('id', 'in', $postIds)
                    ->where('status', 1)
                    ->field('id, title, thumbnail, publish_time')
                    ->order('publish_time desc')
                    ->limit(5)
                    ->select()
                    ->toArray();
            }
        }

        $post['related_posts'] = $relatedPosts;


        //获取上一篇和下一篇文章
        if ($is_get_prev_next > 0) {

            // 获取上一篇文章
            $prevPost = Db::name('blog_post')
                ->field('id, title, thumbnail, publish_time')
                ->where('id', '<', $id)
                ->where('status', 1)
                ->order('id', 'desc')
                ->find();
            // 获取下一篇文章
            $nextPost = Db::name('blog_post')
                ->field('id, title, thumbnail, publish_time')
                ->where('id', '>', $id)
                ->where('status', 1)
                ->order('id', 'asc')
                ->find();
        }
        $post['prev_post'] = $prevPost ?? [];
        $post['next_post'] = $nextPost ?? [];


        // 递归获取评论和回复
        $post['comment'] = [];
        if($is_get_comment){
            $isGetComLikeTotal = (int)input('is_get_com_like_total', 0); // 是否获取评论点赞总数
            $post['comment'] = $this->getCommentsByPostId($id, 0, $isGetComLikeTotal > 0);
        }


        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_successful'), 'data' => $post]);
    }


    /**
     * 获取推荐文章
     * @route('recommend', 'get')
     */
    public function recommend(): Json
    {
        $limit = (int)input('limit', 5);

        $recommendPosts = Db::name('blog_post')
            ->where('status', 1)
            ->where('is_recommend', 1)
            ->field('id, title, thumbnail, publish_time')
            ->order('publish_time desc')
            ->limit($limit)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => $recommendPosts]);
    }

    /**
     * 获取热门文章
     * @route('hot', 'get')
     */
    public function hot(): Json
    {
        $limit = (int)input('limit', 5);
        $hotPosts = Db::name('blog_post')
            ->where('status', 1)
            ->order('views desc, likes desc, comments desc')
            ->limit($limit)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => $hotPosts]);
    }

    /**
     * 获取首页banner展示的文章列表
     * @route('banner_top', 'get')
     */
    public function bannerTop(): Json
    {
        $limit = (int)input('limit', 5);

        $bannerTopPosts = Db::name('blog_post')
            ->where('status', 1)
            ->where('is_banner_top', 1)
            ->field('id, title, thumbnail, views, likes, comments, publish_time')
            ->order('publish_time desc')
            ->limit($limit)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => $bannerTopPosts]);
    }


    /**
     * 记录文章浏览日志
     * @param int $postId 文章ID
     */
    private function recordView(int $postId)
    {
      /*  try {*/
            // 获取客户端信息
            $userAgent = Request::header('user-agent', '');
            $ipAddress = Request::ip();
            $referer = Request::header('referer', '');

            // 准备用户数据
            $userId = $this->userId ?? 0;
            $userType = $userId>0?$this->userType: 'guest';
            if ($this->isLogin()) {
                $userId = $this->userId;
                $userType = $this->userType;
            }

            // 准备日志数据
            $viewLogData = [
                'post_id' => $postId,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'view_duration' => 0, // 初始时长为0，可以通过前端JS在用户离开页面时更新
                'create_time' => time()
            ];

            // 插入浏览日志
            Db::name('blog_post_view_log')->insert($viewLogData);
      /*  } catch (\Exception $e) {
            // 记录浏览日志失败，但不影响文章显示，所以只记录日志
            trace('记录浏览日志失败: ' . $e->getMessage(), 'error');
        }*/
    }

    /**
     * 更新文章浏览时长
     * @route('update_view_duration', 'post')
     */
    public function updateViewDuration(): Json
    {
        $postId = input('post_id', 0);
        $duration = input('duration', 0);
        $isHeartbeat = input('is_heartbeat', false);

        if (!$postId || !$duration) {
            return json(['code' => 400, 'msg' => Lang::get('blog.invalid_params')]);
        }

        try {
            // 准备查询条件
            $userId = 0;
            $userType = 'guest';
            $ipAddress = Request::ip();
            $userAgent = Request::header('user-agent', '');
            $referer = Request::header('referer', '');

            if ($this->isLogin()) {
                $userId = $this->userId;
                $userType = $this->userType;
            }

            $where = [
                ['post_id', '=', $postId],
                ['ip_address', '=', $ipAddress]
            ];

            if ($userId > 0) {
                $where[] = ['user_id', '=', $userId];
                $where[] = ['user_type', '=', $userType];
            }

            // 找到最近的一条浏览记录
            $viewLog = Db::name('blog_post_view_log')
                ->where($where)
                ->order('create_time', 'desc')
                ->find();

            if ($viewLog) {
                // 如果找到浏览记录，则更新浏览时长
                Db::name('blog_post_view_log')
                    ->where('id', $viewLog['id'])
                    ->update(['view_duration' => intval($duration)]);

                return json(['code' => 200, 'msg' => Lang::get('blog.update_view_duration_successful')]);
            }

            // 如果是心跳更新但没找到记录，或者不是心跳更新，则创建新记录
            // 如果是心跳更新但没找到记录，可能是因为数据库记录已被清理，所以创建新记录
            $viewLogData = [
                'post_id' => $postId,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'view_duration' => intval($duration),
                'create_time' => time()
            ];

            // 插入浏览日志
            Db::name('blog_post_view_log')->insert($viewLogData);

            return json(['code' => 200, 'msg' => Lang::get('blog.update_view_duration_successful')]);
        } catch (\Exception $e) {
            // 更新浏览时长失败，记录日志
            trace('更新浏览时长失败: ' . $e->getMessage(), 'error');
            return json(['code' => 500, 'msg' => Lang::get('blog.update_view_duration_failed')]);
        }
    }
}
