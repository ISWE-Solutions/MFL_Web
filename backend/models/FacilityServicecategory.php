<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_servicecategory".
 *
 * @property int $id
 * @property string $name
 *
 * @property MFLService[] $mFLServices
 */
class FacilityServicecategory extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_servicecategory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name','shared_id'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['shared_id'], 'integer'],
            [['name'], 'unique', 'message' => 'Facility service area exist already!'],
            [['shared_id'], 'unique', 'message' => 'Shared code is already in use!'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'shared_id' => 'Shared code',
            'name' => 'Area',
        ];
    }

    /**
     * Gets query for [[MFLServices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLServices() {
        return $this->hasMany(MFLService::className(), ['category_id' => 'id']);
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
