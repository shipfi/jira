<?php
namespace  mysoft\web;

use yii\web\CacheSession;

class MyCacheSession extends CacheSession {
    

    public function init() {
        parent::init();
    }
    
    /**
     * 符合mysoft\cache\MyCache规则产生一个cache_key
     * @see \yii\web\CacheSession::calculateKey()
     */
    protected function calculateKey($id)
    {
        \Yii::info('calculateKey:'.$id);
        return ['session_cache_{id}',$id];
    }
    
    public function close()
    {
        \Yii::info(__METHOD__);
        if ($this->getIsActive()) {
            @session_write_close();
            $error = error_get_last();
            if($error) {
                \Yii::error(json_encode($error), __METHOD__);
            }
        }
    }

}