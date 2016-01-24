<?php

namespace mysoft\erp;

/**
 * ERP接口管家配置管理
 */
class ErpApiConf {

    /**
     * 获取ERP站点配置
     * @param type $tenant_id
     * @return type
     */
    public static function getSite($tenant_id) {
        $setting = self::getErpSetting($tenant_id);
        return !empty($setting['erpapi_url']) ? $setting['erpapi_url'] : '';
    }

    /**
     * 设置ERP站点配置
     * @param type $tenant_id
     * @param type $url
     */
    public static function setSite($tenant_id, $url) {
        $setting = [
            'tenant_id' => $tenant_id,
            'erpapi_url' => $url,
        ];
        self::setErpSetting($setting);
    }

    /**
     * 读取配置信息
     * @param type $tenant_id
     * @return type
     */
    private static function _getErpSetting($tenant_id) {
        $sql = "SELECT tenant_id,app_id,app_secret,erpapi_url from erpapi_seting WHERE tenant_id = :tenant_id";
        $erpSetting = DB('config')->createCommand($sql, [
                    ":tenant_id" => $tenant_id
                ])->queryOne();
        return $erpSetting;
    }

    /**
     * 写配置信息
     * @param array $setting [tenant_id,app_id,app_secret,erpapi_url]
     */
    private static function _setErpSetting($setting) {
        DB('config')->createCommand()->delete('erpapi_seting', ['tenant_id' => $setting['tenant_id']])->execute();
        DB('config')->createCommand()->insert('erpapi_seting', $setting)->execute();
    }

    /**
     * 获取配置信息
     * @param type $tenant_id
     * @return string
     */
    public static function getErpSetting($tenant_id, $useCache = true) {
        if (empty($tenant_id)) {
            return NULL;
        }
        $setting = \yii::$app->cache->get(['erpsetting_{orgcode}', $tenant_id]);
        $setting = $useCache ? $setting : '';
        if (empty($setting)) {
            $setting = self::_getErpSetting($tenant_id);
            \yii::$app->cache->set(['erpsetting_{orgcode}', $tenant_id], $setting, 3600 * 24 * 360);
        }
        return $setting;
    }

    /**
     * 设置erpsetting缓存
     * @param type $setting [tenant_id,app_id,app_secret,erpapi_url]
     */
    public static function setErpSetting($setting) {
        $existsCotent = self::getErpSetting($setting['tenant_id'], false);
        foreach ($setting as $key => $value) {
            $existsCotent[$key] = $value;
        }
        self::_setErpSetting($existsCotent);
        \yii::$app->cache->delete(['erpsetting_{orgcode}', $setting['tenant_id']]);
    }

}
