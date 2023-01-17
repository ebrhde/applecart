<?php
use yii\helpers\Html;
use common\models\Param;
use yii\bootstrap\ActiveForm;

$title = $model->product->title;
$this->title = 'Изменить параметр товара: ' . $model->product->title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['/product/view', 'id' => $model->product_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="program-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <div class="program-form">
        <?php $form = ActiveForm::begin(['action' => ['update', 'id' => $model->id]]); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'form' => $form,
                ]) ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>