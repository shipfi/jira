<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\apiproxy;


class ParamApiProxy extends ApiProxyBase {
    public function GetPushes($tenantid){
        return $this->_Proxy( "/api/param/get-pushes", ["tenantid"=>$tenantid]);
    }
}
