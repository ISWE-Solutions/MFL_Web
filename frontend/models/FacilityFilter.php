<?php

namespace frontend\models;
use yii\base\Model;


/**
 * Signup form
 */
class FacilityFilter extends Model {

    public $province;
    public $district;
    public $ward;
    public $constituency;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['province'], 'required'],
        ];
    }

}
