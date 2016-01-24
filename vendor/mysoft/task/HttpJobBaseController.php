<?php

/*
 * Http处理业务逻辑子类继承，转换参数捕获异常
 * 
 * 
 */

namespace mysoft\task;

/**
 * Description of HttpJobBaseController
 *
 * @author yangzhen
 */
class HttpJobBaseController extends \yii\console\Controller
{
    
    /**
     * 日志服务
     * @var array 
     */
    protected $loginfo = ['info'=>[]];
    
    /**
     * Job参数，其中包含params节点
     * @var array
     */
    protected $params  = [];

    /**
     * 异常的错误信息
     * @var string 
     */
    private   $exception  = '';
    
    
    public function init(){
        
         parent::init();
         
         $this->parseParams();//初始化解析参数
         
         //重写exception handler
         set_exception_handler(function($e){
             //捕获错误信息,stack信息
             $this->exception = \yii\base\ErrorHandler::convertExceptionToString($e); 
         });
         
         
         //重写register_shutdown
         register_shutdown_function(function(){//非致命错误脚本结束写日志
            
             //完成处理log对象
             
             if($this->exception){//异常处理模式跟`return 1`一样
                echo $this->exception,PHP_EOL; 
                exit(1);
             }
            
         });
    }
    
    
    /**
     * 记录日志信息
     * @param array $msg
     */
    protected function logging($msg)
    {
        $this->loginfo['info']= $msg;
    }


    //获取业务命令行参数，解析Job参数和调用方法的函数参数
     protected function parseParams()
     {
         global $argv;
         
         if(isset($argv[2])){//存在参数则解析
            $this->params = json_decode($argv[2],true);
         }
         
          
     }
    
    
    
    
             
}
