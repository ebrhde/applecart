<?php

namespace backend\controllers;

use Yii;
use common\models\Product;
use common\models\ProductMedia;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProductMediaController extends \yii\web\Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
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

    public function actionCreate($productId = null)
    {
        if (!$productId || !Product::find()->andWhere(['id' => $productId])->exists())
            throw new NotFoundHttpException(Yii::t('error', 'Product not found'));

        $model = new ProductMedia();
        $model->product_id = $productId;
        $load = $model->load(Yii::$app->request->post());
        $model->file = UploadedFile::getInstance($model, 'file');

        if ($load && $model->validate()) {
            if ($model->removeImage)
                $model->url = null;

            $model->upload();
            if ($model->save())
                return $this->redirect(['/product/view', 'id' => $model->product_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $load = $model->load(Yii::$app->request->post());
        $model->file = UploadedFile::getInstance($model, 'file');

        if ($load && $model->validate()) {
            if ($model->removeImage)
                $model->url = null;

            $model->upload();
            if ($model->save())
                return $this->redirect(['/product/view', 'id' => $model->product_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        if ($model = $this->findModel($id)) {
            $model->status_id = ProductMedia::STATUS_DELETED;
            $model->save();
        }

        return $this->redirect(['/product/view', 'id' => $model->product_id]);
    }

    protected function findModel($id)
    {
        if (($model = ProductMedia::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует');
    }
}