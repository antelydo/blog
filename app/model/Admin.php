<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 管理员模型
 */
class Admin extends Model
{
    // 设置表名
    protected $name = 'admin';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 创建时间字段
    protected $createTime = 'create_time';
    
    // 更新时间字段
    protected $updateTime = 'update_time';
    
    // 最后登录时间
    protected $lastLoginTime = 'last_login_time';
    
    // 隐藏字段
    protected $hidden = ['password', 'role'];
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'status' => 'integer',
        'last_login_time' => 'timestamp',
        'update_time' => 'timestamp',
        'create_time' => 'timestamp',
    ];
    
    /**
     * 获取用户状态文本
     * @param int $value 状态值
     * @return string
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            0 => '禁用',
            1 => '正常'
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    /**
     * 格式化注册时间
     * @param int $value 时间戳
     * @return string
     */
    public function getRegisterTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    
    /**
     * 格式化最后登录时间
     * @param int $value 时间戳
     * @return string
     */
    public function getLastLoginTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
} 