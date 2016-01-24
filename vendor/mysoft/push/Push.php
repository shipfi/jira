<?php
namespace mysoft\push;

/**
 * 推送消息到设备
 * 
 * 用法：
 * 
 * 1，单平台推送（android or ios）
 * $push = new Push();
 * $ret = $push->send($platform,$device_tokens,$msg_title,$msg_property);
 * print_r($ret);
 * 
 * $ret格式为['errcode'=>xxx,'errmsg'=>xxx,'result'=>xxxx];
 * 
 * 2，多平台推送
 * 
 * $data = [
          Push::PLATFORM_ANDROID =>  ['000ea0489ea'],
          Push::PLATFORM_IOS => ['565d219ef173bae271a8b714023d54c4b1341dd85ccc5398651c003ac057e3c2'],
    ];
    
   $ret = $push->xSend($data);
 * print_r($ret);
 * 
 * 配置：
 * 
 * push_jpush:
 * {"app_key":"765762a1b6fd18ac662d7d3c","master_secret":"51cb90cc3e974b6c116a5e2b"}
 * 
 * push_apns:
 * {"base_path":"D:/php/apps/wzspem"}
 * 
 * 扩展：
 * 
 * 1，新建一个service，实现IPushService接口
 * 2,在Push::$service中注册它。如果是一个新的平台，如win,你可以新建PLATFORM_WIN = 'win'，然后将这个service注册到这个标识下。
 * 如果是希望替换已有的ios/android平台的推送实现，将类名更改为已有的。
 * 
 * @author fangl
 *
 */
class Push {
    const PLATFORM_IOS = 'ios';
    const PLATFORM_ANDROID = 'android';
    
    static $services = [
        self::PLATFORM_ANDROID => 'mysoft\push\XmPushService',
        self::PLATFORM_IOS => 'mysoft\push\APNService',
    ];
    
    const ERR_OK = 0; //执行成功
    const ERR_UNSUPPORT_PLATFORM = '1001'; //不支持的platform标识
    const ERR_PUSHER_NOT_IMPLEMENT_IPUSHSERVICE = '1002'; //push类未实现IPushService接口
    const ERR_PUSHER_UNCAUGHT_EXCEPTION = '1003'; //push类有未捕捉的错误
    
    const ERR_IOS_SEND_FAIL = '1004'; //ios推送发送失败，1004xxxx
    const ERR_ANDROID_SEND_FAIL = '1005'; //android推送失败，1005xxxx
    const ERR_XSEND_FAIL = '1006'; //ios和android都出错了
    
    private function __construct() {
        
    }
    
    static $pusher;
    
    /**
     * @return \mysoft\push\Push
     */
    public static function getInstance() {
        
        if(!self::$pusher) {
            self::$pusher = new Push();
        }
        return self::$pusher;
    }
    
    static $serviceInst;
    
    public static function getService($platform) {
        if(!isset(self::$serviceInst[$platform])) {
            self::$serviceInst[$platform] = new self::$services[$platform];;
        }
        return self::$serviceInst[$platform];
    }
    
    /**
     * 推送消息到设备（单平台）
     * @param string|mix $platform see self::PLATFORM_XX
     * @param array $device_tokens
     * @param string $msg_title
     * @param array $msg_property
     * @param array $badgets ['token'=>badget] 仅支持IOS设备
     * @return array ['errcode','errmsg','result']
     */
    public function send($platform,$device_tokens=[],$msg_title='你收到一个新消息',$msg_property=[],$badgets=[]) {
        if(is_array($platform)) {
            //支持::send(['platform'=>xx,'device_tokens'=>[1,3,]])这种格式调用
            extract($platform,EXTR_OVERWRITE);
        }
        if(isset(self::$services[$platform])) {
            try {
                $pusher = self::getService($platform);
                if($pusher instanceof IPushService) {
                    $ret = $pusher->send($device_tokens,$msg_title,$msg_property,$badgets);
                    return $ret;
                }
                else return self::ret(self::ERR_PUSHER_NOT_IMPLEMENT_IPUSHSERVICE,"平台{$platform}的推送绑定未实现IPushService接口",self::$services);
            }
            catch (\Exception $e) {
                return self::ret(self::ERR_PUSHER_UNCAUGHT_EXCEPTION, "platform标识符{$platform}的绑定类 ".self::$services[$platform]."抛出未捕获异常", ['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
            }
        }
        else return self::ret(self::ERR_UNSUPPORT_PLATFORM, "platform识别符{$platform}未知");
    }
    
    /**
     * 跨平台推送
     * @param array $data  ['ios'=>[xxx],'android'=>[xxx],'msg_title'=>'optional','msg_property'=>'opt']
     * @return array ['errcode'=>1006/0,'errmsg'=>xxx,'result'=>[ ['errcode'=>xxx,'errmsg'=>xxx] ]]
     */
    public function xSend($data) {
        $arg = ['msg_title' => @$data['msg_title'],'msg_property' => @$data['msg_property']];
        $arg = array_filter($arg);
        $data = array_filter($data,function($value){ return !empty($value); });
        $ret = [];
        foreach(array_keys(self::$services) as $k) {
            if(isset($data[$k])) {
                $arg['platform'] = $k;
                $arg['device_tokens'] = (array)$data[$k];
                $arg['badgets'] = isset($data['badgets'])?$data['badgets']:[];
                $ret[$k] = $this->send($arg);
            }
        }

        $invaliduser = [];
        $errcode = 0;
        $errmsg = '';
        foreach($ret as $k=>$r) {
            $invaliduser[$k] = $ret[$k]['invaliduser'];
            if($r['errcode'] == self::ERR_OK) {
                unset($ret[$k]);
            }
            else {
                $errcode = $ret[$k]['errcode'];
                $errmsg .= $ret[$k]['errmsg'];
            }
        }
        
        if(!empty($errcode) && !empty($errmsg)) {
            return self::ret($errcode, $errmsg, $invaliduser, $ret);
        }
        else return self::ret(self::ERR_OK,'ok',$invaliduser);
    }
    
    static function ret($errcode, $errmsg='ok', $invaliduser=[], $inner_ret=[]) {
        if(empty($inner_ret)) {
            return ['errcode'=>$errcode,'errmsg'=>$errmsg,'invaliduser'=>$invaliduser];
        }
        else return ['errcode'=>$errcode,'errmsg'=>$errmsg,'invaliduser'=>$invaliduser,'innerret'=>$inner_ret];
    }
}