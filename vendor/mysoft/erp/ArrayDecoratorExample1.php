<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

/**
 * Description of ArrayDecoratorExample1
 *
 * @author tianl
 */
class ArrayDecoratorExample1 extends ArrayDecorator {

    public function doHandle($context) {
        $result = parent::doHandle($context + 1);
        if (empty($result)) {
            return $result = ['Example' . $context];
        }
        $result[] = 'Example' . ($context);
        return $result;
    }

}
