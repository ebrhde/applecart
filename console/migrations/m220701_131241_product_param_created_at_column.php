<?php

use yii\db\Migration;

/**
 * Class m220701_131241_product_param_created_at_column
 */
class m220701_131241_product_param_created_at_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product_param}}', 'created_at', $this->timestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product_param}}', 'created_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220701_131241_product_param_created_at_column cannot be reverted.\n";

        return false;
    }
    */
}
