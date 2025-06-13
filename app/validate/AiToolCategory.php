<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

/**
 * AI Tool Category Validator
 */
class AiToolCategory extends Validate
{
    /**
     * Validation rules
     */
    protected $rule = [
        'name' => 'require|max:50',
        'slug' => 'require|max:50|alphaDash',
        'icon' => 'max:255',
        'description' => 'max:255',
        'parent_id' => 'integer|egt:0',
        'sort_order' => 'integer|egt:0',
        'is_show' => 'boolean',
        'seo_title' => 'max:100',
        'seo_keywords' => 'max:255',
        'seo_description' => 'max:255',
    ];

    /**
     * Error messages
     */
    protected $message = [
        'name.require' => 'aiTool.category_name_require',
        'name.max' => 'aiTool.category_name_max',
        'slug.require' => 'aiTool.category_slug_require',
        'slug.max' => 'aiTool.category_slug_max',
        'slug.alphaDash' => 'aiTool.category_slug_alpha_dash',
        'icon.max' => 'aiTool.category_icon_max',
        'description.max' => 'aiTool.category_description_max',
        'parent_id.integer' => 'aiTool.category_parent_id_integer',
        'parent_id.egt' => 'aiTool.category_parent_id_egt',
        'sort_order.integer' => 'aiTool.category_sort_order_integer',
        'sort_order.egt' => 'aiTool.category_sort_order_egt',
        'is_show.boolean' => 'aiTool.category_is_show_boolean',
        'seo_title.max' => 'aiTool.category_seo_title_max',
        'seo_keywords.max' => 'aiTool.category_seo_keywords_max',
        'seo_description.max' => 'aiTool.category_seo_description_max',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'create' => ['name', 'slug', 'icon', 'description', 'parent_id', 'sort_order', 'is_show', 'seo_title', 'seo_keywords', 'seo_description'],
        'update' => ['name', 'slug', 'icon', 'description', 'parent_id', 'sort_order', 'is_show', 'seo_title', 'seo_keywords', 'seo_description'],
    ];
}
