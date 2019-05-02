<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 14:19
 */

namespace app\extensions;


use app\models\Goods;
use app\models\MailSetting;
use app\models\MsGoods;
use app\models\MsOrder;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Store;
use app\models\YyGoods;
use app\models\YyOrder;

class SendMail
{
    public $store_id;
    public $order_id;
    public $type;

    /**
     * SendMail constructor.
     * @param int $store_id
     * @param int $order_id 订单id
     * @param int $type 订单类型 0--商城订单 1--秒杀订单 2--拼团订单 3--预约订单
     *
     */
    public function __construct($store_id, $order_id, $type = 0)
    {
        $this->store_id = $store_id;
        $this->order_id = $order_id;
        $this->type = $type;
    }

    public function send()
    {
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        if ($this->type == 0) {
            $order = Order::findOne(['id' => $this->order_id]);
            $goods_list = $this->getOrderGoodsList($this->order_id);
        } else if($this->type == 1) {
            $order = MsOrder::find()->where(['id' => $this->order_id])->asArray()->one();
            $goods_list = $this->getMsOrderGoodsList($this->order_id);
        }else if($this->type == 2){
            $order = PtOrder::findOne(['id' => $this->order_id]);
            $goods_list = $this->getPtOrderGoodsList($this->order_id);
        }else if ($this->type == 3){
            $order = YyOrder::find()->where(['id' => $this->order_id])->asArray()->one();
            $goods_list = $this->getYyOrderGoodsList($this->order_id);
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;
        foreach($receive_mail as $mail){
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.exmail.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('setMail', [
                    'store_name' => $store->name,
                    'goods_list'=>$goods_list,
                    'order'=>$order,
                    'type'=>$this->type
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
            }
        }
        return $res;
    }

    /**
     * @param $order_id
     * @return mixed
     * 拼团订单商品详情
     */
    private function getPtOrderGoodsList($order_id)
    {
        $order_detail_list = PtOrderDetail::find()->alias('od')
            ->leftJoin(['g' => PtGoods::tableName()], 'od.goods_id=g.id')
            ->where([
                'od.is_delete' => 0,
                'od.order_id' => $order_id,
            ])->select('od.*,g.name')->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 订单商品详情
     */
    private function getOrderGoodsList($order_id)
    {
        $order_detail_list = OrderDetail::find()->alias('od')
            ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where([
                'od.is_delete' => 0,
                'od.order_id' => $order_id,
            ])->select('od.*,g.name')->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 秒杀订单商品详情
     */
    private function getMsOrderGoodsList($order_id)
    {
        $order_detail_list = MsGoods::find()->alias('g')
            ->leftJoin(['o'=>MsOrder::tableName()],'o.goods_id=g.id and o.id='.$order_id)
            ->where([
                'o.is_delete'=>0,
            ])->select('g.*')->asArray()->all();
        $order_detail_list = MsOrder::find()->alias('o')
            ->leftJoin(['g'=>MsGoods::tableName()],'g.id=o.goods_id')
            ->where(['o.id'=>$order_id,'o.is_delete'=>0])
            ->select(['o.*','g.name'])->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 订单商品详情
     */
    private function getYyOrderGoodsList($order_id)
    {
        $order_detail_list = YyGoods::find()->alias('g')
            ->leftJoin(['o'=>YyOrder::tableName()],'o.goods_id=g.id and o.id='.$order_id)
            ->where([
                'o.is_delete'=>0,
            ])->select('g.*')->asArray()->one();
        return $order_detail_list;
    }

    /**
     * @return bool
     * 新的售后订单
     */
    public function send_refund()
    {
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;
        foreach($receive_mail as $mail){
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.exmail.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('setMailRefund', [
                    'store_name' => $store->name,
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
                return false;
            }
        }
        return $res;
    }

    /**
     * 邮件发送 兑换码
     * @return bool
     */
    public function sendKeyCodeMail($mail, $mobile, $code)
    {
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        //生成sina短地址
        $tUrl = 'http://api.t.sina.com.cn/short_url/shorten.json?source=4223922328&url_long=' . urlencode("https://h5.waijiao365.cn/redeem?p=WDGNzHRy&mobile=" . $mobile . "&redeemCode=" . $code);
        $helper = new CurlHelper();
        $tUrlN = $helper->get($tUrl);
        $tUrlN = json_decode($tUrlN, true);
        if (isset($tUrlN[0]['url_short'])) {
            $shortUrl = $tUrlN[0]['url_short'];
            $type = 1;
        } else {
            $shortUrl = 'http://t.cn/ESM4X70';
            $type = 2;
        }
        $data = [
            'url' => $shortUrl,
            'type' => $type,
            'code' => $code
        ];
        try {
            $mailer = \Yii::$app->mailer;
            $mailer->transport = $mailer->transport->newInstance('smtp.exmail.qq.com', 465, 'ssl');
            $mailer->transport->setUsername($mail_setting->send_mail);
            $mailer->transport->setPassword($mail_setting->send_pwd);
            $compose = $mailer->compose('keyCodeMail', $data);
            $compose->setFrom([$mail_setting->send_mail => '华美一元外教']); //要发送给那个人的邮箱
            $compose->setTo($mail); //要发送给那个人的邮箱
            $compose->setSubject('【华美互动】华美一元外教课程兑换码'); //邮件主题
            $res = $compose->send();

            if ($type == 1) {
                $mobileMsg = '【华美互动】尊敬的用户，您己成功购买的华美一元外教课程，请前往注册确认绑定的手机号 ' . $data['url'] . ' 当天即可学习，7天无理由退款！';
            } else {
                $mobileMsg = '【华美互动】尊敬的用户，您购买的华美一元外教学习兑换码已生成' . $data['code'] . ',请前往兑换页' . $data['url'] . '将兑换码复制拷贝并注册下载当天即可登录学习，7天无理由退款！';
            }
            return [
                'status' => 1,
                'mobile_msg' => $mobileMsg,
                'url' => $data['url']
            ];
        } catch (\Exception $e) {
            \Yii::warning('邮件发送失败：' . $e->getMessage());
        }
    }
}