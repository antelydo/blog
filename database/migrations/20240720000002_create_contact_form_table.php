<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateContactFormTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // 创建联系表单数据表
        $table = $this->table('contact_message', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci', 'comment' => '网站联系表单消息']);
        $table->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '联系人姓名'])
              ->addColumn('email', 'string', ['limit' => 255, 'null' => false, 'comment' => '联系人邮箱'])
              ->addColumn('subject', 'string', ['limit' => 255, 'null' => false, 'comment' => '消息主题'])
              ->addColumn('message', 'text', ['null' => false, 'comment' => '消息内容'])
              ->addColumn('ip_address', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP地址'])
              ->addColumn('user_agent', 'string', ['limit' => 255, 'default' => '', 'comment' => '浏览器信息'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'signed' => false, 'comment' => '处理状态: 0=未处理, 1=已处理, 2=已回复'])
              ->addColumn('reply', 'text', ['null' => true, 'comment' => '回复内容'])
              ->addColumn('replied_by', 'integer', ['signed' => true, 'null' => true, 'comment' => '回复者ID'])
              ->addColumn('replied_time', 'integer', ['signed' => true, 'null' => true, 'comment' => '回复时间'])
              ->addColumn('create_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '创建时间'])
              ->addColumn('update_time', 'integer', ['signed' => true, 'default' => 0, 'comment' => '更新时间'])
              ->addIndex(['email'], ['name' => 'idx_email'])
              ->addIndex(['status'], ['name' => 'idx_status'])
              ->addIndex(['create_time'], ['name' => 'idx_create_time'])
              ->create();

        // 生成示例数据
        $this->generateSampleData();

        // 添加联系表单相关配置到网站配置表
        $time = time();
        $contactConfigs = [
            [
                'name' => 'contact_form_enabled',
                'value' => '1',
                'group' => 'contact',
                'title' => '启用联系表单',
                'type' => 'switch',
                'options' => '0=禁用,1=启用',
                'sort' => 1,
                'remark' => '是否启用网站联系表单功能',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'contact_email_notification',
                'value' => '1',
                'group' => 'contact',
                'title' => '邮件通知',
                'type' => 'switch',
                'options' => '0=禁用,1=启用',
                'sort' => 2,
                'remark' => '是否在收到新联系表单时发送邮件通知',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'contact_notification_email',
                'value' => '',
                'group' => 'contact',
                'title' => '通知邮箱',
                'type' => 'text',
                'options' => '',
                'sort' => 3,
                'remark' => '接收联系表单通知的邮箱地址，多个邮箱用逗号分隔',
                'create_time' => $time,
                'update_time' => $time
            ]
        ];
        
        $this->table('website_config')->insert($contactConfigs)->save();
    }

    public function down()
    {
        // 删除联系表单表
        $this->dropTable('contact_message');
        
        // 删除联系表单相关配置
        $this->execute('DELETE FROM website_config WHERE `name` IN ("contact_form_enabled", "contact_email_notification", "contact_notification_email")');
    }

    /**
     * 生成联系表单的示例数据
     */
    private function generateSampleData()
    {
        $sampleMessages = [
            [
                'name' => '张三',
                'email' => 'zhangsan@example.com',
                'subject' => '产品咨询',
                'message' => '您好，我想了解一下贵公司的AI对话平台的具体功能和价格，谢谢！',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'status' => 1,
                'reply' => '您好，感谢您对我们产品的兴趣！我们的AI对话平台包括智能客服、多轮对话和知识库问答等功能，价格根据具体需求定制。请问您更关注哪方面的应用场景？',
                'replied_by' => 1,
                'replied_time' => time() - 86400,
                'create_time' => time() - 172800,
                'update_time' => time() - 86400
            ],
            [
                'name' => '李四',
                'email' => 'lisi@example.com',
                'subject' => '技术支持',
                'message' => '在使用过程中遇到了一些问题，AI回答偶尔会出现不连贯的情况，请问如何解决？',
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15',
                'status' => 2,
                'reply' => '您好，这个问题可能与模型参数设置有关，建议您调整temperature值或增加上下文长度，如果问题仍然存在，可以提供具体的对话案例，我们会进一步协助排查。',
                'replied_by' => 2,
                'replied_time' => time() - 43200,
                'create_time' => time() - 129600,
                'update_time' => time() - 43200
            ],
            [
                'name' => '王五',
                'email' => 'wangwu@example.com',
                'subject' => '合作提案',
                'message' => '我们是一家教育科技公司，希望与贵公司探讨AI对话技术在在线教育领域的应用合作可能，期待您的回复。',
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
                'status' => 0,
                'create_time' => time() - 3600,
                'update_time' => time() - 3600
            ]
        ];

        $this->table('contact_message')->insert($sampleMessages)->saveData();
    }
} 