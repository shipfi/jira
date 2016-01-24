<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * Description of ArrayDecorator
 *
 * @author tianl
 */
abstract class ArrayDecorator {

    /**
     * 前一个处理对象节点
     * @var ArrayDecorator 
     */
    public $handle;
 
    /**
     * 处理方法
     */
    public function doHandle($context) {
        if (!isset($this->handle)) {
            return null;
        }
        return $this->handle->doHandle($context);
    }

}
