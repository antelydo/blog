<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiTool;
use app\common\model\AiToolTag;
use app\common\model\AiToolTagRelation;
use app\common\model\AiToolCategoryRelation;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;

/**
 * AI Tool Management Controller
 * @route('adminAiTool')
 */
class AdminAiTool extends BaseController
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
     * Get tool list
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
        $categoryId = input('category_id', 0);
        $categoryIdsStr = input('category_ids_str', '');
        $status = input('status', '');
        $isRecommended = input('is_recommended', '');
        $isTop = input('is_top', '');
        $pricingType = input('pricing_type', '');
        $tagId = input('tag_id', 0);
        $orderField = input('order_field', 'create_time');
        $orderType = input('order_type', 'desc');

        // 构建查询条件
        $query = AiTool::withSearch(['name', 'category_id', 'status', 'is_recommended', 'is_top', 'pricing_type', 'tag_id', 'category_ids'], [
            'name' => $keyword,
            'category_id' => $categoryId,
            'category_ids' => $categoryIdsStr, // 添加多分类筛选
            'status' => $status,
            'is_recommended' => $isRecommended !== '' ? (bool)$isRecommended : '',
            'is_top' => $isTop !== '' ? (bool)$isTop : '',
            'pricing_type' => $pricingType,
            'tag_id' => $tagId,
        ]);

        // 记录日志，方便调试
        $this->log('list query params', [
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'categoryIdsStr' => $categoryIdsStr,
            'status' => $status,
            'isRecommended' => $isRecommended,
            'isTop' => $isTop,
            'pricingType' => $pricingType,
            'tagId' => $tagId,
        ]);

        // 排序
        $allowedOrderFields = ['id', 'create_time', 'update_time', 'publish_time', 'views', 'likes', 'favorites', 'comments', 'rating', 'sort_order'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order($orderField, $orderType);
        } else {
            $query->order('create_time', 'desc');
        }

        // 分页查询
        $query= $query->with(['category']);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        //list =$this->getPageList($querys,$page,$limit);

        // 获取工具标签和分类
        $toolIds = $query->column('id');
        $tagRelations = [];
        $categoryRelations = [];

        if (!empty($toolIds)) {
            // 获取标签关联
            $tagRelations = Db::name('ai_tool_tag_relation')
                ->alias('r')
                ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
                ->field('r.tool_id, r.tag_id, t.name as tag_name')
                ->whereIn('r.tool_id', $toolIds)
                ->select()
                ->toArray();

            // 获取分类关联
            $categoryRelations = Db::name('ai_tool_category_relation')
                ->alias('r')
                ->join('ai_ai_tool_category c', 'r.category_id = c.id')
                ->field('r.tool_id, r.category_id, c.name as category_name')
                ->whereIn('r.tool_id', $toolIds)
                ->select()
                ->toArray();
        }

        // 整理标签数据
        $toolTags = [];
        foreach ($tagRelations as $relation) {
            $toolTags[$relation['tool_id']][] = [
                'id' => $relation['tag_id'],
                'name' => $relation['tag_name'],
            ];
        }

        // 整理分类数据
        $toolCategories = [];
        foreach ($categoryRelations as $relation) {
            $toolCategories[$relation['tool_id']][] = [
                'id' => $relation['category_id'],
                'name' => $relation['category_name'],
            ];
        }

        // 将标签和分类数据添加到工具列表中
        foreach ($list as &$tool) {
            $tool['tags'] = $toolTags[$tool['id']] ?? [];
            $tool['categories'] = $toolCategories[$tool['id']] ?? [];
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tool_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }

    /**
     * Get tool details
     * @route('info', 'get')
     */
    public function info(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        $tool = AiTool::with(['category'])->find($id);

        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 获取工具标签
        $tags = Db::name('ai_tool_tag_relation')
            ->alias('r')
            ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
            ->field('t.id, t.name')
            ->where('r.tool_id', $id)
            ->select()
            ->toArray();

        // 获取工具分类
        $categories = Db::name('ai_tool_category_relation')
            ->alias('r')
            ->join('ai_ai_tool_category c', 'r.category_id = c.id')
            ->field('c.id, c.name')
            ->where('r.tool_id', $id)
            ->select()
            ->toArray();

        $tool['tags'] = $tags;
        $tool['categories'] = $categories;

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tool_info_success'), 'data' => $tool]);
    }

    /**
     * Create tool
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
        $validate = validate('AiTool');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // 检查别名是否已存在
        $exists = AiTool::where('slug', $data['slug'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_slug_exists')]);
        }

        // 处理标签和分类
        $tags = isset($data['tags']) ? $data['tags'] : [];
        $categories = isset($data['categories']) ? $data['categories'] : [];
        unset($data['tags']);
        unset($data['categories']);

        // 如果状态为已发布，设置发布时间
        if ($data['status'] == 'published') {
            $data['publish_time'] = time();
        }

        Db::startTrans();
        try {
            // 创建工具
            $tool = new AiTool;
            $tool->save($data);

            // 处理标签关联
            $this->handleTags($tool->id, $tags);

            // 处理分类关联
            $this->handleCategories($tool->id, $categories);

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_create', Lang::get('aiTool.create_tool_log'), [
                'tool_id' => $tool->id,
                'tool_name' => $tool->name,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.create_tool_success'), 'data' => ['id' => $tool->id]]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Update tool
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 验证数据
        $validate = validate('AiTool');
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }

        // 检查工具是否存在
        $tool = AiTool::find($data['id']);
        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查别名是否已存在（排除自身）
        $exists = AiTool::where('slug', $data['slug'])->where('id', '<>', $data['id'])->find();
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_slug_exists')]);
        }

        // 处理标签和分类
        $tags = isset($data['tags']) ? $data['tags'] : [];
        $categories = isset($data['categories']) ? $data['categories'] : [];
        unset($data['tags']);
        unset($data['categories']);

        // 如果状态从非已发布变为已发布，设置发布时间
        if ($tool->status !== 'published' && $data['status'] == 'published') {
            $data['publish_time'] = time();
        }


        Db::startTrans();
        try {
            // 更新工具
            $tool->save($data);

            // 处理标签关联
            $this->handleTags($tool->id, $tags);

            // 处理分类关联
            $this->handleCategories($tool->id, $categories);

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_update', Lang::get('aiTool.update_tool_log'), [
                'tool_id' => $tool->id,
                'tool_name' => $tool->name,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tool_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Delete tool
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
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 检查工具是否存在
        $tool = AiTool::find($id);
        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        Db::startTrans();
        try {
            // 删除标签关联
            AiToolTagRelation::where('tool_id', $id)->delete();

            // 删除分类关联
            AiToolCategoryRelation::where('tool_id', $id)->delete();

            // 删除工具
            $tool->delete();

            // 更新标签计数
            $tagIds = AiToolTagRelation::where('tool_id', $id)->column('tag_id');
            foreach ($tagIds as $tagId) {
                AiToolTag::updateCount($tagId);
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_delete', Lang::get('aiTool.delete_tool_log'), [
                'tool_id' => $id,
                'tool_name' => $tool->name,
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.delete_tool_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Update tool recommendation status
     * @route('recommend', 'post')
     */
    public function recommend(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $isRecommended = input('is_recommended', null);

        if (!$id || $isRecommended === null) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查工具是否存在
        $tool = AiTool::find($id);
        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 更新推荐状态
        $tool->is_recommended = (bool)$isRecommended;
        $tool->save();

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_recommend', Lang::get('aiTool.update_tool_recommend_log'), [
            'tool_id' => $id,
            'tool_name' => $tool->name,
            'is_recommended' => (bool)$isRecommended,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tool_recommend_success')]);
    }

    /**
     * Update tool top status
     * @route('top', 'post')
     */
    public function top(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $isTop = input('is_top', null);

        if (!$id || $isTop === null) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查工具是否存在
        $tool = AiTool::find($id);
        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 更新置顶状态
        $tool->is_top = (bool)$isTop;
        $tool->save();

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_top', Lang::get('aiTool.update_tool_top_log'), [
            'tool_id' => $id,
            'tool_name' => $tool->name,
            'is_top' => (bool)$isTop,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tool_top_success')]);
    }

    /**
     * Update tool status
     * @route('updateStatus', 'post')
     */
    public function updateStatus(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $status = input('status', '');

        if (!$id || !$status) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查状态是否有效
        $validStatus = ['draft', 'published', 'pending'];
        if (!in_array($status, $validStatus)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.invalid_status')]);
        }

        // 检查工具是否存在
        $tool = AiTool::find($id);
        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        // 如果状态从非已发布变为已发布，设置发布时间
        if ($tool->status != 'published' && $status == 'published') {
            $tool->publish_time = time();
        }

        // 更新状态
        $tool->status = $status;
        $tool->save();

        // 记录操作日志
        $this->recordActivity($this->userId, 'admin', 'ai_tool_update_status', Lang::get('aiTool.update_tool_status_log'), [
            'tool_id' => $id,
            'tool_name' => $tool->name,
            'status' => $status,
        ]);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.update_tool_status_success')]);
    }

    /**
     * Batch operations
     * @route('batch', 'post')
     */
    public function batch(): Json
    {
        // Check if logged in
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $ids = input('ids/a', []);
        $action = input('action', '');

        if (empty($ids) || !$action) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.params_error')]);
        }

        // 检查操作类型是否有效
        $validActions = ['delete', 'recommend', 'unrecommend', 'top', 'untop', 'publish', 'draft', 'pending'];
        if (!in_array($action, $validActions)) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.invalid_action')]);
        }

        // 检查工具是否存在
        $tools = AiTool::whereIn('id', $ids)->select();
        if (count($tools) == 0) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        Db::startTrans();
        try {
            foreach ($tools as $tool) {
                switch ($action) {
                    case 'delete':
                        // 删除标签关联
                        AiToolTagRelation::where('tool_id', $tool->id)->delete();
                        // 删除分类关联
                        AiToolCategoryRelation::where('tool_id', $tool->id)->delete();
                        // 删除工具
                        $tool->delete();
                        break;
                    case 'recommend':
                        $tool->is_recommended = true;
                        $tool->save();
                        break;
                    case 'unrecommend':
                        $tool->is_recommended = false;
                        $tool->save();
                        break;
                    case 'top':
                        $tool->is_top = true;
                        $tool->save();
                        break;
                    case 'untop':
                        $tool->is_top = false;
                        $tool->save();
                        break;
                    case 'publish':
                        if ($tool->status != 'published') {
                            $tool->publish_time = time();
                        }
                        $tool->status = 'published';
                        $tool->save();
                        break;
                    case 'draft':
                        $tool->status = 'draft';
                        $tool->save();
                        break;
                    case 'pending':
                        $tool->status = 'pending';
                        $tool->save();
                        break;
                }
            }

            // 如果是删除操作，更新标签计数
            if ($action == 'delete') {
                $tagIds = AiToolTagRelation::whereIn('tool_id', $ids)->column('tag_id');
                foreach (array_unique($tagIds) as $tagId) {
                    AiToolTag::updateCount($tagId);
                }
            }

            Db::commit();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'ai_tool_batch_' . $action, Lang::get('aiTool.batch_' . $action . '_log'), [
                'tool_ids' => $ids,
                'count' => count($tools),
            ]);

            return json(['code' => 200, 'msg' => Lang::get('aiTool.batch_' . $action . '_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Handle tool tag relations
     * @param int $toolId Tool ID
     * @param array $tags Tag data
     */
    protected function handleTags(int $toolId, array $tags)
    {
        // Delete existing tag relations
        AiToolTagRelation::where('tool_id', $toolId)->delete();

        // Add new tag relations
        if (!empty($tags)) {
            $tagIds = [];

            foreach ($tags as $tag) {
                // If it's a new tag, create it first
                if (isset($tag['id']) && $tag['id'] > 0) {
                    $tagIds[] = $tag['id'];
                } else if (isset($tag['name']) && !empty($tag['name'])) {
                    // Check if tag already exists
                    $existTag = AiToolTag::where('name', $tag['name'])->find();

                    if ($existTag) {
                        $tagIds[] = $existTag->id;
                    } else {
                        // Create new tag
                        $slug = $this->generateSlug($tag['name']);
                        $newTag = AiToolTag::create([
                            'name' => $tag['name'],
                            'slug' => $slug,
                        ]);

                        $tagIds[] = $newTag->id;
                    }
                }
            }

            // Add tag relations
            foreach ($tagIds as $tagId) {
                AiToolTagRelation::create([
                    'tool_id' => $toolId,
                    'tag_id' => $tagId,
                    'create_time' => time()
                ]);

                // Update tag count
                AiToolTag::updateCount($tagId);
            }
        }
    }

    /**
     * Generate slug
     * @param string $name Name
     * @return string Slug
     */
    protected function generateSlug(string $name): string
    {
        // Convert to lowercase
        $slug = strtolower($name);
        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);
        // Remove special characters
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        // Remove consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');

        // If empty, use a random string
        if (empty($slug)) {
            $slug = 'tag-' . uniqid();
        }

        // Check if slug already exists
        $count = 0;
        $originalSlug = $slug;

        while (AiToolTag::where('slug', $slug)->find()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }

        return $slug;
    }

    /**
     * Handle tool category relations
     * @param int $toolId Tool ID
     * @param array $categories Category data
     */
    protected function handleCategories(int $toolId, array $categories)
    {
        // Log for debugging
        $this->log('handleCategories', [
            'toolId' => $toolId,
            'categories' => $categories
        ]);

        // Delete existing category relations
        AiToolCategoryRelation::where('tool_id', $toolId)->delete();

        // Add new category relations
        if (!empty($categories)) {
            $categoryIds = [];

            foreach ($categories as $category) {
                // Handle different data formats
                if (isset($category['id']) && $category['id'] > 0) {
                    $categoryIds[] = $category['id'];
                } elseif (is_numeric($category)) {
                    // If it's a numeric ID directly
                    $categoryIds[] = (int)$category;
                }
            }

            // Remove duplicates
            $categoryIds = array_unique($categoryIds);

            // Log for debugging
            $this->log('categoryIds', $categoryIds);

            // Add category relations
            foreach ($categoryIds as $categoryId) {
                AiToolCategoryRelation::create([
                    'tool_id' => $toolId,
                    'category_id' => $categoryId,
                    'create_time' => time()
                ]);
            }
        }
    }

    /**
     * Log for debugging
     * @param string $title Log title
     * @param mixed $content Log content
     */
    private function log($title, $content)
    {
        $logFile = runtime_path() . 'log/ai_tool_' . date('Y-m-d') . '.log';
        $logContent = '[' . date('Y-m-d H:i:s') . '] ' . $title . ': ' . json_encode($content, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($logFile, $logContent, FILE_APPEND);
    }
}
