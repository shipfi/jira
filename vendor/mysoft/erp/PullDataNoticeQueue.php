<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

use MNS\Message;

/**
 * ERP数据拉取-变更通知队列
 * @author tianl
 */
class PullDataNoticeQueue {

    private $_mns;
    private $_qname;

    public function __construct($app, $conf = null) {
        //$app  :apps,plan,report,workflow
        //每个应用，一个队列
        $this->_qname = "erp-pulldata-notice-" . $app;
        //aliyun mns消息
        $this->_mns = new Message();
        $this->_mns->useQueue($this->_qname);
    }

    protected function doHandle($message) {
        //各子应用自己实现处理队列消息的逻辑
        throw new Exception('未实现处理队列数据方法');
    }

    /**
     * 循环处理队列中的所有数据
     */
    public function dispatchQueueData() {
        while (TRUE) {
            $mdfStatus = $this->execQueue();
            if ($mdfStatus['code'] == '1') {
                //队列为空，退出
                break;
            }
            if ($mdfStatus['code'] == '2') {
                //执行出错，如何处理
                continue;
            }
        }
    }

    public function execQueue() {
        try {
            $this->_mns->recevie();
            if ($this->_mns->queue_empty()) {
                return array('code' => 1, 'msg' => '队列为空');
            }
            $encodeMsg = $this->_mns->get_last_message_content();
            $this->doHandle($encodeMsg);
            //处理成功则删除
            $handle = $this->_mns->get_last_receiptHandle();
            $this->_mns->delete($handle);

            return array('code' => 0, 'msg' => '执行成功');
        } catch (\Exception $exc) {
            $err_msg = 'Code:' . $exc->getCode() . '，File:' . $exc->getFile() . '，Line:' . $exc->getLine() . ';' . $exc->getMessage() . ';' . $exc->getTraceAsString();
            //记录队列处理的异常信息  
            \Yii::error($err_msg, 'ErpApi-PullChangeDataNotice');
            return array('code' => 2, 'msg' => '处理程序发生异常，并已经记录');
        }
    }

    /**
     * 发送消息
     * @param string $message
     */
    public function putMessage($message) {
        $this->_mns->send($message);
    }

}
