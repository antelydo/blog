<?php
declare (strict_types=1);

namespace app\api\controller;
use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\response\Json;
use app\common\Token;

/**
 * 管理员控制器
 * @route('admin')
 */
class Web  extends BaseController{

      /**
     * 获取网站配置
     * @route('config', 'get')
     */
    public function getConfig(): Json
    {

        $where = [];
        $configs = Db::name('website_config')
            ->where($where)
            ->order('group,sort')
            ->select()
            ->toArray();

        // 处理配置选项
        foreach ($configs as &$config) {
            if ($config['options']) {
                $config['options'] = json_decode($config['options'], true);
            }
        }

        return json(['code' => 200, 'msg' => Lang::get('web.get_config_successful'), 'data' => $configs]);
    }
}
