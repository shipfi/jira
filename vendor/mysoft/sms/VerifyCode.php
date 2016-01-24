<?php

/*
 * 短信消息验证码验证逻辑封装
 * 提供两个核心功能
 * 1.根据租户发送租户设置的模板内容
 * 2.验证用户提交的验证码
 * 
 */

namespace mysoft\sms;

use Yii;
use mysoft\pubservice\Conf;

/**
 * 短信验证码对象
 *
 * @author yangzhen
 */
class VerifyCode 
{
    /**
     * 短信消息发送对象
     * @var string 
     */
    private $_sender;
    
    /**
     * 租户标识 tenant_id
     * @var string
     */
    private $_tenant_id;
    
    /**
     * 接受者标识
     * @var string 
     */
    private $_recevierId;

    /**
     * 随机生成的验证码
     * @var string
     */
    private $_verify_code;
    
    
    /**
     * 缓存有效期,也是验证码的有效期
     * @var int
     */
    private $_cache_expiretime = 60;
    
    
    /**
     * 时间描述
     * @var string 对过期时间的描述
     */
    private $_desc_time = '1分钟';




    /**
     * 验证码的位数
     * @var int 
     */
    private $_verify_code_len = 4;



    public function __construct($orgcode='') {
          
        $conf = Conf::getConfig('sms_verify_code');
        $conf = json_decode($conf,true);
        $this->_sender = new HttpSmsSender( (new \mysoft\http\Curl()) );
        
        $this->_sender->SendUrl = 'http://sms3.mobset.com/SDK/Sms_Send.asp';
        $this->_sender->CompanyId = $conf['CompanyId'];
        $this->_sender->LoginName = $conf['LoginName'];
        $this->_sender->Password  = $conf['Password'];
        $this->_sender->MockMode  = false;
        
        $this->_tenant_id = $orgcode;
        
    }
    
    
    /**
     * 获取当前缓存的key
     * @return string
     */
    private function _getVerifyCodeCacheKey()
    {
        if(empty($this->_tenant_id)){
            throw new \Exception('tenant_id不能为空',1);
        }
        
        if(empty($this->_recevierId)){
            throw new \Exception('recevierId不能为空',1);
        }

        return ['verifycode_{tenant_id}_{recevierId}',$this->_tenant_id,$this->_recevierId];
    }


    /**
     * 获取模板内容
     * @return string
     * @throws \Exception
     */
    private function _getSmsContent()
    {
        $template  = $this->_getTemplate($this->_tenant_id);
     
        
        if( empty($template) || strpos($template,'[code]') === false){
            throw new \Exception('不可用的模板，缺少关键标识[code]',2);
        }
        
        $this->_verify_code = $this->_randomCode();
        
        $time  = $this->_desc_time; //对时间的描述
   
        $template = str_replace(['[code]','[time]'], [$this->_verify_code,$time], $template);
        return $template;
        
    }

    /**
     * 根据租户标识找模板内容
     * @param type $tenant_id
     */
    private  function _getTemplate($tenant_id)
    {
          
       $res = DB('config')->createCommand("select * from sms_template where tenant_id=:tenant_id",['tenant_id'=>$tenant_id])->queryOne();
      
       return isset($res['content']) ? $res['content'] : ''; 
    }
    /**
     * 生成随机的四位数
     * @param int $len 默认为4位
     * @return string
     */
    private function _randomCode()
    {
        $len = $this->_verify_code_len;
        
        $num = '';
        for($i=0;$i<$len;$i++){
            $num .= rand(0,9);
        }
        return $num;
    }
    
    
    /**
     * 设置过期时间
     * @param int $interval
     * @return \mysoft\sms\VerifyCode
     */
    public function setValidTimeOfCode($interval)
    {   
        
        if(stripos($interval,'m')){//如果设置为分钟
            $interval = str_ireplace('m', '', $interval);
            $interval = trim($interval);
            if($interval){    
               $this->_cache_expiretime = (int)$interval * 60;
               $this->_desc_time = $interval . '分钟';
            }
            
            
        }else{//否则按秒计算
            
            if($interval){    
                 $this->_cache_expiretime = (int)$interval;
                 $this->_desc_time = $interval .'秒';
            }
        }
        

        return $this;
    }
    
    
    /**
     * 设置验证码的长度
     * @param int $len
     * @return \mysoft\sms\VerifyCode
     */
    public function setVerifyCodeLen($len)
    {
        if($len){
            $this->_verify_code_len = $len;
        }
        
        return $this;
    }
    
    
   /**
    * 设置接受方的标识
    * @param string $recevierId
    * @return \mysoft\sms\VerifyCode
    */ 
   public function setReceiverId($recevierId)
   {
       $this->_recevierId = $recevierId;
       return $this;
   }
    
   
   /**
    * 发送验证短信的消息
    * @param string $receiveMobileTel  发送对象电话号码
    */
   public function send($receiveMobileTel)
   {
       $message  = $this->_getSmsContent();
       $code = $this->_verify_code;

       if(empty($this->_recevierId) && $receiveMobileTel){
           //如果没特别设置接受用户的标识，这里直接取接受人电话做唯一标识
            $this->_recevierId = $receiveMobileTel;
       }
       
       $cache_key = $this->_getVerifyCodeCacheKey();
       $this->_cache_expiretime += 30; //额外延迟30s做误差补充
       $status = Yii::$app->cache->set($cache_key,$code,$this->_cache_expiretime);//设置验证的时间
       
       if($status){//先验证写缓存，然后发送消息
           $this->_sender->send($receiveMobileTel, $message);
       }else{
           throw new \Exception('短信发送失败，请重试',-5);
       }
              
   }
    
   /**
    * 检查验证码
    * @param string $code             用户验证码
    * @param string $receiveMobileTel 用户验证电话
    * @return boolean
    * @throws \Exception
    */
   public function checkCode($code,$receiveMobileTel='')
   {
   
       if(empty($this->_recevierId) && $receiveMobileTel){
           
           $this->_recevierId = $receiveMobileTel; //用接受人电话做验证标识
       }
       
       $cache_key = $this->_getVerifyCodeCacheKey();

       $_code='';
       //由于缓存中取验证码加入重试机制
       for ($i = 0; $i < 3; $i++) {
           if (!empty($_code)) {
               break;
           }
           $_code  = Yii::$app->cache->get($cache_key);
       }

       if(empty($_code)){
           throw new \Exception('无效验证码，请重新获取。',-3);
       }
       
       if(strcmp($_code,$code) !== 0){
            throw new \Exception('验证码错误，请重新输入。',-4);
       }
       
       Yii::$app->cache->delete($cache_key);//验证通过则清理掉缓存
       
       
       return true;
       
   }
   
    
}
