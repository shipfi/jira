<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * Description of ApiParam
 *
 * @author tianl
 */
class ApiParam {

    public function __construct() {
        $this->pageindex = 1;
        $this->pagesize = 100;
    }

    /**
     * 页码
     * @var type 
     */
    public $pageindex;

    /**
     * 页大小
     * @var type 
     */
    public $pagesize;

    /**
     * 开始时间戳
     * @var type 
     */
    public $bgntimestamp;

    /**
     * 截止时间戳
     * @var type 
     */
    public $endtimestamp;
    /**
     *
     * @var type  请求标识，多次请求之间维持状态的标识
     */
    public $requestid; 

    /**
     * 数据字典
     * @var type 
     */
    public $data = [];

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __get($name) {
        return $this->data[$name];
    }

}
