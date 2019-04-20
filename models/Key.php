<?php

namespace app\models;

use app\models\common\admin\log\CommonActionLog;
use Yii;

/**
 * This is the model class for table "{{%key}}".
 */
class Key extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%key}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'key',
            'status' => '状态',
            'is_pay' => '是否支付',
            'exchange_time' => '兑换次数',
            'addtime' => 'Addtime',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $data = $insert ? json_encode($this->attributes) : json_encode($changedAttributes);
        CommonActionLog::storeActionLog('', $insert, '', $data, $this->id);
    }
}
