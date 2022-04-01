<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "MFL_facility_infrastructure".
 *
 * @property int $id
 * @property int $facility_id
 * @property int $infrastructure_id
 *
 * @property MFLFacility $facility
 * @property MFLInfrastructure $infrastructure
 */
class MFLFacilityInfrastructure extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_facility_infrastructure';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['facility_id', 'infrastructure_id'], 'required'],
            [['facility_id', 'infrastructure_id'], 'default', 'value' => null],
            [['facility_id', 'infrastructure_id'], 'integer'],
            [['value'], 'string'],
            ['infrastructure_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('infrastructure_id') &&
                            !empty(self::findOne(['infrastructure_id' => $model->infrastructure_id,
                                        "facility_id" => $model->facility_id, "value" => $model->value])) ? TRUE : FALSE;
                }, 'message' => 'Infrastructure already exist for this facility with the same value!'],
            //[['facility_id', 'infrastructure_id'], 'unique', 'targetAttribute' => ['facility_id', 'infrastructure_id']],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facility::className(), 'targetAttribute' => ['facility_id' => 'id']],
            [['infrastructure_id'], 'exist', 'skipOnError' => true, 'targetClass' => MFLInfrastructure::className(), 'targetAttribute' => ['infrastructure_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'facility_id' => 'Facility',
            'infrastructure_id' => 'Infrastructure',
            'value' => 'Value',
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
     * Gets query for [[Infrastructure]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInfrastructure() {
        return $this->hasOne(MFLInfrastructure::className(), ['id' => 'infrastructure_id']);
    }

}
