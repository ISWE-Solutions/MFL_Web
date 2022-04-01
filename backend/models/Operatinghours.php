<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_operatinghours".
 *
 * @property int $id
 * @property string $name
 *
 * @property MFLFacilityOperatingHours[] $mFLFacilityOperatingHours
 * @property MFLFacility[] $facilities
 */
class Operatinghours extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_operatinghours';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 60],
            [['name'], 'unique', 'message' => 'Facility operating hour exist already!'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[MFLFacilityOperatingHours]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilityOperatingHours() {
        return $this->hasMany(MFLFacilityOperatingHours::className(), ['operatinghours_id' => 'id']);
    }

    /**
     * Gets query for [[Facilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacilities() {
        return $this->hasMany(Facility::className(), ['id' => 'facility_id'])->viaTable('MFL_facility_operating_hours', ['operatinghours_id' => 'id']);
    }

    public static function getNames() {
        $names = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($names, 'name', 'name');
    }

    public static function getList() {
        $list = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'name');
    }

    public static function getById($id) {
        $data = self::find()->where(['id' => $id])->one();
        return $data->name;
    }

}
