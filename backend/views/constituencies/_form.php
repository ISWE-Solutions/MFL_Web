<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Constituency */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="constituency-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'population')->textInput() ?>

    <?= $form->field($model, 'pop_density')->textInput() ?>

    <?= $form->field($model, 'area_sq_km')->textInput() ?>

    <?= $form->field($model, 'geom')->textInput() ?>

    <?= $form->field($model, 'district_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
