<?php

namespace common\models;

use yii\db\Expression;
use yii\helpers\Html;
use Yii;

class Order extends \yii\db\ActiveRecord
{
    const STATUS_ACCEPTED = 1;
    const STATUS_CANCELLED = 7;
    const STATUS_DELETED = 9;

    private static $_statuses = [
        self::STATUS_ACCEPTED => 'Accepted',
        self::STATUS_CANCELLED => 'Canceled',
        self::STATUS_DELETED => 'Deleted',
    ];

    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%order}}';
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
            [['customer_name', 'customer_phone', 'customer_address', 'note'], 'string']
        ];
    }

    public static function getStatuses()
    {
        return self::$_statuses;
    }

    public function getStatus($id = 0)
    {
        if (!$id) $id = $this->status_id;
        return ((!empty(self::$_statuses[$id])) ? self::$_statuses[$id] : 'Not specified');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProducts()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id']);
    }

    public function sendEmail()
    {
        $message = Yii::$app->mailer->compose();

        $body = '<p style="margin-bottom: 20px;">Hello, ' . $this->user->real_name . '! Your order №' . $this->id .
        ' accepted in the AppleCart shop! You can check your orders <a href="https://applecart.dev/orders">here</a> .
        If you need you can also cancel your new order from this page.</p>
        <table style="font-size: 14px; border-spacing: 0; text-align: center;">
        <tr style="background: #2a9f2a; color: #fff;">
        <th style="padding: 10px 20px; border-style: solid;
        border-width: 0 1px 1px 0; border-color: #000;">#</th>
        <th style="padding: 10px 20px; border-style: solid;
        border-width: 0 1px 1px 0; border-color: #000;">Title</th>
        <th style="padding: 10px 20px; border-style: solid;
        border-width: 0 1px 1px 0; border-color: #000;">Quantity</th>
        <th style="padding: 10px 20px; border-style: solid;
        border-width: 0 1px 1px 0; border-color: #000;">Sum</th>
        </tr>';

        foreach ($this->products as $key => $product) {
            $body .= '<tr style="color: #000;">
            <td style="padding: 10px 20px; border-style: solid;
            border-width: 0 1px 1px 0; border-color: #000;">' . ($key + 1) . '</td>
            <td style="padding: 10px 20px; border-style: solid;
            border-width: 0 1px 1px 0; border-color: #000;">' . $product->product->title . '</td>
            <td style="padding: 10px 20px; border-style: solid;
            border-width: 0 1px 1px 0; border-color: #000;">' . $product->quantity . '</td>
            <td style="padding: 10px 20px; border-style: solid;
            border-width: 0 1px 1px 0; border-color: #000;">' . $product->product->price . '$</td>
            </tr>';
        }

        $body .= '</table><h5>Total: ' . $this->total_amount . '$</h5>';

        return $message
            ->setTo([$this->user->email, Yii::$app->params['adminEmail']])
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setSubject('Created a new order №' . $this->id)
            ->setHtmlBody($body)
            ->send();
    }
}