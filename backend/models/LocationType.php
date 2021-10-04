<?php

namespace backend\models;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "geography_locationtype".
 *
 * @property int $id
 * @property string $name
 *
 * @property MFLFacility[] $mFLFacilities
 */
class LocationType extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'geography_locationtype';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['shared_id'], 'integer'],
            [['name', 'shared_id'], 'required'],
            [['name'], 'string', 'max' => 20],
            ['shared_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('shared_id');
                }, 'message' => 'Location type with this HPCZ id exist already!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name');
                }, 'message' => 'Location type should be unique!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') && !empty(self::findOne(['name' => $model->name, "shared_id" => $model->shared_id])) ? TRUE : FALSE;
                }, 'message' => 'Location type exist already with the same shared id!'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'shared_id' => 'HPCZ id',
        ];
    }

    /**
     * Gets query for [[MFLFacilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilities() {
        return $this->hasMany(MFLFacility::className(), ['location_type_id' => 'id']);
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

    public static function getHPCZIds() {
        $list = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'shared_id');
    }

}
