<?php

/*
 * 异步服务的助手类
 * 使用方法:
 * $asyn = new \mysoft\hepler\AsyncService();
 * $res = $asyn->Send('apps','asyntask/send',['id'=>1]);
 * 
 * if($res === true){
 *    //投递异步消息成功
 * 
 * }
 * 
 * 
 */

namespace mysoft\helpers;
//use mysoft\http\Curl;
use mysoft\pubservice\Conf;
/**
 * Class :AsyncServiceHelper
 *
 * @author yangzhen
 */
class AsyncService 
{
    private $_http;
    
    public function __construct() 
    {
//       $this->_http = new Curl();
       
    }

    //获取返回主机信息
    private function _getHost()
    {
        $hostinfo = Conf::getConfig('http_server');
        
        if(empty($hostinfo)){
            throw new \Exception("请核查http-server配置！");
        }
        
        return $hostinfo;

    }


    
    /**
     * 投递消息
     * @param string $app   应用标识
     * @param string $task ,任务 demo/run 
     * @param type $params ,传递参数
     * @return boolean
     */
    
    
    public function Send($app,$task,$params=[])
    {
        $url  = "http://".$this->_getHost()."/?opt=put";
        
        $data = [
           'app'   => $app,
           'task'  => $task,
           'params'=>$params
        ];
        
        $data = json_encode($data);
        
        $post_arr = ['data'=>$data];
        
        $parts   = parse_url($url);
        $errno   =  0;
        $errstr  = null;
        $timeout = 1;
        $method  = 'POST';
        
        $port  = isset($parts['port']) ? $parts['port'] :80;
         
         $fp  =  @fsockopen(
                    $parts['host'],
                    $port,
                    $errno,
                    $errstr,
                    $timeout

                  );
            
            if($fp){
                $post_str = http_build_query($post_arr);
                
                $path = $parts['path'].'?'.$parts['query'];
                
                $header  = "{$method} {$path} HTTP/1.1\r\n";
                $header .= "HOST:".$parts['host'].":".$port."\r\n";
                $header .= "Accept:*/*\r\n";
                $header .= "Connection:Close\r\n";
//                $header .= "Connection:keep-alive\r\n";
                $header .="Content-Length:".strlen($post_str)."\r\n";
                $header .= "Content-Type:application/x-www-form-urlencoded\r\n\r\n";      
                $header .= $post_str."\r\n\r\n";
                
//                echo $header;die;
                fwrite($fp, $header);
                usleep(10000);
                fclose($fp);
                
                return true;
                
            }else{
                throw new \Exception(iconv('GBK', 'UTF-8', $errstr),$errno);
            }
       
        
        
    }
    
    
//     public function Send($app,$task,$params=[])
//    {
//        $url  = "http://".$this->_getHost()."/?opt=put";
//        
//        $data = [
//           'app'   => $app,
//           'task'  => $task,
//           'params'=>$params
//        ];
//        
//        $data = json_encode($data);
//        $res = $this->_http->post($url,["data"=>$data]);
//       
//        if($res == 'HTTP_PUT_OK'){
//            return true;
//        }
//        
//        return false;
//    }
    
    
    
    
}
