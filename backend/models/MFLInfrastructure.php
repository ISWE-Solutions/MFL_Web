<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_infrastructure".
 *
 * @property int $id
 * @property string $name
 *
 * @property MFLFacilityInfrastructure[] $mFLFacilityInfrastructures
 * @property MFLFacility[] $facilities
 */
class MFLInfrastructure extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_infrastructure';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique', 'message' => 'MFL infrastructure name exist already!'],
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
     * Gets query for [[MFLFacilityInfrastructures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilityInfrastructures() {
        return $this->hasMany(MFLFacilityInfrastructure::className(), ['infrastructure_id' => 'id']);
    }

    /**
     * Gets query for [[Facilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacilities() {
        return $this->hasMany(Facility::className(), ['id' => 'facility_id'])->viaTable('MFL_facility_infrastructure', ['infrastructure_id' => 'id']);
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
