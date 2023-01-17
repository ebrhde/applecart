<?php

use yii\db\Migration;

/**
 * Class m220227_101409_product
 */
class m220227_101409_product extends Migration
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

        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'status_id' => $this->tinyInteger(1),
            'category_id' => $this->integer(11),
            'sort' => $this->integer(11),
            'is_hot' => $this->tinyInteger(1),
            'alias' => $this->string(255),
            'title' => $this->string(255),
            'description' => $this->text(),
            'price' => $this->integer(11),
            'old_price' => $this->integer(11)
        ], $tableOptions);

        $this->createTable('{{%param}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'status_id' => $this->tinyInteger(1),
            'title' => $this->string(255),
            'unit' => $this->string(255)
        ], $tableOptions);

        $this->createTable('{{%product_param}}', [
            'id' => $this->primaryKey(),
            'sort' => $this->integer(11),
            'product_id' => $this->integer(11),
            'param_id' => $this->integer(11),
            'value' => $this->string(255)
        ], $tableOptions);

        $this->createTable('{{%product_media}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'status_id' => $this->tinyInteger(1),
            'type_id' => $this->tinyInteger(1),
            'sort' => $this->integer(11),
            'product_id' => $this->integer(11),
            'is_primary' => $this->tinyInteger(1),
            'url' => $this->string(255),
        ], $tableOptions);

        $this->addForeignKey(
            'FK_product_category',
            '{{%product}}',
            'category_id',
            '{{%category}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_pp_product',
            '{{%product_param}}',
            'product_id',
            '{{%product}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_pp_param',
            '{{%product_param}}',
            'param_id',
            '{{%param}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_pm_product',
            '{{%product_media}}',
            'product_id',
            '{{%product}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_pm_product', '{{%product_media}}');
        $this->dropForeignKey('FK_pp_param', '{{%product_param}}');
        $this->dropForeignKey('FK_pp_product', '{{%product_param}}');
        $this->dropForeignKey('FK_product_category', '{{%product}}');

        $this->dropTable('{{%product}}');
        $this->dropTable('{{%param}}');
        $this->dropTable('{{%product_param}}');
        $this->dropTable('{{%product_media}}');
    }
}
