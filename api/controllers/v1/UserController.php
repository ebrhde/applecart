<?php

namespace api\controllers\v1;

use api\models\SignupForm;
use common\models\Cart;
use common\models\Order;
use common\models\ProductMedia;
use Yii;
use yii\rest\ActiveController;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\UserRefreshToken;
use common\models\LoginForm;
use yii\web\Cookie;

class UserController extends ActiveController {
    public $modelClass = 'user';

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
                'login',
                'logout',
                'signup',
                'recover',
                'refresh-token',
                'options',
            ],
        ];

        return $behaviors;
    }

    private function generateJwt(User $user) {
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();

        $jwtParams = Yii::$app->params['jwt'];

        return $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])
            ->permittedFor($jwtParams['audience'])
            ->identifiedBy($jwtParams['id'], true)
            ->issuedAt($time)
            ->expiresAt($time + $jwtParams['expire'])
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);
    }

    private function generateRefreshToken(User $user, User $impersonator = null): UserRefreshToken {
        $refreshToken = Yii::$app->security->generateRandomString(200);

        // TODO: Don't always regenerate - you could reuse existing one if user already has one with same IP and user agent
        $userRefreshToken = new UserRefreshToken([
            'user_id' => $user->id,
            'token' => $refreshToken,
            'ip' => Yii::$app->request->userIP,
            'user_agent' => Yii::$app->request->userAgent,
        ]);
        if (!$userRefreshToken->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to save the refresh token: '. $userRefreshToken->getErrorSummary(true));
        }

        // Send the refresh-token to the user in a HttpOnly cookie that Javascript can never read and that's limited by path
        Yii::$app->response->cookies->add(new Cookie([
            'name' => 'refresh-token',
            'value' => $refreshToken,
            'httpOnly' => true,
            'sameSite' => 'none',
            'secure' => true,
            'path' => '/api/v1/user/refresh-token',  //endpoint URI for renewing the JWT token using this refresh-token, or deleting refresh-token
            'expire' => time() + 3155760000
        ]));

        return $userRefreshToken;
    }

    public function actionLogin() {
        $response = [
            "status" => "error",
            "data" => "There is no user with this login and password"
        ];

        $model = new LoginForm();

        $load = $model->load(Yii::$app->request->post(), '');

        if ($load && $model->login()) {

            $user = Yii::$app->user->identity;

            $userData = ArrayHelper::toArray($user, ['common\models\User' => ['id', 'username', 'real_name', 'email', 'phone_number']]);

            $cart = $user->cart;
            $cartData = null;

            if ($cart) {
                $cartData = [];

                $cartItems = $cart->products;

                $cartData['totalPrice'] = $cart->total_amount;
                $cartData['totalCount'] = $cart->total_quantity;

                foreach ($cartItems as $cartItem) {
                    $cartData['items'][] = array_merge(['quantity' => $cartItem->quantity], ArrayHelper::toArray($cartItem->product, ['common\models\Product' =>
                            [
                                'id',
                                'alias',
                                'categoryAlias' => function($model) {
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
                            ]]
                    ));
                }
            }

            $ordersData = null;
            $ordersCount = 0;

            if($orders = Order::find()->andWhere(['user_id' => $user->id])->orderBy(['id' => SORT_DESC])->all()) {
                $ordersData = [];
                $ordersData['quantity'] = count($orders);

                $ordersData['data'] = ArrayHelper::toArray($orders, ['common\models\Order' => [
                    'id',
                    'status' => function($model) {
                        return $model->status;
                    },
                    'created' => function($model) {
                        return date('d M Y H:i', strtotime($model->created_at));
                    },
                    'quantity' => 'total_quantity',
                    'sum' => 'total_amount',
                    'items' => function($model) {
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
            }

            $token = $this->generateJwt($user);

            $this->generateRefreshToken($user);

            $response = [
                'status' => 'ok',
                'userData' => $userData,
                'cartData' => $cartData,
                'ordersData' => $ordersData,
                'token' => (string) $token
            ];
        }

        $model->password = '';

        return $response;
    }

    public function  actionLogout() {
        $response = [
            "status" => "ok",
        ];

        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);

        if ($refreshToken) {
            $userRefreshToken = UserRefreshToken::findOne(['token' => $refreshToken]);
        }

        if(isset($userRefreshToken) && $userRefreshToken) $userRefreshToken->delete();


        Yii::$app->response->cookies->removeAll();

        return $response;
    }

    public function actionSignup() {
        $response = [
            "status" => "error",
            "data" => "В настоящее время регистрация не доступна. Пожалуйста, попробуйте ещё раз"
        ];


        if(Yii::$app->request->post()['password'] !== Yii::$app->request->post()['password_confirm']) {
            $response['data'] = "Passwords don't match";
            return $response;
        }

        $model = new SignupForm();

        $load = $model->load(Yii::$app->request->post(), '');

        if ($load && $model->signup()) {

            $user = User::findByUsername($model->username);

            if($user) {
                $userData = ArrayHelper::toArray($user, ['common\models\User' => ['id', 'username', 'real_name', 'email', 'phone_number']]);

                $token = $this->generateJwt($user);

                $this->generateRefreshToken($user);

                $response = [
                    'status' => 'ok',
                    'data' => $userData,
                    'token' => (string) $token
                ];
            }
        } else {
            if (isset($model->errors['username']) && current($model->errors['username']) === 'This username has already been taken.')
                $response['data'] = 'Username had already been taken. Please, choose another one';
            elseif (isset($model->errors['email']) && current($model->errors['email']) === 'This email address has already been taken.')
                $response['data'] = 'E-mail address had already bee taken. Please, choose another one';
        }

        return $response;
    }

    public function actionRefreshToken() {
        $response = [
            "status" => "error",
            "data" => "Can't refresh access token"
        ];

        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        if (!$refreshToken) {
            $response['data'] = 'Token not found';
            return $response;
        }

        $userRefreshToken = UserRefreshToken::findOne(['token' => $refreshToken]);

        if (Yii::$app->request->getMethod() == 'GET') {

            // Getting new JWT after it has expired
            if (!$userRefreshToken) {
                $response['data'] = "Тoken don't exist";
                return $response;
            }

            $user = User::find()
                ->where(['id' => $userRefreshToken->user_id])
                ->andWhere(['not', ['status_id' => User::STATUS_INACTIVE]])
                ->one();

            if (!$user) {
                $userRefreshToken->delete();
                $response['data'] = 'User is not active';
                return $response;
            }

            $token = $this->generateJwt($user);

            return [
                'status' => 'ok',
                'token' => (string) $token,
            ];

        } else {
            $response['data'] = 'Invalid request';
            return $response;
        }
    }

    public function actionMe() {
        $response = [
            "status" => "error",
            "data" => "Can't get user data"
        ];

        $user = Yii::$app->user->identity;

        if($user) {
            $userData = ArrayHelper::toArray($user, ['common\models\User' => ['id', 'username', 'real_name', 'email', 'phone_number']]);
            $response['status'] = 'ok';
            $response['data'] = $userData;
        }
        
        return $response;
    }

    public function actionRecover() {
        $response = [
            "status" => "error",
            "data" => "Recovering password is unavailable at this moment. Please, try again later"
        ];

        $request = Yii::$app->request->post();

        if($request && $request['email']) {
            $user = User::find()->andWhere(['email' => $request['email'], 'status_id' => User::STATUS_ACTIVE])->one();

            if(!$user) {
                $response['data'] = 'User with this email not found';
                return $response;
            }

            $newPassword = $user->generatePassword();

            $user->setPassword($newPassword);

            if($user->save()) {
                $textBody = 'Hello, ';
                $textBody .= $user->real_name ?? $user->username;
                $textBody .= '. Your new password: ' . $newPassword;

                Yii::$app->mailer->compose()
                    ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                    ->setTo($user->email)
                    ->setSubject('User password ' . $user->username . ' изменён')
                    ->setTextBody($textBody)
                    ->send();

                $response = [
                    "status" => "ok",
                    "data" => "Your account password was reset, and new one sent to your email. Please, check your email."
                ];
            }
        }
        return $response;
    }
}