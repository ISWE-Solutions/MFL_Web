<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\User;
use kartik\grid\GridView;
use dosamigos\google\maps\LatLng;
use dosamigos\google\maps\overlays\InfoWindow;
use dosamigos\google\maps\overlays\Marker;
use dosamigos\google\maps\Map;
use kartik\form\ActiveForm;
use \yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use kartik\widgets\StarRating;

/* @var $this yii\web\View */
/* @var $model backend\models\MFLFacility */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Facilities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$query_service = backend\models\MFLFacilityServices::find()->where(['facility_id' => $model->id]);
$facility_services = new ActiveDataProvider([
    'query' => $query_service,
        ]);


\yii\web\YiiAsset::register($this);


$facility_rate_count = \backend\models\MFLFacilityRatings::find()
                ->cache(Yii::$app->params['cache_duration'])
                ->where(['facility_id' => $model->id])->count();
$facility_rates_sum = \backend\models\MFLFacilityRatings::find()
        ->cache(Yii::$app->params['cache_duration'])
        ->where(['facility_id' => $model->id])
        ->sum('rate_value');
$rating = !empty($facility_rate_count) && !empty($facility_rates_sum) ? $facility_rates_sum / $facility_rate_count : 0;
$rating_model = new \backend\models\MFLFacilityRatings();
$rate_type_model = \backend\models\MFLFacilityRateTypes::find()
        ->cache(Yii::$app->params['cache_duration'])
        ->all();
