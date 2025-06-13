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
 * AI工具评论控制器
 * @route('toolComment')
 */
class ToolComment extends BaseController
{


    protected function initialize(): void
    {
        parent::initialize();
        $this->userType = 'user';
    }

    /**
     * 获取工具评价列表一级评价并递归找到当前一级评价下的回复
     */
    public function getToolCommentList(): Json
    {
        $toolId = intval(input('tool_id', 0));
        $parentId = input('parent_id', 0);
        $page = intval(input('page', 1));
        $limit = intval(input('limit', 10));
        $sortBy = input('sort_by', 'create_time');
        $sortOrder = input('sort_order', 'desc');
        $user_id =$this->getUserId();
        $uuid =$this->getVisitorId();
        $user_type = $user_id>0?$this->userType:'guest';

        if (!$toolId) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }
        //先找一级评价并分页
        $comments = Db::name('ai_tool_comment')
            ->where('tool_id', $toolId)
            ->where('parent_id', $parentId)
            ->where('status', 'approved')
            ->order($sortBy, $sortOrder)
            ->page($page, $limit)
            ->select()
            ->toArray();

        if($comments){
            // 递归找到当前一级评价下的回复
            foreach ($comments as &$comment) {
                //时间格式化
                $comment['create_time'] =$comment['create_time']? date('Y-m-d H:i:s', $comment['create_time']):'';
                $comment['update_time'] = $comment['update_time']? date('Y-m-d H:i:s', $comment['update_time']):'';
                //组装一级评价用户信息
                switch ($comment['user_type']){
                    case 'admin':
                        $comment['user'] = Db::name('admin')->field('id, username, nickname, avatar')->find($comment['user_id']);
                        break;
                    case 'user':
                        $comment['user'] = Db::name('user')->field('id, username, nickname, avatar')->find($comment['user_id']);
                        break;
                    case  'guest':
                        $comment['user'] = ['id' => 0, 'username' =>'匿名用户', 'nickname' => '访客', 'avatar' => ''];
                        break;
                }
                //检查当前用户或者访客是否已经店在哪
                $comment['is_liked'] = false;
                if ($this->isLogin()) {
                    $liked = Db::name('ai_tool_comment_like')
                        ->where('comment_id', $comment['id'])
                        ->where('user_id', $this->userId)
                        ->where('user_type', $this->userType)
                        ->value('id');
                    $comment['is_liked'] = (bool)$liked;
                } else {
                    $liked = Db::name('ai_tool_comment_like')
                        ->where('comment_id', $comment['id'])
                        ->where('user_id', 0)
                        ->where('user_type', 'guest')
                        ->where('uuid', $uuid)
                        ->value('id');
                    $comment['is_liked'] = (bool)$liked;
                }
                $comment['replies'] = $this->getCommentsByToolId($toolId, $comment['id'],$user_id,$uuid,$user_type);
            }
        }
        $result=[
           'list'=> $comments,
            'sort_by'=>$sortBy,
            'sort_order'=>$sortOrder,
            'page'=>$page,
            'limit'=>$limit,
        ];
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_comment_list_success'), 'data' => $result]);
    }


    /**
     * 工具评价
     * 首先是工具评价 以及评分
     * @route('add', 'post')
     */
    public function addToolCom(): Json
    {
        $toolId = input('tool_id', 0);
        $parentId = input('parent_id', 0);
        $content = input('content', '');
        $rating = input('rating', 0);
        $userId =$this->getUserId();
        $uuid = $this->getVisitorId();
       $userType = $userId>0?$this->userType:'guest';

        if (!$toolId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        if (!$content) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.comment_content_required')]);
        }
        if (!$rating) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.comment_rating_required')]);
        }

        if ($rating < 1 || $rating > 5) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.comment_rating_invalid')]);
        }

        $now = time();
        $data = [
            'tool_id' => $toolId,
            'parent_id' => $parentId,
            'content' => $content,
            'rating' => $rating,
            'user_id' => $userId,
            'user_type' => $userType,
            'ip' => request()->ip(),
            'uuid'=>$uuid,
            'likes' => 0,
            'user_agent' => request()->header('user-agent'),
            'status' => 'approved',
            'create_time' => $now,
            'update_time' => $now,
        ];

        Db::startTrans();
        try {
            // 添加评论
            $commentId = Db::name('ai_tool_comment')->insertGetId($data);

            // 更新工具评论数和评分
            $this->updateToolCommentCount($toolId);
            $this->updateToolRating($toolId);

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiTool.comment_successful'),'id'=>$commentId]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }


    /**
     * 回复工具评价
     * @route('reply', 'post')
     */
    public function toolReply(): Json
    {
        $comment_id = intval(input('comment_id', 0));
        $parentId = intval(input('parent_id', 0));
        $tool_id = intval(input('tool_id', 0));

        $content = input('content', '');
        $userId = $this->getUserId();
        $uuid = $this->getVisitorId();
        $userType = $userId>0?$this->userType:'guest';

        if (!$comment_id) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        if (!$content) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.comment_content_required')]);
        }


        $now = time();
        $data = [
            'tool_id' => $tool_id,
            'parent_id' => $comment_id,
            'content' => $content,
            'user_id' => $userId,
            'user_type' => $userType,
            'rating' => 0,
            'likes' => 0,
            'uuid'=>$uuid,
            'ip' => request()->ip(),
            'user_agent' => request()->header('user-agent'),
            'status' => 'approved',
            'create_time' => $now,
            'update_time' => $now,
        ];

        Db::startTrans();
        try {
            // 添加回复
            $replyId = Db::name('ai_tool_comment')->insertGetId($data);
            Db::commit();
            $reply = Db::name('ai_tool_comment')->find($replyId);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.reply_successful'),'reply'=>$reply,'reply_id'=>$replyId]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }

    }


    /**
     * 获取工具星评分百分比 1-5分的百分比
     * @route('getToolComRatingPercentage', 'get')
     */
    public function getToolComRatingPercentage(): Json
    {
        $toolId = input('tool_id', 0);
        if (!$toolId) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }
        $ratings = Db::name('ai_tool_comment')
            ->where('tool_id', $toolId)
            ->where('parent_id', 0)
            ->where('status', 'approved')
            ->where('rating', '>', 0)
            ->column('rating');

        $count = count($ratings);
        $ratingCounts = [0, 0, 0, 0, 0, 0]; // 对应0-5星的计数
        if ($count == 0) {
            return json(['code' => 200, 'msg' => Lang::get('aiTool.get_rating_percentage_success'), 'data' =>$ratingCounts]);
        }

        foreach ($ratings as $rating) {
            // 处理半星评分
            $ratingIndex = round($rating);
            $ratingCounts[$ratingIndex]++;
        }

        $totalRatings = array_sum($ratingCounts);
        $ratingPercentages = [];

        for ($i = 1; $i <= 5; $i++) {
            $percentage = $totalRatings > 0 ? round(($ratingCounts[$i] / $totalRatings) * 100, 1) : 0;
            $ratingPercentages[] = $percentage;
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_rating_percentage_success'), 'data' => $ratingPercentages]);

    }


    /**
     * 更新工具评论数
     * @param int $toolId 工具ID
     */
    private function updateToolCommentCount(mixed $toolId): void
    {
        $count = Db::name('ai_tool_comment')
            ->where('tool_id', $toolId)
            ->where('parent_id', 0)
            ->where('status', 'approved')
            ->count();
        Db::name('ai_tool')->where('id', $toolId)->update(['comments' => $count]);
    }

    /**
     * 更新工具评分
     * @param int $toolId 工具ID
     */
    private function updateToolRating(mixed $toolId): void
    {
        $ratings = Db::name('ai_tool_comment')
            ->where('tool_id', $toolId)
            ->where('parent_id', 0)
            ->where('status', 'approved')
            ->where('rating', '>', 0)
            ->column('rating');

        $count = count($ratings);
        $rating = 0;

        if ($count > 0) {
            $rating = round(array_sum($ratings) / $count, 1);
        }

        Db::name('ai_tool')->where('id', $toolId)->update([
            'rating' => $rating,
            'rating_count' => $count
        ]);
    }
}