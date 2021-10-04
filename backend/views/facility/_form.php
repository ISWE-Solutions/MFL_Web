<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model backend\models\MFLFacility */
/* @var $form yii\widgets\ActiveForm */
$lat = $model->latitude;
$lng = $model->longitude;
$model->geom = !empty($lat) && !empty($lng) ? $lat . "," . $lng : Yii::$app->params['center_lat'] . "," . Yii::$app->params['center_lng'];
if (!empty($model->district_id)) {
    $model->province_id = backend\models\Districts::findOne($model->district_id)->province_id;
}
$location = "";
if (!empty($model->latitude) && !empty($model->longitude)) {
    $location = [
        'latitude' => $model->latitude,
        'longitude' => $model->longitude,
    ];
} else {
    $location = [
        'latitude' => Yii::$app->params['center_lat'],
        'longitude' => Yii::$app->params['center_lng'],
    ];
}
?>

<div class="mflfacility-form">

    <hr class="dotted short">
    <div class="row">
        <div class="col-lg-6">

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Facility details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <?php $form = ActiveForm::begin(); ?>
                            <?=
                            $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility name'])
                            ?>
                            <?=
                            $form->field($model, 'DHIS2_UID')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter DHIS2 UID'])
                            ?>
                            <?=
                            $form->field($model, 'HMIS_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter HMIS Code'])
                            ?>

                            <?=
                            $form->field($model, 'smartcare_GUID')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter Smart care GUID'])
                            ?>

                            <?=
                            $form->field($model, 'eLMIS_ID')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter ELMIS ID'])
                            ?>

                            <?=
                            $form->field($model, 'iHRIS_ID')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter IHRIS ID'])
                            ?>
                        </div>
                        <div class="col-lg-6">
                            <?=
                                    $form->field($model, 'facility_type_id')
                                    ->dropDownList(
                                            \backend\models\Facilitytype::getList(), ['custom' => true, 'prompt' => 'Select facility type', 'required' => true]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'operation_status_id')
                                    ->dropDownList(
                                            \backend\models\Operationstatus::getList(), ['custom' => true, 'prompt' => 'Select operation status', 'required' => true]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'administrative_unit_id')
                                    ->dropDownList(
                                            \backend\models\MFLAdministrativeunit::getList(), ['custom' => true, 'prompt' => 'Select administrative unit',]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'ownership_id')
                                    ->dropDownList(
                                            \backend\models\FacilityOwnership::getList(), ['custom' => true, 'prompt' => 'Select ownership', 'required' => true]
                            );
                            ?>
                            <?=
                            $form->field($model, 'catchment_population_head_count')->textInput(['placeholder' =>
                                'Enter population head count'])
                            ?>

                            <?=
                            $form->field($model, 'catchment_population_cso')->textInput(['placeholder' =>
                                'Enter population cso'])
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Contact and other details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'number_of_beds')->textInput(['placeholder' => 'Enter facility number of beds']) ?>
                            <?= $form->field($model, 'number_of_cots')->textInput(['placeholder' => 'Enter facility number of cots']) ?>
                            <?= $form->field($model, 'number_of_nurses')->textInput(['placeholder' => 'Enter facility number of nurses']) ?>
                            <?= $form->field($model, 'number_of_doctors')->textInput(['placeholder' => 'Enter facility number of doctors']) ?>
                            <?= $form->field($model, 'number_of_paramedics')->textInput(['placeholder' => 'Pharmacy, Imaging, and laboratory personnel']) ?>
                            <?= $form->field($model, 'number_of_midwives')->textInput(['placeholder' => 'Enter facility number of midwives']) ?>
                            <?= $form->field($model, 'postal_address')->textInput(['maxlength' => true, 'placeholder' => 'Enter facility postal address']) ?>
                            <?= $form->field($model, 'web_address')->textInput(['maxlength' => true]) ?>
                            
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
                            <?=
                            $form->field($model, 'address_line1')->textarea(['rows' => 4, 'placeholder' =>
                                'Enter physical address 1']);
                            ?>
                            <?=
                            $form->field($model, 'address_line2')->textarea(['rows' => 4, 'placeholder' =>
                                'Enter physical address 2']);
                            ?>
                            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'fax')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Location details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <?php
                            echo
                                    $form->field($model, 'province_id')
                                    ->dropDownList(
                                            \backend\models\Provinces::getProvinceList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Please select a province', 'required' => true]
                            );
                            echo Html::hiddenInput('selected_id', $model->isNewRecord ? '' : $model->district_id, ['id' => 'selected_id']);
                            echo $form->field($model, 'district_id')->widget(DepDrop::classname(), [
                                'options' => ['id' => 'dist_id', 'custom' => true, 'required' => TRUE],
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginOptions' => [
                                    'depends' => ['prov_id'],
                                    'initialize' => $model->isNewRecord ? false : true,
                                    'placeholder' => 'Please select a district',
                                    'url' => Url::to(['/constituencies/district']),
                                    'params' => ['selected_id'],
                                    'loadingText' => 'Loading districts....',
                                ]
                            ]);
                            echo Html::hiddenInput('selected_id2', $model->isNewRecord ? '' : $model->constituency_id, ['id' => 'selected_id2']);

                            echo $form->field($model, 'constituency_id')->widget(DepDrop::classname(), [
                                'options' => ['id' => 'constituency_id', 'custom' => true,],
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginOptions' => [
                                    'depends' => ['dist_id'],
                                    'initialize' => $model->isNewRecord ? false : true,
                                    'placeholder' => 'Please select a constituency',
                                    'url' => Url::to(['/constituencies/constituency']),
                                    'params' => ['selected_id2'],
                                    'loadingText' => 'Loading constituencies....',
                                ]
                            ]);

                            echo Html::hiddenInput('selected_id3', $model->isNewRecord ? '' : $model->ward_id, ['id' => 'selected_id3']);

                            echo $form->field($model, 'ward_id')->widget(DepDrop::classname(), [
                                'options' => ['id' => 'w_id', 'custom' => true,],
                                'type' => DepDrop::TYPE_SELECT2,
                                'pluginOptions' => [
                                    'depends' => ['dist_id'],
                                    'initialize' => $model->isNewRecord ? false : true,
                                    'placeholder' => 'Please select a ward',
                                    'url' => Url::to(['/constituencies/ward']),
                                    'params' => ['selected_id3'],
                                    'loadingText' => 'Loading wards....',
                                ]
                            ]);
                            echo
                                    $form->field($model, 'location_type_id')
                                    ->dropDownList(
                                            \backend\models\LocationType::getList(), ['custom' => true, 'prompt' => 'Please select location type', 'required' => false]
                            );
                            ?>
                        </div>
                        <div class="col-lg-8">
                            <?php
                            echo $form->field($model, 'geom')->widget('\pigolab\locationpicker\CoordinatesPicker', [
                                'key' => 'AIzaSyB6G0OqzcLTUt1DyYbWFbK4MPUbi1mSCSc', // require , Put your google map api key
                                'valueTemplate' => '{latitude},{longitude}', // Optional , this is default result format
                                'options' => [
                                    'style' => 'width: 100%; height: 400px', // map canvas width and height
                                ],
                                'enableSearchBox' => true, // Optional , default is true
                                'searchBoxOptions' => [// searchBox html attributes
                                    'style' => 'width: 300px;', // Optional , default width and height defined in css coordinates-picker.css
                                ],
                                'searchBoxPosition' => new JsExpression('google.maps.ControlPosition.TOP_LEFT'), // optional , default is TOP_LEFT
                                'mapOptions' => [
                                    // google map options
                                    // visit https://developers.google.com/maps/documentation/javascript/controls for other options
                                    'mapTypeControl' => true, // Enable Map Type Control
                                    'mapTypeControlOptions' => [
                                        'style' => new JsExpression('google.maps.MapTypeControlStyle.HORIZONTAL_BAR'),
                                        'position' => new JsExpression('google.maps.ControlPosition.TOP_LEFT'),
                                    ],
                                    'streetViewControl' => false, // Enable Street View Control
                                ],
                                'clientOptions' => [
                                    // jquery-location-picker options
                                    'radius' => 300,
                                    'addressFormat' => 'street_number',
                                    'zoom' => 6,
                                    'location' => $location
                                ]
                            ])->label("GPS coordinates (location of facility) - Zoom in and drag the marker to the facility location")
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">


        </div>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-sm']) ?>
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>
