<?php

use yii\db\Migration;

/**
 * Class m221122_094714_order
 */
class m221122_094714_order extends Migration
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

        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
            'status_id' => $this->tinyInteger(1),
            'user_id' => $this->integer(11),
            'total_quantity' => $this->integer(11),
            'total_amount' => $this->integer(11),
            'customer_name' => $this->string(255),
            'customer_phone' => $this->string(255),
            'customer_address' => $this->string(255),
            'note' => $this->string(1020)
        ], $tableOptions);

        $this->createTable('{{%order_product}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'order_id' => $this->integer(11),
            'product_id' => $this->integer(11),
            'quantity' => $this->integer(11)
        ], $tableOptions);

        $this->addForeignKey(
            'FK_order_user',
            '{{%order}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_op_order',
            '{{%order_product}}',
            'order_id',
            '{{%order}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'FK_op_product',
            '{{%order_product}}',
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
        $this->dropForeignKey('FK_op_product', '{{%order_product}}');
        $this->dropForeignKey('FK_op_order', '{{%order_product}}');
        $this->dropForeignKey('FK_order_user', '{{%order}}');
        $this->dropTable('{{%order_product}}');
        $this->dropTable('{{%order}}');
    }
}
