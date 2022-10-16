<?php

use yii\helpers\Html;
use dosamigos\google\maps\LatLng;
use dosamigos\google\maps\overlays\InfoWindow;
use dosamigos\google\maps\overlays\Marker;
use dosamigos\google\maps\Map;
use dosamigos\google\maps\overlays\Polygon;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use yii\helpers\Url;

$this->title = 'Home';
$this->params['breadcrumbs'][] = $this->title;
/* @var $this yii\web\View */
$this->title = 'Home';
$connection = Yii::$app->getDb();
//Get all provinces data
$provinces_model = \backend\models\Provinces::find()
        ->cache(Yii::$app->params['cache_duration'])
        ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
        ->all();
//get facility types
$facility_types_model = \backend\models\Facilitytype::find()->cache(Yii::$app->params['cache_duration'])->all();
$facility_ownership_model = \backend\models\FacilityOwnership::find()->cache(Yii::$app->params['cache_duration'])->all();

/**
 * 
 * Data for Pie/Bar chart by Facility type
 * 
 */
$pie_series = [];
$column_series = [];
$data = [];
$data1 = [];
$labels = [];
$opstatus_id = "";
$facility_model = "";
//We assume facility operation status name "Operational" 
//will never be renamed/deleted otherwise the system breaks
$operation_status_model = \backend\models\Operationstatus::findOne(['shared_id' => 1]);
if (!empty($operation_status_model)) {
    $opstatus_id = $operation_status_model->id;
//We get facilities by operating status and type
    $facility_model = backend\models\Facility::find()->cache(Yii::$app->params['cache_duration'])
                    ->select(['type', 'COUNT(*) AS count'])
                    ->where(['operational_status' => $opstatus_id])
                    ->andWhere(['status' => 1])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();
    foreach ($facility_model as $model) {
        //Push pie data to array
        array_push($data, ['name' => backend\models\Facilitytype::findOne($model['type'])->name, 'y' => (int) $model['count'],]);
        //Push column labels to array
        if (!in_array(backend\models\Facilitytype::findOne($model['type'])->name, $labels)) {
            array_push($labels, backend\models\Facilitytype::findOne($model['type'])->name);
        }
        //We push column data to array
        array_push($data1, (int) $model['count']);
    }
    //We push pie plot details to the series
    array_push($pie_series, ['name' => 'Total', 'colorByPoint' => true, 'data' => $data]);
    array_push($column_series, ['name' => "Total", 'data' => $data1]);
}

/**
 * 
 * Data for Pie/Bar chart by Province
 * 
 */
$pie_series1 = [];
$column_series1 = [];
$data2 = [];
$data3 = [];
$labels1 = [];
$totalOperatingFacilities = 0;

