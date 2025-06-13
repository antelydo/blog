<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use app\common\Token;
use think\facade\Request;
/**
 * 文章分类管理控制器
 * @route('admin/category')
 */
class AdminCat extends BaseController
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();
    }
    
    /**
     * 获取分类列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $where = [];
        $name = input('name', '');
        $status = input('status', '');
        $page = input('page', 1);
        $limit = input('limit',100);

        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        
        if ($status !== '') {
            $where[] = ['status', '=', intval($status)];
        }
        
        $query = Db::name('blog_category');
        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, $where, ['sort asc', 'id asc']);
            
        // 构建分类树
        $tree = $this->buildCategoryTree($result['list']);
        return json(['code' => 200, 'msg' => Lang::get('blog.get_category_list_successful'), 'data' => [
            'list' => $tree,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit']
        ]]);
    }
    
    /**
     * 构建分类树
     * @param array $categories 分类数组
     * @param int $parentId 父ID
     * @return array
     */
    private function buildCategoryTree(array $categories, int $parentId = 0): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildCategoryTree($categories, $category['id']);
                
                if ($children) {
                    $category['children'] = $children;
                }
                
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
    
    /**
     * 创建分类
     * @route('create', 'post')
     */
    public function create(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $name = input('name', '');
        $description = input('description', '');
        $parentId = input('parent_id', 0);
        $sort = input('sort', 0);
        $status = input('status', 1);
        
        if (!$name) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_name_required')]);
        }
        
        // 检查名称是否重复
        $exists = Db::name('blog_category')
            ->where('name', $name)
            ->find();
            
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_already_exists')]);
        }
        
        $now = time();
        $path = '0';
        $level = 1;
        
        // 如果有父级分类，设置path和level
        if ($parentId > 0) {
            $parent = Db::name('blog_category')->find($parentId);
            
            if (!$parent) {
                return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
            }
            
            $path = $parent['path'] . ',' . $parentId;
            $level = $parent['level'] + 1;
        }
        
        $data = [
            'name' => $name,
            'description' => $description,
            'parent_id' => $parentId,
            'path' => $path,
            'level' => $level,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'create_time' => $now,
            'update_time' => $now,
        ];
        
        $id = Db::name('blog_category')->insertGetId($data);
        
        if ($id) {
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_category_create', Lang::get('blog.create_category_log') . ': ' . $name, [
                'category_id' => $id,
                'name' => $name,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.create_category_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.create_category_failed')]);
        }
    }
    
    /**
     * 更新分类
     * @route('update', 'post')
     */
    public function update(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        $name = input('name', '');
        $description = input('description', '');
        $parentId = input('parent_id', 0);
        $sort = input('sort', 0);
        $status = input('status', 1);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }
        
        $category = Db::name('blog_category')->find($id);
        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }
        
        if (!$name) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_name_required')]);
        }
        
        // 检查名称是否重复(排除自己)
        $exists = Db::name('blog_category')
            ->where('name', $name)
            ->where('id', '<>', $id)
            ->find();
            
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_already_exists')]);
        }
        
        // 不能将自己设为自己的父级
        if ($parentId == $id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.cant_set_self_as_parent')]);
        }
        
        // 不能将自己的子分类设为自己的父级
        if ($parentId > 0) {
            $parent = Db::name('blog_category')->find($parentId);
            
            if (!$parent) {
                return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
            }
            
            if (strpos($parent['path'], ',' . $id . ',') !== false) {
                return json(['code' => 400, 'msg' => Lang::get('blog.cant_set_child_as_parent')]);
            }
        }
        
        $path = '0';
        $level = 1;
        
        // 如果有父级分类，设置path和level
        if ($parentId > 0) {
            $parent = Db::name('blog_category')->find($parentId);
            $path = $parent['path'] . ',' . $parentId;
            $level = $parent['level'] + 1;
        }
        
        $data = [
            'name' => $name,
            'description' => $description,
            'parent_id' => $parentId,
            'path' => $path,
            'level' => $level,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'update_time' => time(),
        ];
        
        $result = Db::name('blog_category')->where('id', $id)->update($data);
        
        if ($result) {
            // 如果更新了父级，需要更新所有子分类的path和level
            if ($category['parent_id'] != $parentId) {
                $this->updateChildrenPath($id);
            }
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_category_update', Lang::get('blog.update_category_log') . ': ' . $name, [
                'category_id' => $id,
                'name' => $name,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.update_category_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.update_category_failed')]);
        }
    }
    
    /**
     * 更新子分类的path和level
     * @param int $parentId 父ID
     */
    private function updateChildrenPath(int $parentId): void
    {
        // 获取父级分类信息
        $parent = Db::name('blog_category')->find($parentId);
        if (!$parent) {
            return;
        }
        
        // 获取所有子分类
        $children = Db::name('blog_category')
            ->where('parent_id', $parentId)
            ->select()
            ->toArray();
            
        if (!$children) {
            return;
        }
        
        foreach ($children as $child) {
            // 更新path和level
            $newPath = $parent['path'] . ',' . $parentId;
            $newLevel = $parent['level'] + 1;
            
            Db::name('blog_category')
                ->where('id', $child['id'])
                ->update([
                    'path' => $newPath,
                    'level' => $newLevel,
                    'update_time' => time(),
                ]);
                
            // 递归更新子分类
            $this->updateChildrenPath($child['id']);
        }
    }
    
    /**
     * 删除分类
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }
        
        $category = Db::name('blog_category')->find($id);
        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }
        
        // 检查是否有子分类
        $hasChildren = Db::name('blog_category')
            ->where('parent_id', $id)
            ->find();
            
        if ($hasChildren) {
            return json(['code' => 400, 'msg' => Lang::get('blog.has_subcategories')]);
        }
        
        // 检查是否有文章使用此分类
        $hasPosts = Db::name('blog_post_category')
            ->where('category_id', $id)
            ->find();
            
        if ($hasPosts) {
            return json(['code' => 400, 'msg' => Lang::get('blog.has_posts')]);
        }
        
        $result = Db::name('blog_category')->where('id', $id)->delete();
        
        if ($result) {
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_category_delete', Lang::get('blog.delete_category_log') . ': ' . $category['name'], [
                'category_id' => $id,
                'name' => $category['name'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.delete_category_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.delete_category_failed')]);
        }
    }

    /**
     * 修改状态
     * @route('updateStatus', 'post')
     */
    public function updateStatus(){

      if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        $status = input('status',-1);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }

        if ($status ==-1) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_status_error')]);
        }
        
        $data = [
            'status' => (int)$status,
            'update_time' => time(),
        ];

        $has_one =Db::name('blog_category')->where('id', $id)->value('name');
        if(!$has_one){
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }
        
        $result = Db::name('blog_category')->where('id', $id)->update($data);
        
        if ($result) {
         
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_category_update', Lang::get('blog.update_category_log') . ': ' . $has_one, [
                'category_id' => $id,
                'name' => $has_one,
                'status' => $status,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.update_category_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.update_category_failed')]);
        }

    }
}