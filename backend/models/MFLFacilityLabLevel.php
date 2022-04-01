<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "MFL_facility_lab_level".
 *
 * @property int $id
 * @property int $facility_id
 * @property int $lablevel_id
 *
 * @property MFLFacility $facility
 * @property MFLLablevel $lablevel
 */
class MFLFacilityLabLevel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MFL_facility_lab_level';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['facility_id', 'lablevel_id'], 'required'],
            [['facility_id', 'lablevel_id'], 'default', 'value' => null],
            [['facility_id', 'lablevel_id'], 'integer'],
            [['facility_id', 'lablevel_id'], 'unique', 'targetAttribute' => ['facility_id', 'lablevel_id']],
            [['facility_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facility::className(), 'targetAttribute' => ['facility_id' => 'id']],
            [['lablevel_id'], 'exist', 'skipOnError' => true, 'targetClass' => MFLLablevel::className(), 'targetAttribute' => ['lablevel_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'facility_id' => 'Facility ID',
            'lablevel_id' => 'Lablevel ID',
        ];
    }

    /**
     * Gets query for [[Facility]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacility()
    {
        return $this->hasOne(Facility::className(), ['id' => 'facility_id']);
    }

    /**
     * Gets query for [[Lablevel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLablevel()
    {
        return $this->hasOne(MFLLablevel::className(), ['id' => 'lablevel_id']);
    }
}
