<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

use mysoft\http\Curl;
use mysoft\erp\logs\ErpApiLogFactory;
use mysoft\pubservice\GatedLaunchService;

/**
 * Erp接口客户端类，负责签名请求，异常处理
 * @author tianl
 */
class ErpApiClient extends \mysoft\sign\Base {

    private $_apiSite;
    private $_appid;
    private $_appsecret;
    private $orgcode;
    public $timeout;

    /**
     * 默认系统账号
     * @var string
     */
    protected $default_appid = 'mysoft-ydkf';

    /**
     * 默认系统账号的密钥
     * @var string
     */
    protected $default_appsecret = 'mysoftydkf2015';

    /**
     * 是否抛ERPApi请求异常
     * @var type 
     */
    public $throwErpApiException = true;

    public function __construct($orgcode, $erpApiSetting = '', $isLoop = false) {
        $this->orgcode = $orgcode;
        if (empty($erpApiSetting)) {
            $erpApiSetting = ErpApiConf::getErpSetting($this->orgcode);
        }

        if (is_string($erpApiSetting)) {
            $this->_apiSite = $erpApiSetting;
            $appIdSecret = ErpApiConf::getErpSetting($this->orgcode);
            $this->_appid = !empty($appIdSecret['app_id']) ? $appIdSecret['app_id'] : $this->default_appid;
            $this->_appsecret = !empty($appIdSecret['app_secret']) ? $appIdSecret['app_secret'] : $this->default_appsecret;
        } else if (is_array($erpApiSetting)) {
            //取配置信息
            $this->_apiSite = $erpApiSetting['erpapi_url'];
            $this->_appid = !empty($erpApiSetting['app_id']) ? $erpApiSetting['app_id'] : $this->default_appid;
            $this->_appsecret = !empty($erpApiSetting["app_secret"]) ? $erpApiSetting["app_secret"] : $this->default_appsecret;
        }
        //$this->_apiSite = 'https://10.5.103.20:4433';
        //$this->_apiSite = 'http://10.5.103.20:7300/fdccloud/';
        $this->isLoop = $isLoop;
    }

