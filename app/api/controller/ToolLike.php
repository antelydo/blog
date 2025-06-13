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
 * AI工具点赞控制器
 * @route('toolLike')
 */
class ToolLike extends BaseController
{

    protected function initialize(): void
    {
        $this->userType = 'user';
        parent::initialize();
    }

    /**
     * AI工具点赞
     * @route('like', 'post')
     */
    public function like(): Json
    {
        $toolId = intval(input('tool_id', 0));
        $userId = $this->userId ?? 0;
        $user_type = $userId>0?$this->userType:'guest';
        //访客ID
        $uuid =$this->getVisitorId();

        if (!$toolId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查工具是否存在
        $tool = Db::name('ai_tool')
            ->where('id', $toolId)
            ->find();

        if (!$tool) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查是否已点赞
        $liked = Db::name('ai_tool_like')
            ->where('tool_id', $toolId)
            ->where('user_id', $userId)
            ->where('uuid',$uuid)
            ->where('user_type', $user_type)
            ->find();
        if ($liked) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.already_liked')]);
        }

        Db::startTrans();
        try {
            // 添加点赞记录
            Db::name('ai_tool_like')->insert([
                'tool_id' => $toolId,
                'user_id' => $userId,
                'uuid' => $uuid,
                'user_type' => $user_type,
                'ip' => Request::ip(),
                'create_time' => time(),
            ]);

            // 更新工具点赞数
            Db::name('ai_tool')
                ->where('id', $toolId)
                ->inc('likes')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiTool.like_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * AI工具取消点赞
     * @route('unlike', 'post')
     */
    public function unlike(): Json
    {
        $toolId = intval(input('tool_id', 0));
        $userId = $this->userId ?? 0;
        $user_type = $userId>0?$this->userType:'guest';
        //访客ID
        $uuid =$this->getVisitorId();

        if (!$toolId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查工具是否存在
        $tool = Db::name('ai_tool')
            ->where('id', $toolId)
            ->find();

        if (!$tool) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查是否已点赞
        $query = Db::name('ai_tool_like');
        if ($userId > 0) {
            // 登录用户
            $liked = $query->where('tool_id', $toolId)
                ->where('user_id', $userId)
                ->where('user_type', $user_type)
                ->find();
        } else {
            // 访客
            $liked = $query->where('tool_id', $toolId)
                ->where('user_id', 0)
                ->where('user_type', 'guest')
                ->where('uuid', $uuid)
                ->find();
        }

        if (!$liked) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.not_liked')]);
        }

        Db::startTrans();
        try {
            // 删除点赞记录
            $query->where('tool_id', $toolId)
                ->where('user_id', $userId)
                ->where('user_type', $user_type)
                ->where('uuid', $uuid)
                ->delete();

            // 更新工具点赞数
            Db::name('ai_tool')
                ->where('id', $toolId)
                ->where('likes', '>', 0)
                ->dec('likes')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiTool.unlike_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }
}