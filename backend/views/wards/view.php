<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\User;
use kartik\grid\GridView;
use dosamigos\google\maps\LatLng;
use dosamigos\google\maps\services\DirectionsWayPoint;
use dosamigos\google\maps\services\TravelMode;
use dosamigos\google\maps\overlays\PolylineOptions;
use dosamigos\google\maps\services\DirectionsRenderer;
use dosamigos\google\maps\services\DirectionsService;
use dosamigos\google\maps\overlays\InfoWindow;
use dosamigos\google\maps\overlays\Marker;
use dosamigos\google\maps\Map;
use dosamigos\google\maps\services\DirectionsRequest;
use dosamigos\google\maps\overlays\Polygon;
use dosamigos\google\maps\layers\BicyclingLayer;

/* @var $this yii\web\View */
/* @var $model backend\models\Wards */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Wards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
//We assume facility operation status name "Operational" 
//will never be renamed/deleted otherwise the system breaks
$operation_status_model = \backend\models\Operationstatus::findOne(['name' => "Functional"]);

if (!empty($operation_status_model)) {
    $opstatus_id = $operation_status_model->id;
    //We now get the facilities in the ward
    $facilities_counts = backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->select(["COUNT(*) as count", "type"])
                    ->where(['operational_status' => $opstatus_id])
                    ->andWhere(['ward_id' => $model->id])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();
}



