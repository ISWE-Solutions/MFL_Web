<?php

use kartik\grid\GridView;
use backend\models\User;
use yii\grid\ActionColumn;
use kartik\form\ActiveForm;
use kartik\popover\PopoverX;
use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ApiUsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'API Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">

        <p>

            <?php
            if (User::userIsAllowedTo('Manage api users')) {
                echo '<button class="btn btn-primary btn-xs" href="#" onclick="$(\'#addNewModal\').modal(); 
                       return false;"><i class="fa fa-plus"></i> Add API user</button>';
                echo '<hr class="dotted short">';
            }
            ?>
        </p>

        <?php // echo $this->render('_search', ['model' => $searchModel]);    ?>

        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                // 'id',
                [
                    'filter' => false,
                    'attribute' => 'email',
                ],
                [
                    'filter' => false,
                    'attribute' => 'username',
                ],
                [
                    'filter' => false,
                    'attribute' => 'password',
                    'value' => function($model) {
                        return \substr_replace($model->password, 'xxxxxxxxxxxxxxxx', 10, 35);
                    },
                    'label' => 'Password',
                ],
                //'auth_key',
                [
                    'class' => EditableColumn::className(),
                    'attribute' => 'status',
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => [User::STATUS_ACTIVE => 'Active', User::STATUS_INACTIVE => 'Inactive'],
                    'filterInputOptions' => ['prompt' => 'Filter by Status', 'class' => 'form-control', 'id' => null],
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'format' => 'raw',
                    'editableOptions' => [
                        'asPopover' => false,
                        'options' => ['class' => 'form-control', 'prompt' => 'Select Status...'],
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'data' => [\backend\models\User::STATUS_ACTIVE => 'Activate', User::STATUS_INACTIVE => 'Deactivate'],
                    ],
                    'value' => function($model) {
                        $str = "";
                        if ($model->status == \backend\models\User::STATUS_ACTIVE) {
                            $str = "<p class='badge badge-success'> "
                                    . "<i class='fa fa-check'></i> Active</p><br>";
                        }
                        if ($model->status == \backend\models\User::STATUS_INACTIVE) {
                            $str = "<p class='badge badge-danger'> "
                                    . "<i class='fa fa-times'></i> Inactive</p><br>";
                        }
                        return $str;
                    },
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                //'created_by',
                //'created_at',
                //'updated_by',
                //'updated_at',
                ['class' => ActionColumn::className(),
                    'options' => ['style' => 'width:50px;'],
                    'template' => '{regenerate}',
                    'buttons' => [
//                        'view' => function ($url, $model) {
//                            if (\backend\models\Users::isUserAllowedTo('View client api keys')) {
//                                return Html::a(
//                                                '<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
//                                            'title' => 'View key details',
//                                            'data-toggle' => 'tooltip',
//                                            'data-placement' => 'top',
//                                            'data-pjax' => '0',
//                                            'style' => "padding:5px;",
//                                            'class' => 'btn btn-lg'
//                                                ]
//                                );
//                            }
//                        },
                        'regenerate' => function ($url, $model) {
                            if (User::userIsAllowedTo('Manage api users') && $model->status == User::STATUS_ACTIVE) {
                                return Html::a(
                                                '<span class="fas fa-edit"></span>', ['update', 'id' => $model->id], [
                                            'title' => 'Regenerate password',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to regenerate the user password?'
                                                . '<br>API User will be required to update the password to the new '
                                                . 'password sent to them on mail otherwise they wont be able to access the API services!',
                                                'method' => 'post',
                                            ],
                                            'style' => "padding:5px;",
                                            'class' => 'bt btn-lg'
                                                ]
                                );
                            }
                        },
                       
                    ]
                ],
            ],
        ]);
        ?>


    </div>
</div>


<div class="modal fade card-primary card-outline" id="addNewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add new Province</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <?php
                        $form = ActiveForm::begin([
                                    'action' => 'create',
                                ])
                        ?>
                        <?=
                        $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                            'Enter email', 'id' => "email", 'required' => true,])
                        ?>
                        <?=
                        $form->field($model, 'username', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                            'Enter unique username', 'id' => "username", 'required' => true,])
                        ?>
                    </div>
                    <div class="col-lg-6">
                        <h4>Instructions</h4>
                        <ol>
                            <li>System will generate a password key which will be sent to the provided email address</li>
                            <li>Fields marked with <span style="color: red;">*</span> are required</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::submitButton('Save User', ['class' => 'btn btn-primary btn-sm']) ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php
$this->registerCss('.popover-x {display:none}');
?>
