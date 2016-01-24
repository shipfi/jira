<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\apiproxy;


class CustomApiProxy extends ApiProxyBase {

    public function getConfig($org_code ,$app_code ,$config_code = ''){
        $cache_key=['CustomConfig_{orgcode}_{appcode}', $org_code,$app_code];
        //取该租户所有个性化配置
        $customs =  $this->_Proxy(
            "api/custom/get-config",
            ['orgcode' =>$org_code,'app_code' => $app_code],
            $cache_key
        );
        if (empty($config_code)) {
            return $customs;
        }
        if(is_string($config_code)){
            $config_code = [$config_code];
        }
        $return_arr = [];
        foreach ($config_code as $code) {
            if(isset($customs[$code])){
                $return_arr[$code] = $customs[$code];
            }
        }
        return $return_arr;
    }
}
