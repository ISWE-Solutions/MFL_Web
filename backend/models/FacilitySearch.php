<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Facility;

/**
 * FacilitySearch represents the model behind the search form of `backend\models\Facility`.
 */
class FacilitySearch extends Facility {

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id', 'district_id', 'constituency_id', 'ward_id', 'zone_id', 'operational_status',
            'type', 'mobility_status', 'location', 'ownership_type', 'ownership', 'status',
            'approved_by', 'created_by', 'updated_by', 'province_approval_status',
            'national_approval_status', 'province_approval_status', 'national_approval_status'], 'integer'],
            [['hims_code', 'smartcare_code', 'elmis_code', 'hpcz_code', 'disa_code',
            'name', 'catchment_population_head_count', 'catchment_population_cso',
            'number_of_households', 'accesibility', 'latitude', 'longitude', 'geom',
            'date_approved', 'date_created', 'date_updated', 'province_id'
            , 'physical_address', 'postal_address', 'phone',
            'mobile', 'fax', 'plot_no', 'street', 'town'], 'safe'],
            [['email'], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = Facility::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'district_id' => $this->district_id,
            'constituency_id' => $this->constituency_id,
            'ward_id' => $this->ward_id,
            'zone_id' => $this->zone_id,
            'operational_status' => $this->operational_status,
            'type' => $this->type,
            'mobility_status' => $this->mobility_status,
            'location' => $this->location,
            'ownership_type' => $this->ownership_type,
            'ownership' => $this->ownership,
            'status' => $this->status,
            'date_approved' => $this->date_approved,
            'approved_by' => $this->approved_by,
            'date_created' => $this->date_created,
            'created_by' => $this->created_by,
            'date_updated' => $this->date_updated,
            'updated_by' => $this->updated_by,
        ]);


        $query->andFilterWhere(['ilike', 'hims_code', $this->hims_code])
                ->andFilterWhere(['ilike', 'physical_address', $this->physical_address])
                ->andFilterWhere(['ilike', 'postal_address', $this->postal_address])
                ->andFilterWhere(['ilike', 'email', $this->email])
                ->andFilterWhere(['ilike', 'phone', $this->phone])
                ->andFilterWhere(['ilike', 'mobile', $this->mobile])
                ->andFilterWhere(['ilike', 'fax', $this->fax])
                ->andFilterWhere(['ilike', 'plot_no', $this->plot_no])
                ->andFilterWhere(['ilike', 'town', $this->town])
                ->andFilterWhere(['ilike', 'street', $this->street])
                ->andFilterWhere(['ilike', 'smartcare_code', $this->smartcare_code])
                ->andFilterWhere(['ilike', 'elmis_code', $this->elmis_code])
                ->andFilterWhere(['ilike', 'hpcz_code', $this->hpcz_code])
                ->andFilterWhere(['ilike', 'disa_code', $this->disa_code])
                ->andFilterWhere(['ilike', 'name', $this->name])
                ->andFilterWhere(['ilike', 'catchment_population_head_count', $this->catchment_population_head_count])
                ->andFilterWhere(['ilike', 'catchment_population_cso', $this->catchment_population_cso])
                ->andFilterWhere(['ilike', 'number_of_households', $this->number_of_households])
                ->andFilterWhere(['ilike', 'accesibility', $this->accesibility])
                ->andFilterWhere(['ilike', 'latitude', $this->latitude])
                ->andFilterWhere(['ilike', 'longitude', $this->longitude])
                ->andFilterWhere(['ilike', 'geom', $this->geom]);

        return $dataProvider;
    }

}
