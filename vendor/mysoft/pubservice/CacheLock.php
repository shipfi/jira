<?php
namespace mysoft\pubservice;

/**
 * 缓存锁，避免一些较大粒度的并行。
 * 
 * 注意此锁无法替代一些数据库锁以及一些需要精细控制的场景，请大家谨慎决定是否使用此锁
 * 
 * @author fangl
 *
 */
class CacheLock {
    
    const CACHE_KEY = 'pub_cache_lock_{orgcode}_{event}';
    
    private $orgcode;
    
    private $event;
    
    private $timeout;
    
    /**
     * 
     * @param string $orgcode
     * @param string $event
     * @param number $timeout
     */
    public function __construct($orgcode,$event,$timeout=60) {
        $this->orgcode = $orgcode;
        $this->event = $event;
        $this->timeout = $timeout;
    }
    
    private function key() {
        return [self::CACHE_KEY,$this->orgcode,$this->event];
    }
    
    /**
     * 获取锁，为1代表成功
     * -1 代表其他进程正在处理中
     * -2 代表抢锁失败
     * @return number
     */
    public function getLock() {
        //担心ocs和一般的memcache不一致，add前还是先get一次吧
        $lock = \Yii::$app->cache->get($this->key());
        if( !empty($lock) && $lock['expire_time'] > time() ) {
            echo $lock['process'].'进程正在处理中';
            return -1;
        }
        else {
            //如果锁不为空，则删除先
            if(!empty($lock)) {
                \Yii::$app->cache->delete($this->key());
            }
        
            $lock = [
                'process'=>getmypid(),
                'expire_time' => time() + $this->timeout
            ];
            
            $ret = \Yii::$app->cache->add($this->key(),$lock);
            
            if($ret) {
                return 1;
            }
            else {
                echo $lock['process'].'进程抢锁失败';
                return -2;
            }
        }
    }
    
    /**
     * 释放锁
     * @return \yii\caching\boolean
     */
    public function unLock() {
        return \Yii::$app->cache->delete($this->key());
    }
    
}