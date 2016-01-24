<?php
namespace mysoft\pubservice;

use Queue\RabbitMQ\Fanout;

class PageVisitService extends Fanout {
 
    /* (non-PHPdoc)
     * @see \Queue\RabbitMQ\Fanout::__construct()
     */
    public function __construct($name='page-visit', $conf = null)
    {
        if(class_exists('AMQPConnection')) {
            $name='page-visit';
            return parent::__construct($name,$conf);
        }
    }

    /* (non-PHPdoc)
     * @see \Queue\RabbitMQ\Fanout::doHandle()
     */
    protected function doHandle($message)
    {
        $msg = json_decode($message,true);
        if(!empty($msg) && isset($msg['tenant_id'])) {
            return DB('log')->createCommand()->insert('page_visit_log', $msg)->execute();
        }
        else throw new \Exception('unknow msg :'.$message);
    }

    static $_this;
    static $_log;
    static function start($tenant_id) {
        $page = \Yii::$app->request->getAbsoluteUrl();
        $starttime = microtime(true)*10000;
        $app = isset(\Yii::$app->params['app_code'])?\Yii::$app->params['app_code']:-1;
        
        if(!isset(static::$_log[$app])) {
            static::$_log[$app] = [];
        }
        static::$_log[$app][$page.$tenant_id] = $starttime;
    }
    
    static function end($tenant_id) {
        $page = \Yii::$app->request->getAbsoluteUrl();
        $endtime = microtime(true)*10000;
        $app = isset(\Yii::$app->params['app_code'])?\Yii::$app->params['app_code']:-1;
        
        if(isset(static::$_log[$app][$page.$tenant_id])) {
            static::put($page, static::$_log[$app][$page.$tenant_id], $endtime, $app, $tenant_id);
        }
    }
    
    static function put($page,$starttime,$endtime,$app,$tenant_id) {
        if( YII_DEBUG != 'true') {
            $user_code = isset(\Yii::$app->user->identity->erpUserCode)?\Yii::$app->user->identity->erpUserCode:'';
            $message = [
                'page'=>$page,
                'starttime' => $starttime,
                'endtime' => $endtime,
                'app' => $app,
                'tenant_id' => $tenant_id,
                'time'=>date('Y-m-d H:i:s'),
                'user_code'=>$user_code,
            ];
            
            if(class_exists('AMQPConnection')) {
                if(!self::$_this) {
                    self::$_this = new static();
                }
                return self::$_this->putMessage(json_encode($message));
            }
        }
    }
    
}