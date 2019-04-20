<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2018/1/31
 * Time: 16:08
 */

namespace app\modules\api\controllers\exchange;

use app\modules\api\models\KeyForm;
use app\opening\BaseApiResponse;
use app\modules\api\controllers\Controller;
use app\modules\api\models\fxhb\OpenForm;

class IndexController extends Controller
{
    //兑换页面
    public function actionIndex()
    {
        $form = new OpenForm();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;

        return $this->render('/exchange/index', []);
    }
    //key码兑换
    public function actionKeyExchange()
    {
        $form = new KeyForm();
        $form->attributes = \Yii::$app->request->post();
        return new BaseApiResponse($form->exchange());
    }
}
