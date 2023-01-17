<?php

use common\models\SpecialOffer;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Teaser */
/* @var $form yii\widgets\ActiveForm */

if (!$model->status_id) $model->status_id = SpecialOffer::STATUS_ACTIVE;

?>

<div class="feedback-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status_id')->radioList(SpecialOffer::getStatuses()) ?>

    <?= $form->field($model, 'created_at')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'expires_at')->widget(DatePicker::class,[
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'options' => [
            'placeholder' => 'Введите дату окончания',
            'autocomplete' => 'off',
        ],
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'todayBtn' => true,
            'autoclose' => true,
        ]
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subtitle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file')->fileInput(); ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
