<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI Tool Tag Model
 */
class AiToolTag extends Model
{
    // Set table name
    protected $name = 'ai_tool_tag';

    // Set primary key
    protected $pk = 'id';

    // Auto write timestamp
    protected $autoWriteTimestamp =true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // Type conversion
    protected $type = [
        'id' => 'integer',
        'count' => 'integer',
        'is_show' => 'boolean',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * Get tools using this tag
     */
    public function tools()
    {
        return $this->belongsToMany(AiTool::class, AiToolTagRelation::class, 'tool_id', 'tag_id');
    }

    /**
     * Update tag usage count
     */
    public static function updateCount($tagId)
    {
        $count = AiToolTagRelation::where('tag_id', $tagId)->count();
        self::where('id', $tagId)->update(['count' => $count]);
    }

    /**
     * Searcher - Search by name
     */
    public function searchNameAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('name', 'like', "%{$value}%");
        }
    }

    /**
     * Searcher - Search by is_show
     */
    public function searchIsShowAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('is_show', (int)$value);
        }
    }
}
