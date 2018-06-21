<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
echo "<?php\n";
?>
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
    <div id="ajaxCrudDatatable">
        <?="<?="?>GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns.php'),
            'toolbar'=> [
                ['content'=>
                    Html::a('Создать', ['create'],
                    ['role'=>'modal-remote','title'=> 'Создать','class'=>'btn btn-primary']).
                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=>'Сброс']).
                    '{toggleData}'.
                    '{export}'
                ],
            ],
            'bordered' => false,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'panel' => [
                //'type' => 'primary',
                'heading' => '<i class="glyphicon glyphicon-list"></i> Записи <?= Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> ',
                'before'=>'<em>* Размер столбца можно изменить, перетащив за край столбца.</em>',
                'after'=>BulkButtonWidget::widget([
                            'buttons'=>Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить все',
                                ["bulkdelete"] ,
                                [
                                    "class"=>"btn btn-danger btn-xs",
                                    'role'=>'modal-remote-bulk',
                                    'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                                    'data-request-method'=>'post',
                                    'data-confirm-title'=>'Вы уверены?',
                                    'data-confirm-message'=>'Вы точно хотите удалить эту запись?'
                                ]),
                        ]).                        
                        '<div class="clearfix"></div>',
            ]
        ])<?="?>\n"?>
    </div>
<?='<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "headerOptions" => ["class" => "bg-primary"],
    "size" =>Modal::SIZE_DEFAULT, // SIZE_LARGE / SIZE_SMALL / SIZE_DEFAULT
    "footer"=>"",// always need it for jquery plugin
])?>'."\n"?>
<?='<?php Modal::end(); ?>'?>

