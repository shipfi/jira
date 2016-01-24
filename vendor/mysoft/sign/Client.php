<?php

/*
 * 客户端类
 * 
 * 供各调用客户端使用,目前仅支持 POST & GET
 */

namespace mysoft\sign;

use mysoft\http\Curl;

/**
 * Client 端
 *
 * @author yangzhen
 */
class Client extends Base {

    /**
     * 应用标识
     * @var string
     */
    protected $appid = '';

    /**
     * 应用密钥
     * @var type 
     */
    protected $appsecret = '';

    /**
     * 调试模式
     * @var boolean
     */
    protected $debug = false;
    
    /**
     * 设置CURL从参数
     * @var type 
     */
    private $curl_options = [];

    /**
     *接口站点
     * @var type 
     */
    public $host;

    public function __construct($appid = '', $appsecret = '') {
        $this->appid     = $appid;
        $this->appsecret = $appsecret;
        
        $this->_check_apps();
        $this->host=\mysoft\pubservice\Conf::getConfig('api_site');
    }

    /**
     * 检查apps账号，自定义规则进行处理
     */
    private function _check_apps()
    {
        if(empty($this->appid) && empty($this->appsecret)){//不传值则取默认的
            if(!isset(\Yii::$app->params['app_code'])) {
                throw new \Exception('请各子应用在params.php中正确的配置app_code');
            }
            $this->appid = \Yii::$app->params['app_code'] == '0000'?$this->default_appid:\Yii::$app->params['app_code'];
            $this->appsecret = $this->getAppSecretById($this->appid);
            
        }elseif($this->appid && empty($this->appsecret)){//根据appid自动取密钥
            
            $this->appsecret = $this->getAppSecretById($this->appid);
        }
    }


     //设置curl的配置
     public function setCurlOption($options)
     {
          if(!is_array($options)) return false;
         
          $this->curl_options = $options;
          
          return $this;
     }
    
     
     //设置超时时间
     public function setRequestTimeOut($timeout=30)
     {
        if(!is_numeric($timeout)) return $this;
         
        $options = [
           CURLOPT_CONNECTTIMEOUT=>$timeout,
           CURLOPT_TIMEOUT=>$timeout
        ];
            
        return $this->setCurlOption($options);                      
     }
     

    /**
     * 调试模式设置，默认是关闭
     * @param type $debug
     * @return \mysoft\sign\Client
     */
    public function debug($debug) {
        $this->debug = (bool) $debug;
        return $this;
    }

    /**
     * GET 请求
     * @param string $url
     * @param array  $params
     * @param string  $app_code 获取app的站点地址
     * @return string
     */
    public function get($url, $params = [],$app_code='api_site') {
        $url = $this->_url($url,$app_code); 
        return $this->_callApi('get', $url, $params,$app_code);
    }

    /**
     * POST 请求
     * @param string $url
     * @param array  $data    POST的数据
     * @param array  $app_code  app应用的唯一标识
     * @return string
     */
    public function post($url, $data = [], $app_code = 'api_site') {
        $url = $this->_url($url,$app_code);
        return $this->_callApi('post', $url, [], $data);
    }

    /**
     * 请求Api
     * @param strign       $http_method , post or get
     * @param string       $url , '/orgcode/demo/site/index'   
     * @param array        $params ,['id'=>1,'type'=>'test' ,...] ,也可以不传，如果参数在url参数上，这里必须传空
     * @param array|string $data post提交数据的时候必须传
     * @return string
     * @throws \Exception
     */
    private function _callApi($http_method, $url, $params = [], $data = []) {
//        $cache_result = $this->_getFromCache($url,$params);
//        if($cache_result){
//            return $cache_result;
//        }
        $curl = new Curl();
        
        if($this->curl_options){ //设置超时请求
            foreach ($this->curl_options as $key => $val) {
               $curl->setOption($key,$val);//设置curl options
            }
        }
        
        
        //将签名参数加入到GET请求里，最终生成一个参数值，从而简化了请求
        $params[$this->sign_key] = $this->_getSign($this->appid, $this->appsecret, time());        
        switch (strtoupper($http_method)) {
            case 'GET':
                $result = $curl->get($url, $params, $this->debug);                
                break;
            case 'POST':
                $result = $curl->post($url, $data, $params, $this->debug);
                break;
            default:
                throw new \Exception('不支持的HTTP请求方法');
        }
        if ($curl->getError()) {
            return \mysoft\helpers\String::jsonEncode(['success' => 0, 'data' => $curl->getError()]);
        }
        return $result;
    }

    /**
     * 根据路由获取缓存
     * @param $url
     * @return mixed
     */
    private function _getFromCache($url,$param){
        $cachekey = $url . (empty($param) ? "" : "_" . implode('_',array_values($param)));
        $res =  \yii::$app->cache->get($cachekey);
        if($res){
            $data =
                [
                    "success"=>1,
                    "data"=>$res
                ];
            return json_encode($data);
        }
        return null;
    }

    /**
     * 获取签名值
     * @param string $appid
     * @param string $appsecret
     * @param int $timestamp
     * @return string
     */
    protected function _getSign($appid, $appsecret, $timestamp) {
        $core = $this->getSignCore($appid, $appsecret, $timestamp);
        $raw = sprintf('%s.%s.%s', $appid, $core, $timestamp);
        return $this->encode($raw);
    }
    
    /**
     * 验证url地址，针对没有Host的自动填充api_site
     * @param  string $url
     * @param string $app_code 应用的唯一标识
     * @return string
     */
    protected function _url($url,$app_code)
    {
        $p = parse_url($url);
        
        if(!isset($p['host'])){
            //根据app_code获取app_api_site
           if($app_code!=='api_site'){
            	$data=  $this->getApps($app_code);
            	$host=empty($data['app_api_site'])?$this->host:$data['app_api_site'];
            } 
           $host = empty($host)?$this->host:$host;         
           
           if(strpos($host,'http://') === false){ //加http头
              $host = 'http://'.$host;
           }

            $url = rtrim($host,'/').'/'.ltrim($url,'/');
           
           
        }
        
        return $url;
    }

}
