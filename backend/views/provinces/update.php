<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Provinces */

$this->title = 'Update Province: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Provinces', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="card card-primary card-outline">
    <div class="card-body">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
</div>