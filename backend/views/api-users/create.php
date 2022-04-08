<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ApiUsers */

$this->title = 'Create Api Users';
$this->params['breadcrumbs'][] = ['label' => 'Api Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-users-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
