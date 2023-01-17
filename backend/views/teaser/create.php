<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Teaser*/

$this->title = 'Добавить тизер';
$this->params['breadcrumbs'][] = ['label' => 'Тизеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
