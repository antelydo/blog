<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddAboutUsConfigs extends Migrator
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
        $time = time();
        $aboutUsConfigs = [
            [
                'name' => 'about_us_introduction',
                'value' => '我们是一家致力于AI技术应用的科技公司，专注于为用户提供高效、智能的对话体验。',
                'group' => 'about',
                'title' => '网站介绍',
                'type' => 'textarea',
                'options' => '',
                'sort' => 1,
                'remark' => '关于我们页面的网站介绍内容',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'about_us_history',
                'value' => '2019年：公司成立\n2020年：发布第一版AI对话平台\n2021年：用户数突破一百万\n2022年：完成B轮融资\n2023年：推出新一代AI对话引擎',
                'group' => 'about',
                'title' => '发展历程',
                'type' => 'textarea',
                'options' => '',
                'sort' => 2,
                'remark' => '关于我们页面的公司发展历程',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'about_us_mission',
                'value' => '通过AI技术让人机交流更自然、更高效，为用户创造价值。',
                'group' => 'about',
                'title' => '企业使命',
                'type' => 'textarea',
                'options' => '',
                'sort' => 3,
                'remark' => '关于我们页面的企业使命',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'about_us_vision',
                'value' => '成为全球领先的AI对话技术提供商，引领人工智能交互的未来。',
                'group' => 'about',
                'title' => '企业愿景',
                'type' => 'textarea',
                'options' => '',
                'sort' => 4,
                'remark' => '关于我们页面的企业愿景',
                'create_time' => $time,
                'update_time' => $time
            ]
        ];
        
        $this->table('website_config')->insert($aboutUsConfigs)->save();
    }

    public function down()
    {
        $this->execute('DELETE FROM website_config WHERE `name` IN ("about_us_introduction", "about_us_history", "about_us_mission", "about_us_vision")');
    }
} 