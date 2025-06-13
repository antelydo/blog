<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI工具评论模型
 */
class AiToolComment extends Model
{
    // 设置表名
    protected $name = 'ai_tool_comment';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = false;
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'tool_id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'rating' => 'integer',
        'likes' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
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
     * 获取父评论
     */
    public function parent()
    {
        return $this->belongsTo(AiToolComment::class, 'parent_id', 'id');
    }
    
    /**
     * 获取子评论
     */
    public function children()
    {
        return $this->hasMany(AiToolComment::class, 'parent_id', 'id');
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
     * 搜索器 - 按用户类型搜索
     */
    public function searchUserTypeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('user_type', $value);
        }
    }
    
    /**
     * 搜索器 - 按状态搜索
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('status', $value);
        }
    }
    
    /**
     * 搜索器 - 按内容搜索
     */
    public function searchContentAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('content', 'like', "%{$value}%");
        }
    }
}
