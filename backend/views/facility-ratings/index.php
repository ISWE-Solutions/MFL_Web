<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\grid\ActionColumn;
use backend\models\User;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MFLFacilityRatingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Facility Ratings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">

        <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

        <?php
        $gridColumns = [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            [
                'group' => true,
                'attribute' => 'facility_id',
                'format' => 'raw',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\Facility::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by facility', 'class' => 'form-control',],
                'value' => function ($model) {
                    return !empty($model->facility_id) ? backend\models\Facility::findOne($model->facility_id)->name : "";
                }
            ],
            [
                'enableSorting' => true,
                'attribute' => 'rate_type_id',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => \backend\models\MFLFacilityRateTypes::getList(),
                'filterInputOptions' => ['prompt' => 'Filter by type', 'class' => 'form-control',],
                'format' => 'raw',
                'value' => function ($model) {
                    return !empty($model->rate_type_id) ? backend\models\MFLFacilityRateTypes::findOne($model->rate_type_id)->name : "";
                }
            ],
            [
                //'enableSorting' => false,
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filter' => [
                    'Very Poor' => 'Very Poor',
                    'Poor' => 'Poor',
                    'Average' => 'Average',
                    'Good' => 'Good',
                    'Very Good' => 'Very Good',
                ],
                'filterInputOptions' => ['prompt' => 'Filter by rating', 'class' => 'form-control',],
                'attribute' => 'rating',
                'format' => 'raw',
            ],
            [
                //'enableSorting' => false,
                'attribute' => 'email',
                'filter' => false,
                'format' => 'raw',
            ],
            [
                //'enableSorting' => false,
                'attribute' => 'comment',
                'filter' => false,
                'format' => 'raw',
            ],
            [
                'attribute' => 'date_created',
                'filter' => false,
                'value' => function ($model) {
                    return date('d-M-Y', $model->date_created);
                }
            ],
            ['class' => ActionColumn::className(),
                'options' => ['style' => 'width:130px;'],
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        if (User::userIsAllowedTo('Remove facility ratings')) {
                            return Html::a(
                                            '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                        'title' => 'Remove rating',
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to remove facility rating?<br>'
                                            . 'Rating will only be removed if its not being used by the system!',
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
        ?>

        <?php
        if (!empty($dataProvider) && $dataProvider->getCount() > 0) {
            $fullExportMenu = ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => $gridColumns,
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
                        'filename' => 'facility_rating_export' . date("YmdHis")
            ]);
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
            echo "<p class='text-sm'>There are currently no facility ratings in the system</p>";
        }
        ?>
    </div>
</div>
