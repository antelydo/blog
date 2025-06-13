<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolFavorite;
use app\common\model\AiTool;
use app\common\model\User;
use app\common\model\Admin;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI工具收藏管理控制器
 * @route('adminAiToolFavorite')
 */
class AdminAiToolFavorite extends BaseController
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
     * 获取收藏列表
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
        $userType = input('user_type', '');
        $startTime = input('start_time', 0);
        $endTime = input('end_time', 0);
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');
        
        // 构建查询条件
        $query = AiToolFavorite::alias('f')
            ->join('ai_ai_tool t', 'f.tool_id = t.id')
            ->leftJoin('user u', 'f.user_id = u.id AND f.user_type = "user"')
            ->leftJoin('admin a', 'f.user_id = a.id AND f.user_type = "admin"')
            ->field('f.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname');
        
        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('f.tool_id', $toolId);
        }
        
        // 用户ID筛选
        if ($userId > 0) {
            $query->where('f.user_id', $userId);
        }
        
        // 用户类型筛选
        if ($userType) {
            $query->where('f.user_type', $userType);
        }
        
        // 时间范围筛选
        if ($startTime) {
            $query->where('f.create_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('f.create_time', '<=', $endTime);
        }
        
        // 排序
        $allowedOrderFields = ['id', 'tool_id', 'user_id', 'create_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order('f.' . $orderField, $orderType);
        } else {
            $query->order('f.create_time', 'desc');
        }

        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_favorite_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }
    
    /**
     * 获取收藏详情
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.favorite_not_exist')]);
        }
        
        $favorite = AiToolFavorite::alias('f')
            ->join('ai_ai_tool t', 'f.tool_id = t.id')
            ->leftJoin('user u', 'f.user_id = u.id AND f.user_type = "user"')
            ->leftJoin('admin a', 'f.user_id = a.id AND f.user_type = "admin"')
            ->field('f.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname')
            ->where('f.id', $id)
            ->find();
        
        if (!$favorite) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.favorite_not_exist')]);
        }
        
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_favorite_info_success'), 'data' => $favorite]);
    }
    
    /**
     * 删除收藏
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.favorite_not_exist')]);
        }
        
        $favorite = AiToolFavorite::find($id);
        
        if (!$favorite) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.favorite_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除收藏
            $favorite->delete();
            
            // 更新工具收藏数
            $this->updateToolFavoriteCount($favorite->tool_id);
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_favorite_delete', Lang::get('aiTool.delete_favorite_log'), [
                'favorite_id' => $id,
                'tool_id' => $favorite->tool_id,
                'user_id' => $favorite->user_id,
                'user_type' => $favorite->user_type,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_favorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 批量删除收藏
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
        
        // 检查收藏是否存在
        $favorites = AiToolFavorite::whereIn('id', $ids)->select();
        if (count($favorites) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.favorite_not_exist')]);
        }
        
        // 获取涉及的工具ID
        $toolIds = [];
        foreach ($favorites as $favorite) {
            $toolIds[] = $favorite->tool_id;
        }
        $toolIds = array_unique($toolIds);
        
        Db::startTrans();
        try {
            // 批量删除收藏
            AiToolFavorite::whereIn('id', $ids)->delete();
            
            // 更新工具收藏数
            foreach ($toolIds as $toolId) {
                $this->updateToolFavoriteCount($toolId);
            }
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_favorite_batch_delete', Lang::get('aiTool.batch_delete_favorite_log'), [
                'favorite_ids' => $ids,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_delete_favorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 更新工具收藏数
     * @param int $toolId 工具ID
     */
    protected function updateToolFavoriteCount(int $toolId)
    {
        $count = AiToolFavorite::where('tool_id', $toolId)->count();
        AiTool::where('id', $toolId)->update(['favorites' => $count]);
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
