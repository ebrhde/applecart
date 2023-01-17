<?php

namespace common\models;

use yii\db\Expression;
use yii\helpers\Html;
use Yii;

class Param extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 9;

    private static $_statuses = [
        self::STATUS_ACTIVE => 'Активно',
        self::STATUS_DELETED => 'Удалено',
    ];

    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%param}}';
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

    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['status_id'], 'integer'],
            [['title', 'unit'], 'string'],
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

    public function getProductParams()
    {
        return $this->hasMany(ProductParam::class, ['param_id' => 'id']);
    }
}