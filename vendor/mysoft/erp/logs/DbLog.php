<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of DbLog
 *
 * @author tianl
 */
class DbLog extends \mysoft\base\DALBase implements ILogBase {

    private $_app;
    private $logDb;

    public function __construct($app, $orgcode, $enableSlaves = 'false') {
        parent::__construct('config', $enableSlaves);
        $this->_app = $app;
    }

    private function getLogDb() {
        if (empty($this->logDb)) {
            $this->logDb = DB('log');
        }
        return $this->logDb;
    }

    public function logging($data, $logtype) {
        try {

            $saveData = $this->convertData($data);
            if (empty($saveData['_id'])) {
                $saveData['_id'] =uniqid('elg',TRUE);
            }
            $this->getLogDb()->createCommand()->insert("log_$logtype", $saveData)->execute();
            return $saveData['_id'];
        } catch (\Exception $exc) {
            //$this->createLogTableEx($data, $logtype, $exc);
            \Yii::error($exc->getMessage(), 'Api-log');
        }
    }

    public function updateLog($id, $data, $logtype) {
        try {
            $saveData = $this->convertData($data);
            $this->getLogDb()->createCommand()->update("log_$logtype", $saveData, ['_id' => $id])->execute();
        } catch (\Exception $exc) {
            \Yii::error($exc->getMessage(), 'Api-log');
        }
    }

    private function convertData($data) {
        $saveData = [];
        foreach ($data as $key => $val) {
            //不记录postData,receiveData注释
            if ($key == 'receiveDataStr') {
                $saveData['receiveData'] = $val;
                continue;
            }
            if ($key == 'postDataStr') {
                $saveData['postData'] = $val;
                continue;
            }
            if (is_array($val)) {
                $saveData[$key] = \mysoft\helpers\String::jsonEncode($val);
            } else {
                $saveData[$key] = $val;
            }
        }
        return $saveData;
    }

    public function clearLog($logtype, $daynum) {
        try {
            $sql = "DELETE FROM log_$logtype where bgntime<= ADDDATE(CURRENT_DATE,INTERVAL -$daynum DAY)";
            $this->getLogDb()->createCommand($sql)->execute();
        } catch (\Exception $exc) {
            \Yii::error($exc->getMessage(), 'Api-log');
        }
    }

    public function batchLogging($data, $logtype) {
        if (count($data) == 0)
            return;

        try {
            $saveDataArr = [];
            foreach ($data as $row) {
                $newRow = $this->convertData($row);
                $newRow['_id'] = uniqid('elg', true);
                $saveDataArr[] = $newRow;
            }
            //$sql= $this->getLogDb()->createCommand()->batchInsert("log_$logtype", $allFields, $saveDataArr)->getRawSql();
            //print_r($sql);
            $this->getLogDb()->createCommand()->batchInsert("log_$logtype", array_keys($saveDataArr[0]), $saveDataArr)->execute();
        } catch (\Exception $exc) {
            //print_r($exc->getMessage().';'.$exc->getTraceAsString());
            //$this->createLogTableEx($data, $logtype, $exc);
            \Yii::error($exc->getMessage(), 'Api-log');
        }
    }

//    private function createLogTableEx($data, $logtype, \Exception $exc) {
//        try {
//            $reg = "/Table '\w+\.*\w+' doesn't exist/i";
//            if (preg_match($reg, $exc->getMessage())) {
//                //如果是表存在的异常，则创建日志表
//                $this->createLogTable($logtype);
//            }
//            $this->logging($data, $logtype);
//        } catch (\Exception $exc) {
//            \Yii::error($exc->getMessage(), 'Api-log');
//        }
//    }
//
//    private function createLogTable($logtype) {
//        $typeInfo = LogType::parseLogTypeElement($logtype);
//        if ($typeInfo['logType'] == 'tasklog') {
//            $taskLogSql = "CREATE TABLE IF NOT EXISTS `$logtype` (
//                        `orgcode` varchar(100) DEFAULT NULL,
//                        `_id` varchar(50) NOT NULL DEFAULT '',
//                        `taskType` varchar(100) DEFAULT NULL,
//                        `bgnTime` datetime DEFAULT NULL,
//                        `endTime` datetime DEFAULT NULL,
//                        `success` tinyint(4) DEFAULT NULL,
//                        `_t` datetime DEFAULT NULL,
//                        `logInfo` text,
//                        PRIMARY KEY (`_id`)
//                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//            $this->getLogDb()->createCommand($taskLogSql)->execute();
//            return;
//        }
//        if ($typeInfo['logType'] == 'datalog') {
//            $dataLogSql = "CREATE TABLE IF NOT EXISTS `$logtype` (
//                            `_id` varchar(50) NOT NULL DEFAULT '',
//                            `orgcode` varchar(100) DEFAULT NULL,
//                            `taskId` varchar(50) DEFAULT NULL,
//                            `taskName` varchar(100) DEFAULT NULL,
//                            `requestUrl` varchar(1000) DEFAULT NULL,
//                            `bgnTime` datetime DEFAULT NULL,
//                            `endTime` datetime DEFAULT NULL,
//                            `requestTime` decimal(10,0) DEFAULT NULL,
//                            `_t` datetime DEFAULT NULL,
//                            `receiveData` text,
//                            `postData` text,
//                            PRIMARY KEY (`_id`)
//                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//            $this->getLogDb()->createCommand($dataLogSql)->execute();
//            return;
//        }
//        if ($typeInfo['logType'] == 'errorlog') {
//            $errorLogSql = " CREATE TABLE IF NOT EXISTS `$logtype` (
//                  `_id` varchar(50) NOT NULL DEFAULT '',
//                   `orgcode` varchar(100) DEFAULT NULL,
//                  `_t` datetime DEFAULT NULL,
//                  `taskId` varchar(50) DEFAULT NULL,
//                  `taskName` varchar(100) DEFAULT NULL,
//                  `bgnTime` datetime DEFAULT NULL,
//                  `postData` text,
//                  `httpErrorInfo` varchar(1000) DEFAULT NULL,
//                  `apiErrorInfo` text,
//                  PRIMARY KEY (`_id`)
//                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//            $this->getLogDb()->createCommand($errorLogSql)->execute();
//            return;
//        }
//    }
}
