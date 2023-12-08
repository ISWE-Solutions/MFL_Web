<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\Facility;
use frontend\models\FacilitySearch;

/**
 * Facility controller
 */
class FacilityController extends Controller
{

    /**
     * Displays a single MFLFacility model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Facility::find()
            ->select(['*', 'ST_AsGeoJSON(geom) as geom'])
            ->where(["id" => $id])->one();
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Lists all MFLFacility models.
     * @return mixed
     */
    public function actionIndex($type = "", $ownership = "")
    {
        $searchModel = new FacilitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['status' => 1]);

        if (!empty(Yii::$app->request->queryParams['FacilitySearch']['province_id'])) {
            $district_ids = [];
            $districts = \backend\models\Districts::find()->cache(Yii::$app->params['cache_duration'])->where(['province_id' => Yii::$app->request->queryParams['FacilitySearch']['province_id']])->all();
            if (!empty($districts)) {
                foreach ($districts as $id) {
                    array_push($district_ids, $id['id']);
                }
            }

            $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
        }

        if (!empty($type)) {
            $dataProvider->query->andFilterWhere(['type' => $type]);
        }
        if (!empty($ownership)) {
            $dataProvider->query->andFilterWhere(['ownership' => $ownership]);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSearch()
    {
        $searchModel = new FacilitySearch();

        if (
            !empty(Yii::$app->request->queryParams['FacilitySearch']['district_id']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['constituency_id']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['zone_id']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['ward_id']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['ownership']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['name']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['province_id']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['type']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['operational_status']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['ownership_type']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['service_category']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['service']) ||
            !empty(Yii::$app->request->queryParams['FacilitySearch']['province_id'])
        ) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else {
            // a hack to create an illusion that a person has not searched yet
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andFilterWhere(['id' => -1]);
        }

        //Filter by province
        if (!empty(Yii::$app->request->queryParams['FacilitySearch']['province_id'])) {

            $district_ids = [];
            $districts = \backend\models\Districts::find()->cache(Yii::$app->params['cache_duration'])
                ->where(['province_id' => Yii::$app->request->queryParams['FacilitySearch']['province_id']])->all();
            if (!empty($districts)) {
                foreach ($districts as $id) {
                    array_push($district_ids, $id['id']);
                }
            }

            $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
            //var_dump($dataProvider);exit();
        }
        //Filter by district
        /* if (!empty(Yii::$app->request->queryParams['FacilitySearch']['district_id'])) {
          $dataProvider->query->andFilterWhere(['district_id' => Yii::$app->request->queryParams['FacilitySearch']['district_id']]);
          }
          //Filter by constituency
          if (!empty(Yii::$app->request->queryParams['FacilitySearch']['constituency_id'])) {
          $dataProvider->query->andFilterWhere(['constituency_id' => Yii::$app->request->queryParams['FacilitySearch']['constituency_id']]);
          }
          //Filter by ward
          if (!empty(Yii::$app->request->queryParams['FacilitySearch']['ward_id'])) {
          $dataProvider->query->andFilterWhere(['ward_id' => Yii::$app->request->queryParams['FacilitySearch']['ward_id']]);
          }
          //Filter by ownership
          if (!empty(Yii::$app->request->queryParams['FacilitySearch']['ownership_id'])) {
          $dataProvider->query->andFilterWhere(['ownership_id' => Yii::$app->request->queryParams['FacilitySearch']['ownership_id']]);
          }
          //Filter by facility type
          if (!empty(Yii::$app->request->queryParams['FacilitySearch']['facility_type_id'])) {
          $dataProvider->query->andFilterWhere(['facility_type_id' => Yii::$app->request->queryParams['FacilitySearch']['facility_type_id']]);
          }
          //Filter by operation status
          if (!empty(Yii::$app->request->queryParams['FacilitySearch']['operation_status_id'])) {
          $dataProvider->query->andFilterWhere(['operation_status_id' => Yii::$app->request->queryParams['FacilitySearch']['operation_status_id']]);
          } */


        //Filter by service category
        if (!empty(Yii::$app->request->queryParams['FacilitySearch']['service_category'])) {
            $service_ids = [];
            $facility_service_ids = [];
            $services = \backend\models\FacilityService::find()->cache(Yii::$app->params['cache_duration'])
                ->where(['category_id' => Yii::$app->request->queryParams['FacilitySearch']['service_category']])
                ->all();
            if (!empty($services)) {
                foreach ($services as $id) {
                    array_push($service_ids, $id['id']);
                }
            }
            if (!empty($service_ids)) {
                $facility_services = \backend\models\MFLFacilityServices::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['IN', 'service_id', $service_ids])
                    ->all();
                if (!empty($facility_services)) {
                    foreach ($facility_services as $id) {
                        array_push($facility_service_ids, $id['facility_id']);
                    }
                }
            }
            $dataProvider->query->andFilterWhere(['IN', 'id', $facility_service_ids]);
        }
        //Filter by service
        if (!empty(Yii::$app->request->queryParams['FacilitySearch']['service'])) {
            $facility_service_ids = [];
            $facility_services = \backend\models\MFLFacilityServices::find()
                ->cache(Yii::$app->params['cache_duration'])
                ->where(['service_id' => Yii::$app->request->queryParams['FacilitySearch']['service']])
                ->all();
            if (!empty($facility_services)) {
                foreach ($facility_services as $id) {
                    array_push($facility_service_ids, $id['facility_id']);
                }
            }
            $dataProvider->query->andFilterWhere(['IN', 'id', $facility_service_ids]);
        }


        return $this->render('search', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 
     * @return type
     */
    public function actionServices()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $selected_id = $_POST['depdrop_all_params']['selected_id_s'];
            if ($parents != null) {

                $dist_id = $parents[0];

                $out = \backend\models\FacilityService::find()
                    ->select(['id', 'name'])
                    ->where(['category_id' => $dist_id])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * 
     * @return type
     */
    public function actionOwnerships()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $selected_id = $_POST['depdrop_all_params']['selected_id_o'];
            if ($parents != null) {
                $dist_id = $parents[0];
                if ($dist_id == 2) {
                    $out = \backend\models\FacilityOwnership::find()
                        ->select(['shared_id as id', 'name'])
                        ->where(['shared_id' => 9])
                        ->asArray()
                        ->all();
                } else {
                    $out = \backend\models\FacilityOwnership::find()
                        ->select(['shared_id as id', 'name'])
                        ->where(["NOT IN", 'shared_id', [9]])
                        ->asArray()
                        ->all();
                }

                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * 
     * @return type
     */
    public function actionRating()
    {
        $model = new \backend\models\MFLFacilityRatings();
        if ($model->load(Yii::$app->request->post())) {

            $rating = Yii::$app->request->post()['MFLFacilityRatings'][$model->rate_type_id]['rating'];
            $model->rate_value = $rating;
            $ratings = [
                1 => 'Very Poor',
                2 => 'Poor',
                3 => 'Average',
                4 => 'Good',
                5 => 'Very Good',
            ];
            $model->rating = $ratings[$rating];

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Facility rating was successful.');
            } else {
                $message = '';
                foreach ($model->getErrors() as $error) {
                    $message .= $error[0];
                }
                Yii::$app->session->setFlash('error', 'Error occured while rating facility. Error is::.' . $message);
            }
            return $this->renderAjax('success', []);
        }
    }
}
