<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Параметры';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="param-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить параметр', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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
                'headerOptions' => ['style' => 'width:10%'],
            ],
            'created_at',
            'status',
            'title',
            'unit',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
