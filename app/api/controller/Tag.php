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
 * 前端标签控制器
 * @route('tag')
 */
class Tag extends BaseController
{    

     /**
     * 用户类型
     * @var string
     */
    protected string $userType;

    /**
     * 用户ID
     * @var int
     */
    private int $user_id;

    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        parent::initialize();
        $this->user_id=$this->getUserId();
    }
    
    /**
     * 获取标签列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $is_get_post_count = (int)input('is_get_post_count', 0);
        $status = input('status', default: '');
        $keyword = input('keyword', default: '');
        $is_get_post = (int)input('is_get_post', 0);
        $where = [];
        if($status){
            $where[] = ['status', '=', $status];
        }
        if($keyword){
            $where[] = ['name', 'like', '%'.$keyword.'%'];
        }


        $query = Db::name('blog_tag');
        $list = $this->getPageList($query,$page,$limit,$where);
        if($list['list']){
            foreach($list['list'] as $key => $value){
                if($is_get_post_count){
                    $list['list'][$key]['post_count'] = Db::name('blog_post_tag')->alias('pt')
                    ->join('blog_post p', 'pt.post_id = p.id')
                    ->where('pt.tag_id', $value['id'])
                    ->where('p.status', 1) // 只统计已发布的文章
                    ->distinct(true)->count('p.id');
                }else{
                    $list['list'][$key]['post_count'] =0;
                }
                if($is_get_post){
                    $list['list'][$key]['post'] = Db::name('blog_post_tag')->alias('pt')
                    ->join('blog_post p', 'pt.post_id = p.id')
                    ->where('pt.tag_id', $value['id'])
                    ->where('p.status', 1) // 只统计已发布的文章
                    ->field('p.id,p.title')
                    ->order('p.views desc')
                    ->limit(1)
                    ->select()
                    ->toArray();
                }else{
                    $list['list'][$key]['post'] =[];
                }
            }   
        }
     
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_list_successful'), 'data' => $list]);
    }
    
    /**
     * 获取标签详情
     * @route('detail', 'get')
     */
    public function detail(): Json
    {
        $id = input('id', 0);
        
        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        $tag = Db::name('blog_tag')
            ->where('id', $id)
            ->where('status', 1) // 只显示启用的标签
            ->find();
        
        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }
        
        // 获取该标签下的文章数量
        $postCount = Db::name('blog_post_tag')
            ->alias('pt')
            ->join('blog_post p', 'pt.post_id = p.id')
            ->where('pt.tag_id', $id)
            ->where('p.status', 1) // 只统计已发布的文章
            ->count();
            
        $tag['post_count'] = $postCount;
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_successful'), 'data' => $tag]);
    }
    
    /**
     * 获取所有标签及其文章数量
     * @route('withCount', 'get')
     */
    public function withCount(): Json
    {
        // 获取所有启用的标签
        $tags = Db::name('blog_tag')
            ->where('status', 1)
            ->order('sort asc, id asc')
            ->select()
            ->toArray();
            
        // 获取每个标签下的文章数量
        $postCounts = Db::name('blog_post_tag')
            ->alias('pt')
            ->join('blog_post p', 'pt.post_id = p.id')
            ->where('p.status', 1) // 只统计已发布的文章
            ->field('pt.tag_id, COUNT(pt.post_id) as count')
            ->group('pt.tag_id')
            ->select()
            ->toArray();
            
        $countMap = [];
        foreach ($postCounts as $count) {
            $countMap[$count['tag_id']] = $count['count'];
        }
        
        // 将文章数量添加到标签数据中
        foreach ($tags as &$tag) {
            $tag['post_count'] = $countMap[$tag['id']] ?? 0;
        }
        
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_list_successful'), 'data' => $tags]);
    }
    
    /**
     * 获取热门标签
     * @route('hot', 'get')
     */
    public function hot(): Json
    {
        $limit = (int)input('limit', 10);
        
        // 获取文章数量最多的标签
        $hotTags = Db::name('blog_post_tag')
            ->alias('pt')
            ->join('blog_tag t', 'pt.tag_id = t.id')
            ->join('blog_post p', 'pt.post_id = p.id')
            ->where('t.status', 1) // 只显示启用的标签
            ->where('p.status', 1) // 只统计已发布的文章
            ->field('t.id, t.name, t.description, COUNT(pt.post_id) as post_count')
            ->group('pt.tag_id')
            ->order('post_count desc, t.sort asc')
            ->limit($limit)
            ->select()
            ->toArray();
            
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_list_successful'), 'data' => $hotTags]);
    }

    /**
     * 获取最新标签
     * @route('new', 'get')
     */
    public function new(): Json
    {
        $limit = (int)input('limit', 10);
        
        // 获取创建时间最新的标签
        $newTags = Db::name('blog_tag')
            ->where('status', 1)
            ->order('sort asc, id asc') 
            ->limit($limit)
            ->select()
            ->toArray();
            
        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_list_successful'), 'data' => $newTags]);
    }

    /**
     * 获取标签名称 和当前标签ID
     * @route('getTagName', 'get')
     */
    public function getTagName(): Json
    {
        $tagId = input('tag_id', 0);
        if (!$tagId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }

        $tag = Db::name('blog_tag')
            ->where('id', $tagId)
            ->where('status', 1)
            ->field('id, name,description')
            ->find();

        if (!$tag) {
            return json(['code' => 400, 'msg' => Lang::get('blog.tag_not_exist')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('blog.get_tag_successful'), 'data' => $tag]);
    }
            
}