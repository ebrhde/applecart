<?php

namespace common\models;

use yii\db\Expression;
use yii\helpers\Html;
use Yii;

class Cart extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 7;
    const STATUS_DELETED = 9;

    private static $_statuses = [
        self::STATUS_ACTIVE => 'Активно',
        self::STATUS_INACTIVE => 'Неактивно',
        self::STATUS_DELETED => 'Удалено',
    ];

    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%cart}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['status_id', 'user_id', 'total_quantity', 'total_amount'], 'integer'],
        ];
    }

    public static function getStatuses()
    {
        return self::$_statuses;
    }

    public function getStatus($id = 0)
    {
        if (!$id) $id = $this->status_id;
        return ((!empty(self::$_statuses[$id])) ? self::$_statuses[$id] : 'Не указано');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProducts()
    {
        return $this->hasMany(CartProduct::class, ['cart_id' => 'id']);
    }

//    public static function apiArray() {
//        return [
//          'common\models\Cart'
//        ];
//    }
}