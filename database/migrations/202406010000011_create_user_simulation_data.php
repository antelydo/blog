<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserSimulationData extends Migrator
{
    public function change()
    {
        // 获取表前缀
        $prefix = $this->getAdapter()->getOption('table_prefix');
        
        // 生成日期范围
        $startDate = strtotime('2025-03-08');
        $endDate = strtotime('2025-04-07');
        $currentDate = $startDate;
        
        // 用户数据数组
        $users = [];
        $userId = 1000; // 起始用户ID
        
        // 遍历每一天
        while ($currentDate <= $endDate) {
            // 每天创建5个用户
            for ($i = 1; $i <= 5; $i++) {
                $userId++;
                $username = 'user_' . date('Ymd', $currentDate) . '_' . $i;
                $email = 'user_' . date('Ymd', $currentDate) . '_' . $i . '@example.com';
                $mobile = '138' . str_pad($userId, 8, '0', STR_PAD_LEFT);
                
                // 随机生成性别
                $gender = rand(0, 2);
                
                // 随机生成VIP等级
                $vipLevel = rand(0, 3);
                
                // 随机生成余额和积分
                $balance = round(rand(0, 1000) / 10, 2);
                $points = rand(0, 1000);
                
                // 随机生成生日（18-60岁之间）
                $birthYear = date('Y') - rand(18, 60);
                $birthMonth = rand(1, 12);
                $birthDay = rand(1, 28); // 使用28天避免月份问题
                $birthday = sprintf('%d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
                
                // 随机生成真实姓名
                $surnames = ['张', '王', '李', '赵', '刘', '陈', '杨', '黄', '周', '吴'];
                $names = ['伟', '芳', '娜', '秀英', '敏', '静', '丽', '强', '磊', '军', '洋', '勇', '艳', '杰', '娟', '涛', '明', '超', '秀兰', '霞'];
                $realName = $surnames[array_rand($surnames)] . $names[array_rand($names)];
                
                // 创建用户数据
                $users[] = [
                    'username' => $username,
                    'password' => password_hash('123456', PASSWORD_DEFAULT),
                    'nickname' => '用户' . $userId,
                    'avatar' => '',
                    'email' => $email,
                    'mobile' => $mobile,
                    'real_name' => $realName,
                    'gender' => $gender,
                    'birthday' => $birthday,
                    'status' => 1,
                    'lang' => 'zh-cn',
                    'balance' => $balance,
                    'points' => $points,
                    'vip_level' => $vipLevel,
                    'register_ip' => '127.0.0.1',
                    'register_time' => $currentDate,
                    'create_time' => $currentDate,
                    'update_time' => $currentDate
                ];
            }
            
            // 移动到下一天
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        // 插入数据
        $this->table('user')->insert($users)->save();
    }
} 