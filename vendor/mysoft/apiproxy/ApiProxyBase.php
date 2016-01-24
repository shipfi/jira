<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\apiproxy;

use mysoft\sign\Client;

class ApiProxyBase {
    /**
     * @param $api 接口路径
     * @param $data 参数
     * @param array $cachekey 缓存key
     * @return mixed
     * @throws \mysoft\base\Exception|void
     */
    protected function _Proxy($api,$data,$cachekey=[]){
        $resdata=false;
        if($cachekey!=[]){
            $resdata = \yii::$app->cache->get($cachekey);
        }
        if($resdata === false){
            $client = new Client();
            $resultdata = json_decode($client->get($api,$data),true);
            if ($resultdata["success"] == "1") {
                $resdata = $resultdata["data"];
            } else {
                throw E(\mysoft\helpers\String::jsonEncode($resultdata["data"]));
            }
        }
        return $resdata;
    }
}
