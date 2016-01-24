<?php
namespace mysoft\http;
use yii\helpers\Url;

/**
 * 异步处理任务
 * 
 * 用法：
 * 
 * use mysoft\http\AsyncTask;
 * 
 * $task = new AsyncTaks();
 * $task->add($url,$params)->add(...)->execute();
 * 这样，会触发一个到$url的异步请求，你可以将一些不需要同步完成的，比较耗时的任务（拉取图片，大量数据转存）等以异步任务的方式进行。
 * 
 * 注意，在你自己的$url对应的任务处理时，你需要自己处理超时问题。比如在任务处理开始如下设置：
 * 
 * ignore_user_abort(TRUE); //如果客户端断开连接，不会引起脚本abort.
 * set_time_limit(0);//取消脚本执行延时上限
 * 
 * @author fangl
 *
 */
class AsyncTask {
    
    private $tasks = [];
    private $timeout;
    private $usleep;
    
    private $errno;
    private $errstr;
    
    /**
     * @param number $timeout fsocket超时时间
     * @param number $usleep fwrite到fclose直接的间隔时间; 解决nginx 49x错误
     */
    public function __construct($timeout=1,$usleep=10000) {
        $this->timeout = $timeout;
        $this->usleep = $usleep;
    }
    
    /**
     * 增加一个任务到任务池
     * @param string|array $url|$route，可以接受一个Yii的$route数组
     * @param array $params
     * @param string $method
     * @return \mysoft\http\AsyncTask
     */
    public function add($url,$params=[],$method='GET') {
        if(is_array($url)) {
            $url = Url::toRoute($url,true);
        }
        $this->tasks[] = ['url'=>$url,'params'=>$params,'method'=>$method];
        return $this;
    }
    
    /**
     * 执行任务池中所有任务
     * 判断任务十分执行成功（如有需要），调用hasErr()方法
     */
    public function execute() {
        $this->errno = 0;
        $this->errstr = null;
        foreach($this->tasks as $task) {
            $this->_execute($task['url'],$task['params'],$task['method']);
        }
    }
    
    /**
     * 单独执行某个任务
     * @param string $url
     * @param array $params
     * @param string $method
     * @return boolean
     */
    public function _execute($url,$params=[],$method='GET') {
        $parts = parse_url($url);
        $method = strtoupper($method);
        
        $errno = 0;
        $errstr = null;
        
        if($parts && isset($parts['host']) && isset($parts['path'])) {
            $fp = fsockopen(
                $parts['host'],
                isset($parts['port']) ? $parts['port'] : 80,
                $errno, $errstr, $this->timeout
            );
            
            if($fp) {
                if($method == 'GET') {
                    //get方法，将params和query放在一起
                    $path = $parts['path'] .
                            (!empty($parts['query'])?'?'. $parts['query']:'').
                            (!empty($params)?(empty($parts['query'])?'?':'&').http_build_query($params):'');
    
                }
                else {
                    $path = $parts['path'] .
                    (!empty($parts['query'])?'?'. $parts['query']:'');
                }
                
                $header = "{$method}  {$path} HTTP/1.1\r\n";
                $header .= 'Host: '. $parts['host'] . "\r\n";
    
                $header .= "User-Agent: async-task\r\n";
                $header .= "Connection: Close\r\n";
    
                if(!empty($params) && $method == 'POST') {
                    //post方法，将params数据放在post域
                    $_post = http_build_query($params);
                    $post_str  = "Content-Type: application/x-www-form-urlencoded\r\n";
                    $post_str .= 'Content-Length: '. strlen($_post) ."\r\n\r\n";
                    $post_str .= $_post; //header 跟body之间有空行
                    $header .= $post_str;
                }
                else $header .= "\r\n";
                fwrite($fp, $header);
                //这里写入后马上close针对nginx代理服务器会存在请求未转发到php-fpm上去的问题（49x错误）
                usleep($this->usleep);
                fclose($fp);
                return true;
            }
            else {
                $this->errno = $errno;
                $this->errstr = $errstr;
                return false;
            }
        }
        else {
            $this->errno = 1;
            $this->errstr = 'url 不合法: '.$url;
        }
    }
    
    public function hasErr() {
        return $this->errno != 0;
    }
    
    public function getLastErr() {
        return ['errno'=>$this->errno,'errstr'=>$this->errstr];
    }
}
