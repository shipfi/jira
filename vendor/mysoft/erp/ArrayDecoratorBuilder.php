<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * Description of DataTableHandle
 *
 * @author tianl
 */
class ArrayDecoratorBuilder {

    /**
     * 管道中的最后一个节点
     * @var ArrayDecorator 
     */
    private $curHandle;

    /**
     *管道中的第一个节点
     * @var ArrayDecorator 
     */
    public $Handle;

    public function registerHandle(ArrayDecorator $handle) {
        //将管道中最后一个节点的handle指向新添加的handle
        if (!isset($this->curHandle)) {
            $this->curHandle = $handle;
            $this->Handle=$this->curHandle;
            return $this;
        }
        $this->curHandle->handle = $handle;
        $this->curHandle = $this->curHandle->handle;
        return $this;
    }

}
