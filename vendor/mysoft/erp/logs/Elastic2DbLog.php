<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of Elastic2DbLog
 *
 * @author tianl
 */
class Elastic2DbLog implements ILogBase {

    private $esLog;
    private $dbLog;

    public function __construct($app, $orgcode) {
        $this->esLog = new ElasticLog($app, $orgcode);
        $this->dbLog = new DbLog($app, $orgcode);
    }

    public function logging($data, $logtype) {
        $logId = null;
        try {
            $logId = $this->esLog->logging($data, $logtype);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
        try {
            if (empty($logId)) {
                $logId = uniqid('elg',TRUE);
            }
            $data['_id'] = $logId;
            $this->dbLog->logging($data, $logtype);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
        return $logId;
    }

    public function updateLog($id, $data, $logtype) {

        try {
            $this->esLog->updateLog($id, $data, $logtype);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
        try {
            $this->dbLog->updateLog($id, $data, $logtype);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
    }

    private function logExc(\Exception $exc) {
        $msg = $exc->getMessage() . PHP_EOL . $exc->getTraceAsString();
        \Yii::error($msg, 'Api-log');
        LogMonitor::log($msg);
    }

    public function clearLog($logtype, $daynum) {
        try {
            $this->esLog->clearLog($logtype, $daynum);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
        try {
            $this->dbLog->clearLog($logtype, $daynum);
        } catch (\Exception $exc) {
            $this->logExc($exc);
        }
    }

    public function batchLogging($data, $logtype) {
        
    }

}
