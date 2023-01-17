<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Teaser*/

$this->title = 'Добавить специальное предложение';
$this->params['breadcrumbs'][] = ['label' => 'Специальные предложения', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
