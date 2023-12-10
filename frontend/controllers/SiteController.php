<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use common\models\LoginForm;
use yii\filters\AccessControl;
use frontend\models\SignupForm;
use backend\models\Constituency;
use frontend\models\ContactForm;
use yii\data\ActiveDataProvider;
use frontend\models\VerifyEmailForm;
use yii\web\BadRequestHttpException;
use frontend\models\ResetPasswordForm;
use yii\base\InvalidArgumentException;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResendVerificationEmailForm;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $totalOperatingFacilities = 0;
        $Facility_model = new \frontend\models\Facility();
        $searchModel = new \frontend\models\FacilitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $connection = Yii::$app->getDb();

        //For graph filtering
        $pie_series = [];
        $column_series = [];
        $data = [];
        $data1 = [];
        $labels = [];
        $pie_series1 = [];
        $column_series1 = [];
        $data2 = [];
        $data3 = [];
        $labels1 = [];
        $province_counts = "";

        $opstatus_id = "";
        $facility_model = "";
        $operation_status_model = \backend\models\Operationstatus::findOne(['shared_id' => 1]);
        //We assume facility operation status name "Operational" 
        //will never be renamed/deleted otherwise the system breaks
        //We get facilities by operating status and type
        if (!empty($operation_status_model)) {
            $opstatus_id = $operation_status_model->id;
            $facility_model = \backend\models\Facility::find()->cache(Yii::$app->params['cache_duration'])
                ->select(['type', 'COUNT(*) AS count'])
                ->where(['operational_status' => $operation_status_model->id])
                ->andWhere(['status' => 1])
                ->groupBy(['type'])
                ->createCommand()->queryAll();
        }

        if (!empty($operation_status_model)) {
            $province_counts = $connection->cache(function ($connection) use ($operation_status_model) {
                return $connection->createCommand('select count(f.id) as count,p.name from public."facility" f INNER JOIN 
                                            public."geography_district" d ON f.district_id=d.id INNER JOIN
                                            public."geography_province" p ON d.province_id=p.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . '
                                            group by p.name Order by p.name')
                ->queryAll();
            });
        }


        //Public
        $public_count_active = \backend\models\Facility::find()
            ->cache(Yii::$app->params['cache_duration'])
            ->where(['ownership_type' => 1])
            ->andWhere(['operational_status' => $operation_status_model->id])
            ->andWhere(['status' => 1])
            ->count();

        // Private
        $_private_count_active = \backend\models\Facility::find()
            ->cache(Yii::$app->params['cache_duration'])
            ->where(['IN', 'ownership_type', [2]])
            ->andWhere(['operational_status' => $operation_status_model->id])
            ->andWhere(['status' => 1])
            ->count();


        if (
            !empty(Yii::$app->request->queryParams['Facility']['province_id']) ||
            !empty(Yii::$app->request->queryParams['Facility']['district_id']) ||
            !empty(Yii::$app->request->queryParams['Facility']['ownership']) ||
            !empty(Yii::$app->request->queryParams['Facility']['name']) ||
            !empty(Yii::$app->request->queryParams['Facility']['type']) ||
            !empty(Yii::$app->request->queryParams['Facility']['ward_id']) ||
            !empty(Yii::$app->request->queryParams['Facility']['constituency_id'])
        ) {

         //If ward is set we filter only by ward
        if (!empty(Yii::$app->request->queryParams['Facility']['ward_id'])) {
           
            //Public
            $public_count_active = \backend\models\Facility::find()
                ->cache(Yii::$app->params['cache_duration'])
                ->where(['ownership_type' => 1])
                ->andWhere(['operational_status' => $operation_status_model->id])
                ->andWhere(['ward_id' => Yii::$app->request->queryParams['Facility']['ward_id']])
                ->andWhere(['status' => 1])
                ->count();

            // Private
            $_private_count_active = \backend\models\Facility::find()
                ->cache(Yii::$app->params['cache_duration'])
                ->where(['IN', 'ownership_type', [2]])
                ->andWhere(['operational_status' => $operation_status_model->id])
                ->andWhere(['ward_id' => Yii::$app->request->queryParams['Facility']['ward_id']])
                ->andWhere(['status' => 1])
                ->count();

            $facility_model = \backend\models\Facility::find()->cache(Yii::$app->params['cache_duration'])
                ->select(['type', 'COUNT(*) AS count'])
                ->where(['operational_status' => $operation_status_model->id])
                ->andWhere(['status' => 1])
                ->andWhere(['ward_id' => Yii::$app->request->queryParams['Facility']['ward_id']])
                ->groupBy(['type'])
                ->createCommand()->queryAll();
                $dataProvider->query->andFilterWhere(['ward_id' => Yii::$app->request->queryParams['Facility']['ward_id']]);
                $districtId = Yii::$app->request->queryParams['Facility']['district_id'];
                if (!empty($operation_status_model)) {
                    $province_counts = $connection->cache(function ($connection) use ($operation_status_model, $districtId) {

                        return $connection->createCommand('select count(f.id) as count,c.name from public."facility" f INNER JOIN 
                                            public."geography_constituency" c ON f.constituency_id=c.id INNER JOIN
                                            public."geography_district" d ON c.district_id=d.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . ' 
                                             AND f.district_id =' . $districtId . ' group by c.name Order by c.name')
                        ->queryAll();
                    });
                }
            }

            if (
                !empty(Yii::$app->request->queryParams['Facility']['constituency_id']) &&
                empty(Yii::$app->request->queryParams['Facility']['ward_id'])
            ) {
                $constituency= Yii::$app->request->queryParams['Facility']['constituency_id'];
             
                //Public
                $public_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['ownership_type' => 1])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['constituency_id' => $constituency])
                    ->andWhere(['status' => 1])
                    ->count();

                // Private
                $_private_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['IN', 'ownership_type', [2]])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['constituency_id' => $constituency])
                    ->andWhere(['status' => 1])
                    ->count();

                $facility_model = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->select(['type', 'COUNT(*) AS count'])
                    ->where(['operational_status' => $operation_status_model->id])
                    ->andWhere(['status' => 1])
                    ->andWhere(['constituency_id' => $constituency])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();
                $dataProvider->query->andFilterWhere(['constituency_id' => $constituency]);
                $districtId = Constituency::findOne($constituency)->district_id;

                if (!empty($operation_status_model)) {
                    $province_counts = $connection->cache(function ($connection) use ($operation_status_model, $districtId) {
                        return $connection->createCommand('select count(f.id) as count,c.name from public."facility" f INNER JOIN 
                                            public."geography_constituency" c ON f.constituency_id=c.id INNER JOIN
                                            public."geography_district" d ON c.district_id=d.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . ' 
                                             AND f.district_id =' . $districtId . ' group by c.name Order by c.name')
                        ->queryAll();
                    });
                }

               
            }
            if (
                !empty(Yii::$app->request->queryParams['Facility']['district_id']) &&
                (empty(Yii::$app->request->queryParams['Facility']['ward_id']) &&
                empty(Yii::$app->request->queryParams['Facility']['constituency_id']))
            ) {
                //Public
                $public_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['ownership_type' => 1])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['district_id' => Yii::$app->request->queryParams['Facility']['district_id']])
                    ->andWhere(['status' => 1])
                    ->count();

                // Private
                $_private_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['IN', 'ownership_type', [2]])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['district_id' => Yii::$app->request->queryParams['Facility']['district_id']])
                    ->andWhere(['status' => 1])
                    ->count();

                $facility_model = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->select(['type', 'COUNT(*) AS count'])
                    ->where(['operational_status' => $operation_status_model->id])
                    ->andWhere(['status' => 1])
                    ->andWhere(['district_id' => Yii::$app->request->queryParams['Facility']['district_id']])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();
                $dataProvider->query->andFilterWhere(['district_id' => Yii::$app->request->queryParams['Facility']['district_id']]);
                $districtId= Yii::$app->request->queryParams['Facility']['district_id'];
                if (!empty($operation_status_model)) {
                    $province_counts = $connection->cache(function ($connection) use ($operation_status_model, $districtId) {
                     
                        return $connection->createCommand('select count(f.id) as count,c.name from public."facility" f INNER JOIN 
                                            public."geography_constituency" c ON f.constituency_id=c.id INNER JOIN
                                            public."geography_district" d ON c.district_id=d.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . ' 
                                             AND f.district_id =' . $districtId . ' group by c.name Order by c.name')
                        ->queryAll();
                    });
                }
           
            }
            if (!empty(Yii::$app->request->queryParams['Facility']['ownership'])) {
                $dataProvider->query->andFilterWhere(['ownership' => Yii::$app->request->queryParams['Facility']['ownership']]);
            }
            if (!empty(Yii::$app->request->queryParams['Facility']['type'])) {
                $dataProvider->query->andFilterWhere(['type' => Yii::$app->request->queryParams['Facility']['type']]);
            }
            if (!empty(Yii::$app->request->queryParams['Facility']['name'])) {
                $dataProvider->query->andFilterWhere(['LIKE', 'name', Yii::$app->request->queryParams['Facility']['name']]);
            }

            if (
                !empty(Yii::$app->request->queryParams['Facility']['province_id']) &&
                (
                    empty(Yii::$app->request->queryParams['Facility']['ward_id']) &&
                    empty(Yii::$app->request->queryParams['Facility']['district_id']) &&
                    empty(Yii::$app->request->queryParams['Facility']['constituency_id']))
            ) {
               
                $district_ids = [];
                $districts = \backend\models\Districts::find()->where(['province_id' => Yii::$app->request->queryParams['Facility']['province_id']])->all();
                if (!empty($districts)) {
                    foreach ($districts as $id) {
                        array_push($district_ids, $id['id']);
                    }
                }

                //Public
                $public_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['ownership_type' => 1])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['IN', 'district_id', $district_ids])
                    ->andWhere(['status' => 1])
                    ->count();

                // Private
                $_private_count_active = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->where(['IN', 'ownership_type', [2]])
                    ->andWhere(['operational_status' => $operation_status_model->id])
                    ->andWhere(['IN', 'district_id', $district_ids])
                    ->andWhere(['status' => 1])
                    ->count();

                $facility_model = \backend\models\Facility::find()
                    ->cache(Yii::$app->params['cache_duration'])
                    ->select(['type', 'COUNT(*) AS count'])
                    ->where(['operational_status' => $operation_status_model->id])
                    ->andWhere(['status' => 1])
                    ->andWhere(['IN', 'district_id', $district_ids])
                    ->groupBy(['type'])
                    ->createCommand()->queryAll();

                $dataProvider->query->andFilterWhere(['IN', 'district_id', $district_ids]);
                if (!empty($operation_status_model)) {
                    $province_counts = $connection->cache(function ($connection) use ($operation_status_model, $district_ids) {
                    $districtIds=implode(',',$district_ids);
                        return $connection->createCommand('select count(f.id) as count,d.name from public."facility" f INNER JOIN 
                                            public."geography_district" d ON f.district_id=d.id INNER JOIN
                                            public."geography_province" p ON d.province_id=p.id INNER JOIN
                                            public."MFL_operationstatus" ops ON f.operational_status=ops.id
                                            WHERE f.status=1 AND ops.id=' . $operation_status_model->id . ' 
                                             AND f.district_id IN ('. $districtIds.') group by d.name Order by d.name')
                        ->queryAll();
                    });
                }
               
            }
        } else {
            if (
                !empty(Yii::$app->request->queryParams['Facility']) &&
                Yii::$app->request->queryParams['filter'] == "true"
            ) {
                Yii::$app->session->setFlash('error', 'Please pick a filter to filter on the map!');
            }
            $dataProvider = "";
        }




        if (!empty($province_counts)) {
            foreach ($province_counts as $model) {
                //Add to total operating facilities
                //$totalOperatingFacilities += (int) $model['count'];
                //Push pie data to array
                array_push($data2, ['name' => $model['name'], 'y' => (int) $model['count'],]);
                //Push column labels to array
                if (!in_array($model['name'], $labels1)) {
                    array_push($labels1, $model['name']);
                }
                //We push column data to array
                array_push($data3, (int) $model['count']);
            }
            //We push pie plot details to the series
            array_push($pie_series1, ['name' => 'Total', 'colorByPoint' => true, 'data' => $data2]);
            array_push($column_series1, ['name' => "Total", 'data' => $data3]);
        }



        if (!empty($facility_model)) {
            foreach ($facility_model as $model) {
                //Push pie data to array
                array_push($data, ['name' => \backend\models\Facilitytype::findOne($model['type'])->name, 'y' => (int) $model['count'],]);
                //Push column labels to array
                if (!in_array(\backend\models\Facilitytype::findOne($model['type'])->name, $labels)) {
                    array_push($labels, \backend\models\Facilitytype::findOne($model['type'])->name);
                }
                //We push column data to array
                array_push($data1, (int) $model['count']);
            }
            //We push pie plot details to the series
            array_push($pie_series, ['name' => 'Total', 'colorByPoint' => true, 'data' => $data]);
            array_push($column_series, ['name' => "Total", 'data' => $data1]);
        }

        $totalOperatingFacilities = $public_count_active + $_private_count_active;

        return $this->render('index', [
            'Facility_model' => $Facility_model,
            'dataProvider' => $dataProvider,
            '_private_count_active' => $_private_count_active,
            'public_count_active' => $public_count_active,
            'operation_status_model' => $operation_status_model,
            'totalOperatingFacilities' => $totalOperatingFacilities,
            'pie_series' => $pie_series,
            'column_series' => $column_series,
            'labels' => $labels,
            'opstatus_id' => $opstatus_id,
            'pie_series1' => $pie_series1,
            'column_series1' => $column_series1,
            'labels1' => $labels1,
        ]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    /*public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                        'model' => $model,
            ]);
        }
    }*/

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    /* public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }*/

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    /* public function actionContact() {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                        'model' => $model,
            ]);
        }
    }*/

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    /*public function actionSignup() {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
                    'model' => $model,
        ]);
    }*/

    public function actionDistrict()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            // $selected_id = $_POST['depdrop_params'];
            $selected_id = $_POST['depdrop_all_params']['selected_id'];
            if ($parents != null) {
                $prov_id = $parents[0];
                $out = \backend\models\Districts::find()
                    ->select(['id', 'name'])
                    ->where(['province_id' => $prov_id])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => $selected_id];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionConstituency()
    {
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

    public function actionWard()
    {
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
