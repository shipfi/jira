<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\statistics;

/**
 * Description of StatisticsLocalQueue
 *
 * @author tianl
 */
class StatisticsLocalQueue implements IQueue {

    public function execQueue() {
        
    }

    public function putMessage($message, $attr = array()) {
        print_r($message);die;
    }

}
