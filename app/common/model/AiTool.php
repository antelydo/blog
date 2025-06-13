<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\facade\Db;

/**
 * AI Tool Model
 */
class AiTool extends Model
{
    // Set table name
    protected $name = 'ai_tool';

    // Set primary key
    protected $pk = 'id';

    // Auto write timestamp
    protected $autoWriteTimestamp = false;


    // Type conversion
    protected $type = [
        'id' => 'integer',
        'category_id' => 'integer',
        'is_verified' => 'boolean',
        'is_recommended' => 'boolean',
        'is_top' => 'boolean',
        'views' => 'integer',
        'likes' => 'integer',
        'favorites' => 'integer',
        'comments' => 'integer',
        'rating' => 'float',
        'rating_count' => 'integer',
        'sort_order' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
        'publish_time' => 'integer',
    ];

    // JSON field auto conversion
    protected $json = ['screenshots', 'features', 'pricing_info', 'platforms', 'languages', 'usage_tips'];
    protected $jsonAssoc = true;

    /**
     * Get category (single category compatible with old version)
     */
    public function category()
    {
        return $this->belongsTo(AiToolCategory::class, 'category_id', 'id');
    }

    /**
     * Get all categories (many-to-many relationship)
     */
    public function categories()
    {
        return $this->belongsToMany(AiToolCategory::class, AiToolCategoryRelation::class, 'category_id', 'tool_id');
    }

    /**
     * Get tool tags
     */
    public function tags()
    {
        return $this->belongsToMany(AiToolTag::class, AiToolTagRelation::class, 'tag_id', 'tool_id');
    }

    /**
     * Get tool comments
     */
    public function comments()
    {
        return $this->hasMany(AiToolComment::class, 'tool_id', 'id');
    }

    /**
     * Get tool favorites
     */
    public function favorites()
    {
        return $this->hasMany(AiToolFavorite::class, 'tool_id', 'id');
    }

    /**
     * Get tool likes
     */
    public function likes()
    {
        return $this->hasMany(AiToolLike::class, 'tool_id', 'id');
    }

    /**
     * Get tool visit logs
     */
    public function visitLogs()
    {
        return $this->hasMany(AiToolVisitLog::class, 'tool_id', 'id');
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
     * Searcher - Search by category
     */
    public function searchCategoryIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            // First query the category relation table
            $toolIds = Db::name('ai_tool_category_relation')
                ->where('category_id', $value)
                ->column('tool_id');

            // If there is associated data, use it for the query
            if (!empty($toolIds)) {
                $query->whereIn('id', $toolIds);
            } else {
                // Compatible with old version, query using category_id field
                $query->where('category_id', $value);
            }
        }
    }

    /**
     * Searcher - Search by multiple categories
     */
    public function searchCategoryIdsAttr($query, $value, $data)
    {
        if (!empty($value)) {
            // Convert comma-separated string to array
            $categoryIds = explode(',', $value);

            if (!empty($categoryIds)) {
                // Query the category relation table to find tool IDs that contain any of the specified categories
                $toolIds = Db::name('ai_tool_category_relation')
                    ->whereIn('category_id', $categoryIds)
                    ->group('tool_id')
                    ->column('tool_id');

                if (!empty($toolIds)) {
                    $query->whereIn('id', $toolIds);
                } else {
                    // If no matching tools, return empty result
                    $query->where('id', 0);
                }
            }
        }
    }

    /**
     * Searcher - Search by status
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('status', $value);
        }
    }

    /**
     * Searcher - Search by tag ID
     */
    public function searchTagIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            // Query tag relation table
            $toolIds = Db::name('ai_tool_tag_relation')
                ->where('tag_id', $value)
                ->column('tool_id');

            if (!empty($toolIds)) {
                $query->whereIn('id', $toolIds);
            } else {
                // If no associated data, return empty result
                $query->where('id', 0);
            }
        }
    }

    /**
     * Searcher - Search by recommendation status
     */
    public function searchIsRecommendedAttr($query, $value, $data)
    {
        if ($value !== null && $value !== '') {
            $query->where('is_recommended', $value);
        }
    }

    /**
     * Searcher - Search by top status
     */
    public function searchIsTopAttr($query, $value, $data)
    {
        if ($value !== null && $value !== '') {
            $query->where('is_top', $value);
        }
    }

    /**
     * Searcher - Search by price type
     */
    public function searchPricingTypeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('pricing_type', $value);
        }
    }

//    /**
//     * Searcher - Search by tag
//     */
//    public function searchTagIdAttr($query, $value, $data)
//    {
//        if (!empty($value)) {
//            $query->whereExists(function ($query) use ($value) {
//                $query->table('ai_ai_tool_tag_relation')
//                    ->where('ai_ai_tool_tag_relation.tool_id', '=', $this->getTable() . '.id')
//                    ->where('ai_ai_tool_tag_relation.tag_id', '=', $value);
//            });
//        }
//    }
}
