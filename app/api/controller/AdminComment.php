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
 * 文章评论管理控制器
 * @route('admin/comment')
 */
class AdminComment extends BaseController
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
     * 获取评论列表
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
        $status = input('status', '');
        $keyword = input('keyword', '');
        $page= input('page', 1);
        $limit = input('limit', 10);

        $where = [];
        if ($postId) {
            $where[] = ['c.post_id', '=', $postId];
        }
        
        if ($userId) {
            $where[] = ['c.user_id', '=', $userId];
        }
        
        if ($userType) {
            $where[] = ['c.user_type', '=', $userType];
        }
        
        if ($status !== '') {
            $where[] = ['c.status', '=', intval($status)];
        }
        
        if ($keyword) {
            $where[] = ['c.content', 'like', "%{$keyword}%"];
        }
        
        $query = Db::name('blog_comment')->alias('c')
            ->join('blog_post p', 'c.post_id = p.id')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, p.title as post_title, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname, IFNULL(u.avatar, a.avatar) as avatar');
            
        // 使用统一的分页查询方法
        $result = $this->getPageList($query,$page,$limit,$where,['c.create_time desc']);
            
        // 查询父级评论信息
        foreach ($result['list'] as &$item) {
            if ($item['parent_id'] > 0) {
                $parent = Db::name('blog_comment')->alias('c')
                    ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
                    ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
                    ->field('c.id, c.content, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname')
                    ->where('c.id', $item['parent_id'])
                    ->find();
                    
                $item['parent'] = $parent ?: null;
            } else {
                $item['parent'] = null;
            }
        }
            
        return json(['code' => 200, 'msg' => Lang::get('blog.get_comment_list_successful'), 'data' => $result]);
    }
    
    /**
     * 更新评论状态
     * @route('updateStatus', 'post')
     */
    public function updateStatus(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        $status = input('status', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        $comment = Db::name('blog_comment')->find($id);
        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        $result = Db::name('blog_comment')
            ->where('id', $id)
            ->update([
                'status' => (int)$status,
                'update_time' => time(),
            ]);
            
        if ($result) {
            // 更新文章评论数
            if ($status == 1 && $comment['status'] != 1) {
                // 评论从非审核通过变为审核通过，文章评论数+1
                Db::name('blog_post')
                    ->where('id', $comment['post_id'])
                    ->inc('comments')
                    ->update();
            } else if ($status != 1 && $comment['status'] == 1) {
                // 评论从审核通过变为非审核通过，文章评论数-1
                Db::name('blog_post')
                    ->where('id', $comment['post_id'])
                    ->where('comments', '>', 0)
                    ->dec('comments')
                    ->update();
            }
            
            // 记录操作日志
            $statusText = $status == 1 ? Lang::get('blog.status_approved') : ($status == 0 ? Lang::get('blog.status_pending') : Lang::get('blog.status_rejected'));
            $this->recordActivity($this->userId, 'admin', 'blog_comment_update_status', Lang::get('blog.update_comment_status_log') . $statusText, [
                'comment_id' => $id,
                'status' => $status,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.update_comment_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.update_comment_failed')]);
        }
    }
    
    /**
     * 删除评论
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        $comment = Db::name('blog_comment')->find($id);
        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除评论及子评论
            Db::name('blog_comment')->where('id', $id)->delete();
            Db::name('blog_comment')->where('parent_id', $id)->delete();
            
            // 如果评论状态为已审核，更新文章评论数
            if ($comment['status'] == 1) {
                Db::name('blog_post')
                    ->where('id', $comment['post_id'])
                    ->where('comments', '>', 0)
                    ->dec('comments')
                    ->update();
            }
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_comment_delete', Lang::get('blog.delete_comment_log'), [
                'comment_id' => $id,
                'post_id' => $comment['post_id'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.delete_comment_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 回复评论
     * @route('reply', 'post')
     */
    public function reply(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $postId = input('post_id', 0);
        $parentId = input('parent_id', 0);
        $content = input('content', '');

        
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        if (!$content) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_content_required')]);
        }
        
        // 检查文章是否存在
        $post = Db::name('blog_post')->find($postId);
        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }
        
        // 如果有父评论，检查父评论是否存在
        if ($parentId) {
            $parentComment = Db::name('blog_comment')->find($parentId);
            if (!$parentComment) {
                return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
            }
        }
        
        $now = time();
        $data = [
            'post_id' => $postId,
            'parent_id' => $parentId,
            'content' => $content,
            'user_id' => $this->userId,
            'user_type' => 'admin',
            'type'=>'admin',
            'ip' => request()->ip(),
            'user_agent' => request()->header('user-agent'),
            'status' => 1, // 管理员评论默认通过审核
            'create_time' => $now,
            'update_time' => $now,
        ];
        
        Db::startTrans();
        try {
            // 添加评论
            $commentId = Db::name('blog_comment')->insertGetId($data);
            
            // 更新文章评论数
            Db::name('blog_post')
                ->where('id', $postId)
                ->inc('comments')
                ->update();
                
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_comment_create', Lang::get('blog.create_comment_log'), [
                'comment_id' => $commentId,
                'post_id' => $postId,
                'parent_id' => $parentId,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.create_comment_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    
}