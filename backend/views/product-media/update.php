<?php
use yii\helpers\Html;

$title = $model->product->title;
$this->title = "Изменить медиа товара: " . $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['/product/view', 'id' => $model->product_id]];
$this->params['breadcrumbs'][] = "Изменить";
?>
<div class="program-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
