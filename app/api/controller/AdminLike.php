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
 * 文章点赞管理控制器
 * @route('admin/like')
 */
class AdminLike extends BaseController
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
     * 获取点赞列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        $userId = input('user_id', 0);
        $userType = input('user_type', '');
        $page = input('page', 1);
        $limit = input('limit', 10);
        $keyword = input('keyword', '');
        
        $where = [];
        if ($postId) {
            $where[] = ['l.post_id', '=', $postId];
        }
        
        if ($userId) {
            $where[] = ['l.user_id', '=', $userId];
        }
        
        if ($userType) {
            if($userType!='guest'){
                $where[] = ['l.user_type', '=', $userType];
            }else{
                $where[] = ['l.user_id','=',0];
    
            }
        }

        if ($keyword) {
            $where[] = ['a.username|u.username|p.title|mobile', 'like', "%{$keyword}%"];
        }



        
        $query = Db::name('blog_like')->alias('l')
            ->join('blog_post p', 'l.post_id = p.id')
            ->leftJoin('user u', 'l.user_id = u.id AND l.user_type = "user"')
            ->leftJoin('admin a', 'l.user_id = a.id AND l.user_type = "admin"')
            ->field('l.*, p.title as post_title,IFNULL(u.avatar, a.avatar) as avatar,IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname');
            
        // 使用统一的分页查询方法
        $result = $this->getPageList($query,$page,$limit,$where, ['l.create_time desc']);

        if($result){
            foreach($result['list'] as $k=>$v){
                if(!$v['user_type'] && $v['user_id']==0){
                    $result['list'][$k]['user_type']='guest';
                }
            }
        }
        
            
        return json(['code' => 200, 'msg' => Lang::get('blog.get_like_list_successful'), 'data' => $result]);
    }
    
    /**
     * 删除点赞
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId =  Request::param('post_id', 0);
        $userId =  Request::param('user_id', 0);
        $userType = Request::param('user_type', '');
        $id = Request::param('id', 0);

        //匿名用户㔘
        if( $userType =='guest'){
            $userId =0;
            $userType =NULL;
        }else{
            if (!$postId || !$userId || !$userType) {
                return json(['code' => 400, 'msg' => Lang::get('blog.invalid_params')]);
            }
        }
        
        
        $like = Db::name('blog_like')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->find();
            
        if (!$like) {
            return json(['code' => 400, 'msg' => Lang::get('blog.like_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('blog_like')
                ->where('post_id', $postId)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->delete();
                
            // 更新文章点赞数
            Db::name('blog_post')
                ->where('id', $postId)
                ->dec('likes')
                ->update();
                
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_like_delete', Lang::get('blog.delete_like_log'), [
                'post_id' => $postId,
                'user_id' => $userId,
                'user_type' => $userType,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.unlike_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 统计点赞数据
     * @route('stats', 'get')
     */
    public function stats(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        
        if ($postId) {
            // 获取指定文章的点赞统计
            $stats = Db::name('blog_like')
                ->where('post_id', $postId)
                ->field('user_type, count(*) as count')
                ->group('user_type')
                ->select()
                ->toArray();
                
            $data = [
                'total' => 0,
                'user' => 0,
                'admin' => 0,
            ];
            
            foreach ($stats as $stat) {
                $data[$stat['user_type']] = $stat['count'];
                $data['total'] += $stat['count'];
            }
            
            return json(['code' => 200, 'msg' => Lang::get('blog.get_like_stats_successful'), 'data' => $data]);
        } else {
            // 获取总体点赞统计
            $totalLikes = Db::name('blog_like')->count();
            $userLikes = Db::name('blog_like')->where('user_type', 'user')->count();
            $adminLikes = Db::name('blog_like')->where('user_type', 'admin')->count();
            
            // 获取点赞最多的文章
            $topPosts = Db::name('blog_post')
                ->field('id, title, likes')
                ->where('likes', '>', 0)
                ->order('likes desc')
                ->limit(10)
                ->select()
                ->toArray();
                
            return json(['code' => 200, 'msg' => Lang::get('blog.get_like_stats_successful'), 'data' => [
                'total_likes' => $totalLikes,
                'user_likes' => $userLikes,
                'admin_likes' => $adminLikes,
                'top_posts' => $topPosts,
            ]]);
        }
    }
}