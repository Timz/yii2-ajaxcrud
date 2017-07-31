<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\helpers\StringHelper;
use yii\db\ActiveRecordInterface;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulkdelete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Список всех <?= $modelClass ?> моделей
     *
     * @return mixed
     */
    public function actionIndex()
    {    
       <?php if (!empty($generator->searchModelClass)): ?>
 $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }


    /**
     * Отображает одну выбранную <?= $modelClass ?> модель
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     *
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {   
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "<?= $modelClass ?> #".<?= $actionParams ?>,
                    'content'=>$this->renderAjax('view', [
                        'model' => $this->findModel(<?= $actionParams ?>),
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Правка',['update','<?= substr($actionParams,1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];    
        }
		else{
            return $this->render('view', [
                'model' => $this->findModel(<?= $actionParams ?>),
            ]);
        }
    }

    /**
     * Добавляет запись в модельку <?= $modelClass ?>
     * Для аякс-запросов - возвращает ответ в формате json
     * и для не аяксов, если создание успешно редиректит браузер на страницу просмотра
	 *
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new <?= $modelClass ?>();  

        if($request->isAjax){
            /*
            *   Обработка аякс-запроса
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "Добавить запись <?= $modelClass ?>",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Сохранить',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
			else if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> "Добавить запись <?= $modelClass ?>",
                    'content'=>'<span class="text-success">Create <?= $modelClass ?> success</span>',
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Добавить еще',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
        
                ];         
            }
			else{           
                return [
                    'title'=> "Добавить запись <?= $modelClass ?>",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Сохранить',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
        }
		else{
            /*
            *   Обработка обычного, не аякс-запроса
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', <?= $urlParams ?>]);
            }
			else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
       
    }

    /**
     * Редактирует существующую запись модели <?= $modelClass ?>
 	 * Для аякс-запросов - возвращает ответ в формате json
	 * и для не аяксов, если правка успешна редиректит браузер на страницу просмотра
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * 
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $request = Yii::$app->request;
        $model = $this->findModel(<?= $actionParams ?>);       

        if($request->isAjax){
            /*
            *   Обработка аякс-запроса
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "Правка <?= $modelClass ?> #".<?= $actionParams ?>,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Сохранить',['class'=>'btn btn-primary','type'=>"submit"])
                ];         
            }
			else if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> "<?= $modelClass ?> #".<?= $actionParams ?>,
                    'content'=>$this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Правка',['update','<?= substr($actionParams,1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];    
            }
			else{
                 return [
                    'title'=> "Правка <?= $modelClass ?> #".<?= $actionParams ?>,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Закрыть',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Сохранить',['class'=>'btn btn-primary','type'=>"submit"])
                ];        
            }
        }
		else{
            /*
            *   Обработка обычного, не аякс-запроса
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', <?= $urlParams ?>]);
            }
			else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Удаление существующей модели <?= $modelClass ?>
	 * Для аякс-запросов - возвращает ответ в формате json
	 * и для не аяксов, если удаление успешно редиректит браузер на index
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     *
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        $request = Yii::$app->request;
        $this->findModel(<?= $actionParams ?>)->delete();

        if($request->isAjax){
            /*
            *   Обработка аякс-запроса
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }
		else{
            /*
            *   Обработка обычного, не аякс-запроса
            */
            return $this->redirect(['index']);
        }


    }

     /**
     * Групповое удаление существующих моделей <?= $modelClass ?>
	 * Для аякс-запросов - возвращает ответ в формате json
	 * и для не аяксов, если удаление успешно редиректит браузер на index
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     *
     * @return mixed
     */
    public function actionBulkdelete()
    {        
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Обработка аякс-запроса
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }
		else{
            /*
            *   Обработка обычного, не аякс-запроса
            */
            return $this->redirect(['index']);
        }
       
    }

    /**
     * Ищет <?= $modelClass ?> модель, основываясь на значение ее primary key
     * Если модель не будет найдена, то выбрасывается исключение NotFoundHttpException
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     *
     * @return <?=                   $modelClass ?> the loaded model
     * @throws NotFoundHttpException если модель не будет найдена
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }
		else {
            throw new NotFoundHttpException('Запрошенная страница не существует!');
        }
    }
}
