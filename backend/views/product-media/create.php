<?php
use yii\helpers\Html;

$title = $model->product->title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['/product/view', 'id' => $model->product_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="program-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
