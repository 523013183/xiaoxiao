<?php
/**
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 9:42
 */

namespace app\modules\api\models;

use app\models\Key;
use app\opening\ApiCode;

/**
 * app\models\Goods $goods
 * app\models\Store $store
 */
class KeyForm extends ApiModel
{
    public $key;
    public $store;
    public $status;
    public $is_pay;
    public $exchange_time;
    public $exchange_count;
    public $phone;

    public function rules()
    {
        return [
            [['key','phone'], 'trim'],
            [['key','phone'], 'required'],
            [['phone'],'match','pattern' =>Model::MOBILE_PATTERN , 'message'=>'手机号错误']
        ];
    }
    public function attributeLabels()
    {
        return [
            'key' => 'key码',
            'phone' => '手机号',
        ];
    }
    //兑换
    public function exchange()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }


        //判断是否存在该兑换码
        $info = Key::findOne([
            'key' => $this->key,
            'is_pay' => 0,
        ]);

        if (!$info) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '兑换码不存在，请重试',
            ];
        }

        //判断是否已经兑换过
        $keyModel = Key::findOne([
            'key' => $this->key,
            'phone' => $this->phone,
            'status' => 1,
        ]);

        if ($keyModel) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '该兑换码已兑换过，请换个兑换码',
            ];
        }

        $info->phone = $this->phone;
        $info->exchange_time = time();
        $info->status = 1;
        $info->exchange_count = $info['exchange_count'] + 1;
        if ($info->save()) {
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