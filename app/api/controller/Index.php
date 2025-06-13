<?php

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;

class Index extends BaseController
{
    public function index()
    {
        return '<style>*{padding:0;margin:0}div{padding:4rem;text-align:center}h1{font-size:2.5rem;color:#333}</style><div><h1>' . Lang::get('index.welcome') . '</h1></div>';
    }

    public function hello($name = 'ThinkPHP')
    {
        return sprintf(Lang::get('index.hello'), $name);
    }


}
