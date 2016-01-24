<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\statistics;

/**
 * 统计服务 
 * @author tianl
 */
class VisitStatiService {

    /**
     * 记录统计信息
     * @param type $statisInfo
     */
    public static function log(array $statisInfo) {
        $type = 'mysoft\statistics\StatisticsQueue';
        if (YII_ENV == 'dev') {
            $type = 'mysoft\statistics\StatisticsLocalQueue';
        }
        $queueSrv = \Yii::createObject($type);
        $jsonStr = \mysoft\helpers\String::jsonEncode($statisInfo);
        $queueSrv->putMessage($jsonStr);
    }

}
