<?php

namespace denchotsanov\user\migrations;


class m190616_202012_create_account_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%social_account}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->null(),
            'provider'   => $this->string()->notNull(),
            'client_id'  => $this->string()->notNull(),
            'data'       => $this->text()->null(),
            'code'       => $this->string(32)->null(),
            'created_at' => $this->integer()->null(),
            'email'      => $this->string()->null(),
            'username'   => $this->string()->null(),

        ], $this->tableOptions);
        $this->createIndex('{{%account_unique}}', '{{%account}}', ['provider', 'client_id'], true);
        $this->addForeignKey('{{%fk_user_account}}', '{{%account}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
        $this->createIndex('{{%account_unique_code}}', '{{%social_account}}', 'code', true);
    }
    public function down()
    {
        $this->dropTable('{{%account}}');
    }

}