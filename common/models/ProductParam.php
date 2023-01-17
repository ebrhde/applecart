<?php
namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "{{%product_param}}".
 *
 * @property int $id
 * @property string|null $created_at
 * @property int|null $sort
 * @property int|null $product_id
 * @property int|null $param_id
 * @property string|null $value
 *
 * @property Param $param
 * @property Product $product
 */
class ProductParam extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_param}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['sort', 'product_id', 'param_id'], 'integer'],
            [['product_id', 'param_id'], 'required'],
            [['value'], 'string'],
            [['param_id'], 'exist', 'skipOnError' => true, 'targetClass' => Param::class, 'targetAttribute' => ['param_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'sort' => 'Sort',
            'product_id' => 'Product ID',
            'param_id' => 'Param ID',
            'value' => 'Value',
            'unit' => 'Unit',
        ];
    }

    /**
     * Gets query for [[Param]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParam()
    {
        return $this->hasOne(Param::class, ['id' => 'param_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}