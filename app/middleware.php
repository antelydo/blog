<?php
// 全局中间件定义文件
return [
    // 跨域请求支持 (必须放在最前面)
    \app\middleware\AllowCrossDomain::class,
    // 全局请求缓存
     //\think\middleware\CheckRequestCache::class,
    // 多语言加载
    \think\middleware\LoadLangPack::class,
    // Session初始化
     \think\middleware\SessionInit::class,
];
