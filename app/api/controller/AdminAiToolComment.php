<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolComment;
use app\common\model\AiTool;
use app\common\model\User;
use app\common\model\Admin;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI工具评论管理控制器
 * @route('adminAiToolComment')
 */
class AdminAiToolComment extends BaseController
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
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $toolId = (int)input('tool_id', 0);
        $userId = (int)input('user_id', 0);
        $toolName = input('toolName', '');
        $minRating= input('minRating', '');
        $maxRating= input('maxRating', '');


        $userType = input('userType', '');
        $parentId = (int)input('parentId', -1);
        $status = input('status', '');
        $keyword = input('keyword', '');
        $startTime = input('startTime', 0);
        $endTime = input('endTime', 0);
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');

        // 构建查询条件
        $query = AiToolComment::alias('c')
            ->join('ai_ai_tool t', 'c.tool_id = t.id')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.username , a.username) as user_nickname');

        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('c.tool_id', $toolId);
        }

        // 用户ID筛选
        if ($userId > 0) {
            $query->where('c.user_id', $userId);
        }

        // 工具名称筛选
        if ($toolName) {
            $query->where('t.name', 'like', "%{$toolName}%");
        }
        // 评分筛选
        if ($minRating && $maxRating) {
            $query->where('c.rating', 'between', [$minRating, $maxRating]);
        }
        // 用户类型筛选
        if ($userType) {
            $query->where('c.user_type', $userType);
        }

        // 父评论ID筛选
        if ($parentId >= 0) {
            $query->where('c.parent_id', $parentId);
        }

        // 状态筛选
        if ($status) {
            $query->where('c.status', $status);
        }

        // 关键词筛选
        if ($keyword) {
            $query->where('c.content', 'like', "%{$keyword}%");
        }

        // 时间范围筛选
        if ($startTime) {
            $query->where('c.create_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('c.create_time', '<=', $endTime);
        }

        // 排序
        $allowedOrderFields = ['id', 'tool_id', 'user_id', 'user_type', 'parent_id', 'rating', 'likes', 'create_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order('c.' . $orderField, $orderType);
        } else {
            $query->order('c.create_time', 'desc');
        }

        // 分页查询
        $count = $query->count();
        $list=$query->page($page, $limit)->select();

        // 获取回复数量
        foreach ($list as &$item) {
            $item['reply_count'] = AiToolComment::where('parent_id', $item['id'])->count();
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_comment_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }

    /**
     * 获取评论详情
     * @route('info', 'get')
     */
    public function info(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        $comment = AiToolComment::alias('c')
            ->join('ai_ai_tool t', 'c.tool_id = t.id')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname')
            ->where('c.id', $id)
            ->find();

        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        // 获取父评论
        if ($comment['parent_id'] > 0) {
            $parent = AiToolComment::alias('c')
                ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
                ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
                ->field('c.id, c.content, IFNULL(u.nickname, a.nickname) as user_nickname')
                ->where('c.id', $comment['parent_id'])
                ->find();
            $comment['parent'] = $parent;
        }

        // 获取回复列表
        $replies = AiToolComment::alias('c')
            ->leftJoin('user u', 'c.user_id = u.id AND c.user_type = "user"')
            ->leftJoin('admin a', 'c.user_id = a.id AND c.user_type = "admin"')
            ->field('c.*, IFNULL(u.nickname, a.nickname) as user_nickname')
            ->where('c.parent_id', $id)
            ->order('c.create_time', 'asc')
            ->select();
        $comment['replies'] = $replies;

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_comment_info_success'), 'data' => $comment]);
    }

    /**
     * 回复评论
     * @route('reply', 'post')
     */
    public function reply(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $parentId = input('parent_id', 0);
        $content = input('content', '');

        if (!$parentId || !$content) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查父评论是否存在
        $parent = AiToolComment::find($parentId);
        if (!$parent) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        Db::startTrans();
        try {
            // 创建回复
            $comment = new AiToolComment;
            $comment->tool_id = $parent->tool_id;
            $comment->user_id = $this->userId;
            $comment->user_type = 'admin';
            $comment->parent_id = $parentId;
            $comment->content = $content;
            $comment->status = 'approved'; // 管理员回复默认通过审核
            $comment->ip = request()->ip();
            $comment->user_agent = request()->header('user-agent');
            $comment->save();

            // 更新工具评论数（只计算顶级评论）
            $this->updateToolCommentCount($parent->tool_id);

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_comment_reply', Lang::get('aiTool.reply_comment_log'), [
                'comment_id' => $comment->id,
                'parent_id' => $parentId,
                'tool_id' => $parent->tool_id,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.reply_comment_success'), 'data' => ['id' => $comment->id]]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新评论状态
     * @route('updateStatus', 'post')
     */
    public function updateStatus(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $status = input('status', '');

        if (!$id || !$status) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查状态是否有效
        $validStatus = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatus)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.invalid_status')]);
        }

        // 检查评论是否存在
        $comment = AiToolComment::find($id);
        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        Db::startTrans();
        try {
            // 更新评论状态
            $comment->status = $status;
            $comment->save();

            // 更新工具评论数和评分（只计算顶级评论）
            if ($comment->parent_id == 0) {
                $this->updateToolCommentCount($comment->tool_id);
                $this->updateToolRating($comment->tool_id);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_comment_update_status', Lang::get('aiTool.update_comment_status_log'), [
                'comment_id' => $id,
                'status' => $status,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.update_comment_status_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 批量更新评论状态
     * @route('batchUpdateStatus', 'post')
     */
    public function batchUpdateStatus(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $ids = input('ids/a', []);
        $status = input('status', '');

        if (empty($ids) || !$status) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查状态是否有效
        $validStatus = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatus)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.invalid_status')]);
        }

        // 检查评论是否存在
        $comments = AiToolComment::whereIn('id', $ids)->select();
        if (count($comments) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        // 获取涉及的工具ID
        $toolIds = [];
        $topCommentToolIds = [];
        foreach ($comments as $comment) {
            $toolIds[] = $comment->tool_id;
            if ($comment->parent_id == 0) {
                $topCommentToolIds[] = $comment->tool_id;
            }
        }
        $toolIds = array_unique($toolIds);
        $topCommentToolIds = array_unique($topCommentToolIds);

        Db::startTrans();
        try {
            // 批量更新评论状态
            AiToolComment::whereIn('id', $ids)->update(['status' => $status]);

            // 更新工具评论数和评分（只计算顶级评论）
            foreach ($topCommentToolIds as $toolId) {
                $this->updateToolCommentCount($toolId);
                $this->updateToolRating($toolId);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_comment_batch_update_status', Lang::get('aiTool.batch_update_comment_status_log'), [
                'comment_ids' => $ids,
                'status' => $status,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_update_comment_status_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 删除评论
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        $comment = AiToolComment::find($id);

        if (!$comment) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        Db::startTrans();
        try {
            // 如果是顶级评论，删除所有回复
            if ($comment->parent_id == 0) {
                AiToolComment::where('parent_id', $id)->delete();
            }

            // 删除评论
            $comment->delete();

            // 更新工具评论数和评分（只计算顶级评论）
            if ($comment->parent_id == 0) {
                $this->updateToolCommentCount($comment->tool_id);
                $this->updateToolRating($comment->tool_id);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_comment_delete', Lang::get('aiTool.delete_comment_log'), [
                'comment_id' => $id,
                'tool_id' => $comment->tool_id,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_comment_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 批量删除评论
     * @route('batchDelete', 'post')
     */
    public function batchDelete(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $ids = input('ids/a', []);

        if (empty($ids)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查评论是否存在
        $comments = AiToolComment::whereIn('id', $ids)->select();
        if (count($comments) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.comment_not_exist')]);
        }

        // 获取涉及的工具ID和顶级评论ID
        $toolIds = [];
        $topCommentIds = [];
        $topCommentToolIds = [];
        foreach ($comments as $comment) {
            $toolIds[] = $comment->tool_id;
            if ($comment->parent_id == 0) {
                $topCommentIds[] = $comment->id;
                $topCommentToolIds[] = $comment->tool_id;
            }
        }
        $toolIds = array_unique($toolIds);
        $topCommentToolIds = array_unique($topCommentToolIds);

        Db::startTrans();
        try {
            // 删除顶级评论的所有回复
            if (!empty($topCommentIds)) {
                AiToolComment::whereIn('parent_id', $topCommentIds)->delete();
            }

            // 批量删除评论
            AiToolComment::whereIn('id', $ids)->delete();

            // 更新工具评论数和评分（只计算顶级评论）
            foreach ($topCommentToolIds as $toolId) {
                $this->updateToolCommentCount($toolId);
                $this->updateToolRating($toolId);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_comment_batch_delete', Lang::get('aiTool.batch_delete_comment_log'), [
                'comment_ids' => $ids,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_delete_comment_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新工具评论数
     * @param int $toolId 工具ID
     */
    protected function updateToolCommentCount(int $toolId)
    {
        $count = AiToolComment::where('tool_id', $toolId)
            ->where('parent_id', 0)
            ->where('status', 'approved')
            ->count();
        AiTool::where('id', $toolId)->update(['comments' => $count]);
    }

    /**
     * 更新工具评分
     * @param int $toolId 工具ID
     */
    protected function updateToolRating(int $toolId)
    {
        $ratings = AiToolComment::where('tool_id', $toolId)
            ->where('parent_id', 0)
            ->where('status', 'approved')
            ->where('rating', '>', 0)
            ->column('rating');

        $count = count($ratings);
        $rating = 0;

        if ($count > 0) {
            $rating = round(array_sum($ratings) / $count, 1);
        }

        AiTool::where('id', $toolId)->update([
            'rating' => $rating,
            'rating_count' => $count
        ]);
    }

    /**
     * 检查是否登录
     * @return bool
     */
    protected function isLogin(): bool
    {
        return $this->userId > 0;
    }

    /**
     * 需要登录
     * @return Json
     */
    protected function needLogin(): Json
    {
        return json(['code' => 401, 'msg' => Lang::get('user.need_login')]);
    }


}
