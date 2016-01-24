<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\statistics;

/**
 * Description of StatisticsQueue
 *
 * @author tianl
 */
class StatisticsQueue extends \Queue\RabbitMQ\Fanout implements IQueue {

    public function __construct() {
        parent::__construct('pv_data_queue');
    }

    private $caches = [];

    protected function doHandle($message) {
        //单个实例中，先搜集到内存，每50条批量插入
        $msgObj = json_decode($message, true);
        $this->caches[] = $msgObj;
//        $logSrv = \mysoft\erp\logs\ErpApiLogFactory::createDbLog($msgObj['orgcode'], 'analysis');
//        $logSrv->logging($msgObj, 'pv');
        return true;
    }

    public function handleAllQueueData() {
        $count = 0;
        $logSrv = \mysoft\erp\logs\ErpApiLogFactory::createDbLog('config', 'analysis');
        while (TRUE) {
            $mdfStatus = $this->execQueue();
            if ($count == 50) {
                $logSrv->batchLogging($this->handleSaveData($this->caches), 'pv');
                //每50个插入一次，重置计数器
                $count = 0;
                $this->caches = [];
            }
            if ($mdfStatus['code'] == '1') {
                //队列为空，退出
                if (count($this->caches) > 0) {
                    $logSrv->batchLogging($this->handleSaveData($this->caches), 'pv');
                }
                break;
            }

            if ($mdfStatus['code'] == '2') {
                //执行出错，如何处理
                continue;
            }

            $count++;
        }
    }

    private function handleSaveData($cacheData) {
        $allFields = [];
        foreach ($cacheData as $row) {
            if (count(array_keys($row)) > count($allFields)) {
                $allFields = array_keys($row);
            }
        }
        $result = [];
        foreach ($cacheData as $row) {
            $newRow = [];
            foreach ($allFields as $fd) {
                $newRow[$fd] = isset($row[$fd]) ? $row[$fd] : null;
            }
            $result[] = $newRow;
        }
        return $result;
    }

}
