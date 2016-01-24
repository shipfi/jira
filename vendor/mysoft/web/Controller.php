<?php

/**
 * 扩展控制器，继承优化
 * @author yangz03
 * @since 2015-01-17
 */

namespace mysoft\web;

use ___PHPSTORM_HELPERS\object;
use yii;
use yii\web\Controller as CTR;
use yii\web\Response;

class Controller extends CTR {

    public $enableCsrfValidation = false;
    protected $orgcode;
    
   
    /**
     *  模板变量
     * @var  string
     */
    public $vars = [];

    /**
     * 布局,默认main
     * @var string
     */
    public $layout = 'main';

    /**
     * 初始化函数
     */
    public function init() {
        $this->orgcode = I("__orgcode");

        //根据前端cookie backurl跳转
        $this->backUrlLocation();
       
     }

   
    
    public function render($view, $params = []) {
        $ua_arr = ['ua' => (object) $this->getua()];
        $urlParams = ['urlParams' => (object) Yii::$app->request->getQueryParams()];
        $tpldata = array_merge($params, $urlParams, $ua_arr);
        $tpldata['APP_CODE'] = @\Yii::$app->params['app_code'];
        Yii::$app->view->params['tpldata'] = (object) $tpldata;
        $params['tplData'] = $tpldata;
        Yii::$app->response->formatters[Response::FORMAT_HTML] = 'mysoft\web\MyHtmlResponseFormatter';
        return parent::render($view, $params);
    }

    public function getua() {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = '';
        $browser_ver = '';
        if (preg_match('/iphone os([\^\s+]\w+_\w+)/i', $agent, $regs)) {
            $browser = 'iphone';
            $browser_ver = trim($regs[1]);
        }

        if (preg_match('/android([\^\s+]\w+\.\w+)/i', $agent, $regs)) {
            $browser = 'android';
            $browser_ver = trim($regs[1]);
        }
        
        $from = I('__from');
        if(empty($from)) {
            $from = cookie('__from@'.$this->orgcode);
        }
        
        return ['platform' => $browser, 'version' => $browser_ver, 'from'=>$from, 'client'=>I('__platform','')];
    }

    /**
     * ajax 统一返回格式
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     *
     */
    public function ajax_response($isSuccess = 1, $msg = "", $result = []) {
        $response = Yii::$app->response;
        $response->format = $response::FORMAT_JSON;
        $sub_ticket = '';
        if (isset($result['sub_ticket'])) {
            $sub_ticket = $result['sub_ticket'];
        }
        $result = ['isSuccess' => $isSuccess, 'message' => $msg, 'result' => $result, 'sub_ticket' => $sub_ticket];
        $response->data = $result;
        return $response;
    }

    //根据cookie中的backurl重定向
    public function backUrlLocation() {
        $back = cookie('backUrl');
        if ($back && !Yii::$app->request->getIsAjax()) {
            cookie('backUrl', null);
            header("location: " . $back);
            exit;
        }
    }

  
    
}
