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
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model backend\models\MFLFacility */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Facilities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$query_service = backend\models\MFLFacilityServices::find()->where(['facility_id' => $model->id]);
$facility_services = new ActiveDataProvider([
    'query' => $query_service,
        ]);
$facility_services->pagination = ['pageSize' => 15];
$facility_services->setSort([
    'attributes' => [
        'service_area_id' => [
            'desc' => ['service_area_id' => SORT_ASC],
            'default' => SORT_ASC
        ],
    ],
    'defaultOrder' => [
        'service_area_id' => SORT_ASC
    ]
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

$user_type = Yii::$app->user->identity->user_type;
$district_user_district_id = "";
$province_user_province_id = "";

if ($user_type == "District") {
    $district_user_district_id = Yii::$app->user->identity->district_id;
}

if ($user_type == "Province") {
    $province_user_province_id = Yii::$app->user->identity->province_id;
}

$query_foh = backend\models\MFLFacilityOperatingHours::find()->where(['facility_id' => $model->id]);
$facility_operating_hours = new ActiveDataProvider([
    'query' => $query_foh,
        ]);
?>
<div class="card card-primary card-outline">
    <div class="card-header border-transparent">
        <h3 class="card-title">
            <?php
            if (User::userIsAllowedTo('Manage facilities')) {
                if ($user_type == "District" && in_array($model->province_approval_status, [0, 2])) {
                    if (!empty($district_user_district_id) && $district_user_district_id == $model->district_id) {
                        echo Html::a(
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
                }

                if ($user_type == "Province" && in_array($model->province_approval_status, [0, 2])) {
                    $distric_model = backend\models\Districts::findOne($model->district_id);
                    if (!empty($distric_model) && $distric_model->province_id == $province_user_province_id) {
                        echo Html::a(
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
                }

                if ($user_type == "National") {
                    echo Html::a(
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
            }
            if (User::userIsAllowedTo('Remove facility') && $model->ownership_type == 1) {
                if (in_array($model->province_approval_status, [0, 2])) {
                    echo Html::a(
                            '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                        'title' => 'Remove facility',
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
            }
            ?>
        </h3>

        <div class="card-tools">
            <table style="margin-top: 0px;">
                <tr><td class="text-sm">Average Facility rating: <?= $rating ?> </td><td>
                        <?php
                        echo StarRating::widget([
                            'name' => 'facility_rating',
                            'value' => round($rating),
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
                        <a class="nav-link" id="contact-tab" data-toggle="pill" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="location-tab" data-toggle="pill" href="#location" role="tab" aria-controls="location" aria-selected="false">Location</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="operating-hours-tab" data-toggle="pill" href="#operating-hours" role="tab" aria-controls="operating-hours" aria-selected="false">Operating hours</a>
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
                                        'id',
                                        [
                                            'enableSorting' => true,
                                            'attribute' => 'name',
                                            'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                            'filterWidgetOptions' => [
                                                'pluginOptions' => ['allowClear' => true],
                                            ],
                                            'filter' => \backend\models\Facility::getNames(),
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
                                                $name = backend\models\FacilityOwnership::findOne(["shared_id" => $model->ownership]);
                                                return !empty($name) ? $name->name : "";
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
                                                $status_arr = [1 => "Mobile", 2 => "Fixed", 3 => "telemedicine"];
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
                                            'value' => function ($model) {
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
                                                        if ($model->province_approval_status === 2 && $model->national_approval_status == 2) {
                                                            $str = "<span class='badge badge-pill badge-danger'> "
                                                                    . "<i class='fas fa-times'></i> Rejected at national level,need more infor!<br> See approval comments";
                                                        }
                                                        if ($model->province_approval_status === 2 && $model->national_approval_status == 0) {
                                                            $str = "<span class='badge badge-pill badge-danger'> "
                                                                    . "<i class='fas fa-times'></i> Rejected at province level,need more infor!<br> See approval comments";
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
                                            'value' => function ($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->created_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'label' => 'Updated by',
                                            'value' => function ($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->updated_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'label' => 'Updated at',
                                            'value' => function ($model) {
                                                return $model->date_updated;
                                            }
                                        ],
                                        [
                                            'label' => 'Created at',
                                            'value' => function ($model) {
                                                return $model->date_created;
                                            }
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'label' => 'Verified by',
                                            'value' => function ($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->verified_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'attribute' => 'province_approval_status',
                                            'format' => 'raw',
                                            'value' => function ($model) {
                                                if ($model->province_approval_status === 1) {
                                                    return "<span class='badge badge-pill badge-success'> "
                                                            . "<i class='ti-check'></i> Approved</span>";
                                                } elseif ($model->province_approval_status === 2) {
                                                    return "<span class='badge badge-pill badge-danger'> "
                                                            . "<i class='ti-times'></i> Rejected</span>";
                                                } else {
                                                    return "<span class='badge badge-pill badge-dark'> "
                                                            . "<i class='fas fa-hourglass-half'></i> Pending</span>";
                                                }
                                            },
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'attribute' => 'verifier_comments',
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'label' => 'Date verified',
                                            'value' => function ($model) {
                                                return $model->date_verified;
                                            }
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'label' => 'Approved by',
                                            'value' => function ($model) {
                                                $user = \backend\models\User::findOne(['id' => $model->approved_by]);
                                                return !empty($user) ? $user->email : "";
                                            }
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'attribute' => 'national_approval_status',
                                            'format' => 'raw',
                                            'value' => function ($model) {
                                                if ($model->national_approval_status === 1) {
                                                    return "<span class='badge badge-pill badge-success'> "
                                                            . "<i class='ti-check'></i> Approved</span>";
                                                } elseif ($model->national_approval_status === 2) {
                                                    return "<span class='badge badge-pill badge-danger'> "
                                                            . "<i class='ti-times'></i> Rejected</span>";
                                                } else {
                                                    return "<span class='badge badge-pill badge-dark'> "
                                                            . "<i class='fas fa-hourglass-half'></i> Pending</span>";
                                                }
                                            },
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'attribute' => 'approver_comments',
                                        ],
                                        [
                                            'visible' => $model->ownership_type == 1 ? true : false,
                                            'label' => 'Date approved',
                                            'value' => function ($model) {
                                                return $model->date_approved;
                                            }
                                        ],
                                    ],
                                ])
                                ?>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact">
                        <?=
                        DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'mobile',
                                'phone',
                                'fax',
                                'email:email',
                                'postal_address',
                                'town',
                                'street',
                                'plot_no',
                                'physical_address',
                            ],
                        ])
                        ?>
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
                                if (empty($model->longitude) && empty($model->latitude)) {
                                    echo "<div class='alert alert-warning'>There are no location coordinates for facility:" . $model->name . "</div>";
                                } else {
                                    // $coordinate = json_decode($model->geom, true)['coordinates'];
                                    $coord = new LatLng(['lat' => $model->latitude, 'lng' => $model->longitude]);
                                    //$center = round(count($coord) / 2);
                                    //$center_coords = $coord;
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
                                            //  if ($model->status == 1) {
                                            $count1 = \backend\models\MFLFacilityServices::find()->where(['facility_id' => $model->id])->count();
                                            $count2 = \backend\models\FacilityService::find()->count();

                                            if ($count1 == $count2) {
                                                echo "<div class='alert alert-warning'>Facility already has all system services!</div>";
                                            } else {
                                                echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
                                                  return false;"><i class="fa fa-plus"></i> Add service</button>';
                                            }
                                            //  }
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
                                                    'attribute' => 'service_area_id',
                                                    'label' => 'Service area',
                                                    'group' => true,
                                                    'filter' => false,
                                                    'value' => function ($facility_services) {
                                                        $type_id = \backend\models\FacilityService::findOne($facility_services->service_id)->category_id;
                                                        $type = !empty($type_id) ? \backend\models\FacilityServicecategory::findOne($type_id)->name : "";
                                                        return $type;
                                                    },
                                                ],
                                                [
                                                    'attribute' => 'service_id',
                                                    'filter' => false,
                                                    'value' => function ($facility_services) {
                                                        return !empty($facility_services->service_id) ? \backend\models\FacilityService::findOne($facility_services->service_id)->name : "";
                                                    },
                                                ],
                                                ['class' => ActionColumn::className(),
                                                    'template' => '{delete}',
                                                    'buttons' => [
                                                        'delete' => function ($url, $facility_services) use ($model) {
                                                            if (User::userIsAllowedTo('Manage facilities')) {
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
                    <div class="tab-pane fade" id="operating-hours" role="tabpanel" aria-labelledby="operating-hours">
                        <div class="row"> 
                            <div class="col-md-8"> 
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <p>
                                        <?php
                                        if (User::userIsAllowedTo('Manage facilities') && !empty(\backend\models\Operatinghours::getList())) {
                                            echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addOperatingHourModal\').modal(); 
                                     return false;"><i class="fa fa-plus"></i> Add operating hour</button>';
                                        }
                                        ?>  
                                    </p>
                                    <?php
                                    if ($facility_operating_hours->getCount() > 0) {
                                        echo GridView::widget([
                                            'dataProvider' => $facility_operating_hours,
                                            //  'filterModel' => $searchModel,
                                            'condensed' => true,
                                            'responsive' => true,
                                            'hover' => true,
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                //'id',
                                                [
                                                    'attribute' => 'operatinghours_id',
                                                    'filter' => false,
                                                    'value' => function ($facility_operating_hours) {
                                                        $name = !empty($facility_operating_hours->operatinghours_id) ? \backend\models\Operatinghours::findOne($facility_operating_hours->operatinghours_id)->name : "";
                                                        return $name;
                                                    },
                                                ],
                                                ['class' => ActionColumn::className(),
                                                    'template' => '{delete}',
                                                    'buttons' => [
                                                        'delete' => function ($url, $facility_operating_hours) {
                                                            if (User::userIsAllowedTo('Manage facilities')) {
                                                                return Html::a(
                                                                                '<span class="fa fa-trash"></span>', ['/facilities/delete-operatinghour', 'id' => $facility_operating_hours->id], [
                                                                            'title' => 'Delete',
                                                                            'data-toggle' => 'tooltip',
                                                                            'data-placement' => 'top',
                                                                            'data' => [
                                                                                'confirm' => 'Are you sure you want to remove operating hour from this facility?',
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
                                        echo "No operating hours have been set for this facility!";
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Services for facility: <?= $model->name ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <h4>Instructions</h4>
                        <ol>
                            <li>Fields marked with <span style="color: red;">*</span> are required</li>
                            <li>You can select multiple services at the same time</li>
                            <li>The system will only show the services which have not been added to the facility</li>
                        </ol>
                    </div>
                    <div class="col-lg-12">
                        <?php
                        $service_model = new backend\models\MFLFacilityServices();
                        $form1 = ActiveForm::begin([
                                    'action' => 'services',
                        ]);

//                        echo
//                                $form1->field($service_model, 'service_area_id')
//                                ->dropDownList(
//                                        \backend\models\FacilityServicecategory::getList(), ['id' => 'dist_id', 'custom' => true, 'prompt' => 'Please select service area', 'required' => true]
//                        );
//                        echo $form1->field($service_model, 'service_id')->widget(DepDrop::classname(), [
//                            'options' => ['id' => 'constituency_id', 'custom' => true,],
//                            'type' => DepDrop::TYPE_SELECT2,
//                            'pluginOptions' => [
//                                'depends' => ['dist_id'],
//                                'initialize' => $service_model->isNewRecord ? false : true,
//                                'placeholder' => 'Please select a service',
//                                'url' => yii\helpers\Url::to(['/facility-service/services']),
//                                'params' => ['selected_id2'],
//                                'loadingText' => 'Loading services....',
//                            ]
//                        ]);

                        echo $form1->field($service_model, 'service_id')->widget(kartik\select2\Select2::classname(), [
                            'data' => \backend\models\MFLFacilityServices::getServices($model->id),
                            'name' => 'kv_theme_classic_1',
                            'options' => ['placeholder' => 'Select a services ....', 'multiple' => true],
                            'theme' => kartik\select2\Select2::THEME_DEFAULT,
                            //'size' => kartik\select2\Select2::SMALL,
                            'pluginOptions' => [
                                'allowClear' => true,
                                'tags' => true,
                            // 'tokenSeparators' => [','],
                            ],
                        ])->label("Services")->hint("Select multiple services at the same time");
                        ?>
                        <?=
                        $form1->field($service_model, 'facility_id')->hiddenInput(['value' => $model->id, 'id' => 'selected_id2'])->label(false);
                        ?>

                    </div>

                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::submitButton('Save services', ['class' => 'btn btn-primary btn-sm']) ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade card-primary card-outline" id="addOperatingHourModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Operating hour for facility: <?= $model->name ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <?php
                        $ophour_model = new backend\models\MFLFacilityOperatingHours();
                        $form = ActiveForm::begin([
                                    'action' => 'operatinghour',
                                ])
                        ?>
                        <?=
                        $form->field($ophour_model, 'facility_id')->hiddenInput(['value' => $model->id])->label(false);
                        ?>
                        <?=
                                $form->field($ophour_model, 'operatinghours_id', ['enableAjaxValidation' => true])
                                ->dropDownList(
                                        \backend\models\Operatinghours::getList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Select operating hour', 'required' => true]
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
                <?= Html::submitButton('Add Operating hour', ['class' => 'btn btn-primary btn-sm']) ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

