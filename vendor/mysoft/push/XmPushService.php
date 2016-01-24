<?php
namespace mysoft\push;

class XmPushService implements IPushService {
    
    private $sender;
    public function __construct() {
        $setting = \mysoft\pubservice\Conf::getConfig('push_xm');
        $setting = json_decode($setting,true);
        if(is_array($setting) && isset($setting['secret']) && isset($setting['package'])) {
            \xmpush\Constants::setPackage($setting['package']);
            \xmpush\Constants::setSecret($setting['secret']);
            \xmpush\Constants::useOfficial();
        }
        else throw new \Exception('push_xm配置有误');
        $this->sender = new \xmpush\Sender();
    }
    
    public function send($device_tokens,$msg_title='你收到一个新消息',$msg_property=[]) {
        $payload = json_encode($msg_property);
        $message = new \xmpush\Builder();
        $message->title('明源微助手');  // 通知栏的title
        $message->description($msg_title); // 通知栏的descption
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        $message->payload($payload); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $message->extra(\xmpush\Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
        $message->notifyId(rand(0, 4)); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        
        if(count($device_tokens) == 1 && $device_tokens[0] == 'all') {
            $ret = $this->sender->broadcastAll($message);
        }
        else {
            $ret = $this->sender->sendToIds($message, $device_tokens);
        }
        if($ret->getErrorCode() == \xmpush\ErrorCode::Success) {
            return Push::ret($ret->getErrorCode(),$ret->getRaw()['info']);
        }
        else return Push::ret($ret->getErrorCode(),$ret->getRaw()['reason']);
    }
    
    public function _send(\xmpush\Message $message,$regId,$retries=1) {
        return $this->sender->send($message, $regId, $retries);
    }
    
    public function sendToIds(\xmpush\Message $message,$regIdList,$retries=1) {
        $sender = new \xmpush\Sender();
        return $sender->sendToIds($message, $regIdList, $retries);
    }
    
    public function close() {
        $this->sender->close();
    }
    
    public function __destruct() {
        $this->sender->close();
    }
}