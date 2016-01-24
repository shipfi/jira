<?php
/**
 * 本地的memcached缓存
 * @author sglz
 *
 * error code
 *            100020
 *            100021 后台没有生成keyrule缓存
 *            100022 没有找到对应的缓存规则
 *
 */
namespace mysoft\caching;

use yii\caching\MemCache;

class MyCache2 extends MemCache
{

    const KEY_RULE_KEY = 'mycache.key_rule';

    //final key
    private function _key($key)
    {
       return $this->keyPrefix.md5($key);
    }


    /**
     * @param array $key ['rule','replacement','replacement',...]
     * @return string
     * @throws \mysoft\base\Exception|void
     */
    public function buildKey($key)
    {
        if (is_string($key)) {
            throw E('不合法的CacheKey规则，key规则应为：["rule","replacement","replacement",...]','100020');
        } 
        else {
            //$this->findKeyRule($key);
            $key = $this->buildKeyByRule($key);
        }
        return $this->_key($key);
    }

    /**
     * 检查某个key规则是否存在
     * @param mixed $key 为字符串all时，返回规则数组；为数组时，校验key是否在规则数组中
     * @return mixed|boolean
     */
    public function findKeyRule($key = 'all')
    {
        if (is_array($key)) {
            $key = array_shift($key);
        }
        
        if(!is_string($key)) {
            throw E('缓存规则必须为字符串:'.json_encode($key));
        }
        
        $key_rules = $this->getValue(self::KEY_RULE_KEY);
        
//         \Yii::warning(gettype($key_rules).":".serialize($key_rules),__CLASS__);
        if ($this->serializer === null && !is_array($key_rules) && $key_rules) {
            $key_rules = unserialize($key_rules);
        } 
        else if(!is_array($key_rules) && $key_rules) {
            $key_rules = call_user_func($this->serializer[1], $key_rules);
        }
        
        if(!$key_rules) {
            $key_rules = $this->SetCacheKeyRule();
        }
        
//         //出现直接从cache中获取某个值的时候，返回一个没有unserialize的值
//         if(!is_array($key_rules)) {
//             $key_rules = unserialize($key_rules);
//         }
        
        if($key == 'all') {
            return $key_rules;
        }
        else {
            if(!in_array($key,$key_rules)) {
                throw E("缓存规则{$key}未注册!",'100022');
            }
            else return true;
        }
    }

    /**
     * 初始化缓存规则缓存
     * @return Ambigous <underscore, unknown>
     */
    public function SetCacheKeyRule()
    {
        $sql = 'select cache_key from cache_key_rule';
//         $key_rules = DB('config')->createCommand($sql)->queryAll();
//         $key_rules= __()->pluck($key_rules,'cache_key');

        $key_rules = DB('config')->createCommand($sql)->queryColumn();
        $this->setValue(self::KEY_RULE_KEY,$key_rules,0);
        return $key_rules;
    }

    protected function buildKeyByRule($key)
    {
        $rule = array_shift($key);
        //$str = preg_replace('({\w+})',$key,$rule);
        preg_match_all('({\w+})',$rule,$replace);
        foreach($replace[0] as $li )
        {
            $rule = str_replace($li,array_shift($key),$rule);
        }
        
        return $rule;
    }

//     public function set($key,$value,$duration=0,$dependency=null)
//     {
//         $a = parent::set($key,$value,$duration,$dependency);
//         if($a)
//         {
//             $realkey = $this->keyPrefix.$this->buildKeyByRule($key);
//             $isunique = DB("config")->createCommand("select id from cache_key_list where `key` = :key",[':key'=>$realkey])->queryOne();
//             if($isunique)
//             {
//                 return true;
//             }
//             $data = ['cache_key_rule'=>$key[0],'key'=>$realkey,'create_time'=>date('Y-m-d H:i:s')];
//             return DB("config")->createCommand()->insert("cache_key_list",$data)->execute();
//         }
//     }
    
    /**
     * 重写set方法，在设置缓存的时候，进行规则校验并插入存储的key，注意这里key-rule的长度不用超过100
     * @see \yii\caching\Cache::set()
     */
    public function set($key,$value,$duration=0,$dependency=null)
    {
        if($this->findKeyRule($key)) {
            $a = parent::set($key,$value,$duration,$dependency);
//            if($a) {
//                $sql = 'insert ignore into `cache_key_list` ( `cache_key_rule`, `key` , `create_time`) values (:CKR,:K,:CT)';
//                DB('config')->createCommand($sql,[':CKR'=>$key[0],':K'=>$this->keyPrefix.$this->buildKeyByRule($key),':CT'=>date('Y-m-d H:i:s')])->execute();
//                return true;
//            }
            return $a;
        }
        return false;
    }
    
    public function get($key) {
        $ret = parent::get($key);
        return $ret;
    }
    
    public function add($key, $value, $duration = 0, $dependency = null) {
        return parent::add($key, $value, $duration, $dependency);
    }
    
    public function delete($key) {
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