    /**
     * 发送get请求
     * @param type $apiPath
     * @param array $params
     * @param type $context 请求上下文，比如带状态的请求的taskId，格式['taskId'=>'','logId'=>'']
     * @return type
     */
    public function get($apiPath, $params = array(), $context = []) {
        $signature = $this->_getSign($this->_appid, $this->_appsecret, time());
        $params[$this->sign_key] = $signature;

        $params['appid'] = $this->_appid;
        $curl = new Curl();
        if (!isset($this->timeout)) {
            $this->timeout = 120;
        }
        $curl->setOption(CURLOPT_TIMEOUT, $this->timeout);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, FALSE);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, FALSE);
        $url = $this->_buildUrl($apiPath);

        $startTime = microtime(true);
        $context['bgnTime'] = date('Y-m-d H:i:s');

        $receiveData = $curl->get($url, $params);

        $context['endTime'] = date('Y-m-d H:i:s');
        $context['requestTime'] = (microtime(true) - $startTime) * 1000;
        $postData = [
            'url' => $url,
            'data' => null,
            'params' => $params,
        ];
        return $this->_handleReceiveData($curl, $postData, $receiveData, $context);
    }

    /**
     * 发送post请求
     * @param type $apiPath
     * @param type $data
     * @param array $params
     * @param type $context 请求上下文，比如带状态的请求的taskId，格式['taskId'=>'','logId'=>'']
     * @return type
     */
    public function post($apiPath, $data, $params = array(), $context = []) {
        $signature = $this->_getSign($this->_appid, $this->_appsecret, time());
        $params[$this->sign_key] = $signature;

        $params['appid'] = $this->_appid;
        $curl = new Curl();
        if (!isset($this->timeout)) {
            $this->timeout = 120;
        }
        $curl->setOption(CURLOPT_TIMEOUT, $this->timeout);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, FALSE);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, FALSE);
        $url = $this->_buildUrl($apiPath);

        $postData = [
            'url' => $url,
            'data' => $data,
            'params' => $params,
        ];
        $startTime = microtime(true);
        $context['bgnTime'] = date('Y-m-d H:i:s');

        $receiveData = $curl->post($url, \mysoft\helpers\String::jsonEncode($data), $params);

        $context['endTime'] = date('Y-m-d H:i:s');
        $context['requestTime'] = (microtime(true) - $startTime) * 1000;
        return $this->_handleReceiveData($curl, $postData, $receiveData, $context);
    }

    protected function _buildUrl($apiPath, $site = '') {
        if (empty($site)) {
            $site = $this->_apiSite;
        }
        return rtrim($site, '/') . '/' . ltrim($apiPath, '/');
    }

    private $_log;

    public function getLog() {
        if (!isset($this->_log)) {
            $this->_log = ErpApiLogFactory::createAsyncLog($this->orgcode);
        }
        return $this->_log;
    }

    public function setLog($log) {
        $this->_log = $log;
    }

    /**
     * 是否来自轮询调用，实时调用时，不记录数据日志，只记录错误日志
     * @var type 
     */
    public $isLoop = false;

    /**
     * 处理请求数据，基类处理日志记录
     * @param type $url
     * @param type $data
     * @param type $params
     * @param type $context [taskId,logId,]
     */
    public function _handlePostData($url, $data = [], $params = [], $context = []) {
        return;
        $finalUrl = \mysoft\http\Curl::appendUrlParam($url, $params);
        //$taskId = isset($context['taskId']) ? $context['taskId'] : '';
        if ($this->isLoop == FALSE) {
            return;
        }
        return $this->getLog()->logPostData($data, $finalUrl, $context);
    }

    /**
     * 处理接收数据，基类处理日志记录
     * @param type $curl
     * @param type $receiveData
     * @param type $context [taskId,logId,]
     * @return type
     * @throws \Exception
     */
    public function _handleReceiveData($curl, $postData, $receiveData, $context = []) {
//        return json_decode($receiveData, true);
        $logId = isset($context['logId']) ? $context['logId'] : '';
        if (empty($logId)) {
            //预先指定logID
            $logId = uniqid('elg', TRUE);
            $context['logId'] = $logId;
        }
        $errorInfo = [];
        $receiveDataAr = [];
        $errMessage = ''; //错误提示信息
        if ($curl->getError()) {
            $errorInfo['httpErrorInfo'] = $curl->getError() . ' ; requestUrl:' . $postData['url'];
            $this->_staticsApiCall($this->orgcode, $postData, FALSE, $context);
        } else {
            $receiveDataAr = json_decode($receiveData, true);
            if (!empty($receiveDataAr["error"])) {
                $errorInfo['apiErrorInfo'] = $receiveDataAr["error"];
                $errMessage = $errorInfo['apiErrorInfo'][count($errorInfo['apiErrorInfo']) - 1]['message'];
            }
            if (!empty($receiveDataAr['errmsg'])) {
                $errorInfo['apiErrorInfo'] = $receiveDataAr["errmsg"];
                $errMessage = $errorInfo['apiErrorInfo'];
            }
            $this->_staticsApiCall($this->orgcode, $postData, empty($errMessage), $context);
        }
        if (!empty($errorInfo)) {
            //存在异常，则记录异常
            $this->getLog()->logError($postData, $errorInfo, $context);
            if ($this->throwErpApiException == TRUE) {
                if (!empty($errorInfo['httpErrorInfo'])) {
                    //http请求异常
                    throw new logs\ErpApiException($errorInfo['httpErrorInfo']);
                } else if (!empty($errMessage)) {
                    //业务异常
                    $this->_handleApiError($errMessage);
                    throw new logs\ErpApiException($errMessage);
                }
            }
        } else {
            if ($this->isLoop) {
                $this->getLog()->logRequest($postData, $receiveDataAr, $postData['url'], $context);
            }
        }
        return $receiveDataAr;
    }

    private function _handleApiError($errMessage) {
        if (strpos($errMessage, '令牌已过期') !== FALSE) {
            //如果是令牌过期错误，则重置缓存
            $tokenKey = $this->_getCacheTokenKey();
            $this->delCacheToken($tokenKey);
        }
    }

    /**
     * 增加调用统计
     * @param type $orgcode
     * @param type $apiPath
     * @param type $success
     * @param type $costTime
     */
    private function _staticsApiCall($orgcode, $postData, $success, $context) {
        return;
        if (YII_ENV == 'dev') {
            return;
        }
        $msg = [
            'orgcode' => $orgcode,
            'apiPath' => $postData['url'],
            'success' => $success,
            'costTime' => $context['requestTime'],
            'bgnTime' => date('Y-m-d H:i:s'),
        ];
        if (isset($postData->data['ServiceName'])) {
            $msg['taskName'] = $postData->data['ServiceName'];
        }
        if (isset($context['taskId'])) {
            $msg['taskId'] = $context['taskId'];
        }
        if (isset($context['logId'])) {
            $msg['logId'] = $context['logId'];
        }

        $async = new \mysoft\helpers\AsyncService();
        $res = $async->Send('apps', 'erp-async-job/logging-erp-api-statics', [
            'message' => $msg
        ]);
    }

    /**
     * 定义签名的字段名
     *
     * @var string
     */
    protected $sign_key = 'signature';

    /**
     * 获取签名值
     *
     * @param string $appid            
     * @param string $appsecret            
     * @param int $timestamp            
     * @return string
     */
    private function _getSign($appid, $appsecret, $timestamp) {
        if ($this->getGatedLaunchSrv()->checkErpApiIsOldVersion(GatedLaunchService::UTYPE_ERPAPI_UPDATE, $this->orgcode)) {
            $this->sign_key = "access_token";
            //老认证token
            $core = $this->getSignCore($appid, $appsecret, $timestamp);
            $raw = sprintf('%s.%s.%s', $appid, $core, $timestamp);
            return $this->encode($raw);
        } else {
            //先从缓存中取Token
            $cacheKey = $this->_getCacheTokenKey();
            $accessToken = $this->getCacheToken($cacheKey);
            if (!empty($accessToken)) {
                return $accessToken;
            }
            //缓存中没有则通过接口获取
            $accessToken = $this->getAccessToken();
            if (!empty($accessToken['errmsg'])) {
                //如果有获取token异常，直接抛出异常
                throw new ErpApiTokenException($accessToken['errmsg']);
            }
            //生成签名
            $encodeToken = $this->get_signature($accessToken['access_token'], $appsecret);
            $this->setTokenCache($cacheKey, $encodeToken, $accessToken['expires_in'] - 60);
            return $encodeToken;
        }
    }

    private function _getCacheTokenKey() {
        return ['erpapi_accesstoken_{orgcode}_{appid}_{appsecret}', $this->orgcode, $this->_appid, $this->_appsecret];
    }

    public function get_signature($token, $appsecret) {
        $baseString = sprintf('%s#%s', $token, date('Y-m-d\TH:i:s'));
        $core = hash_hmac('sha1', $baseString, $appsecret, false);

        $raw = sprintf('%s.%s', $core, $baseString);
        return $this->encode($raw);
    }

    /**
     * Api管理平台站点
     *
     * @return mixed
     */
    private function getApiPlartformSite() {
        // $this->_apiPlatformSite='http://10.5.103.20:9111/fdccloud/'; 平台站点默认将接口站点名称换成平台站点名称
        return str_replace($this->getApiSiteName(), static::ApiPlartformName, $this->_apiSite);
    }

    /**
     * API站点名称
     */
    private function getApiSiteName() {
        // http://10.5.103.20:9111/fdccloud/ 截取 fdccloud
        $apiSite = trim($this->_apiSite, '/');
        return substr($apiSite, strrpos($apiSite, '/') + 1);
    }

    /**
     * 默认的平台站点名称
     *
     * @var string
     */
    const ApiPlartformName = 'platform';

    private function getAccessToken() {
        $curl = new Curl();
        $url = $this->_buildUrl('api/sys/GetAccessToken.ashx', $this->getApiPlartformSite());

        $param = [
            'timestamp' => date('Y-m-d\TH:i:s'),
            'appid' => $this->_appid,
        ];

        $token = $curl->get($url, $param);
        //发现http请求错误，抛出异常
        if ($curl->getError()) {
            throw new ErpApiTokenException($curl->getError());
        }
        $token = json_decode($token, true);
        if (!isset($token['errmsg']) && !isset($token['access_token'])) {
            throw new ErpApiTokenException('令牌服务器异常');
        }
        return $token;
    }

    /**
     * 设置token缓存 模板方法
     * @param type $tokenKey
     * @param type $token
     * @param type $expiresIn
     */
    protected function setTokenCache($tokenKey, $token, $expiresIn) {
        return \Yii::$app->cache->set($tokenKey, $token, $expiresIn);
    }

    /**
     * 获取token缓存 模板方法
     * @param type $tokenKey
     */
    protected function getCacheToken($tokenKey) {
        return \Yii::$app->cache->get($tokenKey);
    }

    protected function delCacheToken($tokenKey) {
        return \Yii::$app->cache->delete($tokenKey);
    }

    private $_gatedLaunchSrv;

    /**
     * 获取灰度发布表服务
     * @return GatedLaunchService
     */
    public function getGatedLaunchSrv() {
        if (isset($this->_gatedLaunchSrv)) {
            return $this->_gatedLaunchSrv;
        }
        $this->_gatedLaunchSrv = new GatedLaunchService();
        return $this->_gatedLaunchSrv;
    }

}
