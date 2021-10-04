<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_operationstatus".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 *
 * @property MFLFacility[] $mFLFacilities
 */
class Operationstatus extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'operations_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'shared_id'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 30],
            // [['name'], 'unique', 'message' => 'Facility operation status exist already!'],
            ['shared_id', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('shared_id');
                }, 'message' => 'Facility operation status with this HPCZ id exist already!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name');
                }, 'message' => 'Facility operation status should be unique!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') && !empty(self::findOne(['name' => $model->name, "shared_id" => $model->shared_id])) ? TRUE : FALSE;
                }, 'message' => 'Facility operation status exist already with the same shared id!'],
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
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[MFLFacilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilities() {
        return $this->hasMany(MFLFacility::className(), ['operation_status_id' => 'id']);
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
