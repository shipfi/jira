<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\apiproxy;


class MessageApiProxy extends ApiProxyBase {
    private $pusher = []; //推送器
    private $license=[];

    public function __construct($tenantid) {
        //获取该租户有哪些推送器
        $paramsProxy = new ParamApiProxy();
        $push_param = $paramsProxy->getPushes($tenantid);
        if(!empty($push_param)){
            foreach ($push_param as $push) {
                if($push["param_value"]){
                    $pusher[]=substr($push['param_code'], strlen('push_msg_'));
                }
            }
        }


        var_dump($pusher);

    }
    public function send($msginfo){

    }
}
