<?php

/*
 * Http服务实现异步模式，基于Swoole扩展
 * 服务swoole_http_server 继承于 swoole_server
 * 非crontab模式
 */

namespace mysoft\task;

use yii\console\Controller;
/**
 * HttpServerController
 *
 * @author yangzhen
 */
class HttpServerController extends TaskBaseController
{
    public $config = array(
        'worker_num' => 2,
        'open_tcp_nodelay' => true,
        'task_worker_num' => 2,
//        'daemonize' => true,
//        'log_file' => './swoole_http_server.log',
    ); 
    
     // 接收每一种event请求的个数
    public static $cnt = array();

    // 各种类型event的基数   做取模后 相加 如email是5个
    public static $event_base = array();

    public static $q_config = array();

 
    public function init() {
        parent::init();
        
        self::$q_config = [
            'Email' => 2,   // 公共邮件处理进程数量
            'Common' =>100,  // 公共进程数量
            'Retry' => 3,   // 公共进程数量,一般处理特殊通道的进程数
            
        ];
        
        $this->mergeConf();
    }
    
   
    /**
     * 如果有重置workers的需求，这里需要重载该方法
     * @return void
     */
    protected function mergeConf()
    {
       //setConf()
       //setTaskWorkers()
       
    }

    //设置系统配置参数
    protected function setCfg($conf)
    {
       //设置worker_num
       if(isset($conf['worker_num'])){
          $this->config['worker_num'] = $conf['worker_num'];
       }  
        
      //设置是否后台模式 
       if(isset($conf['daemonize'])){
           $this->config['daemonize'] = $conf['daemonize'];
       }
         
       //设置日志路径
       if(isset($conf['log_file'])){
           $this->config['log_file'] = $conf['log_file'];
       }
         
    }

    

    /**
     * 设置task workers的数量
     * @param array  $config
     * @param string $prefix
     * @return boolean
     */
    protected function setTaskWorkers($config=[],$prefix='')
    {
        if(empty($config)) return false;
        
        foreach($config as $cf=>$num){  
          if($prefix) $cf = $prefix.$cf;
          self::$q_config[$cf] = $num;       
        }
        
    }
    
    //入口文件
    public function actionRun($hostinfo='0.0.0.0:9580')
    {
         $this->parseHostPort($hostinfo);
         $http = new \swoole_http_server($this->host,$this->port);
         
         //初始化进程数 ,关联getTaskId函数的使用
         $task_num = 0;
         foreach (self::$q_config as $key => $val) {
            self::$event_base[$key] = $task_num;
            self::$cnt[$key] = 0;
            $task_num += $val;
        }

         //初始化配置
         $this->config['task_worker_num'] = $task_num;
         $http->set($this->config);
         
         //接受http请求参数
         $http->on('request',function($request,$response)use($http){
             

         if(!isset($request->get['opt'])){
            $response->end('HTTP_OPT_ERROR:missing opt');
            return;
         }
            
         $opt  = $request->get['opt'];
            
        

 
        if($opt  == 'put'){
            
            //获取data数据
            if(isset($request->get['data'])){
                
               $data  =  $request->get['data'];
               
            }elseif(isset($request->post['data'])){
                
               $data  =  $request->post['data'];
    
            }else{
                
               $response->end('HTTP_PARAM_ERROR:missing data');
               return;
            }


           $data = json_decode($data,true);
           

           if(json_last_error() != JSON_ERROR_NONE){
               $response->end(json_last_error_msg());
               return;
           }

           if( !isset($data['class']) || !isset(self::$q_config[$data['class']]) )
           {
                $type = 'Common';
           }else{
                $type = $data['class'];
           }
            

           $taskId = $this->getTaskId($type);
             
           if(!isset($data['task'])){
              $response->end('HTTP_PARAM_ERROR:missing task');
              return;
              
           }
             
             
             //分配给task的worker
             $http->task($data,$taskId);
             $response->end("HTTP_PUT_OK");
             
        }elseif($opt == 'reload'){
             $http->reload();
             $response->end('reset now!');
             return;
        }elseif($opt == 'shutdown'){
             $http->shutdown();
             $response->end('shutdown now!');
             return;
        }else{
             $response->end("HTTP_OPT_ERROR:nothing to do");
             return;
        }
             
             
             
             
      });
         
        //任务调度
         $http->on('Task',function($http, $taskId, $fromId, $data){
                        
            $rs = $this->doJob($data); 
            return $rs;
         });
         
         //任务完成的响应事件
         $http->on('Finish',function($http, $taskId, $data){
               echo "finish task,the result:".$data."\n";
         });
         
         
         $http->start();
         
    }
    
    
     /**
     * 根据任务类型获取task id
     * @param  string $type
     * @return int
     */
    public function getTaskId($type)
    {
        self::$cnt[$type]++;
        $mod = self::$cnt[$type] % self::$q_config[$type];
        $tid = $mod + self::$event_base[$type];
        return $tid;
    }
    
    
    //任务实现方法
    protected  function doJob($data)
    {  
       
       $task    = $data['task'];
       $app     = isset($data['app']) ? $data['app'] : '';
       $path    = $this->getApp2Dir($app);
       
       $params  = isset($data['params']) ? $data['params'] : [];
 
       if(empty($path) || !is_dir($path)){
          return "应用目录不存在{$path}，请检查路径配置问题";
       }
        
       $params  = json_encode($params);
       
      
       $res = exec("cd {$path} && php yii {$task} '{$params}'",$output,$code);//执行命令行,注意权限问题
       
       $msg  = '';
      
        switch(intval($code))
        {
           case 0:  //正常退出
                return "task_id 执行成功!";
                break;
            
           case 1:  //抛出异常    
               if(is_array($output)) $output = implode ("\n", $output);//异常抛出的body内容进行捕获 
               return $output; //异常信息抛出到主屏
               break;
           
           case 255://致命异常       
               return '未知的致命错误:'.$res;
               break;
               
        }
 
    }
    
   
    //appName对应的绝对路径
    protected  function getApp2Dir($appName)
    {
         $app2dir  = [
                            
         ];
        
         return isset($app2dir[$appName]) ? $app2dir[$appName] : '';
    }
    
   
    
    
}
