<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiToolTag;
use app\common\model\AiToolTagRelation;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 *ai标签
 * @route('toolTag')
 */
class ToolTag extends BaseController
{
    /**
     * Initialize method
     */
    protected function initialize(): void
    {
        $this->userType = 'user';
    }


    /**
     * 获取标签列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $name = input('name', '');
        $status = input('status', '');
        $page = input('page', 1);
        $limit = input('limit', 10);
        $isShow = input('is_show', '');

        // 构建查询条件
        $query = AiToolTag::withSearch(['name', 'is_show'], [
            'name' => $name,
            'is_show' => $isShow,
        ]);

        // 使用搜索器进行筛选

        // 排序
        $query->order('count', 'desc');

        // 分页查询
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>'count','order_type'=>'desc']]);
    }

    /**
     * 获取标签列表
     * @route('detail', 'get')
     */

    public function detail(): Json
    {
        $id = input('id', 0);
        if (!$id) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        $tag = AiToolTag::find($id);

        if (!$tag) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_info_success'), 'data' => $tag]);

    }


    /**
     * 获取工具标签
     * @route('detail', 'get')
     */
    public function byTool(): Json
    {
        $toolId = input('tool_id', 0);
        if (!$toolId) {
            return json(['code' => 100400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        $tags = Db::name('ai_tool_tag_relation')
            ->alias('r')
            ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
            ->field('t.id, t.name')
            ->where('r.tool_id', $toolId)
            ->where('t.is_show', 1)
            ->select()
            ->toArray();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_info_success'), 'data' => $tags]);
    }

}
