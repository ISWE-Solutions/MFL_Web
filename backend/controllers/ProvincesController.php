<?php

namespace backend\controllers;

use Yii;
use backend\models\Provinces;
use backend\models\ProvincesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\models\AuditTrail;
use backend\models\User;
use yii\db\Expression;

/**
 * ProvincesController implements the CRUD actions for Provinces model.
 */
class ProvincesController extends Controller {
    /**
     * {@inheritdoc}
     */

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
     * Lists all Provinces models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage provinces')) {
            $model = new Provinces();
            $searchModel = new ProvincesSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (Yii::$app->request->post('hasEditable')) {
                $Id = Yii::$app->request->post('editableKey');
                $model = Provinces::findOne($Id);
                $out = Json::encode(['output' => '', 'message' => '']);
                $posted = current($_POST['Provinces']);
                $post = ['Provinces' => $posted];
                $old = $model->name;
                $old_population = $model->population;
                $old_pop_density = $model->pop_density;
                $old_area_sq_km = $model->area_sq_km;
                $action = "";

                if ($model->load($post)) {
                    if ($old != $model->name) {
                        $action = "updated province name from $old to " . $model->name;
                    }
                    if ($old_population != $model->population) {
                        $action = "updated " . $model->name . " province population from $old_population to " . $model->population;
                    }
                    if ($old_pop_density != $model->pop_density) {
                        $action = "updated " . $model->name . " province population density from $old_pop_density to " . $model->pop_density;
                    }
                    if ($old_area_sq_km != $model->area_sq_km) {
                        $action = "updated " . $model->name . " province area from $old_area_sq_km sq kilometer to " . $model->area_sq_km . " sq kilometer";
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
     * Displays a single Provinces model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        if (User::userIsAllowedTo('Manage provinces')) {
            $model = Provinces::find()
                            ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km', 'ST_AsGeoJSON(geom) as geom'])
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
     * Creates a new Provinces model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage provinces')) {
            $model = new Provinces();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->load(Yii::$app->request->post())) {
                $coordinates = json_decode(Yii::$app->request->post()['geom'], true)['features'][0]['geometry']['coordinates'];
                if (!empty($coordinates)) {
                    $coordinates_json = \GuzzleHttp\json_encode(json_decode(Yii::$app->request->post()['geom'], true)['features'][0]['geometry']);
                    $model->geom = new Expression("ST_Force2D(ST_Multi(ST_GeomFromGeoJSON(:geom)))",
                            array(':geom' => $coordinates_json));
                    if ($model->save()) {
                        $audit = new AuditTrail();
                        $audit->user = Yii::$app->user->id;
                        $audit->action = "Added province " . $model->name;
                        $audit->ip_address = Yii::$app->request->getUserIP();
                        $audit->user_agent = Yii::$app->request->getUserAgent();
                        $audit->save();
                        Yii::$app->session->setFlash('success', 'Province ' . $model->name . ' was successfully added.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->session->setFlash('error', 'Error occured while adding Province ' . $model->name);
                    }
                    //return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'You need to pick geometry coordinates for the province!');
                }
            }

            return $this->render('create', ['model' => $model]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionUpdate($id) {
        if (User::userIsAllowedTo('Manage provinces')) {
            $model = Provinces::find()
                            ->select(['id', 'name', 'population', 'pop_density', 'area_sq_km',
                                'ST_AsGeoJSON(geom) as geom'])
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
                    if ($model->save()) {
                        $audit = new AuditTrail();
                        $audit->user = Yii::$app->user->id;
                        $audit->action = "Updated province " . $model->name;
                        $audit->ip_address = Yii::$app->request->getUserIP();
                        $audit->user_agent = Yii::$app->request->getUserAgent();
                        $audit->save();
                        Yii::$app->session->setFlash('success', 'Province ' . $model->name . ' was successfully updated.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->session->setFlash('error', 'Error occured while adding updating ' . $model->name);
                    }
                    //return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'You need to pick geometry coordinates for the province!');
                }
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
     * Deletes an existing Provinces model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        if (User::userIsAllowedTo('Remove provinces')) {
            $model = $this->findModel($id);
            $name = $model->name;
            if ($model->delete()) {
                $audit = new AuditTrail();
                $audit->user = Yii::$app->user->id;
                $audit->action = "Removed province $name from the system.";
                $audit->ip_address = Yii::$app->request->getUserIP();
                $audit->user_agent = Yii::$app->request->getUserAgent();
                $audit->save();
                Yii::$app->session->setFlash('success', "Province $name was successfully removed.");
            } else {
                Yii::$app->session->setFlash('error', "Province $name could not be removed. Please try again!");
            }

            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Finds the Provinces model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Provinces the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Provinces::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
