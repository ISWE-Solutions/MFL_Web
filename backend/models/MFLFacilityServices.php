<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "MFL_facility_services".
 *
 * @property int $id
 * @property int $facility_id
 * @property int $service_id
 *
 * @property MFLFacility $facility
 * @property MFLService $service
 */
class MFLFacilityServices extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_facility_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['facility_id', 'service_id'], 'required'],
            [['facility_id', 'service_id'], 'default', 'value' => null],
            [['facility_id', 'service_id', 'service_area_id'], 'integer'],
            // [['facility_id', 'service_id'], 'unique', 'targetAttribute' => ['facility_id', 'service_id']],
            ['service_id', 'unique', 'when' => function ($model) {
                    return $model->isAttributeChanged('service_id') && !empty(self::findOne(['service_id' => $model->service_id, "facility_id" => $model->facility_id])) ? TRUE : FALSE;
                }, 'message' => 'Service already exist for this facility!'],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facility::className(), 'targetAttribute' => ['facility_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => FacilityService::className(), 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'facility_id' => 'Facility',
            'service_id' => 'Service',
            'service_area_id' => 'Service area',
        ];
    }

    /**
     * Gets query for [[Facility]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacility() {
        return $this->hasOne(Facility::className(), ['id' => 'facility_id']);
    }

    /**
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService() {
        return $this->hasOne(FacilityService::className(), ['id' => 'service_id']);
    }

    public static function getServices($id) {
        $data = [];
        $service_area = \backend\models\FacilityServicecategory::getCategoryList();
        $facility_services = \backend\models\MFLFacilityServices::find()->where(['facility_id' => $id])->asArray()->all();
        if (!empty($service_area)) {

            foreach ($service_area as $area) {
                $servicesArray = [];
                $out = [];
                if (!empty($facility_services)) {
                    $service_arr = [];
                    foreach ($facility_services as $service) {
                        array_push($service_arr, $service['service_id']);
                    }
                    $out = \backend\models\FacilityService::find()
                            ->select(['id', 'name'])
                            ->where(['category_id' => $area['id']])
                            ->andWhere(['NOT IN', 'id', $service_arr])
                            ->asArray()
                            ->all();
                } else {
                    $out = \backend\models\FacilityService::find()
                            ->select(['id', 'name'])
                            ->where(['category_id' => $area['id']])
                            ->asArray()
                            ->all();
                }

                if (!empty($out)) {
                    foreach ($out as $service) {
                        $servicesArray[$service['id']] = $service['name'];
                    }
                }

                $data[$area['name']] = $servicesArray;
            }
        }

        return $data;
    }

}
