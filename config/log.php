<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道
    'default'      => 'file',
    // 日志记录级别
    'level'        => ['error', 'alert','debug','notice','info','sql','warning','emergency'],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => ['error', 'alert','debug','notice','info','sql','warning','emergency'],
    // 关闭全局日志写入
    'close'        => false,
    // 全局日志处理 支持闭包
    'processor'    => null,
    
    'apart_level'=>[
        'error', 'alert','debug','notice','info','sql','warning','emergency'
    ],

    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => '\\app\\common\\CustomFormatter',//\\app\\common\\CustomFormatter
            // 日志保存目录
            'path'           => '',
            // 单文件日志写入
            'single'         => false,
            // 独立日志级别
            'apart_level'    => [],
            // 最大日志文件数量
            'max_files'      => 50,
            // 日志文件后缀
            'file_suffix'    => '',
            // 日志文件大小限制
            'max_filesize'   => 1024*1024*100,
            // 日志文件大小限制
            'file_size'   	=> 	1024*1024*10,
            // 使用JSON格式记录
            'json'           => false,
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出日期格式
            'time_format'   =>'Y-m-d H:i:s',
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => true,
        ]
    ],

];
