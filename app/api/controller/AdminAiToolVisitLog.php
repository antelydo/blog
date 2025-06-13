<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolVisitLog;
use app\common\model\AiTool;
use app\common\model\User;
use app\common\model\Admin;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI工具访问日志管理控制器
 * @route('adminAiToolVisitLog')
 */
class AdminAiToolVisitLog extends BaseController
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
     * 获取访问日志列表
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
        $ip = input('ip', '');
        $referer = input('referer', '');
        $startTime = input('start_time', 0);
        $endTime = input('end_time', 0);
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');
        
        // 构建查询条件
        $query = AiToolVisitLog::alias('v')
            ->join('ai_ai_tool t', 'v.tool_id = t.id')
            ->leftJoin('user u', 'v.user_id = u.id AND v.user_type = "user"')
            ->leftJoin('admin a', 'v.user_id = a.id AND v.user_type = "admin"')
            ->field('v.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname');
        
        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('v.tool_id', $toolId);
        }
        
        // 用户ID筛选
        if ($userId > 0) {
            $query->where('v.user_id', $userId);
        }
        
        // 用户类型筛选
        if ($userType) {
            $query->where('v.user_type', $userType);
        }
        
        // IP筛选
        if ($ip) {
            $query->where('v.ip', 'like', "%{$ip}%");
        }
        
        // 来源页面筛选
        if ($referer) {
            $query->where('v.referer', 'like', "%{$referer}%");
        }
        
        // 时间范围筛选
        if ($startTime) {
            $query->where('v.create_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('v.create_time', '<=', $endTime);
        }
        
        // 排序
        $allowedOrderFields = ['id', 'tool_id', 'user_id', 'create_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order('v.' . $orderField, $orderType);
        } else {
            $query->order('v.create_time', 'desc');
        }

        // 分页查询
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_visit_log_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }
    
    /**
     * 获取访问日志详情
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.visit_log_not_exist')]);
        }
        
        $visitLog = AiToolVisitLog::alias('v')
            ->join('ai_ai_tool t', 'v.tool_id = t.id')
            ->leftJoin('user u', 'v.user_id = u.id AND v.user_type = "user"')
            ->leftJoin('admin a', 'v.user_id = a.id AND v.user_type = "admin"')
            ->field('v.*, t.name as tool_name, t.logo as tool_logo, IFNULL(u.nickname, a.nickname) as user_nickname')
            ->where('v.id', $id)
            ->find();
        
        if (!$visitLog) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.visit_log_not_exist')]);
        }
        
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_visit_log_info_success'), 'data' => $visitLog]);
    }
    
    /**
     * 删除访问日志
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.visit_log_not_exist')]);
        }
        
        $visitLog = AiToolVisitLog::find($id);
        
        if (!$visitLog) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.visit_log_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除访问日志
            $visitLog->delete();
            
            // 更新工具浏览量
            $this->updateToolViewCount($visitLog->tool_id);
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_visit_log_delete', Lang::get('aiTool.delete_visit_log_log'), [
                'visit_log_id' => $id,
                'tool_id' => $visitLog->tool_id,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_visit_log_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 批量删除访问日志
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
        
        // 检查访问日志是否存在
        $visitLogs = AiToolVisitLog::whereIn('id', $ids)->select();
        if (count($visitLogs) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.visit_log_not_exist')]);
        }
        
        // 获取涉及的工具ID
        $toolIds = [];
        foreach ($visitLogs as $visitLog) {
            $toolIds[] = $visitLog->tool_id;
        }
        $toolIds = array_unique($toolIds);
        
        Db::startTrans();
        try {
            // 批量删除访问日志
            AiToolVisitLog::whereIn('id', $ids)->delete();
            
            // 更新工具浏览量
            foreach ($toolIds as $toolId) {
                $this->updateToolViewCount($toolId);
            }
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_visit_log_batch_delete', Lang::get('aiTool.batch_delete_visit_log_log'), [
                'visit_log_ids' => $ids,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_delete_visit_log_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 清空访问日志
     * @route('clear', 'post')
     */
    public function clear(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $toolId = (int)input('tool_id', 0);
        $days = (int)input('days', 0);
        
        // 构建查询条件
        $query = AiToolVisitLog::where('id', '>', 0);
        
        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('tool_id', $toolId);
        }
        
        // 天数筛选
        if ($days > 0) {
            $time = time() - $days * 86400;
            $query->where('create_time', '<', $time);
        }
        
        Db::startTrans();
        try {
            // 获取涉及的工具ID
            $toolIds = [];
            if ($toolId > 0) {
                $toolIds[] = $toolId;
            } else {
                $toolIds = AiTool::column('id');
            }
            
            // 删除访问日志
            $query->delete();
            
            // 更新工具浏览量
            foreach ($toolIds as $id) {
                $this->updateToolViewCount($id);
            }
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_visit_log_clear', Lang::get('aiTool.clear_visit_log_log'), [
                'tool_id' => $toolId,
                'days' => $days,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('aiTool.clear_visit_log_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取访问统计数据
     * @route('stats', 'get')
     */
    public function stats(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $toolId = (int)input('tool_id', 0);
        $days = (int)input('days', 30);
        
        // 限制天数范围
        if ($days <= 0) {
            $days = 30;
        } elseif ($days > 365) {
            $days = 365;
        }
        
        // 计算开始时间
        $startTime = strtotime(date('Y-m-d', strtotime("-{$days} days")));
        $endTime = strtotime(date('Y-m-d')) + 86399; // 今天的最后一秒
        
        // 构建查询条件
        $query = AiToolVisitLog::where('create_time', '>=', $startTime)
            ->where('create_time', '<=', $endTime);
        
        // 工具ID筛选
        if ($toolId > 0) {
            $query->where('tool_id', $toolId);
        }
        
        // 按日期分组统计
        $dailyStats = $query->field('FROM_UNIXTIME(create_time, "%Y-%m-%d") as date, COUNT(*) as count')
            ->group('FROM_UNIXTIME(create_time, "%Y-%m-%d")')
            ->select()
            ->toArray();
 
        
        // 填充没有数据的日期
        $stats = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $stats[$date] = 0;
        }
        
        foreach ($dailyStats as $item) {
            if (isset($stats[$item['date']])) {
                $stats[$item['date']] = $item['count'];
            }
        }
        
        // 转换为数组格式
        $result = [];
        foreach ($stats as $date => $count) {
            $result[] = [
                'date' => $date,
                'count' => $count,
            ];
        }
        
        // 按日期排序
        usort($result, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        // 获取总访问量
        $totalVisits = $query->count();


        // 获取独立访客数
        // $uniqueVisitors = $query->fieldRaw('COUNT(DISTINCT ip) as count')->find()->count;

        // 方案2：分步查询，先获取distinct的IP列表，再计算数量
        $uniqueVisitors = count($query->field('ip')->distinct(true)->column('ip'));

        // 方案3：使用子查询的方式
        // $subQuery = $query->field('DISTINCT ip')->buildSql();
        // $uniqueVisitors = Db::table($subQuery.' a')->count();
    
        // 获取来源分布
        $refererStats = $query->field('referer, COUNT(*) as count')
            ->group('referer')
            ->order('count', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        // 处理来源数据
        foreach ($refererStats as &$item) {
            if (empty($item['referer'])) {
                $item['referer'] = '直接访问';
            } else {
                // 提取域名
                $parts = parse_url($item['referer']);
                $item['referer'] = isset($parts['host']) ? $parts['host'] : $item['referer'];
            }
        }
        
        return json([
            'code' => 200,
            'msg' => Lang::get('aiTool.get_visit_stats_success'),
            'data' => [
                'daily' => $result,
                'total_visits' => $totalVisits,
                'unique_visitors' => $uniqueVisitors,
                'referer_stats' => $refererStats,
            ],
        ]);
    }
    
    /**
     * 更新工具浏览量
     * @param int $toolId 工具ID
     */
    protected function updateToolViewCount(int $toolId)
    {
        $count = AiToolVisitLog::where('tool_id', $toolId)->count();
        AiTool::where('id', $toolId)->update(['views' => $count]);
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
