<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddVersionLicenseToAiToolTable extends Migrator
{
    /**
     * 添加版本和许可证字段到AI工具表
     */
    public function up()
    {
        $table = $this->table('ai_tool');
        $table
            ->addColumn('version', 'string', ['limit' => 50, 'null' => true, 'comment' => '版本号'])
            ->addColumn('license', 'string', ['limit' => 100, 'null' => true, 'comment' => '许可证类型'])
            ->update();
    }

    /**
     * 删除AI工具表中的版本和许可证字段
     */
    public function down()
    {
        $table = $this->table('ai_tool');
        $table
            ->removeColumn('version')
            ->removeColumn('license')
            ->update();
    }
}
