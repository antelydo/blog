<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;

/**
 * 前端分类控制器
 * @route('category')
 */
class Cat extends BaseController
{

    /**
     * 用户类型
     * @var string
     */
    protected string $userType;

    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        $this->userId = $this->getUserId();
        //parent::initialize();
    }



    /**
     * 获取分类列表 并统计 每一个分类下的文章数量
     * @route('getCatList', 'get')
     */
    public function getCatList(): Json
    {
        $status = (int)input('status','');
        $page= (int)input('page',1);
        $limit = (int)input('limit',10);
        $category_id = (int)input('category_id',0);
        $keyword = input('keyword','');
        //是否获取分类下的文章统计
        $is_get_post_count = (int)input('is_get_post_count',0);
        //
        $is_get_post = (int)input('is_get_post',0);

        $where = [];
        if($status){
            $where[] = ['status', '=', $status];
        }
        if($keyword){
            $where[] = ['name', 'like', '%'.$keyword.'%'];
        }
        if($category_id){
            $where[] = ['category_id', '=', $category_id];
        }


        $query = Db::name('blog_category');
        $list = $this->getPageList($query,$page,$limit,$where);
        if($list['list']){
            foreach($list['list'] as $key => $value){
                if($is_get_post_count){
                    $list['list'][$key]['post_count'] = Db::name('blog_post_category')->alias('pt')
                        ->join('blog_post p', 'pt.post_id = p.id')
                        ->where('pt.category_id', $value['id'])
                        ->where('p.status', 1) // 只统计已发布的文章
                        ->distinct(true)->count('pt.id');
                }else{
                    $list['list'][$key]['post_count'] =0;
                }

                if($is_get_post){
                    $list['list'][$key]['post'] = Db::name('blog_post_category')->alias('pt')
                        ->join('blog_post p', 'pt.post_id = p.id')
                        ->where('pt.category_id', $value['id'])
                        ->where('p.status', 1) // 只统计已发布的文章
                        ->field('p.id,p.title')
                        ->order('p.publish_time  desc')
                        ->limit(1)
                        ->select()
                        ->toArray();
                }else{
                    $list['list'][$key]['post'] =[];
                }
            }
        }

        return json(['code' => 200, 'msg' => Lang::get('blog.get_category_list_successful'), 'data' => $list]);

    }


    /**
     * 获取分类列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $where = [
            ['status', '=', 1], // 只显示启用的分类
        ];

        $categories = Db::name('blog_category')
            ->where($where)
            ->order('sort asc, id asc')
            ->select()
            ->toArray();

        // 构建分类树
        $tree = $this->buildCategoryTree($categories);

        return json(['code' => 200, 'msg' => Lang::get('blog.get_category_list_successful'), 'data' => $tree]);
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
     * 获取分类详情
     * @route('detail', 'get')
     */
    public function detail(): Json
    {
        $id = input('id', 0);


        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }

        $category = Db::name('blog_category')
            ->where('id', $id)
            ->where('status', 1) // 只显示启用的分类
            ->find();

        if (!$category) {
            return json(['code' => 400, 'msg' => Lang::get('blog.category_not_exist')]);
        }

        // 获取该分类下的文章数量
        $postCount = Db::name('blog_post_category')
            ->alias('pc')
            ->join('blog_post p', 'pc.post_id = p.id')
            ->where('pc.category_id', $id)
            ->where('p.status', 1) // 只统计已发布的文章
            ->count();

        $category['post_count'] = $postCount;

        // 获取子分类
        $children = Db::name('blog_category')
            ->where('parent_id', $id)
            ->where('status', 1)
            ->order('sort asc, id asc')
            ->select()
            ->toArray();

        $category['children'] = $children;

        return json(['code' => 200, 'msg' => Lang::get('blog.get_category_successful'), 'data' => $category]);
    }

    /**
     * 获取所有分类及其文章数量
     * @route('withCount', 'get')
     */
    public function withCount(): Json
    {
        // 获取所有启用的分类
        $categories = Db::name('blog_category')
            ->where('status', 1)
            ->order('sort asc, id asc')
            ->select()
            ->toArray();

        // 获取每个分类下的文章数量
        $postCounts = Db::name('blog_post_category')
            ->alias('pc')
            ->join('blog_post p', 'pc.post_id = p.id')
            ->where('p.status', 1) // 只统计已发布的文章
            ->field('pc.category_id, COUNT(pc.post_id) as count')
            ->group('pc.category_id')
            ->select()
            ->toArray();

        $countMap = [];
        foreach ($postCounts as $count) {
            $countMap[$count['category_id']] = $count['count'];
        }

        // 将文章数量添加到分类数据中
        foreach ($categories as &$category) {
            $category['post_count'] = $countMap[$category['id']] ?? 0;
        }

        // 构建分类树
        $tree = $this->buildCategoryTree($categories);

        return json(['code' => 200, 'msg' => Lang::get('blog.get_category_list_successful'), 'data' => [
            'list' => $tree,
            'total' => count($categories)
        ]]);
    }
}