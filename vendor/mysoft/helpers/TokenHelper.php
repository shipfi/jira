<?php
/*
 * 基于非对称加密方式的签名算法实现常规token管理，这里不对敏感参数进行AES的对称加密
 * 如果存在敏感参数，请自行对该参数进行AES的加密处理
 * 基于sha1 和 base64 基础算法
 * 
 * 使用方法
 * use mysoft\helpers\TokenHelper;
 * 
 * $t = new TokenHelper();
 * 
 * //1. 生成token,这里针对参数生成token，保障参数的完整性，不可串改，但不做内容对称加密处理
 * 
 * $params = ['userid'=>1,"age"=>20];
 * $t->generate($params);
 *
 * 
 * //2.验证token并获取到参数
 * $token  = I('token');
 * try{
 *    $token  = $t->verify($token);
 * 
 * }catch(\Exception $ex){
 * 
 *     echo $ex->getMessage();
 *     exit;
 * }
 * 
 *  $id  = $token['id']; //获取到token参数
 *  ...
 */

namespace mysoft\helpers;
use yii\base\NotSupportedException;
/**
 * TokenHelper
 * @since  \mysoft\helpers\TokenHelper.php  20150916 v1    
 * @author yangzhen
 */
class TokenHelper
{
 
    /**
     * salt值
     * @var string 
     */
    private  $_salt;


    public function __construct($salt='Mysoft95938') 
    {
        if (!function_exists('hash_hmac')) {
            throw new NotSupportedException('PHP "Hash" extension is required.');
        }
        
        $this->_salt = $salt;
    }
    
    
   /**
    * 基于base64 编码
    * @param string $string
    * @return string
    */
    private function _encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    
    /**
     * 基于base64解码
     * @param string  $string
     * @return string
     */
    private function _decode($string)
    {
       $data = str_replace(array('-','_'),array('+','/'),$string);
       $mod4 = strlen($data) % 4;
       if ($mod4) {
          $data .= substr('====', $mod4);
       }
       return base64_decode($data);
    }    
    
    
    
    
    private function _getSign($pstr, $timestamp) {
        $core = $this->_getSignCore($pstr, $timestamp);
        $raw  = sprintf('%s.%s.%s', $pstr, $core, $timestamp);
        return $this->_encode($raw);
    }
    
    
    /**
     * 获取核心密文部分
     * @param string   $appid
     * @param string   $appsecret
     * @param int      $timestamp
     * @return text
     */
    private function _getSignCore($pstr,$timestamp)
    {
        
        $key  = $this->_salt;
        $baseString = sprintf('%s#%s',$pstr,$timestamp);
        $core =  hash_hmac('sha1', $baseString, $key, false);
        return $core;
    }
    
    
    /**
     * 产生token
     * @param array $params
     * @return string
     */
    public function generate($params)
    {
        ksort($params);
        
        $pstr = http_build_query($params);
               
        $token = $this->_getSign($pstr, time());
        
        return $token;
    }
    
    
    //做时间限制
    public function useTimeLimit()
    {
        
    }
    
    //验证token
    public function verify($token)
    {
        
        $baseString  = $this->_decode($token);
        list($pstr,$core,$timestamp) = explode('.',$baseString);
        
        $_core = $this->_getSignCore($pstr, $timestamp);
        
        if( strcasecmp($core,$_core) !== 0){
            throw new \Exception("签名验证失败");
        }
        
        $params  = [];
        parse_str($pstr,$params); //解析参数到数组
        
        return $params;
       
    }
    
}
