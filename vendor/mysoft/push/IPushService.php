<?php
namespace mysoft\push;

/**
 * 所有的消息推送服务需要继承此接口并实现send方法。
 * 
 * send方法接收$device_tokens,$msg_title,$msg_property三个参数；第一个不能为空，后面可以为空
 * 
 * 返回['errno'=>xxx,'errmsg'=>xxx,'result'=>someotherinfo]] 格式的结果
 * 
 * @author fangl
 *
 */
interface IPushService {
    
    /**
     * 
     * @param array $device_tokens 不能为空，推送的设备识别号
     * @param string $msg_title 默认为'你收到一个新消息'
     * @param array $msg_property
     */
    public function send($device_tokens,$msg_title='你收到一个新消息',$msg_property=[]);
}