<?php

use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use backend\models\User;
use common\models\Role;
use \kartik\popover\PopoverX;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="card card-primary card-outline">
    <div class="card-body">

        <p>
            <?php
            if (\backend\models\User::userIsAllowedTo('Manage Users')) {
//                echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
//                    return false;"><i class="fa fa-plus"></i> Add new user</button>';
                echo Html::a('<i class="fa fa-plus"></i> Add User', ['create'], ['class' => 'btn btn-primary btn-sm']);
            }
            ?>
        </p>

        <hr class="dotted short">
        <?php // echo $this->render('_search', ['model' => $searchModel]);     ?>

        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                //'id',
                [
//                    'class' => EditableColumn::className(),
//                    'editableOptions' => [
//                        'asPopover' => true,
//                        'type' => 'primary',
//                        'size' => PopoverX::SIZE_MEDIUM,
//                        'options' => ['data' => \common\models\Role::getRoles()],
//                        'inputType' => Editable::INPUT_SELECT2,
//                    ],
                    'attribute' => 'role',
                    'format' => 'raw',
                    'label' => 'Role',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => common\models\Role::getRoles(),
                    'filterInputOptions' => ['prompt' => 'Filter by role', 'class' => 'form-control', 'id' => null],
                    'value' => function ($model) {
                        $respone = "";
                        return common\models\Role::getRoleById($model->role);
                    },
                ],
                [
//                    'class' => EditableColumn::className(),
//                    'editableOptions' => [
//                        'type' => 'primary',
//                        'asPopover' => true,
//                        'size' => PopoverX::SIZE_MEDIUM,
//                    ],
//                    'refreshGrid' => true,
                    'attribute' => 'first_name',
                    'label' => 'First name',
                    'format' => 'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\User::getFirstname(),
                    'filterInputOptions' => ['prompt' => 'Filter by first name', 'class' => 'form-control', 'id' => null],
                    "value" => function ($model) {
                        $name = "";
                        $user_model = \backend\models\User::findOne(["id" => $model->id]);
                        if (!empty($user_model)) {
                            $name = $user_model->first_name;
                        }
                        return $name;
                    }
                ],
                [
//                    'class' => EditableColumn::className(),
//                    'editableOptions' => [
//                        'type' => 'primary',
//                        'asPopover' => true,
//                        'size' => PopoverX::SIZE_MEDIUM,
//                    ],
                    'attribute' => 'last_name',
                    'label' => 'Last name',
                    'format' => 'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\User::getLastNames(),
                    'filterInputOptions' => ['prompt' => 'Filter by last name', 'class' => 'form-control', 'id' => null],
                    "value" => function ($model) {
                        $name = "";
                        $user_model = \backend\models\User::findOne(["id" => $model->id]);
                        if (!empty($user_model)) {
                            $name = $user_model->last_name;
                        }
                        return $name;
                    }
                ],
                [
//                    'class' => EditableColumn::className(),
//                    'editableOptions' => [
//                        'type' => 'primary',
//                        'asPopover' => true,
//                        'size' => PopoverX::SIZE_MEDIUM,
//                    ],
                    'attribute' => 'email',
                    'label' => 'Email',
                    'format' => 'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\User::getEmails(),
                    'filterInputOptions' => ['prompt' => 'Filter by email', 'class' => 'form-control', 'id' => null],
                ],
                [
//                    'class' => EditableColumn::className(),
//                    'editableOptions' => [
//                        'type' => 'primary',
//                        'asPopover' => true,
//                        'size' => PopoverX::SIZE_MEDIUM,
//                    ],
                    'attribute' => 'user_type',
                    'label' => 'User type',
                    'format' => 'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => ['National' => "National", "District" => "District", "Province" => "Province"],
                    'filterInputOptions' => ['prompt' => 'Filter by user type', 'class' => 'form-control', 'id' => null],
                ],
                [
                    'class' => EditableColumn::className(),
                    'attribute' => 'status',
                    'filter' => false,
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
                        'data' => [\backend\models\User::STATUS_ACTIVE => 'Active', User::STATUS_INACTIVE => 'Inactive'],
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
                //'created_at',
                //'updated_at',
                //'updated_by',
                //'created_by',
                ['class' => 'yii\grid\ActionColumn',
                    'template' => '{view}{update}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            if (User::userIsAllowedTo('View Users')|| User::userIsAllowedTo('Manage Users')) {
                                return Html::a(
                                                '<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
                                            'title' => 'View user',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data-pjax' => '0',
                                            'style' => "padding:5px;",
                                            'class' => 'bt btn-lg'
                                                ]
                                );
                            }
                        },
                        'update' => function ($url, $model) {
                            if (User::userIsAllowedTo('Manage Users') && $model->status == User::STATUS_ACTIVE) {
                                return Html::a(
                                                '<span class="fas fa-edit"></span>', ['update', 'id' => $model->id], [
                                            'title' => 'Update user',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            // 'target' => '_blank',
                                            'data-pjax' => '0',
                                            'style' => "padding:5px;",
                                            'class' => 'bt btn-lg'
                                                ]
                                );
                            }
                        },
                        'delete' => function ($url, $model) {
                            if (User::userIsAllowedTo('Manage Users') && $model->status == User::STATUS_INACTIVE) {
                                return Html::a(
                                                '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                            'title' => 'Remove user',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to remove this user?',
                                                'method' => 'post',
                                            ],
                                            'style' => "padding:5px;",
                                            'class' => 'bt btn-lg'
                                                ]
                                );
                            }
                        },
                    ]
                ]
            ],
        ]);
        ?>


    </div>
</div>
<div class="modal fade" id="addNewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content card-primary card-outline">
            <div class="modal-header">
                <h5 class="modal-title">Add new system user</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <?php
                        $form = ActiveForm::begin([
                                    'action' => 'create',
                                ])
                        ?>

                        <?php
                        echo $form->field($model, 'first_name')->textInput(['maxlength' => true, 'class' => "form-control", 'placeholder' => 'First name'])->label("First name");
                        echo $form->field($model, 'last_name')->textInput(['maxlength' => true, 'class' => "form-control", 'placeholder' => 'Last name'])->label("Last name");

                        echo $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'type' => 'email', 'placeholder' => 'email address', 'required' => true])->label("Email");

                        echo $form->field($model, 'role')->dropDownList(
                                yii\helpers\ArrayHelper::map(Role::find()->asArray()->all(), 'id', 'role'), ['custom' => true, 'maxlength' => true, 'style' => '', 'prompt' => 'Please select role', 'required' => true]
                        )->label("Role");
                        ?>
                    </div>
                    <div class="col-lg-4">
                        <h4>Instructions</h4>
                        <ol>
                            <li>Fields marked with <span style="color: red;">*</span> are required</li>
                            <li>Email is used for login</li>
                            <li>A account activation email will be sent to users email</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::submitButton('Save user', ['class' => 'btn btn-primary btn-sm']) ?>
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
