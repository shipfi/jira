<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of AsyncWriteLog
 *
 * @author tianl
 */
class AsyncLogService implements ILogWriteService, ITaskWriteService {

    private $_orgcode;
    private $_log;

    public function __construct($orgcode) {
        $this->_orgcode = $orgcode;
        $this->_log = ErpApiLogFactory::createLog($orgcode);
    }

    public function closeTask($taskId, $taskInfo, $isSuccess = 1) {
        //同步写记录
        $this->_log->closeTask($taskId, $taskInfo, $isSuccess);
    }

    public function createTask($taskType) {
        //同步写记录
        return $this->_log->createTask($taskType);
    }

    public function logPostData($postData, $requestUrl = "", $context = array()) {
        
    }

    public function logReceiveData($logId, $receiveData, $context = array()) {
        
    }

    public function logRequest($postData, $receiveData, $requestUrl = "", $context = array()) {
        if (YII_ENV == 'dev') {
            //开发环境暂时记录同步日志
            $this->_log->logRequest($postData, $receiveData, $requestUrl, $context);
            return;
        }
        $async = new \mysoft\helpers\AsyncService();
        $res = $async->Send('apps', 'erp-async-job/log-request', [
            'postData' => $postData,
            'receiveData' => $receiveData,
            'requestUrl' => $requestUrl,
            'context' => $context,
            'orgcode' => $this->_orgcode,
        ]);
    }

    public function logError($postData, $errorInfo, $context = array()) {
        if (YII_ENV == 'dev') {
            //开发环境暂时记录同步日志
            $this->_log->logError($postData, $errorInfo, $context);
            return;
        }
        $async = new \mysoft\helpers\AsyncService();
        $res = $async->Send('apps', 'erp-async-job/log-error', [
            'postData' => $postData,
            'errorInfo' => $errorInfo,
            'context' => $context,
            'orgcode' => $this->_orgcode,
        ]);
    }

    public function logTask($taskId, $taskType, $taskInfo, $isSuccess = 1, $context = array()) {
        if (YII_ENV == 'dev') {
            //开发环境暂时记录同步日志
            $this->_log->logTask($taskId, $taskType, $taskInfo, $isSuccess, $context);
            return;
        }
		//如果改为同步写日志，则LogService中的task只能写DB保证稳定性
        $async = new \mysoft\helpers\AsyncService();
        $res = $async->Send('apps', 'erp-async-job/log-task', [
            'taskId' => $taskId,
            'taskType' => $taskType,
            'taskInfo' => $taskInfo,
            'isSuccess' => $isSuccess,
            'context' => $context,
            'orgcode' => $this->_orgcode,
        ]);
    }

}
