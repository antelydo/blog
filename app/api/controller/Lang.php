<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang as LangManager;
use think\facade\Cookie;
use think\response\Json;

/**
 * 语言控制器
 * @route('lang')
 */
class Lang extends BaseController
{
    /**
     * 设置语言
     * @route('set', 'post')
     */
    public function set(): Json
    {
        $lang = input('lang', 'zh-cn');
        $allowLang = config('lang.allow_lang_list', ['zh-cn', 'en-us']);

        if (!in_array($lang, $allowLang)) {
            return json(['code' => 400, 'msg' => LangManager::get('lang.unsupported_language'), 'data' => null]);
        }

        Cookie::set('think_lang', $lang);

        return json(['code' => 200, 'msg' => LangManager::get('lang.switch_success'), 'data' => ['lang' => $lang]]);
    }

    /**
     * 获取当前语言
     * @route('get', 'get')
     */
    public function get(): Json
    {
        $currentLang = LangManager::getLangSet();

        return json(['code' => 200, 'msg' => LangManager::get('lang.get_successful'), 'data' => ['lang' => $currentLang]]);
    }
}