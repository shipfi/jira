<?php

/* 
 * @author wank
 * 
 */

namespace mysoft\dd;

use mysoft\http\Curl;

class QyHelper{
    
    const DDHTTP = "https://oapi.dingtalk.com";
    
//     const FXTHTTP = 'https://oapi.fosun.com';
//     const FXTHTTP_TEST = 'https://oapiuat.fosun.com:8445';
//     const FXT_CLIENT = "162014";
//     const FXT_MESSAGEAGENTID = "5545482";
    
    public static function getToken($corpid, $corpsecret)
    {
        $curl = new Curl();
        return json_decode($curl->get(self::DDHTTP.'/gettoken?'.http_build_query(['corpid'=>$corpid,'corpsecret'=>$corpsecret])),true);
    }
    
    public static function getTicket($accessToken)
    {
        $curl = new Curl();
        return json_decode($curl->get(self::DDHTTP.'/get_jsapi_ticket?'.http_build_query(['access_token'=>$accessToken,'type'=>'jsapi'])),true);
    }
    
    public static function isDdRetOk($ret) {
        return isset($ret['errcode']) && $ret['errcode'] == 0 || !isset($ret['errcode']);
    }
    
    public static function sign($ticket, $nonceStr, $timeStamp, $url)
    {
        $plain = 'jsapi_ticket=' . $ticket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;
        return sha1($plain);
    }
    
//     public static function sendOA($msg)
//     {
//         $ts = time();
//         $clientid = self::FXT_CLIENT;
//         $secretkey = $this->getSecretKey($clientid);
//         $sign = $this->getFxtSign($ts, $clientid, $secretkey);
//         $curl = new Curl();
//         return json_decode($curl->post(self::FXTHTTP_TEST.'/api/message/sendOa?'.http_build_query(['clientid'=>$clientid,'sign'=>$sign,'timestamp'=>$timestamp])),String::jsonEncode($msg));
        
        
//     }
    
    private function getSecretKey($clientid)
    {
        return md5('FOSUN;'.$clientid) . md5("webapi");
    }
    
    private function getFxtSign($ts,$clientid,$secretkey)
    {
        return md5($ts.$clientid.$secretkey);
    }



    /**
     * 解密钉钉的消息
     * @param type $encrypted_msg 加密消息体
     * @param type $aeskey  加密key
     * @param type $corpid  企业标识
     */
    static function decryptMsg($encrypted_msg, $aeskey, $corpid)
    {
        $prpcrypt = new Prpcrypt($aeskey);
        $content = $prpcrypt->decrypt($encrypted_msg, $corpid);

        if($content)
        {
            return $content;
        }
        else
        {
            return null;
        }
    }
    
}