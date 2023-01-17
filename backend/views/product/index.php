<?php

use common\models\Category;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Товары';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить товар', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
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
            'old_price',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
