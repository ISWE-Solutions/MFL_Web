<?php

use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use backend\models\User;
use common\models\Role;
use \kartik\popover\PopoverX;
use yii\grid\ActionColumn;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MFLFacilitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Facilities';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">

        <p>
            <?php
            if (\backend\models\User::userIsAllowedTo('Manage facilities')) {
                echo Html::a('<i class="fa fa-plus"></i> Add facility', ['create'], ['class' => 'btn btn-sm btn-primary']);
                echo '<hr class="dotted short">';
            }
            ?>
        </p>

        <?php
        $gridColumns = [
            //'id',
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
                'filter' => \backend\models\Districts::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by District', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                    $name = backend\models\Districts::findOne($model->district_id)->name;
                    return $name;
                },
            ],
            [
                'attribute' => 'facility_type_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\Facilitytype::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by facility type', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                    $name = backend\models\Facilitytype::findOne($model->facility_type_id)->name;
                    return $name;
                },
            ],
            [
                'attribute' => 'ownership_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\FacilityOwnership::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by ownership', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                       $name = backend\models\FacilityOwnership::findOne(["shared_id" => $model->ownership]);
                    return $name;
                },
            ],
            [
                'attribute' => 'HMIS_code',
                'filter' => false,
            ],
            [
                'enableSorting' => true,
                'attribute' => 'name',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\MFLFacility::getNames(),
                'filterInputOptions' => ['prompt' => 'Filter by name', 'class' => 'form-control',],
                'format' => 'raw',
            ],
            //'HMIS_code',
            // 'smartcare_GUID',
            // 'eLMIS_ID',
            // 'iHRIS_ID',
            //'number_of_beds',
            //'number_of_cots',
            //'number_of_nurses',
            //'number_of_doctors',
            //'address_line1',
            //'address_line2',
            //'postal_address',
            //'web_address',
            //'email:email',
            //'phone',
            //'mobile',
            //'fax',
            //'catchment_population_head_count',
            //'catchment_population_cso',
            //'star:ntext',
            //'rated:ntext',
            //'rating:ntext',
            //'longitude',
            //'latitude',
            //'comment:ntext',
            //'geom',
            //'timestamp',
            //'updated',
            //'slug',
            //'administrative_unit_id',
            //'constituency_id',
            //'location_type_id',
            //'operation_status_id',
            //'ownership_id',
            //'ward_id',
            ['class' => ActionColumn::className(),
                'options' => ['style' => 'width:130px;'],
                'template' => '{view}{update}{delete}',
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
                    'update' => function ($url, $model) {
                        if (User::userIsAllowedTo('Manage facilities')) {
                            return Html::a(
                                            '<span class="fas fa-edit"></span>', ['update', 'id' => $model->id], [
                                        'title' => 'Update facility',
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-pjax' => '0',
                                        'style' => "padding:5px;",
                                        'class' => 'bt btn-lg'
                                            ]
                            );
                        }
                    },
                    'delete' => function ($url, $model) {
                        if (User::userIsAllowedTo('Remove facility')) {
                            return Html::a(
                                            '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                        'title' => 'Delete',
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete facility: ' . $model->name . '?<br>'
                                            . 'Facility will only be removed if it is not being used by the system!',
                                            'method' => 'post',
                                        ],
                                        'style' => "padding:5px;",
                                        'class' => 'bt btn-lg'
                                            ]
                            );
                        }
                    },
                ]
            ],
        ];
        $gridColumns2 = [
            //'id',
            [
                'enableSorting' => true,
                'attribute' => 'name',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\MFLFacility::getNames(),
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
                'filter' => \backend\models\Districts::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by District', 'class' => 'form-control', 'id' => null],
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
                'attribute' => 'administrative_unit_id',
                'value' => function ($model) {
                    $name = !empty($model->administrative_unit_id) ? backend\models\MFLAdministrativeunit::findOne($model->administrative_unit_id)->name : "";
                    return $name;
                },
            ],
            [
                'attribute' => 'location_type_id',
                'value' => function ($model) {
                    $name = backend\models\LocationType::findOne($model->location_type_id)->name;
                    return $name;
                },
            ],
            [
                'attribute' => 'facility_type_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\Facilitytype::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by facility type', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                    $name = backend\models\Facilitytype::findOne($model->facility_type_id)->name;
                    return $name;
                },
            ],
            [
                'attribute' => 'ownership_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\FacilityOwnership::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by ownership', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                    $name = backend\models\FacilityOwnership::findOne(["shared_id" => $model->ownership]);
                    return $name;
                },
            ],
            [
                'format' => 'raw',
                'attribute' => 'operation_status_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\Operationstatus::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by status', 'class' => 'form-control', 'id' => null],
                'value' => function ($model) {
                    $name = backend\models\Operationstatus::findOne($model->operation_status_id)->name;

                    return strtoupper($name) === "OPERATIONAL" ? "<p style='Color: green;'>$name</p>" : "<p style='Color: red;'> $name</p>";
                },
            ],
            [
                'attribute' => 'DHIS2_UID',
            // 'visible' => false
            ],
            [
                'attribute' => 'HMIS_code',
                'filter' => false,
            ],
            'smartcare_GUID',
            'eLMIS_ID',
            'iHRIS_ID',
            'number_of_beds',
            'number_of_cots',
            'number_of_nurses',
            'number_of_doctors',
            'address_line1',
            'address_line2',
            'postal_address',
            'web_address',
            'email:email',
            'phone',
            'mobile',
            'fax',
            'catchment_population_head_count',
            'catchment_population_cso',
            //'star:ntext',
            //'rated:ntext',
            //'rating:ntext',
            'longitude',
            'latitude',
                //'comment:ntext',
                //'geom',
                //'timestamp',
                //'updated',
                //'slug',
                //'administrative_unit_id',
                //'constituency_id',
                //'location_type_id',
                //'operation_status_id',
                //'ownership_id',
        ];

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
            //  echo "<p class='text-sm'>Found " . $dataProvider->getCount() . " search record(s)</p>";
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
               // 'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container']],
                'panel' => [
                    'type' => GridView::TYPE_DEFAULT,
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
        } else {
            echo "<p class='text-sm'>There are currently no facilities in the system</p>";
        }

       
        ?>

    </div>
</div>
