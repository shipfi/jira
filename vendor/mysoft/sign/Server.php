<?php

/*
 * 服务端，内部接口通讯
 * 
 * 接口验证方，实现接口权限验证
 */

namespace mysoft\sign;

/**
 * Server端
 * 
 * 调用的时候采用try-catch方式在需要地方加上即可
 *  
 * eg:
 * try{
 *    $server = new \mysoft\sign\Server();
 *    $server->verify();
 * 
 * }catch(\Exception $ex){
 *     echo $ex->getMessage(); //系统错误提示信息
 *     echo $ex->getCode();   //错误提示码，可以根据码去重定义错误描述
 * }
 * 
 * 系统异常错误码对应系统消息描述:
 * 0 - access_token is not allowed,please check  ,这里的access_token是在base类里定义的
 * 1 - 请求验证失败，拒绝访问
 * 2 - 访问超时!
 * 3 - 应用不存在
 * 
 * 
 * @author yangzhen
 */
class Server extends Base {

    /**
     * 解析签名里的应用信息
     * @var type 
     */
    private $_verify_params  = [];
    
    /**
     * 设置有效时间，单位second
     * @var int
     */
    private $_valid_interval = 0;

    public function __construct() {
        
    }

    /**
     * 验证签名逻辑
     * @return boolean
     * @throws \Exception
     */
    public function verify() {
        
        $sign = \Yii::$app->request->get($this->sign_key);

        if (empty($sign)) {
            throw new \Exception("{$this->sign_key} is not allowed,please check",'0');
        }

        $isOk = $this->verifySign($sign);
        
        
        if($isOk === false){
            
            throw  new \Exception("请求验证失败，拒绝访问",'1');
        }
        
        
        //设置检查时间大于0，则进行时间检查
        if($this->_valid_interval){
            
            $server_time  = time();
            $client_time  = (int)$this->_verify_params['timestamp'];
        
            if( $server_time - $client_time > $this->_valid_interval ){
                 throw  new \Exception("访问超时!有效时间:".$this->_valid_interval,'2');
            }
        }
        
        
        
    }
    
    
    /**
     * 设置接入检查的时间单位秒，控制检查签名用的token有限期
     * 调用的时候服务端根据需求来设置
     * @param type $valid_interval
     * @return \mysoft\sign\Server
     */
    public function set_access_time($valid_interval = 0)
    {
        $this->_valid_interval = (int)$valid_interval;  
        return $this;
    }
    
    
    /**
     * 根据appid取配置的secret
     * @param type $appid
     * @return string
     */
    private function _getSecret($appid) {
        
       return $this->getAppSecretById($appid);
       
    }

    /**
     * 验证签名值
     * @param string $sign     * 
     * @param string $appsecret
     * @return boolean 是否匹配
     */
    protected function verifySign($sign) {
        $raw = $this->decode($sign);
        list($appid, $core, $timestamp) = explode('.', $raw);
        $this->_verify_params['appid'] = $appid;
        $this->_verify_params['timestamp'] = $timestamp;
        $appsecret = $this->_getSecret($appid);
        $_core = $this->getSignCore($appid, $appsecret, $timestamp);
        
        
        return strcmp($core, $_core) === 0;
    }

}
