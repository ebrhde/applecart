<?php

namespace common\models;

use yii\db\Expression;

class UserRefreshToken extends \yii\db\ActiveRecord
{
    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%user_refresh_tokens}}';
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
            [['user_id'], 'integer'],
            [['token', 'ip', 'user_agent'], 'string'],
        ];
    }
}