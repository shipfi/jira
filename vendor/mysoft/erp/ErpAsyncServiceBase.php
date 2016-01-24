<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\erp;

use mysoft\helpers\String;

/**
 * ERP轮询同步数据基类
 * @author tianl
 */
abstract class ErpAsyncServiceBase extends \mysoft\base\ServiceBase {

    private $erpApiClient;
    protected $_syncParam;

    public function __construct($syncParam, $orgcode, $enableSlaves = false) {
        parent::__construct($orgcode, $enableSlaves);
        $this->_syncParam = $syncParam;
    }

    protected function getLog() {
        return $this->getCleint()->getLog();
    }

    /**
     * 获取ErpApiClient实例
     * @return \mysoft\erp\ErpApiClient
     */
    protected function getCleint() {
        if (!empty($this->erpApiClient)) {
            return $this->erpApiClient;
        }

        $this->erpApiClient = new ErpApiClient($this->orgcode);
        $this->erpApiClient->isLoop = true;
        return $this->erpApiClient;
    }

    /**
     * 设置ErpApiClient
     * @param \mysoft\erp\ErpApiClient $erpApiClient
     */
    public function setClient($erpApiClient) {
        $this->erpApiClient = $erpApiClient;
    }

    private $erpApiTsSrv;

    /**
     * @return  \mysoft\erp\ErpApiTimestamp
     */
    protected function getErpApiTsSrv() {
        if (isset($this->erpApiTsSrv)) {
            return $this->erpApiTsSrv;
        }
        $this->erpApiTsSrv = new ErpApiTimestamp($this->orgcode);
        return $this->erpApiTsSrv;
    }

    /**
     * 
     * @param \mysoft\erp\ErpApiTimestamp $erpApiTsSrv
     */
    public function setErpApiTsSrv($erpApiTsSrv) {
        $this->erpApiTsSrv = $erpApiTsSrv;
    }

    private $_erpFieldMapping;

    /**
     * 获取ERP字段映射操作服务
     * @return \mysoft\erp\ErpFieldMapping 
     */
    public function getErpFieldMapping() {
        if (isset($this->_erpFieldMapping)) {
            return $this->_erpFieldMapping;
        }
        $this->_erpFieldMapping = new \mysoft\erp\ErpFieldMapping($this->orgcode);
        return $this->_erpFieldMapping;
    }

    /**
     * 设置ERP字段映射操作服务
     * @param \mysoft\erp\ErpFieldMapping $erpFieldMapping
     */
    public function setErpFieldMapping($erpFieldMapping) {
        $this->_erpFieldMapping = $erpFieldMapping;
    }

    /**
     * 字段映射XML中的serviceName
     * @return type
     */
    protected function getServiceName() {
        //return 'GetProject';
        return $this->_syncParam['serviceName'];
    }

    /**
     * 拉取模式 all，重新拉取全部
     */
    protected function getPullMode() {
        return !empty($this->_syncParam['pullMode']) ? $this->_syncParam['pullMode'] : '';
    }

    /**
     * 时间戳参数组成的业务键，用于确定时间戳
     * @return string
     */
    protected function getBizkey() {
        return '';
    }

    /**
     * ERP接口路径
     * @return string
     */
    protected abstract function getApi();

    /**
     * 是否将所有分页请求数据收集到内存，用于需要事物处理，删除，增改的场景
     * @return boolean
     */
    protected function readPageDataInMem() {
        return FALSE;
    }

    /**
     * 获取默认的增量请求的参数，需附加参数，重写该方法，添加其他参数
     * @return \mysoft\erp\ApiParam
     */
    protected function getPostParam() {
        $param = new \mysoft\erp\ApiParam();
        $param->data['ServiceName'] = $this->getServiceName();
        if ($this->getPullMode() == 'all') {
            $param->bgntimestamp = '';
        } else {
            $param->bgntimestamp = $this->getErpApiTsSrv()->getTimestamp($this->getServiceName(), $this->getBizkey());
            if (empty($param->bgntimestamp)) {
                $this->_syncParam['pullMode'] = 'all';
            }
        }
        return $param;
    }

    /**
     * doAsync方法是否往外抛异常
     * @var type 
     */
    public $throwDoAsyncException = false;

