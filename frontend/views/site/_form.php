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
    isset($_GET['FacilityFilter']['province']) &&
    !empty($_GET['FacilityFilter']['province'])
) {
    $model->province = $_GET['FacilityFilter']['province'];
}
if (
    isset($_GET['FacilityFilter']['district']) &&
    !empty($_GET['FacilityFilter']['district'])
) {
    $model->district = $_GET['FacilityFilter']['district'];
}
if (
    isset($_GET['FacilityFilter']['constituency']) &&
    !empty($_GET['FacilityFilter']['constituency'])
) {
    $model->constituency = $_GET['FacilityFilter']['constituency'];
}
if (
    isset($_GET['FacilityFilter']['ward']) &&
    !empty($_GET['FacilityFilter']['ward'])
) {
    $model->ward = $_GET['FacilityFilter']['ward'];
}

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'GET',
]);
?>
<div class="row">
    <div class="col-lg-3">
        <?=
        $form->field($model, 'province', [
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
        echo $form->field($model, 'district', [
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
        echo Html::hiddenInput('selected_id2', $model->constituency, ['id' => 'selected_id2']);
        echo $form->field($model, 'constituency', [
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
        echo Html::hiddenInput('selected_id3', $model->ward, ['id' => 'selected_id3']);
        echo $form->field($model, 'ward', [
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


    <div class="col-lg-12">
        <?= Html::submitButton('Filter', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>