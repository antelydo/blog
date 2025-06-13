<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

/**
 * AI Tool Tag Validator
 */
class AiToolTag extends Validate
{
    /**
     * Validation rules
     */
    protected $rule = [
        'name' => 'require|max:50',
        'slug' => 'require|max:50|alphaDash',
        'description' => 'max:255',
        'is_show' => 'boolean',
        'seo_title' => 'max:100',
        'seo_keywords' => 'max:255',
        'seo_description' => 'max:255',
    ];

    /**
     * Error messages
     */
    protected $message = [
        'name.require' => 'aiTool.tag_name_require',
        'name.max' => 'aiTool.tag_name_max',
        'slug.require' => 'aiTool.tag_slug_require',
        'slug.max' => 'aiTool.tag_slug_max',
        'slug.alphaDash' => 'aiTool.tag_slug_alpha_dash',
        'description.max' => 'aiTool.tag_description_max',
        'is_show.boolean' => 'aiTool.tag_is_show_boolean',
        'seo_title.max' => 'aiTool.tag_seo_title_max',
        'seo_keywords.max' => 'aiTool.tag_seo_keywords_max',
        'seo_description.max' => 'aiTool.tag_seo_description_max',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'create' => ['name', 'slug', 'description', 'is_show', 'seo_title', 'seo_keywords', 'seo_description'],
        'update' => ['name', 'slug', 'description', 'is_show', 'seo_title', 'seo_keywords', 'seo_description'],
    ];
}
