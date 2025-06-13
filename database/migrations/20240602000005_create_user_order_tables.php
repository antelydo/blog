<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserOrderTables extends Migrator
{
    public function change()
    {
   
        $table = $this->table('user_order', ['engine' => 'InnoDB', 'comment' => '用户订单表']);
        $table->addColumn('user_id', 'integer', ['limit' => 11, 'comment' => '用户ID'])
              ->addColumn('order_no', 'string', ['limit' => 50, 'comment' => '订单编号'])
              ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'comment' => '订单金额'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '订单状态 0-未支付 1-已支付 2-已取消'])
              ->addColumn('pay_time', 'integer', ['null' => true, 'comment' => '支付时间'])
              ->addColumn('pay_method', 'string', ['limit' => 20, 'null' => true, 'comment' => '支付方式'])
              ->addColumn('pay_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'comment' => '实际支付金额'])
              ->addColumn('product_id', 'integer', ['limit' => 11, 'comment' => '产品ID'])
              ->addColumn('product_name', 'string', ['limit' => 100, 'comment' => '产品名称'])
              ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
              ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
              ->addIndex(['user_id'])
              ->addIndex(['order_no'], ['unique' => true])
              ->create();
    }
}