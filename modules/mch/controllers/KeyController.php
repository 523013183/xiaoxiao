<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 10:56
 */

namespace app\modules\mch\controllers;

use app\models\Key;
use app\modules\mch\models\KeyForm;

/**
 * Class GoodController
 * @package app\modules\mch\controllers
 * 商品
 */
class KeyController extends Controller
{

    /**
     * key管理
     * @return string
     */
    public function actionKey()
    {
        $form = new KeyForm();
        $res = $form->search();

        return $this->render('key', [
            'list' => $res['list'],
            'pagination' => $res['pagination'],
        ]);
    }
    /**
     * key添加
     */
    public function actionKeyEdit($id = null)
    {
        $model = Key::findOne(['id' => $id]);
        if (!$model) {
            $model = new Key();
        }
        if (\Yii::$app->request->isPost) {
            $form = new KeyForm();
            $form->attributes = \Yii::$app->request->post('model');
            $form->model = $model;
            return $form->save();
        }
        return $this->render('key-edit', [
            'model' => $model,
        ]);
    }
}