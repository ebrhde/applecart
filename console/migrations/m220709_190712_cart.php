<?php

use yii\db\Migration;

/**
 * Class m220709_190712_cart
 */
class m220709_190712_cart extends Migration
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

        $this->createTable('{{%cart}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
            'status_id' => $this->tinyInteger(1),
            'user_id' => $this->integer(11),
            'total_quantity' => $this->integer(11),
            'total_amount' => $this->integer(11)
        ], $tableOptions);

        $this->createTable('{{%cart_product}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'cart_id' => $this->integer(11),
            'product_id' => $this->integer(11),
            'quantity' => $this->integer(11)
        ], $tableOptions);

        $this->addForeignKey(
            'FK_cart_user',
            '{{%cart}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_cp_cart',
            '{{%cart_product}}',
            'cart_id',
            '{{%cart}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_cp_product',
            '{{%cart_product}}',
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
        $this->dropForeignKey('FK_cp_product', '{{%cart_product}}');
        $this->dropForeignKey('FK_cp_cart', '{{%cart_product}}');
        $this->dropForeignKey('FK_cart_user', '{{%cart}}');
        $this->dropTable('{{%cart_product}}');
        $this->dropTable('{{%cart}}');
    }
}
