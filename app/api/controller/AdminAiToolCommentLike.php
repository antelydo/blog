<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use think\facade\Request;

/**
 * 管理后台工具评价点赞控制器
 * @route('adminAiToolCommentLike')
 */
class AdminAiToolCommentLike extends BaseController
{
    protected function initialize(): void
    {
        $this->userType = 'admin';
        parent::initialize();

        // 检查管理员登录状态
        if (!$this->isLogin()) {
            echo json_encode(['code' => 401, 'msg' => Lang::get('auth.not_logged_in')]);
            exit;
        }
    }

    /**
     * 检查是否登录
     * @return bool
     */
    protected function isLogin(): bool
    {
        return !empty($this->userId);
    }

    // 使用BaseController中的recordActivity方法

    /**
     * 获取评价点赞列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $commentId = input('comment_id', 0);
        $userId = input('user_id', 0);
        $userType = input('user_type', '');
        $startTime = input('start_time', 0);
        $endTime = input('end_time', 0);
        $ip = input('ip', '');
        $keyword = input('keyword', '');
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');
        $uuid = input('uuid', '');

        // 构建查询条件
        $query = Db::name('ai_tool_comment_like')
            ->alias('l')
            ->join('ai_ai_tool_comment c', 'l.comment_id = c.id')
            ->join('ai_ai_tool t', 'c.tool_id = t.id')
            ->leftJoin('user u', 'l.user_id = u.id AND l.user_type = "user"')
            ->leftJoin('admin a', 'l.user_id = a.id AND l.user_type = "admin"')
            ->field('l.*, c.content as comment_content, t.name as tool_name, IFNULL(u.username, a.username) as user_nickname');

        // 评价ID筛选
        if ($commentId) {
            $query->where('l.comment_id', $commentId);
        }

        // 用户ID筛选
        if ($userId) {
            $query->where('l.user_id', $userId);
        }

        // 用户类型筛选
        if ($userType) {
            $query->where('l.user_type', $userType);
        }

        // IP筛选
        if ($ip) {
            $query->where('l.ip', 'like', "%{$ip}%");
        }

        // 时间范围筛选
        if ($startTime) {
            $query->where('l.create_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('l.create_time', '<=', $endTime);
        }

        // 关键词筛选
        if ($keyword) {
            $query->where('c.content', 'like', "%{$keyword}%");
        }
        //uuid筛选
        if ($uuid) {
            $query->where('l.uuid', $uuid);
        }

        // 排序
        $allowedOrderFields = ['id', 'comment_id', 'user_id', 'create_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order('l.' . $orderField, $orderType);
        } else {
            $query->order('l.create_time', 'desc');
        }

        // 分页查询
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        $list = $list->toArray();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['user_nickname'] = $v['user_nickname'] ?? '匿名用户';
            }
        }

        return json(['code' => 200, 'msg' => Lang::get('aiToolCommentLike.get_like_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);

    }

    /**
     * 删除评价点赞记录
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiToolCommentLike.params_error')]);
        }

        // 查询点赞记录
        $like = Db::name('ai_tool_comment_like')
            ->where('id', $id)
            ->find();

        if (!$like) {
            return json(['code' => 400, 'msg' => Lang::get('aiToolCommentLike.like_not_exist')]);
        }

        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('ai_tool_comment_like')
                ->where('id', $id)
                ->delete();

            // 更新评价点赞数
            Db::name('ai_tool_comment')
                ->where('id', $like['comment_id'])
                ->where('likes', '>', 0) // 防止点赞数变为负数
                ->dec('likes')
                ->update();

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'delete_comment_like', Lang::get('aiToolCommentLike.delete_comment_like_log'), [
                'id' => $id,
                'comment_id' => $like['comment_id']
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiToolCommentLike.delete_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取点赞统计数据
     * @route('statistics', 'get')
     */
    public function statistics(): Json
    {
        // 总点赞数
        $totalLikes = Db::name('ai_tool_comment_like')->count();

        // 今日新增点赞数
        $todayStart = strtotime(date('Y-m-d'));
        $todayLikes = Db::name('ai_tool_comment_like')
            ->where('create_time', '>=', $todayStart)
            ->count();

        // 用户类型分布
        $userTypeDistribution = Db::name('ai_tool_comment_like')
            ->field('user_type, COUNT(*) as count')
            ->group('user_type')
            ->select()
            ->toArray();

        // 点赞最多的评价
        $mostLikedComments = Db::name('ai_tool_comment')
            ->where('likes', '>', 0)
            ->field('id, content, likes')
            ->order('likes', 'desc')
            ->limit(5)
            ->select()
            ->toArray();

        // 点赞用户数
        $likeUserCount = Db::name('ai_tool_comment_like')
            ->where('user_id', '>', 0)
            ->field('user_id, user_type')
            ->group('user_id, user_type')
            ->count();

        // 点赞访客数
        $likeGuestCount = Db::name('ai_tool_comment_like')
            ->where('user_id', 0)
            ->field('uuid')
            ->group('uuid')
            ->count();

        $data = [
            'total_likes' => $totalLikes,
            'today_likes' => $todayLikes,
            'user_type_distribution' => $userTypeDistribution,
            'most_liked_comments' => $mostLikedComments,
            'like_user_count' => $likeUserCount,
            'like_guest_count' => $likeGuestCount
        ];

        return json(['code' => 200, 'data' => $data]);
    }

