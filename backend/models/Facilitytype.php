<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_facilitytype".
 *
 * @property int $id
 * @property string $name
 *
 * @property MFLFacility[] $mFLFacilities
 */
class Facilitytype extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'facility_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'shared_id'], 'required'],
            [['shared_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            ['shared_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('shared_id');
                }, 'message' => 'Facility type with this HPCZ id exist already!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name');
                }, 'message' => 'Facility type should be unique!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') && !empty(self::findOne(['name' => $model->name, "shared_id" => $model->shared_id])) ? TRUE : FALSE;
                }, 'message' => 'Facility type exist already with the same shared id!'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'id',
            'shared_id' => 'HPCZ id',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[MFLFacilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilities() {
        return $this->hasMany(MFLFacility::className(), ['facility_type_id' => 'id']);
    }

    public static function getNames() {
        $names = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($names, 'name', 'name');
    }

    public static function getList() {
        $list = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'name');
    }

    public static function getHPCZIds() {
        $list = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'shared_id');
    }

    public static function getById($id) {
        $data = self::find()->where(['id' => $id])->one();
        return $data->name;
    }

}
