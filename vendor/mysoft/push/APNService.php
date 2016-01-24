<?php
namespace mysoft\push;


use mysoft\pubservice\Conf;
/**
 * 推送消息到苹果设备。
 * 
 * 默认的私有pem为basePath目录下面的wzs3.0.pem
 * 
 * 默认的信任根pem为basePath目录下面的entrust_2048_ca.cer
 * 
 * wzs3.0.pem的生成方式为：
 * 
	1. openssl pkcs12 -clcerts -nodes -out cert.pem -in cert.p12  
	2. openssl pkcs12 -nodes -out key.pem -in key.p12   
	3. cat cert.pem key.pem > yourapp.pem  
 * 
 * entrust_2048_ca.cer下载自：https://www.entrust.net/downloads/binary/entrust_2048_ca.cer
 * 
 * 参考文档：https://github.com/immobiliare/ApnsPHP/blob/master/Doc/CertificateCreation.md
 * 
 * 用法：
 * $srv = new APNService(\Yii::$app->basePath);
 * 
 * $ret = $srv->_send('devicetoken','msgtxt','identifier');
 * 
 * if(empty($ret)) {
 *  //send ok
 * }
 * else {
 *  //send fail ['identifier'=>'msg']
 * }
 * 
 * @author fangl
 *
 */
class APNService implements IPushService {
    
    const MSG_EXPIRY = 30;
    
    private $msgs; //待发送消息池
    
    private $push; //apn推送对象
    
    public function __construct() {
        $setting = Conf::getConfig('push_apns');
        $setting = json_decode($setting,true);
        $this->init($setting['base_path']);
    }
    
    /**
     * 
     * @param string $basePath pem文件基址
     * @param string $keyPem 应用私钥文件名（注意是合并之后的）,默认名字为  wzs3.0.pem
     * @param string $certPem 信任根公钥文件名，默认名为 entrust_2048_ca.cer
     * @throws \Exception
     */
    public function init($basePath, $keyPem=null, $certPem=null) {
        
        $basePath = empty($basePath)?'':$basePath;
        
        $certPem = $basePath.'/'.(empty($certPem)?'entrust_2048_ca.cer':$certPem);
        
        if(empty($keyPem) || is_string($keyPem)) {
            $keyPem = $basePath.'/'.(empty($keyPem)?'wzs3.0.pem':$keyPem);
        }
        else if( is_array($keyPem) && count($keyPem) == 2 && is_string($keyPem[0]) && is_string($keyPem[1]) ){
            $keyPem = $basePath.'/'.$keyPem[0];
            $passPhrase = $keyPem[1];
        }
        else throw new \Exception('keyPem必须为一个相对于basePath的pem文件名或者格式为[pem,passphrass]的数组');
        
        $this->push = new \ApnsPHP_Push(
            \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
            $keyPem
        );
        
        if(!empty($passPhrase)) {
            $this->push->setProviderCertificatePassphrase($passPhrase);
        }
 
        //设置信任根证书
        $this->push->setRootCertificationAuthority($certPem);
        $this->push->setLogger(new MyAPNLogger());
    }
    
    public function getLogs() {
        return $this->push->getLogger()->getLogs();
    }
    
    /**
     * 发送消息到苹果设备
     * @param array $tokens 设备id
     * @param string $text 消息标题
     * @param array $customProperty 自定义属性
     * @param array $badget 消息在设备显示的红点中的数值
     * @param string $sound 声音
     * @throws \Exception
     * @return array 正常，返回空数组;异常，返回['tokens'=>'err msg']这种格式的错误数组
     */
    public function send($tokens=null, $msg_title='你收到一个新消息',$msg_property=[], $badget=[], $sound='default') {
        try {
            $this->push->connect();
    
            $invaliduser = [];
            $err = [];
            foreach($tokens as $token) {
                try {
                    $message = new \ApnsPHP_Message($token);
                    
                    $message->setCustomIdentifier($token);
                    
                    //设置消息的小图标（红点中显示的数目
                    $message->setBadge(isset($badget[$token])?intval($badget[$token]):1);
                    //设置消息显示的标题
                    $message->setText($msg_title);
                    
                    // Play the default sound
                    $message->setSound($sound);
                    
                    // Set a custom property
                    foreach($msg_property as $key=>$value) {
                        $message->setCustomProperty($key, $value);
                    }
                    
                    $message->setExpiry(self::MSG_EXPIRY);
                    
                    // Add the message to the message queue
                    $this->push->add($message);
                }
                catch (\ApnsPHP_Message_Exception $e) {
                    $invaliduser[] = $token;
                    $err[$token] = $e->getMessage();
                }
            }
            
            // Send all messages in the message queue
            $this->push->send();
    
            $this->push->disconnect();
        }
        catch (\ApnsPHP_Exception $e) {
            return Push::ret(Push::ERR_IOS_SEND_FAIL,$e->getMessage());
        }
            
        
        $aErrorQueue = $this->push->getErrors();
        if (!empty($aErrorQueue)) {
            foreach($aErrorQueue as $err) {
                $invaliduser[] = $err['MESSAGE']->getCustomIdentifier();
                $err[$err['MESSAGE']->getCustomIdentifier()] = json_encode($err['ERRORS']);
            }
        }
        
        if(empty($err)) {
            return Push::ret(Push::ERR_OK,'ok',[]);
        }
        else {
            return Push::ret(Push::ERR_OK,json_encode($err),$invaliduser);
        }
    }
    
}

class MyAPNLogger implements \ApnsPHP_Log_Interface {
    
    var $logs;
    
    public function log($sMessage) {
        $this->logs[] = sprintf("%s ApnsPHP[%d]: %s\n",date('r'), getmypid(), trim($sMessage));
    }
    
    public function getLogs() {
        return $this->logs;
    }
}
