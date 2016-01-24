<?php

/*
 * 实现机制:
 * 
 * 利用swoole定时器启用，基于Console的脚本模式
 * 
 */

namespace mysoft\task;

/**
 * TaskTimerController
 *
 * @author yangzhen
 */
class TaskTimerController extends TaskBaseController
{
    use Com; //继承Com Trait
    /**
     * 注册额外的定时器
     * ['func'=>'','interval'=>''] => 为一个独立的结构单元
     *  func =>指定本类的方法
     *  interval => 间隔时间，考虑一致性，单位为秒，定时器的基本单位为毫秒，这里会自动转换
     * @var array
     */
    protected $reg_timer = [];

    /**
     * 定时任务计时器入口文件
     * @param int    $interval  定时器时间间隔，单位秒
     * @param string $hostinfo  监听主机
     */
    public function actionRun($interval=3,$hostinfo='0.0.0.0:9550')
    {
        $interval = $interval*1000; //单位是秒转换为基本单位毫秒
        
        $this->parseHostPort($hostinfo);
        
        echo 'timer set ',$interval,' ms ' ,PHP_EOL;
        
        //主定时器,按interval间隔执行，单位毫秒
        swoole_timer_tick($interval,function(){
             $this->execTasks();
        });
        
        //如果存在额外注册定时器
        if($this->reg_timer){
            foreach($this->reg_timer as $reg){
               $tick  = $reg['interval']*1000;
               if(method_exists($this, $reg['func']))
                 swoole_timer_tick($interval,[$this,$reg['func']]); 
            }
            
        }
        
    }
    
    
    /**
     * 获取task类别class
     * @param string $key
     * @param string $prefix
     * @return string
     */
    protected function getTaskWorkerClass($key,$prefix='')
    {
        return $prefix.$key;
    }
    
    
    /**
     * 任务数据，供子类重写
     * 
     */
    protected function getTaskData()
    {
        return  [
             ['service'=>'pub/user/info','params'=>['a'=>1,'b'=>'test']],
             ['service'=>'pub/wx/init','params'=>['type'=>'qy']],
        ];
    }


    /**
     * 主任务
     * @return  void 
     */
    protected function execTasks()
    {
        //任务数据
        $tasks = $this->getTaskData(); 
       
        foreach($tasks as $task){
            
             if(!isset($task['class'])) $task['class'] = 'Common'; //如果没设置class则默认为公共类别
             
             $this->Send($task);//启动子任务实现异步并发
        }
        
    }
    
    /**
     * 以异步模式发布子任务消息
     * @param array $task
     */
    protected function Send($task)
    {
    
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        
        //客户端连接成功的事件,$task为子任务参数
        $client->on('connect',function($cli)use($task){
              $task = json_encode($task);
              $cli->send($task);
        });
        
        //客户端接受响应事件
        $client->on('receive',function($cli,$data=""){
            
            echo 'received:',$data,PHP_EOL;
            $cli->close();
        });
        
        //客户端关闭事件
        $client->on('close',function($cli){
             echo 'Connection close',PHP_EOL;
        });
        
        //客户端错误事件
        $client->on('error',function($cli){
             //TODO: error
        });
        
        $client->connect($this->host, $this->port,0.5);
        
    }
}
