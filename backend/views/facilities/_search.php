<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\FacilitySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="facility-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'district_id') ?>

    <?= $form->field($model, 'constituency_id') ?>

    <?= $form->field($model, 'ward_id') ?>

    <?= $form->field($model, 'zone_id') ?>

    <?php // echo $form->field($model, 'hims_code') ?>

    <?php // echo $form->field($model, 'smartcare_code') ?>

    <?php // echo $form->field($model, 'elmis_code') ?>

    <?php // echo $form->field($model, 'hpcz_code') ?>

    <?php // echo $form->field($model, 'disa_code') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'catchment_population_head_count') ?>

    <?php // echo $form->field($model, 'catchment_population_cso') ?>

    <?php // echo $form->field($model, 'number_of_households') ?>

    <?php // echo $form->field($model, 'operational_status') ?>

    <?php // echo $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'mobility_status') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'ownership_type') ?>

    <?php // echo $form->field($model, 'ownership') ?>

    <?php // echo $form->field($model, 'accesibility') ?>

    <?php // echo $form->field($model, 'latitude') ?>

    <?php // echo $form->field($model, 'longitude') ?>

    <?php // echo $form->field($model, 'geom') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'date_approved') ?>

    <?php // echo $form->field($model, 'approved_by') ?>

    <?php // echo $form->field($model, 'date_created') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'date_updated') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
