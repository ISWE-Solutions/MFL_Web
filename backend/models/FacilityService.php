<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MFL_service".
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 *
 * @property MFLFacilityServices[] $mFLFacilityServices
 * @property MFLFacility[] $facilities
 * @property MFLServicecategory $category
 */
class FacilityService extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'MFL_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'category_id', 'shared_id'], 'required'],
            [['category_id', 'comments'], 'default', 'value' => null],
            [['category_id', 'shared_id'], 'integer'],
            [['comments'], 'safe'],
            [['shared_id'], 'unique', 'message' => 'Shared code is already in use!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') &&
                            empty($model->category_id) ? TRUE : FALSE;
                }, 'message' => 'Service scope already exist!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') && !empty(self::findOne(['name' => $model->name, "category_id" => $model->category_id])) ? TRUE : FALSE;
                }, 'message' => 'Service scope already exist for this service area!'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => FacilityServicecategory::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Scope',
            'category_id' => 'Service Area',
            'shared_id' => 'Shared code',
            'comments' => 'Comment',
        ];
    }

    /**
     * Gets query for [[MFLFacilityServices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilityServices() {
        return $this->hasMany(MFLFacilityServices::className(), ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Facilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacilities() {
        return $this->hasMany(MFLFacility::className(), ['id' => 'facility_id'])->viaTable('MFL_facility_services', ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(FacilityServicecategory::className(), ['id' => 'category_id']);
    }

    public static function getNames() {
        $names = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($names, 'name', 'name');
    }

    public static function getList() {
        $list = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'name');
    }

    public static function getListFacility($id) {
        $facility_services = MFLFacilityServices::find()->where(['facility_id' => $id])->asArray()->all();
        if (!empty($facility_services)) {
            $service_arr = [];
            foreach ($facility_services as $service) {
                array_push($service_arr, $service['service_id']);
            }
            $list = self::find()->where(['NOT IN', 'id', $service_arr])->orderBy(['name' => SORT_ASC])->all();
            return ArrayHelper::map($list, 'id', 'name');
        } else {
            $list = self::find()->orderBy(['name' => SORT_ASC])->all();
            return ArrayHelper::map($list, 'id', 'name');
        }
    }

    public static function getById($id) {
        $data = self::find()->where(['id' => $id])->one();
        return $data->name;
    }

}
