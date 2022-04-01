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
/* @var $model backend\models\Districts */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Districts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

//We assume facility operation status name "Operational" 
//will never be renamed/deleted otherwise the system breaks
$operation_status_model = \backend\models\Operationstatus::findOne(['name' => "Functional"]);

if (!empty($operation_status_model)) {
    $opstatus_id = $operation_status_model->id;
    //We now get the facilities in the district
    $facilities_counts = backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->select(["COUNT(*) as count", "type"])
                    ->where(['operational_status' => $opstatus_id])
                    ->andWhere(['district_id' => $model->id])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();
}



//We build the window string
$type_str = "";
if(!empty($facilities_counts)){
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
            if (User::userIsAllowedTo('Manage districts')) {
                echo Html::a(
                        '<span class="fas fa-edit fa-2x"></span>', ['update', 'id' => $model->id],
                        [
                            'title' => 'Update district',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'data-pjax' => '0',
                            'style' => "padding:5px;",
                            'class' => 'bt btn-lg'
                        ]
                );
            }
            if (User::userIsAllowedTo('Remove districts')) {

                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo Html::a('<i class="fas fa-trash fa-2x"></i>', ['delete', 'id' => $model->id], [
                    'title' => 'Remove district',
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'data' => [
                        'confirm' => 'Are you sure you want to remove ' . $model->name . ' District?<br>'
                        . 'District will only be removed if its not being used by the system!',
                        'method' => 'post',
                    ],
                ]);
            }
            //This is a hack, just to use pjax for the delete confirm button
            $query = User::find()->where(['id' => '-2']);
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $query,
            ]);
            GridView::widget([
                'dataProvider' => $dataProvider,
            ]);
            ?>
        </p>
        <div class="row">
            <div class="col-lg-3">
                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        // 'id',
                        [
                            'attribute' => 'province_id',
                            'value' => function($model) {
                                return \backend\models\Provinces::findOne($model->province_id)->name;
                            }
                        ],
                        [
                            'attribute' => 'district_type_id',
                            'value' => function($model) {
                                return \backend\models\DistrictType::findOne($model->district_type_id)->name;
                            }
                        ],
                        'name',
                        'population',
                        'pop_density',
                        'area_sq_km',
                    /* [
                      'contentOptions' => ['style' => ['max-width' => '1000px;', 'height' => '100px']],
                      'attribute' => 'geom',
                      'format' => "raw",
                      ], */
                    ],
                ])
                ?>
            </div>
            <div class="col-lg-9">
                <?php
                $coords = [];
                $center_coords = [];
                if (empty($model->geom)) {
                    echo "<div class='alert alert-warning'>There are no polygon coordinates for district</div>";
                } else {
                    $coords = \backend\models\Districts::getCoordinates(json_decode($model->geom, true)['coordinates']);
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
                  'zoom' => 8,
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
                  'content' => '<p>' . $model->name . ' District</p>'
                  ]));

                  $map->addOverlay($polygon);
                  echo $map->display(); */
                ?>
                <div id="map" style="width: 100%; height: 500px"></div>
            </div>
        </div>

    </div>
</div>
<script>
var myvar = <?php echo json_encode($model->name); ?>;
var facility_types=<?php echo json_encode($type_str); ?>;

<?php
echo 'var center=[' . $center_coords[1] . ',' . $center_coords[0] . ']';
?> 
var map = L.map('map').setView(center, 8);

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

var poly = L.polygon(polygon,{color: 'red'}).addTo(map);
poly.bindPopup("<p><strong><span class='text-center'>"+myvar+" District functional facilities</span></strong></p><hr>"+facility_types);
</script>
