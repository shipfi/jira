<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp\logs;

/**
 *
 * @author tianl
 */
interface ILogWriteService {

    /**
     * 添加请求日志（废弃）
     * @param string $postData 请求的数据
     * @param string $requestUrl 请求接口的地址
     * @return int 本次请求ID
     */
    public function logPostData($postData, $requestUrl = "", $context = []);

    /**
     * 请求结束日志（废弃）
     * @param int  $logId  请求ID
     * @param type $receiveData 结束数据
     */
    public function logReceiveData($logId, $receiveData, $context = []);

    /**
     *  记录请求日志
     * @param type $postData
     * @param type $receiveData
     * @param type $requestUrl
     * @param type $context
     */
    public function logRequest($postData, $receiveData, $requestUrl = "", $context = []);

    /**
     * 记录错误日志
     * @param type $postData
     * @param type $errorInfo
     */
    public function logError($postData, $errorInfo, $context = []);
}
