<?php

declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use think\facade\Request;
use app\common\Token;

/**
 * 友情链接管理控制器
 * @route('AdminLink')
 *
 */
class AdminLink extends BaseController
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
     * 获取链接类型
     */
    public function getLinkType(){
        $linkType =[
            1 => Lang::get('link.link_firend'),
            2 => Lang::get('link.link_tech'),
            3 => Lang::get('link.link_partner'),
        ];
        return $linkType;
    }

    /**
     * 获取链接类型列表
     */
    public function getLinkTypeList(){
        $linkType = $this->getLinkType();
        $linkTypeList=[];
        if($linkType){
            foreach($linkType as $k=>$v){
                $linkTypeList[] = [
                    'id' => $k,
                    'name' => $v
                ];
            }
        }
        return json(['code' => 200, 'msg' => Lang::get('link.link_type_list'), 'data' => $linkTypeList]);
    }

    /**
     * 获取友情链接列表
     */
    public function list()
    {

        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $title = input('title', '');
        $status = input('status', '');
        $page = intval(input('page', 1));
        $limit = intval(input('limit', 10));
        $type= intval(input('type', default: ''));


        $where = [];
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }

        if ($type) {
            $where[] = ['type', '=', intval($type)];
        }

        if ($status !== '') {
            $where[] = ['status', '=', intval($status)];
        }

        $query = Db::name('friendship_links');

        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, $where, ['sort asc', 'id asc']);
        // 获取链接类型
        $linkType = $this->getLinkType();

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['type_name'] = $linkType[$v['type']];
        }

        return json(['code' => 200, 'msg' => Lang::get('link.list_Success'), 'data' => $result]);
    }


    /**
     * 添加友情链接
     */
    public function create()
    {

        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        $data = $this->request->post();

        try {
            // Validate required fields
            $this->validate($data, [
                'title' => 'require',
                'logo' => 'require',
                'url' => 'require|url',
                'sort' => 'require|number',
                'email' => 'email',
                'type' => 'require|number'
            ],[
                'title.require' => Lang::get('link.link_title_required'),
                'logo.require' => Lang::get('link.link_logo_required'),
                'url.require' => Lang::get('link.link_url_required'),
                'sort.require' => Lang::get('link.link_sort_required'),
                'sort.number' => Lang::get('link.link_sort_error'),
                'email.email' => Lang::get('link.link_email_error'),
                'type.number' => Lang::get('link.link_type_error'),
            ]);

            // Add creator info
            $data['creator'] = DB::name('admin')->where('id', $this->userId)->value('username');
            $data['user_id'] = $this->userId;
            $data['user_type'] = $this->userType;
            $data['created_at'] = date('Y-m-d H:i:s');
            // $data['updated_at'] = date('Y-m-d H:i:s');
            // halt($data);
            // 检查标题是否重复(排除自己)
            $exists = Db::name('friendship_links')
                ->where('title', $data['title'])
                ->find();

            if ($exists) {
                return json(['code' => 400, 'msg' => Lang::get('link.link_already_exists')]);
            } else {

                $result = Db::name('friendship_links')->insert($data);

                if ($result) {
                    // 记录操作日志
                    $this->recordActivity($this->userId, 'admin', 'link_info_update', Lang::get('link.create_link_log') . ': ' . $data['title'], $data);

                    return json(['code' => 200, 'msg' => Lang::get('link.createSuccess')]);
                } else {
                    return json(['code' => 500, 'msg' => Lang::get('link.createError')]);
                }
            }
        } catch (\Exception $e) {
            return json([
                'code' => 400,
                'msg' => Lang::get('link.addError')
            ]);
        }
    }

    /**
     *修改友情链接
     */
    public function update()
    {

        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = input('id', 0);
        $title = input('title', '');
        $status = input('status', '');
        $sort = input('sort', 0);
        $url = input('url', '');
        $logo = input('logo', '');
        $type = input('type', 1);

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('link.link_id_required')]);
        }


        $link = Db::name('friendship_links')->find($id);
        if (!$link) {
            return json(['code' => 400, 'msg' => Lang::get('link.link_not_exist')]);
        }


        // 检查标题是否重复(排除自己)
        $exists = Db::name('friendship_links')
            ->where('title', $title)
            ->where('id', '<>', $id)
            ->find();

        if ($exists) {
            return json(['code' => 400999, 'msg' => Lang::get('link.link_already_exists')]);
        }

        $data = [
            'title' => $title,
            'url' => $url,
            'logo' => $logo,
            'sort' => (int)$sort,
            'status' => (int)$status,
            'type' => $type,
            'updated_at' => date('Y-m-d H:i:s', time()),
        ];

        $result = Db::name('friendship_links')->where('id', $id)->update($data);
        //halt($result);
        if ($result) {
            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'link_info_create', Lang::get('link.update_link_log') . ': ' . $title, $data);

            return json(['code' => 200, 'msg' => Lang::get('link.updateSuccess')]);
        } else {
            return json(['code' => 500, 'msg' => Lang::get('link.updateError')]);
        }
    }

    /**
     * Delete friendship link
     */
    public function delete()
    {

        if (!$this->isLogin()) {
            return $this->needLogin();
        }
        $id = $this->request->post('id');

        try {
            if (!$id) {
                return json(['code' => 400, 'msg' => Lang::get('link.link_id_required')]);
            }

            Db::name('friendship_links')
                ->where('id', $id)
                ->delete();

            // 记录操作日志
            $this->recordActivity($this->userId, 'admin', 'link_info_delete', Lang::get('link.delete_link_log') . ': ' . $id, []);

            return json([
                'code' => 200,
                'msg' => Lang::get('link.deleteSuccess')
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 400,
                'msg' => Lang::get('link.deleteError')
            ]);
        }
    }


    /**
     * Update sort order
     */
    public function updateSort()
    {
        $data = $this->request->post();

        try {
            if (!isset($data['items']) || !is_array($data['items'])) {
                throw new \Exception(Lang::get('link.invalidSortData'));
            }

            Db::startTrans();
            try {
                foreach ($data['items'] as $item) {
                    Db::name('friendship_links')
                        ->where('id', $item['id'])
                        ->update(['sort_order' => $item['sort_order']]);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }

            return json([
                'code' => 200,
                'msg' => Lang::get('link.sortUpdateSuccess')
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 400,
                'msg' => Lang::get('link.sortUpdateError')
            ]);
        }
    }


}
