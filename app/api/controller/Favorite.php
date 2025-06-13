<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\facade\Request;
use think\facade\Validate;
use think\response\Json;

/**
 * 文章收藏控制器
 * @route('favorite')
 */
class Favorite extends BaseController
{
    /**
     * 添加收藏
     * @route('add', 'post')
     */
    public function add(): Json
    {
        // 检查用户是否登录
        if (!$this->isLogin()) {
            return json(['code' => 401, 'msg' => Lang::get('blog.need_login')]);
        }

        $postId = input('post_id', 0, 'intval');
        $userId = $this->userId;
        $userType = $this->userType;

        // 验证文章ID
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 检查文章是否存在
        $post = Db::name('blog_post')
            ->where('id', $postId)
            ->where('status', 1) // 只能收藏已发布的文章
            ->find();

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 检查是否已收藏
        $favorite = Db::name('blog_favorite')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->find();

        if ($favorite) {
            return json(['code' => 400, 'msg' => Lang::get('blog.already_favorited')]);
        }

        Db::startTrans();
        try {
            // 添加收藏记录
            $data = [
                'post_id' => $postId,
                'user_id' => $userId,
                'user_type' => $userType,
                'create_time' => time(),
                'update_time' => time()
            ];

            $favoriteId = Db::name('blog_favorite')->insertGetId($data);

            // 更新文章收藏数
            Db::name('blog_post')
                ->where('id', $postId)
                ->inc('favorites')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('blog.favorite_success'), 'data' => $favoriteId]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 取消收藏
     * @route('cancel', 'post')
     */
    public function cancel(): Json
    {
        // 检查用户是否登录
        if (!$this->isLogin()) {
            return json(['code' => 401, 'msg' => Lang::get('blog.need_login')]);
        }

        $postId = input('post_id', 0, 'intval');
        $userId = $this->userId;
        $userType = $this->userType;

        // 验证文章ID
        if (!$postId) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 检查文章是否存在
        $post = Db::name('blog_post')
            ->where('id', $postId)
            ->where('status', 1) // 只能操作已发布的文章
            ->find();

        if (!$post) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        // 检查是否已收藏
        $favorite = Db::name('blog_favorite')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->find();

        if (!$favorite) {
            return json(['code' => 400, 'msg' => Lang::get('blog.not_favorited')]);
        }

        Db::startTrans();
        try {
            // 删除收藏记录
            Db::name('blog_favorite')
                ->where('post_id', $postId)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->delete();

            // 更新文章收藏数
            Db::name('blog_post')
                ->where('id', $postId)
                ->where('favorites', '>', 0) // 防止收藏数变为负数
                ->dec('favorites')
                ->update();

            Db::commit();

            return json(['code' => 200, 'msg' => Lang::get('blog.unfavorite_success')]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取收藏状态
     * @route('status', 'post')
     */
    public function status(): Json
    {
        // 检查用户是否登录
        if (!$this->isLogin()) {
            return json(['code' => 401, 'msg' => Lang::get('blog.need_login')]);
        }

        $postIds = input('post_ids', '');
        $userId = $this->userId;
        $userType = $this->userType;

        if (!$postIds) {
            return json(['code' => 400, 'msg' => Lang::get('blog.post_not_exist')]);
        }

        $postIdArray = explode(',', $postIds);

        // 查询用户收藏状态
        $favorites = Db::name('blog_favorite')
            ->where('post_id', 'in', $postIdArray)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->column('post_id');

        $result = [];
        foreach ($postIdArray as $postId) {
            $result[$postId] = in_array($postId, $favorites);
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $result]);
    }

    /**
     * 获取用户收藏列表
     * @route('list', 'get')
     */
    public function getUserFavorites(): Json
    {
        // 检查用户是否登录
        if (!$this->isLogin()) {
            return json(['code' => 401, 'msg' => Lang::get('blog.need_login')]);
        }

        $page = input('page', 1, 'intval');
        $limit = input('limit', 10, 'intval');
        $userId = $this->userId;
        $userType = $this->userType;

        // 查询用户收藏的文章
        $query = Db::name('blog_favorite')->alias('f')
            ->join('blog_post p', 'f.post_id = p.id')
            ->where('f.user_id', $userId)
            ->where('f.user_type', $userType)
            ->where('p.status', 1) // 只显示已发布的文章
            ->field('f.*, p.title, p.description, p.thumbnail, p.author, p.views, p.likes, p.comments, p.favorites, p.create_time as post_time, p.update_time as post_update_time');

        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, [], ['f.create_time' => 'desc']);

        return json(['code' => 200, 'msg' => Lang::get('blog.get_favorite_success'), 'data' => $result]);
    }
}
