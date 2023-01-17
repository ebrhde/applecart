<?php
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = Yii::$app->name;
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Административная панель</h1>
    </div>

    <div class="body-content">
        <div class="row">
            <div class="col-sm-4">
                <div class="panel panel-info">
                    <div class="panel-heading"><h3>Контент</h3></div>
                    <div class="panel-body">
                        <?php if (Yii::$app->user->can('admin')): ?>
                            <h4>Тизеры</h4>
                            <ul>
                                <li><?= Html::a('Тизеры', ['teaser/index']); ?></li>
                            </ul>
                            <h4>Специальные предложения</h4>
                            <ul>
                                <li><?= Html::a('Специальные предложения', ['special-offer/index']); ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="panel panel-info">
                    <div class="panel-heading"><h3>Каталог</h3></div>
                    <div class="panel-body">
                        <?php if (Yii::$app->user->can('admin')): ?>
                            <h4>Товары</h4>
                            <ul>
                                <li><?= Html::a('Товары', ['product/index']); ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">

                <div class="panel panel-success">
                    <div class="panel-heading"><h3>Сервисы</h3></div>
                    <div class="panel-body">
                        <?php if (Yii::$app->user->can('admin')): ?>
                            <h4>Обратная связь</h4>
                            <ul>
                                <li><?= Html::a('Форма обратной связи', ['feedback/index', 'type' => 'manager']); ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="panel panel-danger">
                    <div class="panel-heading"><h3>Система</h3></div>
                    <div class="panel-body">
                        <?php if (Yii::$app->user->can('admin')): ?>
                            <ul>
                                <li><?= Html::a('Конфигурация сайта', ['site/setup']); ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
