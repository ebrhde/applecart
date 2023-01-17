<?php
use yii\helpers\Html;
use common\models\Param;
use yii\bootstrap\ActiveForm;

$title = $model->product ? $model->product->title : $model->id;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['/product/view', 'id' => $model->product_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="program-category-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <div class="program-form">
        <?php $form = ActiveForm::begin(['action' => ['create']]); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'form' => $form,
                ]) ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
