<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\MFLFacilitySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="mflfacility-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'DHIS2_UID') ?>

    <?= $form->field($model, 'HMIS_code') ?>

    <?= $form->field($model, 'smartcare_GUID') ?>

    <?= $form->field($model, 'eLMIS_ID') ?>

    <?php // echo $form->field($model, 'iHRIS_ID') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'number_of_beds') ?>

    <?php // echo $form->field($model, 'number_of_cots') ?>

    <?php // echo $form->field($model, 'number_of_nurses') ?>

    <?php // echo $form->field($model, 'number_of_doctors') ?>

    <?php // echo $form->field($model, 'address_line1') ?>

    <?php // echo $form->field($model, 'address_line2') ?>

    <?php // echo $form->field($model, 'postal_address') ?>

    <?php // echo $form->field($model, 'web_address') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'mobile') ?>

    <?php // echo $form->field($model, 'fax') ?>

    <?php // echo $form->field($model, 'catchment_population_head_count') ?>

    <?php // echo $form->field($model, 'catchment_population_cso') ?>

    <?php // echo $form->field($model, 'star') ?>

    <?php // echo $form->field($model, 'rated') ?>

    <?php // echo $form->field($model, 'rating') ?>

    <?php // echo $form->field($model, 'longitude') ?>

    <?php // echo $form->field($model, 'latitude') ?>

    <?php // echo $form->field($model, 'comment') ?>

    <?php // echo $form->field($model, 'geom') ?>

    <?php // echo $form->field($model, 'timestamp') ?>

    <?php // echo $form->field($model, 'updated') ?>

    <?php // echo $form->field($model, 'slug') ?>

    <?php // echo $form->field($model, 'administrative_unit_id') ?>

    <?php // echo $form->field($model, 'constituency_id') ?>

    <?php // echo $form->field($model, 'district_id') ?>

    <?php // echo $form->field($model, 'facility_type_id') ?>

    <?php // echo $form->field($model, 'location_type_id') ?>

    <?php // echo $form->field($model, 'operation_status_id') ?>

    <?php // echo $form->field($model, 'ownership_id') ?>

    <?php // echo $form->field($model, 'ward_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
