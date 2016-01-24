<?php
/**
 * 配置助手
 * @static class Conf
 * @author yangzhen
 *
 */
namespace mysoft\pubservice;
use mysoft\base\Exception;

class Conf
{
    
    /**
     * 获取配内容
     * @access private
     * @return array
     */
    private static function _getConfig()
    {
        try
        {
            $config = DB('config')->createCommand("select * from  configsettings")->queryAll();
        }
        catch(Exception $e)
        {
            throw new \mysoft\base\Exception($e->getMessage(), $e->getCode());
        }

        $arr = array();
        foreach ($config as $list)
        {
        	$arr[$list['KeyName']] = $list['Value'];
        }
        
        return $arr;
    	
    }
    
    
    /**
     * 获取配置
     * @access public
     * @static
     * @example
     *  \mysoft\pubservice\Conf::getConfig('EmailPassword')
     * @param string $keyName
     * @param string $isLocal
     * @return mixed
     */
    public static function getConfig($keyName = '',$isLocal =  false)
    {
        //by fangl 如果本地配置中有某个配置项，则从本地配置中取，否则从库/缓存中取
        if(isset(\Yii::$app->params[$keyName])) {
            return \Yii::$app->params[$keyName];
        }
        if($isLocal){ //本地缓存读file
            return static::fromLocal($keyName);
        }
        else { //直接读cache组件配置
            return static::fromCache($keyName);
        }
    }
    
    
    /**
     * 从本地获取
     * @access public 
     * @example
     *   \mysoft\pubservice\Conf::fromLocal('EmailPassword')
     * @return array
     */
    public static function fromLocal($keyName='all')
    {
        if(!$keyName) return '';
        $cache  = \Yii::$app->localcache;
        $cache_key = 'local_config';
        $config = $cache->get($cache_key);
        
        if($config){
        
        	return $keyName == 'all' ? $config :$config[$keyName];
        
        }else{
        
        	$arr = static::_getConfig();
        
        	$cache->set($cache_key,$arr,60);
        
        	return $keyName == 'all' ? $arr :$arr[$keyName];
        }
        
        
    }
        
       
    /**
     * 从全局配置的缓存cache组件对象获取
     * @access public 
     * @example
     *    \mysoft\pubservice\Conf::fromCache('EmailPassword')
     * @return array
     */
    public static function fromCache($keyName='')
    {
        if(!$keyName)
        	return '';
        
        $config = \Yii::$app->cache->get(['config']);
        
        if(!empty($config) && isset($config[$keyName])) {
        	return $config[$keyName];
        }
        else {
        
            $arr = static::_getConfig();
            
        	\Yii::$app->cache->set(['config'],$arr,60);
        		
        	return $arr[$keyName];
        }
        
        
        
    }
    
    

}