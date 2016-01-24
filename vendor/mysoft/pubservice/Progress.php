<?php
namespace mysoft\pubservice;

/**
 * 通用的进度管理类，用于前台跟作业交换作业的处理状态
 * 
 * $pg = new \mysoft\pubservice\Progress($orgcode,$event,$app_code);
 * 
 * $pg->start();
 * 
 * $pg->get()
 * 
 * $pg->set()
 * @author fangl
 *
 */
class Progress {
    
    const CACHE_KEY = 'progress_{orgcode}_{app_code}_{event}';

    const STATUS_START = 0;  //开始状态
    
    const STATUS_END = 1;   //结束状态
    
    const SATUS_ERR = -1;   //异常状态
    
    private $orgcode;
    private $event;
    private $app_code;
    
    /**
     * @param string $orgcode 租户ID
     * @param string $event 进度识别ID
     * @param string $app_code
     */
    public function __construct($orgcode,$event,$app_code) {
        $this->orgcode = $orgcode;
        $this->event = $event;
        $this->app_code = $app_code;
    }
    
    /**
     * 进度开始
     * @param number $status
     * @param number $percent
     * @param string $msg
     * @param array $data
     * @return \yii\caching\boolean
     */
    public function start($status=self::STATUS_START,$percent=0,$msg="开始了",$data=[]) {
        $pgdata = [
            'status'=>$status,
            'percent'=>$percent,
            'msg'=>$msg,
            'data'=>$data
        ];
        return $this->setPgData($pgdata);
    }
    
    /**
     * 进度结束
     * @param number $status
     * @param number $percent
     * @param string $msg
     * @param array $data
     * @return \yii\caching\boolean
     */
    public function end($status=self::STATUS_END,$percent=100,$msg="结束了",$data=[]) {
        $pgdata = [
            'status'=>$status,
            'percent'=>$percent,
            'msg'=>$msg,
            'data'=>$data
        ];
        return $this->setPgData($pgdata);
    }
    
    /**
     * 设置进度状态
     * @param number $status
     * @param number $percent
     * @param string $msg
     * @param array $data
     * @return \yii\caching\boolean
     */
    public function set($percent, $msg='', $status=self::STATUS_START, $data=[]) {
        $pgdata = $this->get();
        if(empty($pgdata)) {
            $pgdata = [];
        }
        
        $pgdata['status'] = $status;
        $pgdata['percent'] = $percent;
        
        if(!empty($msg)) {
            $pgdata['msg'] = $msg;
        }
        
        if(!empty($data)) {
            $pgdata['data'] = $data;
        }

        return $this->setPgData($pgdata);
    }
    
    /**
     * 获取进度状态
     * @return \yii\caching\mixed 
     * $pgdata = [
            'status'=>$status,
            'percent'=>$percent,
            'msg'=>$msg,
            'data'=>$data
        ];
     */
    public function get() {
        return \Yii::$app->cache->get([self::CACHE_KEY,$this->orgcode,$this->app_code,$this->event]);
    }
    
    private function setPgData($pgdata) {
        return \Yii::$app->cache->set([self::CACHE_KEY,$this->orgcode,$this->app_code,$this->event], $pgdata);
    }
}