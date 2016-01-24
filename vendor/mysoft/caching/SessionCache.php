<?php
namespace mysoft\caching;
use yii\caching\MemCache;
/**
 * SessionCache
 * master && slave ,double cache mechanism
 * 
 * @author yangzhen
 */
class SessionCache extends MemCache
{

     
    /**
     * 重写set方法，在设置缓存的时候
     * @see \yii\caching\Cache::set()
     */
    public function set($key,$value,$duration=0,$dependency=null)
    {
        if( !$this->isDisableCache()) {
            $a = parent::set($key,$value,$duration,$dependency);
	    if(!$a) {
		  if($this->useMemcached) {
    		     $msg = $this->getMemcache()->getResultMessage();
    		      $code = $this->getMemcache()->getResultCode();
    		      $keytmp = $this->buildKey($key);
    		      file_put_contents('/tmp/mycache.log',  date("Y-m-d H:i:s",time()).json_encode($key)." key {$keytmp} set fail \r\n",FILE_APPEND);
    		      file_put_contents('/tmp/mycache.log',  date("Y-m-d H:i:s",time())."code :{$code}, msg: {$msg} \r\n",FILE_APPEND);
		    }
	    }
            \Yii::$app->slavecache->set($key,$value,$duration,$dependency);
            return $a;
        }
        return false;
    }
    
    /**
     * 
     * 重写get方法，获取缓存的时候
     * @see \yii\caching\Cache::set()
     */
    public function get($key) {
        $ret = parent::get($key);
  
        if($ret === false) {
            
            $ret = \Yii::$app->slavecache->get($key);
            if($ret !== false) {
		$this->delete($key);
                $key = $this->buildKey($key);
                if($this->useMemcached) {
                    file_put_contents('/tmp/mycache.log',  date("Y-m-d H:i:s",time())."key {$key} get from slave \r\n",FILE_APPEND);
                } 
           }
        }
       
        return $ret;
    }

    public function add($key, $value, $duration = 0, $dependency = null) {
       \Yii::$app->slavecache->add($key, $value, $duration, $dependency);
        return parent::add($key, $value, $duration, $dependency);
    }
    
    public function delete($key) {
        
        \Yii::$app->slavecache->delete($key);
        return parent::delete($key);
    }

    /**
     * `内部清理缓存，类似delete
     * `只是不做key规则检查，直接删除指定key
     * 
     * @param string $key
     * @return boolean
     */
    public function remove($key)
    {
        $key = $this->buildKey($key);
        return $this->deleteValue($key);
    }
    
    /**
     * 禁止flush操作，如需flush，请确认目的
     * (non-PHPdoc)
     * @see \yii\caching\Cache::flush()
     * @author fangl
     */
    public function flush()
    {
        throw new \Exception('没事你flush个啥？确认此操作是否合理，否则联系@fangl');
    }
    
    
}
