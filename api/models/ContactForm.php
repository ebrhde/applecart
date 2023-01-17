<?php

namespace api\models;

use Yii;

class ContactForm extends \yii\base\Model
{
    public $name;
    public $email;
    public $body;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'email', 'body'], 'required'],
            [['name', 'email', 'body'], 'string'],
            ['email', 'email']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'email' => 'Email',
            'body' => 'Сообщение'
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    public function contact($email)
    {
        if ($this->validate()) {
            $send = Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setReplyTo([$this->email => $this->name])
                ->setSubject('Contact form message')
                ->setTextBody(
                    'Author: ' . $this->name . PHP_EOL
                        . 'Email: ' . $this->email . PHP_EOL
                        . 'Message: ' . $this->body. PHP_EOL
                )
                ->send();

            if(!$send)
                return false;

            return true;
        }
        return false;
    }
}
