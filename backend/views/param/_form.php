<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Param;

/* @var $this yii\web\View */
/* @var $model common\models\Param */
/* @var $form yii\widgets\ActiveForm */

if (!$model->status_id)
    $model->status_id = Param::STATUS_ACTIVE;

?>

<div class="param-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status_id')
        ->inline()
        ->radioList(Param::getStatuses())
        ->label(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unit')->textInput(['maxlength' => true]) ?>

    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранитьx', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
