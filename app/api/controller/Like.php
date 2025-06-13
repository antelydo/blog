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
 * 前端点赞控制器
 * @route('like')
 */
class Like extends BaseController
{    

 /**
     * 用户类型
     * @var string
     */
    protected string $userType;
    
    /**
     * 构造方法
     */
    protected function initialize(): void
    {
        $this->userType = 'user';
        parent::initialize();
    }

    /**
     * 点赞文章
     * @route('add', 'post')
     */
    public function add(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 检查文章是否存在
        $post = Db::name('blog_post')
            ->where('id', $postId)
            ->where('status', 1) // 只能点赞已发布的文章
            ->find();
            
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 检查是否已点赞
        $liked = Db::name('blog_like')
            ->where('post_id', $postId)
            ->where('user_id', $this->userId)
            ->where('user_type', $this->userType)
            ->find();
            
        if ($liked) {
            return json(['code' => 400, 'msg' => Lang::get('blog.already_liked')]);
        }
        
        Db::startTrans();
        try {
            // 添加点赞记录
            Db::name('blog_like')->insert([
                'post_id' => $postId,
                'user_id' => $this->userId,
                'user_type' => $this->userType,
                'ip' => request()->ip(),
                'create_time' => time(),
            ]);
            
            // 更新文章点赞数
            Db::name('blog_post')
                ->where('id', $postId)
                ->inc('likes')
                ->update();
                
            Db::commit();
            
            // 记录用户活动
            $this->recordActivity($this->userId, $this->userType, 'blog_post_like', Lang::get('blog.like_post_log') . ': ' . $post['title'], [
                'post_id' => $postId,
                'title' => $post['title'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.like_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 取消点赞
     * @route('cancel', 'post')
     */
    public function cancel(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 检查文章是否存在
        $post = Db::name('blog_post')->find($postId);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 检查是否已点赞
        $liked = Db::name('blog_like')
            ->where('post_id', $postId)
            ->where('user_id', $this->userId)
            ->where('user_type', $this->userType)
            ->find();
            
        if (!$liked) {
            return json(['code' => 200, 'msg' => Lang::get('blog.unlike_successful')]);
        }
        
        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('blog_like')
                ->where('post_id', $postId)
                ->where('user_id', $this->userId)
                ->where('user_type', $this->userType)
                ->delete();
                
            // 更新文章点赞数
            Db::name('blog_post')
                ->where('id', $postId)
                ->where('likes', '>', 0)
                ->dec('likes')
                ->update();
                
            Db::commit();
            
            // 记录用户活动
            $this->recordActivity($this->userId, $this->userType, 'blog_post_unlike', Lang::get('blog.unlike_post_log') . ': ' . $post['title'], [
                'post_id' => $postId,
                'title' => $post['title'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.unlike_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 检查是否已点赞
     * @route('check', 'get')
     */
    public function check(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 检查是否已点赞
        $liked = Db::name('blog_like')
            ->where('post_id', $postId)
            ->where('user_id', $this->userId)
            ->where('user_type', $this->userType)
            ->find();
            
        return json(['code' => 200, 'msg' => 'success', 'data' => [
            'liked' => (bool)$liked
        ]]);
    }


    /**
     * 获取点赞状态  登录情况
     * @route('likeStatus', 'post')
     */
    public function likeStatus(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $postId = input('post_ids', '');
        $userId = $this->userId;

        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        $postIds = explode(',', $postId);
        $liked = Db::name('blog_like')
            ->where('post_id', 'in', $postIds)
            ->where('user_id', $userId)
            ->where('user_type', $this->userType)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => 'success', 'data' => $liked]);
    }

    /**
     * 获取点赞状态  未登录情况
     * @route('guestLikeStatus', 'post')
     */
    public function guestLikeStatus(): Json
    {

        $postId = input('post_ids', '');
        $uuid = input('uuid', '');
        if (!$postId || !$uuid) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        $liked = Db::name('blog_like')
            ->where('post_id', 'in', $postId)
            ->where('uuid', $uuid)
            ->where('user_type', $this->userType)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => 'success', 'data' => $liked]);
    }

    /**
     * 文章取消点赞 包含用户与访客
     * @route('unlikePost', 'post')
     */
    public function unlikePost()
    {
        $postId = input('post_id', 0);
        $uuid =$this->getUuid();
        $userId = $this->userId;
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        $query =Db::name('blog_like');
        if($userId>0){
            //用户
            $query->where('user_id',$userId)->where('user_type',$this->userType)->where('post_id',$postId);
        }else{
            //访客
            $query->where('user_id',0)->where('uuid',$uuid)->where('user_type','guest')->where('post_id',$postId);
        }
        $result =$query->delete();
        if($result){
            //当前该文章点赞数减少1
            Db::name('blog_post')->where('id',$postId)->dec('likes')->update();
            return json(['code' => 200, 'msg' => 'success', 'data' => $result]);
        }else{
             return  json(['code' => 400, 'msg' => 'fail', 'data' => $result]);
        }

    }

    /**
     * 文章点赞 包含用户与访客
     * @route('likePost', 'post')
     */
    public function likePost(): Json
    {
        $postId = input('post_id', 0);
        $uuid =$this->getUuid();
        $userId = $this->userId??0;
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

            $link =[
                    'post_id'=>$postId,
                    'ip'=>Request::ip(),
                    'user_id'=>$userId,
                    'create_time'=>time(),
                    'user_type'=>$userId>0?$this->userType:'guest',
                    'uuid'=>$userId>0?'':$uuid,
            ];
        $linkId=db::name('blog_like')->insert($link);
        if($linkId>0){
            //当前该文章点赞数增加1
            Db::name('blog_post')->where('id',$postId)->inc('likes')->update();
            return json(['code' => 200, 'msg' => 'success', 'data' => $linkId]);
        }else{
            return json(['code' => 500, 'msg' => 'error', 'data' => $linkId]);
        }
    }

}