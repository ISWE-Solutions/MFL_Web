<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use borales\extensions\phoneInput\PhoneInputValidator;

/**
 * This is the model class for table "facility".
 *
 * @property int $id
 * @property int $district_id
 * @property int|null $constituency_id
 * @property int|null $ward_id
 * @property int|null $zone_id
 * @property string|null $hims_code
 * @property string|null $smartcare_code
 * @property string|null $elmis_code
 * @property string|null $hpcz_code
 * @property string|null $disa_code
 * @property string $name
 * @property string|null $catchment_population_head_count
 * @property string|null $catchment_population_cso
 * @property string|null $number_of_households
 * @property int $operational_status
 * @property int $type
 * @property int $mobility_status
 * @property int $location
 * @property int $ownership_type
 * @property int $ownership
 * @property string $accesibility
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $geom
 * @property int|null $status 0=Inactive, 1=Active,  2=Pending Approval
 * @property string|null $date_approved
 * @property int|null $approved_by
 * @property string $date_created
 * @property int $created_by
 * @property string|null $date_updated
 * @property int|null $updated_by
 *
 * @property MFLOperationstatus $operationalStatus
 * @property FacilityTypes $type0
 * @property GeographyDistrict $district
 * @property Ownership $ownership0
 */
class Facility extends \yii\db\ActiveRecord {

    public $province_id;
    public $coordinates;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'facility';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['district_id', 'name', 'operational_status', 'type', 'mobility_status', 'location', 'ownership_type', 'ownership', 'accesibility', 'date_created'], 'required'],
            [['district_id', 'constituency_id', 'ward_id', 'zone_id', 'operational_status', 'type', 'mobility_status', 'location', 'ownership_type', 'ownership', 'status', 'approved_by', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['district_id', 'constituency_id', 'ward_id', 'zone_id', 'operational_status', 'type', 'mobility_status', 'location', 'ownership_type', 'ownership', 'status', 'approved_by', 'created_by', 'updated_by', 'province_approval_status', 'national_approval_status'], 'integer'],
            [['hims_code', 'smartcare_code', 'elmis_code', 'hpcz_code', 'disa_code', 'name', 'catchment_population_head_count', 'catchment_population_cso', 'number_of_households', 'accesibility', 'latitude', 'longitude'], 'string'],
            [['date_approved', 'date_created', 'date_updated', 'geom', 'verifier_comments', 'approver_comments'], 'safe'],
            ['name', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('name');
                }, 'message' => 'Facility name exist already!'],
//            [['verifier_comments'], 'required', 'when' => function($model) {
//                    return $this->province_approval_status == 2 ? true : false;
//                }, 'whenClient' => "function (attribute, value) {
//                   return $('input[type=\"select\"][name=\"Facility[province_approval_status]\"]:selected').val() == 2 ;
//              }", 'message' => 'Please provide reason for not approving facility!'
//            ],
//            [['approver_comments'], 'required', 'when' => function($model) {
//                    return $this->national_approval_status == 2 ? true : false;
//                }, 'whenClient' => "function (attribute, value) {
//                   return $('input[type=\"select\"][name=\"Facility[national_approval_status]\"]:selected').val() == 2;
//              }", 'message' => 'Please provide reason for not approving facility!'
//            ],
            [['coordinates', 'physical_address', 'postal_address', 'phone',
            'fax', 'plot_no', 'street', 'town','mobile'], 'safe'],
            ['email', 'email', 'message' => "The email isn't correct!"],
            //[['mobile'], PhoneInputValidator::className()],
            [['operational_status'], 'exist', 'skipOnError' => true, 'targetClass' => Operationstatus::className(), 'targetAttribute' => ['operational_status' => 'id']],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => Facilitytype::className(), 'targetAttribute' => ['type' => 'id']],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => Districts::className(), 'targetAttribute' => ['district_id' => 'id']],
            [['ownership'], 'exist', 'skipOnError' => true, 'targetClass' => FacilityOwnership::className(), 'targetAttribute' => ['ownership' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Code',
            'district_id' => 'District',
            'province_id' => 'Province',
            'constituency_id' => 'Constituency',
            'ward_id' => 'Ward',
            'zone_id' => 'Zone',
            'hims_code' => 'Hims code',
            'smartcare_code' => 'Smartcare code',
            'elmis_code' => 'Elmis code',
            'hpcz_code' => 'Hpcz code',
            'disa_code' => 'Disa code',
            'name' => 'Name',
            'catchment_population_head_count' => 'Catchment population head count',
            'catchment_population_cso' => 'Catchment population cso',
            'number_of_households' => 'Number of households',
            'operational_status' => 'Operational status',
            'type' => 'Type',
            'mobility_status' => 'Mobility status',
            'location' => 'Location',
            'ownership_type' => 'Ownership type',
            'ownership' => 'Ownership',
            'accesibility' => 'Accesibility',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'geom' => 'Geom',
            'status' => 'Status',
            'physical_address' => 'Address',
            'postal_address' => 'Postal address',
            'email' => 'Email',
            'phone' => 'Phone',
            'mobile' => 'Mobile',
            'fax' => 'Fax',
            'plot_no' => "Plot no",
            'street' => "Street",
            'town' => "Town",
            'date_approved' => 'Date approved',
            'approved_by' => 'Approved by',
            'date_created' => 'Date created',
            'created_by' => 'Created by',
            'date_updated' => 'Date updated',
            'updated_by' => 'Updated by',
            'verifier_comments' => "Verifier comments",
            'approver_comments' => "Approver comments"
        ];
    }

    /**
     * Gets query for [[OperationalStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperationalStatus() {
        return $this->hasOne(MFLOperationstatus::className(), ['id' => 'operational_status']);
    }

    /**
     * Gets query for [[Type0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType0() {
        return $this->hasOne(FacilityTypes::className(), ['id' => 'type']);
    }

    /**
     * Gets query for [[District]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict() {
        return $this->hasOne(GeographyDistrict::className(), ['id' => 'district_id']);
    }

    /**
     * Gets query for [[Ownership0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwnership0() {
        return $this->hasOne(Ownership::className(), ['id' => 'ownership']);
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

    public static function getCoordinates($coordinates) {
        return new LatLng(['lat' => $coordinate[1], 'lng' => $coordinate[0]]);
    }

}
