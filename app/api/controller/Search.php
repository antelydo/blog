<?php

declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;




/**
 * 搜索控制器
 * @route('Search')
 * @route('Search/query', 'get')
 *
 *
 */


class Search extends BaseController
{
    /**
     * 搜索
     * @route('query', 'get')
     */
    public function index()
    {
        return $this->querySearch();
    }



    public function querySearch()
    {
        // 获取搜索关键词 过滤特殊字符非法字符
        $keyword = Request::param('query');
        $keyword = htmlspecialchars($keyword);
        $keyword = trim($keyword);
        $keyword = mb_strtolower($keyword);
        $page = (int)Request::param('page', 1);
        $limit = (int)Request::param('limit', 10);

        if (empty($keyword)) {
            return $this->error(Lang::get('blog.please_input_search_keyword'));
        }

        //先根据关键字搜索 标签与文章关联表 并获取对应的文章ID
        $tagPostIds = Db::name('blog_post_tag')->alias('tp')
            ->join('blog_tag t', 'tp.tag_id = t.id')
            ->where('t.name', 'like', '%' . $keyword . '%')
            ->where('t.status', 1)
            ->distinct(true)
            ->column('post_id');


        //再根据关键字搜索 分类与文章关联表 并获取对应的文章ID
        $categoryPostIds = Db::name('blog_post_category')->alias('cp')
            ->join('blog_category c', 'cp.category_id = c.id')
            ->where('c.name', 'like', '%' . $keyword . '%')
            ->where('c.status', 1)
            ->distinct(true)
            ->column('post_id');


        //最后根据关键字搜索 文章表 和标签搜索出的文章ID 和分类搜索出的文章ID 进行合并
        $postIds = array_merge($tagPostIds, $categoryPostIds);
        $postIds = array_unique($postIds);

        // 排序参数
        $orderParams = [];

        //根据文章ID搜索文章
        $query = Db::name('blog_post')
            //->field('id,title,created_at,updated_at,view_count,comment_count,like_count,share_count,thumbnail,content')
            ->where('title ', 'like', '%' . $keyword . '%')
            ->whereOr('content', 'like', '%' . $keyword . '%')
            ->whereOr('description', 'like', '%' . $keyword . '%')
            ->whereOr('id', 'in', $postIds)
            ->where('status', 1);


        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, [], $orderParams);
        // 返回结果
        // 获取文章分类
        if ($result['list']) {
            $postIds = array_column($result['list'], 'id');
            $postCategories = Db::name('blog_post_category')
                ->alias('pc')
                ->join('blog_category c', 'pc.category_id = c.id')
                ->where('pc.post_id', 'in', $postIds)
                ->where('c.status', 1)
                ->field('pc.post_id, c.id as category_id, c.name as category_name')
                ->select()
                ->toArray();

            $postCategoryMap = [];
            foreach ($postCategories as $cat) {
                if (!isset($postCategoryMap[$cat['post_id']])) {
                    $postCategoryMap[$cat['post_id']] = [];
                }
                $postCategoryMap[$cat['post_id']][] = [
                    'id' => $cat['category_id'],
                    'name' => $cat['category_name']
                ];
            }

            // 获取文章标签
            $postTags = Db::name('blog_post_tag')
                ->alias('pt')
                ->join('blog_tag t', 'pt.tag_id = t.id')
                ->where('pt.post_id', 'in', $postIds)
                ->where('t.status', 1)
                ->field('pt.post_id, t.id as tag_id, t.name as tag_name')
                ->select()
                ->toArray();

            $postTagMap = [];
            foreach ($postTags as $tag) {
                if (!isset($postTagMap[$tag['post_id']])) {
                    $postTagMap[$tag['post_id']] = [];
                }
                $postTagMap[$tag['post_id']][] = [
                    'id' => $tag['tag_id'],
                    'name' => $tag['tag_name']
                ];
            }

            // 添加作者信息、分类和标签到文章数据
            foreach ($result['list'] as &$post) {
                $post['categories'] = $postCategoryMap[$post['id']] ?? [];
                $post['tags'] = $postTagMap[$post['id']] ?? [];

                // 获取作者信息
                if ($post['author_type'] == 'admin') {
                    $author = Db::name('admin')->field('id, username, nickname, avatar')->find($post['author_id']);
                } else {
                    $author = Db::name('user')->field('id, username, nickname, avatar')->find($post['author_id']);
                }

                $post['author'] = $author ?? ['id' => 0, 'username' => Lang::get('blog.unknown'), 'nickname' => Lang::get('blog.unknown')];
            }

            // 检查当前用户是否已点赞
            $post['is_liked'] = false;
            if ($this->isLogin()) {
                $liked = Db::name('blog_like')
                    ->where('post_id', $post['id'])
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->find();

                $post['is_liked'] = (bool)$liked;
            }
        }

        return json(['code' => 200, 'msg' => Lang::get('blog.get_post_list_successful'), 'data' => $result]);
    }
}
