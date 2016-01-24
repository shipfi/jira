<?php

/*
 * 通知组件发送邮件，短信消息
 *   组件配置:
 * 
 *   'notice'=>[
            'class'=>'mysoft\helpers\notice',
            'smsto'=>['18627801592','13800000000'],
 *          'mailto'=>['26560347@qq.com','27571234@qq.com'],
        ],
 * 
 *     \Yii::$app->notice->sms('测试短信消息');
 *     OR
 *     \Yii::$app->notice->mail('标题','测试邮件');
 * 
 * 
 */

namespace mysoft\helpers;
use mysoft\http\Curl;
use mysoft\pubservice\Conf;
use mysoft\sms\HttpSmsSender;
/**
 * 通知的发送组件
 *
 * @author yangzhen
 */
class Notice 
{
    //邮件对象
    public $mailto=[];
    
    //短信发送对象
    public $smsto=[];
    
    
    
    //发邮件
    public function mail($subject,$content,$mailto=[])
    {
         $url   = "http://120.26.210.193:1218/?name=mail&opt=put";
         $curl  = new Curl();
         
         if($mailto){
            $to = $mailto; 
         }else{
            $to = $this->mailto; 
         }
         
         if(empty($to)){
             throw new \Exception('发送对像不允许为空!');
         }
         
         if(empty($subject)){
             throw new \Exception('发送主题不为空');
         }
         
         if(empty($content)){
             throw new \Exception('发送内容不为空');
         }
         $body = sprintf('%s|%s|%s',  implode(',',$to),$subject,$content);
         return $curl->post($url, $body);
         
    }
    
    
    
    //发短信消息
    public function sms($content,$smsto=[])
    {
        $to = [];
        
        if($smsto){
            $to = $smsto;
        }else{
            $to = $this->smsto; 
        }
        
        if(empty($to)){
            throw  new \Exception('发送对象为空');
        }
        
        
        if(empty($content)){
           throw  new \Exception('内容发送不能为空');
        }
        
        //追加消息尾部
        $content .= "[{$this->_randomCode()}]";
        
        
        
        $receiveMobileTel = implode(';',$to);
        
        $conf = Conf::getConfig('sms_verify_code');
        $conf = json_decode($conf,true);
        $sender = new HttpSmsSender( (new \mysoft\http\Curl()) );
        
        $sender->SendUrl = 'http://sms3.mobset.com/SDK/Sms_Send.asp';
        $sender->CompanyId = $conf['CompanyId'];
        $sender->LoginName = $conf['LoginName'];
        $sender->Password  = $conf['Password'];
        $sender->MockMode  = false;
        
        return $sender->send($receiveMobileTel, $content);
        
    }
    
    
    
    private function _randomCode($len=4)
    {
        
        $num = '';
        for($i=0;$i<$len;$i++){
            $num .= rand(0,9);
        }
        return $num;
    }
    
}
