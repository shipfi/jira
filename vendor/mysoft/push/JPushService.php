<?php
namespace mysoft\push;

use mysoft\http\Curl;
use mysoft\push\IPushService;
use mysoft\pubservice\Conf;

class JPushService implements IPushService {
    
    const PORT_URL = 'https://api.jpush.cn/v3/push';
    const VALID_URL = 'https://api.jpush.cn/v3/push/validate';
    
    var $appKey;
    var $masterSecret;
    
    var $curl;
    
    var $data;
    
    public function __construct() {
        $setting = Conf::getConfig('push_jpush');
        $setting = json_decode($setting,true);
        $this->init($setting['app_key'], $setting['master_secret']);
    }
    
    public function init($appKey,$masterSecret) {
        $this->appKey = $appKey;
        $this->masterSecret = $masterSecret;
        $this->curl = new Curl();
        $headers = [
            'Authorization' => 'Basic '.base64_encode("{$this->appKey}:{$this->masterSecret}"),
            'Content-Type' => 'application/json'
        ];
        $this->curl->setHeaders($headers);
    }
    
    public function send($device_tokens,$msg_title='你收到一个新消息',$msg_property=[]) {
        $data = [
            'platform'=>['android'],
            'audience'=>[
                'registration_id'=>$device_tokens 
            ],
            'notification'=>[
                'android'=> [
                    'alert'=>$msg_title,
                    'extras'=>$msg_property,
                ]
            ]
        ];
        $ret = $this->_send($data);
        //jpush推送发送出去即代表成功，不保证可达性（因为无法返回未达用户）
        if( isset($ret['sendno']) && $ret['sendno'] >= 0 ) {
            return Push::ret(Push::ERR_OK);
        }
        else if(isset($ret['error'])) {
            return Push::ret(Push::ERR_ANDROID_SEND_FAIL.$ret['error']['code'],$ret['error']['message'],$device_tokens);
        }
        else return Push::ret(Push::ERR_ANDROID_SEND_FAIL,'jpush发送失败:'.json_encode($ret),$device_tokens);
    }
    
    public function _send($data) {
        if(empty($data)) {
            $data = $this->data;
            $this->data = [];
        }
        
        if(!empty($data)) {
            $ret = $this->curl->post(self::PORT_URL, json_encode($data));
            return json_decode($ret,true);
        }
        else return ['error'=>['code'=>1,'message'=>'欲发送的消息内容为空']];
    }
    
    public function valid($data) {
        $ret = $this->curl->post(self::VALID_URL, $data);
        if($this->curl->getStatus() == 200) {
            $ret = json_decode($ret,true);
            if($ret['sendno'] == 0) {
                return [];
            }
            else return $ret;
        }
        return [$ret];
    }
}
