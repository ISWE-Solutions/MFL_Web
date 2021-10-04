<?php

use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\grid\ActionColumn;
use backend\models\User;
use \kartik\popover\PopoverX;
use kartik\number\NumberControl;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OperationstatusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Operation status';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">
        <p>
            <?php
            if (User::userIsAllowedTo('Manage MFL operation status')) {
                echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
                    return false;"><i class="fa fa-plus"></i> Add operation status</button>';
                echo '<hr class="dotted short">';
            }
            ?>
        </p>

        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                //'id',
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'attribute' => 'name',
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'size' => kartik\popover\PopoverX::SIZE_MEDIUM,
                    ],
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\Operationstatus::getNames(),
                    'filterInputOptions' => ['prompt' => 'Filter by name', 'class' => 'form-control',],
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'attribute' => 'shared_id',
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'success',
                        'size' => PopoverX::SIZE_MEDIUM,
                        'options' => ['class' => 'form-control', 'custom' => true,],
                        'inputType' => Editable::INPUT_WIDGET,
                        'widgetClass' => '\kartik\number\NumberControl',
                        'options' => [
                            'displayOptions' => [
                                'placeholder' => 'Enter a unique HPCZ Ownership id'
                            ],
                            'maskedInputOptions' => [
                                //  'suffix' => ' User(s)',
                                'allowMinus' => false,
                                'min' => 0,
                                'max' => 10000000,
                                'digits' => 0
                            ],
                        ]
                    ],
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\Operationstatus::getHPCZIds(),
                    'filterInputOptions' => ['prompt' => 'Filter by HPCZ id', 'class' => 'form-control',],
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'attribute' => 'description',
                    'width' => '600px',
                    'contentOptions' => [
                        // 'style' => 'padding:0px 0px 0px 30px;',
                        'class' => 'text-left',
                    ],
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'inputType' => Editable::INPUT_TEXTAREA,
                        'submitOnEnter' => false,
                        'placement' => \kartik\popover\PopoverX::ALIGN_TOP,
                        'size' => PopoverX::SIZE_LARGE,
                        'options' => [
                            'class' => 'form-control',
                            'rows' => 6,
                            'placeholder' => 'Enter description...',
                            'style' => 'width:460px;',
                        ]
                    ],
                    'filter' => false,
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                ['class' => ActionColumn::className(),
                    'options' => ['style' => 'width:130px;'],
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            if (User::userIsAllowedTo('Manage MFL operation status')) {
                                return Html::a(
                                                '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                            'title' => 'Remove operation status',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to remove ' . $model->name . ' MFL operation status?<br>'
                                                . 'Operation status will only be removed if its not being used by the system!',
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
                <h5 class="modal-title">Add MFL Operation status</h5>
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
                        <?=
                        $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                            'Name of operation status', 'id' => "status_name", 'required' => true,])
                        ?>
                         <?=
                        $form->field($model, 'shared_id', ['enableAjaxValidation' => true])->widget(NumberControl::classname(), [
                            'displayOptions' => [
                                'placeholder' => 'Enter a unique HPCZ facility operation status id'
                            ],
                            'maskedInputOptions' => [
                                // 'prefix' => '$ ',
                                'suffix' => '',
                                'allowMinus' => false,
                                'digits' => 0,
                                'min' => 1,
                                'max' => 10000000
                            ],
                        ])->label("HPCZ id");
                        ?>
                        <?=
                        $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' =>
                            'Enter description'])->label("Description ");
                        ?>
                    </div>
                    <div class="col-lg-4">
                        <h4>Instructions</h4>
                        <ol>
                            <li>Fields marked with <span style="color: red;">*</span> are required</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-sm']) ?>
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

