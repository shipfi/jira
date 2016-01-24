<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\statistics;

/**
 * Description of IQueue
 *
 * @author tianl
 */
interface IQueue {

    /**
     * 投放消息到指定的队列中
     * @param string $message 消息内容,必须是字符串类型
     * @param array $attr 消息内容 array('priority'=>'消息的优先级（0-10)')
     * @author gongzheng <gongz@mysoft.com.cn>
     * @since 2015年6月24日 15:33:29
     * @copyright © 2014 深圳市明源软件股份有限公司 <http://www.mysoft.com.cn>
     * @return boolean 成功返回TRUE/失败返回FALSE
     */
    public function putMessage($message, $attr = array());

    /**
     * 获取一条消息，并进行处理的流程
     * @author gongzheng <gongz@mysoft.com.cn>
     * @since 2015年6月24日 15:33:29
     * @copyright © 2014 深圳市明源软件股份有限公司 <http://www.mysoft.com.cn>
     * @return array array('code' => '状态码', 'msg' => '说明'); 成功code=0/失败code为除0以外的值
     */
    public function execQueue();
}
