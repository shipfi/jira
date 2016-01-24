<?php

namespace mysoft\sms;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\InvalidCallException;
use yii\base\DynamicModel;
use mysoft\http\Curl;
use mysoft\sms\SmsSender;


/**
 * HTTP短信发送
 *
 * @author sunfx
 */
class HttpSmsSender extends Model implements SmsSender {

    public $SendUrl;
    public $CompanyId;
    public $LoginName;
    public $Password;
    public $MockMode = true;
    public $LongSms = 1;
    private $_curl;
    private $_sendErrors = [
        0 => "短信发送失败",
        -1 => "输入参数不完整",
        -2 => "非法来源IP地址或账号密码有误",
        -3 => "目标号码错误",
        -4 => "企业账号余额不足",
        -5 => "用户账号余额不足",
        -6 => "输入参数不完整",
        -7 => "短信服务连接数据库失败",
        -8 => "企业账号已被禁用",
        -9 => "短信内容含有过滤关键字",
    ];

    public function __construct(Curl $curl, $config = []) {
        $this->_curl = $curl;
        parent::__construct($config);
    }

    public function rules() {
        return [
            [['SendUrl', 'CompanyId', 'LoginName'], 'trim'],
            [['SendUrl', 'CompanyId', 'LoginName', 'Password'], 'required'],
            ['SendUrl', 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'SendUrl' => '发送地址',
            'CompanyId' => '公司Id',
            'LoginName' => '登录名称',
            'Password' => '密码',
            'MockMode' => '模拟模式',
        ];
    }

    /**
     * 发送短信
     * @param string $receiveMobileTel 接收手机号,多个号码可以使用;号分隔
     * @param string $message 消息
     * @param string $actionMark 功能点标识
     * @throws InvalidConfigException 初始化配置错误
     * @throws InvalidParamException 参数错误
     * @throws InvalidCallException 调用短信服务发短信出错
     */
    public function send($receiveMobileTel, $message, $actionMark = '') {
        if (!$this->validate()) {
            throw new InvalidConfigException(implode(" ", $this->firstErrors));
        }

        $modeTemp = DynamicModel::validateData(compact('receiveMobileTel', 'message'), [
                    [['message', 'receiveMobileTel'], 'filter', 'filter' => 'trim'],
                    [['receiveMobileTel', 'message'], 'required'],
        ]);

        if ($modeTemp->hasErrors()) {
            throw new InvalidParamException(implode(' ', $modeTemp->firstErrors));
        }
       
        $sendResult = explode(',', $this->sendSms($receiveMobileTel, $message), 2);
        $code = is_int($sendResult[0] + 0) ? $sendResult[0] + 0 : -127;
        $msgId = isset($sendResult[1]) ? $sendResult[1] : '';

        try {
            $log = new SmsSendLog();
            $log->receive_mobile_tel = $receiveMobileTel;
            $log->message = $message;
            $log->action_mark = $actionMark;
            $log->result = $code;
            $log->msg_ids = $msgId;
            $log->save();
        } catch (\Exception $ex) {
            Yii::error($ex);
        }

        if ($code <= 0) {
            throw new InvalidCallException($this->getSendErrorMsg($code));
        }
    }

    private function sendSms($receiveMobileTel, $message) {
        if ($this->MockMode) {
            return '1,' . rand(100000, 9999999);
        }

       
        $outCharset = 'GB2312';
        if (strcasecmp(Yii::$app->charset, $outCharset) !== 0) {
            $message = iconv(Yii::$app->charset, $outCharset.'//IGNORE', $message);
        }
 
        $postData = [
            'CorpID' => $this->CompanyId,
            'LoginName' => $this->LoginName,
            'Passwd' => $this->Password,
            'send_no' => $receiveMobileTel,
            'msg' => $message
        ];


        $postData = http_build_query($postData);//这里做字符串化
        $response = $this->_curl->post($this->SendUrl,$postData);

        if ($this->_curl->getStatus() != 200) {
            throw new InvalidCallException("调用短信服务失败");
        }
       
       return $response;
    }

    private function getSendErrorMsg($errorId) {
        if (!isset($this->_sendErrors[$errorId])) {
            return "未知错误";
        }

        return $this->_sendErrors[$errorId];
    }

}
