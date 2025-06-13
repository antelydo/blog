<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

/**
 * AI Tool Validator
 */
class AiTool extends Validate
{
    /**
     * Validation rules
     */
    protected $rule = [
        'category_id' => 'require|integer|gt:0',
        'name' => 'require|max:100',
        'slug' => 'require|max:100|alphaDash',
        'logo' => 'max:255',
        'banner' => 'max:255',
        'short_description' => 'max:255',
        'website_url' => 'url|max:255',
        'pricing_type' => 'in:free,freemium,paid',
        'is_verified' => 'boolean',
        'is_recommended' => 'boolean',
        'is_top' => 'boolean',
        'status' => 'in:draft,published,pending',
        'sort_order' => 'integer|egt:0',
        'seo_title' => 'max:100',
        'seo_keywords' => 'max:255',
        'seo_description' => 'max:255',
        'company' => 'max:100',
        'version' => 'max:50',
        'license' => 'max:100',
    ];

    /**
     * Error messages
     */
    protected $message = [
        'category_id.require' => 'aiTool.tool_category_id_require',
        'category_id.integer' => 'aiTool.tool_category_id_integer',
        'category_id.gt' => 'aiTool.tool_category_id_gt',
        'name.require' => 'aiTool.tool_name_require',
        'name.max' => 'aiTool.tool_name_max',
        'slug.require' => 'aiTool.tool_slug_require',
        'slug.max' => 'aiTool.tool_slug_max',
        'slug.alphaDash' => 'aiTool.tool_slug_alpha_dash',
        'logo.max' => 'aiTool.tool_logo_max',
        'banner.max' => 'aiTool.tool_banner_max',
        'short_description.max' => 'aiTool.tool_short_description_max',
        'website_url.url' => 'aiTool.tool_website_url_url',
        'website_url.max' => 'aiTool.tool_website_url_max',
        'pricing_type.in' => 'aiTool.tool_pricing_type_in',
        'is_verified.boolean' => 'aiTool.tool_is_verified_boolean',
        'is_recommended.boolean' => 'aiTool.tool_is_recommended_boolean',
        'is_top.boolean' => 'aiTool.tool_is_top_boolean',
        'status.in' => 'aiTool.tool_status_in',
        'sort_order.integer' => 'aiTool.tool_sort_order_integer',
        'sort_order.egt' => 'aiTool.tool_sort_order_egt',
        'seo_title.max' => 'aiTool.tool_seo_title_max',
        'seo_keywords.max' => 'aiTool.tool_seo_keywords_max',
        'seo_description.max' => 'aiTool.tool_seo_description_max',
        'company.max' => 'aiTool.tool_company_max',
        'version.max' => 'aiTool.tool_version_max',
        'license.max' => 'aiTool.tool_license_max',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'create' => ['category_id', 'name', 'slug', 'logo', 'banner', 'short_description', 'website_url', 'pricing_type', 'is_verified', 'is_recommended', 'is_top', 'status', 'sort_order', 'seo_title', 'seo_keywords', 'seo_description', 'company', 'platforms', 'languages', 'usage_tips', 'version', 'license'],
        'update' => ['category_id', 'name', 'slug', 'logo', 'banner', 'short_description', 'website_url', 'pricing_type', 'is_verified', 'is_recommended', 'is_top', 'status', 'sort_order', 'seo_title', 'seo_keywords', 'seo_description', 'company', 'platforms', 'languages', 'usage_tips', 'version', 'license'],
    ];
}
