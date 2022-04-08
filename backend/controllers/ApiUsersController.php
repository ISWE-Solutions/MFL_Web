<?php

namespace backend\controllers;

use Yii;
use backend\models\ApiUsers;
use backend\models\ApiUsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\models\AuditTrail;
use backend\models\User;
use yii\db\Expression;

/**
 * ApiUsersController implements the CRUD actions for ApiUsers model.
 */
class ApiUsersController extends Controller {

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
     * Lists all ApiUsers models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage api users') || (User::userIsAllowedTo('View api users'))) {
            $searchModel = new ApiUsersSearch();
            $model = new ApiUsers();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (Yii::$app->request->post('hasEditable')) {
                $userId = Yii::$app->request->post('editableKey');
                $model = ApiUsers::findOne($userId);
                $out = Json::encode(['output' => '', 'message' => '']);
                $posted = current($_POST['ApiUsers']);
                $post = ['ApiUsers' => $posted];
                $old_status = $model->status;

                if ($model->load($post)) {
                    if ($old_status != $model->status) {
                        $action = $model->status == User::STATUS_ACTIVE ?
                                "Activated API user account with email:" . $model->email : "Deactivated API user account with email:" . $model->email;
                        $audit = new AuditTrail();
                        $audit->user = Yii::$app->user->id;
                        $audit->action = $action;
                        $audit->ip_address = Yii::$app->request->getUserIP();
                        $audit->user_agent = Yii::$app->request->getUserAgent();
                        $audit->save();
                    }

                    $model->updated_by = Yii::$app->user->id;
                    $model->save(false);
                    $output = '';
                    $out = Json::encode(['output' => $output, 'message' => '']);
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
     * Displays a single ApiUsers model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ApiUsers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage api users')) {
            $model = new ApiUsers();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                $model->auth_key = Yii::$app->security->generateRandomString(10);
                $key = Yii::$app->security->generateRandomString(10);
                $passwordHash = self::getEncodePassword($key . $model->auth_key);


                if (!empty($passwordHash)) {
                    $model->created_by = Yii::$app->user->identity->id;
                    $model->updated_by = Yii::$app->user->identity->id;
                    $model->status = 1;
                    $model->created_at = new \yii\db\Expression('NOW()');
                    $model->updated_at = new \yii\db\Expression('NOW()');
                    $model->password = $passwordHash;
                    if ($model->save()) {
                        $ath = new \backend\models\AuditTrail();
                        $ath->user = Yii::$app->user->identity->id;
                        $ath->action = "Created API user with username:" . $model->username;
                        $ath->ip_address = Yii::$app->request->getUserIP();
                        $ath->user_agent = Yii::$app->request->getUserAgent();
                        $ath->save();
                        if (self::sendApiKey($model->username, $key, $model->email)) {
                            Yii::$app->session->setFlash('success', 'API user details were successfully sent to the provided email.');
                        } else {
                            Yii::$app->session->setFlash('error', 'API user was created but not sent to the provided email. kindly click regenerate to resent the api details!');
                        }
                    } else {
                        $message = '';
                        foreach ($model->getErrors() as $error) {
                            $message .= $error[0];
                        }
                        Yii::$app->session->setFlash('error', 'Error occured while creating api user.Error:' . $message);
                        return $this->redirect(['index']);
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Error occured while regenerating api user password.Error: Could not connect to the MFL API to encode password!');
                }
                return $this->redirect(['index']);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Updates an existing ApiUsers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        if (User::userIsAllowedTo('Manage api users')) {
            $model = $this->findModel($id);

            if ($model != null) {
                $model->auth_key = Yii::$app->security->generateRandomString(10);
                $key = Yii::$app->security->generateRandomString(10);
                $passwordHash = self::getEncodePassword($key . $model->auth_key);

                if (!empty($passwordHash)) {
                    $model->password = $passwordHash;
                    $model->updated_by = Yii::$app->user->identity->id;
                    $model->updated_at = new \yii\db\Expression('NOW()');
                    if ($model->save()) {
                        $ath = new \backend\models\AuditTrail();
                        $ath->user = Yii::$app->user->identity->id;
                        $ath->action = "Regenerated API user password for user with username:" . $model->username;
                        $ath->ip_address = Yii::$app->request->getUserIP();
                        $ath->user_agent = Yii::$app->request->getUserAgent();
                        $ath->save();

                        if (self::sendApiKeyRegenerated($model->username, $key, $model->email)) {
                            Yii::$app->session->setFlash('success', 'API user password was successfully regenerated and sent to the provided email.');
                        } else {
                            Yii::$app->session->setFlash('error', 'API user password was regenerated but not sent to the provided email. Kindly click regenerate to resend the api password!');
                        }

                        return $this->redirect(['index']);
                    } else {
                        $message = '';
                        foreach ($model->getErrors() as $error) {
                            $message .= $error[0];
                        }
                        Yii::$app->session->setFlash('error', 'Error occured while regenerating api user password.Error:' . $message);
                        return $this->redirect(['index']);
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Error occured while regenerating api user password.Error: Could not connect to the MFL API to encode password!');
                    return $this->redirect(['index']);
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public static function getEncodePassword($key) {
        try {
            $url = str_replace("{key}", $key, Yii::$app->params['api_url']);
            $ch = \curl_init();
            \curl_setopt($ch, CURLOPT_URL, $url);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_HEADER, false);
            $response = curl_exec($ch);
            \curl_close($ch);
            return $response;
        } catch (Exception $ex) {
            return "";
        }
    }

    /**
     * @param $email
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function sendApiKey($username, $key, $email) {
        return Yii::$app->mailer
                        ->compose(['html' => 'APIAccess-html', 'text' => 'passwordResetToken-text_1'], ['username' => $username, 'key' => $key])
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['supportEmail']])
                        ->setTo($email)
                        ->setSubject('MFL API Access details')
                        ->send();
    }

    /**
     * @param $email
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function sendApiKeyRegenerated($username, $key, $email) {
        return Yii::$app->mailer
                        ->compose(['html' => 'APIAccess-html', 'text' => 'passwordResetToken-text_1'], ['username' => $username, 'key' => $key])
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['supportEmail']])
                        ->setTo($email)
                        ->setSubject('Regenerated MFL API Access details')
                        ->send();
    }

    /**
     * Deletes an existing ApiUsers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionDelete($id) {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the ApiUsers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ApiUsers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = ApiUsers::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
