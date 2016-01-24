<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * 已废弃，转移至 ErpFieldMapping
 */
class AsyncFields {

    const ERP_SYNC_XML_CACHE = 'erp_sync_xml_{orgcode}';
    const ERP_SYNC_XML_SRV_CACHE = 'erp_sync_xml_{orgcode}_{servname}_{type}';

    /**
     * 获取字段映射信息
     * @param type $orgcode
     * @return type
     */
    private static function getFieldXml($orgcode) {
        $cacheData = \Yii::$app->cache->get([self::ERP_SYNC_XML_CACHE, $orgcode]);
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $sql = "SELECT `xml` from erp_sync_xml WHERE orgcode=:orgcode";
        $result = DB("config")->createCommand($sql, [':orgcode' => $orgcode])->queryOne();
        if (!empty($result)) {
            \Yii::$app->cache->set([self::ERP_SYNC_XML_CACHE, $orgcode], $result['xml'], 60);
            return $result['xml'];
        }
    }

    /**
     * 获取配置信息
     * @param type $orgcode
     * @param type $serviceName
     * @param type $nodeType
     * @return type
     */
    public static function getFields($orgcode, $serviceName, $nodeType = 'ServiceName') {
        $cacheData = \Yii::$app->cache->get([self::ERP_SYNC_XML_SRV_CACHE, $orgcode, $serviceName, $nodeType]);
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $result = [];
        $xml = self::getFieldXml($orgcode);
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
            \Yii::$app->cache->set([self::ERP_SYNC_XML_SRV_CACHE, $orgcode, $serviceName, $nodeType], $result, 60 * 5);
        }
        return $result;
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

    /**
     * 将ERP数据转换成云客字段
     * @param type $orgcode
     * @param type $erpData
     * @param type $serviceName
     * @return type
     */
    public static function convertToMyFields($orgcode, $erpData, $serviceName) {
        $fieldMappings = self::getFields($orgcode, $serviceName);
        $fieldMappings = \yii\helpers\ArrayHelper::index($fieldMappings, 'ErpField');
        $result = [];
        foreach ($erpData as $row) {
            $newRow = static::convertRowToMyFields($fieldMappings, $row);
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
    public static function convertRowToMyFields($fieldMappings, $row) {
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

}
