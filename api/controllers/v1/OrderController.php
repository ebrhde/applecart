<?php

namespace api\controllers\v1;

use common\models\Cart;
use common\models\CartProduct;
use common\models\Category;
use api\models\ContactForm;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductMedia;
use common\models\SpecialOffer;
use common\models\Teaser;
use yii\helpers\ArrayHelper;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;

class OrderController extends \yii\rest\ActiveController
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
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $response = [
            "status" => "error",
            "data" => null
        ];

        file_put_contents('1', 111);

        $user = Yii::$app->user->identity;

        if(!$user) return $response;

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
        if($orders = Order::find()->andWhere(['user_id' => $user->id])->orderBy(['id' => SORT_DESC])->all()) {
            $ordersCount = count($orders);

            $ordersData = ArrayHelper::toArray($orders, ['common\models\Order' => [
                'id',
                'status' => function($model) {
                    return $model->status;
                },
                'created' => function($model) {
                    return date('d.m.Y H:i', strtotime($model->created_at));
                },
                'quantity' => 'total_quantity',
                'sum' => 'totalAmount',
                'items' => function($model) {
                    $productsData = [];
                    $products = $model->products;

                    foreach ($products as $product) {
                        $productEntity = $product->product;

                        $productsData[] = [
                            'id' => $productEntity->id,
                            'title' => $productEntity->title,
                            'quantity' => $product->quantity,
                            'price' => $productEntity->price,
                            'productMedia' => $productEntity->productMedia[0]->getPhoto(400, 600, 'resize')
                        ];
                    }

                    return $productsData;
                }
            ]]);

            $response['status'] = 'ok';

            $response['data'] = [
                'categories' => $categoriesData,
                'ordersCount' => $ordersCount,
                'orders' => $ordersData
            ];
        }

        return $response;
    }

    public function actionCreate() {
        $response = [
            "status" => "error",
            "data" => "Can't create order"
        ];

        $user = Yii::$app->user->identity;

        if(!$user) return $response;

        $cart = Cart::find()->andWhere(['user_id' => $user->id, 'status_id' => Cart::STATUS_ACTIVE])->one();
        $items = $cart->products;

        if(!$cart || !$items) {
            $response['data'] = 'Your cart has no items';
            return $response;
        }

        $order = new Order();
        $order->load(['Order' => [
            'status_id' => Cart::STATUS_ACTIVE,
            'user_id' => $user->id,
            'total_quantity' => $cart->total_quantity,
            'total_amount' => $cart->total_amount,
            'customer_name' => Yii::$app->request->post()['name'],
            'customer_phone' => Yii::$app->request->post()['phone'],
            'customer_address' => Yii::$app->request->post()['address'],
            'note' => Yii::$app->request->post()['note'],
        ]]);

        if($order->save()) {
            foreach ($items as $item) {
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $item->product_id;
                $orderProduct->quantity = $item->quantity;
                $orderProduct->save();
            }

            $cart->status_id = Cart::STATUS_INACTIVE;
            $cart->save();

            $orders = Order::find()->andWhere(['user_id' => $user->id])->orderBy(['id' => SORT_DESC])->all();
            $ordersData = [];
            $ordersData['quantity'] = count($orders);

            $ordersData['data'] = ArrayHelper::toArray($orders, ['common\models\Order' => [
                'id',
                'status' => function ($model) {
                    return $model->status;
                },
                'created' => function ($model) {
                    return date('d M Y H:i', strtotime($model->created_at));
                },
                'quantity' => 'total_quantity',
                'sum' => 'total_amount',
                'items' => function ($model) {
                    $productsData = [];
                    $products = $model->products;

                    foreach ($products as $product) {
                        $productEntity = $product->product;

                        $productsData[] = [
                            'id' => $productEntity->id,
                            'title' => $productEntity->title,
                            'alias' => $productEntity->alias,
                            'categoryAlias' => $productEntity->category->alias,
                            'quantity' => $product->quantity,
                            'price' => $productEntity->price,
                            'productThumb' => $productEntity->productMedia[0]->getPhoto(400, 600, 'resize')
                        ];
                    }

                    return $productsData;
                }
            ]]);

            $response = [
                'status' => 'ok',
                'message' => 'Order № '. $order->id . ' was created successfully! Check your email for details',
                'data' => $ordersData
            ];

            $order->sendEmail();
        }

        return $response;
    }

    public function actionCancel() {
        $response = [
            "status" => "error",
            "data" => "Не получилось отменить заказ"
        ];

        $user = Yii::$app->user->identity;
        $orderId = Yii::$app->request->get('id');

        if(!$user || !$orderId) return $response;

        $order = Order::find()->andWhere(['id' => $orderId, 'user_id' => $user->getId()])->one();

        if(!$order) return $response;

        $order->status_id = Order::STATUS_CANCELLED;
        $order->save();
        $response['status'] = 'ok';
        unset($response['data']);

        return $response;
    }
}