<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * ERP拉取数据服务
 * @author tianl
 */
class PullDataConfigService {

    /**
     * 根据表名获取分发配置
     * @param type $orgcode
     * @param type $tableName
     */
    public function getDispatchConfig($orgcode, $tableName) {
        $returnResult = [];
        $cacheKey = ['erp_pulldata_cfg_{orgcode}', $orgcode];
        $cacheData = \Yii::$app->cache->get($cacheKey);
        $cacheData=[];
        if (empty($cacheData)) {
            $sql = "SELECT orgcode,app,tbname as tableInErp,keyfield as keyField,servname as serviceName,erpapi as erpApi,cfgext from erp_pulldata_cfg WHERE orgcode =:orgcode or orgcode='_default'";
            $allCfgs = DB('config')->createCommand($sql, [':orgcode' => $orgcode])->queryAll();
            $dbResultDic = [];
            foreach ($allCfgs as $row) {
                $dbResultDic[$row['orgcode']][$row['tableInErp']] = $row;
            }
            $defaultCfg = $dbResultDic['_default'];
            $orgCfg = [];
            if (isset($dbResultDic[$orgcode])) {
                $orgCfg = $dbResultDic[$orgcode];
                //将默认的模板配置添加到当前租户，如果当前租户存在，则当前租户的配置优先，不存在，则将模板配置添加到当前租户
                foreach ($defaultCfg as $tbName => $row) {
                    if (empty($orgCfg[$tbName])) {
                        $orgCfg[$tbName] = $row;
                    }
                }
          
            } else {
                //如果租户下没有配置，则取默认配置
                $orgCfg = $defaultCfg;
            }
            \Yii::$app->cache->set($cacheKey, $orgCfg, 2 * 60);
            if (!empty($orgCfg[$tableName])) {
                $returnResult = $orgCfg[$tableName];
            }
        } else {
            if (!empty($cacheData[$tableName])) {
                $returnResult = $cacheData[$tableName];
            }
        }
        return $returnResult;
    }

}
