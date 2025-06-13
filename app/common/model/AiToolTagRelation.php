<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI工具标签关联模型
 */
class AiToolTagRelation extends Model
{
    // 设置表名
    protected $name = 'ai_tool_tag_relation';

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
        'tag_id' => 'integer',
        'create_time' => 'integer',
    ];

    /**
     * 获取工具信息
     */
    public function tool()
    {
        return $this->belongsTo(AiTool::class, 'tool_id', 'id');
    }

    /**
     * 获取标签信息
     */
    public function tag()
    {
        return $this->belongsTo(AiToolTag::class, 'tag_id', 'id');
    }
}
