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
interface ITaskWriteService {

    /**
     * 创建任务
     * @param type $taskType 任务类型
     */
    public function createTask($taskType);

    /**
     * 关闭任务
     * @param type $taskId 任务ID
     * @param type $taskInfo 任务执行摘要信息
     * @param type $isSuccess 是否成功
     */
    public function closeTask($taskId, $taskInfo, $isSuccess = 1);
    
    /**
     * 记录任务
     * @param type $taskId 任务ID
     * @param type $taskType 任务类型
     * @param type $taskInfo 任务执行摘要信息
     * @param type $isSuccess 是否成功
     * @param type $context 上下文信息
     */
    public function logTask($taskId, $taskType, $taskInfo, $isSuccess = 1, $context = array());
}
