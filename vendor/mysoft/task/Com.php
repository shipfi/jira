<?php

/*
 * Trait For Common 
 * 公用的task_db的操作方法,其他控制器类可以通过如下使用:
 * use Com;
 * $this->func();
 * ...
 * $this->task_db();//获取task_db数据库连接单例对象
 * 
 * 
 */

namespace mysoft\task;

/**
 * Trait Com
 *
 * 继承的使用方法:
 * class XXXX 
 * {
 *    use Com; 
 *    ...
 * }
 * 
 * @author yangzhen
 */
trait Com
{
     
     /**
      * 获取task_db连接的单例对象
      * @return \yii\db\Connection
      */
     public function task_db($auto_open=true)
     {
         static $db; //单例task_db对象
         
         if( empty($db) ){
             
            $conf  = \mysoft\pubservice\Conf::getConfig('task_db_config');
        
            if(empty($conf)) throw new \Exception ("task_db config not in configsettings");

            $conf  = json_decode($conf,true);
            
            $dbname = 'task';//默认数据库名
            
            if(isset($conf['dbname']) && $conf['dbname']){//便于调试如果配置里填写过dbname的话这里直接替换
               $dbname = trim($conf['dbname']);
            }

            $conn_arr = [
               'dsn' => 'mysql:host='.$conf["host"].';port='.$conf['port'].';dbname='.$dbname,
               'username' => $conf["user_name"],
               'password' => $conf["password"]
            ];

            $db = new \yii\db\Connection($conn_arr);  

            if($auto_open) $db->open();
              
         } 

         return $db;
         
     }
     
     
     
    /**
     * 更新任务状态,task_lists
     * 子类使用方法
     * 
     * $this->updateTaskStatus(...);
     */
    public function updateTaskStatus($task_id,$data)
    {
       
       return $this->task_db()->createCommand()->update('task_lists',$data,'task_id=:task_id',[':task_id'=>$task_id])->execute();
    }
     
    
    /**
     * 插入日志表
     *
     * 子类使用方法
     * 
     * $this->task_log(...);
     * 
     */
    public function task_log($task_id,$orgcode,$task,$task_type,$start_time,$end_time,$msg,$IsSuccess,$exception='')
    {
        
        $log                = [];
        $log['task_id']     = $task_id;
        $log['orgcode']     = $orgcode;
        $log['task']        = $task;
        $log['task_type']   = $task_type;
        $log['start_time']  = $start_time;
        $log['end_time']    = $end_time;
        $log['msg']         = json_encode($msg,JSON_UNESCAPED_UNICODE);//记录执行过程中的信息
        $log['success']     = $IsSuccess; 
        $log['exception']   = $exception; //记录失败时候异常信息
        
        
        $result = $this->task_db()->createCommand()->insert('task_log', $log)->execute();
        
        if($result){
            return  $this->task_db()->lastInsertID;//auto increment ID
        }else{
            return 0;
        }
    
    }
    
    
    
    
}
