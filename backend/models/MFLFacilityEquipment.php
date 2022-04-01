<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "MFL_facility_equipment".
 *
 * @property int $id
 * @property int $facility_id
 * @property int $equipment_id
 *
 * @property MFLEquipment $equipment
 * @property MFLFacility $facility
 */
class MFLFacilityEquipment extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_facility_equipment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['facility_id', 'equipment_id'], 'required'],
            [['facility_id', 'equipment_id'], 'default', 'value' => null],
            [['facility_id', 'equipment_id'], 'integer'],
             [['value'], 'string'],
            ['equipment_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('equipment_id') &&
                            !empty(self::findOne(['equipment_id' => $model->equipment_id,
                                        "facility_id" => $model->facility_id, "value" => $model->value])) ? TRUE : FALSE;
                }, 'message' => 'Equipment already exist for this facility with the same value!'],
            //[['facility_id', 'equipment_id'], 'unique', 'targetAttribute' => ['facility_id', 'equipment_id']],
            [['equipment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipment::className(), 'targetAttribute' => ['equipment_id' => 'id']],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facility::className(), 'targetAttribute' => ['facility_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'facility_id' => 'Facility',
            'equipment_id' => 'Equipment',
            'value' => 'Value',
        ];
    }

    /**
     * Gets query for [[Equipment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipment() {
        return $this->hasOne(MFLEquipment::className(), ['id' => 'equipment_id']);
    }

    /**
     * Gets query for [[Facility]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacility() {
        return $this->hasOne(Facility::className(), ['id' => 'facility_id']);
    }

}
