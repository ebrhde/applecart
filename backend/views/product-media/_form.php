<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\ProductMedia;

if (!$model->status_id)
    $model->status_id = ProductMedia::STATUS_ACTIVE;

if (!$model->type_id)
    $model->type_id = ProductMedia::TYPE_IMAGE;

$js = <<<JS
    function set_type_form(form)
    {
        if (form == undefined || form == null)
            form = $('[name="ProductMedia[type_id]"]:checked').val();

        $('.types_form>div').hide().removeClass('hide');
        $('.types_form>.type-' + form).show();
    }

    $(function() {
        set_type_form('{$model['type_id']}');
        $('[name="ProductMedia[type_id]"]').on('change', function () { set_type_form(null); });
    });
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>

<div class="program-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'product_id')->hiddenInput()->label(false); ?>
        <?= $form->field($model, 'status_id')
            ->inline()
            ->radioList(ProductMedia::getStatuses())
            ->label(); ?>

        <?= $form->field($model, 'is_primary')->inline()->checkbox(); ?>

        <?= $form->field($model, 'sort')->inline()->textInput(); ?>

        <?= $form->field($model, 'type_id')
            ->inline()
            ->radioList(ProductMedia::getTypes())
            ->label(); ?>

        <div class="types_form">
            <div class="type-1 hide">
                <div class="card border-warning mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'file')->fileInput()->label(false); ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'removeImage')->checkbox()
                                    ->label('Удалить изображение'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="type-5 hide">
                <?= $form->field($model, 'url')->inline()->textInput(); ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>