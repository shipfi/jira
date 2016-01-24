<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * Description of ErpFieldMappingDAL
 *
 * @author tianl
 */
class ErpFieldMappingDAL extends \mysoft\base\DALBase {

    const ERP_SYNC_XML_CACHE = 'erp_sync_xml_{orgcode}';


    /**
     * 获取字段映射信息
     * @param type $orgcode
     * @return type
     */
    public function getFieldXml($orgcode) {
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

}
