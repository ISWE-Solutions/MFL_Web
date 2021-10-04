<?php

namespace backend\controllers;

use Yii;
use backend\models\MFLFacility;
use backend\models\MFLFacilitySearch;
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
 * FacilityController implements the CRUD actions for MFLFacility model.
 */
class FacilityController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'delete', 'view',
                    'services', 'operatinghour', 'infrastructure', 'equipment',
                    'delete-equipment', 'delete-infrastructure', 'delete-service', 'delete-operatinghour'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'view',
                            'services', 'operatinghour', 'infrastructure', 'equipment',
                            'delete-equipment', 'delete-infrastructure', 'delete-service', 'delete-operatinghour'],
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
     * Lists all MFLFacility models.
     * @return mixed
     */
    public function actionIndex() {
        if (User::userIsAllowedTo('Manage facilities') ||
                User::userIsAllowedTo('View facilities')) {
            $searchModel = new MFLFacilitySearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            if (!empty(Yii::$app->request->queryParams['MFLFacilitySearch']['province_id'])) {
                $district_ids = [];
                $districts = \backend\models\Districts::find()->where(['province_id' => Yii::$app->request->queryParams['MFLFacilitySearch']['province_id']])->all();
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
     * Displays a single MFLFacility model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        if (User::userIsAllowedTo('Manage facilities') ||
                User::userIsAllowedTo('View facilities')) {
            $model = MFLFacility::find()
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

    /**
     * Creates a new MFLFacility model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new MFLFacility();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {

                //get latitude and longitude and create a geom array
                if (!empty($model->geom)) {
                    $arr = explode(",", $model->geom);
                    $model->latitude = $arr[0];
                    $model->longitude = $arr[1];
                    $model->geom = new Expression("ST_SetSRID(ST_GeomFromText(:point),4326)",
                            array(':point' => 'POINT(' . $model->longitude . ' ' . $model->latitude . ')'));
                }
                $model->timestamp = new Expression('NOW()');
                $model->updated = new Expression('NOW()');
                if ($model->save()) {
                    if (!empty(Yii::$app->params['amqQueues'])) {
                        //We publish the creation of the facility
                        $msg = [
                            'action' => "CREATE",
                            'facilityId' => $model->id,
                            'DHIS2UId' => $model->DHIS2_UID,
                            'HMISCode' => $model->HMIS_code,
                            'smartcareGUId' => $model->smartcare_GUID,
                            'eLMISId' => $model->eLMIS_ID,
                            'iHRISId' => $model->iHRIS_ID,
                            'name' => $model->name,
                            'numberOfBeds' => $model->number_of_beds,
                            'numberOfCots' => $model->number_of_cots,
                            'numberOfNurses' => $model->number_of_nurses,
                            'numberOfDoctors' => $model->number_of_doctors,
                            'addressLine1' => $model->address_line1,
                            'addressLine2' => $model->address_line2,
                            'postalAddress' => $model->postal_address,
                            'webAddress' => $model->web_address,
                            'email' => $model->email,
                            'phone' => $model->phone,
                            'mobile' => $model->mobile,
                            'fax' => $model->fax,
                            'catchmentPopulationHeadCount' => $model->catchment_population_head_count,
                            'catchmentPopulationCso' => $model->catchment_population_cso,
                            'longitude' => $model->longitude,
                            'latitude' => $model->latitude,
                            'administrativeUnit' => !empty($model->administrative_unit_id) ? \backend\models\MFLAdministrativeunit::findOne($model->administrative_unit_id)->name : "",
                            'facilityType' => !empty($model->facility_type_id) ? \backend\models\Facilitytype::findOne($model->facility_type_id)->name : "",
                            'locationType' => !empty($model->location_type_id) ? \backend\models\LocationType::findOne($model->location_type_id)->name : "",
                            'operationStatus' => !empty($model->operation_status_id) ? \backend\models\Operationstatus::findOne($model->operation_status_id)->name : "",
                            'ownership' => !empty($model->ownership_id) ? \backend\models\FacilityOwnership::findOne($model->ownership_id)->name : "",
                            'province' => !empty($model->province_id) ? \backend\models\Provinces::findOne($model->province_id)->name : "",
                            'district' => !empty($model->district_id) ? \backend\models\Districts::findOne($model->district_id)->name : "",
                            'constituency' => !empty($model->constituency_id) ? \backend\models\Constituency::findOne($model->constituency_id)->name : "",
                            'ward' => !empty($model->ward_id) ? \backend\models\Wards::findOne($model->ward_id)->name : "",
                        ];

                        foreach (Yii::$app->params['amqQueues'] as $queue) {
                            self::publishAMQMsg($msg, $queue);
                        }
                    }

                    //We log action taken
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added Facility " . $model->name;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility was added successfully.You can add other details now i.e. Services,Operating hours etc');
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

    /**
     * Updates an existing MFLFacility model.
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
            if ($model->load(Yii::$app->request->post())) {
                if (!empty($model->geom)) {
                    $arr = explode(",", $model->geom);
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
                }
                $model->updated = new Expression('NOW()');
                if ($model->save()) {
                    if (!empty(Yii::$app->params['amqQueues'])) {
                        //We publish the update to rabbitMQ
                        $msg = [
                            'action' => "CREATE",
                            'facilityId' => $model->id,
                            'DHIS2UId' => $model->DHIS2_UID,
                            'HMISCode' => $model->HMIS_code,
                            'smartcareGUId' => $model->smartcare_GUID,
                            'eLMISId' => $model->eLMIS_ID,
                            'iHRISId' => $model->iHRIS_ID,
                            'name' => $model->name,
                            'numberOfBeds' => $model->number_of_beds,
                            'numberOfCots' => $model->number_of_cots,
                            'numberOfNurses' => $model->number_of_nurses,
                            'numberOfDoctors' => $model->number_of_doctors,
                            'addressLine1' => $model->address_line1,
                            'addressLine2' => $model->address_line2,
                            'postalAddress' => $model->postal_address,
                            'webAddress' => $model->web_address,
                            'email' => $model->email,
                            'phone' => $model->phone,
                            'mobile' => $model->mobile,
                            'fax' => $model->fax,
                            'catchmentPopulationHeadCount' => $model->catchment_population_head_count,
                            'catchmentPopulationCso' => $model->catchment_population_cso,
                            'longitude' => $model->longitude,
                            'latitude' => $model->latitude,
                            'administrativeUnit' => !empty($model->administrative_unit_id) ? \backend\models\MFLAdministrativeunit::findOne($model->administrative_unit_id)->name : "",
                            'facilityType' => !empty($model->facility_type_id) ? \backend\models\Facilitytype::findOne($model->facility_type_id)->name : "",
                            'locationType' => !empty($model->location_type_id) ? \backend\models\LocationType::findOne($model->location_type_id)->name : "",
                            'operationStatus' => !empty($model->operation_status_id) ? \backend\models\Operationstatus::findOne($model->operation_status_id)->name : "",
                            'ownership' => !empty($model->ownership_id) ? \backend\models\FacilityOwnership::findOne($model->ownership_id)->name : "",
                            'province' => !empty($model->province_id) ? \backend\models\Provinces::findOne($model->province_id)->name : "",
                            'district' => !empty($model->district_id) ? \backend\models\Districts::findOne($model->district_id)->name : "",
                            'constituency' => !empty($model->constituency_id) ? \backend\models\Constituency::findOne($model->constituency_id)->name : "",
                            'ward' => !empty($model->ward_id) ? \backend\models\Wards::findOne($model->ward_id)->name : "",
                        ];

                        foreach (Yii::$app->params['amqQueues'] as $queue) {
                            self::publishAMQMsg($msg, $queue);
                        }
                    }

                    //We log action taken
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Updated Facility: " . $model->name . "' details";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility was updated successfully.You can update other details now i.e. Services,Operating hours etc');
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

    public function actionServices() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new \backend\models\MFLFacilityServices();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $service = \backend\models\FacilityService::findOne($model->service_id)->name;
                    $facility = \backend\models\MFLFacility::findOne($model->facility_id)->name;
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added service  " . $service . " to facility: " . $facility;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility service was successfully added.');
                } else {
                    $message = '';
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding service to facility. Error:' . $message);
                }
                return $this->redirect(['view', 'id' => $model->facility_id]);
            }
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
                    $facility = \backend\models\MFLFacility::findOne($model->facility_id)->name;
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

    public function actionInfrastructure() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new \backend\models\MFLFacilityInfrastructure();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $infra = \backend\models\MFLInfrastructure::findOne($model->infrastructure_id)->name;
                    $facility = \backend\models\MFLFacility::findOne($model->facility_id)->name;
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added infrastructure '" . $infra . "' to facility: " . $facility;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility infrastructure was successfully added.');
                } else {
                    $message = '';
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding infrastructure to facility. Error:' . $message);
                }
                return $this->redirect(['view', 'id' => $model->facility_id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionEquipment() {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = new \backend\models\MFLFacilityEquipment();
            if (Yii::$app->request->isAjax) {
                $model->load(Yii::$app->request->post());
                return Json::encode(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $infra = \backend\models\Equipment::findOne($model->equipment_id)->name;
                    $facility = \backend\models\MFLFacility::findOne($model->facility_id)->name;
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Added equipment '" . $infra . "' to facility: " . $facility;
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', 'Facility equipment was successfully added.');
                } else {
                    $message = '';
                    foreach ($model->getErrors() as $error) {
                        $message .= $error[0];
                    }
                    Yii::$app->session->setFlash('error', 'Error occured while adding equipment to facility. Error:' . $message);
                }
                return $this->redirect(['view', 'id' => $model->facility_id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Deletes an existing MFLFacility model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = $this->findModel($id);
            $name = $model->name;
            try {
                //We delete other attachments to the facility before deleting facility
                \backend\models\MFLFacilityEquipment::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityInfrastructure::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityLabLevel::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityOperatingHours::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityServices::deleteAll(['facility_id' => $id]);
                \backend\models\MFLFacilityRatings::deleteAll(['facility_id' => $id]);
                if ($model->delete()) {
                    if (!empty(Yii::$app->params['amqQueues'])) {
                        //We publish the deletion to rabbitMQ
                        $msg = [
                            'action' => "DELETE",
                            'facilityID' => $id
                        ];
                        foreach (Yii::$app->params['amqQueues'] as $queue) {
                            self::publishAMQMsg($msg, $queue);
                        }
                    }

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

    public function actionDeleteInfrastructure($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = \backend\models\MFLFacilityInfrastructure::findOne($id);
            $facility_id = $model->facility_id;
            try {
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility infrastructure from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility infrastructure was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility infrastructure could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility infrastructure could not be removed. Please try again!");
            }

            return $this->redirect(['view', 'id' => $facility_id]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    public function actionDeleteEquipment($id) {
        if (User::userIsAllowedTo('Manage facilities')) {
            $model = \backend\models\MFLFacilityEquipment::findOne($id);
            $facility_id = $model->facility_id;
            try {
                if ($model->delete()) {
                    $audit = new AuditTrail();
                    $audit->user = Yii::$app->user->id;
                    $audit->action = "Removed Facility equipment from the system.";
                    $audit->ip_address = Yii::$app->request->getUserIP();
                    $audit->user_agent = Yii::$app->request->getUserAgent();
                    $audit->save();
                    Yii::$app->session->setFlash('success', "Facility equipment was successfully removed.");
                } else {
                    Yii::$app->session->setFlash('error', "Facility equipment could not be removed. Please try again!");
                }
            } catch (yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('error', "Facility equipment could not be removed. Please try again!");
            }

            return $this->redirect(['view', 'id' => $facility_id]);
        } else {
            Yii::$app->session->setFlash('error', 'You are not authorised to perform that action.');
            return $this->redirect(['home/home']);
        }
    }

    /**
     * Finds the MFLFacility model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MFLFacility the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = MFLFacility::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
