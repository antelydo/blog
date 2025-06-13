<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use think\facade\Request;
use app\common\Token;
/**
 * 文章标签管理控制器
 * @route('admin/tag')
 */
class AdminTag extends BaseController
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
     * 获取标签列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $name = input('name', '');
        $status = input('status', '');
        $page = input('page', 1);
        $limit = input('limit', 10);

        
        $where = [];
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        
        if ($status !== '') {
            $where[] = ['status', '=', intval($status)];
        }
        
        $query = Db::name('blog_tag');
        
        // 使用统一的分页查询方法
        $result = $this->getPageList(query: $query,page: $page,limit: $limit,where: $where, order: ['sort asc', 'id asc']);
            
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_list_successful'), 'data' => $result]);
    }
    
    /**
     * 创建标签
     * @route('create', 'post')
     */
    public function create(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $name = input('name', '');
        $description = input('description', '');
        $sort = input('sort', 0);
        $status = input('status', 1);
        
        if (!$name) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_name_required')]);
        }
        
        // 检查名称是否重复
        $exists = Db::name('blog_tag')
            ->where('name', $name)
            ->find();
            
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_already_exists')]);
        }
        
        $now = time();
        $data = [
            'name' => $name,
            'description' => $description,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'create_time' => $now,
            'update_time' => $now,
        ];
        
        $id = Db::name('blog_tag')->insertGetId($data);
        
        if ($id) {
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_tag_create', Lang::get('blog.create_tag_log') . ': ' . $name, [
                'tag_id' => $id,
                'name' => $name,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.create_tag_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.create_tag_failed')]);
        }
    }
    
    /**
     * 更新标签
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
        $sort = input('sort', 0);
        $status = input('status', 1);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        $tag = Db::name('blog_tag')->find($id);
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        if (!$name) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_name_required')]);
        }
        
        // 检查名称是否重复(排除自己)
        $exists = Db::name('blog_tag')
            ->where('name', $name)
            ->where('id', '<>', $id)
            ->find();
            
        if ($exists) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_already_exists')]);
        }
        
        $data = [
            'name' => $name,
            'description' => $description,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'update_time' => time(),
        ];
        
        $result = Db::name('blog_tag')->where('id', $id)->update($data);
        
        if ($result) {
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_tag_update', Lang::get('blog.update_tag_log') . ': ' . $name, [
                'tag_id' => $id,
                'name' => $name,
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.update_tag_successful')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('blog.update_tag_failed')]);
        }
    }
    
    /**
     * 删除标签
     * @route('delete', 'post')
     */
    public function delete(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        
        $id = input('id', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        $tag = Db::name('blog_tag')->find($id);
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        Db::startTrans();
        try {
            // 删除标签
            Db::name('blog_tag')->where('id', $id)->delete();
            
            // 删除文章标签关联
            Db::name('blog_post_tag')->where('tag_id', $id)->delete();
            
            Db::commit();
            
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'blog_tag_delete', Lang::get('blog.delete_tag_log') . ': ' . $tag['name'], [
                'tag_id' => $id,
                'name' => $tag['name'],
            ]);
            
            return json(['code' => 200, 'msg' => Lang::get('blog.delete_tag_successful')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
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
              return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
          }
  
          if ($status ==-1) {
              return json(['code' => 400, 'msg' => Lang::get('blog.tag_status_error')]);
          }
          
          $data = [
              'status' => (int)$status,
              'update_time' => time(),
          ];
  
          $has_one =Db::name('blog_tag')->where('id', $id)->value('name');
          if(!$has_one){
              return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
          }
          
          $result = Db::name('blog_tag')->where('id', $id)->update($data);
          
          if ($result) {
           
              // 记录操作日志
              $this->recordActivity($this->userId, 'admin', 'blog_tag_update', Lang::get('blog.update_tag_log') . ': ' . $has_one, [
                  'category_id' => $id,
                  'name' => $has_one,
                  'status' => $status,
              ]);
              
              return json(['code' => 200, 'msg' => Lang::get('blog.update_tag_successful')]);
          } else {
              return json(['code' => 500, 'msg' => Lang::get('blog.update_tag_failed')]);
          }
  
      }
}