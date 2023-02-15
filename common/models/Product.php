<?php

namespace common\models;

use yii\db\Expression;
use yii\helpers\Html;
use Yii;

class Product extends \yii\db\ActiveRecord
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
        return '{{%product}}';
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
            [['status_id', 'category_id', 'sort', 'is_hot', 'price', 'old_price'], 'integer'],
            [['alias', 'title', 'description'], 'string'],
            [['alias'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
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

    public function getProductMedia()
    {
        return $this->hasMany(ProductMedia::class, ['product_id' => 'id']);
    }

    public function getProductParams()
    {
        return $this->hasMany(ProductParam::class, ['product_id' => 'id']);
    }

    public function getParams()
    {
        return $this->hasMany(Param::class, ['id' => 'param_id'])
            ->via('productParams');
    }

    public function getCategory() {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public static function apiArray()
    {
        return ['common\models\Product' =>
            [
                'id',
                'alias',
                'categoryAlias' => function ($model) {
                    return $model->category->alias;
                },
                'title',
                'description',
                'productMedia' => function ($model) {
                    $mediaArr = [];
                    $media = $model->productMedia;
                    foreach ($media as $m) {
                        $mediaArr[] = $m->type_id == ProductMedia::TYPE_IMAGE ? $m->getPhoto(400, 600, 'resize') : $m->url;
                    };
                    return $mediaArr;
                },
                'productParams' => function ($model) {
                    $paramsArr = [];
                    $params = $model->productParams;

                    if ($params) {
                        foreach ($params as $p) {
                            $param = [
                                'title' => $p->param->title,
                                'value' => $p->value,
                                'unit' => $p->param->unit
                            ];

                            $paramsArr[] = $param;
                        }
                    }

                    return $paramsArr;
                },
                'price',
                'old_price',
            ]
        ];
    }
}