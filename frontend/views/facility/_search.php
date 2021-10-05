<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

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
                $form->field($model, 'service_category')
                ->dropDownList(
                        \backend\models\FacilityServicecategory::getList(), ['custom' => true, 'prompt' => 'Filter by service type', 'required' => false]
        );
        ?>
    </div>
    <div class="col-lg-3">
        <?=
                $form->field($model, 'service')
                ->dropDownList(
                        \backend\models\FacilityService::getList(), ['custom' => true, 'prompt' => 'Filter by service', 'required' => false]
        );
        ?>
    </div>
    <div class="col-lg-3">
        <?=
                $form->field($model, 'type')
                ->dropDownList(
                        \backend\models\Facilitytype::getList(), ['custom' => true, 'prompt' => 'Filter by facility type', 'required' => false]
        )->label("Facility type");
        ?>
    </div>
    <div class="col-lg-3">
        <?=
                $form->field($model, 'ownership')
                ->dropDownList(
                        \backend\models\FacilityOwnership::getList(), ['custom' => true, 'prompt' => 'Filter by facility owner', 'required' => false]
        );
        ?>
    </div>
    <div class="col-lg-3">
        <?=
        $form->field($model, 'ownership_type')
                ->dropDownList(
                        [1 => "Public", 2 => "Private"], ['custom' => true, 'prompt' => 'Filter by ownership type', 'required' => false]
        );
        ?>
    </div>
    <div class="col-lg-3">
        <?=
                $form->field($model, 'operational_status')
                ->dropDownList(
                        \backend\models\Operationstatus::getList(), ['custom' => true, 'prompt' => 'Filter by operation status', 'required' => false]
        );
        ?>
    </div>
    
    <div class="col-lg-3">
        <?php
        echo
                $form->field($model, 'province_id')
                ->dropDownList(
                        \backend\models\Provinces::getProvinceList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Filter by province', 'required' => false]
        );
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
    <?php //$form->field($model, 'smartcare_GUID')      ?>

    <?php //$form->field($model, 'eLMIS_ID')      ?>

    <?php // echo $form->field($model, 'iHRIS_ID')      ?>

    <?php // echo $form->field($model, 'name')      ?>

    <?php // echo $form->field($model, 'number_of_beds')      ?>

    <?php // echo $form->field($model, 'number_of_cots')      ?>

    <?php // echo $form->field($model, 'number_of_nurses')      ?>

    <?php // echo $form->field($model, 'number_of_doctors')      ?>

    <?php // echo $form->field($model, 'address_line1')      ?>

    <?php // echo $form->field($model, 'address_line2')      ?>

    <?php // echo $form->field($model, 'postal_address')      ?>

    <?php // echo $form->field($model, 'web_address')      ?>

    <?php // echo $form->field($model, 'email')      ?>

    <?php // echo $form->field($model, 'phone')      ?>

    <?php // echo $form->field($model, 'mobile')      ?>

    <?php // echo $form->field($model, 'fax')      ?>

    <?php // echo $form->field($model, 'catchment_population_head_count')      ?>

    <?php // echo $form->field($model, 'catchment_population_cso')      ?>

    <?php // echo $form->field($model, 'star')      ?>

    <?php // echo $form->field($model, 'rated')      ?>

    <?php // echo $form->field($model, 'rating')      ?>

    <?php // echo $form->field($model, 'longitude')      ?>

    <?php // echo $form->field($model, 'latitude')      ?>

    <?php // echo $form->field($model, 'comment')      ?>

    <?php // echo $form->field($model, 'geom')      ?>

    <?php // echo $form->field($model, 'timestamp')      ?>

    <?php // echo $form->field($model, 'updated')      ?>

    <?php // echo $form->field($model, 'slug')      ?>

    <?php // echo $form->field($model, 'administrative_unit_id')      ?>

    <?php // echo $form->field($model, 'constituency_id')      ?>

    <?php // echo $form->field($model, 'district_id')      ?>

    <?php // echo $form->field($model, 'facility_type_id')      ?>

    <?php // echo $form->field($model, 'location_type_id')      ?>

    <?php // echo $form->field($model, 'operation_status_id')      ?>

    <?php // echo $form->field($model, 'ownership_id')      ?>

    <?php // echo $form->field($model, 'ward_id')       ?>

    <div class="form-group col-lg-12">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

