<?php
/**
 * Created by PhpStorm.
 * User: Dencho Tsanov
 */

namespace denchotsanov\user\migrations;


use yii\db\Migration;

class m190616_202012_add_username_column_to_user_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{user}}','username',$this->string()->after('id'));
    }
    public function safeDown()
    {
        $this->dropColumn('{{user}}','username');
    }
}