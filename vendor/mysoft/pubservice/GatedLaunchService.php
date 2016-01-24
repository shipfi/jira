<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\pubservice;

/**
 * 灰度发布表
 *
 * @author tianl
 */
class GatedLaunchService {

    /**
     * ERP接口框架灰度发布标识
     */
    const UTYPE_ERPAPI_UPDATE = "erp-api-update";

    /**
     * ERP接口改造，触发器增量方案灰度发布
     */
    const UTYPE_ERPAPI_TRIGGERPULL = "erp-api-trigger-pull";

    /**
     * 获取所有未升级的租户
     * @param type $utype
     * @return type
     */
    public function getUnupdateTenants($utype) {
        return DB("config")->createCommand("select tenant_id from p_gatedlaunch where utype=:utype", [':utype' => $utype])->queryColumn();
    }

    /**
     * 从灰度发布表删除
     * @param type $utype
     * @param type $tenant_id
     */
    public function delUnupdateTenants($utype, $tenant_id) {
        DB("config")->createCommand("delete from p_gatedlaunch where utype=:utype and tenant_id=:tenant_id", [':utype' => $utype, ':tenant_id' => $tenant_id])->execute();
        \Yii::$app->cache->delete(['gatedlaunch_{utype}', $utype]);
    }

    /**
     * 添加灰度发布表
     * @param type $utype
     * @param type $tenant_id
     */
    public function addUnupdateTenants($utype, $tenant_id) {
        $this->delUnupdateTenants($utype, $tenant_id);
        DB("config")->createCommand()->insert('p_gatedlaunch', ['utype' => $utype, 'tenant_id' => $tenant_id])->execute();
        \Yii::$app->cache->delete(['gatedlaunch_{utype}', $utype]);
    }

    /**
     * 检查是否使用了新ERP接口管家
     * @param type $utype 灰度类型
     * @param type $tenant_id 租户ID
     * @return type
     */
    public function checkErpApiIsOldVersion($utype, $tenant_id) {
        $key = ['gatedlaunch_{utype}', $utype];
        $unUpdateOrgs = \Yii::$app->cache->get($key);
        if (isset($unUpdateOrgs)) {
            $unUpdateOrgs = $this->getUnupdateTenants($utype);
            \Yii::$app->cache->set($key, $unUpdateOrgs, 60);
        }
        if (empty($unUpdateOrgs)) {
            $unUpdateOrgs = [];
        }
        $isOldVersion = in_array($tenant_id, $unUpdateOrgs);
        //@file_put_contents('/tmp/erp_api_auth.log', date("Y-m-d H:i:s", time()) . "  orgcode:{$this->orgcode} 是否旧版本:$isOldVersion \r\n", FILE_APPEND);       
        return $isOldVersion;
    }

}
