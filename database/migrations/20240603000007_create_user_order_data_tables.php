<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserOrderDataTables extends Migrator
{
    public function up()
    {
        $data = [
            [
                'user_id' => 1,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 99.00,
                'status' => 1,
                'pay_time' => time(),
                'pay_method' => 'wechat',
                'pay_amount' => 99.00,
                'product_id' => 1,
                'product_name' => '基础套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 2,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 199.00,
                'status' => 0,
                'pay_time' => null,
                'pay_method' => null,
                'pay_amount' => null,
                'product_id' => 2,
                'product_name' => '高级套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 3,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 299.00,
                'status' => 2,
                'pay_time' => null,
                'pay_method' => null,
                'pay_amount' => null,
                'product_id' => 3,
                'product_name' => '企业套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 1,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 49.00,
                'status' => 1,
                'pay_time' => time(),
                'pay_method' => 'alipay',
                'pay_amount' => 49.00,
                'product_id' => 4,
                'product_name' => '试用套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 2,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 399.00,
                'status' => 1,
                'pay_time' => time(),
                'pay_method' => 'wechat',
                'pay_amount' => 399.00,
                'product_id' => 5,
                'product_name' => '专业套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 3,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 199.00,
                'status' => 0,
                'pay_time' => null,
                'pay_method' => null,
                'pay_amount' => null,
                'product_id' => 2,
                'product_name' => '高级套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 1,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 99.00,
                'status' => 1,
                'pay_time' => time(),
                'pay_method' => 'alipay',
                'pay_amount' => 99.00,
                'product_id' => 1,
                'product_name' => '基础套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 2,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 299.00,
                'status' => 2,
                'pay_time' => null,
                'pay_method' => null,
                'pay_amount' => null,
                'product_id' => 3,
                'product_name' => '企业套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 3,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 399.00,
                'status' => 1,
                'pay_time' => time(),
                'pay_method' => 'wechat',
                'pay_amount' => 399.00,
                'product_id' => 5,
                'product_name' => '专业套餐',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'user_id' => 1,
                'order_no' => 'ORD' . date('YmdHis') . rand(1000, 9999),
                'amount' => 49.00,
                'status' => 0,
                'pay_time' => null,
                'pay_method' => null,
                'pay_amount' => null,
                'product_id' => 4,
                'product_name' => '试用套餐',
                'create_time' => time(),
                'update_time' => time()
            ]
        ];

        $this->table('user_order')->insert($data)->save();
    }
}