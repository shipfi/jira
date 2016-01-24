<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 * Description of LogType
 *
 * @author tianl
 */
class LogType {

    /**
     * 获取logType的格式，按类型，日期进行拆分文档，或者存数据表
     * @param type $logType
     * @return type
     */
    public static function getLogTypeFormart($logType, $date = '') {
        //年，月，全年第几周
        if (empty($date)) {
            $date = date('Y_m_d');
        }
        return sprintf('%s|%s', $logType, $date); //date('Y_m_W')
    }

    /**
     * 获取logType组成元素
     * @param type $logTypeFull
     * @return type
     */
    public static function parseLogTypeElement($logTypeFull) {
        list($logType, $date) = explode('|', $logTypeFull);
        return [
            'logType' => $logType,
            'date' => $date,
        ];
    }

    /**
     * ES日志数据按天拆分，默认只能查询15天内的数据
     * @param type $logType
     * @param type $dayLength
     * @return type
     */
    public static function getQueryLogTypes($logType, $dayLength = 15) {
        $types = [];
        for ($i = 0; $i < $dayLength; $i++) {
            $types[] = self::getLogTypeFormart($logType, date('Y_m_d', strtotime("-$i day")));
        }
        return implode(',', $types);
    }

}
