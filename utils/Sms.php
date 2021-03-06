<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/3
 * Time: 14:58
 */

namespace app\utils;

use app\models\SmsRecord;
use app\models\SmsSetting;
use Opening\Sms\Messages\TemplateMessage;
use Opening\Sms\Messages\VerificationCodeMessage;
use Opening\Sms\Senders\AlidayuSender;
use Opening\Sms\Senders\AliyunSender;

class Sms
{
    /**
     * 发送短信
     *
     * 短信通知
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function send($store_id, $content = null)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
        $content_sms[$sms_setting->msg] = substr($content, -8);
        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        foreach ($g as $mobile) {
            if (!$mobile) {
                continue;
            }
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $sms_setting->tpl,
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile
                ];
                $message = new TemplateMessage($messageParams);
                $res = $message->send();
            } catch (\Exception $e) {
                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $sms_setting->tpl,
                        'tplParams' => $content_sms,
                        'phoneNumber' => $mobile
                    ];
                    $message = new TemplateMessage($messageParams);
                    $resp = $message->send();
                } catch (\Exception $r_e) {
                    \Yii::warning("阿里大鱼调用失败：" . $r_e->getMessage());
                    return [
                        'code' => 1,
                        'msg' => $e->getMessage() . $r_e->getMessage()
                    ];
                }
            }
        }
        if (is_array($content_sms)) {
            foreach ($content_sms as $k => $v) {
                $content_sms[$k] = strval($v);
            }
            $content_sms = json_encode($content_sms, JSON_UNESCAPED_UNICODE);
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $sms_setting->tpl;
        $smsRecord->content = $content_sms;
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }

    /**
     * 发送短信  退款通知
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function send_refund($store_id, $content = null)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
//        $content_sms[$sms_setting->msg] = substr($content, -8);
        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        $tpl = json_decode($sms_setting->tpl_refund, true);
        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置退款短信'
            ];
        }
        foreach ($g as $mobile) {
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $tpl['tpl'],
                    'tplParams' => [],
                    'phoneNumber' => $mobile
                ];
                $message = new TemplateMessage($messageParams);
                $res = $message->send();
            } catch (\Exception $e) {
//                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $tpl['tpl'],
                        'tplParams' => [],
                        'phoneNumber' => $mobile
                    ];
                    $message = new TemplateMessage($messageParams);
                    $res = $message->send();
                } catch (\Exception $r_e) {
                    return [
                        'code' => 1,
                        'msg' => $e->getMessage().$r_e->getMessage()
                    ];
                }
            }
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = '';
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }


    public static function send_text($store_id, $content = null, $mobile)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        $mobile_cache = \Yii::$app->cache->get('mobile_cache' . $mobile);
        if ($mobile_cache) {
            return [
                'code' => 1,
                'msg' => '请勿频繁发送短信',
                'data' => $mobile,
            ];
        }
        \Yii::$app->cache->set('mobile_cache' . $mobile, true, 60);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
        if (!$mobile) {
            return [
                'code' => 1,
                'msg' => '请输入手机号'
            ];
        }
        $tpl = json_decode($sms_setting->tpl_code, true);
        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置验证码短信'
            ];
        }
        $content_sms[$tpl['msg']] = $content;
        $res = null;
        $resp = null;

        try {
            $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
            $messageParams = [
                'sender' => $sender,
                'sign' => $sms_setting->sign,
                'tplId' => $tpl['tpl'],
                'tplParams' => $content_sms,
                'phoneNumber' => $mobile
            ];
            $message = new VerificationCodeMessage($messageParams);
            $message->codePointer = &$message->tplParams['code'];
            $res = $message->send();
            $content_sms[$tpl['msg']] = $message->codePointer;
            \Yii::$app->cache->set('code_cache' . $mobile, $message, 600);
        } catch (\Exception $e) {
            \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
            try {
                \Yii::$app->cache->delete('mobile_cache' . $mobile);
                $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $tpl['tpl'],
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile
                ];
                $message = new VerificationCodeMessage($messageParams);
                $message->codePointer = &$message->tplParams['code'];
                $res = $message->send();
                $content_sms[$tpl['msg']] = $message->codePointer;
                \Yii::$app->cache->set('code_cache' . $mobile, $message, 600);
            } catch (\Exception $r_e) {
                \Yii::$app->cache->delete('mobile_cache' . $mobile);
                return [
                    'code' => 2,
                    'msg' => $r_e->getMessage().$e->getMessage()
                ];
            }
        }
        if (is_array($content_sms)) {
            foreach ($content_sms as $k => $v) {
                $content_sms[$k] = strval($v);
            }
            $content_sms = json_encode($content_sms, JSON_UNESCAPED_UNICODE);
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = $content_sms;
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }

    /**
     * 发送短信（泰迪熊）
     * @param unknown $mobile
     * @param unknown $smsSign
     * @param unknown $smsParam
     * @param unknown $templateCode
     */
    public function sendSmsByTeddy($mobile, $smsParam){
//        $url = "http://server-usp.teddymobile.cn/api/sms/vSend";
        $url = "http://server-usp.teddymobile.cn/api/sms/multiSend";
        if (is_array($smsParam)) {
            $paramData = implode("##", $smsParam);
        } else {
            $paramData = $smsParam;
        }
        //生成sina短地址
        $tUrl = 'http://api.t.sina.com.cn/short_url/shorten.json?source=4223922328&url_long=' . urlencode("https://h5.waijiao365.cn/redeem?p=WDGNzHRy&mobile=" . $mobile . "&redeemCode=" . $paramData);
        $helper = new CurlHelper();
        $tUrlN = $helper->get($tUrl);
        $tUrlN = json_decode($tUrlN, true);
        if (isset($tUrlN[0]['url_short'])) {
            $shortUrl = $tUrlN[0]['url_short'];
            $paramData = $shortUrl;
//            $template = '尊敬的用户，您己成功购买的华美一元外教课程，请前往注册确认绑定的手机号{} 当天即可学习！';
            $template = '恭喜你，课程购买成功！我们的客服人员会第一时间与您联系！你也可以直接添加客服微信：18150380775（手机微信同号）完成课程兑换~';
        } else {
            $shortUrl = 'http://t.cn/ESM4X70';
            $paramData = $paramData . '##' . $shortUrl;
//            $template = '尊敬的用户，您购买的华美一元外教学习兑换码已生成{},请前往兑换页{}将兑换码复制拷贝并注册下载当天即可登录学习！';
            $template = '恭喜你，课程购买成功！我们的客服人员会第一时间与您联系！你也可以直接添加客服微信：18150380775（手机微信同号）完成课程兑换~';
        }
        /*$post_data = [
            "account"=>'td_hmhd',
            "password"=> '9699e76157131755704e4733adbe6643',
            "data" => [
                'sign' => '【华美互动】',
                "template"=> $template,
                "param" => [
                    $mobile => $paramData
                ]
            ]
        ];*/
        $post_data = [
            "account"=>'td_hmhd',
            "password"=> '9699e76157131755704e4733adbe6643',
            "data" => [
                'phones' => $mobile,
                'sign' => '【华美互动】',
                "content"=> $template
            ]
        ];

        $resp = $helper->post($url, json_encode($post_data));
        $resp = json_decode($resp);
        if ($resp && $resp->error_code==0) {
            return array('status' => 1, 'msg' => '已发送成功, 请注意查收');
        } else {
            $paramData = explode('##', $paramData);
            $num = substr_count($template,'{}');
            for ($i = 0;$i <= $num-1;$i++) {
                $template = preg_replace("/{}/",$paramData[$i], $template,1);
            }
            $template = $post_data['data']['sign'] . $template;
            \Yii::warning('短信发送失败：' . $resp->error_msg.' , 错误代码:'.$resp->error_code  . "，值" . json_encode($post_data));
            return array('status' => -1, 'msg' => $template, 'data' => $template);
        }

    }
}