    /**
     * 获取评价点赞详情
     * @route('info', 'get')
     */
    public function info(): Json
    {
        $id = intval(input('id', 0));

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiToolCommentLike.params_error')]);
        }

        // 查询点赞记录
        $like = Db::name('ai_tool_comment_like')
            ->alias('l')
            ->join('ai_ai_tool_comment c', 'l.comment_id = c.id')
            ->leftJoin('user u', 'l.user_id = u.id AND l.user_type = "user"')
            ->leftJoin('admin a', 'l.user_id = a.id AND l.user_type = "admin"')
            ->field('l.*, c.content as comment_content, c.tool_id, IFNULL(u.username, a.username) as user_nickname')
            ->where('l.id', $id)
            ->find();

        if (!$like) {
            return json(['code' => 404, 'msg' => Lang::get('aiToolCommentLike.like_not_exist')]);
        }

        // 获取工具信息
        if ($like['tool_id']) {
            $tool = Db::name('ai_tool')
                ->where('id', $like['tool_id'])
                ->field('name')
                ->find();
            $like['tool_name'] = $tool ? $tool['name'] : '';
        } else {
            $like['tool_name'] = '';
        }

        $like['user_nickname'] = $like['user_nickname'] ?? '匿名用户';
        // 格式化时间
        $like['create_time_text'] = date('Y-m-d H:i:s', $like['create_time']);
        $like['user_type_text'] = Lang::get('aiToolCommentLike.user_type_' . $like['user_type']);

        return json(['code' => 200, 'data' => $like]);
    }

    /**
     * 批量删除评价点赞记录
     * @route('batchDelete', 'post')
     */
    public function batchDelete(): Json
    {
        $ids = input('ids', []);

        if (empty($ids)) {
            return json(['code' => 400, 'msg' => Lang::get('aiToolCommentLike.params_error')]);
        }

        // 查询点赞记录
        $likes = Db::name('ai_tool_comment_like')
            ->whereIn('id', $ids)
            ->select()
            ->toArray();

        if (empty($likes)) {
            return json(['code' => 400, 'msg' => Lang::get('aiToolCommentLike.like_not_exist')]);
        }

        // 获取评价ID列表
        $commentIds = array_column($likes, 'comment_id');
        $commentLikeCounts = [];

        // 统计每个评价的点赞数
        foreach ($likes as $like) {
            if (!isset($commentLikeCounts[$like['comment_id']])) {
                $commentLikeCounts[$like['comment_id']] = 0;
            }
            $commentLikeCounts[$like['comment_id']]++;
        }

        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('ai_tool_comment_like')
                ->whereIn('id', $ids)
                ->delete();

            // 更新评价点赞数
            foreach ($commentLikeCounts as $commentId => $count) {
                Db::name('ai_tool_comment')
                    ->where('id', $commentId)
                    ->where('likes', '>=', $count) // 防止点赞数变为负数
                    ->dec('likes', $count)
                    ->update();
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'batch_delete_comment_like', Lang::get('aiToolCommentLike.batch_delete_comment_like_log'), [
                'ids' => $ids,
                'comment_ids' => $commentIds
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiToolCommentLike.batch_delete_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
}
