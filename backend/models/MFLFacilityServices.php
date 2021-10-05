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
            ['service_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('service_id') && !empty(self::findOne(['service_id' => $model->service_id, "facility_id" => $model->facility_id])) ? TRUE : FALSE;
                }, 'message' => 'Service already exist for this facility!'],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => MFLFacility::className(), 'targetAttribute' => ['facility_id' => 'id']],
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
        return $this->hasOne(MFLFacility::className(), ['id' => 'facility_id']);
    }

    /**
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService() {
        return $this->hasOne(FacilityService::className(), ['id' => 'service_id']);
    }

}
