<?php

namespace backend\controllers;

use Yii;
use backend\models\FacilityOwnership;
use backend\models\FacilityOwnershipSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\models\AuditTrail;
use backend\models\User;

/**
 * FacilityOwnershipController implements the CRUD actions for FacilityOwnership model.
 */
class FacilityOwnershipController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'delete',],
                'rules' => [
                    [
                        'actions' => ['index', 'delete'],
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
     * Lists all FacilityOwnership models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage facility ownerships')) {
            $model = new FacilityOwnership();
            $searchModel = new FacilityOwnershipSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (Yii::$app->request->post('hasEditable')) {
                $Id = Yii::$app->request->post('editableKey');
                 Yii::info("-----------------------------LOGGGGGGIINNNNNGGGGGG STARTS-----------------------------");
                Yii::info($Id);
                Yii::info("-----------------------------LOGGGGGGIINNNNNGGGGGG ENDS-------------------------------");
                die();
                $model = FacilityOwnership::findOne($Id);
                
               
                
                $out = Json::encode(['output' => '', 'message' => '']);
                $posted = current($_POST['FacilityOwnership']);
                $post = ['FacilityOwnership' => $posted];
                $old = $model->name;
                $old_shared_id = $model->shared_id;

                if ($model->load($post)) {
                    $msg = "";
                    if ($old != $model->name) {
                        $msg = "Updated Facility ownership name from $old to " . $model->name;
                    }
                    if ($old_shared_id != $model->shared_id) {
                        $msg = "Updated Facility ownership shared id from $old_shared_id to " . $model->shared_id;
                    }
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = $msg;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();

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
            $dataProvider->pagination = ['pageSize' => 15];
            $dataProvider->setSort([
                'attributes' => [
                    'id' => [
                        'desc' => ['id' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]);
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
     * Creates a new FacilityOwnership model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage facility ownerships')) {
            $model = new FacilityOwnership();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added Facility ownership " . $model->name;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility ownership ' . $model->name . ' was successfully added.');
                } else {
                    Yii::$app->session->setFlash('error', 'Error occured while adding Facility ownership ' . $model->name);
                }
                return $this->redirect(['index']);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Deletes an existing FacilityOwnership model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        if (User::userIsAllowedTo('Manage facility ownerships')) {
            $model = $this->findModel($id);
            $name = $model->name;
            try {
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility ownership $name from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility ownership $name was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility ownership $name could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility ownership $name could not be removed. Please try again!");
            }

            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Finds the FacilityOwnership model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FacilityOwnership the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = FacilityOwnership::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
