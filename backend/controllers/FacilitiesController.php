<?php

namespace backend\controllers;

use Yii;
use backend\models\Facility;
use backend\models\FacilitySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\models\AuditTrail;
use backend\models\User;
use yii\db\Expression;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * FacilitiesController implements the CRUD actions for Facility model.
 */
class FacilitiesController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete', 'view',
                    'services', 'delete-service', 'approve-facility-province',
                    'approve-facility-national', 'unapprove'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'view',
                            'services', 'delete-service', 'approve-facility-province',
                            'approve-facility-national', 'unapprove'],
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
     * Lists all Facility models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage facilities') ||
                User::userIsAllowedTo('View facilities')) {
            $searchModel = new FacilitySearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if (Yii::$app->request->post('hasEditable')) {
                $userId = Yii::$app->request->post('editableKey');
                $model = Facility::findOne($userId);
                $out = Json::encode(['output' => '', 'message' => '']);
                $posted = current($_POST['Facility']);
                $post = ['Facility' => $posted];
                $old_status = $model->status;

                if ($model->load($post)) {
                    if ($old_status != $model->status) {
                        $status = $model->status === 1 ? "Active from Inactive" : "Inactive from Active";
                        $log_str = "Changed facility status to: " . $status;
                    }
                    if (!empty($log_str)) {
                        $audit = new AuditTrail();
                        $audit->user = Yii::$app->user->id;
                        $audit->action = $log_str;
                        $audit->ip_address = Yii::$app->request->getUserIP();
                        $audit->user_agent = Yii::$app->request->getUserAgent();
                        $audit->save();
                    }

                    $model->updated_by = Yii::$app->user->id;
                    $model->date_updated = new Expression('NOW()');
                    $model->save(false);
                    $output = '';
                    $out = Json::encode(['output' => $output, 'message' => '']);
                }
                return $out;
            }

            //Lets filter based on logged in user type
            if (Yii::$app->user->identity->user_type == "District") {
                $dataProvider->query->andFilterWhere(['district_id' => Yii::$app->user->identity->district_id]);
            }

            if (Yii::$app->user->identity->user_type == "Province") {
                $district_ids = [];
                $districts = \backend\models\Districts::find()->where(['province_id' => Yii::$app->user->identity->province_id])->all();
                if (!empty($districts)) {
                    foreach ($districts as $id) {
                        array_push($district_ids, $id['id']);
                    }
                }

                $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
            }

            //When one filters by province
            if (!empty(Yii::$app->request->queryParams['FacilitySearch']['province_id'])) {
                $district_ids = [];
                $districts = \backend\models\Districts::find()->where(['province_id' => Yii::$app->request->queryParams['FacilitySearch']['province_id']])->all();
                if (!empty($districts)) {
                    foreach ($districts as $id) {
                        array_push($district_ids, $id['id']);
                    }
                }

                $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
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
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Displays a single Facility model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        if (User::userIsAllowedTo('Manage facilities') ||
                User::userIsAllowedTo('View facilities')) {
            $model = Facility::find()
                            ->select(['*', 'ST_AsGeoJSON(geom) as geom'])
                            ->where(["id" => $id])->one();
            return $this->render('view', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionApproveFacilityProvince($id) {
        if (User::userIsAllowedTo('Approve facility - Province')) {
            $model = Facility::find()
                            ->select(['*', 'ST_AsGeoJSON(geom) as geom'])
                            ->where(["id" => $id])->one();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                if ($model->province_approval_status == 1) {
                    $model->national_approval_status = 0;
                    $model->date_verified = new Expression('NOW()');
                    $model->date_updated = new Expression('NOW()');
                    $model->verified_by = Yii::$app->user->identity->id;
                    $model->updated_by = Yii::$app->user->identity->id;

                    if ($model->save()) {
                        //Send to national level
                        $role_model = \common\models\RightAllocation::findOne(['right' => "Approve facility - National"]);
                        if (!empty($role_model)) {
                            $user = User::findOne(["role" => $role_model->role]);
                            if ($user) {
                                $subject = "New MFL Facility:" . $model->name;
                                $msg = "";
                                $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                                $msg .= "A new MFL facility:" . $model->name . " was created and has been approved at Province level. You need to approve it to make it active<br>";
                                $msg .= "Login below to view the full details</p>";
                                if (self::sendEmail($subject, $user->email, $msg)) {
                                    Yii::$app->session->setFlash('success', 'Facility was successfully approved and sent for National approval');
                                } else {
                                    Yii::$app->session->setFlash('warning', 'Facility was successfully approved. But notification was not sent to national user for approval!');
                                }
                            } else {
                                Yii::$app->session->setFlash('warning', 'Facility was successfully approved. But notification was not sent to national user for approval!');
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', 'Facility was successfully approved. But notification was not sent to national user for approval!');
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Error occured while approvving facility. Error::' . $message);
                    }
                } else {

                    //Send notification to creator 
                    $model->national_approval_status = 2;
                    $user = User::findOne($model->created_by);

                    if ($model->save(false)) {
                        if ($user) {
                            $subject = "Attention: MFL Facility:" . $model->name;
                            $msg = "";
                            $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                            $msg .= "The MFL facility:" . $model->name . " you created needs more information. See verifier comments for more details<br>";
                            $msg .= "Login below and go to facilities to view the facility details</p>";
                            if (self::sendEmail($subject, $user->email, $msg)) {
                                
                            }
                        }
                        Yii::$app->session->setFlash('success', 'Facility creator was notified to provide more information');
                    } else {
                        $message = "";
                        foreach ($model->getErrors() as $error) {
                            $message .= $error[0];
                        }
                        Yii::$app->session->setFlash('error', 'Error occured while approving facility. Error::' . $message);
                    }
                }

                return $this->redirect(['home/home']);
            }
            return $this->render('view_1', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionApproveFacilityNational($id) {
        if (User::userIsAllowedTo('Approve facility - National')) {
            $model = Facility::find()
                            ->select(['*', 'ST_AsGeoJSON(geom) as geom'])
                            ->where(["id" => $id])->one();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {

                $model->date_updated = new Expression('NOW()');
                $model->updated_by = Yii::$app->user->identity->id;
                if ($model->national_approval_status == 1) {
                    $model->approved_by = Yii::$app->user->identity->id;
                    $model->date_approved = new Expression('NOW()');
                    $model->status = 1; //Active
                    if ($model->save()) {
                        if (!empty(Yii::$app->params['amqQueues'])) {
                            $district_model = \backend\models\Districts::findOne($model->district_id);
                            $province = !empty($district_model) ? \backend\models\Provinces::findOne($district_model->province_id)->name : "";
                            $status_arr = [1 => "Fixed", 2 => "Mobile", 3 => "telemedicine"];
                            $mobility = $status_arr[$model->mobility_status];
                            //We publish the facility to rabbitMQ
                            $msg = [
                                'action' => "CREATE",
                                'facilityId' => $model->id,
                                'hims_code' => $model->hims_code,
                                'smartcare_code' => $model->smartcare_code,
                                'elmis_code' => $model->elmis_code,
                                'hpcz_code' => $model->hpcz_code,
                                'disa_code' => $model->disa_code,
                                'name' => $model->name,
                                'number_of_households' => $model->number_of_households,
                                'accesibility' => $model->accesibility,
                                'plot_no' => $model->plot_no,
                                'street' => $model->street,
                                'town' => $model->town,
                                'postalAddress' => $model->postal_address,
                                'physical_address' => $model->physical_address,
                                'email' => $model->email,
                                'phone' => $model->phone,
                                'mobile' => $model->mobile,
                                'fax' => $model->fax,
                                'catchmentPopulationHeadCount' => $model->catchment_population_head_count,
                                'catchmentPopulationCso' => $model->catchment_population_cso,
                                'longitude' => $model->longitude,
                                'latitude' => $model->latitude,
                                'mobilityStatus' => $mobility,
                                'facilityType' => !empty($model->type) ? \backend\models\Facilitytype::findOne($model->type)->name : "",
                                'locationType' => !empty($model->location) ? \backend\models\LocationType::findOne($model->location)->name : "",
                                'operationStatus' => !empty($model->operational_status) ? \backend\models\Operationstatus::findOne($model->operational_status)->name : "",
                                'ownership' => !empty($model->ownership) ? \backend\models\FacilityOwnership::findOne($model->ownership)->name : "",
                                'ownershipType' => $model->ownership_type == 1 ? "Public" : "Private",
                                'province' => $province,
                                'district' => !empty($district_model) ? $district_model->name : "",
                                'constituency' => !empty($model->constituency_id) ? \backend\models\Constituency::findOne($model->constituency_id)->name : "",
                                'ward' => !empty($model->ward_id) ? \backend\models\Wards::findOne($model->ward_id)->name : "",
                            ];

                            foreach (Yii::$app->params['amqQueues'] as $queue) {
                                self::publishAMQMsg($msg, $queue);
                            }
                        }
                        Yii::$app->session->setFlash('success', 'Facility was approved successfully');
                        return $this->redirect(['home/home']);
                    } else {
                        $message = "";
                        foreach ($model->getErrors() as $error) {
                            $message .= $error[0];
                        }
                        Yii::$app->session->setFlash('error', 'Error occured while approving facility. Error::' . $message);
                    }
                } else {
                    $model->province_approval_status = 2;
                    $model->national_approval_status = 2;
                    if ($model->save(false)) {
                        //Send notification to creator 
                        $user = User::findOne($model->created_by);
                        if ($user) {
                            $subject = "Attention: MFL Facility:" . $model->name;
                            $msg = "";
                            $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                            $msg .= "The MFL facility:" . $model->name . " you created needs more information. See approver comments for more details<br>";
                            $msg .= "Login below and go to facilities to view the facility details</p>";
                            self::sendEmail($subject, $user->email, $msg);
                        }
                    }
                    Yii::$app->session->setFlash('success', 'Facility creator was notified to provide more information');
                    return $this->redirect(['home/home']);
                }
            }
            return $this->render('view_1_1', [
                        'model' => $model,
            ]);
        } else {
            $message = "";
            foreach ($model->getErrors() as $error) {
                $message .= $error[0];
            }
            Yii::$app->session->setFlash('error', 'Error occured while approving facility. Error::' . $message);
        }
        return $this->redirect(['home/home']);
    }

    /**
     * Action for sending back the facility back to district
     * @param type $id
     */
    public function actionUnapprove($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = $this->findModel($id);
            $model->date_updated = new Expression('NOW()');
            $model->updated_by = Yii::$app->user->identity->id;
            $model->province_approval_status = 2;
            $model->national_approval_status = 2;
            $model->status = 0; //pending provincial approval
            if ($model->save(false)) {
                //We log action taken
                $audit = new AuditTrail();
                $audit->user = Yii::$app->user->id;
                $audit->action = "Sent Facility: " . $model->name . " back to district for editing";
                $audit->ip_address = Yii::$app->request->getUserIP();
                $audit->user_agent = Yii::$app->request->getUserAgent();
                $audit->save();
                $user = User::findOne($model->created_by);

                if ($user) {
                    $subject = "Editing MFL Facility:" . $model->name;
                    $msg = "";
                    $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                    $msg .= "MFL facility:" . $model->name . " has been enabled for editting.<br>";
                    $msg .= "Login below and go to facilities to edit facility details</p>";
                    self::sendEmail($subject, $user->email, $msg);
                }

                Yii::$app->session->setFlash('success', 'Facility was added successfully sendt back to district user. District user will have to login to edit the facility');
            } else {
                $message = "";
                foreach ($model->getErrors() as $error) {
                    $message .= $error[0];
                }
                Yii::$app->session->setFlash('error', 'Error occured while sending facility back to district. Error::' . $message);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Creates a new Facility model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new Facility();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                //get latitude and longitude and create a geom array
                if (!empty($model->coordinates)) {
                    $arr = explode(",", $model->coordinates);
                    $model->latitude = $arr[0];
                    $model->longitude = $arr[1];
                    $model->geom = new Expression("ST_SetSRID(ST_GeomFromText(:point),4326)",
                            array(':point' => 'POINT(' . $model->longitude . ' ' . $model->latitude . ')'));
                }
                if (empty($model->coordinates) && (!empty($model->latitude) && !empty($model->longitude))) {
                    $model->geom = new Expression("ST_SetSRID(ST_GeomFromText(:point),4326)",
                            array(':point' => 'POINT(' . $model->longitude . ' ' . $model->latitude . ')'));
                }

                $model->date_created = new Expression('NOW()');
                $model->date_updated = new Expression('NOW()');
                $model->created_by = Yii::$app->user->identity->id;
                $model->updated_by = Yii::$app->user->identity->id;
                $model->province_approval_status = 0;
                $model->national_approval_status = 0;
                $model->status = 0; //pending provincial approval
                //all facilities created through MFL are public=1
                //All those from HPCZ are private=2
                $model->ownership_type = 1;

                if ($model->save()) {
                    //We log action taken
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added Facility " . $model->name;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    //We notify province that a facility has been approved for their review
                    $role_model = \common\models\RightAllocation::findOne(['right' => "Approve facility - Province"]);
                    if (!empty($role_model)) {
                        $facility_province = \backend\models\Districts::findOne($model->district_id);
                        $user = User::findOne(["role" => $role_model->role, "province_id" => $facility_province->province_id]);
                        if ($user) {
                            $subject = "New MFL Facility:" . $model->name;
                            $msg = "";
                            $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                            $msg .= "A new MFL facility:" . $model->name . " has been created. You need to verify the submitted information as a province "
                                    . "user before it can be approved at National level<br>";
                            $msg .= "Login below to view the full details</p>";
                            if (self::sendEmail($subject, $user->email, $msg)) {
                                Yii::$app->session->setFlash('success', 'Facility was added successfully.You can add Services after it has been approved');
                            } else {
                                Yii::$app->session->setFlash('warning', 'Facility was added successfully. But notification was not sent to province user for verification!');
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', 'Facility was added successfully. But notification was not sent to province user for verification!');
                        }
                    } else {
                        Yii::$app->session->setFlash('warning', 'Facility was added successfully. But notification was not sent to province user for verification!');
                    }

                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    $message = "";
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding facility. Error::' . $message);
                }
            }

            return $this->render('create', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public static function sendEmail($subject, $email, $msg) {
        $response = false;
        try {
            return Yii::$app
                            ->mailer
                            ->compose(
                                    [
                                        'html' => 'facilityApproval-html',
                                        'text' => 'passwordResetToken-text_1'
                                    ], ['msg' => $msg]
                            )
                            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['supportEmail']])
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
        } catch (Exception $ex) {
            return $response;
        }
    }

    /**
     * Updates an existing Facility model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = $this->findModel($id);
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            
            $old_lat = $model->latitude;
            $old_lng = $model->longitude;
            $old_geom = $model->geom;
            $old_provincial_status = $model->province_approval_status;
            $old_national_approval_status = $model->national_approval_status;
            $old_created_by = $model->created_by;

            if ($model->load(Yii::$app->request->post())) {

                if (!empty($model->coordinates)) {
                    $arr = explode(",", $model->coordinates);
                    $model->latitude = $arr[0];
                    $model->longitude = $arr[1];
                    if ($old_lat !== $model->latitude &&
                            $old_lng !== $model->longitude) {
                        if ($model->longitude !== Yii::$app->params['center_lng'] &&
                                $model->latitude !== Yii::$app->params['center_lat']) {
                            $model->geom = new Expression("ST_SetSRID(ST_GeomFromText(:point),4326)",
                                    array(':point' => 'POINT(' . $model->longitude . ' ' . $model->latitude . ')'));
                        }
                    }
                } else {
                    if (empty($model->coordinates) && (!empty($model->latitude) && !empty($model->longitude))) {
                        $model->geom = new Expression("ST_SetSRID(ST_GeomFromText(:point),4326)",
                                array(':point' => 'POINT(' . $model->longitude . ' ' . $model->latitude . ')'));
                    } else {
                        $model->geom = $old_geom;
                    }
                }

                $model->date_updated = new Expression('NOW()');
                $model->updated_by = Yii::$app->user->identity->id;
                if (empty($old_created_by)) {
                    $model->created_by = Yii::$app->user->identity->id;
                }

                if ($model->ownership_type == 2) {
                    $model->created_by = Yii::$app->user->identity->id;
                }


                if ($old_provincial_status === 2) {
                    $model->province_approval_status = 0;
                    $model->national_approval_status = 0;
                }
                
                if ($old_national_approval_status === 2) {
                    $model->province_approval_status = 0;
                    $model->national_approval_status = 0;
                }
                
                if ($model->save()) {

                    //We log action taken
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Updated Facility: " . $model->name . "' details";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    if ($old_provincial_status === 2) {
                        //We notify province that a facility has been approved for their review
                        $role_model = \common\models\RightAllocation::findOne(['right' => "Approve facility - Province"]);
                        if (!empty($role_model)) {
                            $user = User::findOne(["role" => $role_model->role]);
                            if ($user) {
                                $subject = "New MFL Facility:" . $model->name;
                                $msg = "";
                                $msg .= "<p>Hello! " . $user->first_name . " " . $user->last_name . "<br>";
                                $msg .= "A MFL facility:" . $model->name . " which was rejected for approval for more information has been updated. You need to verify the submitted information as a province "
                                        . "user before it can be approved at National level<br>";
                                $msg .= "Login below to view the full details</p>";
                                if (self::sendEmail($subject, $user->email, $msg)) {
                                    Yii::$app->session->setFlash('success', 'Facility was updated successfully.You can add Services after it has been approved');
                                } else {
                                    Yii::$app->session->setFlash('warning', 'Facility was updated successfully. But notification was not sent to province user for verification!');
                                }
                            } else {
                                Yii::$app->session->setFlash('warning', 'Facility was updated successfully. But notification was not sent to province user for verification!');
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', 'Facility was updated successfully. But notification was not sent to province user for verification!');
                        }
                    } else {
                        if (!empty(Yii::$app->params['amqQueues'])) {
                            $district_model = \backend\models\Districts::findOne($model->district_id);
                            $province = !empty($district_model) ? \backend\models\Provinces::findOne($district_model->province_id)->name : "";
                            $status_arr = [1 => "Fixed", 2 => "Mobile", 3 => "telemedicine"];
                            $mobility = $status_arr[$model->mobility_status];
                            //We publish the facility to rabbitMQ
                            $msg = [
                                'action' => "UPDATE",
                                'facilityId' => $model->id,
                                'hims_code' => $model->hims_code,
                                'smartcare_code' => $model->smartcare_code,
                                'elmis_code' => $model->elmis_code,
                                'hpcz_code' => $model->hpcz_code,
                                'disa_code' => $model->disa_code,
                                'name' => $model->name,
                                'number_of_households' => $model->number_of_households,
                                'accesibility' => $model->accesibility,
                                'plot_no' => $model->plot_no,
                                'street' => $model->street,
                                'town' => $model->town,
                                'postalAddress' => $model->postal_address,
                                'physical_address' => $model->physical_address,
                                'email' => $model->email,
                                'phone' => $model->phone,
                                'mobile' => $model->mobile,
                                'fax' => $model->fax,
                                'catchmentPopulationHeadCount' => $model->catchment_population_head_count,
                                'catchmentPopulationCso' => $model->catchment_population_cso,
                                'longitude' => $model->longitude,
                                'latitude' => $model->latitude,
                                'mobilityStatus' => $mobility,
                                'facilityType' => !empty($model->type) ? \backend\models\Facilitytype::findOne($model->type)->name : "",
                                'locationType' => !empty($model->location) ? \backend\models\LocationType::findOne($model->location)->name : "",
                                'operationStatus' => !empty($model->operational_status) ? \backend\models\Operationstatus::findOne($model->operational_status)->name : "",
                                'ownership' => !empty($model->ownership) ? \backend\models\FacilityOwnership::findOne($model->ownership)->name : "",
                                'ownershipType' => $model->ownership_type == 1 ? "Public" : "Private",
                                'province' => $province,
                                'district' => !empty($district_model) ? $district_model->name : "",
                                'constituency' => !empty($model->constituency_id) ? \backend\models\Constituency::findOne($model->constituency_id)->name : "",
                                'ward' => !empty($model->ward_id) ? \backend\models\Wards::findOne($model->ward_id)->name : "",
                            ];

                            foreach (Yii::$app->params['amqQueues'] as $queue) {
                                self::publishAMQMsg($msg, $queue);
                            }
                        }
                        Yii::$app->session->setFlash('success', 'Facility was updated successfully');
                    }
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    $message = "";
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while updating facility. Error::' . $message);
                }
            }

            return $this->render('update', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionServices() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new \backend\models\MFLFacilityServices();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                $count = 0;
                foreach ($model->service_id as $key => $value) {
                    $_model = new \backend\models\MFLFacilityServices();
                    $serviceDetails = \backend\models\FacilityService::findOne($value);
                    if (!empty($serviceDetails)) {
                        $_model->service_area_id = $serviceDetails->category_id;
                        $_model->service_id = $serviceDetails->id;
                        $_model->facility_id = $model->facility_id;
                        $_model->save(false);
                    }

                    $count++;
                }

                if ($count > 0) {
                    $facility = \backend\models\Facility::findOne($model->facility_id)->name;
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added " . $count . " services to facility: " . $facility;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility services were successfully added.');
                } else {
                    $message = '';
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding services to facility. Error:' . $message);
                }
                return $this->redirect(['view', 'id' => $model->facility_id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Deletes an existing Facility model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        if (User::userIsAllowedTo('Remove facility')) {
            $model = $this->findModel($id);
            $name = $model->name;
            try {
//We delete other attachments to the facility before deleting facility
                \backend\models\MFLFacilityServices::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityRatings::deleteAll(['facility_id' => $id]);
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility $name from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility $name was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility $name could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility $name could not be removed. Please try again!");
            }

            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionDeleteService($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = \backend\models\MFLFacilityServices::findOne($id);
            $facility_id = $model->facility_id;
            try {
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility service from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility service was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility service could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility service could not be removed. Please try again!");
            }

            return $this->redirect(['view', 'id' => $facility_id]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Finds the Facility model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Facility the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Facility::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDeleteOperatinghour($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = \backend\models\MFLFacilityOperatingHours::findOne($id);
            $facility_id = $model->facility_id;
            try {
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility operating hour from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility operating hour was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility operating hour could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility operating hour could not be removed. Please try again!");
            }

            return $this->redirect(['view', 'id' => $facility_id]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionOperatinghour() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new \backend\models\MFLFacilityOperatingHours();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $op_hour = \backend\models\Operatinghours::findOne($model->operatinghours_id)->name;
                    $facility = \backend\models\Facility::findOne($model->facility_id)->name;
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added operating hour '" . $op_hour . "' to facility: " . $facility;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility operating hour was successfully added.');
                } else {
                    $message = '';
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding operating hour to facility. Error:' . $message);
                }
                return $this->redirect(['view', 'id' => $model->facility_id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public static function publishAMQMsg($message, $queue) {
        $connection = new AMQPStreamConnection(Yii::$app->params['amqHost'], Yii::$app->params['amqPort'], Yii::$app->params['amqUsername'], Yii::$app->params['amqPassword']);
        $channel = $connection->channel();

        $channel->queue_declare($queue, false, TRUE, false, false);
        //We send json encoded messages
        $msg = new AMQPMessage(\GuzzleHttp\json_encode($message));
        $channel->basic_publish($msg, '', $queue);
        //We close the channel and connection 
        $channel->close();
        $connection->close();
    }

}
