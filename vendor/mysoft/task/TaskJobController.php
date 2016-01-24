<?php

/*
 * 业务逻辑Job继承的父类
 * 功能:
 * 1.解析父进程分配过来的业务参数params
 * 2.行为日志跟踪，及与父类进程之间的信号交互 0- 成功 ,1-失败，请求父类分配新的task任务重试
 * 
 * 注意方法体中，如果确定job的工作内容顺利完成直接return 0
 * 如果直接抛出异常或未捕获的异常，或主动try-catch捕获异常的行为，或者业务逻辑认为job失败需要重试，则return 1
 * 
 * 
 */

namespace mysoft\task;

/**
 * TaskJobController : Job's Parent
 *
 * @author yangzhen
 */
class TaskJobController extends \yii\console\Controller
{
    use Com { task_db  as protected; }
    
    /**
     * 日志服务
     * @var array 
     */
    protected $loginfo = ['info'=>[]];
    
    /**
     * 业务参数,供方法调用的参数集合
     * @var array
     */
    protected $params  = [];
    
    /**
     * Job参数，其中包含params节点
     * @var array
     */
    protected $data      = [];

    /**
     * 异常的错误信息
     * @var string 
     */
    private  $exception  = '';
    
    
    /**
     * 当前作业的orgcde
     * @var string
     */
    protected $orgcode  = '';




    public function init() {
         parent::init();
         $this->parseParams();//初始化解析参数
         
         //重写exception handler
         set_exception_handler(function($e){
              //捕获错误信息
              $this->exception = \yii\base\ErrorHandler::convertExceptionToString($e); 
         });
         
         
         //重写register_shutdown
         register_shutdown_function(function(){//非致命错误脚本结束写日志
            

      
             $task        =  isset($this->data['task']) ? $this->data['task'] : '';
             $task_id     =  isset($this->data['info']['task_id']) ? $this->data['info']['task_id'] : 0;
             $orgcode     =  isset($this->data['info']['orgcode']) ? $this->data['info']['orgcode'] : ''; 
             $task_type   =  isset($this->data['info']['task_type']) ? $this->data['info']['task_type'] : '';
             $start_time  =  isset($this->data['info']['start_time']) ? $this->data['info']['start_time'] : '';
             
             $msg  = [];
             $msg['info'] = $this->loginfo['info'];
             $msg['exitStatus'] =\Yii::$app->response->exitStatus;
             
             if( isset($this->data['retry_times'])){
                 $msg['retry_times'] = $this->data['retry_times'];
             }
             
             $end_time  = date('Y-m-d H:i:s');
             
             
             if($this->exception){
                 $msg['exitStatus'] =1;
                 $IsSuccess = 0;
                 
             }elseif( $msg['exitStatus'] == 0){
                 $IsSuccess = 1;
                 
             }elseif( $msg['exitStatus'] == 1 ){
                 $IsSuccess = 0;
             }
             
             
             $this->task_log($task_id,$orgcode,$task, $task_type, $start_time, $end_time, $msg, $IsSuccess,$this->exception);
             
             if($IsSuccess){ //成功则更细
                 
                 $this->updateTaskStatus($task_id,['progress'=>0,'retry_times'=>0]); //更新任务状态
                 
             }else{//失败则更新
                 
                 $retry_times = isset($this->data['retry_times']) ? $this->data['retry_times'] :0;
//                 $this->updateTaskStatus($task_id,['retry_times'=>$retry_times]); //更新任务状态,重试次数
                 $this->updateTaskStatus($task_id,['progress'=>0,'retry_times'=>0]); //更新任务状态

             }
             
             if($this->exception){
                 echo $this->exception,PHP_EOL; 
                 exit(1);
             }
            
         });
     }
     
     
     /**
      * 写日志操作
      * @param type $msg
      */
     protected function logging($msg)
     {
         $this->loginfo['info'][]=$msg;
     }
     
     
     //获取业务命令行参数，解析Job参数和调用方法的函数参数
     protected function parseParams()
     {
         global $argv;
         
         if(isset($argv[2])){//存在参数则解析
            
             $this->data = json_decode($argv[2],true);
             if(isset($this->data['params'])){
                $this->params = $this->data['params'];
             }
             
             if(isset($this->data['info']['orgcode'])){
                $this->orgcode = $this->data['info']['orgcode'];
             }
             
             
         }
         
          
     }
     

    
 
    
}
