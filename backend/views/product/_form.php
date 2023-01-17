<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Product;
use common\models\Category;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */

if (!$model->status_id)
    $model->status_id = Product::STATUS_ACTIVE;

$categoryList = [];
$categories = Category::find()
    ->alias('c')
    ->andWhere(['c.status_id' => Category::STATUS_ACTIVE])
    ->orderBy(['c.title' => SORT_ASC])
    ->all();
if ($categories) {
    $categoryList = ArrayHelper::map($categories, 'id', function ($model) {
        return '[' . $model->id . '] ' . $model->title;
    });
}

?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status_id')
        ->inline()
        ->radioList(Product::getStatuses())
        ->label(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'category_id')
                ->inline()
                ->dropdownList([null => 'Не выбрано'] + $categoryList); ?>

            <?= $form->field($model, 'sort')->textInput() ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'is_hot')->inline()->radioList([0 =>'Нет', 1 => 'Да']); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'alias')->textInput(['maxlength' => true]); ?>
        </div>
    </div>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'old_price')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
