<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of ElasticLogMonitor
 *
 * @author tianl
 */
class LogMonitor {

    private static $logDb;

    private static function getLogDb() {
        if (!isset(self::$logDb)) {
            self::$logDb = DB('log');
        }
        return self::$logDb;
    }

    /**
     * 记录日志写入异常
     * @param type $errorInfo
     */
    public static function log($errorInfo) {
        try {
            self::getLogDb()->createCommand()->insert('log_exception', ['msg' => $errorInfo])->execute();
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'Api-log');
        }
    }

    public static function getLogs() {
        $sql = 'select * from log_exception';
        return self::getLogDb()->createCommand($sql)->queryAll();
    }

    /**
     * 写入接口统计信息
     * @param type $message
     */
    public static function writeErpApiStatics($message) {
        self::getLogDb()->createCommand()->insert('erpapi_call_statics', $message)->execute();
    }

}
