<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use think\facade\Request;

/**
 * 工具评价点赞控制器
 * @route('ToolCommentLike')
 */
class ToolCommentLike extends BaseController
{
    protected function initialize(): void
    {
        $this->userType = 'user';
        parent::initialize();
    }

    // 使用BaseController中的getVisitorId方法

    // 使用BaseController中的getUserId方法

    /**
     * 点赞工具评价
     * @route('like', 'post')
     */
    public function like(): Json
    {
        $commentId = input('comment_id', 0);
        $userId = $this->getUserId();
        $uuid = $this->getVisitorId();
        $userType = $this->userType;

        if (!$commentId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.comment_not_exist')]);
        }

        // 检查评价是否存在
        $comment = Db::name('ai_tool_comment')
            ->where('id', $commentId)
            ->where('status', 'approved') // 只能点赞已审核的评价
            ->find();

        if (!$comment) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.comment_not_exist')]);
        }

        // 构建查询条件
        $where = [
            'comment_id' => $commentId
        ];

        // 根据用户类型设置不同的查询条件
        if ($userId > 0) {
            // 已登录用户
            $where['user_id'] = $userId;
            $where['user_type'] = $userType;
        } else {
            // 访客
            $where['uuid'] = $uuid;
            $where['user_type'] = 'guest';
        }

        // 检查是否已点赞
        $liked = Db::name('ai_tool_comment_like')
            ->where($where)
            ->find();

        if ($liked) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.already_liked')]);
        }

        Db::startTrans();
        try {
            // 添加点赞记录
            $data = [
                'comment_id' => $commentId,
                'user_id' => $userId,
                'user_type' => $userId > 0 ? $userType : 'guest',
                'uuid' => $userId > 0 ? '' : $uuid,
                'ip' => Request::ip(),
                'user_agent' => Request::header('user-agent'),
                'create_time' => time()
            ];

            Db::name('ai_tool_comment_like')->insert($data);

            // 更新评价点赞数
            Db::name('ai_tool_comment')
                ->where('id', $commentId)
                ->inc('likes')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiToolCommentLike.like_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 取消点赞工具评价
     * @route('unlike', 'post')
     */
    public function unlike(): Json
    {
        $commentId = input('comment_id', 0);
        $userId = $this->getUserId();
        $uuid = $this->getVisitorId();
        $userType = $this->userType;

        if (!$commentId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.comment_not_exist')]);
        }

        // 检查评价是否存在
        $comment = Db::name('ai_tool_comment')
            ->where('id', $commentId)
            ->where('status', 'approved') // 只能操作已审核的评价
            ->find();

        if (!$comment) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.comment_not_exist')]);
        }

        // 构建查询条件
        $where = [
            'comment_id' => $commentId
        ];

        // 根据用户类型设置不同的查询条件
        if ($userId > 0) {
            // 已登录用户
            $where['user_id'] = $userId;
            $where['user_type'] = $userType;
        } else {
            // 访客
            $where['uuid'] = $uuid;
            $where['user_type'] = 'guest';
        }

        // 检查是否已点赞
        $liked = Db::name('ai_tool_comment_like')
            ->where($where)
            ->find();

        if (!$liked) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.not_liked')]);
        }

        Db::startTrans();
        try {
            // 删除点赞记录
            Db::name('ai_tool_comment_like')
                ->where($where)
                ->delete();

            // 更新评价点赞数
            Db::name('ai_tool_comment')
                ->where('id', $commentId)
                ->where('likes', '>', 0) // 防止点赞数变为负数
                ->dec('likes')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiToolCommentLike.unlike_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户点赞状态
     * @route('status', 'post')
     */
    public function status(): Json
    {
        $commentIds = input('comment_ids', []);
        $userId = $this->getUserId();
        $uuid = $this->getVisitorId();
        $userType = $this->userType;

        if (empty($commentIds)) {
            return json(['code' => 100400, 'msg' => Lang::get('aiToolCommentLike.params_error')]);
        }

        // 构建查询条件
        $where = [
            'comment_id' => $commentIds
        ];

        // 根据用户类型设置不同的查询条件
        if ($userId > 0) {
            // 已登录用户
            $where['user_id'] = $userId;
            $where['user_type'] = $userType;
        } else {
            // 访客
            $where['uuid'] = $uuid;
            $where['user_type'] = 'guest';
        }

        // 查询点赞记录
        $likes = Db::name('ai_tool_comment_like')
            ->where($where)
            ->column('comment_id');

        // 构建结果
        $result = [];
        foreach ($commentIds as $commentId) {
            $result[$commentId] = in_array($commentId, $likes);
        }

        return json(['code' => 200, 'data' => $result]);
    }
}
