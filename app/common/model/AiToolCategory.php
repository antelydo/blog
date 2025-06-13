<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * AI Tool Category Model
 */
class AiToolCategory extends Model
{
    // Set table name
    protected $name = 'ai_tool_category';

    // Set primary key
    protected $pk = 'id';

    // Auto write timestamp
    // 自动写入时间戳
    protected $autoWriteTimestamp =true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';




    // Type conversion
    protected $type = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_show' => 'boolean',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * Get child categories
     */
    public function children()
    {
        return $this->hasMany(AiToolCategory::class, 'parent_id', 'id');
    }

    /**
     * Get parent category
     */
    public function parent()
    {
        return $this->belongsTo(AiToolCategory::class, 'parent_id', 'id');
    }

    /**
     * Get tools under the category (single category relation compatible with old version)
     */
    public function tools()
    {
        return $this->hasMany(AiTool::class, 'category_id', 'id');
    }

    /**
     * Get all tools under the category (many-to-many relationship)
     */
    public function allTools()
    {
        return $this->belongsToMany(AiTool::class, AiToolCategoryRelation::class, 'tool_id', 'category_id');
    }

    /**
     * Get all categories (tree structure)
     */
    public static function getCategoryTree($isShow = null)
    {
        $query = self::order('sort_order', 'asc');

        if ($isShow !== null) {
            $query->where('is_show', $isShow);
        }

        $categories = $query->select()->toArray();

        return self::buildTree($categories);
    }

    /**
     * Build tree structure
     */
    protected static function buildTree(array $elements, $parentId = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);

                if ($children) {
                    $element['children'] = $children;
                }

                $branch[] = $element;
            }
        }

        return $branch;
    }

}
