<?php
use yii\helpers\Html;
?>

<?= $form->field($model, 'product_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'param_id')->hiddenInput()->label(false); ?>

<?= $form->field($model, 'sort')->inline()->textInput(); ?>
<?= $form->field($model, 'value')->textInput(); ?>

<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
</div>