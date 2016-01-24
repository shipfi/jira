<?php

/*
 * 任务服务基于Swoole扩展
 * extension load source:http://pecl.php.net/package/swoole
 * 关于Swoole介绍 : http://www.swoole.com/
 */

namespace mysoft\task;

use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * 任务服务基类 TaskBaseController
 *
 * @author yangzhen
 */
class TaskBaseController extends Controller
{
    /**
     * 监听主机
     * @var string 
     */
    protected  $host;

    /**
     * 监听端口
     * @var string
     */
    protected  $port;

    
    public function init() {
         
       /**
        * 检查扩展情况
        */ 
       if(!extension_loaded('swoole')) {
             throw new InvalidConfigException("use Swoole for Task Service, requires PHP  extension to be loaded.");
       }    
         
    }
    
    
    /**
     * 解析主机信息
     * @param string $hostinfo  
     * @throws \Exception
     */
    protected function parseHostPort($hostinfo)
    {
         $arr = explode(':',$hostinfo);
         
         if(count($arr) !=2){
             throw new \Exception('please input hostinfo like 127.0.0.1:9550');
         }
         
         $this->host  = $arr[0];
         $this->port  = $arr[1];
    }
    
    
    
}
