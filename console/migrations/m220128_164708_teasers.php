<?php

use yii\db\Migration;

/**
 * Class m220128_164708_teasers
 */
class m220128_164708_teasers extends Migration
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

        $this->createTable('{{%teaser}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'status_id' => $this->tinyInteger(1),
            'text' => $this->string(255),
            'image' => $this->string(255)
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%teaser}}');
    }
}
