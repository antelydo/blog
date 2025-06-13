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
 * AI工具收藏控制器
 * @route('toolFavorite')
 */
class ToolFavorite extends BaseController
{

    protected function initialize(): void
    {
        $this->userType = 'user';
        parent::initialize();
    }

    /**
     * AI工具收藏 必须登录
     * @route('add', 'post')
     */
    public function favorite(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $toolId =intval( input('tool_id', 0));
        $userId = $this->userId;

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

        // 检查是否已收藏
        $favorited = Db::name('ai_tool_favorite')
            ->where('tool_id', $toolId)
            ->where('user_id', $userId)
            ->where('user_type', $this->userType)
            ->find();

        if ($favorited) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.already_favorite')]);
        }

        Db::startTrans();
        try {
            // 添加收藏记录
            Db::name('ai_tool_favorite')->insert([
                'tool_id' => $toolId,
                'user_id' => $userId,
                'user_type' => $this->userType,
                'create_time' => time(),
            ]);

            // 更新工具收藏数
            Db::name('ai_tool')
                ->where('id', $toolId)
                ->inc('favorites')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiTool.favorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 取消AI工具收藏 必须登录
     * @route('cancel', 'post')
     */
    public function unfavorite(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $toolId = input('tool_id', 0);
        $userId = $this->userId;

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

        // 检查是否已收藏
        $favorited = Db::name('ai_tool_favorite')
            ->where('tool_id', $toolId)
            ->where('user_id', $userId)
            ->where('user_type', $this->userType)
            ->find();

        if (!$favorited) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.not_favorite')]);
        }

        Db::startTrans();
        try {
            // 删除收藏记录
            Db::name('ai_tool_favorite')
                ->where('tool_id', $toolId)
                ->where('user_id', $userId)
                ->where('user_type', $this->userType)
                ->delete();

            // 更新工具收藏数
            Db::name('ai_tool')
                ->where('id', $toolId)
                ->where('favorites', '>', 0)
                ->dec('favorites')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('aiTool.unfavorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 100500, 'msg' => $e->getMessage()]);
        }
    }


}
