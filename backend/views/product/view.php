<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use yii\grid\GridView;
use common\models\ProductMedia;

/* @var $this yii\web\View */
/* @var $model common\models\Product */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="feedback-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить эту запись?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_at',
            'status',
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return $model->category->title;
                },
            ],
            'sort',
            'is_hot',
            'alias',
            'title',
            'description',
            'price',
            'old_price'
        ],
    ]) ?>

    <?php Pjax::begin(); ?>
    <h2>Медиа</h2>
    <p>
        <?= Html::a('Создать', ['product-media/create', 'productId' => $model->id], ['class' => 'btn btn-success', 'data-pjax' => 0]) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $mediaProvider,
        'pager' => [
            'disabledPageCssClass' => 'disabled',
            'pageCssClass' => 'page-link',
            'nextPageCssClass' => 'page-link',
            'prevPageCssClass' => 'page-link',
            'firstPageCssClass' => 'page-link',
            'lastPageCssClass' => 'page-link',
        ],
        'columns' => [
            'id',
            'status',
            'type',
            'sort',
            [
                'format' => 'raw',
                'label' => 'Содержание',
                'value' => function($model) {
                    if ($model->type_id == ProductMedia::TYPE_IMAGE)
                        return Html::a($model->getPicture()
                            , $model->getPathPicture()
                            , ['target' => '_blank', 'data-pjax' => 0]
                        );
                    elseif ($model->type_id == ProductMedia::TYPE_VIDEO)
                        return Html::a($model->url, $model->url, ['target' => '_blank']);
                }
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'controller' => 'product-media'
            ],
        ],
    ]); ?>
    <hr />
    <h2><?= 'Параметры'; ?></h2>

    <?= GridView::widget([
        'dataProvider' => $paramsProvider,
        'pager' => [
            'disabledPageCssClass' => 'disabled',
            'pageCssClass' => 'page-link',
            'nextPageCssClass' => 'page-link',
            'prevPageCssClass' => 'page-link',
            'firstPageCssClass' => 'page-link',
            'lastPageCssClass' => 'page-link',
        ],
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function($model) {
                    if ($model->param)
                        return $model->param->id;
                }
            ],
            [
                'label' => 'Название',
                'value' => function($model) {
                    if ($model->param)
                        return $model->param->title . ', ' . $model->param->unit;
                }
            ],
            'value',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'controller' => 'product-param'
            ],
        ],
    ]); ?>

    <?php $form = ActiveForm::begin(['action' => ['/product-param/add', 'productId' => $model->id]]); ?>

    <div class="row">
        <div class="col-md-8">
            <?= Html::dropDownList('paramId', null
                , [null => 'Не выбрано'] + $paramList
                , ['class' => 'form-control is-valid', 'required' => true])
            ; ?>
        </div>
        <div class="col-md-4">
            <?= Html::submitButton('Добавить', ['data-pjax' => 0,'class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <hr />
    <hr />
    <?php Pjax::end();?>

</div>
