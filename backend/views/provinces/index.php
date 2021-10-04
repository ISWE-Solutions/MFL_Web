<?php

use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\grid\ActionColumn;
use backend\models\User;
use kartik\number\NumberControl;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProvincesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Provinces';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">

        <p>
            <?php
            if (User::userIsAllowedTo('Manage provinces')) {
                // echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
                //   return false;"><i class="fa fa-plus"></i> Add Province</button>';
                echo Html::a('<i class="fa fa-plus"></i> Add Province', ['create'], ['class' => 'btn btn-sm btn-primary']);
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
                // 'id',
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
                    'filter' => \backend\models\Provinces::getProvinceNames(),
                    'filterInputOptions' => ['prompt' => 'Filter by name', 'class' => 'form-control',],
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'size' => kartik\popover\PopoverX::SIZE_MEDIUM,
                    ],
                    'refreshGrid' => true,
                    'attribute' => 'population',
                    'filter' => false,
                ],
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'size' => kartik\popover\PopoverX::SIZE_MEDIUM,
                    ],
                    'refreshGrid' => true,
                    'attribute' => 'pop_density',
                    'filter' => false,
                ],
                [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'size' => kartik\popover\PopoverX::SIZE_MEDIUM,
                    ],
                    'refreshGrid' => true,
                    'attribute' => 'area_sq_km',
                    'filter' => false,
                ],
                // 'geom',
                ['class' => ActionColumn::className(),
                    'options' => ['style' => 'width:130px;'],
                    'template' => '{view}{update}{delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a(
                                            '<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
                                        'title' => 'View province',
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-pjax' => '0',
                                        'style' => "padding:5px;",
                                        'class' => 'bt btn-lg'
                                            ]
                            );
                        },
                        'update' => function ($url, $model) {
                            if (User::userIsAllowedTo('Manage provinces')) {
                                return Html::a(
                                                '<span class="fas fa-edit"></span>', ['update', 'id' => $model->id], [
                                            'title' => 'Update all details',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data-pjax' => '0',
                                            'style' => "padding:5px;",
                                            'class' => 'bt btn-lg'
                                                ]
                                );
                            }
                        },
                        'delete' => function ($url, $model) {
                            if (User::userIsAllowedTo('Remove provinces')) {
                                return Html::a(
                                                '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                            'title' => 'Remove province',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to remove ' . $model->name . ' Province?<br>'
                                                . 'Province will only be removed if its not being used by the system!',
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
                    <div class="col-lg-8">
                        <?php
                        $form = ActiveForm::begin([
                                    'action' => 'create',
                                ])
                        ?>
                        <?=
                        $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                            'Name of province', 'id' => "province", 'required' => true,])
                        ?>
                        <?=
                        $form->field($model, 'population')->widget(NumberControl::classname(), [
                            'displayOptions' => [
                                'placeholder' => 'Enter population in the province'
                            ],
                            'maskedInputOptions' => [
                                'suffix' => ' ',
                                'allowMinus' => false
                            ],
                        ]);
                        ?>
                        <?=
                        $form->field($model, 'pop_density')->widget(NumberControl::classname(), [
                            'displayOptions' => [
                                'placeholder' => 'Enter population density for the province'
                            ],
                            'maskedInputOptions' => [
                                'suffix' => ' ',
                                'allowMinus' => false
                            ],
                        ]);
                        ?>
                        <?=
                        $form->field($model, 'area_sq_km')->widget(NumberControl::classname(), [
                            'displayOptions' => [
                                'placeholder' => 'Enter size of province'
                            ],
                            'maskedInputOptions' => [
                                'suffix' => ' Sq Km',
                                'allowMinus' => false
                            ],
                        ]);
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
                <?= Html::submitButton('Save province', ['class' => 'btn btn-primary btn-sm']) ?>
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
