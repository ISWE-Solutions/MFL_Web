<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use dosamigos\google\maps\LatLng;

/**
 * This is the model class for table "geography_ward".
 *
 * @property int $id
 * @property string $name
 * @property float|null $population
 * @property float|null $pop_density
 * @property float|null $area_sq_km
 * @property string $geom
 * @property int|null $constituency_id
 *
 * @property MFLFacility[] $mFLFacilities
 * @property GeographyConstituency $constituency
 */
class Wards extends \yii\db\ActiveRecord {

    public $province_id;

    // public $district_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'geography_ward';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'district_id'], 'required'],
            [['population', 'pop_density', 'area_sq_km'], 'number'],
            //[['geom'], 'string'],
            [['province_id', 'geom'], 'safe'],
            [['constituency_id'], 'default', 'value' => null],
            [['constituency_id', 'district_id'], 'integer'],
            [['name'], 'string', 'max' => 254],
            ['name', 'unique', 'when' => function($model) {
                    return trim($model->isAttributeChanged('name')) &&
                            $model->isAttributeChanged('constituency_id') &&
                            !empty(self::findOne(['name' => $model->name, "constituency_id" => $model->constituency_id])) ? TRUE : FALSE;
                }, 'message' => 'Ward name already exist for this constituency!'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name') &&
                            $model->isAttributeChanged('district_id') &&
                            !empty(self::findOne(['name' => $model->name, "district_id" => $model->district_id])) ? TRUE : FALSE;
                }, 'message' => 'Ward name already exist for this district!'],
            [['constituency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Constituency::className(), 'targetAttribute' => ['constituency_id' => 'id']],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => Districts::className(), 'targetAttribute' => ['district_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'population' => 'Population',
            'pop_density' => 'Population Density',
            'area_sq_km' => 'Area Sq Km',
            'geom' => 'Geometry Coordinates',
            'constituency_id' => 'Constituency',
            'district_id' => 'District',
            'province_id' => "Province"
        ];
    }

    /**
     * Gets query for [[MFLFacilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMFLFacilities() {
        return $this->hasMany(MFLFacility::className(), ['ward_id' => 'id']);
    }

    /**
     * Gets query for [[Constituency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConstituency() {
        return $this->hasOne(Constituency::className(), ['id' => 'constituency_id']);
    }

    public static function getNames() {
        $names = self::find()->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($names, 'name', 'name');
    }

    public static function getList($districtId = "")
    {
        if (empty($districtId)) {
            $list = self::find()->orderBy(['name' => SORT_ASC])->all();
            return ArrayHelper::map($list, 'id', 'name');
        } else {
            $list = self::find()->where(["district_id" => $districtId])->orderBy(['name' => SORT_ASC])->all();
            return ArrayHelper::map($list, 'id', 'name');
        }
    }

    public static function getById($id) {
        $data = self::find()->where(['id' => $id])->one();
        return $data->name;
    }

    public static function getCoordinates($coordinate_array) {
        $coordinates = [];
        foreach ($coordinate_array[0][0] as $coordinate) {
            array_push($coordinates, new LatLng(['lat' => $coordinate[1], 'lng' => $coordinate[0]]));
        }
        return $coordinates;
    }

    public static function getListByDistrictID($id) {
        $list = self::find()->where(['district_id' => $id])->orderBy(['name' => SORT_ASC])->all();
        return ArrayHelper::map($list, 'id', 'name');
    }

}
