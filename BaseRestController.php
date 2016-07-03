<?php

namespace system\v1\components\base;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * The base implementation of rest controller.
 * @author Sergiusz Lebedyn<sergiusz.lebedyn@indsuti.com>
 */
abstract class BaseRestController extends \yii\rest\ActiveController {

    /**
     * set own serializer
     * @var array 
     */
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     *
     * @var callback 
     */
    public $findModelQuery;

    /**
     * setting base behaviors for rest controller
     * @return array
     */
    public function behaviors() {
        $behaviors = array_merge(parent::behaviors(), [
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => [
                        'GET', 'HEAD'
                    ],
                    'view' => [
                        'GET', 'HEAD'
                    ],
                    'create' => [
                        'POST'
                    ],
                    'update' => [
                        'PUT', 'PATCH'
                    ],
                    'delete' => [
                        'DELETE'
                    ]
                ]
            ],
        ]);
        if (Yii::$app->getRequest()->getMethod() != 'OPTIONS') {
            $behaviors['authenticator'] = [
                'class' => HttpBasicAuth::className(),
            ];
        }

        return $behaviors;
    }

    /**
     * Find model by id
     * @param integer $id
     * @return object|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id) {
        $modelClass = $this->modelClass;
        $query = $modelClass::find();

        if (is_callable($this->findModelQuery)) {
            call_user_func($this->findModelQuery, $query);
        }
        $query->andWhere([current($modelClass::primaryKey()) => $id]);
        $model = $query->one();
        if (!$model) {
            throw new NotFoundHttpException("Object with id $id not found");
        }

        return $model;
    }

}
