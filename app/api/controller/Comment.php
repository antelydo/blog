<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;

/**
 * 前端评论控制器
 * @route('comment')
 */
class Comment extends BaseController
{


    /**
     * 构造方法
     */
    protected function initialize()
    {
      // parent::initialize();
    }

    /**
     * 获取文章评论列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $postId = input('post_id', 0);
        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $sortBy = input('sortBy', 'create_time');
        $sortOrder = input('sortOrder', 'desc');
        $isGetComLikeTotal = (int)input('is_get_com_like_total', 0); // 是否获取评论点赞总数

        // 验证排序字段是否存在
        $validSortFields = ['create_time', 'likes', 'id'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'create_time'; // 如果不是有效的排序字段，则使用默认值
        }

        // 验证排序方向
        $validSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'desc'; // 如果不是有效的排序方向，则使用默认值
        }
        $parentId = input('parent_id', '');
        $status = input('status', '');

        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 检查文章是否存在
        $post = Db::name('blog_post')
            ->where('id', $postId)
            ->where('status', 1) // 只显示已发布文章的评论
            ->find();

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        if($parentId!==''){
            $where[] = ['c.parent_id', '=', $parentId];
        }
        if($status!==''){
            $where[] = ['c.status', '=', $status];
        }
        if($postId!==''){
            $where[] = ['c.post_id', '=', $postId];
        }

        $total = Db::name('blog_comment')
            ->alias('c')
            ->where($where)
            ->count();

        $qurey = Db::name('blog_comment')
            ->alias('c')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname, IFNULL(u.avatar, a.avatar) as avatar');
        // 记录排序参数
        trace("\u8bc4论排序参数: sortBy={$sortBy}, sortOrder={$sortOrder}", 'info');

        $parentComments = $this->getPageList($qurey,$page,$limit,$where,[$sortBy=>$sortOrder]);

        // 获取子评论
            if ($parentComments) {
                // 获取所有评论 ID，包括父评论和子评论
                $allCommentIds = [];

                // 收集父评论 ID
                foreach ($parentComments['list'] as $comment) {
                    $allCommentIds[] = $comment['id'];
                }

                // 如果需要获取评论点赞总数
                $commentLikeCounts = [];
                if ($isGetComLikeTotal && !empty($allCommentIds)) {
                    // 获取评论点赞数量
                    $likeCounts = Db::name('comment_like')
                        ->field('comment_id, COUNT(*) as like_count')
                        ->where('comment_id', 'in', $allCommentIds)
                        ->group('comment_id')
                        ->select()
                        ->toArray();

                    // 将结果转换为以评论 ID 为键的数组
                    foreach ($likeCounts as $count) {
                        $commentLikeCounts[$count['comment_id']] = $count['like_count'];
                    }
                }

                //组装 访客评论
               foreach ($parentComments['list'] as $k=>$v){
                   if($v['user_id'] == 0 && $v['type'] =='guest' && $v['user_type']=='guest'){
                       $parentComments['list'][$k]['username'] = $v['user_name'];
                       $parentComments['list'][$k]['nickname'] = $v['user_name'];
                   }

                   // 添加点赞总数
                   if ($isGetComLikeTotal) {
                       $parentComments['list'][$k]['like_count'] = $commentLikeCounts[$v['id']] ?? 0;
                   }

                   //递归找子级
                   $replies = $this->getCommentsByPostId($v['post_id'], $v['id'], $isGetComLikeTotal > 0);

                   // 如果需要获取点赞总数，对子评论也进行处理
                   if ($isGetComLikeTotal && !empty($replies)) {
                       // 收集子评论 ID
                       $replyIds = [];
                       $this->collectCommentIds($replies, $replyIds);

                       if (!empty($replyIds)) {
                           // 获取子评论点赞数量
                           $replyLikeCounts = Db::name('comment_like')
                               ->field('comment_id, COUNT(*) as like_count')
                               ->where('comment_id', 'in', $replyIds)
                               ->group('comment_id')
                               ->select()
                               ->toArray();

                           // 将结果转换为以评论 ID 为键的数组
                           foreach ($replyLikeCounts as $count) {
                               $commentLikeCounts[$count['comment_id']] = $count['like_count'];
                           }

                           // 为子评论添加点赞总数
                           $this->addLikeCountsToReplies($replies, $commentLikeCounts);
                       }
                   }

                   $parentComments['list'][$k]['replies'] = $replies;
               }
            }

        return json(['code' => 200, 'msg' => Lang::get('blog.get_comment_list_successful'), 'data' =>$parentComments]);
    }

    /**
     * 发表评论
     * @route('add', 'post')
     */
    public function add(): Json
    {

        $postId = input('post_id', 0);
        $parentId = input('parent_id', 0);
        $content = input('content', '');
        $userId = input('user_id', 0);
       // $userId = $this->getUserId()??$user_id??0;
        $userType = $userId>0?$this->userType:'guest';
        $user_name =input('user_name','');
        $email =input('email','');

        //读取网站配置
        $config =$this->getWebConfig();
        $commentNeedAudit =$config['comment_need_audit'];//是否评论审核
        if(!$config['comment_enabled']){
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_enabled')]);
        }


        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        if (!$content) {
            return json(['code' => 400, 'msg' => Lang::get('blog.comment_content_required')]);
        }

        // 检查文章是否存在
        $post = Db::name('blog_post')
            ->where('id', $postId)
            ->where('status', 1) // 只能评论已发布的文章
            ->find();

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 如果有父评论，检查父评论是否存在
        if ($parentId) {
            $parentComment = Db::name('blog_comment')
                ->where('id', $parentId)
                ->where('status', 1) // 只能回复已审核的评论
                ->find();

            if (!$parentComment) {
                return json(['code' => 400, 'msg' => Lang::get('blog.comment_not_exist')]);
            }
        }

        // 获取网站配置-评论是否需要审核
        $config = Db::name('website_config')
            ->where('name', 'comment_need_audit')
            ->find();

        if ($config) {
            $commentNeedAudit = $config['value'] == 1;
        }

        $now = time();
        $data = [
            'post_id' => $postId,
            'parent_id' => $parentId,
            'content' => $content,
            'user_id' => $userId,
            'user_type' => $userType,
            'ip' => request()->ip(),
            'user_name'=>$user_name,
            'email'=>$email,
            'user_agent' => request()->header('user-agent'),
            'status' => $this->userType == 'admin' ? 1 : ($commentNeedAudit ? 0 : 1), // 管理员评论默认通过，用户评论根据配置
            'create_time' => $now,
            'update_time' => $now,
            'type'=>$userType
        ];

        Db::startTrans();
        try {
            // 添加评论
            $commentId = Db::name('blog_comment')->insertGetId($data);

            // 如果评论状态为已审核，更新文章评论数
            if ($data['status'] == 1) {
                Db::name('blog_post')
                    ->where('id', $postId)
                    ->inc('comments')
                    ->update();
            }

            Db::commit();

            // 记录用户活动
            $this->recordActivity($userId, $userType, 'blog_post_comment', Lang::get('blog.comment_post_log') . ': ' . $post['title'], [
                'comment_id' => $commentId,
                'post_id' => $postId,
                'parent_id' => $parentId,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('blog.create_comment_successful'), 'data' => [
                'need_audit' => $data['status'] == 0
            ]]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取最新评论
     * @route('new', 'get')
     */
    public function new(): Json
    {
        $limit = (int)input('limit', 5);
        $sortBy = input('sortBy', 'create_time');
        $sortOrder = input('sortOrder', 'desc');
        $page = (int)input('page', 1);
        $parent_id = input('parent_id', -1);

        $comments = Db::name('blog_comment');

        if($parent_id != -1){
            $comments->where('parent_id', $parent_id);
        }

       $list= $comments->alias('c')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, IFNULL(u.username, a.username) as username, IFNULL(u.nickname, a.nickname) as nickname, IFNULL(u.avatar, a.avatar) as avatar')
            ->where('c.status', 1)
            ->order($sortBy, $sortOrder)
            ->page($page, $limit)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => Lang::get('blog.get_comment_list_successful'), 'data' => $list]);
    }

    /**
     * 递归收集评论 ID
     * @param array $comments 评论数组
     * @param array $ids 收集的 ID 数组
     */
    protected function collectCommentIds(array $comments, array &$ids): void
    {
        foreach ($comments as $comment) {
            $ids[] = $comment['id'];

            // 如果有子评论，递归收集
            if (!empty($comment['replies'])) {
                $this->collectCommentIds($comment['replies'], $ids);
            }
        }
    }

    /**
     * 为评论回复添加点赞数量
     * @param array $comments 评论数组
     * @param array $likeCounts 点赞数量数组
     */
    protected function addLikeCountsToReplies(array &$comments, array $likeCounts): void
    {
        foreach ($comments as &$comment) {
            // 添加点赞数量
            $comment['like_count'] = $likeCounts[$comment['id']] ?? 0;

            // 如果有子评论，递归处理
            if (!empty($comment['replies'])) {
                $this->addLikeCountsToReplies($comment['replies'], $likeCounts);
            }
        }
    }
}