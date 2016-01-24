<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * ERP增量推送服务基类
 *
 * @author tianl
 */
class ErpAsyncPushServiceBase extends \mysoft\erp\ErpAsyncServiceBase {

    /**
     * ERP接口路径
     * @return string
     */
    protected function getApi() {
        return empty($this->_syncParam['erpApi']) ? 'api/Public/PushData.asmx' : $this->_syncParam['erpApi'];
    }

    /**
     * ERP监控的主表
     * @return type
     */
    protected function getMainTableInErp() {
        return $this->_syncParam['tableInErp'];
    }

    /**
     * ERP监控的主表主键
     * @return type
     */
    protected function getMainTableInErpKeyField() {
        return $this->_syncParam['keyField'];
    }

    /**
     * ERP主表的关联表
     * @return type
     */
    protected function getRelativeTable() {
        if (!isset($this->_syncParam['relativeTables'])) {
            return [];
        }
        return $this->_syncParam['relativeTables'];
    }

    protected function getPostParam() {
        $param = parent::getPostParam();
        $param->data['TableName'] = $this->getMainTableInErp();
        $param->data['KeyField'] = $this->getMainTableInErpKeyField();
        $param->data['RelativeTables'] = $this->getRelativeTable();
        return $param;
    }

    /**
     * 获取ERP字段对应的云端字段
     * @return type
     */
    protected function getMapYdField() {
        $result = [];
        //$fieldMappings = AsyncFields::getFields($this->orgcode, $this->getServiceName());
        $fieldMappings = $this->getErpFieldMapping()->getFields($this->getServiceName());
        foreach ($fieldMappings as $fieldMap) {
            $result[$fieldMap['ErpField']] = $fieldMap['ScrmField'];
        }
        return $result;
    }

    /**
     * 获取ERP表对应的云端表
     * @return type
     */
    protected function getMapYdTable() {
        $result = [];
        //$fieldMappings = AsyncFields::getFields($this->orgcode, $this->getServiceName());
        $fieldMappings = $this->getErpFieldMapping()->getFields($this->getServiceName());
        foreach ($fieldMappings as $fieldMap) {
            if (empty($fieldMap['ScrmTable']) || empty($fieldMap['ErpTable'])) {
                continue;
            }
            $result[$fieldMap['ErpTable']] = $fieldMap['ScrmTable'];
        }
        return $result;
    }

    /**
     * 获取ERP删除数据
     * @param type $firstPageData 第一页，默认删除的数据在第一页传回来
     * @return type
     */
    protected function getErpDeletedData($firstPageData) {
        $delInfos = null;
        if (!empty($firstPageData['data']['Deleted'])) {
            $delInfos = $firstPageData['data']['Deleted'];
        }
        return $delInfos;
    }

}
