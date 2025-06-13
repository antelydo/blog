<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use app\model\User;
use app\model\Admin;

/**
 * AI工具访问记录模型
 */
class AiToolVisitLog extends Model
{
    // 设置表名
    protected $name = 'ai_tool_visit_log';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'tool_id' => 'integer',
        'user_id' => 'integer',
        'create_time' => 'integer',
        'visit_date' => 'date',
    ];
    
    /**
     * 获取工具信息
     */
    public function tool()
    {
        return $this->belongsTo(AiTool::class, 'tool_id', 'id');
    }
    
    /**
     * 获取用户信息
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * 获取管理员信息
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }
    
    /**
     * 搜索器 - 按工具ID搜索
     */
    public function searchToolIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('tool_id', $value);
        }
    }
    
    /**
     * 搜索器 - 按用户ID搜索
     */
    public function searchUserIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('user_id', $value);
        }
    }
    
    /**
     * 搜索器 - 按访问日期搜索
     */
    public function searchVisitDateAttr($query, $value, $data)
    {
        if (!empty($value)) {
            if (is_array($value) && count($value) == 2) {
                $query->whereBetweenTime('visit_date', $value[0], $value[1]);
            } else {
                $query->whereTime('visit_date', '=', $value);
            }
        }
    }
}
