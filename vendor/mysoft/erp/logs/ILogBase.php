<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * log持久化
 * @author tianl
 */
interface ILogBase {

    /**
     * 写日志操作
     * @param array|string $data
     * @param string $logtype ,设置日志类别，这里传值则无须调用setLogType
     * @return mixed
     */
    public function logging($data, $logtype);

    /**
     * 更新日志
     * @param string|int    $id
     * @param array|string  $data
     * @param string `      $logtype
     * @return mixed
     */
    public function updateLog($id, $data, $logtype);

    /**
     * 清除daynum前的日志
     * @param type $logtyp 日志类型
     * @param type $daynum 天数
     */
    public function clearLog($logtype, $daynum);

    /**
     * 批量写日志
     * @param type $data
     * @param type $logtype
     */
    public function batchLogging($data, $logtype);
}
