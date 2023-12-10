<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var backend\models\CoursesSearch $model */
/** @var yii\widgets\ActiveForm $form */


if (
    isset($_GET['Facility']['province_id']) &&
    !empty($_GET['Facility']['province_id'])
) {
    $model->province_id = $_GET['Facility']['province_id'];
}
if (
    isset($_GET['Facility']['district_id']) &&
    !empty($_GET['Facility']['district_id'])
) {
    $model->district_id = $_GET['Facility']['district_id'];
}
if (
    isset($_GET['Facility']['ownership']) &&
    !empty($_GET['Facility']['ownership'])
) {
    $model->ownership = $_GET['Facility']['ownership'];
}
if (
    isset($_GET['Facility']['type']) &&
    !empty($_GET['Facility']['type'])
) {
    $model->type = $_GET['Facility']['type'];
}
if (
    isset($_GET['Facility']['name']) &&
    !empty($_GET['Facility']['name'])
) {
    $model->name = $_GET['Facility']['name'];
}
if (
    isset($_GET['Facility']['constituency_id']) &&
    !empty($_GET['Facility']['constituency_id'])
) {
    $model->constituency_id = $_GET['Facility']['constituency_id'];
}
if (
    isset($_GET['Facility']['ward_id']) &&
    !empty($_GET['Facility']['ward_id'])
) {
    $model->ward_id = $_GET['Facility']['ward_id'];
}

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'GET',
]);
?>
<div class="row">
    <div class="col-lg-3">
        <?=
        $form->field($model, 'province_id', [
            'labelOptions' => [
                'class' => 'text-dark is-required',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(Select2::classname(), [
            'data' => \backend\models\Provinces::getProvinceList(),
            'theme' => Select2::THEME_MATERIAL,
            'options' => ['placeholder' => 'Filter by province', 'id' => 'province_id'],
            'pluginOptions' => [
                'allowClear' => true,
                
            ],
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        $model->isNewRecord = !empty($_GET['Facility']['province_id']) ? false : true;
        echo Html::hiddenInput('selected_id', $model->isNewRecord ? '' : $model->district_id, ['id' => 'selected_id']);
        echo $form->field($model, 'district_id', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(DepDrop::classname(), [
            'options' => ['id' => 'district_id', 'custom' => true, 'required' => false,],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'theme' => Select2::THEME_MATERIAL,
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '100%',
                ],
            ],
            'pluginOptions' => [
                'depends' => ['province_id'],
                'initialize' => true,
                'placeholder' => 'Filter by district',
                'prompt' => 'Filter by district',
                'url' => Url::to(['/site/district']),
                'allowClear' => true,
                'params' => ['selected_id'],
                'loadingText' => 'Loading districts....',
            ]
        ]);
        ?>
    </div>
    <div class="col-lg-3">
        <?php
        echo Html::hiddenInput('selected_id2', $model->constituency_id, ['id' => 'selected_id2']);
        echo $form->field($model, 'constituency_id', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(DepDrop::classname(), [
            'options' => ['id' => 'constituency_id', 'custom' => true,],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'theme' => Select2::THEME_MATERIAL,
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '100%',
                ],
            ],
            'pluginOptions' => [
                'depends' => ['district_id'],
                'initialize' => true,
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
        echo Html::hiddenInput('selected_id3', $model->ward_id, ['id' => 'selected_id3']);
        echo $form->field($model, 'ward_id', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(DepDrop::classname(), [
            'options' => ['id' => 'ward_id', 'custom' => true,],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'theme' => Select2::THEME_MATERIAL,
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '100%',
                ],
            ],
            'pluginOptions' => [
                'depends' => ['district_id'],
                'initialize' =>  true,
                'placeholder' => 'Filter by ward',
                'prompt' => 'Filter by ward',
                'url' => Url::to(['/site/ward']),
                'params' => ['selected_id3'],
                'loadingText' => 'Loading wards....',
            ]
        ]);
        ?>
    </div>
    <div class="col-lg-4">
        <?=
        $form->field($model, 'name', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->textInput(['maxlength' => true, 'placeholder' =>
        'Filter by facility name', 'required' => false,]);
        ?>
    </div>
    <div class="col-lg-4">
        <?=
        $form->field($model, 'type', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(Select2::classname(), [
            'data' => \backend\models\Facilitytype::getList(),
            'theme' => Select2::THEME_MATERIAL,
            'options' => ['placeholder' => 'Filter by facility type', 'id' => 'type'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        ?>
    </div>
    <div class="col-lg-4">
        <?=
        $form->field($model, 'ownership', [
            'labelOptions' => [
                'class' => 'text-dark',
                'style' => "font-size:13px;font-weight:normal;",
            ],
        ])->widget(Select2::classname(), [
            'data' => \backend\models\FacilityOwnership::getList(),
            'theme' => Select2::THEME_MATERIAL,
            'options' => ['placeholder' => 'Filter by ownership', 'id' => 'ownership'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        ?>
    </div>

    <div class="col-lg-12">
        <?= Html::submitButton('Filter', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>