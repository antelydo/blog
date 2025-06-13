<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;

/**
 * 评论点赞控制器
 * @route('commentLike')
 */
class CommentLike extends BaseController
{
    /**
     * 获取访客UUID
     * @return string
     */
    protected function getUuid(): string
    {
        $uuid = Request::param('uuid', '');
        if (empty($uuid)) {
            $uuid = Request::header('X-Visitor-ID', '');
        }
        return $uuid;
    }
    
    /**
     * 评论点赞
     * @route('like', 'post')
     */
    public function like(): Json
    {
        $commentId = input('comment_id', 0);
        $uuid = $this->getUuid();
        $userId = $this->userId ?? 0;
        
        if (!$commentId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        // 检查评论是否存在
        $comment = Db::name('blog_comment')
            ->where('id', $commentId)
            ->where('status', 1) // 只能点赞已审核的评论
            ->find();
            
        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        // 检查是否已点赞
        $query = Db::name('comment_like');
        if ($userId > 0) {
            // 登录用户
            $liked = $query->where('comment_id', $commentId)
                ->where('user_id', $userId)
                ->where('user_type', $this->userType)
                ->find();
        } else {
            // 访客
            $liked = $query->where('comment_id', $commentId)
                ->where('user_id', 0)
                ->where('user_type', 'guest')
                ->where('uuid', $uuid)
                ->find();
        }
        
        if ($liked) {
            return json(['code' => 400, 'msg' => Lang::get('blog.already_liked')]);
        }
        
        Db::startTrans();
        try {
            // 添加点赞记录
            $like = [
                'comment_id' => $commentId,
                'ip' => Request::ip(),
                'user_id' => $userId,
                'create_time' => time(),
                'user_type' => $userId > 0 ? $this->userType : 'guest',
                'uuid' => $userId > 0 ? '' : $uuid,
            ];
            
            $likeId = Db::name('comment_like')->insertGetId($like);
            
            // 更新评论点赞数
            Db::name('blog_comment')
                ->where('id', $commentId)
                ->inc('likes')
                ->update();
                
            Db::commit();
            
            return json(['code' => 200, 'msg' => Lang::get('blog.like_successful'), 'data' => $likeId]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 取消评论点赞
     * @route('unlike', 'post')
     */
    public function unlike(): Json
    {
        $commentId = input('comment_id', 0);
        $uuid = $this->getUuid();
        $userId = $this->userId ?? 0;
        
        if (!$commentId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        // 检查评论是否存在
        $comment = Db::name('blog_comment')
            ->where('id', $commentId)
            ->where('status', 1) // 只能操作已审核的评论
            ->find();
            
        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        // 查询点赞记录
        $query = Db::name('comment_like');
        if ($userId > 0) {
            // 登录用户
            $query->where('user_id', $userId)
                ->where('user_type', $this->userType)
                ->where('comment_id', $commentId);
        } else {
            // 访客
            $query->where('user_id', 0)
                ->where('uuid', $uuid)
                ->where('user_type', 'guest')
                ->where('comment_id', $commentId);
        }
        
        Db::startTrans();
        try {
            // 删除点赞记录
            $deleted = $query->delete();
            
            if ($deleted) {
                // 更新评论点赞数
                Db::name('blog_comment')
                    ->where('id', $commentId)
                    ->where('likes', '>', 0) // 防止点赞数变为负数
                    ->dec('likes')
                    ->update();
            }
            
            Db::commit();
            
            return json(['code' => 200, 'msg' => Lang::get('blog.unlike_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取评论点赞状态
     * @route('status', 'post')
     */
    public function status(): Json
    {
        $commentIds = input('comment_ids', '');
        $uuid = $this->getUuid();
        $userId = $this->userId ?? 0;
        
        if (!$commentIds) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        $commentIdArray = explode(',', $commentIds);
        $query = Db::name('comment_like');
        
        if ($userId > 0) {
            // 登录用户
            $liked = $query->where('comment_id', 'in', $commentIdArray)
                ->where('user_id', $userId)
                ->where('user_type', $this->userType)
                ->select()
                ->toArray();
        } else {
            // 访客
            $liked = $query->where('comment_id', 'in', $commentIdArray)
                ->where('user_id', 0)
                ->where('user_type', 'guest')
                ->where('uuid', $uuid)
                ->select()
                ->toArray();
        }
        
        return json(['code' => 200, 'msg' => 'success', 'data' => $liked]);
    }
}
