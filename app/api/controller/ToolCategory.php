<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\response\Json;
use app\common\model\AiToolCategory;

/**
 * AI工具分类控制器
 * @route('toolCategory')
 */
class ToolCategory extends BaseController
{

    protected function initialize(): void
    {
        $this->userType = 'user';
    }

    /**
     * 获取分类列表
     * @route('tree', 'get')
     */
    public function tree(): Json
    {
        // 获取所有分类（树形结构）
        $categories = AiToolCategory::getCategoryTree(1);
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_list_success'), 'data' => $categories]);
    }

    /**
     * 获取分类列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        // 获取所有分类（树形结构）
        $categories = AiToolCategory::getCategoryTree(1);
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_list_success'), 'data' => $categories]);
    }


    /**
     * 获取分类详情
     * @route('detail', 'get')
     */
    public function detail(): Json
    {
        $id = input('id', 0);
        if (!$id) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        $category = AiToolCategory::find($id);

        if (!$category) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_info_success'), 'data' => $category]);
    }

    /**
     * 获取子分类
     * @route('children', 'get')
     */
    public function children(): Json
    {
        $id = input('id', 0);
        if (!$id) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }

        $children = AiToolCategory::where('parent_id', $id)->select()->toArray();
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_info_success'), 'data' => $children]);
    }

    /**
     * 递归获取当前分类的父分类 与当前分类 组成面板屑分类
     * 若当前一级 则返回当前分类名称与ID 以及所属父级分类ID
     * 若当前二级 侧返回 当前分类名称与ID 与 父分类名称与ID 与 父父分类ID
     * 若当前三级 侧返回 当前分类名称与ID 与 父分类名称与ID 与 父父分类名称与ID 与 父父父分类ID
     * 如此类推，无论多少级分类
     * 返回数据格式 按层级 若当前一级 则返回 数据格式
     * current 用来标识当前分类
     * 若当前传过来的ID是 一级分类 则返回 数据格式
     * [
     *  {
     *      id: 1,
     *      name: '一级分类',
     *      parentId: 0,
     *      current: 1
     *  }
     * ]
     * 若当前传过来的ID是二级分类 则返回 数据格式
     * [
     * {
     *      id: 1,
     *      name: '一级分类',
     *      parentId: 0,
     *      current: 0
     *  },
     *  {
     *      id: 2,
     *      name: '二级分类',
     *      parentId: 1,
     *     current: 1,
     *  }
     * ]
     *
     * 若当前传过来的ID是三级分类 则返回数据格式
     * [
     *  {
     *      id: 1,
     *      name: '一级分类',
     *      parentId: 0,
     *      current: 0,
     * },
      *  {
     *      id: 2,
     *      name: '二级分类',
     *      parentId: 1,
     *      current: 0,
     *  },
     *  {
     *      id: 3,
     *      name: '三级分类',
     *      parentId: 2,
     *      current: 1,
     *  },
     * ]
     *
     * @route('getParentCat', 'get')
     */
    public function getParentCat(): Json
    {
        $id = input('id', 0);
        if (!$id) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.category_not_exist')]);
        }
        $categories = AiToolCategory::select()->toArray();
        $currentCategory = AiToolCategory::find($id);
        $currentCategory['current'] = 1;
        $parentCategories = $this->getParentCategories($currentCategory, $categories);
        $parentCategories[] = $currentCategory;
        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_category_info_success'), 'data' => $parentCategories]);
    }

    /**
     * 递归获取当前分类的父分类
     * @param mixed $currentCategory 当前分类
     * @param array $categories 分类数组
     * @return
     */
    private function getParentCategories(mixed $currentCategory, array $categories): array
    {
        if ($currentCategory['parent_id'] == 0) {
            return [];
        }
        $parentCategories = [];
        foreach ($categories as $category) {
            if ($category['id'] == $currentCategory['parent_id']) {
                $category['current'] = 0;
                $parentCategories[] = $category;
                $parentCategories = array_merge($this->getParentCategories($category, $categories), $parentCategories);
            }
        }
        return $parentCategories;

    }


}