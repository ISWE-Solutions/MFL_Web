<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use yii\web\JsExpression;
use borales\extensions\phoneInput\PhoneInput;

/* @var $this yii\web\View */
/* @var $model backend\models\Facility */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
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

$user_type = Yii::$app->user->identity->user_type;
$district_user_district_id = "";
$province_user_province_id = "";
?>

<div class="mflfacility-form">

    <hr class="dotted short">
    <div class="row">
        <div class="col-lg-12">

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Facility details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <?php $form = ActiveForm::begin(); ?>
                            <?=
                            $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility name'])
                            ?>


                            <?=
                                    $form->field($model, 'type')
                                    ->dropDownList(
                                            \backend\models\Facilitytype::getList(), ['custom' => true, 'prompt' => 'Select facility type', 'required' => true]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'operational_status')
                                    ->dropDownList(
                                            \backend\models\Operationstatus::getList(), ['custom' => true, 'prompt' => 'Select operation status', 'required' => true]
                            );
                            ?>
                            <?php
                            //$model->ownership_type = 1;
                            echo $form->field($model, 'ownership_type')
                                    ->dropDownList(
                                            [1 => "Public", 2 => "Private"], ['custom' => true, 'prompt' => 'Select ownership type',]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'ownership')
                                    ->dropDownList(
                                            \backend\models\FacilityOwnership::getList(), ['custom' => true, 'prompt' => 'Select ownership', 'required' => true]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'mobility_status')
                                    ->dropDownList(
                                            [1 => "Mobile", 2 => "Fixed", 3 => "telemedicine"], ['custom' => true, 'prompt' => 'Select mobility status',]
                            );
                            ?>
                            <?=
                                    $form->field($model, 'accesibility')
                                    ->dropDownList(
                                            ["Open" => "Open", "Restricted" => "Restricted"], ['custom' => true, 'prompt' => 'Select accessibility',]
                            );
                            ?>
                            <?= $form->field($model, 'postal_address')->textInput(['placeholder' => 'Enter facility postal address']) ?>

                        </div>
                        <div class="col-lg-4">
                            <?=
                            $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility email'])
                            ?>

                            <?=
                            $form->field($model, 'mobile', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility Mobile no'])
                            ?>
                            <?=
                            $form->field($model, 'phone', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility telephone no'])
                            ?>
                            <?=
                            $form->field($model, 'town', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility town'])
                            ?>
                            <?=
                            $form->field($model, 'plot_no', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility plot no'])
                            ?>
                            <?=
                            $form->field($model, 'street', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility street'])
                            ?>
                            <?=
                            $form->field($model, 'fax', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter facility fax'])
                            ?>
                            <?= $form->field($model, 'number_of_households')->textInput(['placeholder' => 'Enter facility number of households']) ?>

                        </div>
                        <div class="col-lg-4">
                            <?=
                            $form->field($model, 'disa_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter disa code'])
                            ?>
                            <?=
                            $form->field($model, 'hims_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter hims Code'])
                            ?>

                            <?=
                            $form->field($model, 'smartcare_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter Smartcare code'])
                            ?>

                            <?=
                            $form->field($model, 'elmis_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter elmis code'])
                            ?>

                            <?=
                            $form->field($model, 'hpcz_code')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter hpcz code'])
                            ?>
                            <?=
                            $form->field($model, 'catchment_population_head_count')->textInput(['placeholder' =>
                                'Enter population head count'])
                            ?>

                            <?=
                            $form->field($model, 'catchment_population_cso')->textInput(['placeholder' =>
                                'Enter population cso'])
                            ?>
                            <?=
                            $form->field($model, 'physical_address')->textarea(['rows' => 3, "placeholder" => "Enter physical address"])->label("Physical address");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--        <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Contact and other details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Location details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <?php
                            if ($user_type == "District") {
                                $district_user_district_id = Yii::$app->user->identity->district_id;
                                $distric_model = backend\models\Districts::findOne($district_user_district_id);

                                echo $form->field($model, 'province_id')->hiddenInput(['value' => $distric_model->province_id])->label(false);
                                echo $form->field($model, 'district_id')->hiddenInput(['value' => $district_user_district_id])->label(false);
                                echo
                                        $form->field($model, 'constituency_id')
                                        ->dropDownList(
                                                \backend\models\Constituency::getListByDistrictID($district_user_district_id), ['id' => 'dist_id', 'custom' => true, 'prompt' => 'Please select a constituency', 'required' => false]
                                );
                                echo
                                        $form->field($model, 'ward_id')
                                        ->dropDownList(
                                                \backend\models\Wards::getListByDistrictID($district_user_district_id), ['id' => 'dist_id', 'custom' => true, 'prompt' => 'Please select a ward', 'required' => false]
                                );
                            }

                            if ($user_type == "Province") {
                                $province_user_province_id = Yii::$app->user->identity->province_id;
                                echo $form->field($model, 'province_id')->hiddenInput(['value' => $province_user_province_id, 'id' => 'prov_id',])->label(false);

                                echo
                                        $form->field($model, 'district_id')
                                        ->dropDownList(
                                                \backend\models\Districts::getListByProvinceID($province_user_province_id), ['id' => 'dist_id', 'custom' => true, 'prompt' => 'Please select a district', 'required' => true]
                                );
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
                                    //'loadingText' => 'Loading constituencies....',
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
                                    //'loadingText' => 'Loading wards....',
                                    ]
                                ]);
                            }

                            if ($user_type == "National") {
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
                                    // 'loadingText' => 'Loading districts....',
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
                                    //'loadingText' => 'Loading constituencies....',
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
                                    //'loadingText' => 'Loading wards....',
                                    ]
                                ]);
                            }
                            echo
                                    $form->field($model, 'location')
                                    ->dropDownList(
                                            \backend\models\LocationType::getList(), ['custom' => true, 'prompt' => 'Please select location type', 'required' => false]
                            );
                            echo
                                    $form->field($model, 'zone_id')
                                    ->dropDownList(
                                            \backend\models\Zones::getList(), ['custom' => true, 'prompt' => 'Please select zone', 'required' => false]
                            );
                            ?>
                            <?=
                            $form->field($model, 'latitude')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter Latitude'])
                            ?>
                            <?=
                            $form->field($model, 'longitude')->textInput(['maxlength' => true, 'placeholder' =>
                                'Enter Longitude'])
                            ?>
                        </div>
                        <div class="col-lg-8">
                            <?php
                            echo $form->field($model, 'coordinates')->widget('\pigolab\locationpicker\CoordinatesPicker', [
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