?>
<div class="card card-primary card-outline">
    <div class="card-header border-transparent">
        <h3 class="card-title">
            <?php
            if (User::userIsAllowedTo('Manage facilities') && $model->ownership_type == 1) {
                echo Html::a('<i class="fas fa-pencil-alt fa-2x"></i>', ['update', 'id' => $model->id], [
                    'title' => 'Update facility',
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip'
                ]);
            }
            if (User::userIsAllowedTo('Remove facility') && $model->status === 2) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo Html::a('<i class="fas fa-trash fa-2x"></i>', ['delete', 'id' => $model->id], [
                    'title' => 'Remove facility',
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete facility: ' . $model->name . '?<br>'
                        . 'Facility will only be removed if it is not being used by the system!',
                        'method' => 'post',
                    ],
                ]);
            }
            ?>
        </h3>

        <div class="card-tools">
            <table style="margin-top: 0px;">
                <tr><td class="text-sm">Average Facility rating: <?= $rating ?> </td><td>
                        <?php
                        echo StarRating::widget([
                            'name' => 'facility_rating',
                            'value' => $rating,
                            'pluginOptions' => [
                                'min' => 0,
                                'max' => 5,
                                'step' => 1,
                                'size' => 'xsm',
                                'showClear' => false,
                                'showCaption' => true,
                                'displayOnly' => true,
                                'starCaptions' => [
                                    0 => 'Not rated',
                                    1 => 'Very Poor',
                                    2 => 'Poor',
                                    3 => 'Average',
                                    4 => 'Good',
                                    5 => 'Very Good',
                                ],
                                'starCaptionClasses' => [
                                    0 => 'text-danger',
                                    1 => 'text-danger',
                                    2 => 'text-warning',
                                    3 => 'text-info',
                                    4 => 'text-primary',
                                    5 => 'text-success',
                                ],
                            ],
                        ]);
                        ?>

                    </td></tr>
            </table>
        </div>
    </div>
    <div class="card-body">

        <?php
        //This is a hack, just to use pjax for the delete confirm button
        $query = User::find()->where(['id' => '-2']);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);
        GridView::widget([
            'dataProvider' => $dataProvider,
        ]);
        ?>

        <div class="card card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="location-tab" data-toggle="pill" href="#location" role="tab" aria-controls="location" aria-selected="false">Location</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">Services</a>
                    </li>

                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
                    <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                        <div class="row">
                            <div class="col-lg-12">
                                <?=
                                DetailView::widget([
                                    'model' => $model,
                                    'attributes' => [
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
                                            'attribute' => 'ownership',
                                            'filterType' => GridView::FILTER_SELECT2,
                                            'filterWidgetOptions' => [
                                                'pluginOptions' => ['allowClear' => true],
                                            ],
                                            'filter' => \backend\models\FacilityOwnership::getList(),
                                            'filterInputOptions' => ['prompt' => 'Filter by ownership', 'class' => 'form-control', 'id' => null],
                                            'value' => function ($model) {
                                                $name = backend\models\FacilityOwnership::findOne($model->ownership)->name;
                                                return $name;
                                            },
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'operational_status',
                                            'filterType' => GridView::FILTER_SELECT2,
                                            'filterWidgetOptions' => [
                                                'pluginOptions' => ['allowClear' => true],
                                            ],
                                            'filter' => \backend\models\Operationstatus::getList(),
                                            'filterInputOptions' => ['prompt' => 'Filter by status', 'class' => 'form-control', 'id' => null],
                                            'value' => function ($model) {
                                                $name = backend\models\Operationstatus::findOne($model->operational_status)->name;
                                                return $name;
                                            },
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'mobility_status',
                                            'value' => function ($model) {
                                                $status_arr = [1 => "Fixed", 2 => "Mobile", 3 => "telemedicine"];
                                                return $status_arr[$model->mobility_status];
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
                                        'accesibility',
                                        'hims_code',
                                        'smartcare_code',
                                        'elmis_code',
                                        'hpcz_code',
                                        'disa_code',
                                        'catchment_population_head_count',
                                        'catchment_population_cso',
                                        'number_of_households',
                                        [
                                            'attribute' => 'status',
                                            'value' => function($model) {
                                                $str = "";
                                                if ($model->ownership_type == 1) {
                                                    if ($model->province_approval_status === 1 && $model->national_approval_status === 1) {
                                                        if ($model->status === 1) {
                                                            $str = "<span class='badge badge-pill badge-success'> "
                                                                    . "<i class='fa fa-check'></i> Active</span>";
                                                        }
                                                        if ($model->status === 0) {
                                                            $str = "<span class='badge badge-pill badge-danger'> "
                                                                    . "<i class='fa fa-times'></i> Inactive";
                                                        }
                                                    } else {
                                                        if ($model->province_approval_status === 0 && $model->national_approval_status === 0) {
                                                            $str = "<span class='badge badge-pill badge-dark'> "
                                                                    . "<i class='fas fa-hourglass-half'></i> Pending Provincial review";
                                                        }
                                                        if ($model->province_approval_status === 1 && $model->national_approval_status === 0) {
                                                            $str = "<span class='badge badge-pill badge-info'> "
                                                                    . "<i class='fas fa-hourglass-half'></i> Pending National approval";
                                                        }
                                                        if ($model->province_approval_status === 2) {
                                                            $str = "<span class='badge badge-pill badge-danger'> "
                                                                    . "<i class='fas fa-times'></i> Rejected,need more infor!<br> See approval comments";
                                                        }
                                                    }
                                                } else {
                                                    $str = "<span class='badge badge-pill badge-success'> "
                                                            . "<i class='fa fa-check'></i> Active</span>";
                                                }
                                                return $str;
                                            },
                                            'format' => 'raw',
                                        ],
                                        [
                                            'label' => 'Created by',
                                            'value' => function($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->created_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'label' => 'Updated by',
                                            'value' => function($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->updated_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'label' => 'Updated at',
                                            'value' => function($model) {
                                                return $model->date_updated;
                                            }
                                        ],
                                        [
                                            'label' => 'Created at',
                                            'value' => function($model) {
                                                return $model->date_created;
                                            }
                                        ],
                                        [
                                            'label' => 'Verified by',
                                            'value' => function($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->verified_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'attribute' => 'verifier_comments',
                                        ],
                                        [
                                            'label' => 'Date verified',
                                            'value' => function($model) {
                                                return $model->date_verified;
                                            }
                                        ],
                                        [
                                            'label' => 'Approved by',
                                            'value' => function($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->approved_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'attribute' => 'approver_comments',
                                        ],
                                        [
                                            'label' => 'Date approved',
                                            'value' => function($model) {
                                                return $model->date_approved;
                                            }
                                        ],
                                    ],
                                ])
                                ?>

                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="location" role="tabpanel" aria-labelledby="location">
                        <div class="row">
                            <div class="col-lg-6">
                                <?=
                                DetailView::widget([
                                    'model' => $model,
                                    'attributes' => [
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
                                            'label' => 'Latitude/Longitude',
                                            'value' => function ($model) {
                                                return $model->longitude . "/" . $model->latitude;
                                            },
                                        ],
                                    ],
                                ])
                                ?>

                            </div>
                            <div class="col-lg-6">
                                <?php
                                $coords = [];
                                $center_coords = [];
                                if (empty($model->geom)) {
                                    echo "<div class='alert alert-warning'>There are no location coordinates for facility:" . $model->name . "</div>";
                                } else {
                                    $coordinate = json_decode($model->geom, true)['coordinates'];
                                    $coord = new LatLng(['lat' => $coordinate[1], 'lng' => $coordinate[0]]);
                                    //$center = round(count($coord) / 2);
                                    $center_coords = $coord;
                                }
                                if (empty($coord)) {
                                    $coord = new LatLng([
                                        'lat' => Yii::$app->params['center_lat'],
                                        'lng' => Yii::$app->params['center_lng']
                                    ]);
                                }
                                $map = new Map([
                                    'center' => $coord,
                                    'streetViewControl' => false,
                                    'mapTypeControl' => true,
                                    'zoom' => 10,
                                    'width' => '100%',
                                    'height' => 500,
                                ]);
                                if (!empty($model->geom)) {
                                    $marker = new Marker([
                                        'position' => $coord,
                                        'title' => $model->name,
                                        'icon' => \yii\helpers\Url::to('@web/img/map_icon.png')
                                    ]);

                                    $marker->attachInfoWindow(
                                            new InfoWindow([
                                                'content' => '<p>' . $model->name . '</p>'
                                                    ])
                                    );

                                    $map->addOverlay($marker);
                                }
                                echo $map->display();
                                ?>

                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel" aria-labelledby="custom-tabs-one-profile-tab">
                        <div class="row"> 
                            <div class="col-md-8"> 
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <p>
                                        <?php
                                        if (User::userIsAllowedTo('Manage facilities') && !empty(\backend\models\FacilityService::getList())) {
                                            if ($model->status == 1) {
                                                echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
                                                  return false;"><i class="fa fa-plus"></i> Add service</button>';
                                            }
                                        }
                                        ?>
                                    </p>
                                    <?php
                                    if ($facility_services->getCount() > 0) {
                                        echo GridView::widget([
                                            'dataProvider' => $facility_services,
                                            //  'filterModel' => $searchModel,
                                            'condensed' => true,
                                            'responsive' => true,
                                            'hover' => true,
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                //'id',
                                                [
                                                    'attribute' => 'service_id',
                                                    'filter' => false,
                                                    'value' => function ($facility_services) {
                                                        return !empty($facility_services->service_id) ? \backend\models\FacilityService::findOne($facility_services->service_id)->name : "";
                                                        ;
                                                    },
                                                ],
                                                [
                                                    'label' => 'Service area',
                                                    'filter' => false,
                                                    'value' => function ($facility_services) {
                                                        $type_id = \backend\models\FacilityService::findOne($facility_services->service_id)->category_id;
                                                        $type = !empty($type_id) ? \backend\models\FacilityServicecategory::findOne($type_id)->name : "";
                                                        return $type;
                                                    },
                                                ],
                                                ['class' => ActionColumn::className(),
                                                    'template' => '{delete}',
                                                    'buttons' => [
                                                        'delete' => function ($url, $facility_services) {
                                                            if (User::userIsAllowedTo('Remove facility')) {
                                                                return Html::a(
                                                                                '<span class="fa fa-trash"></span>', ['/facilities/delete-service', 'id' => $facility_services->id], [
                                                                            'title' => 'Delete',
                                                                            'data-toggle' => 'tooltip',
                                                                            'data-placement' => 'top',
                                                                            'data' => [
                                                                                'confirm' => 'Are you sure you want to remove this service from this facility?',
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
                                            ],
                                        ]);
                                    } else {
                                        echo "No Facility services found!";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>



<div class="modal fade card-primary card-outline" id="addNewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Service for facility: <?= $model->name ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <?php
                        $service_model = new backend\models\MFLFacilityServices();
                        $form = ActiveForm::begin([
                                    'action' => 'services',
                                ])
                        ?>
                        <?=
                        $form->field($service_model, 'facility_id')->hiddenInput(['value' => $model->id])->label(false);
                        ?>
                        <?=
                                $form->field($service_model, 'service_id', ['enableAjaxValidation' => true])
                                ->dropDownList(
                                        \backend\models\FacilityService::getList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Select Service', 'required' => true]
                        );
                        ?>
                    </div>
                    <div class="col-lg-4">
                        <h4>Instructions</h4>
                        <ol>
                            <li>Fields marked with <span style="color: red;">*</span> are required</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::submitButton('Add service', ['class' => 'btn btn-primary btn-sm']) ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>



