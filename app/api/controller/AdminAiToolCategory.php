<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolCategory;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI Tool Category Management Controller
 * @route('adminAiToolCategory')
 */
class AdminAiToolCategory extends BaseController
{
    /**
     * Initialize method
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();
    }

    /**
     * Get category list
     * @route('list', 'get')
     */
    public function list(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        // 获取所有分类（树形结构）
        $categories = AiToolCategory::getCategoryTree();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_list_success'), 'data' => $categories]);
    }

    /**
     * 获取分类详情
     * @route('info', 'get')
     */
    public function info(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        $category = AiToolCategory::find($id);

        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_info_success'), 'data' => $category]);
    }

    /**
     * Create category
     * @route('create', 'post')
     */
    public function create(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = input('post.');

        // 验证数据
        $validate = validate('AiToolCategory');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // 检查别名是否已存在
        $exists = AiToolCategory::where('slug', $data['slug'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_slug_exists')]);
        }

        // 创建分类
        $category = new AiToolCategory;
        $category->save($data);

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_category_create', Lang::get('aiTool.create_category_log'), [
            'category_id' => $category->id,
            'category_name' => $category->name,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.create_category_success'), 'data' => $category]);
    }

    /**
     * 更新分类
     * @route('update', 'post')
     */
    public function update(): Json
    {
        // 检查是否登录
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = input('post.');

        if (!isset($data['id']) || !$data['id']) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        // Validate data
        $validate = validate('AiToolCategory');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // Check if category exists
        $category = AiToolCategory::find($data['id']);
        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        // Check if slug already exists (excluding itself)
        $exists = AiToolCategory::where('slug', $data['slug'])->where('id', '<>', $data['id'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_slug_exists')]);
        }

        // Check if parent category is itself or its child category
        if ($data['parent_id'] > 0) {
            if ($data['parent_id'] == $data['id']) {
                return json(['code' => 400, 'msg' => Lang::get('aiTool.category_parent_invalid')]);
            }

            // Get all child category IDs
            $childIds = $this->getAllChildIds($data['id']);
            if (in_array($data['parent_id'], $childIds)) {
                return json(['code' => 400, 'msg' => Lang::get('aiTool.category_parent_invalid')]);
            }
        }

        // Update category
        $category->save($data);

        // Record activity log
        $this->recordActivity($this->userId, 'admin', 'ai_tool_category_update', Lang::get('aiTool.update_category_log'), [
            'category_id' => $category->id,
            'category_name' => $category->name,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_category_success')]);
    }

    /**
     * Delete category
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        // Check if category exists
        $category = AiToolCategory::find($id);
        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        // Check if it has child categories
        $childCount = AiToolCategory::where('parent_id', $id)->count();
        if ($childCount > 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_has_children')]);
        }

        // Check if it has associated tools
        $toolCount = Db::name('ai_tool')->where('category_id', $id)->count();
        if ($toolCount > 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_has_tools')]);
        }

        // Delete category
        $category->delete();

        // Record activity log
        $this->recordActivity($this->userId, 'admin', 'ai_tool_category_delete', Lang::get('aiTool.delete_category_log'), [
            'category_id' => $id,
            'category_name' => $category->name,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_category_success')]);
    }

    /**
     * Update category display status
     * @route('updateStatus', 'post')
     */
    public function updateStatus(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $isShow = input('is_show', null);

        if (!$id || $isShow === null) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // Check if category exists
        $category = AiToolCategory::find($id);
        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        // Update display status
        $category->is_show = (bool)$isShow;
        $category->save();

        // Record activity log
        $this->recordActivity($this->userId, 'admin', 'ai_tool_category_update_status', Lang::get('aiTool.update_category_status_log'), [
            'category_id' => $id,
            'category_name' => $category->name,
            'is_show' => (bool)$isShow,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_category_status_success')]);
    }

    /**
     * Get all child category IDs
     * @param int $categoryId Category ID
     * @return array Child category ID array
     */
    protected function getAllChildIds(int $categoryId): array
    {
        $childIds = [];
        $children = AiToolCategory::where('parent_id', $categoryId)->column('id');

        if (!empty($children)) {
            $childIds = array_merge($childIds, $children);

            foreach ($children as $childId) {
                $childIds = array_merge($childIds, $this->getAllChildIds($childId));
            }
        }

        return $childIds;
    }
}
