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
 * AI Tool Tag Management Controller
 * @route('adminAiToolTag')
 */
class AdminAiToolTag extends BaseController
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
     * Get tag list
     * @route('list', 'get')
     */
    public function list(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $keyword = input('keyword', '');
        $orderField = input('order_field', 'count');
        $orderType = input('order_type', 'desc');
        $isShow = input('is_show', '');

        // 构建查询条件
        $query = AiToolTag::withSearch(['name', 'is_show'], [
            'name' => $keyword,
            'is_show' => $isShow,
        ]);

        // 使用搜索器进行筛选

        // 排序
        $allowedOrderFields = ['id', 'name', 'count', 'create_time', 'update_time'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order($orderField, $orderType);
        } else {
            $query->order('count', 'desc');
        }

        // 分页查询
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }

    /**
     * 获取所有标签（不分页）
     * @route('all', 'get')
     */
    public function all(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $keyword = input('keyword', '');
        $isShow = input('is_show', '');

        // 构建查询条件
        $query = AiToolTag::withSearch(['name', 'is_show'], [
            'name' => $keyword,
            'is_show' => $isShow,
        ]);

        // 排序
        $query->order('count', 'desc');

        // 查询
        $list = $query->select();

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_list_success'), 'data' => $list]);
    }

    /**
     * 获取标签详情
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        $tag = AiToolTag::find($id);

        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tag_info_success'), 'data' => $tag]);
    }

    /**
     * Create tag
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
        $validate = validate('AiToolTag');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // 检查别名是否已存在
        $exists = AiToolTag::where('slug', $data['slug'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_slug_exists')]);
        }

        // 检查名称是否已存在
        $exists = AiToolTag::where('name', $data['name'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_name_exists')]);
        }

        // 创建标签
        $tag = new AiToolTag;
        $tag->save($data);

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_create', Lang::get('aiTool.create_tag_log'), [
            'tag_id' => $tag->id,
            'tag_name' => $tag->name,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.create_tag_success'), 'data' => $tag]);
    }

    /**
     * Update tag
     * @route('update', 'post')
     */
    public function update(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $data = input('post.');

        if (!isset($data['id']) || !$data['id']) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        // 验证数据
        $validate = validate('AiToolTag');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // 检查标签是否存在
        $tag = AiToolTag::find($data['id']);
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        // 检查别名是否已存在（排除自身）
        $exists = AiToolTag::where('slug', $data['slug'])->where('id', '<>', $data['id'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_slug_exists')]);
        }

        // 检查名称是否已存在（排除自身）
        $exists = AiToolTag::where('name', $data['name'])->where('id', '<>', $data['id'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_name_exists')]);
        }

        // 更新标签
        $tag->save($data);

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_update', Lang::get('aiTool.update_tag_log'), [
            'tag_id' => $tag->id,
            'tag_name' => $tag->name,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tag_success')]);
    }

    /**
     * Delete tag
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        // 检查标签是否存在
        $tag = AiToolTag::find($id);
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        Db::startTrans();
        try {
            // 删除标签关联
            AiToolTagRelation::where('tag_id', $id)->delete();

            // 删除标签
            $tag->delete();

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_delete', Lang::get('aiTool.delete_tag_log'), [
                'tag_id' => $id,
                'tag_name' => $tag->name,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_tag_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Batch delete tags
     * @route('batchDelete', 'post')
     */
    public function batchDelete(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $ids = input('ids/a', []);

        if (empty($ids)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查标签是否存在
        $tags = AiToolTag::whereIn('id', $ids)->select();
        if (count($tags) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        Db::startTrans();
        try {
            // 删除标签关联
            AiToolTagRelation::whereIn('tag_id', $ids)->delete();

            // 删除标签
            AiToolTag::whereIn('id', $ids)->delete();

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_batch_delete', Lang::get('aiTool.batch_delete_tag_log'), [
                'tag_ids' => $ids,
                'count' => count($tags),
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_delete_tag_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Update tag display status
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

        // Check if tag exists
        $tag = AiToolTag::find($id);
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        // Update display status
        $tag->is_show = (bool)$isShow;
        $tag->save();

        // Record activity log
        $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_update_status', Lang::get('aiTool.update_tag_status_log'), [
            'tag_id' => $id,
            'tag_name' => $tag->name,
            'is_show' => (bool)$isShow,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tag_status_success')]);
    }

    /**
     * Merge tags
     * @route('merge', 'post')
     */
    public function merge(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $sourceIds = input('source_ids/a', []);
        $targetId = input('target_id', 0);

        if (empty($sourceIds) || !$targetId) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // Check if target tag exists
        $targetTag = AiToolTag::find($targetId);
        if (!$targetTag) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        // Remove target tag ID (if it exists in source tag IDs)
        $sourceIds = array_diff($sourceIds, [$targetId]);

        if (empty($sourceIds)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.merge_tag_error')]);
        }

        // Check if source tags exist
        $sourceTags = AiToolTag::whereIn('id', $sourceIds)->select();
        if (count($sourceTags) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tag_not_exist')]);
        }

        Db::startTrans();
        try {
            // Get tool IDs associated with source tags
            $toolIds = AiToolTagRelation::whereIn('tag_id', $sourceIds)->column('tool_id');

            // Get tool IDs already associated with target tag
            $existingToolIds = AiToolTagRelation::where('tag_id', $targetId)->column('tool_id');

            // Tool IDs that need to be associated (excluding already associated ones)
            $newToolIds = array_diff($toolIds, $existingToolIds);

            // Add new associations
            foreach ($newToolIds as $toolId) {
                AiToolTagRelation::create([
                    'tool_id' => $toolId,
                    'tag_id' => $targetId,
                ]);
            }

            // Delete source tag associations
            AiToolTagRelation::whereIn('tag_id', $sourceIds)->delete();

            // Delete source tags
            AiToolTag::whereIn('id', $sourceIds)->delete();

            // Update target tag count
            AiToolTag::updateCount($targetId);

            Db::commit();

            // Record activity log
            $this->recordActivity($this->userId, 'admin', 'ai_tool_tag_merge', Lang::get('aiTool.merge_tag_log'), [
                'source_tag_ids' => $sourceIds,
                'target_tag_id' => $targetId,
                'target_tag_name' => $targetTag->name,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.merge_tag_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }
}
