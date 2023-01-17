<?php

namespace api\controllers\v1;

use common\models\Category;
use api\models\ContactForm;
use common\models\Product;
use common\models\ProductMedia;
use common\models\SpecialOffer;
use common\models\Teaser;
use yii\helpers\ArrayHelper;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;

class SiteController extends \yii\rest\ActiveController
{
    public $modelClass = '';

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => [
                'index',
                'feedback',
                'error'
            ],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $response = [
            "status" => "error",
            "data" => 'Ошибка при получении данных'
        ];

        $teasers = Teaser::find()->andWhere(['status_id' => Teaser::STATUS_ACTIVE])->all();
        $teasersData = ArrayHelper::toArray($teasers, ['common\models\Teaser' => ['id', 'text', 'image']]);
        $specialOffers = SpecialOffer::find()->andWhere(['status_id' => SpecialOffer::STATUS_ACTIVE])->all();
        $specialOffersData = ArrayHelper::toArray($specialOffers, ['common\models\SpecialOffer' => ['id', 'title', 'subtitle', 'image']]);
        $categories = Category::find()->andWhere(['status_id' => Category::STATUS_ACTIVE])->orderBy('sort')->all();
        $categoriesData = ArrayHelper::toArray($categories, ['common\models\Category' => [
            'id',
            'alias',
            'title',
            'description',
            'image' => function($model) {
            return $model->getPathPicture();
            }
            ]]);
        $products = Product::find()->andWhere(['status_id' => Product::STATUS_ACTIVE, 'is_hot' => 1])->limit(4)->all();

        $productsData = ArrayHelper::toArray(
            $products,
            ['common\models\Product' =>
                [
                    'id',
                    'alias',
                    'categoryAlias' => function($model) {
                        return $model->category->alias;
                    },
                    'title',
                    'productMedia' => function($model) {
                        $mediaArr = [];
                        $media = $model->productMedia;
                        foreach ($media as $m) {
                            $mediaArr[] = $m->type_id == ProductMedia::TYPE_IMAGE ? $m->getPhoto(160, 220, 'resize') : $m->url;
                        };
                        return $mediaArr;
                    },
                    'price',
                    'old_price',
                ]]);

        if(count($teasersData) > 0 && count($specialOffersData) && count($categoriesData))
            $response = [
                "status" => "ok",
                "data" => [
                    "teasers" => $teasersData,
                    "specialOffers" => $specialOffersData,
                    "categories" => $categoriesData,
                    "products" => $productsData
                ]
            ];

        return $response;
    }

    public function actionFeedback() {
        $response = [
            "status" => "error",
            "data" => 'Не удалось отправить сообщение'
        ];

        $model = new ContactForm();

        if ($model->load(Yii::$app->request->post(), '') && $model->contact(Yii::$app->params['adminEmail'])) {
            $response = [
                'status' => 'ok',
                'data' => 'Сообщение было успешно отправлено. Менеджер ответит вам в ближайшее время'
            ];
        }

        return $response;
    }
}