<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI工具分类关联模型
 */
class AiToolCategoryRelation extends Model
{
    // 设置表名
    protected $name = 'ai_tool_category_relation';

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
        'category_id' => 'integer',
        'create_time' => 'integer',
    ];
}
