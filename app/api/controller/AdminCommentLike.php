<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;

/**
 * 评论点赞管理控制器
 * @route('adminCommentLike')
 */
class AdminCommentLike extends BaseController
{
    /**
     * 获取评论点赞列表
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
        $commentId = input('comment_id', 0);
        $userId = input('user_id', 0);
        $userType = input('user_type', '');
        $startTime = input('start_time', 0);
        $endTime = input('end_time', 0);
        
        $query = Db::name('comment_like')->alias('cl')
            ->leftJoin('blog_comment bc', 'cl.comment_id = bc.id')
            ->leftJoin('user u', 'cl.user_id = u.id AND cl.user_type = "user"')
            ->leftJoin('admin a', 'cl.user_id = a.id AND cl.user_type = "admin"')
            ->field('cl.*, bc.content as comment_content, bc.post_id, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname');
        
        // 构建查询条件
        $where = [];
        
        if ($commentId) {
            $where[] = ['cl.comment_id', '=', $commentId];
        }
        
        if ($userId) {
            $where[] = ['cl.user_id', '=', $userId];
        }
        
        if ($userType) {
            $where[] = ['cl.user_type', '=', $userType];
        }
        
        if ($startTime) {
            $where[] = ['cl.create_time', '>=', strtotime($startTime)];
        }
        
        if ($endTime) {
            $where[] = ['cl.create_time', '<=', strtotime($endTime . ' 23:59:59')];
        }
        
        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, $where, ['cl.create_time desc']);
        
        // 获取文章标题
        foreach ($result['list'] as &$item) {
            //根据评论表user_id 和admin id判断
            if($item['user_id']==0 &&$item['user_type']=='guest '){
                $item['username'] =$item['user_name'];
                $item['nickname'] =$item['user_name'];
            }

            if ($item['post_id']) {
                $post = Db::name('blog_post')->field('title')->where('id', $item['post_id'])->find();
                $item['post_title'] = $post ? $post['title'] : '';
            } else {
                $item['post_title'] = '';
            }
        }
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_like_list_successful'), 'data' => $result]);
    }
    
    /**
     * 删除评论点赞
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
            return json(['code' => 400, 'msg' => Lang::get('blog.like_not_exist')]);
        }
        
        // 检查点赞记录是否存在
        $like = Db::name('comment_like')->where('id', $id)->find();
        
        if (!$like) {
            return json(['code' => 400, 'msg' => Lang::get('blog.like_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('comment_like')->where('id', $id)->delete();
            
            // 更新评论点赞数
            Db::name('blog_comment')
                ->where('id', $like['comment_id'])
                ->where('likes', '>', 0)
                ->dec('likes')
                ->update();
                
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'comment_like_delete', Lang::get('blog.delete_like_log'), [
                'like_id' => $id,
                'comment_id' => $like['comment_id'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.delete_like_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取评论点赞统计
     * @route('stats', 'get')
     */
    public function stats(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        // 获取总点赞数
        $totalLikes = Db::name('comment_like')->count();
        
        // 获取今日点赞数
        $todayStart = strtotime(date('Y-m-d'));
        $todayLikes = Db::name('comment_like')
            ->where('create_time', '>=', $todayStart)
            ->count();
        
        // 获取昨日点赞数
        $yesterdayStart = $todayStart - 86400;
        $yesterdayLikes = Db::name('comment_like')
            ->where('create_time', '>=', $yesterdayStart)
            ->where('create_time', '<', $todayStart)
            ->count();
        
        // 获取本周点赞数
        $weekStart = strtotime(date('Y-m-d', strtotime('this week Monday')));
        $weekLikes = Db::name('comment_like')
            ->where('create_time', '>=', $weekStart)
            ->count();
        
        // 获取本月点赞数
        $monthStart = strtotime(date('Y-m-01'));
        $monthLikes = Db::name('comment_like')
            ->where('create_time', '>=', $monthStart)
            ->count();
        
        // 获取用户类型分布
        $userTypeStats = Db::name('comment_like')
            ->field('user_type, count(*) as count')
            ->group('user_type')
            ->select()
            ->toArray();
        
        // 获取最受欢迎的评论（点赞数最多的10条评论）
        $popularComments = Db::name('blog_comment')
            ->field('id, content, likes, post_id')
            ->where('likes', '>', 0)
            ->order('likes desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        // 获取文章标题
        foreach ($popularComments as &$comment) {
            $post = Db::name('blog_post')->field('title')->where('id', $comment['post_id'])->find();
            $comment['post_title'] = $post ? $post['title'] : '';
        }
        
        $data = [
            'total_likes' => $totalLikes,
            'today_likes' => $todayLikes,
            'yesterday_likes' => $yesterdayLikes,
            'week_likes' => $weekLikes,
            'month_likes' => $monthLikes,
            'user_type_stats' => $userTypeStats,
            'popular_comments' => $popularComments
        ];
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_like_stats_successful'), 'data' => $data]);
    }
}
