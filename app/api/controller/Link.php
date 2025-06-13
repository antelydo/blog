<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Lang;

class Link extends BaseController
{

     /**
     * 初始化方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        $this->userId =$this->getUserId();
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
        $linkTypeList=[];
        $linkType = $this->getLinkType();
        if($linkType){
            foreach($linkType as $k=>$v){
                $linkTypeList[] = [
                    'id' => $k,
                    'name' => $v
                ];
        return json(['code' => 200, 'msg' => Lang::get('link.link_type_list'), 'data' => $linkType]);
            }
        }
    }
     /**
     * 添加友情链接
     */
    public function create()
    {
        $data = $this->request->post(); 
        $sort = Db::name('friendship_links')->max('sort');
        try {
            // Validate required fields
            $this->validate($data, [
                'title' => 'require|min:2|max:50',
                'logo' => 'url',
                'url' => 'require|url',
                'description' => 'require|min:5|max:100',
                'email' => 'require|email|min:5|max:50',
                'type' => 'require|number'
            ],[
                'title.require' => Lang::get('link.link_title_required'),
                'title.min' => Lang::get('link.link_title_min'),
                'title.max' => Lang::get('link.link_title_max'),
                'logo.url' => Lang::get('link.link_logo_url'),
                'url.require' => Lang::get('link.link_url_required'),
                'url.url' => Lang::get('link.link_url_url'),
                'description.require' => Lang::get('link.link_description_required'),
                'description.min' => Lang::get('link.link_description_min'),
                'description.max' => Lang::get('link.link_description_max'),
                'email.require' => Lang::get('link.link_email_required'),
                'email.email' => Lang::get('link.link_email_email'),
                'email.min' => Lang::get('link.link_email_min'),
                'email.max' => Lang::get('link.link_email_max'),
                'type.require' => Lang::get('link.link_type_required'),
                'type.number' => Lang::get('link.link_type_number')
            ]);

            // Add creator info
            $data['creator'] = DB::name('admin')->where('id', $this->userId)->value('username');
            $data['user_id'] = $this->userId;
            $data['user_type'] = $this->userType;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['sort'] = $sort + 1;
            $data['status'] = 2;
            // $data['updated_at'] = date('Y-m-d H:i:s'); 
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
                    $this->recordActivity($this->userId, 'user', 'link_info_update', Lang::get('link.create_link_log') . ': ' . $data['title'], $data);
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
     * 获取友情链接列表
     */
    public function list()
    {
        $title = input('title', '');
        $status = input('status', '');
        $page = intval(input('page', 1));
        $limit = intval(input('limit', 10));
        $type= input('type', default: '');
    
        $query = Db::name('friendship_links');
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

        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page, $limit, $where, ['sort asc', 'id asc']);
        // 获取链接类型
        $linkType = $this->getLinkType();
   
        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['type_name'] = $linkType[$v['type']];
        }

        return json(['code' => 200, 'msg' => Lang::get('link.list_Success'), 'data' => $result]);
    }

} 