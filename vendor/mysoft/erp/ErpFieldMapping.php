<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * AsyncFields包装类，依赖的静态类无法进行模拟测试
 * @author tianl
 */
class ErpFieldMapping {

    private $orgcode;

    public function __construct($orgcode) {
        $this->orgcode = $orgcode;
    }

    const ERP_SYNC_XML_SRV_CACHE = 'erp_sync_xml_{orgcode}_{servname}_{type}';

    public $_erpFieldMappingDal;

    /**
     * 
     * @return \mysoft\erp\ErpFieldMappingDAL
     */
    public function getErpFieldMappingDal() {
        if (isset($this->_erpFieldMappingDal)) {
            return $this->_erpFieldMappingDal;
        }
        $this->_erpFieldMappingDal = new ErpFieldMappingDAL($this->orgcode);
        return $this->_erpFieldMappingDal;
    }

    /**
     * 
     * @param \mysoft\erp\ErpFieldMappingDAL $erpFieldMappingDal
     */
    public function setErpFieldMappingDal($erpFieldMappingDal) {
        $this->_erpFieldMappingDal = $erpFieldMappingDal;
    }

    /**
     * 获取配置信息
     * @param type $serviceName
     * @param type $nodeType
     * @return type
     */
    public function getFields($serviceName, $nodeType = 'ServiceName') {
        //return AsyncFields::getFields($this->orgcode, $serviceName, $nodeType);
        $cacheData = \Yii::$app->cache->get([self::ERP_SYNC_XML_SRV_CACHE, $this->orgcode, $serviceName, $nodeType]);
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $result = [];
        $xml = $this->getErpFieldMappingDal()->getFieldXml($this->orgcode);
        if (!empty($xml)) {
            $xmlObj = simplexml_load_string($xml);
            foreach ($xmlObj->ServiceConfig as $value) {
                if ($nodeType == 'ServiceName' && (string) $value->attributes()->ServiceName == $serviceName) {
                    $result = self::convertToArray($value);
                    break;
                }
                if ($nodeType == 'ErpApiName' && (string) $value->attributes()->ErpApiName == $serviceName) {
                    $result = self::convertToArray($value);
                    break;
                }
            }
        }
        if (!empty($result)) {
            \Yii::$app->cache->set([self::ERP_SYNC_XML_SRV_CACHE, $this->orgcode, $serviceName, $nodeType], $result, 60 * 5);
        }
        return $result;
    }

    /**
     * 将ERP数据转换成云客字段
     * @param type $erpData
     * @param type $serviceName
     * @return type
     */
    public function convertToMyFields($erpData, $serviceName) {
        //return AsyncFields::convertToMyFields($this->orgcode, $erpData, $serviceName);       
        $fieldMappings = $this->getFields($serviceName);
        $fieldMappings = \yii\helpers\ArrayHelper::index($fieldMappings, 'ErpField');
        $result = [];
        foreach ($erpData as $row) {
            $newRow = $this->convertRowToMyFields($fieldMappings, $row);
            $result[] = $newRow;
        }
        return $result;
    }

    /**
     * 将一行数据转换成云端字段
     * @param type $fieldMappings
     * @param type $row
     * @return type
     */
    public function convertRowToMyFields($fieldMappings, $row) {
        $newRow = [];
        foreach ($row as $key => $val) {
            if (isset($fieldMappings[$key]['ScrmField'])) {
                if (isset($fieldMappings[$key]['ErpType']) && $fieldMappings[$key]['ErpType'] == 'bool') {
                    $val = strtolower($val) == 'true';
                }
                $newRow[$fieldMappings[$key]['ScrmField']] = $val;
            }
        }
        return $newRow;
    }

    private static function convertToArray($value) {
        $result = [];
        foreach ($value->Attribute as $key => $row) {
            $rowAr = [];
            foreach ($row->attributes() as $key => $val) {
                $rowAr[$key] = (string) $val;
            }
            if (empty($rowAr['ErpType'])) {
                $rowAr['ErpType'] = 'string';
            }
            if (empty($rowAr['ScrmExpression'])) {
                $rowAr['ScrmExpression'] = '';
            }
            $result[] = $rowAr;
        }
        return $result;
    }

}
