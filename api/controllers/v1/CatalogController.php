<?php

namespace api\controllers\v1;

use common\models\Param;
use common\models\Product;
use common\models\ProductMedia;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use common\models\Category;
use yii\web\NotFoundHttpException;

class CatalogController extends ActiveController {
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

        $behaviors['access'] = [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                [
                    'actions' => ['index', 'view'],
                    'allow' => true,
                    'verbs' => ['GET', 'HEAD']
                ],
                [
                    'allow' => true,
                    'verbs' => ['OPTIONS']
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex($alias = null) {
        if (!$alias || !$category = Category::find()->andWhere(['status_id' => Category::STATUS_ACTIVE, 'alias' => $alias])->one())
            throw new NotFoundHttpException('Category not found');

        $getParams = Yii::$app->request->queryParams;

        $products = Product::find()
            ->distinct(true)
            ->alias('pr')
            ->joinWith('productParams as pp')
            ->joinWith('params as p')
            ->andWhere(['pr.status_id' => Category::STATUS_ACTIVE, 'pr.category_id' => $category->id]);

        if(count($getParams) > 2) {
            $titleFilters = [];
            $valueFilters = [];

            foreach ($getParams as $key => $getParam) {
                if($key !== 'alias' && $key !== 'page') {
                    $filterValues = explode("'", $getParam);
                    $valueFilters[] = ['in', 'pp.value', $filterValues];
                    $titleFilters[] = ucfirst($key);
                }
            }

            if(!empty($valueFilters) && !empty($titleFilters)) {
                foreach ($valueFilters as $key => $valueFilter)
                    if($key === 0) {
                        $products->andFilterWhere($valueFilter);
                    } else {
                        $products->orFilterWhere($valueFilter);
                    }

                $products->andFilterWhere(['in', 'p.title', $titleFilters]);
            }
        }

        $productsProvider = new ActiveDataProvider([
            'query' => $products,
            'sort' => [
                'attributes' => ['sort'],
            ],
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);

        $productsData = ArrayHelper::toArray($productsProvider->getModels(),
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
                ]]
        );

        $params = Param::find()->andWhere(['status_id' => Param::STATUS_ACTIVE])->all();
        $currentCategoryParams = [];

        foreach ($params as $param) {
            foreach ($param->productParams as $productParam) {
                if($productParam->product)
                    if($productParam->product->category)
                        if($productParam->product->category->id === $category->id) {
                            $i = 0;
                            foreach($currentCategoryParams as $currentCategoryParam) {
                                if ($currentCategoryParam->title === $param->title) {
                                    $i = 1;
                                    break;
                                }
                            }
                            if(!$i)
                                $currentCategoryParams[] = $param;
                        }

            }
        }

        $paramsData = ArrayHelper::toArray($currentCategoryParams, ['common\models\Param' => [
            'title',
            'values' => function ($model) {
                $values = [];

                foreach ($model->productParams as $productParam)
                    if(!array_search($productParam->value, $values))
                        $values[] = $productParam->value;

                return $values;
            },
            'unit'
        ]]);

        $categories = Category::find()->andWhere(['status_id' => Category::STATUS_ACTIVE])->orderBy('sort')->all();
        $categoriesData = ArrayHelper::toArray($categories, ['common\models\Category' => [
            'id',
            'image' => function($model) {
                return $model->getPathPicture();
            },
            'alias',
            'title',
            'description'
        ]]);

        return [
            "status" => "ok",
            "data" => [
                "products" => $productsData,
                "productsCount" => $productsProvider->totalCount,
                "params" => $paramsData,
                "categories" => $categoriesData
            ]
        ];
    }

    public function actionView($alias = null, $palias = null) {
        if (!$alias || !$palias || !$product = Product::find()
                ->alias('p')
                ->joinWith('category c')
                ->andWhere([
                    'p.status_id' => Product::STATUS_ACTIVE,
                    'p.alias' => $palias,
                    'c.alias' => $alias
                ])
                ->one()) {
            throw new NotFoundHttpException('Product not found');
        }

        $productData = ArrayHelper::toArray($product, ['common\models\Product' =>
            [
                'id',
                'alias',
                'categoryAlias' => function($model) {
                    return $model->category->alias;
                },
                'title',
                'description',
                'productMedia' => function($model) {
                    $mediaArr = [];
                    $media = $model->productMedia;
                    foreach ($media as $m) {
                        $mediaArr[] = $m->type_id == ProductMedia::TYPE_IMAGE ? $m->getPhoto(400, 600, 'resize') : $m->url;
                    };
                    return $mediaArr;
                },
                'productParams' => function($model) {
                    $paramsArr = [];
                    $params = $model->productParams;

                    if($params) {
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
            ]]
        );

        $categories = Category::find()->andWhere(['status_id' => Category::STATUS_ACTIVE])->orderBy('sort')->all();
        $categoriesData = ArrayHelper::toArray($categories, ['common\models\Category' => [
            'id',
            'image' => function($model) {
                return $model->getPathPicture();
            },
            'alias',
            'title',
            'description'
        ]]);

        return [
            "status" => "ok",
            "data" => [
                "product" => $productData,
                "categories" => $categoriesData
            ]
        ];
    }
}
