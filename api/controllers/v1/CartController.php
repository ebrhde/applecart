<?php

namespace api\controllers\v1;

use common\models\Cart;
use common\models\CartProduct;
use common\models\Category;
use api\models\ContactForm;
use common\models\Product;
use common\models\ProductMedia;
use common\models\SpecialOffer;
use common\models\Teaser;
use yii\helpers\ArrayHelper;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;

class CartController extends \yii\rest\ActiveController
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

    public function actionAdd() {
        $response = [
            "status" => "error",
            "data" => "Can't add item to cart"
        ];

        $user = Yii::$app->user->identity;

        if(!$user) return $response;


        $cart = Cart::find()->andWhere(['user_id' => $user->id, 'status_id' => Cart::STATUS_ACTIVE])->one();
        $item = Product::find()->andWhere(['id' => Yii::$app->request->post()['product'], 'status_id' => Product::STATUS_ACTIVE])->one();

        if(!$cart) {
            $cart = new Cart();
            $cart->load(['Cart' => [
              'status_id' => Cart::STATUS_ACTIVE,
              'user_id' => $user->id,
              'total_quantity' => 1,
              'total_amount' => $item->price
            ]]);

            if(!$cart->save()) return $response;

            $cartItem = new CartProduct();
            $cartItem->load(['CartProduct' => [
                'cart_id' => $cart->id,
                'product_id' => $item->id,
                'quantity' => 1
            ]]);

            if(!$cartItem->save()) return $response;
        } else {
            $cart->total_quantity += 1;
            $cart->total_amount += $item->price;

            if(!$cart->save()) return $response;

            if(!$cartItem = $cart->getProducts()->andWhere(['product_id' => $item->id])->one()) {
                $cartItem = new CartProduct();
                $cartItem->load(['CartProduct' => [
                    'cart_id' => $cart->id,
                    'product_id' => $item->id,
                    'quantity' => 1
                ]]);
            } else {
                $cartItem->quantity += 1;
            }

            if(!$cartItem->save()) return $response;
        }

        $response['status'] = 'ok';
        unset($response['data']);

        return $response;
    }

    public function actionRemove() {
        $response = [
            "status" => "error",
            "data" => "Can't remove item from cart"
        ];

        $user = Yii::$app->user->identity;
        $productId = Yii::$app->request->get('id');

        if(!$user || !$productId) return $response;

        $cart = Cart::find()->andWhere(['user_id' => $user->id, 'status_id' => Cart::STATUS_ACTIVE])->one();
        if(!$cart) return $response;

        $product = Product::find()->andWhere(['id' => $productId, 'status_id' => Product::STATUS_ACTIVE])->one();
        $cartItem = $cart->getProducts()->andWhere(['product_id' => $productId])->one();

        $cart->total_quantity -= $cartItem->quantity;
        $cart->total_amount -= $product->price * $cartItem->quantity;
        if($cart->total_quantity === 0) $cart->status_id = Cart::STATUS_DELETED;
        if(!$cart->save()) return $response;

        $cartItem->delete();

        $response['status'] = 'ok';
        unset($response['data']);

        return $response;
    }
}