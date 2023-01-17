<?php

namespace backend\controllers;

use common\models\Param;
use Yii;
use common\models\Product;
use common\models\ProductParam;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProductParamController extends \yii\web\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'add', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionAdd($productId = null)
    {
        if (!$productId)
            throw new NotFoundHttpException(Yii::t('error', 'Product ID not found'));

        if (!$product = Product::find()->andWhere(['id' => $productId, 'status_id' => Product::STATUS_ACTIVE])->one())
            throw new NotFoundHttpException(Yii::t('error', 'Product not found'));

        if (!$paramId = Yii::$app->request->post('paramId', null))
            throw new NotFoundHttpException(Yii::t('error', 'Param ID not found'));

        if (!$param = Param::find()->andWhere(['id' => $paramId, 'status_id' => Param::STATUS_ACTIVE])->one())
            throw new NotFoundHttpException(Yii::t('error', 'Param not found'));

        $model = new ProductParam();
        $model->param_id = $paramId;
        $model->product_id = $productId;

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new ProductParam();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/product/view', 'id' => $model->product_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/product/view', 'id' => $model->product_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $id = $model->product_id;
        $model->delete();

        return $this->redirect(['/product/view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = ProductParam::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('error', 'The requested page does not exist.'));
    }
}