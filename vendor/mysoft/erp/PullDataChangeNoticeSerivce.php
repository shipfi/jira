<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * ERP拉取数据服务--拉取变更通知服务
 * @author tianl
 */
class PullDataChangeNoticeSerivce extends ErpAsyncServiceBase {

    protected function getApi() {
        return 'api/Public/PullDataChangeNotify.asmx';
    }

    protected function getServiceName() {
        return 'PullDataChangeNotify';
    }

    protected function handleModifyData($erpData) {
        if (!empty($erpData['data']['ModifyTable'])) {
            $queueMap = [];
            //为每个租户，每个应用创建队列，将消息分发到各个队列
            foreach ($erpData['data']['ModifyTable'] as $tableName) {
                $cfg = $this->getPullDataCfg()->getDispatchConfig($this->orgcode, $tableName);
                if (!empty($cfg)) {
                    $queueKey = sprintf('org{%s}app{%s}', $this->orgcode, $cfg['app']);
                    if (!isset($queueMap[$queueKey])) {
                        $queueMap[$queueKey] = new PullDataNoticeQueue($cfg['app']);
                    }
                    $cfg['orgcode'] = $this->orgcode;
                    $queueMap[$queueKey]->putMessage(\mysoft\helpers\String::jsonEncode($cfg));
                }
            }
        }
    }

    private $_pullDataCfg;

    protected function getPullDataCfg() {
        if (empty($this->_pullDataCfg)) {
            $this->_pullDataCfg = new PullDataConfigService();
        }
        return $this->_pullDataCfg;
    }

}
