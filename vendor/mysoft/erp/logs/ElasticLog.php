<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of ElasticLog
 *
 * @author tianl
 */
class ElasticLog implements ILogBase {

    private $_log;

    public function __construct($app, $orgcode) {
        $this->_log = \Yii::createObject([
                    'class' => 'mysoft\log\Elastic',
                    'app' => $app,
//                    'hosts' => ['121.41.13.159:9203'],
        ]);
    }

    public function logging($data, $logtype = '') {
        $logtype = LogType::getLogTypeFormart($logtype);
        return $this->_log->setLogDebug(YII_DEBUG)->logging($data, $logtype);
    }

    public function updateLog($id, $data, $logtype = '') {
        $logtype = LogType::getLogTypeFormart($logtype);
        $this->_log->setLogDebug(YII_DEBUG)->updateLog($id, $data, $logtype);
    }

    public function clearLog($logtype, $daynum) {
        
    }

    public function batchLogging($data, $logtype) {
        
    }

}
