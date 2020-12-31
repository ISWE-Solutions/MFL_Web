<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Districts */

$this->title = 'Add District';
$this->params['breadcrumbs'][] = ['label' => 'Districts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
</div>
