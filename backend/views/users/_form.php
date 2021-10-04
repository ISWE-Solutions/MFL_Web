<?php

use yii\helpers\Html;
//use yii\bootstrap4\ActiveForm;
use borales\extensions\phoneInput\PhoneInput;
use kartik\form\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use common\models\Role;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
$this->registerJs(
        '$("#seeAnotherField").change(function () {
    if ($(this).val() ==="Province") {
        $("#otherFieldDiv").show();
        $("#otherFieldDiv1").hide();
       
    }else if($(this).val() ==="District"){
        $("#otherFieldDiv").show();
        $("#otherFieldDiv1").show();
     } else {
        $("#otherFieldDiv").hide();
        $("#otherFieldDiv1").hide();
     }
});
$("#seeAnotherField").trigger("change");', yii\web\View::POS_END);
?>


<div class="row" style="">
    <div class="col-lg-6">
        <h4>Instructions</h4>
        <ol>
            <li>Fields marked with <code>*</code> are required</li>
            <li>Email will be used as login username</li>
        </ol>
    </div>
</div>

<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]]);
?>
<hr class="dotted short">
<div class="row">
    <?php
    echo '<div class="col-md-6">';

    echo $form->field($model, 'first_name')->textInput(['maxlength' => true, 'class' => "form-control", 'placeholder' => 'First name'])->label("First name");
    echo $form->field($model, 'last_name')->textInput(['maxlength' => true, 'class' => "form-control", 'placeholder' => 'Last name'])->label("Last name");
    echo $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'type' => 'email', 'placeholder' => 'email address'])->label("Email");
    echo $form->field($model, 'role')->dropDownList(
            yii\helpers\ArrayHelper::map(Role::find()->asArray()->all(), 'id', 'role'), ['custom' => true, 'maxlength' => true, 'style' => '', 'prompt' => 'Please select role']
    )->label("Role");
    echo $form->field($model, 'user_type')->dropDownList(
            ['National' => "National", "District" => "District", "Province" => "Province"],
            ['custom' => true, 'maxlength' => true, "id" => "seeAnotherField", 'style' => '', 'prompt' => 'Please select user type']
    )->label("User type");

    echo '<div  id="otherFieldDiv">';
    echo
            $form->field($model, 'province_id',['enableAjaxValidation' => true])
            ->dropDownList(
                    \backend\models\Provinces::getProvinceList(), ['id' => 'prov_id', 'custom' => true, 'prompt' => 'Please select a province']
    );
    echo Html::hiddenInput('selected_id', $model->isNewRecord ? '' : $model->district_id, ['id' => 'selected_id']);
    echo '  </div>';
    echo '<div  id="otherFieldDiv1">';
    echo $form->field($model, 'district_id',['enableAjaxValidation' => true])->widget(DepDrop::classname(), [
        'options' => ['id' => 'dist_id', 'custom' => true],
        'type' => DepDrop::TYPE_SELECT2,
        'pluginOptions' => [
            'depends' => ['prov_id'],
            'initialize' => $model->isNewRecord ? false : true,
            'placeholder' => 'Please select a district',
            'url' => Url::to(['/constituencies/district']),
            'params' => ['selected_id'],
            'loadingText' => 'Loading districts....',
        ]
    ]);
    echo '  </div></div>
        <div class="form-group col-lg-12">';
    echo Html::submitButton('Save', ['class' => 'btn btn-success btn-sm']);
    echo '</div>';
    ?>

</div>
<?php ActiveForm::end();
?>


