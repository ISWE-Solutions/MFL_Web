<?php

namespace backend\controllers;

use Yii;
use backend\models\AuditTrail;
use backend\models\AuditTrailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\User;
use yii\filters\AccessControl;

/**
 * AuditTrailController implements the CRUD actions for AuditTrailHea model.
 */
class AuditTrailController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index',],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all AuditTrailHea models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('View audit trail logs')) {
            //$audit = new AuditTrail();
            $searchModel = new AuditTrailSearch();
            /*$audit->user = Yii::$app->user->id;
            $audit->action = "Viewed audit trail logs";
            $audit->ip_address = Yii::$app->request->getUserIP();
            $audit->user_agent = Yii::$app->request->getUserAgent();
            $audit->save();*/
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (!empty(Yii::$app->request->queryParams['AuditTrailSearch']['date'])) {
                $date_arry = explode("-", Yii::$app->request->queryParams['AuditTrailSearch']['date']);
                $start_date = $date_arry[0];
                $end_date = $date_arry[1];
                $dataProvider->query->andFilterWhere(["BETWEEN", 'date', strtotime($start_date), strtotime($end_date)]);
            }
            $dataProvider->setSort([
                'attributes' => [
                    'date' => [
                        'desc' => ['date' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                ],
                'defaultOrder' => [
                    'date' => SORT_DESC
                ]
            ]);
            return $this->render('index', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/index']);
        }
    }

    /**
     * Displays a single AuditTrailHea model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found

      public function actionView($id) {
      return $this->render('view', [
      'model' => $this->findModel($id),
      ]);
      }
     */
    /**
     * Creates a new AuditTrailHea model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed

      public function actionCreate() {
      $model = new AuditTrailHea();

      if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
      }

      return $this->render('create', [
      'model' => $model,
      ]);
      }
     */
    /**
     * Updates an existing AuditTrailHea model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found

      public function actionUpdate($id) {
      $model = $this->findModel($id);

      if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
      }

      return $this->render('update', [
      'model' => $model,
      ]);
      }
     */
    /**
     * Deletes an existing AuditTrailHea model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found

      public function actionDelete($id) {
      $this->findModel($id)->delete();

      return $this->redirect(['index']);
      }
     */

    /**
     * Finds the AuditTrailHea model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AuditTrailHea the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = AuditTrailHea::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
