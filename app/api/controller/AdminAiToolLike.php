<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolLike;
use app\common\model\AiTool;
use app\common\model\User;
use app\common\model\Admin;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI工具点赞管理控制器
 * @route('adminAiToolLike')
 */
class AdminAiToolLike extends BaseController
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
     * 获取点赞列表
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
        $userId = (int)input('userId', 0);
        $userType = input('userType', '');
        $toolName = input('toolName', '');
        $ip = input('ip', '');
        $startTime = input('startTime', 0);
        $endTime = input('endTime', 0);
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');
        $uuid = input('uuid', '');

        // 构建查询条件
        $query = AiToolLike::alias('l')
            ->join('ai_ai_tool t', 'l.tool_id = t.id')
            ->leftJoin('user u', 'l.user_id = u.id AND l.user_type = "user"')
            ->leftJoin('admin a', 'l.user_id = a.id AND l.user_type = "admin"')
            ->field('l.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname');
        
        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('l.tool_id', $toolId);
        }
        
        // 用户ID筛选
        if ($userId > 0) {
            $query->where('l.user_id', $userId);
        }

        //工具名称筛选
        if ($toolName) {
            $query->where('t.name', 'like', "%{$toolName}%");
        }
        //访客ID
        if ($uuid) {
            $query->where('l.uuid', $uuid);
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
        
        // 排序
        $allowedOrderFields = ['id', 'tool_id', 'user_id', 'create_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order('l.' . $orderField, $orderType);
        } else {
            $query->order('l.create_time', 'desc');
        }

        // 分页查询
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_like_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }
    
    /**
     * 获取点赞详情
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.like_not_exist')]);
        }
        
        $like = AiToolLike::alias('l')
            ->join('ai_ai_tool t', 'l.tool_id = t.id')
            ->leftJoin('user u', 'l.user_id = u.id AND l.user_type = "user"')
            ->leftJoin('admin a', 'l.user_id = a.id AND l.user_type = "admin"')
            ->field('l.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname')
            ->where('l.id', $id)
            ->find();
        
        if (!$like) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.like_not_exist')]);
        }
        
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_like_info_success'), 'data' => $like]);
    }
    
    /**
     * 删除点赞
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.like_not_exist')]);
        }
        
        $like = AiToolLike::find($id);
        
        if (!$like) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.like_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除点赞
            $like->delete();
            
            // 更新工具点赞数
            $this->updateToolLikeCount($like->tool_id);
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_like_delete', Lang::get('aiTool.delete_like_log'), [
                'like_id' => $id,
                'tool_id' => $like->tool_id,
                'user_id' => $like->user_id,
                'user_type' => $like->user_type,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_like_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 批量删除点赞
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
        
        // 检查点赞是否存在
        $likes = AiToolLike::whereIn('id', $ids)->select();
        if (count($likes) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.like_not_exist')]);
        }
        
        // 获取涉及的工具ID
        $toolIds = [];
        foreach ($likes as $like) {
            $toolIds[] = $like->tool_id;
        }
        $toolIds = array_unique($toolIds);
        
        Db::startTrans();
        try {
            // 批量删除点赞
            AiToolLike::whereIn('id', $ids)->delete();
            
            // 更新工具点赞数
            foreach ($toolIds as $toolId) {
                $this->updateToolLikeCount($toolId);
            }
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_like_batch_delete', Lang::get('aiTool.batch_delete_like_log'), [
                'like_ids' => $ids,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_delete_like_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 更新工具点赞数
     * @param int $toolId 工具ID
     */
    protected function updateToolLikeCount(int $toolId)
    {
        $count = AiToolLike::where('tool_id', $toolId)->count();
        AiTool::where('id', $toolId)->update(['likes' => $count]);
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
