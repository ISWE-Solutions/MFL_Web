<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model backend\models\FacilitySearch */
/* @var $form yii\widgets\ActiveForm */
?>



<?php
$form = ActiveForm::begin([
    'action' => ['search'],
    'method' => 'get',
]);
?>
<div class="row">
    <div class="col-lg-3">
        <?php
        echo $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' =>
        'Filter by facility name', 'required' => false,]);
        ?>
    </div>
    <div class="col-lg-3">
        <?=
        $form->field($model, 'service_category')->widget(Select2::classname(), [
            'data' => \backend\models\FacilityServicecategory::getList(),
            'options' => ['placeholder' => 'Filter by service type', 'id' => 'service_category_id'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['FacilitySearch']['service_category']) ? false : true;
        echo Html::hiddenInput('selected_id_s', $model->isNewRecord ? '' : $model->service, ['id' => 'selected_id_s']);

        echo $form->field($model, 'service')->widget(DepDrop::classname(), [
            'options' => ['id' => 'service_id', 'custom' => true,],
            'type' => DepDrop::TYPE_SELECT2,
            'pluginOptions' => [
                'depends' => ['service_category_id'],
                'initialize' => $model->isNewRecord ? false : true,
                'placeholder' => 'Filter by service',
                'url' => yii\helpers\Url::to(['/facility/services']),
                'params' => ['selected_id_s'],
                'loadingText' => 'Loading services....',
            ]
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?=
        $form->field($model, 'type')->widget(Select2::classname(), [
            'data' => \backend\models\Facilitytype::getList(),
            'options' => ['placeholder' => 'Filter by facility type', 'id' => 'type_id'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label("Facility type");
        ?>
    </div>
    <div class="col-lg-3">
        <?=
        $form->field($model, 'ownership_type')->widget(Select2::classname(), [
            'data' =>  [1 => "Public", 2 => "Private"],
            'options' => ['placeholder' => 'Filter by ownership type', 'id' => 'ownership_type_id'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['FacilitySearch']['ownership_type']) ? false : true;
        echo Html::hiddenInput('selected_id_o', $model->isNewRecord ? '' : $model->ownership, ['id' => 'selected_id_o']);

        echo $form->field($model, 'ownership')->widget(DepDrop::classname(), [
            'options' => ['id' => 'ownership_id', 'custom' => true,],
            'type' => DepDrop::TYPE_SELECT2,
            'pluginOptions' => [
                'depends' => ['ownership_type_id'],
                'initialize' => $model->isNewRecord ? false : true,
                'placeholder' => 'Filter by facility owner',
                'url' => yii\helpers\Url::to(['/facility/ownerships']),
                'params' => ['selected_id_o'],
                'loadingText' => 'Loading ownership....',
            ]
        ]);
        ?>

    </div>

    <div class="col-lg-3">
        <?=
        $form->field($model, 'operational_status')->widget(Select2::classname(), [
            'data' =>  \backend\models\Operationstatus::getList(),
            'options' => ['placeholder' => 'Filter by operation status', 'id' => 'operational_status_id'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>

    <div class="col-lg-3">
        <?=
        $form->field($model, 'province_id')->widget(Select2::classname(), [
            'data' =>  \backend\models\Provinces::getProvinceList(),
            'options' => ['placeholder' => 'Filter by province', 'id' => 'prov_id'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['FacilitySearch']['province_id']) ? false : true;
        echo Html::hiddenInput('selected_id', $model->isNewRecord ? '' : $model->district_id, ['id' => 'selected_id']);

        echo $form->field($model, 'district_id')->widget(DepDrop::classname(), [
            'options' => ['id' => 'dist_id', 'custom' => true, 'required' => false,],
            //'data' => [backend\models\Districts::getListByProvinceID($model->province_id)],
            //'value'=>$MFLFacility_model->district_id,
            'type' => DepDrop::TYPE_SELECT2,
            'pluginOptions' => [
                'depends' => ['prov_id'],
                'initialize' => $model->isNewRecord ? false : true,
                'placeholder' => 'Filter by district',
                'prompt' => 'Filter by district',
                'url' => Url::to(['/site/district']),
                'params' => ['selected_id'],
                'loadingText' => 'Loading districts....',
            ]
        ]);
        //  }
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['FacilitySearch']['district_id']) ? false : true;
        echo Html::hiddenInput('selected_id2', $model->isNewRecord ? '' : $model->constituency_id, ['id' => 'selected_id2']);
        echo $form->field($model, 'constituency_id')->widget(DepDrop::classname(), [
            'options' => ['id' => 'constituency_id', 'custom' => true,],
            // 'data' => [\backend\models\Constituency::getListByDistrictID($model->district_id)],
            'type' => DepDrop::TYPE_SELECT2,
            'pluginOptions' => [
                'depends' => ['dist_id'],
                'initialize' => $model->isNewRecord ? false : true,
                'placeholder' => 'Filter by constituency',
                'prompt' => 'Filter by constituency',
                'url' => Url::to(['/site/constituency']),
                'params' => ['selected_id2'],
                'loadingText' => 'Loading constituencies....',
            ]
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['FacilitySearch']['district_id']) ? false : true;
        echo Html::hiddenInput('selected_id3', $model->isNewRecord ? '' : $model->ward_id, ['id' => 'selected_id3']);
        echo $form->field($model, 'ward_id')->widget(DepDrop::classname(), [
            'options' => ['id' => 'ward_id', 'custom' => true,],
            //'data' => [\backend\models\Wards::getListByDistrictID($model->district_id)],
            'type' => DepDrop::TYPE_SELECT2,
            'pluginOptions' => [
                'depends' => ['dist_id'],
                'initialize' => $model->isNewRecord ? false : true,
                'placeholder' => 'Filter by ward',
                'prompt' => 'Filter by ward',
                'url' => Url::to(['/site/ward']),
                'params' => ['selected_id3'],
                'loadingText' => 'Loading wards....',
            ]
        ]);
        ?>
    </div>


    <div class="form-group col-lg-12">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>