    /**
     * 执行拉取ERP数据动作，默认记录记录task信息，处理异常，记录日志
     */
    public function doAsync() {
        set_time_limit(0);
        //作业上下文，用于分页请求之间传递共享信息
        $taskContext = [];
        $exception = null;
        $exeCheck = false;
        try {
            $exeCheck = $this->checkHasExecutionTask();
            if (!$exeCheck) {
                $taskContext['taskId'] = uniqid('erptsk', TRUE);
                $taskContext['taskName'] = $this->getServiceName();
                $taskContext['bgnTime'] = date('Y-m-d H:i:s');
                $this->setExecutionStatus(TRUE);
                $this->handleBusiness($taskContext);
            }
        } catch (logs\ErpApiException $exc) {
            //ErpApi异常已在异常日志中记录了，task的mesage中不记录异常
            $exception = $exc;
        } catch (\Exception $exc) {
            $exception = $exc;

            $taskContext['errMsg'] = $exc->getMessage();
            $taskContext['trace'] = $exc->getTraceAsString();
        } finally {
            $taskContext['endTime'] = date('Y-m-d H:i:s');
            //如果有正在执行的作业
            if ($exeCheck) {
                throw new logs\ErpApiException(sprintf('有正在执行的作业 %s', $this->getServiceName()));
            }

            $this->setExecutionStatus(FALSE);
        }

        try {
            $taskErrInfo = [];
            if (!empty($taskContext['errMsg'])) {
                $taskErrInfo['errMsg'] = $taskContext['errMsg'];
            }
            if (!empty($taskContext['trace'])) {
                $taskErrInfo['trace'] = $taskContext['trace'];
            }
            $isSuccess = isset($exception) ? 0 : 1;
            $this->getLog()->logTask($taskContext['taskId'], $taskContext['taskName'], $taskErrInfo, $isSuccess, $taskContext);
        } catch (\Exception $exc) {
            $msg = $exc->getMessage() . PHP_EOL . $exc->getTraceAsString();
            \Yii::error($msg, 'Api-log');
        }

        if (isset($exception) && $this->throwDoAsyncException) {
            throw $exception;
        }
    }

    protected function handleBusiness($taskContext) {
        $asyncResult = [];
        $readInMem = $this->readPageDataInMem();

        //构造请求参数，分页拉取数据
        $param = $this->getPostParam();
        $firstPageData = $this->getCleint()->post($this->getApi(), $param, [], $taskContext);
        if ($readInMem) {
            $asyncResult[] = $firstPageData;
        }

        //记录截止时间戳
        $param->endtimestamp = $firstPageData['endtimestamp'];
        $this->handleDelData($param, $firstPageData);
        if (!empty($firstPageData['data'])) {
            $this->handleModifyData($firstPageData);
            $pageCount = intval($firstPageData['pagecount']);
            for ($i = 2; $i <= $pageCount; $i++) {
                $param->pageindex = $i;
                $data = $this->getCleint()->post($this->getApi(), $param, [], $taskContext);
                if ($readInMem) {
                    $asyncResult[] = $data;
                }
                $this->handleModifyData($data);
            }
        }

        //请求后处理
        $this->afterDoAsync($param, $asyncResult);

        //数据处理完，无异常则更新时间戳
        if (!empty($param->endtimestamp)) {
            $this->getErpApiTsSrv()->updateTimestamp($taskContext['taskName'], $this->getBizkey(), $param->endtimestamp);
        }
    }

    /**
     * 处理返回数据
     * @param type $erpData ERP返回数据
     */
    protected function handleModifyData($erpData) {
        
    }

    /**
     * 处理主表和关联表的删除
     * @param \mysoft\erp\ApiParam $param
     * @param type $firstPageData
     * @return type
     * @throws \Exception
     */
    protected function handleDelData(\mysoft\erp\ApiParam $param, $firstPageData) {
        
    }

    protected function afterDoAsync(\mysoft\erp\ApiParam $param, $asyncResult = []) {
        
    }

    /**
     * 检查是否有正在执行的作业，true正在执行
     * @return type
     */
    protected function checkHasExecutionTask() {
        $taskKeyFormart = 'execution_task_{taskname}_{orgcode}';
        $execution = \Yii::$app->cache->get([$taskKeyFormart, $this->getServiceName(), $this->orgcode]);
        return !empty($execution);
    }

    /**
     * 设置任务执行状态
     * @param type $startOrEnd ,start =>true end=>false
     */
    protected function setExecutionStatus($startOrEnd) {
        $taskKeyFormart = 'execution_task_{taskname}_{orgcode}';
        $arrKey = [
            $taskKeyFormart,
            $this->getServiceName(),
            $this->orgcode
        ];
        if ($startOrEnd == false) {
            // 执行完成移除正在执行的状态
            $success = \Yii::$app->cache->delete($arrKey);
            if (!$success) {
                sleep(5);
                $success = \Yii::$app->cache->delete($arrKey);
                if (!$success) {
                    // 删除正在执行的缓存失败
                    \Yii::error("删除正在执行的的状态失败" . json_encode($arrKey), "Erp-Api-Executing-Task");
                }
            }
        } else {
            $success = \Yii::$app->cache->set($arrKey, $startOrEnd, 60 * 10);
            if (!$success) {
                sleep(5);
                $success = \Yii::$app->cache->set($arrKey, $startOrEnd, 60 * 10);
                if (!$success) {
                    \Yii::error("添加同步控制失败" . json_encode($arrKey), "Erp-Api-Executing-Task");
                }
            }
        }
    }

}
