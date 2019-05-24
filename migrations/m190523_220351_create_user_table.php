<?php


use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m190523_220351_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (Yii::$app->db->schema->getTableSchema('user') !== null) {
            $this->renameTable('{{%user}}', '{{%user_old}}');
        }
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->unique()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique()->notNull(),
            'auth_key' => $this->string()->notNull()->unique(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'last_login' => $this->integer(),
            'deleted_at' => $this->integer()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (Yii::$app->db->schema->getTableSchema('user_old') !== null) {
            $this->renameTable('{{%user}}', '{{%user_old}}');
        }
        $this->dropTable('{{%user}}');
    }
}
