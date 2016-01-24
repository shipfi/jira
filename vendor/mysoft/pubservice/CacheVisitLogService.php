<?php
namespace mysoft\pubservice;

use Queue\RabbitMQ\Fanout;

class CacheVisitLogService extends Fanout {
    
    const DO_SET = 0;  //set操作
    const DO_GET = 1;   //get操作
    const DO_DEL = 2;   //del操作
    const DO_ADD = 3;   //add操作
    const DO_KEY_RULE = 10;  //SetCacheKeyRule操作
    
    
    /* (non-PHPdoc)
     * @see \Queue\RabbitMQ\Fanout::__construct()
     */
    public function __construct($name='cache-visit-log', $conf = null)
    {   
        if(class_exists('AMQPConnection')) {
            $name = 'cache-visit-log';
            $conf = isset(\Yii::$app->params['cache-visit-log-conf'])?\Yii::$app->params['cache-visit-log-conf']:'';
            if(!empty($conf)) {
                $conf = json_decode($conf,true);
                parent::__construct($name,$conf);
            }
        }
    }

    /* (non-PHPdoc)
     * @see \Queue\RabbitMQ\Fanout::doHandle()
     */
    protected function doHandle($message)
    {
        if(isset(\Yii::$app->params['cache-visit-log-conf']) && class_exists('AMQPConnection')) {
            $msg = json_decode($message,true);
            $msg['value'] = substr($msg['value'], 0, 5000);
            $msg['server'] = substr($msg['server'], 0, 5000);
            DB('log')->createCommand()->insert('cache_visit_log', $msg)->execute();
            return true;
        }
        else return false;
    }

    static $_this;
    static function put($do,$key,$value='') {
        $message = [
            'key'=>json_encode($key),
            'value' => substr(json_encode($value),0,5000),
            'server' => substr(json_encode($_SERVER),0,5000),
            'datetime' => time(),
            'do'=>$do,
        ];
        
        if(isset(\Yii::$app->params['cache-visit-log-conf']) && class_exists('AMQPConnection')) {
            if(!self::$_this) {
                self::$_this = new static();
            }
            return self::$_this->putMessage(json_encode($message));
        }
        else if(isset(\Yii::$app->params['cache-visit-log-conf'])) {
            if($key === 'error' || $do == self::DO_KEY_RULE) {
                DB('log')->createCommand()->insert('cache_visit_log', $message)->execute();
            }
        }
    }
}