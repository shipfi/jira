<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * ERP接口访问基类，负责获取访问的客户端，设置默认访问签名
 *
 * @author tianl
 */
class ErpApiProxy extends \mysoft\base\ServiceBase {

    public function __construct($orgcode, $enableSlaves = false) {
        parent::__construct($orgcode, $enableSlaves);
    }

    private $_erpApiClient;

    /**
     * 获取ErpApiClient，如果外部没有传入，则默认创建一个
     * @return \mysoft\erp\ErpApiClient
     */
    public function getErpApiClient() {
        if (isset($this->_erpApiClient)) {
            return $this->_erpApiClient;
        }
        $this->_erpApiClient = new ErpApiClient($this->orgcode);
        return $this->_erpApiClient;
    }

    /**
     * 设置ErpApiClient
     * @param \mysoft\erp\ErpApiClient $apiClient
     */
    public function setErpApiClient($apiClient) {
        $this->_erpApiClient = $apiClient;
    }

    private $_erpFieldMapping;

    /**
     * 获取ERP字段映射操作服务
     * @return \mysoft\erp\ErpFieldMapping 
     */
    public function getErpFieldMapping() {
        if (isset($this->_erpFieldMapping)) {
            return $this->_erpFieldMapping;
        }
        $this->_erpFieldMapping = new ErpFieldMapping($this->orgcode);
        return $this->_erpFieldMapping;
    }

    /**
     * 设置ERP字段映射操作服务
     * @param \mysoft\erp\ErpFieldMapping $erpFieldMapping
     */
    public function setErpFieldMapping($erpFieldMapping) {
        $this->_erpFieldMapping = $erpFieldMapping;
    }

}
