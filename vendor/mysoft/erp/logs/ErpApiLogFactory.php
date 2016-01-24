<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of ErpApiLogFactory
 *
 * @author tianl
 */
class ErpApiLogFactory {

    /**
     * 同步日志
     * @param type $orgcode
     * @return \mysoft\erp\logs\LogService
     */
    public static function createLog($orgcode) {
        return new LogService($orgcode);
    }

    /**
     * 异步日志
     * @param type $orgcode
     * @return \mysoft\erp\logs\AsyncLogService
     */
    public static function createAsyncLog($orgcode) {
        return new AsyncLogService($orgcode);
    }

    /**
     * 写入ES和DB日志
     * @param type $orgcode
     * @param type $app
     * @return \mysoft\erp\logs\Elastic2DbLog
     */
    public static function createDb2EsLog($orgcode, $app = 'erpapi') {
        return new Elastic2DbLog($app, $orgcode);
    }

    /**
     * 写DB日志
     * @param type $orgcode
     * @param type $app
     * @return \mysoft\erp\logs\DbLog
     */
    public static function createDbLog($orgcode, $app = 'erpapi') {
        return new DbLog($app, $orgcode);
    }

}
