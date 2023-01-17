<?php
namespace console\controllers;

use common\models\User;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->getAuthManager();
        $auth->removeAll();

        $admin = $auth->createRole(User::ROLE_ADMIN);

        $auth->add($admin);

        $adminUser = User::find()->andWhere(['email' => 'tharuthlessman@gmail.com'])->one();

        $auth->assign($admin, $adminUser->id);
    }

    public function actionTest()
    {

    }
}