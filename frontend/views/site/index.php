<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\Wards;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use backend\models\Facility;
use backend\models\Districts;
use backend\models\Provinces;
use dosamigos\google\maps\Map;
use backend\models\Constituency;
use backend\models\Facilitytype;
use backend\models\LocationType;
use dosamigos\google\maps\LatLng;
use backend\models\Operationstatus;
use backend\models\FacilityOwnership;
use dosamigos\google\maps\overlays\Marker;
use dosamigos\google\maps\overlays\Polygon;
use dosamigos\google\maps\overlays\InfoWindow;

$this->title = 'Home';
$this->params['breadcrumbs'][] = $this->title;

$connection = Yii::$app->getDb();
//Get all provinces data
$provinces_model = Provinces::find()
    ->cache(Yii::$app->params['cache_duration'])
    ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
    ->all();
//get facility types
$facility_types_model = Facilitytype::find()->cache(Yii::$app->params['cache_duration'])->all();
$facility_ownership_model = FacilityOwnership::find()->cache(Yii::$app->params['cache_duration'])->all();
$facility_model = "";
$area = "Province";
$filter_str = "<strong>Filters: </strong> ";

//Show the filter parameters
if (isset($_GET['Facility']) && (!empty($dataProvider) && $dataProvider->getTotalCount() > 0)) {
    if (
        !empty($_GET['Facility']['province_id']) ||
        !empty($_GET['Facility']['constituency_id']) ||
        !empty($_GET['Facility']['ward_id']) ||
        !empty($_GET['Facility']['ownership']) ||
        !empty($_GET['Facility']['type']) ||
        !empty($_GET['Facility']['name']) ||
        !empty($_GET['Facility']['district_id'])
    ) {

        $_province = !empty($_GET['Facility']['province_id']) ? Provinces::findOne($_GET['Facility']['province_id'])->name : "";
        $_district = !empty($_GET['Facility']['district_id']) ? Districts::findOne($_GET['Facility']['district_id'])->name : "";
        $constituency = !empty($_GET['Facility']['constituency_id']) ? Constituency::findOne($_GET['Facility']['constituency_id'])->name : "";
        $ward = !empty($_GET['Facility']['ward_id']) ? Wards::findOne($_GET['Facility']['ward_id'])->name : "";
        $_facility_type = !empty($_GET['Facility']['type']) ? Facilitytype::findOne($_GET['Facility']['type'])->name : "";
        $_ownership = !empty($_GET['Facility']['ownership']) ? FacilityOwnership::findOne($_GET['Facility']['ownership'])->name : "";
        $prov_str = !empty($_province) ?  $_province . " province | " : "";
        $dist_str = !empty($_district) ? $_district . " district | " : "";
        $cons_str = !empty($constituency) ? $constituency . " constituency | " : "";
        $ward_str = !empty($ward) ? $ward . " ward | " : "";
        $fac_str = !empty($_facility_type) ? " Facility type: " . $_facility_type . " | " : "";
        $own_str = !empty($_ownership) ? "Ownship: " . $_ownership : "";
        $name_str = !empty($_GET['Facility']['name']) ? " Facility name: " . $_GET['Facility']['name'] . " | " : "";
        $filter_str .= "<i>" . $name_str . "</i><i>" . $prov_str . "</i><i>" . $dist_str . "</i><i> $cons_str </i><i>$ward_str</i><i>"
            . $fac_str . "</i><i>" . $own_str . "</I>";
        //echo "<p class='text-sm'>$filter_str</p>";

        $area = !empty($_district) && !empty($_GET['Facility']['district_id']) ? $_district . " district Constituencies" : $area;
        $area = empty($_GET['Facility']['district_id']) && !empty($_GET['Facility']['province_id']) ? $_province . " province districts" : $area;
    }
}
if (isset($_GET['Facility']) && (!empty($dataProvider) && $dataProvider->getTotalCount() == 0)) {
    echo "<p class='text-sm'>No filter results were found!</p>";
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- <p>Use the form below to perform a filter</p> -->
        <div class="col-lg-12 text-sm">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <p class="card-title text-sm">
                        <?= $filter_str ?>
                        <!-- Filters by <strong>name, type and ownership</strong> are only applicable to the <strong>Map</strong>. -->
                        <!-- Filter by <strong>ward/Constituency</strong> is not applicable to the <strong>second graph</strong> -->
                    </p>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <div class="card-body">
                    <?php
                    echo $this->render('_form', ['model' => $Facility_model]);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number"> <?= $public_count_active ?></span>

                    <div class="progress">
                        <div class="progress-bar" style="width:100%"></div>
                    </div>
                    <span class="progress-description text-sm">
                        Public Operating Facilities
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-lg-4">
            <div class="info-box bg-gradient-indigo">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>

                <div class="info-box-content">
                    <span class="info-box-number"><?= $_private_count_active ?></span>

                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description text-sm">
                        Private Operating Facilities
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-lg-4">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-hospital"></i></span>

                <div class="info-box-content">
                    <span class="info-box-number"><?= $totalOperatingFacilities ?></span>

                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description text-sm">
                        Total Operating Facilities
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Pie</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#sales-chart" data-toggle="tab">Bar</a>
                            </li>
                        </ul>
                    </div>
                </div><!-- /.card-header -->
                <div class="card-body">
                    <div class="tab-content p-0">
                        <!-- Morris chart - Sales -->
                        <div class="chart tab-pane active" id="revenue-chart" style="height: auto;">
                            <?=
                            \dosamigos\highcharts\HighCharts::widget([
                                'clientOptions' => [
                                    'chart' => [
                                        'plotBackgroundColor' => null,
                                        'plotBorderWidth' => null,
                                        'plotShadow' => false,
                                        'type' => 'pie',
                                    ],
                                    'title' => [
                                        'text' => 'Operating Facilities by type: ' . $area
                                    ],
                                    'tooltip' => [
                                        'pointFormat' => '{series.name}: <b>{point.percentage:.1f}%</b>'
                                    ],
                                    [
                                        'accessibility' => [
                                            'point' => [
                                                'valueSuffix' => '%'
                                            ]
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'pie' => [
                                            'allowPointSelect' => true,
                                            'cursor' => 'pointer',
                                            'size' => '70%',
                                            'height' => '100%',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'style' => [
                                                    'fontSize' => 5
                                                ],
                                                'format' => '{point.name}',
                                                //'format' => '{point.name}: {point.percentage:.1f} %',
                                            ],
                                            'showInLegend' => false
                                        ]
                                    ],
                                    'series' =>
                                    $pie_series
                                ]
                            ]);
                            ?>
                        </div>
                        <div class="chart tab-pane" id="sales-chart" style="position: relative; height: auto;">
                            <?=
                            \dosamigos\highcharts\HighCharts::widget([
                                'clientOptions' => [
                                    'chart' => [
                                        'plotBackgroundColor' => null,
                                        'plotBorderWidth' => null,
                                        'plotShadow' => false,
                                        'type' => 'column'
                                    ],
                                    'legend' => [
                                        'enabled' => false
                                    ],
                                    'plotOptions' => [
                                        'column' => [
                                            'allowPointSelect' => true,
                                            'colorByPoint' => true,
                                            'cursor' => 'pointer',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'style' => [
                                                    'fontSize' => 5
                                                ],
                                            ],
                                        ]
                                    ],
                                    'title' => [
                                        'text' => 'Operating Facilities by type: ' . $area
                                    ],
                                    'xAxis' => [
                                        'categories' => $labels
                                    ],
                                    'yAxis' => [
                                        'title' => [
                                            'text' => 'Count'
                                        ]
                                    ],
                                    'series' => $column_series
                                ]
                            ]);
                            ?>
                        </div>
                    </div>
                </div><!-- /.card-body -->
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="#bar-chart" data-toggle="tab">Bar</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="#pie-chart" data-toggle="tab">Pie</a>
                            </li>

                        </ul>
                    </div>
                </div><!-- /.card-header -->
                <div class="card-body">
                    <div class="tab-content p-0">
                        <!-- Morris chart - Sales -->
                        <div class="chart tab-pane " id="pie-chart" style="height: auto;">
                            <?=
                            \dosamigos\highcharts\HighCharts::widget([
                                'clientOptions' => [
                                    'chart' => [
                                        'plotBackgroundColor' => null,
                                        'plotBorderWidth' => null,
                                        'plotShadow' => false,
                                        'type' => 'pie',
                                    ],
                                    'title' => [
                                        'text' => 'Operating Facilities ' . $area
                                    ],
                                    'tooltip' => [
                                        'pointFormat' => '{series.name}: <b>{point.percentage:.1f}%</b>'
                                    ],
                                    [
                                        'accessibility' => [
                                            'point' => [
                                                'valueSuffix' => '%'
                                            ]
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'pie' => [
                                            'allowPointSelect' => true,
                                            'cursor' => 'pointer',
                                            'size' => '70%',
                                            'height' => '100%',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'style' => [
                                                    'fontSize' => 5
                                                ],
                                                'format' => '{point.name}',
                                                //'format' => '{point.name}: {point.percentage:.1f} %',
                                            ],
                                            'showInLegend' => false
                                        ]
                                    ],
                                    'series' =>
                                    $pie_series1
                                ]
                            ]);
                            ?>
                        </div>
                        <div class="chart tab-pane active" id="bar-chart" style="position: relative; height: auto;">
                            <?=
                            \dosamigos\highcharts\HighCharts::widget([
                                'clientOptions' => [
                                    'chart' => [
                                        'plotBackgroundColor' => null,
                                        'plotBorderWidth' => null,
                                        'plotShadow' => false,
                                        'type' => 'column'
                                    ],
                                    'legend' => [
                                        'enabled' => false
                                    ],
                                    'plotOptions' => [
                                        'column' => [
                                            'allowPointSelect' => true,
                                            'colorByPoint' => true,
                                            'cursor' => 'pointer',
                                            'dataLabels' => [
                                                'enabled' => true,
                                                'style' => [
                                                    'fontSize' => 5
                                                ],
                                            ],
                                        ]
                                    ],
                                    'title' => [
                                        'text' => 'Operating Facilities ' . $area
                                    ],
                                    'xAxis' => [
                                        'categories' => $labels1
                                    ],
                                    'yAxis' => [
                                        'title' => [
                                            'text' => 'Count'
                                        ]
                                    ],
                                    'series' => $column_series1
                                ]
                            ]);
                            ?>
                        </div>
                    </div>
                </div><!-- /.card-body -->
            </div>
        </div>



        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <p></p>
                            <?php

                            $counter = 0;
                            $colors = ["#ed5151", "#149ece", "#a7c636", "#9e559c", "#fc921f", "purple", "#006D2C", ' #2a4858', '#fafa6e', 'lime'];

                            //Default map settings
                            $coord = new LatLng([
                                'lat' => -13.445529118205,
                                'lng' => 28.983639375
                            ]);

                            $map = new Map([
                                'center' => $coord,
                                'zoom' => Yii::$app->params['polygon_zoom'],
                                'width' => '100%', 'height' => 500,
                            ]);

                            //We set the map settings based on the province/distric search
                            //1. By province
                            if (
                                isset($_GET['Facility']['province_id']) &&
                                !empty($_GET['Facility']['province_id'])
                            ) {
                                $prov_model = Provinces::find()->cache(Yii::$app->params['cache_duration'])
                                    ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                    ->where(["id" => $_GET['Facility']['province_id']])->one();

                                if (!empty($prov_model) && !empty($prov_model->geom)) {
                                    $coords = Provinces::getCoordinates(json_decode($prov_model->geom, true)['coordinates']);
                                    $coord = json_decode($prov_model->geom, true)['coordinates'][0][0];
                                    $center = round(count($coord) / 2);
                                    $center_coords = $coord[$center];
                                    if (!empty($center_coords)) {
                                        $coord = new LatLng([
                                            'lat' => $center_coords[1],
                                            'lng' => $center_coords[0]
                                        ]);
                                    } else {
                                        $coord = new LatLng([
                                            'lat' => Yii::$app->params['center_lat'],
                                            'lng' => Yii::$app->params['center_lng']
                                        ]);
                                    }
                                    $map = new Map([
                                        'center' => $coord,
                                        'zoom' => 8,
                                        'width' => '100%', 'height' => 500,
                                    ]);
                                }
                            }

                            //2. By district
                            if (
                                isset($_GET['Facility']['district_id']) &&
                                !empty($_GET['Facility']['district_id'])
                            ) {
                                $prov_model = Districts::find()->cache(Yii::$app->params['cache_duration'])
                                    ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                    ->where(["id" => $_GET['Facility']['district_id']])->one();

                                if (!empty($prov_model) && !empty($prov_model->geom)) {
                                    $coords = Districts::getCoordinates(json_decode($prov_model->geom, true)['coordinates']);
                                    $coord = json_decode($prov_model->geom, true)['coordinates'][0][0];
                                    $center = round(count($coord) / 2);
                                    $center_coords = $coord[$center];
                                    if (!empty($center_coords)) {
                                        $coord = new LatLng([
                                            'lat' => $center_coords[1],
                                            'lng' => $center_coords[0]
                                        ]);
                                    } else {
                                        $coord = new LatLng([
                                            'lat' => Yii::$app->params['center_lat'],
                                            'lng' => Yii::$app->params['center_lng']
                                        ]);
                                    }
                                    $map = new Map([
                                        'center' => $coord,
                                        'zoom' => 10,
                                        'width' => '100%', 'height' => 500,
                                    ]);
                                }
                            }

                            //3. By constituency
                            if (
                                isset($_GET['Facility']['constituency_id']) &&
                                !empty($_GET['Facility']['constituency_id'])
                            ) {
                                $_model = Constituency::find()
                                    ->cache(Yii::$app->params['cache_duration'])
                                    ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                    ->where(["id" => $_GET['Facility']['constituency_id']])->one();

                                if (!empty($_model) && !empty($_model->geom)) {
                                    $coords = Constituency::getCoordinates(json_decode($_model->geom, true)['coordinates']);
                                    $coord = json_decode($_model->geom, true)['coordinates'][0][0];
                                    $center = round(count($coord) / 2);
                                    $center_coords = $coord[$center];
                                    if (!empty($center_coords)) {
                                        $coord = new LatLng([
                                            'lat' => $center_coords[1],
                                            'lng' => $center_coords[0]
                                        ]);
                                    } else {
                                        $coord = new LatLng([
                                            'lat' => Yii::$app->params['center_lat'],
                                            'lng' => Yii::$app->params['center_lng']
                                        ]);
                                    }
                                    $map = new Map([
                                        'center' => $coord,
                                        'zoom' => 10,
                                        'width' => '100%', 'height' => 500,
                                    ]);
                                }
                            }

                            //4. By ward
                            if (
                                isset($_GET['Facility']['ward_id']) &&
                                !empty($_GET['Facility']['ward_id'])
                            ) {
                                $_model = Wards::find()
                                    ->cache(Yii::$app->params['cache_duration'])
                                    ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                    ->where(["id" => $_GET['Facility']['ward_id']])->one();

                                if (!empty($_model) && !empty($_model->geom)) {
                                    $coords = Wards::getCoordinates(json_decode($_model->geom, true)['coordinates']);
                                    $coord = json_decode($_model->geom, true)['coordinates'][0][0];
                                    $center = round(count($coord) / 2);
                                    $center_coords = $coord[$center];
                                    if (!empty($center_coords)) {
                                        $coord = new LatLng([
                                            'lat' => $center_coords[1],
                                            'lng' => $center_coords[0]
                                        ]);
                                    } else {
                                        $coord = new LatLng([
                                            'lat' => Yii::$app->params['center_lat'],
                                            'lng' => Yii::$app->params['center_lng']
                                        ]);
                                    }
                                    $map = new Map([
                                        'center' => $coord,
                                        'zoom' => 10,
                                        'width' => '100%', 'height' => 500,
                                    ]);
                                }
                            }

                            if ($dataProvider !== "" && $dataProvider->getTotalCount() > 0) {
                                $dataProvider_models = $dataProvider->getModels();
                                foreach ($provinces_model as $model) {
                                    if (!empty($model->geom)) {
                                        //We pick a color for each province polygon
                                        $stroke_color = $colors[$counter];
                                        $counter++;

                                        $coords = Provinces::getCoordinates(json_decode($model->geom, true)['coordinates']);

                                        $polygon = new Polygon([
                                            'paths' => $coords,
                                            'strokeColor' => $stroke_color,
                                            'strokeOpacity' => 0.8,
                                            'strokeWeight' => 2,
                                            'fillColor' => $stroke_color,
                                            'fillOpacity' => 0.35,
                                        ]);
                                        //We get all districts in the province
                                        $districts_model = Districts::find()->cache(Yii::$app->params['cache_duration'])
                                            ->where(['province_id' => $model->id])
                                            ->all();
                                        //We create an array to be used to get the facilities in the province
                                        $district_ids = [];
                                        if (!empty($districts_model)) {
                                            foreach ($districts_model as $id) {
                                                array_push($district_ids, $id['id']);
                                            }
                                        }

                                        //We now get the facilities in the province
                                        $facilities_counts = Facility::find()
                                            ->cache(Yii::$app->params['cache_duration'])
                                            ->select(["COUNT(*) as count", "type"])
                                            ->where(['operational_status' => $opstatus_id])
                                            ->andWhere(['IN', 'district_id', $district_ids])
                                            ->groupBy(['type'])
                                            ->createCommand()->queryAll();

                                        //We build the window string
                                        $type_str = "";
                                        foreach ($facilities_counts as $f_model) {
                                            $facility_type = !empty($f_model['type']) ? backend\models\Facilitytype::findOne($f_model['type'])->name : "";
                                            $type_str .= $facility_type . ":<b>" . $f_model['count'] . "</b><br>";
                                        }
                                        $polygon->attachInfoWindow(new InfoWindow([
                                            'content' => '<p><strong><span class="text-center">' . $model->name . ' Province Facility types</span></strong><hr>'
                                                . $type_str . '</p>'
                                        ]));

                                        $map->addOverlay($polygon);

                                        foreach ($dataProvider_models as $_model) {
                                            // var_dump($_model);
                                            if (!empty($_model->latitude) && !empty($_model->longitude)) {
                                                $coord = new LatLng(['lat' => $_model->latitude, 'lng' => $_model->longitude]);
                                                $marker = new Marker([
                                                    'position' => $coord,
                                                    'title' => $_model->name,
                                                    'icon' => \yii\helpers\Url::to('@web/img/map_icon.png')
                                                ]);

                                                $constituency = !empty($_model->constituency_id) ? Constituency::findOne($_model->constituency_id)->name : "";
                                                $ward = !empty($_model->ward_id) ? Wards::findOne($_model->ward_id)->name : "";
                                                $loc_type = !empty($_model->location) ? LocationType::findOne($_model->location)->name : "";
                                                $type = !empty($_model->type) ? Facilitytype::findOne($_model->type)->name : "";
                                                $ownership = !empty($_model->ownership) ? FacilityOwnership::findOne($_model->ownership)->name : "";
                                                $operation_status = !empty($_model->operational_status) ? Operationstatus::findOne($_model->operational_status)->name : "";
                                                $type_str = "";
                                                $type_str .= "<b>Province: </b>" . Provinces::findOne(Districts::findOne($_model->district_id)->province_id)->name . "<br>";
                                                $type_str .= "<b>District: </b>" . Districts::findOne($_model->district_id)->name . "<br>";
                                                $type_str .= "<b>Constituency: </b>" . $constituency . "<br>";
                                                $type_str .= "<b>Ward: </b>" . $ward . "<br>";
                                                $type_str .= "<b>Location type: </b>" . $loc_type . "<br>";
                                                $type_str .= "<b>Facility type: </b>" . $type . "<br>";
                                                $type_str .= "<b>Ownership: </b>" . $ownership . "<br>";
                                                $type_str .= "<b>Operation status: </b><span style='color:green;'>" . $operation_status . "</span><br>";
                                                $type_str .= Html::a('View more details', ['/facility/view', 'id' => $_model->id], ["class" => "text-sm"]);
                                                $marker->attachInfoWindow(
                                                    new InfoWindow([
                                                        'content' => '<p><strong><span class="text-center">' . $_model->name . '</span></strong><hr>'
                                                            . $type_str . '</p>'
                                                    ])
                                                );

                                                $map->addOverlay($marker);
                                            }
                                        }
                                    }
                                }

                                echo $map->display();
                            } else {

                                foreach ($provinces_model as $model) {
                                    if (!empty($model->geom)) {
                                        //We pick a color for each province polygon
                                        $stroke_color = $colors[$counter];
                                        $counter++;

                                        $coords = Provinces::getCoordinates(json_decode($model->geom, true)['coordinates']);

                                        $polygon = new Polygon([
                                            'paths' => $coords,
                                            'strokeColor' => $stroke_color,
                                            'strokeOpacity' => 0.8,
                                            'strokeWeight' => 2,
                                            'fillColor' => $stroke_color,
                                            'fillOpacity' => 0.35,
                                        ]);

                                        //We get all districts in the province
                                        $districts_model = Districts::find()->cache(Yii::$app->params['cache_duration'])
                                            ->where(['province_id' => $model->id])
                                            ->all();
                                        //We create an array to be used to get the facilities in the province
                                        $district_ids = [];
                                        if (!empty($districts_model)) {
                                            foreach ($districts_model as $id) {
                                                array_push($district_ids, $id['id']);
                                            }
                                        }

                                        //We now get the facilities in the province
                                        $facilities_counts = Facility::find()
                                            ->cache(Yii::$app->params['cache_duration'])
                                            ->select(["COUNT(*) as count", "type"])
                                            ->where(['operational_status' => $opstatus_id])
                                            ->andWhere(['IN', 'district_id', $district_ids])
                                            ->groupBy(['type'])
                                            ->createCommand()->queryAll();

                                        //We build the window string
                                        $type_str = "";
                                        foreach ($facilities_counts as $f_model) {
                                            $facility_type = !empty($f_model['type']) ? Facilitytype::findOne($f_model['type'])->name : "";
                                            $type_str .= $facility_type . ":<b>" . $f_model['count'] . "</b><br>";
                                        }
                                        $polygon->attachInfoWindow(new InfoWindow([
                                            'content' => '<p><strong><span class="text-center">' . $model->name . ' Province Facility types</span></strong><hr>'
                                                . $type_str . '</p>'
                                        ]));

                                        $map->addOverlay($polygon);
                                    }
                                }

                                echo $map->display();
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h5 class="card-title">Facility Types</h5>
                    <p class="card-text">
                    </p>
                    <?php
                    if (!empty($facility_types_model)) {
                        foreach ($facility_types_model as $_typeModel) {
                            echo Html::a($_typeModel->name, ['/facility/index', 'type' => $_typeModel->id], ["class" => "card-link text-sm"]);
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h5 class="card-title">Facility ownership</h5>

                    <p class="card-text">
                    </p>
                    <?php
                    if (!empty($facility_ownership_model)) {
                        foreach ($facility_ownership_model as $_ownershipModel) {
                            echo Html::a($_ownershipModel->name, ['/facility/index', 'ownership' => $_ownershipModel->id], ["class" => "card-link text-sm"]);
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>