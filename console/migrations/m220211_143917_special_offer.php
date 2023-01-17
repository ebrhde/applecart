<?php

use yii\db\Migration;

/**
 * Class m220211_143917_special_offer
 */
class m220211_143917_special_offer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%special_offer}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'expires_at' => $this->dateTime(),
            'status_id' => $this->tinyInteger(1),
            'title' => $this->string(255),
            'subtitle' => $this->string(255),
            'image' => $this->string(255),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%special_offer}}');
    }
}
