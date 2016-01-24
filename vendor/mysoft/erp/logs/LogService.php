<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

use yii\log\Logger;

/**
 * Description of LogService
 *
 * @author tianl
 */
class LogService implements ILogWriteService, ITaskWriteService {

    const TASK_TYPE_ERPAPI = 'erpapi';
    const DATE_FORMART = 'Y-m-d H:i:s';
    const LOG_ERROR = 'errorlog';
    const LOG_TASK = 'tasklog';
    const LOG_DATA = 'datalog';

    private $_orgcode;
    private $_log;

    public function __construct($orgcode) {
        $this->_orgcode = $orgcode;
        //数据日志为异步写，同时写DB和ES日志
        //$this->_log = ErpApiLogFactory::createDb2EsLog($orgcode);
        $this->_log = ErpApiLogFactory::createDbLog($orgcode);
    }

    /**
     * 记录错误日志
     * @param type $postData
     * @param type $errorInfo ['apiErrorInfo'=>[],'httpErrorInfo'=>'']
     * @param type $taskId
     */
    public function logError($postData, $errorInfo, $context = []) {
        try {
            $logInfo = [
                'taskId' => isset($context['taskId']) ? $context['taskId'] : '',
                'taskName' => isset($context['taskName']) ? $context['taskName'] : '',
                'postData' => $postData,
                'bgnTime' => date(self::DATE_FORMART),
                'orgcode' => $this->_orgcode,
            ];
            if (isset($errorInfo['apiErrorInfo'])) {
                $logInfo['apiErrorInfo'] = $errorInfo['apiErrorInfo'];
            }
            if (isset($errorInfo['httpErrorInfo'])) {
                $logInfo['httpErrorInfo'] = $errorInfo['httpErrorInfo'];
            }
            $this->_log->logging($logInfo, self::LOG_ERROR);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
    }

    public function logPostData($postData, $requestUrl = "", $context = []) {
        try {
            $logInfo = [
                'orgcode' => $this->_orgcode,
                'postDataStr' => \mysoft\helpers\String::jsonEncode((array) $postData),
                'requestUrl' => $requestUrl,
                'taskId' => isset($context['taskId']) ? $context['taskId'] : '',
                'bgnTime' => date(self::DATE_FORMART),
            ];
            if (isset($postData->data['ServiceName'])) {
                $logInfo['taskName'] = $postData->data['ServiceName'];
            }
            $logId = $this->_log->logging($logInfo, self::LOG_DATA);
            return $logId;
        } catch (\Exception $exc) {
            $this->logExc($exc);
            return '';
        }
    }

    public function logReceiveData($logId, $receiveData, $context = []) {
        try {
            $logInfo = [
                'receiveDataStr' => \mysoft\helpers\String::jsonEncode($receiveData),
                'taskId' => isset($context['taskId']) ? $context['taskId'] : '',
                'endTime' => date(self::DATE_FORMART),
                'requestTime' => isset($context['requestTime']) ? $context['requestTime'] : -1,
            ];
            $this->_log->updateLog($logId, $logInfo, self::LOG_DATA);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
    }

    public function createTask($taskType) {
        try {
            $logInfo = [
                'orgcode' => $this->_orgcode,
                'taskType' => $taskType,
                'bgnTime' => date(self::DATE_FORMART),
            ];
            return $this->_log->logging($logInfo, self::LOG_TASK);
        } catch (\Exception $exc) {
            $this->logExc($exc);
            return '';
        }
    }

    public function closeTask($taskId, $taskInfo, $isSuccess = 1) {
        try {
            $logInfo = [
                'logInfo' => $taskInfo,
                'success' => $isSuccess,
                'endTime' => date(self::DATE_FORMART),
            ];
            $this->_log->updateLog($taskId, $logInfo, self::LOG_TASK);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
    }

    private function logExc(\Exception $exc) {
        $msg = $exc->getMessage() . PHP_EOL . $exc->getTraceAsString();
        \Yii::error($msg, 'Api-log');
        LogMonitor::log($msg);
    }

    public function logRequest($postData, $receiveData, $requestUrl = "", $context = array()) {
        try {
            $logInfo = [
                'orgcode' => $this->_orgcode,
                'postDataStr' => \mysoft\helpers\String::jsonEncode((array) $postData),
                'requestUrl' => $requestUrl,
                'receiveDataStr' => \mysoft\helpers\String::jsonEncode($receiveData),
                'taskId' => isset($context['taskId']) ? $context['taskId'] : '',
                'requestTime' => isset($context['requestTime']) ? $context['requestTime'] : -1,
            ];
            if (!empty($context['logId'])) {
                //预先指定组件ID
                $logInfo['_id'] = $context['logId'];
            }
            if (isset($context['bgnTime'])) {
                $logInfo['bgnTime'] = $context['bgnTime'];
            }
            $logInfo['endTime'] = date(self::DATE_FORMART);
            if (isset($postData->data['ServiceName'])) {
                $logInfo['taskName'] = $postData->data['ServiceName'];
            }
            if (!empty($context['taskName'])) {
                $logInfo['taskName'] = $context['taskName'];
            }
            $logId = $this->_log->logging($logInfo, self::LOG_DATA);
            return $logId;
        } catch (\Exception $exc) {
            $this->logExc($exc);
            return '';
        }
    }

    public function logTask($taskId, $taskType, $taskInfo, $isSuccess = 1, $context = array()) {
        try {
            $logInfo = [
                '_id' => $taskId,
                'orgcode' => $this->_orgcode,
                'taskType' => $taskType,
                'bgnTime' => $context['bgnTime'],
                'logInfo' => $taskInfo,
                'success' => $isSuccess,
                'endTime' => $context['endTime'],
            ];
            return $this->_log->logging($logInfo, self::LOG_TASK);
        } catch (\Exception $exc) {
            $this->logExc($exc);
            return '';
        }
    }

}
