<?php

namespace backend\controllers;

use Yii;
use yii\rest\ActiveController;
use backend\models\Authentication;
use backend\models\User;

class AuthController extends ActiveController {

    public $modelClass = 'backend\models\Authentication';
    private $data = [
        "firstName" => "",
        "surname" => "",
        "email" => "",
        "status" => "",
        "districtId" => "",
        "provinceId" => "",
    ];

    /**
     * Define behaviors for each action
     * @return type
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'authenticate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Receive Authentication payload
     * @return array
     */
    public function actionAuthenticate() {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
        $authResponse = [
            "success" => false,
            "data" => $this->data,
            "message" => "Invalid Email/Password provided!",
        ];
        $model = new Authentication(json_decode(Yii::$app->request->getRawBody(), true));

        if (!empty($model) && (!empty($model->email) && !empty($model->password))) {
            $user = User::findByUsername($model->email);
            if (!empty($user)) {
                $authResponse = $this->authenticate($user, $model->password);
            }
        }
        
        return $authResponse;
    }

    /**
     * Try to actually authenticate
     * @param type $user
     * @param type $password
     * @return type
     */
    private function authenticate($user, $password) {
        if (Yii::$app->security->validatePassword($password . $user->auth_key, $user->password)) {

            $this->data = [
                "firstName" => $user->first_name,
                "surname" => $user->last_name,
                "email" => $user->email,
                "status" => "Active",
                "districtId" => $user->district_id,
                "provinceId" => $user->province_id,
            ];
            
            return
                    [
                        "success" => true,
                        "data" => $this->data,
                        "message" => "Success",
            ];
        } else {
            return
                    [
                        "success" => false,
                        "data" => $this->data,
                        "message" => "Invalid Username/Password provided!",
            ];
        }
    }

}
