<?php

namespace backend\controllers;

use Yii;
use backend\models\Constituency;
use backend\models\ConstituencySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\models\AuditTrail;
use backend\models\User;
use yii\db\Expression;

/**
 * ConstituenciesController implements the CRUD actions for Constituency model.
 */
class ConstituenciesController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'delete', 'view', 'create', 'update'],
                'rules' => [
                    [
                        'actions' => ['index', 'delete', 'view', 'create', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Constituency models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage constituencies')) {
            $model = new Constituency();
            $searchModel = new ConstituencySearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (!empty(Yii::$app->request->queryParams['ConstituencySearch']['province_id'])) {
                $district_ids = [];
                $districts = \backend\models\Districts::find()->where(['province_id' => Yii::$app->request->queryParams['ConstituencySearch']['province_id']])->all();
                if (!empty($districts)) {
                    foreach ($districts as $id) {
                        array_push($district_ids, $id['id']);
                    }
                }

                $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
            }

            if (Yii::$app->request->post('hasEditable')) {
                $Id = Yii::$app->request->post('editableKey');
                $model = Constituency::findOne($Id);
                $out = Json::encode(['output' => '', 'message' => '']);
                $posted = current($_POST['Constituency']);
                $post = ['Constituency' => $posted];
                $old = $model->name;
                $old_population = $model->population;
                $old_pop_density = $model->pop_density;
                $old_area_sq_km = $model->area_sq_km;
                $old_district = $model->district_id;
                $action = "";

                if ($model->load($post)) {
                    if ($old != $model->name) {
                        $action = "updated Constituency name from $old to " . $model->name;
                    }
                    if ($old_population != $model->population) {
                        $action = "updated " . $model->name . " Constituency population from $old_population to " . $model->population;
                    }
                    if ($old_pop_density != $model->pop_density) {
                        $action = "updated " . $model->name . " Constituency population density from $old_pop_density to " . $model->pop_density;
                    }
                    if ($old_area_sq_km != $model->area_sq_km) {
                        $action = "updated " . $model->name . " Constituency area from $old_area_sq_km sq kilometer to " . $model->area_sq_km . " sq kilometer";
                    }
                    if ($old_district != $model->district_id) {
                        $action = "updated " . $model->name . " Constituency district from " . \backend\models\Districts::findOne($old_district)->name . " to " . \backend\models\Districts::findOne($model->district_id)->name;
                    }

                    if (!empty($action)) {
                        $audit = new AuditTrail();
                        $audit->user = Yii::$app->user->id;
                        $audit->action = $action;
                        $audit->ip_address = Yii::$app->request->getUserIP();
                        $audit->user_agent = Yii::$app->request->getUserAgent();
                        $audit->save();
                    }

                    $message = '';
                    if (!$model->save()) {
                        foreach ($model->getErrors() as $error) {
                            $message .= $error[0];
                        }
                        $output = $message;
                    }
                    $output = '';
                    $out = Json::encode(['output' => $output, 'message' => $message]);
                }
                return $out;
            }
            return $this->render('index', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Displays a single Constituency model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        if (User::userIsAllowedTo('Manage constituencies')) {
            $model = Constituency::find()
                            ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom', 'district_id'])
                            ->where(["id" => $id])->one();
            return $this->render('view', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Creates a new Constituency model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage constituencies')) {
            $model = new Constituency();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->load(Yii::$app->request->post())) {

                $coordinates = json_decode(Yii::$app->request->post()['geom'], true)['features'];
                //  [0]['geometry']['coordinates'];

                if (!empty($coordinates)) {
                    $coordinates_json = \GuzzleHttp\json_encode(json_decode(Yii::$app->request->post()['geom'], true)['features'][0]['geometry']);
                    $model->geom = new Expression("ST_Force2D(ST_Multi(ST_GeomFromGeoJSON(:geom)))",
                            array(':geom' => $coordinates_json));
                }

                if ($model->save()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added Constituency " . $model->name;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Constituency ' . $model->name . ' was successfully added.');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error occured while adding Constituency ' . $model->name);
                }
                //return $this->redirect(['index']);
//                } else {
//                    Yii::$app->session->setFlash('error', 'You need to pick geometry coordinates for the constituency!');
//                }
            }
            return $this->render('create', ['model' => $model]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionUpdate($id) {
        if (User::userIsAllowedTo('Manage constituencies')) {
            $model = Constituency::find()
                            ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km',
                                'district_id', 'ST_AsGeoJSON(geom) as geom'])
                            ->where(["id" => $id])->one();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->load(Yii::$app->request->post())) {
                $_post_decodeded = json_decode(Yii::$app->request->post()['geom'], true)['features'];
                $coordinates = !empty($_post_decodeded) ? $_post_decodeded[0]['geometry']['coordinates'] : "";
                if (!empty($coordinates)) {
                    $coordinates_json = \GuzzleHttp\json_encode(json_decode(Yii::$app->request->post()['geom'], true)['features'][0]['geometry']);
                    $model->geom = new Expression("ST_Force2D(ST_Multi(ST_GeomFromGeoJSON(:geom)))",
                            array(':geom' => $coordinates_json));
                }

                if ($model->save()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Updated Constituency " . $model->name;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Constituency ' . $model->name . ' was successfully updated.');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    $message = "";
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while updating constituency ' . $model->name . ".Error is:::$message");
                }
//                } else {
//                    Yii::$app->session->setFlash('error', 'You need to pick geometry coordinates for the constituency!');
//                }
            }
            return $this->render('update', [
                        'model' => $model
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Deletes an existing Constituency model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        if (User::userIsAllowedTo('Remove constituencies')) {
            $model = $this->findModel($id);
            $name = $model->name;
            if ($model->delete()) {
                $audit = new AuditTrail();
                $audit->user = Yii::$app->user->id;
                $audit->action = "Removed Constituency $name from the system.";
                $audit->ip_address = Yii::$app->request->getUserIP();
                $audit->user_agent = Yii::$app->request->getUserAgent();
                $audit->save();
                Yii::$app->session->setFlash('success', "Constituency $name was successfully removed.");
            } else {
                Yii::$app->session->setFlash('error', "Constituency $name could not be removed. Please try again!");
            }

            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Finds the Constituency model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Constituency the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Constituency::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDistrict() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            //  Yii::warning('**********************', var_export($_POST['depdrop_parents'],true));
            //   $parents = $_POST['depdrop_all_params']['parent_id'];
            $selected_id = $_POST['depdrop_params'];
            if ($parents != null) {
                $prov_id = $parents[0];
                $out = \backend\models\Districts::find()
                        ->select(['id', 'name'])
                        ->where(['province_id' => $prov_id])
                        ->asArray()
                        ->all();

                return ['output' => $out, 'selected' => $selected_id[0]];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionConstituency() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $selected_id = $_POST['depdrop_all_params']['selected_id2'];
            if ($parents != null) {
                $dist_id = $parents[0];
                $out = \backend\models\Constituency::find()
                        ->select(['id', 'name'])
                        ->where(['district_id' => $dist_id])
                        ->asArray()
                        ->all();

                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionWard() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $selected_id = $_POST['depdrop_all_params']['selected_id3'];
            if ($parents != null) {
                $dist_id = $parents[0];
                $out = \backend\models\Wards::find()
                        ->select(['id', 'name'])
                        ->where(['district_id' => $dist_id])
                        ->asArray()
                        ->all();

                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

}
