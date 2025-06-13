<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;

/**
 * 文章收藏管理控制器
 * @route('adminFavorite')
 */
class AdminFavorite extends BaseController
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
     * 获取收藏列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $postId = input('post_id', 0);
        $userId = input('user_id', 0);
        $userType = input('user_type', '');
        $startTime = input('start_time', 0);
        $endTime = input('end_time', 0);
        
        $query = Db::name('blog_favorite')->alias('f')
            ->leftJoin('blog_post p', 'f.post_id = p.id')
            ->leftJoin('user u', 'f.user_id = u.id AND f.user_type = "user"')
            ->leftJoin('admin a', 'f.user_id = a.id AND f.user_type = "admin"')
            ->field('f.*, p.title as post_title, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname');
        
        // 构建查询条件
        $where = [];
        
        if ($postId) {
            $where[] = ['f.post_id', '=', $postId];
        }
        
        if ($userId) {
            $where[] = ['f.user_id', '=', $userId];
        }
        
        if ($userType) {
            $where[] = ['f.user_type', '=', $userType];
        }
        
        if ($startTime) {
            $where[] = ['f.create_time', '>=', strtotime($startTime)];
        }
        
        if ($endTime) {
            $where[] = ['f.create_time', '<=', strtotime($endTime . ' 23:59:59')];
        }
        
        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, $where, ['f.create_time desc']);
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_favorite_list_success'), 'data' => $result]);
    }
    
    /**
     * 删除收藏
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.favorite_not_exist')]);
        }
        
        // 检查收藏记录是否存在
        $favorite = Db::name('blog_favorite')->where('id', $id)->find();
        
        if (!$favorite) {
            return json(['code' => 400, 'msg' => Lang::get('blog.favorite_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除收藏记录
            Db::name('blog_favorite')->where('id', $id)->delete();
            
            // 更新文章收藏数
            Db::name('blog_post')
                ->where('id', $favorite['post_id'])
                ->where('favorites', '>', 0)
                ->dec('favorites')
                ->update();
                
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'favorite_delete', Lang::get('blog.delete_favorite_log'), [
                'favorite_id' => $id,
                'post_id' => $favorite['post_id'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.delete_favorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取收藏统计
     * @route('stats', 'get')
     */
    public function stats(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        // 获取总收藏数
        $totalFavorites = Db::name('blog_favorite')->count();
        
        // 获取今日收藏数
        $todayStart = strtotime(date('Y-m-d'));
        $todayFavorites = Db::name('blog_favorite')
            ->where('create_time', '>=', $todayStart)
            ->count();
        
        // 获取昨日收藏数
        $yesterdayStart = $todayStart - 86400;
        $yesterdayFavorites = Db::name('blog_favorite')
            ->where('create_time', '>=', $yesterdayStart)
            ->where('create_time', '<', $todayStart)
            ->count();
        
        // 获取本周收藏数
        $weekStart = strtotime(date('Y-m-d', strtotime('this week Monday')));
        $weekFavorites = Db::name('blog_favorite')
            ->where('create_time', '>=', $weekStart)
            ->count();
        
        // 获取本月收藏数
        $monthStart = strtotime(date('Y-m-01'));
        $monthFavorites = Db::name('blog_favorite')
            ->where('create_time', '>=', $monthStart)
            ->count();
        
        // 获取用户类型分布
        $userTypeStats = Db::name('blog_favorite')
            ->field('user_type, count(*) as count')
            ->group('user_type')
            ->select()
            ->toArray();
        
        // 获取最受欢迎的文章（收藏数最多的10篇文章）
        $popularPosts = Db::name('blog_post')
            ->field('id, title, favorites')
            ->where('favorites', '>', 0)
            ->order('favorites desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        $data = [
            'total_favorites' => $totalFavorites,
            'today_favorites' => $todayFavorites,
            'yesterday_favorites' => $yesterdayFavorites,
            'week_favorites' => $weekFavorites,
            'month_favorites' => $monthFavorites,
            'user_type_stats' => $userTypeStats,
            'popular_posts' => $popularPosts
        ];
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_favorite_stats_success'), 'data' => $data]);
    }
}
