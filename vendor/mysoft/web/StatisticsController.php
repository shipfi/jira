<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\web;

/**
 * Description of StatisticsController
 *
 * @author tianl
 */
class StatisticsController extends FrontendController {

    public function init() {
        if (!empty($_REQUEST['u'])) {
            //将来源URL中的参数全部设置到参数对象中
            $urlInfo = $this->parseValidPram($_REQUEST['u']);
            //print_r($urlInfo);die;
            \Yii::$app->request->setQueryParams($urlInfo);
        }
        parent::init();
    }

    public function beforeAction($action) {
        $isValid = true;
        try {
            $isValid = parent::beforeAction($action);
        } catch (\Exception $exc) {

            $isValid = FALSE;
        }
        if (!$isValid) {
            //验证不通过的可能是匿名用户，可能是没有应用权限的
        }
        return true;
    }

    private $app = [ 'workflow' => '3022', 'plan' => '3042', 'report' => '3023', 'apps' => '0000'];
    protected $appDes;

    protected function parseValidPram($url) {
        //$_REQUEST['u']
        $queryParam = [];
        if (empty($url)) {
            return $queryParam;
        }
        $urlInfo = parse_url($url);
        if (!empty($urlInfo['query'])) {
            parse_str($urlInfo['query'], $queryParam);
        }
        if (!empty($urlInfo["path"])) {
            $pathArr = explode('/', trim($urlInfo["path"], '/'));
            if (!empty($pathArr[0]) && $pathArr[0] != 'api') {
                //解析来源应用
                foreach ($this->app as $ap => $appcode) {
                    if (strpos($pathArr[0], $ap) !== false) {
                        $queryParam['app'] = $appcode;
                        $this->appDes = $appcode;
                        break;
                    }
                }
            }
            //解析租户编码，如果统计JS发了orgcode则使用该值，否则根据路由规则取
            if (!empty($_REQUEST['o'])) {
                $queryParam['__orgcode'] = $_REQUEST['o'];
            } else if (empty($queryParam['__orgcode'])) {
                if (!empty($queryParam['app'])) {
                    //如果是轻应用的路由规则，则orgcode是第1个索引位
                    if (!empty($pathArr[1]))
                        $queryParam['__orgcode'] = $pathArr[1];
                    
                }else {
                    //还未配置的轻应用或者是中台，暂不处理
                }
            }
            //解析来源应用
            if (!empty($queryParam['__orgcode']) && !empty($pathArr[0]) && $queryParam['__orgcode'] == $pathArr[0]) {
                //此路由规则为中台的路由规则
                $queryParam['app'] = $this->app['apps'];
                $this->appDes = $queryParam['app'];
            }
        }
        return $queryParam;
    }

    public function check_access_appauth() {
        return true;
    }

}
