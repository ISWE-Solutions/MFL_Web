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
                            <div class="col-lg-6">
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
                                            'attribute' => 'province_approval_status',
                                            'format' => 'raw',
                                            'value' => function($model) {
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
                                            'attribute' => 'approver_comments',
                                        ],
                                        [
                                            'label' => 'Date verified',
                                            'value' => function($model) {
                                                return $model->date_verified;
                                            }
                                        ],
                                    ],
                                ])
                                ?>

                            </div>
                            <div class="col-lg-6">
                                <div class="col-lg-6">
                                    <p class=" text-center">Take action by clicking the button below</p>
                                    <?php
                                    //  $model1 = new \backend\models\Facility();
                                    $form = ActiveForm::begin(['action' => 'approve-facility-national?id=' . $model->id,])
                                    ?>
                                    <?php
                                    echo $form->field($model, 'national_approval_status')
                                            ->dropDownList(
                                                    [1 => "Accept & make facility active", 2 => "Send back for more information"], ['custom' => true, 'prompt' => 'Select Action', 'required' => true]
                                    );

                                    echo $form->field($model, 'approver_comments', ['enableAjaxValidation' => true])->textarea(['rows' => 5, 'placeholder' =>
                                        'Enter any comments'])->label("Comments ");
                                    ?>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <?= Html::submitButton('Approve facility', ['class' => 'btn btn-success btn-sm']) ?>
                                    <?php ActiveForm::end() ?>
                                </div>
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



