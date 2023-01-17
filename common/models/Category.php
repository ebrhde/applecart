<?php

namespace common\models;

use Imagine\Image\Box;
use yii\db\Expression;
use yii\helpers\Html;
use Yii;
use yii\imagine\Image;

class Category extends \yii\db\ActiveRecord
{
    public $file = null;
    private $_assetsPath = '@next/public/images/categories';

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 9;

    private static $_statuses = [
        self::STATUS_ACTIVE => 'Активно',
        self::STATUS_DELETED => 'Удалено',
    ];

    public $enableCsrfValidation = false;

    public static function tableName()
    {
        return '{{%category}}';
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
            [['file'], 'file', 'extensions' => 'jpg, png, svg'],
            [['status_id', 'sort'], 'integer'],
            [['alias', 'title', 'description', 'image'], 'string'],
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
            $src = Yii::$app->params['frontUrl'] . '/images/categories/' . $this->image;
            return '<div style="width: 150px; height: 150px; background-color: #cecece">' . Html::img($src, ['style' => 'max-width:100%; max-height:100%;']) . '</div>';
        }
        return 'Not found';
    }

    public function getPathPicture($path = null)
    {
        $image = $this->image;
        if ($path)
            $image = $path;

        if ($image)
            return Yii::$app->params['frontUrl'] . '/images/categories/' . $image;

        return null;
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

    public function getProducts() {
        return $this->hasMany(Product::class, ['category_id' => 'id']);
    }
}