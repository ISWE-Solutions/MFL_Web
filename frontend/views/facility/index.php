<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\grid\ActionColumn;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MFLFacilitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Facility list';
$this->params['breadcrumbs'][] = $this->title;
$provinceId = "";
$districtId = "";

if (!empty($_GET['FacilitySearch']['province_id'])) {
    $provinceId = $_GET['FacilitySearch']['province_id'];
}
if (!empty($_GET['FacilitySearch']['district_id'])) {
    $districtId = $_GET['FacilitySearch']['district_id'];
}
?>
<div class="container-fluid">
    <div class="row"  style="margin-right:-100px;margin-left:-100px;">
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-body">

                    <?php
                    $gridColumns = [
                        //'id',
                        [
                            'enableSorting' => true,
                            'attribute' => 'name',
                            'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => \backend\models\Facility::getNamesFilter($provinceId,$districtId),
                            'filterInputOptions' => ['prompt' => 'Filter by name', 'class' => 'form-control',],
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'province_id',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => true,
                            'filter' => \backend\models\Provinces::getProvinceList(),
                            'filterInputOptions' => ['prompt' => 'Filter by Province', 'class' => 'form-control', 'id' => null],
                            'value' => function ($model) {
                                $province_id = backend\models\Districts::findOne($model->district_id)->province_id;
                                $name = backend\models\Provinces::findOne($province_id)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'district_id',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => \backend\models\Districts::getList($provinceId),
                            'filterInputOptions' => ['prompt' => 'Filter by District', 'class' => 'form-control', 'id' => null],
                            'value' => function ($model) {
                                $name = backend\models\Districts::findOne($model->district_id)->name;
                                return $name;
                            },
                        ],
                        [
                            'label' => 'Facility type',
                            'attribute' => 'type',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => \backend\models\Facilitytype::getList(),
                            'filterInputOptions' => ['prompt' => 'Filter by facility type', 'class' => 'form-control', 'id' => null],
                            'value' => function ($model) {
                                $name = backend\models\Facilitytype::findOne($model->type)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'ownership_type',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => [1 => "Public", 2 => "Private"],
                            'filterInputOptions' => ['prompt' => 'Filter by ownership', 'class' => 'form-control', 'id' => null],
                            'format' => 'raw',
                            'value' => function ($model) {
                                $status_arr = [1 => "Public", 2 => "Private"];
                                return $status_arr[$model->ownership_type];
                            },
                        ],
                        [
                            'attribute' => 'ownership',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filter' => \backend\models\FacilityOwnership::getList(),
                            'filterInputOptions' => ['prompt' => 'Filter by ownership', 'class' => 'form-control', 'id' => null],
                            'value' => function ($model) {
                                $name = backend\models\FacilityOwnership::findOne($model->ownership);
                                return !empty($name) ? $name->name : "";
                            },
                        ],
                        ['class' => ActionColumn::className(),
                            'options' => ['style' => 'width:40px;'],
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a(
                                            '<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
                                        'title' => 'View facility',
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-pjax' => '0',
                                        'style' => "padding:5px;",
                                        'class' => 'bt btn-lg'
                                            ]
                                    );
                                },
                            ]
                        ],
                    ];
                    $gridColumns2 = [
                        //'id',
                        [
                            'enableSorting' => true,
                            'attribute' => 'name',
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'province_id',
                            'value' => function ($model) {
                                $province_id = backend\models\Districts::findOne($model->district_id)->province_id;
                                $name = backend\models\Provinces::findOne($province_id)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'district_id',
                            'value' => function ($model) {
                                $name = backend\models\Districts::findOne($model->district_id)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'constituency_id',
                            'value' => function ($model) {
                                $name = !empty($model->constituency_id) ? backend\models\Constituency::findOne($model->constituency_id)->name : "";
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'ward_id',
                            'value' => function ($model) {
                                $name = !empty($model->ward_id) ? backend\models\Wards::findOne($model->ward_id)->name : "";
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'zone_id',
                            'value' => function ($model) {
                                $name = !empty($model->zone_id) ? backend\models\Zones::findOne($model->zone_id)->name : "";
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'location',
                            'value' => function ($model) {
                                $name = backend\models\LocationType::findOne($model->location)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function ($model) {
                                $name = backend\models\Facilitytype::findOne($model->type)->name;
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'ownership',
                            'value' => function ($model) {
                                $name = backend\models\FacilityOwnership::findOne($model->ownership)->name;
                                return $name;
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'ownership_type',
                            'value' => function ($model) {
                                $status_arr = [1 => "Public", 2 => "Private"];
                                return $status_arr[$model->ownership_type];
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'operational_status',
                            'value' => function ($model) {
                                $name = backend\models\Operationstatus::findOne($model->operational_status)->name;
                                return $name;
                            },
                        ],
                        [
                            'format' => 'raw',
                            'attribute' => 'mobility_status',
                            'value' => function ($model) {
                                $status_arr = [1 => "Mobile", 2 => "Fixed", 3 => "telemedicine"];
                                return $status_arr[$model->mobility_status];
                            },
                        ],
                        'accesibility',
                        'catchment_population_head_count',
                        'catchment_population_cso',
                        'number_of_households',
                        'latitude',
                        'longitude',
//                        [
//                            'attribute' => 'status',
//                            'value' => function($model) {
//                                $str = "";
//                                if ($model->status === 1) {
//                                    $str = "<span class='badge badge-pill badge-success'> "
//                                            . "<i class='fa fa-check'></i> Active</span>";
//                                }
//                                if ($model->status === 0) {
//                                    $str = "<span class='badge badge-pill badge-danger'> "
//                                            . "<i class='fa fa-times'></i> Inactive";
//                                }
//                                if ($model->status === 2) {
//                                    $str = "<span class='badge badge-pill badge-dark'> "
//                                            . "<i class='fas fa-hourglass-half'></i> Pending approval";
//                                }
//                                return $str;
//                            },
//                            'format' => 'raw',
//                        ],
                    ];

                    $fullExportMenu = "";
                    if (!empty($dataProvider) && $dataProvider->getCount() > 0) {
                        $fullExportMenu = ExportMenu::widget([
                                    'dataProvider' => $dataProvider,
                                    'columns' => $gridColumns2,
                                    'columnSelectorOptions' => [
                                        'label' => 'Cols...',
                                    ],
                                    'batchSize' => 200,
                                    // 'hiddenColumns' => [0, 9],
                                    //'disabledColumns' => [1, 2],
                                    //'target' => ExportMenu::TARGET_BLANK,
                                    'exportConfig' => [
                                        ExportMenu::FORMAT_TEXT => false,
                                        ExportMenu::FORMAT_HTML => false,
                                        ExportMenu::FORMAT_EXCEL => false,
                                        ExportMenu::FORMAT_PDF => false,
                                        ExportMenu::FORMAT_CSV => false,
                                    ],
                                    'pjaxContainerId' => 'kv-pjax-container',
                                    'exportContainer' => [
                                        'class' => 'btn-group mr-2'
                                    ],
                                    'dropdownOptions' => [
                                        'label' => 'Export to Excel',
                                        'class' => 'btn btn-outline-secondary',
                                        'itemsBefore' => [
                                            '<div class="dropdown-header">Export All Data</div>',
                                        ],
                                    ],
                                    'filename' => 'mfl_facilities_export' . date("YmdHis")
                        ]);
                    }
                    //  echo "<p class='text-sm'>Found " . $dataProvider->getCount() . " search record(s)</p>";
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => $gridColumns,
                        'condensed' => true,
                        'responsive' => true,
                        'hover' => true,
                        // 'pjax' => true,
                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container']],
                        'panel' => [
                            'type' => GridView::TYPE_PRIMARY,
                        // 'heading' => '<h3 class="panel-title"><i class="fas fa-book"></i> Library</h3>',
                        ],
                        // set a label for default menu
                        'export' => false,
                        'exportContainer' => [
                            'class' => 'btn-group mr-2'
                        ],
                        // your toolbar can include the additional full export menu
                        'toolbar' => [
                            '{export}',
                            $fullExportMenu,
                        ]
                    ]);
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>
