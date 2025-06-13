<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI工具评价点赞模型
 */
class AiToolCommentLike extends Model
{
    // 设置表名
    protected $name = 'ai_tool_comment_like';

    // 设置主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 类型转换
    protected $type = [
        'id' => 'integer',
        'comment_id' => 'integer',
        'user_id' => 'integer',
        'create_time' => 'integer',
        'uuid' => 'string',
        'ip' => 'string',
        'user_agent' => 'string',
        'user_type' => 'string',
    ];

    /**
     * 获取评价信息
     */
    public function comment()
    {
        return $this->belongsTo(AiToolComment::class, 'comment_id', 'id');
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
     * 搜索器 - 按评价ID搜索
     */
    public function searchCommentIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('comment_id', $value);
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
}
