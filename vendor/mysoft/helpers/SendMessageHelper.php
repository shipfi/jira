<?php
/*
 * 消息发送的助手，微信微助手或第三方消息服务
 * 调用方法:
 * 
 * $h = new SendMessageHepler();
 * 
 * $h->add($body1);
 * $h->add($body2);
 * ....
 * 
 * $h->Send(); //POST方式发送消息
 * 
 */

namespace mysoft\helpers;
use mysoft\sign\Client;

class SendMessageHelper extends Client 
{
    /**
     * 全局消息内容体
     * @var array 
     */
    private $_messages = [];
    
    /**
     * 租户tenant_id
     * @var array
     */
    private $_tenant_id;
    
    
    private $_last_index = 0;
    
    
    protected $_results = [];



    public function __construct($appid='') {
       parent::__construct($appid); //调用父类初始化构建请求基础部分
       
    }
    
    /**
     * 设置租户ID
     * @param type $tenant_id
     * @return \mysoft\helpers\SendMessageHelper
     */
    public function setTenantId($tenant_id)
    {
        $this->_tenant_id = $tenant_id;
        return $this;
    }
    
    /**
     * 搜集消息体
     * @param array $body
     * @return \mysoft\helpers\SendMessageHelper
     */
    public function add($body)
    {
        $message = [];
       // $message['url'] = $this->_url($this->tenant_id."/api/message/send?{$this->sign_key}=".$this->_getSign($this->appid, $this->appsecret, time())); 
        $message['url'] = 'http://qy-ci.mysoft.com.cn/api/message/send?access_token=MjAwMDAuMWY1NWRiMzI5Zjg1ZTcwODlkZWI1ZmU1ZGVlNTJlYzFhNmEwMmM3Yy4xNDQwNDkxOTQx';
        $message['body'] = $body;
        $this->_messages = $message;
        return $this;
    }
    
    
    //根据message体构造curl对象
    protected function getCurlHandler($message)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL,$message['url']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $poststr = is_array($message['body']) ? json_encode($message['body']) : $message['body'];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $poststr);
        
        return $ch;
    }


    //按curl_mutil并发
    protected function run($messages)
    {
        $mh = curl_multi_init(); 
        $hanlders = [];
        
        foreach($messages as $message){
          $this->_last_index++;   //递增请求编号
          
          $ch = $this->getCurlHandler($message);
          
          $handlers[] = [
              'index'=>$this->_last_index, //编号
              'ch'=>$ch //curl对象
          ];
          
          curl_multi_add_handle($mh, $ch);    
        }
        
        $active  = null;
        
        //execute the handles && send curl requests
        do{
           $mrc = curl_multi_exec($mh, $active);
        }while($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while($active && $mrc == CURLM_OK){
            
             if(curl_multi_select($mh) != -1){
                 
                 do{
                     $mrc = curl_multi_exec($mh, $actvie);
                     
                 }while($mrc == CURLM_CALL_MULTI_PERFORM);
                 
                 
             }
            
            
        }
        
        //get content
        foreach($handlers as $handler){
            $ch    = $handler['ch'];
            $index = $handler['index'];
            $rs  = curl_multi_getcontent($ch);
            $rs  = json_decode($rs,true);
            $this->_results[$index] = $rs;
        }
        
        
        //end curl multi
        curl_multi_close($mh);
        
    }




    /**
     * 按批次并行发送消息
     * @param int $size
     */
    public function send($size=20)
    {
       $chunks = array_chunk($this->_messages, $size);  
       foreach($chunks as $messages){
           
            $this->run($messages);
           
       }
        
    }
    
    
    
    /**
     * 获取批量发送的结果
     * @return array
     */
    public function getResults()
    {
        return $this->_results;
    }
    
    
}
