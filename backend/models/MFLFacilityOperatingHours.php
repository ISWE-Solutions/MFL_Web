<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "MFL_facility_operating_hours".
 *
 * @property int $id
 * @property int $facility_id
 * @property int $operatinghours_id
 *
 * @property MFLFacility $facility
 * @property MFLOperatinghours $operatinghours
 */
class MFLFacilityOperatingHours extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_facility_operating_hours';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['facility_id', 'operatinghours_id'], 'required'],
            [['facility_id', 'operatinghours_id'], 'default', 'value' => null],
            [['facility_id', 'operatinghours_id'], 'integer'],
            ['operatinghours_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('operatinghours_id') && 
                            !empty(self::findOne(['operatinghours_id' => $model->operatinghours_id, 
                                "facility_id" => $model->facility_id])) ? TRUE : FALSE;
                }, 'message' => 'Operating hour already exist for this facility!'],
            //[['facility_id', 'operatinghours_id'], 'unique', 'targetAttribute' => ['facility_id', 'operatinghours_id']],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facility::className(), 'targetAttribute' => ['facility_id' => 'id']],
            [['operatinghours_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operatinghours::className(), 'targetAttribute' => ['operatinghours_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'facility_id' => 'Facility',
            'operatinghours_id' => 'Operating hour',
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
     * Gets query for [[Operatinghours]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperatinghours() {
        return $this->hasOne(MFLOperatinghours::className(), ['id' => 'operatinghours_id']);
    }

}
