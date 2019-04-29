<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%key_code}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $goods_id
 * @property integer $num
 * @property integer $addtime
 * @property integer $is_delete
 * @property string $attr
 */
class KeyCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%key_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['id', 'order_id', 'status'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'key code',
            'order_id' => 'order id'
        ];
    }
}
