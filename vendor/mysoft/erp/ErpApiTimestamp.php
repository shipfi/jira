<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * ERPAPI时间戳
 */
class ErpApiTimestamp extends \mysoft\base\DALBase {

    /**
     * 获取时间戳
     * @param type $orgcode
     * @param type $taskname
     * @param type $businessKey
     * @return string
     */
    public function getTimestamp($taskname, $businessKey) {
        $sql = 'SELECT time_stamp from erp_api_timestamp WHERE task_name=:tskname and businesskey=:businesskey limit 1';
        $result = $this->db->createCommand($sql, [':tskname' => $taskname, ':businesskey' => $businessKey])->queryScalar();
        if (empty($result))
            $result = '';

        return $result;
    }

    /**
     * 更新时间戳
     * @param type $orgcode
     * @param type $taskname
     * @param type $businessKey
     * @param type $timestamp
     */
    public function updateTimestamp($taskname, $businessKey, $timestamp) {
        $sql = 'INSERT INTO erp_api_timestamp(`task_name`,`businesskey`,`time_stamp`)VALUES(:task_name,:businesskey,:time_stamp)
                ON DUPLICATE KEY UPDATE `time_stamp`=:time_stamp';
//        $rawSql=  $this->db->createCommand($sql, [':task_name' => $taskname, ':businesskey' => $businessKey, ':time_stamp' => $timestamp])->getRawSql();
//        print_r($rawSql);die;
        $this->db->createCommand($sql, [':task_name' => $taskname, ':businesskey' => $businessKey, ':time_stamp' => $timestamp])->execute();
    }

    /**
     * 删除时间戳
     * @param type $taskname
     * @param type $businessKey
     */
    public function delTimeStamp($taskname, $businessKey) {
        $sql = 'delete from erp_api_timestamp WHERE task_name=:tskname and businesskey=:businesskey';
        $this->db->createCommand($sql, [':tskname' => $taskname, ':businesskey' => $businessKey])->execute();
    }

}
