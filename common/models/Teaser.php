<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;

class Teaser extends \yii\db\ActiveRecord
{
    public $file = null;
    private $_assetsPath = '@next/public/images/teasers';

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 9;

    private static $_statuses = [
        self::STATUS_ACTIVE => 'Активно',
        self::STATUS_DELETED => 'Удалено',
    ];

    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%teaser}}';
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
            [['file'], 'file', 'extensions' => 'png, svg'],
            [['status_id'], 'integer'],
            [['text', 'image'], 'string'],
        ];
    }

    public function upload()
    {
        if (!$this->file) return false;

        $dirName = Yii::getAlias($this->_assetsPath);
        $fileName = uniqid() . '.' . $this->file->getExtension();

        if (!is_dir($dirName)) mkdir($dirName, 0775, true);
        $this->file->saveAs($dirName . '/' . $fileName);

        $this->image = $fileName;
        $this->file = false;
        return $this->image;
    }

    public function getPicture()
    {
        if (!empty($this->image)) {
            $src = Yii::$app->params['frontUrl'] . '/images/teasers/' . $this->image;
            return '<div style="width: 80px; height: 80px; background-color: #cecece">' . Html::img($src, ['style' => 'max-width:100%; max-height:100%;']) . '</div>';
        }
        return Yii::t('Round', 'Not found');
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
}