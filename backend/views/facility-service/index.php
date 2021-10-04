<?php
use \kartik\popover\PopoverX;
use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\grid\ActionColumn;
use backend\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\FacilityServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Services';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card card-primary card-outline">
    <div class="card-body">
        <p>
            <?php
            if (User::userIsAllowedTo('Manage MFL services')) {
                echo '<button class="btn btn-primary btn-sm" href="#" onclick="$(\'#addNewModal\').modal(); 
                    return false;"><i class="fa fa-plus"></i> Add Service</button>';
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
                    'attribute' => 'category_id',
                    //'readonly' => false,
                    'refreshGrid' => true,
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filter' => \backend\models\FacilityServicecategory::getList(),
                    'filterInputOptions' => ['prompt' => 'Filter by category', 'class' => 'form-control', 'id' => null],
                    'editableOptions' => [
                        'asPopover' => true,
                        'type' => 'primary',
                        'size' => \kartik\popover\PopoverX::SIZE_MEDIUM,
                        'options' => ['data' => \backend\models\FacilityServicecategory::getList(),],
                        'inputType' => kartik\editable\Editable::INPUT_SELECT2,
                    ],
                    'value' => function ($model) {
                        $name = backend\models\FacilityServicecategory::findOne($model->category_id)->name;
                        return $name;
                    },
                ],
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
                    'filter' => \backend\models\FacilityService::getNames(),
                    'filterInputOptions' => ['prompt' => 'Filter by name', 'class' => 'form-control',],
                    'format' => 'raw',
                    'refreshGrid' => true,
                ],
                             [
                    'class' => EditableColumn::className(),
                    'enableSorting' => true,
                    'attribute' => 'comments',
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
                            if (User::userIsAllowedTo('Manage MFL services')) {
                                return Html::a(
                                                '<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
                                            'title' => 'Remove service category',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to remove ' . $model->name . ' MFL service?<br>'
                                                . 'Service will only be removed if its not being used by the system!',
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
                <h5 class="modal-title">Add MFL Service</h5>
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
                                $form->field($model, 'category_id')
                                ->dropDownList(
                                        \backend\models\FacilityServicecategory::getList(), ['custom' => true, 'prompt' => 'Select service category', 'required' => true]
                        );
                        ?>
                        <?=
                        $form->field($model, 'name', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' =>
                            'Name of service', 'id' => "province", 'required' => true,])
                        ?>
                         <?=
                        $form->field($model, 'comments')->textarea(['rows' => 4, 'placeholder' =>
                            'Enter service comments'])->label("Comment ");
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