//We build the window string
$type_str = "";
if (!empty($facilities_counts)) {
    foreach ($facilities_counts as $f_model) {
        $facility_type = !empty($f_model['type']) ? backend\models\Facilitytype::findOne($f_model['type'])->name : "";
        $type_str .= $facility_type . ":<b>" . $f_model['count'] . "</b><br>";
    }
}
?>
<div class="card card-primary card-outline">
    <div class="card-body">
        <p>
            <?php
            if (User::userIsAllowedTo('Manage wards')) {
                echo Html::a(
                        '<span class="fas fa-edit fa-2x"></span>', ['update', 'id' => $model->id],
                        [
                            'title' => 'Update ward',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'data-pjax' => '0',
                            'style' => "padding:5px;",
                            'class' => 'bt btn-lg'
                        ]
                );
            }

            if (User::userIsAllowedTo('Remove wards')) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo Html::a('<i class="fas fa-trash fa-2x"></i>', ['delete', 'id' => $model->id],
                        [
                            'title' => 'Remove ward',
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                            'data' => [
                                'confirm' => 'Are you sure you want to remove ' . $model->name . ' ward?<br>'
                                . 'Ward will only be removed if its not being used by the system!',
                                'method' => 'post',
                            ],
                        ]
                );
            }
            //This is a hack, just to use pjax for the delete confirm button
            $query = User::find()->where(['id' => '-2']);
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $query,
            ]);
            GridView::widget([
                'dataProvider' => $dataProvider,
            ]);
            // echo backend\models\Districts::findOne($model->district_id)->name;
            ?>
        </p>
        <div class="row">
            <div class="col-lg-3">

                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'province_id', 'value' => function ($model) {
                                $province_id = !empty($model->district_id) ? backend\models\Districts::findOne($model->district_id)->province_id : "";
                                $name = !empty($province_id) ? backend\models\Provinces::findOne($province_id)->name : "";
                                return $name;
                            },
                        ],
                        [
                            'attribute' => 'district_id',
                            'value' => function ($model) {
                                $name = !empty($model->district_id) ? backend\models\Districts::findOne($model->district_id)->name : "";
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
                        'name',
                        'population',
                        'pop_density',
                        'area_sq_km',
                    //'geom',
                    ],
                ])
                ?>
            </div>
            <div class="col-lg-9">
                <?php
                $coords = [];
                $center_coords = [];
                if (empty($model->geom)) {
                    echo "<div class='alert alert-warning'>There are no polygon coordinates for ward</div>";
                } else {
                    $coords = \backend\models\Constituency::getCoordinates(json_decode($model->geom, true)['coordinates']);
                    $coord = json_decode($model->geom, true)['coordinates'][0][0];
                    $center = round(count($coord) / 2);
                    $center_coords = $coord[$center];
                }

                /* if (!empty($center_coords)) {
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
                  $polygon = new Polygon([
                  'paths' => $coords,
                  'strokeColor' => Yii::$app->params['polygon_strokeColor'],
                  'strokeOpacity' => 0.8,
                  'strokeWeight' => 2,
                  'fillColor' => Yii::$app->params['polygon_strokeColor'],
                  'fillOpacity' => 0.35,
                  ]);

                  $polygon->attachInfoWindow(new InfoWindow([
                  'content' => '<p>' . $model->name . ' Ward</p>'
                  ]));

                  $map->addOverlay($polygon);
                  echo $map->display(); */
                ?>


                <?php
                // first lets setup the center of our map
                // $center = new \dosamigos\leaflet\types\LatLng(['lat' => -13.445529118205,
                //  'lng' => 28.983639375]);
                /*  $center = new \dosamigos\leaflet\types\LatLng(['lat' => 37.7900,
                  'lng' => -122.401]);
                  $polygon = new dosamigos\leaflet\layers\Polygon(
                  [
                  'name' => 'poly',
                  //'map' => 'map',
                  'clientOptions' => [
                  'color' => ''
                  ]
                  ]);
                  $polygon->setLatLngs([
                  new \dosamigos\leaflet\types\LatLng(['lat' => 37.786617, 'lng' => -122.404654]),
                  new \dosamigos\leaflet\types\LatLng(['lat' => 37.797843, 'lng' => -122.407057]),
                  new \dosamigos\leaflet\types\LatLng(['lat' => 37.798962, 'lng' => -122.398260]),
                  new \dosamigos\leaflet\types\LatLng(['lat' => 37.794299, 'lng' => -122.395234]),
                  ]); */

                /*
                 * var polygonPoints = [
                  [37.786617, -122.404654],
                  [37.797843, -122.407057],
                  [37.798962, -122.398260],
                  [37.794299, -122.395234]];
                 */
                // now lets create a marker that we are going to place on our map
                /* $marker = new \dosamigos\leaflet\layers\Marker(['latLng' => $center, 'popupContent' => 'Hi!']);

                  // The Tile Layer (very important)
                  $tileLayer = new \dosamigos\leaflet\layers\TileLayer([
                  'urlTemplate' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                  'clientOptions' => [
                  // 'attribution' => '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                  // 'subdomains' => ['1', '2', '3', '4'],
                  ],
                  ]);

                  // now our component and we are going to configure it
                  $leaflet = new \dosamigos\leaflet\LeafLet([
                  'center' => $center, // set the center
                  'name' => 'map',
                  'tileLayer' => $tileLayer,
                  'zoom' => Yii::$app->params['polygon_zoom'],
                  ]);

                  // Different layers can be added to our map using the `addLayer` function.
                  $leaflet->addLayer($marker)      // add the marker
                  ->addLayer($tileLayer)
                  ->addLayer($polygon);  // add the tile layer
                  //  echo \dosamigos\leaflet\widgets\Map::widget(['leafLet' => $leaflet]);

                  echo $leaflet->widget(['options' => ['style' => 'min-height: 500px']]); */
                ?>
                <div id="map" style="width: 100%; height: 500px"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var myvar = <?php echo json_encode($model->name); ?>;
    var facility_types =<?php echo json_encode($type_str); ?>;

<?php
if(!empty($center_coords)){
echo 'var center=[' . $center_coords[1] . ',' . $center_coords[0] . ']';
}
?>
    var map = L.map('map').setView(center, 10);

    L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; MoH-MFL, <a href="http://osm.org/copyright">OpenStreetMap</a>; contributors'
    }).addTo(map);
<?php
if (!empty($model->geom)) {
    echo 'var polygon =[';
    //echo 'var latlngs ='. GuzzleHttp\json_encode(json_decode($model->geom, true)['coordinates'][0][0]);
    foreach (json_decode($model->geom, true)['coordinates'][0][0] as $point) {
        echo '[' . $point[1] . "," . $point[0] . "], ";
    }
    echo "];\n";
} else {
    echo 'var polygon =[];';
}
?>

    var poly = L.polygon(polygon, {color: 'red'}).addTo(map);
    poly.bindPopup("<p><strong><span class='text-center'>" + myvar + " Ward operational facilities</span></strong></p><hr>" + facility_types);
</script>

