<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\apiproxy;


class UserApiProxy extends ApiProxyBase {
    public function GetUserinfoByUsercode($tenantid,$usercode){
        $cache_key=['userinfo_by_usercode_{tenantid}_{usercode}', $tenantid,$usercode];
        return $this->_Proxy(
            "/api/user/get-userinfo-by-usercode",
            ["tenantid"=>$tenantid,"usercode"=>$usercode],
            $cache_key
        );
    }
}
