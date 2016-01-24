<?php

/*
 * 内部通讯基类
 * 
 * 
 */

namespace mysoft\sign;

use yii\base\NotSupportedException;
use yii\base\Object;
/**
 * 基类定义通用方法
 *
 * @author yangzhen
 */
class Base
{
    
    /**
     * 参与签名的salt
     * @var type 
     */
    private $_salt = '^mysoft.yd2015^';
    
    /**
     * 定义签名的字段名
     * @var string
     */
    protected $sign_key = 'access_token'; 

    /**
     * 默认系统账号
     * @var string
     */
    protected $default_appid     = 'mysoft-ydkf';
    
    /**
     * 默认系统账号的密钥
     * @var string
     */
    protected $default_appsecret = 'mysoftydkf2015';
    
    
    /**
     * 默认的访问组
     * @var array 
     */
    protected $default_app_groups =[
        'mysoft-ydkf'=>'mysoftydkf2015',//系统默认
        'mysoft-yk'=>'yunke95938',
    ];



    public function init()
    {
        if (!function_exists('hash_hmac')) {
            throw new NotSupportedException('PHP "Hash" extension is required.');
        }
    }

    //编码
    protected function encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    
    //解码
    protected function decode($string)
    {
       $data = str_replace(array('-','_'),array('+','/'),$string);
       $mod4 = strlen($data) % 4;
       if ($mod4) {
          $data .= substr('====', $mod4);
       }
       return base64_decode($data);
    }
    
    
    
    /**
     * 获取核心密文部分
     * @param string   $appid
     * @param string   $appsecret
     * @param int      $timestamp
     * @return text
     */
    protected function getSignCore($appid,$appsecret,$timestamp)
    {
        $this->init();//初始化检查核心扩展
        
        $key  = $this->_salt;
        $baseString = sprintf('%s#%s#%s',$appid,$appsecret,$timestamp);
        $core =  hash_hmac('sha1', $baseString, $key, false);
        return $core;
    }

    
    
    /**
     * 根据appid 换取 Secret
     * @return string
     */
    protected function getAppSecretById($appid)
    {
//        if( $appid == $this->default_appid ){//如果默认账号则直接返回对应默认密钥
//            return $this->default_appsecret;
//        } 
     
        if(isset($this->default_app_groups[$appid]))
        {
            return $this->default_app_groups[$appid];//存在即返回对应的value值为密钥
        }
                  
        $apps = $this->getApps($appid);
        if(empty($apps)){
            throw  new \Exception('应用不存在','3');
        }
        return $apps['app_key'];
            
    }
    
    
    /**
     * 获取应用的方法
     * @param string $app_code
     * @return array
     */
     protected function getApps($app_code){
        
         $cache_key = ['api_apps_{app_code}',$app_code];
         $apps = \Yii::$app->cache->get($cache_key);
         
         if($apps){
             
             return $apps;
             
         }else{
             
             $apps = DB('config')->createCommand('SELECT * FROM p_apps WHERE app_code=:app_code',['app_code'=>$app_code])->queryOne();
             if($apps === false) $apps = [];
             \Yii::$app->cache->set($cache_key,$apps,60);
             
             return $apps;
         }
        
    }

}
