<?php

/*
 * 接收来自TaskTimer的异步过来的调度消息，阻塞完成task并在finish响应TaskTimer
 * TaskTimer可以在receive事件logging
 * 
 */

namespace mysoft\task;
/**
 * 任务服务类: TaskServerController
 *
 * @author yangzhen
 */
class TaskServerController extends TaskBaseController 
{
     use Com;//继承Com Trait
    
     public $config = array(
       'worker_num' => 1,
       'task_worker_num' => 2,
       'task_ipc_mode' => 1,
       'heartbeat_check_interval' => 300,
       'heartbeat_idle_time'      => 300,
//        'log_file'=>'/Users/yangzhen/desktop/print.log',
//        'daemonize '=>true
    );

    // 接收每一种event请求的个数
    public static $cnt = array();

    // 各种类型event的基数   做取模后 相加 如email是5个
    public static $event_base = array();

    public static $q_config = array();
    
    public $num = 0;

    //优先加载
    public function init() {
        parent::init();
        //event worker & task worker nums
        self::$q_config = [
            'Common' =>100,  // 公共进程数量
            
        ];
        
        $this->mergeConf();
        
    }

    
    /**
     * 如果有重置workers的需求，这里需要重载该方法
     * @return void
     */
    protected function mergeConf()
    {
        //如果有额外的task workers 数量限制在这里实现
        //设置workers nums需要直接调用setTaskWorkers
        //$this->setTaskWorkers();
       
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


    /**
     * 入口函数
     * @param type $hostinfo
     */
    public function actionRun($hostinfo='0.0.0.0:9550')
    {
        $this->parseHostPort($hostinfo);
        
        $serv = new \swoole_server($this->host, $this->port);
        //初始化进程数 ,关联getTaskId函数的使用
        $task_num = 0;
        foreach (self::$q_config as $key => $val) {
            self::$event_base[$key] = $task_num;
            self::$cnt[$key] = 0;
            $task_num += $val;
        }

        //初始化配置
        $this->config['task_worker_num'] = $task_num;
        $serv->set($this->config);

        //定义触发事件的函数
        $serv->on('Start', array($this, 'my_onStart'));
        $serv->on('Connect', array($this, 'my_onConnect'));
        $serv->on('Receive', array($this, 'my_onReceive'));
        $serv->on('Close', array($this, 'my_onClose'));
        $serv->on('Shutdown', array($this, 'my_onShutdown'));
        $serv->on('Timer', array($this, 'my_onTimer'));
        $serv->on('WorkerStart', array($this, 'my_onWorkerStart'));
        $serv->on('WorkerStop', array($this, 'my_onWorkerStop'));
        $serv->on('Task', array($this, 'my_onTask'));
        $serv->on('Finish', array($this, 'my_onFinish'));
        $serv->on('WorkerError', array($this, 'my_onWorkerError'));
        $serv->start();
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
    
    
    public function my_onStart($serv)
    {
       echo "MasterPid={$serv->master_pid}|Manager_pid={$serv->manager_pid}\n";
       echo "Server: start.Swoole version is [".SWOOLE_VERSION."]\n";
    }

    public function my_onShutdown($serv)
    {
        echo "Server: onShutdown\n";
    }

    public function my_onTimer($serv, $interval)
    {
        echo "Server:Timer Call.Interval=$interval\n";
    }

    public function my_onClose($serv, $fd, $from_id)
    {
        echo "Client: fd=$fd is closed.\n";
    }

    public function my_onConnect($serv, $fd, $from_id)
    {
         echo "Client: fd=$fd is Connect, from id = $from_id.\n";
    }

    public function my_onWorkerStop($serv, $worker_id)
    {
        echo "WorkerStop[$worker_id]|pid=".posix_getpid().".\n";
    }

    public function my_onWorkerStart($serv, $worker_id)
    {
        global $argv;
        $pid = getmypid();
        if($worker_id >= $serv->setting['worker_num']) {
            echo "php {$argv[0]} task worker {$pid}\n";
//            cli_set_process_title("php {$argv[0]} task worker");
        } else {
            echo "php {$argv[0]} event worker {$pid}\n";
//            cli_set_process_title("php {$argv[0]} event worker");
        }
        echo "WorkerStart|MasterPid={$serv->master_pid}|Manager_pid={$serv->manager_pid}|WorkerId=$worker_id | CurPid:{$pid} \n";
        //$serv->addtimer(500); //500ms
    }

  


    //服务端接受数据的函数
    public function my_onReceive(\swoole_server $serv, $fd, $from_id, $rdata)
    {

        $data = json_decode($rdata, true);
        if (isset($data['class'])) {
            $type = $data['class'];
            if (!isset(self::$cnt[$type])) {
                // 没有专属处理进程，则使用公共进程
                $type = 'Common';
            }

            $tid = $this->getTaskId($type);

            $data['fd'] = $fd;
            $rs = $serv->task($data, $tid); //调用task事件
            echo "receive to task ,task_id =$tid \n";
            return ;
        } else {
            echo "没有相应 事件处理类, 报警\n";
            $serv->close($fd); //服务端主动断开连接
        }
        return;

    }

    //将接受数据交付给task任务,完成调度参数需要处理的业务
    public function my_onTask(\swoole_server $serv, $task_id, $from_id, $data)
    {
         
        $rs = $this->doJob($data);
        $rs = array('rs'=> $rs, 'fd' => $data['fd']);
        return $rs;
    }

    //提供重写的方法，返回消息体给客户端处理
     //任务实现方法
    protected  function doJob($data)
    {  
                
        return 'task is ok';
    }
    
    /**
     * Finish事件处理task的处理结果
     * 这里实现任务失败的机制
     * @param \swoole_server $serv
     * @param type $task_id
     * @param type $data
     */
    public function my_onFinish(\swoole_server $serv, $task_id, $data)
    {
        $is_send = 1;
        $rs = $data['rs'];
//        if (is_array($data['rs'])) {
//            // 失败了
//            if ($rs['err_no'] > 0) {
//                $tid = $this->getTaskId('Retry');
//                echo "faild tid: {$tid} \n";
//
//                $task_data['class'] = $rs['class'];
//                $task_data['param'] = $rs['param'];
//                $task_data['fd'] = $data['fd'];
//                $task_data['retry_cnt'] = $rs['retry_cnt'];
//
//                if ($rs['retry_cnt'] < 3) {
//                    $serv->task($task_data, $tid);
//                } else {
//                    $is_send = 1;
//                    echo "超过3次，需要报警! \n";
//                }
//            } else {
//                // 第一次就成功了。
//                $is_send = 1;
//            }
//
//            $rs = json_encode($data['rs']);
//        } else {
//            $is_send = 1;
//        }
//
        if ($is_send > 0) {
            
            $serv->send($data['fd'], $rs);
//            $serv->close($data['fd']);//服务端主动断开connection
    
        }else{
            echo 'errror';
        }

   }

    public function my_onWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code)
    {
       
         echo "worker abnormal exit. WorkerId=$worker_id|Pid=$worker_pid|ExitCode=$exit_code\n";
    }

}
