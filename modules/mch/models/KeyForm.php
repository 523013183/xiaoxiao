<?php
/**
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 9:42
 */

namespace app\modules\mch\models;

use app\models\Key;
use app\models\KeyCode;
use app\opening\ApiCode;
use yii\data\Pagination;

/**
 * app\models\Goods $goods
 * app\models\Store $store
 */
class KeyForm extends MchModel
{
    public $model;
    public $key;
    public $store;
    public $status;
    public $is_pay;
    public $exchange_time;
    public $exchange_count;
    public $phone;
    public $limit = 10;
    public $page = 1;

    public function rules()
    {
        return [
            [['key','phone'], 'trim'],
            [['key'], 'required'],
            [['phone'],'match','pattern' =>Model::MOBILE_PATTERN , 'message'=>'手机号错误']
        ];
    }
    public function attributeLabels()
    {
        return [
            'key' => 'key码',
        ];
    }

    // key查询
    public function search()
    {
        $query = KeyCode::find()->alias('a')->where([
            'status' => 0
        ]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->select([
            'a.*'
        ])->limit($pagination->limit)->offset($pagination->offset)->orderBy('a.id asc')->asArray()->all();
        return [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'pagination' => $pagination,
            'list' => $list,
        ];
    }
    //保存
    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $this->model->attributes = $this->attributes;
        if ($this->model->isNewRecord) {
            $this->model->addtime = time();
        }
        if ($this->model->save()) {
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        }
        return $this->getErrorResponse($this->model);
    }
    //兑换
    public function exchange()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        //判断是否已经兑换过

        $model = Key::findOne([
            'key' => $this->key,
            'phone' => $this->phone,
            'status' => 1,
        ]);
        if ($model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '该兑换码已兑换过，请换个兑换码',
            ];
        }
        $model->key = $this->key;
        $model->phone = $this->phone;
        $model->addtime = time();
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '兑换成功',
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '兑换失败，请稍后重试',
            ];
        }
    }

}