if (!empty($operation_status_model)) {
    $province_counts = $connection->cache(function ($connection) use ($operation_status_model) {
        return $connection->createCommand('select count(f.id) as count,p.name from public."facility" f INNER JOIN 
                                            public."geography_district" d ON f.district_id=d.id INNER JOIN
                                            public."geography_province" p ON d.province_id=p.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . '
                                            group by p.name Order by p.name')
                ->queryAll();
    });

    foreach ($province_counts as $model) {
        //Add to total operating facilities
        $totalOperatingFacilities += (int) $model['count'];
        //Push pie data to array
        array_push($data2, ['name' => $model['name'], 'y' => (int) $model['count'],]);
        //Push column labels to array
        if (!in_array($model['name'], $labels1)) {
            array_push($labels1, $model['name']);
        }
        //We push column data to array
        array_push($data3, (int) $model['count']);
    }
    //We push pie plot details to the series
    array_push($pie_series1, ['name' => 'Total', 'colorByPoint' => true, 'data' => $data2]);
    array_push($column_series1, ['name' => "Total", 'data' => $data3]);
}

//Public
$public_count_active = backend\models\Facility::find()
        ->cache(Yii::$app->params['cache_duration'])
        ->where(['ownership_type' => 1])
        ->andWhere(['operational_status' => $operation_status_model->id])
        ->andWhere(['status' => 1])
        ->count();

// Private
$_private_count_active = backend\models\Facility::find()
        ->cache(Yii::$app->params['cache_duration'])
        ->where(['IN', 'ownership_type', [2]])
        ->andWhere(['operational_status' => $operation_status_model->id])
        ->andWhere(['status' => 1])
        ->count();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-sm-6 col-12">
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
        <div class="col-md-4 col-sm-6 col-12">
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
        <div class="col-md-4 col-sm-6 col-12">
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
                        <div class="chart tab-pane active" id="revenue-chart"
                             style="height: auto;">
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
                                             'text' => 'Operating Facilities by type'
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
                        <div class="chart tab-pane" id="sales-chart" 
                             style="position: relative; height: auto;">
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
                                             'text' => 'Operating Facilities by type'
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
                        <div class="chart tab-pane " id="pie-chart"
                             style="height: auto;">
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
                                             'text' => 'Operating Facilities by Province'
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
                        <div class="chart tab-pane active" id="bar-chart" 
                             style="position: relative; height: auto;">
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
                                             'text' => 'Operating Facilities by Province'
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
                        <div class="col-lg-8">
                            <p></p>
                            <?php
                            $filter_str = "Search results for: ";
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
                            if (isset($_GET['Facility']['province_id']) &&
                                    !empty($_GET['Facility']['province_id'])) {
                                $prov_model = \backend\models\Provinces::find()->cache(Yii::$app->params['cache_duration'])
                                                ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                                ->where(["id" => $_GET['Facility']['province_id']])->one();

                                if (!empty($prov_model)) {
                                    $coords = \backend\models\Provinces::getCoordinates(json_decode($prov_model->geom, true)['coordinates']);
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
                            if (isset($_GET['Facility']['district_id']) &&
                                    !empty($_GET['Facility']['district_id'])) {
                                $prov_model = \backend\models\Districts::find()->cache(Yii::$app->params['cache_duration'])
                                                ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
                                                ->where(["id" => $_GET['Facility']['district_id']])->one();

                                if (!empty($prov_model)) {
                                    $coords = \backend\models\Districts::getCoordinates(json_decode($prov_model->geom, true)['coordinates']);
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


                            //Show the filter parameters
                            if (isset($_GET['Facility']) && (!empty($dataProvider) && $dataProvider->getTotalCount() > 0)) {
                                if (!empty($_GET['Facility']['province_id']) ||
                                        !empty($_GET['Facility']['ownership']) ||
                                        !empty($_GET['Facility']['type']) ||
                                        !empty($_GET['Facility']['name']) ||
                                        !empty($_GET['Facility']['district_id'])) {
                                    $_province = !empty($_GET['Facility']['province_id']) ? \backend\models\Provinces::findOne($_GET['Facility']['province_id'])->name : "";
                                    $_district = !empty($_GET['Facility']['district_id']) ? \backend\models\Districts::findOne($_GET['Facility']['district_id'])->name : "";
                                    $_facility_type = !empty($_GET['Facility']['type']) ? \backend\models\Facilitytype::findOne($_GET['Facility']['type'])->name : "";
                                    $_ownership = !empty($_GET['Facility']['ownership']) ? \backend\models\FacilityOwnership::findOne($_GET['Facility']['ownership'])->name : "";
                                    $prov_str = !empty($_province) ? "Province:" . $_province . " | " : "";
                                    $dist_str = !empty($_district) ? "District:" . $_district . " | " : "";
                                    $fac_str = !empty($_facility_type) ? "Facility type:" . $_facility_type . " | " : "";
                                    $own_str = !empty($_ownership) ? "Ownship:" . $_ownership : "";
                                    $name_str = !empty($_GET['Facility']['name']) ? "Facility name:" . $_GET['Facility']['name'] . "|" : "";
                                    $filter_str .= "<i>" . $name_str . "</i><i>" . $prov_str . "</i><i>" . $dist_str . "</i><i>"
                                            . $fac_str . "</i><i>" . $own_str . "</I>";
                                    echo "<p class='text-sm'>$filter_str</p>";
                                }
                            }
                            if (isset($_GET['Facility']) && (!empty($dataProvider) && $dataProvider->getTotalCount() == 0)) {
                                echo "<p class='text-sm'>No search results were found!</p>";
                            }

                            //We make sure that the filter form maintains the filter values
                            if (isset($_GET['Facility']['province_id']) &&
                                    !empty($_GET['Facility']['province_id'])) {
                                $Facility_model->province_id = $_GET['Facility']['province_id'];
                            }
                            if (isset($_GET['Facility']['district_id']) &&
                                    !empty($_GET['Facility']['district_id'])) {
                                $Facility_model->district_id = $_GET['Facility']['district_id'];
                            }
                            if (isset($_GET['Facility']['ownership']) &&
                                    !empty($_GET['Facility']['ownership'])) {
                                $Facility_model->ownership = $_GET['Facility']['ownership'];
                            }
                            if (isset($_GET['Facility']['type']) &&
                                    !empty($_GET['Facility']['type'])) {
                                $Facility_model->type = $_GET['Facility']['type'];
                            }
                            if (isset($_GET['Facility']['name']) &&
                                    !empty($_GET['Facility']['name'])) {
                                $Facility_model->name = $_GET['Facility']['name'];
                            }
                            /* if (isset($_GET['Facility']['district_id']) &&
                              !empty($_GET['Facility']['district_id'])) {
                              $Facility_model->district_id = $_GET['Facility']['district_id'];
                              } */
                            if ($dataProvider !== "" && $dataProvider->getTotalCount() > 0) {
                                $dataProvider_models = $dataProvider->getModels();
                                foreach ($provinces_model as $model) {
                                    if (!empty($model->geom)) {
                                        //We pick a color for each province polygon
                                        $stroke_color = $colors[$counter];
                                        $counter++;

                                        $coords = \backend\models\Provinces::getCoordinates(json_decode($model->geom, true)['coordinates']);

                                        $polygon = new Polygon([
                                            'paths' => $coords,
                                            'strokeColor' => $stroke_color,
                                            'strokeOpacity' => 0.8,
                                            'strokeWeight' => 2,
                                            'fillColor' => $stroke_color,
                                            'fillOpacity' => 0.35,
                                        ]);
                                        //We get all districts in the province
                                        $districts_model = backend\models\Districts::find()->cache(Yii::$app->params['cache_duration'])
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
                                        $facilities_counts = backend\models\Facility::find()
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

                                                $constituency = !empty($_model->constituency_id) ? backend\models\Constituency::findOne($_model->constituency_id)->name : "";
                                                $ward = !empty($_model->ward_id) ? backend\models\Wards::findOne($_model->ward_id)->name : "";
                                                $loc_type = !empty($_model->location) ? backend\models\LocationType::findOne($_model->location)->name : "";
                                                $type = !empty($_model->type) ? backend\models\Facilitytype::findOne($_model->type)->name : "";
                                                $ownership = !empty($_model->ownership) ? backend\models\FacilityOwnership::findOne($_model->ownership)->name : "";
                                                $operation_status = !empty($_model->operational_status) ? backend\models\Operationstatus::findOne($_model->operational_status)->name : "";
                                                $type_str = "";
                                                $type_str .= "<b>Province: </b>" . \backend\models\Provinces::findOne(backend\models\Districts::findOne($_model->district_id)->province_id)->name . "<br>";
                                                $type_str .= "<b>District: </b>" . backend\models\Districts::findOne($_model->district_id)->name . "<br>";
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
                                                            . $type_str . '</p>'])
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

                                        $coords = \backend\models\Provinces::getCoordinates(json_decode($model->geom, true)['coordinates']);

                                        $polygon = new Polygon([
                                            'paths' => $coords,
                                            'strokeColor' => $stroke_color,
                                            'strokeOpacity' => 0.8,
                                            'strokeWeight' => 2,
                                            'fillColor' => $stroke_color,
                                            'fillOpacity' => 0.35,
                                        ]);

                                        //We get all districts in the province
                                        $districts_model = backend\models\Districts::find()->cache(Yii::$app->params['cache_duration'])
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
                                        $facilities_counts = backend\models\Facility::find()
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
                                    }
                                }

                                echo $map->display();
                            }
                            ?>
                        </div>
                        <div class="col-lg-4 text-sm">
                            <h4>Map Instructions</h4>
                            <ol>
                                <li>Click province to view facility counts by type</li>
                                <li>A filter below will show actual 
                                    facility locations on the map</li>
                            </ol>
                            <div class="row">

                                <?php
                                $form = ActiveForm::begin([
                                            'action' => ['index'],
                                            'method' => 'GET',
                                ]);
                                ?>
                                <div class="col-lg-12">
                                    <?php
                                    echo $form->field($Facility_model, 'name')->textInput(['maxlength' => true, 'placeholder' =>
                                        'Filter by facility name', 'required' => false,]);
                                    ?>
                                </div>
                                <div class="col-lg-12">
                                    <?php
                                    echo
                                            $form->field($Facility_model, 'province_id')
                                            ->dropDownList(
                                                    \backend\models\Provinces::getProvinceList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Filter by province', 'required' => false]
                                    );
                                    ?>
                                </div>
                                <div class="col-lg-12">
                                    <?php
                                    $Facility_model->isNewRecord = !empty($_GET['Facility']['province_id']) ? false : true;
                                    echo Html::hiddenInput('selected_id', $Facility_model->isNewRecord ? '' : $Facility_model->district_id, ['id' => 'selected_id']);

                                    echo $form->field($Facility_model, 'district_id')->widget(DepDrop::classname(), [
                                        'options' => ['id' => 'dist_id', 'custom' => true, 'required' => false,],
                                        //'data' => [backend\models\Districts::getListByProvinceID($Facility_model->province_id)],
                                        //'value'=>$Facility_model->district_id,
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'pluginOptions' => [
                                            'depends' => ['prov_id'],
                                            'initialize' => $Facility_model->isNewRecord ? false : true,
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
                                <div class="col-lg-12">
                                    <?=
                                            $form->field($Facility_model, 'type')
                                            ->dropDownList(
                                                    \backend\models\Facilitytype::getList(), ['custom' => true, 'prompt' => 'Filter by facility type', 'required' => false]
                                    );
                                    ?>
                                </div>
                                <div class="col-lg-12">
                                    <?=
                                            $form->field($Facility_model, 'ownership')
                                            ->dropDownList(
                                                    \backend\models\FacilityOwnership::getList(), ['custom' => true, 'prompt' => 'Filter by ownership', 'required' => false]
                                    );
                                    ?>
                                </div>
                                <div class="col-lg-12">
                                    <?= Html::submitButton('Filter map', ['class' => 'btn btn-primary btn-sm', 'name' => "filter", "value" => "true"]) ?>
                                    <?php //echo Html::resetButton('Reset', ['class' => 'btn btn-default btn-sm']) ?>
                                </div>
                                <?php ActiveForm::end(); ?>

                            </div>
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

