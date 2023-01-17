<?php

use yii\db\Migration;

/**
 * Class m220227_095614_category
 */
class m220227_095614_category extends Migration
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

        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'status_id' => $this->tinyInteger(1),
            'sort' => $this->integer(11),
            'alias' => $this->string(255),
            'title' => $this->string(255),
            'description' => $this->text(),
            'image' => $this->string(255),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }
}
